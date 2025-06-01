<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PodShellController extends Controller
{
    private const CACHE_PREFIX = 'shell_session_';
    private const CACHE_TTL = 1800; // 30 minutes

    /**
     * Store session data in cache
     */
    private function storeSession($sessionId, $data)
    {
        Cache::put(self::CACHE_PREFIX . $sessionId, $data, self::CACHE_TTL);
    }

    /**
     * Retrieve session data from cache
     */
    private function getSession($sessionId)
    {
        return Cache::get(self::CACHE_PREFIX . $sessionId);
    }

    /**
     * Remove session from cache
     */
    private function removeSession($sessionId)
    {
        Cache::forget(self::CACHE_PREFIX . $sessionId);
    }

    /**
     * Get all active sessions (simplified for file cache)
     */
    private function getAllSessions()
    {
        // For file-based cache, we'll maintain a simple registry
        $registry = Cache::get('shell_sessions_registry', []);
        $sessions = [];

        foreach ($registry as $sessionId) {
            $session = $this->getSession($sessionId);
            if ($session) {
                $sessions[$sessionId] = $session;
            }
        }

        return $sessions;
    }

    /**
     * Add session to registry
     */
    private function addToRegistry($sessionId)
    {
        $registry = Cache::get('shell_sessions_registry', []);
        $registry[] = $sessionId;
        Cache::put('shell_sessions_registry', array_unique($registry), self::CACHE_TTL);
    }

    /**
     * Remove session from registry
     */
    private function removeFromRegistry($sessionId)
    {
        $registry = Cache::get('shell_sessions_registry', []);
        $registry = array_filter($registry, fn($id) => $id !== $sessionId);
        Cache::put('shell_sessions_registry', $registry, self::CACHE_TTL);
    }

    /**
     * Detect the working directory for a pod
     */
    private function detectWorkingDirectory($session)
    {
        try {
            $kubeconfigPath = $session['kubeconfig_path'];
            $namespace = $session['namespace'];
            $pod = $session['pod'];
            $container = $session['container'];

            // Try to detect common project directories
            $detectCommand = [
                'kubectl', 'exec', '-n', $namespace, $pod
            ];

            if ($container) {
                $detectCommand[] = '-c';
                $detectCommand[] = $container;
            }

            $detectCommand[] = '--';
            $detectCommand[] = 'sh';
            $detectCommand[] = '-c';
            $detectCommand[] = 'for dir in /var/www/html /app /usr/src/app /opt/app /home/app /workspace /code; do if [ -d "$dir" ]; then echo "$dir"; exit 0; fi; done; pwd';

            $env = array_merge($_ENV, [
                'KUBECONFIG' => $kubeconfigPath,
                'TERM' => 'xterm-256color'
            ]);

            $process = new Process($detectCommand, null, $env);
            $process->setTimeout(10);
            $process->run();

            $workingDir = trim($process->getOutput());
            return $workingDir ?: '/';

        } catch (\Exception $e) {
            Log::warning('Failed to detect working directory', [
                'session_id' => $session['session_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return '/';
        }
    }

    /**
     * Start a shell session for a pod
     */
    public function startShell(Request $request)
    {
        $request->validate([
            'namespace' => 'required|string',
            'pod' => 'required|string',
            'container' => 'nullable|string',
        ]);

        $namespace = $request->input('namespace');
        $pod = $request->input('pod');
        $container = $request->input('container');
        $cluster = session('selectedCluster');

        if (!$cluster) {
            return response()->json(['error' => 'No cluster selected'], 400);
        }

        $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $cluster;

        if (!file_exists($kubeconfigPath)) {
            return response()->json(['error' => 'Kubeconfig file not found'], 404);
        }

        // Generate unique session ID
        $sessionId = uniqid('shell_', true);

        // Build kubectl exec command similar to Lens IDE
        $command = [
            'kubectl',
            'exec',
            '-i',
            '-t',
            '-n', $namespace,
            $pod
        ];

        // Add container if specified
        if ($container) {
            $command[] = '-c';
            $command[] = $container;
        }

        // Add shell command with full Linux support and proper TTY
        $command[] = '--';
        $command[] = 'sh';
        $command[] = '-c';
        $command[] = 'export TERM=xterm-256color; export SHELL=/bin/bash; stty sane; clear; exec bash -l || exec ash -l || exec sh -l';

        try {
            // Set KUBECONFIG environment variable
            $env = array_merge($_ENV, [
                'KUBECONFIG' => $kubeconfigPath,
                'TERM' => 'xterm-256color'
            ]);

            // Create and start process
            $process = new Process($command, null, $env);
            $process->setTimeout(null); // No timeout for interactive sessions
            $process->setIdleTimeout(null);
            $process->start(); // Start the process immediately

            // Detect working directory
            $workingDir = $this->detectWorkingDirectory([
                'kubeconfig_path' => $kubeconfigPath,
                'namespace' => $namespace,
                'pod' => $pod,
                'container' => $container
            ]);

            // Store session info in cache (without the process object)
            $sessionData = [
                'namespace' => $namespace,
                'pod' => $pod,
                'container' => $container,
                'created_at' => now()->toISOString(),
                'last_activity' => now()->toISOString(),
                'command' => $command,
                'cluster' => $cluster,
                'kubeconfig_path' => $kubeconfigPath,
                'status' => 'active',
                'working_dir' => $workingDir,
                'last_output' => "Connected to pod $pod in namespace $namespace\nWorking directory: $workingDir\n",
                'last_error' => '',
                'last_exit_code' => 0,
                'output_ready' => true
            ];

            $this->storeSession($sessionId, $sessionData);
            $this->addToRegistry($sessionId);

            Log::info('Shell session created', [
                'session_id' => $sessionId,
                'namespace' => $namespace,
                'pod' => $pod,
                'container' => $container
            ]);

            return response()->json([
                'session_id' => $sessionId,
                'status' => 'ready',
                'message' => 'Shell session created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start pod shell', [
                'namespace' => $namespace,
                'pod' => $pod,
                'container' => $container,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to start shell: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Execute command in shell session
     */
    public function executeCommand(Request $request, $sessionId)
    {
        $request->validate([
            'command' => 'sometimes|string',
            'raw_input' => 'sometimes|string',
        ]);

        $session = $this->getSession($sessionId);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        // Handle raw input (like tab completion)
        if ($request->has('raw_input')) {
            $input = $request->input('raw_input');
            $this->sendToShell($sessionId, $input);
            return response()->json(['status' => 'sent']);
        }

        $command = $request->input('command');

        try {
            // Handle special commands
            if ($command === 'clear') {
                $session['last_output'] = "\033[2J\033[H"; // ANSI clear screen
                $session['last_error'] = '';
                $session['last_exit_code'] = 0;
                $session['last_activity'] = now()->toISOString();
                $session['output_ready'] = true;
                $this->storeSession($sessionId, $session);
                return response()->json(['status' => 'executed', 'exit_code' => 0]);
            }

            // Execute command with full Linux support

            $kubeconfigPath = $session['kubeconfig_path'];
            $namespace = $session['namespace'];
            $pod = $session['pod'];
            $container = $session['container'];

            // Build kubectl exec command for single command execution
            $execCommand = [
                'kubectl',
                'exec',
                '-n', $namespace,
                $pod
            ];

            if ($container) {
                $execCommand[] = '-c';
                $execCommand[] = $container;
            }

            $execCommand[] = '--';
            $execCommand[] = 'sh';
            $execCommand[] = '-c';
            // Get current working directory from session or detect it
            $workingDir = $session['working_dir'] ?? $this->detectWorkingDirectory($session);

            // Handle directory change commands - but don't pre-change the working dir
            $isCdCommand = preg_match('/^cd\s+(.*)$/', trim($command), $matches);
            if ($isCdCommand) {
                $newDir = trim($matches[1]);
                if (empty($newDir) || $newDir === '~') {
                    $newDir = '/root'; // Default home directory
                } elseif (!str_starts_with($newDir, '/')) {
                    // Relative path
                    $newDir = rtrim($workingDir, '/') . '/' . $newDir;
                }
                // Don't change working_dir yet - wait for command to succeed
            }

            // Wrap command to ensure full Linux environment and proper shell handling
            $wrappedCommand = sprintf(
                'export TERM=xterm-256color; export SHELL=/bin/bash; export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin; cd %s 2>/dev/null || cd /; %s; echo "PWD:$(pwd)"',
                escapeshellarg($workingDir),
                $command
            );
            $execCommand[] = $wrappedCommand;

            $env = array_merge($_ENV, [
                'KUBECONFIG' => $kubeconfigPath,
                'TERM' => 'xterm-256color'
            ]);

            $process = new Process($execCommand, null, $env);
            // Optimized timeouts for better responsiveness
            $process->setTimeout(60); // 1 minute timeout for commands
            $process->setIdleTimeout(30); // 30 second idle timeout
            $process->run();

            // Store command output for retrieval
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            $exitCode = $process->getExitCode();

            // Extract working directory from output
            if (preg_match('/PWD:(.+)$/', $output, $matches)) {
                $newWorkingDir = trim($matches[1]);
                // Always update working directory from PWD output (it shows actual current directory)
                $session['working_dir'] = $newWorkingDir;
                // Remove the PWD line from output
                $output = preg_replace('/PWD:.+$/', '', $output);
                $output = rtrim($output);
            }

            // Format the output without command echo (frontend already shows it)
            $formattedOutput = "";
            if ($output) {
                $formattedOutput .= $output;
                if (!str_ends_with($output, "\n")) {
                    $formattedOutput .= "\n";
                }
            }
            if ($errorOutput) {
                $formattedOutput .= $errorOutput;
                if (!str_ends_with($errorOutput, "\n")) {
                    $formattedOutput .= "\n";
                }
            }
            if ($exitCode !== 0) {
                $formattedOutput .= "[Exit code: $exitCode]\n";
            }

            // Store the formatted output in session for retrieval
            $session['last_output'] = $formattedOutput;
            $session['last_error'] = '';
            $session['last_exit_code'] = $exitCode;
            $session['last_activity'] = now()->toISOString();
            $session['output_ready'] = true;
            $session['command_executed'] = true; // Flag to indicate a command was executed

            $this->storeSession($sessionId, $session);

            Log::info('Command executed', [
                'session_id' => $sessionId,
                'command' => $command,
                'exit_code' => $exitCode,
                'output_length' => strlen($output)
            ]);

            return response()->json([
                'status' => 'executed',
                'exit_code' => $exitCode
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to execute command in shell', [
                'session_id' => $sessionId,
                'command' => $command,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to execute command'], 500);
        }
    }

    /**
     * Send raw input to shell (for tab completion, etc.)
     */
    private function sendToShell($sessionId, $input)
    {
        $session = $this->getSession($sessionId);
        if (!$session) {
            return false;
        }

        try {
            // For tab completion, we need to send the input directly to the shell
            // This is a simplified approach - in a real implementation, you'd want
            // to maintain a persistent connection to the shell process

            // Build kubectl exec command to send input
            $command = [
                'kubectl',
                'exec',
                '-i',
                '-n', $session['namespace'],
                $session['pod']
            ];

            if ($session['container']) {
                $command[] = '-c';
                $command[] = $session['container'];
            }

            $command[] = '--';
            $command[] = 'bash';
            $command[] = '-c';
            $command[] = "printf '$input'";

            $env = ['KUBECONFIG' => $session['kubeconfig_path']];
            $process = new Process($command, null, $env);
            $process->setTimeout(2);
            $process->run();

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send input to shell', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Handle tab completion
     */
    public function tabComplete(Request $request, $sessionId)
    {
        $request->validate([
            'partial_command' => 'required|string',
            'cursor_position' => 'required|integer',
        ]);

        $session = $this->getSession($sessionId);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $partialCommand = $request->input('partial_command');
        $cursorPosition = $request->input('cursor_position');

        try {
            $kubeconfigPath = $session['kubeconfig_path'];
            $namespace = $session['namespace'];
            $pod = $session['pod'];
            $container = $session['container'];

            // Extract the word being completed
            $words = explode(' ', $partialCommand);
            $currentWord = end($words);

            // Determine if this is a path completion
            // Consider it a path if it has a slash OR if it's the second+ argument (likely a file/path)
            $isPath = strpos($currentWord, '/') !== false || count($words) > 1;
            $prefix = ''; // Initialize prefix variable

            if ($isPath) {
                // File/directory completion
                $workingDir = $session['working_dir'] ?? '/';
                $lastSlash = strrpos($currentWord, '/');

                if ($lastSlash === false) {
                    // No slash - completing in current directory
                    $searchDir = $workingDir;
                    $prefix = '';
                    $pattern = $currentWord;
                } else {
                    // Has slash - split path into directory and filename parts
                    $pathPart = substr($currentWord, 0, $lastSlash + 1);
                    $pattern = substr($currentWord, $lastSlash + 1);

                    if (str_starts_with($pathPart, '/')) {
                        // Absolute path
                        $searchDir = rtrim($pathPart, '/') ?: '/';
                        $prefix = $pathPart;
                    } else {
                        // Relative path
                        $searchDir = rtrim($workingDir, '/') . '/' . rtrim($pathPart, '/');
                        $prefix = $pathPart;
                    }
                }

                $completionScript = sprintf(
                    'cd %s 2>/dev/null && for item in $(ls -1a . 2>/dev/null | grep "^%s" | head -20); do if [ -d "$item" ]; then echo "$item/"; else echo "$item"; fi; done',
                    escapeshellarg($searchDir),
                    preg_quote($pattern, '/')
                );
            } else {
                // Command completion
                $completionScript = sprintf(
                    'compgen -c "%s" 2>/dev/null | head -20 || (echo "ls"; echo "cat"; echo "grep"; echo "find"; echo "ps"; echo "top"; echo "df"; echo "free"; echo "which"; echo "pwd") | grep "^%s"',
                    preg_quote($currentWord, '/'),
                    preg_quote($currentWord, '/')
                );
            }

            // Build kubectl exec command
            $execCommand = [
                'kubectl',
                'exec',
                '-n', $namespace,
                $pod
            ];

            if ($container) {
                $execCommand[] = '-c';
                $execCommand[] = $container;
            }

            $execCommand[] = '--';
            $execCommand[] = 'sh';
            $execCommand[] = '-c';
            $execCommand[] = $completionScript;

            $env = array_merge($_ENV, [
                'KUBECONFIG' => $kubeconfigPath,
                'TERM' => 'xterm-256color'
            ]);

            $process = new Process($execCommand, null, $env);
            $process->setTimeout(3); // Optimized timeout for tab completion
            $process->run();

            $output = trim($process->getOutput());
            $suggestions = $output ? explode("\n", $output) : [];

            // Filter and clean suggestions
            $suggestions = array_filter($suggestions, function($suggestion) {
                return !empty(trim($suggestion)) && $suggestion !== '.' && $suggestion !== '..';
            });

            $suggestions = array_values($suggestions); // Re-index array

            if (count($suggestions) === 1) {
                // Single completion - complete the word
                $completion = $suggestions[0];

                if ($isPath) {
                    // Use the prefix we calculated earlier
                    $words[count($words) - 1] = $prefix . $completion;
                } else {
                    // Command completion - add space after command
                    $words[count($words) - 1] = $completion;
                    $completion .= ' '; // Add space after command
                }

                $finalCompletion = implode(' ', $words);

                // Don't add extra space if completion already ends with /
                if (!str_ends_with($finalCompletion, '/') && !$isPath) {
                    $finalCompletion .= ' ';
                }

                return response()->json([
                    'completion' => $finalCompletion
                ]);
            } elseif (count($suggestions) > 1) {
                // Multiple suggestions - show them
                return response()->json([
                    'suggestions' => $suggestions
                ]);
            }

            // No completions found
            return response()->json([
                'completion' => null,
                'suggestions' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Tab completion failed', [
                'session_id' => $sessionId,
                'partial_command' => $partialCommand,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'completion' => null,
                'suggestions' => []
            ]);
        }
    }

    /**
     * Get shell output
     */
    public function getOutput($sessionId)
    {
        $session = $this->getSession($sessionId);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        try {
            // Return any pending output from the last command
            $output = '';
            $errorOutput = '';
            $exitCode = null;
            $commandExecuted = false;

            if (isset($session['output_ready']) && $session['output_ready']) {
                $output = $session['last_output'] ?? '';
                $errorOutput = $session['last_error'] ?? '';
                $exitCode = $session['last_exit_code'] ?? null;
                $commandExecuted = $session['command_executed'] ?? false;

                // Clear the output after sending it
                $session['output_ready'] = false;
                $session['last_output'] = '';
                $session['last_error'] = '';
                $session['last_exit_code'] = null;
                $session['command_executed'] = false;
                $this->storeSession($sessionId, $session);
            }

            $response = [
                'output' => $output,
                'error_output' => $errorOutput,
                'is_running' => $session['status'] === 'active',
                'exit_code' => $exitCode,
                'working_directory' => $session['working_dir'] ?? '/',
                'command_executed' => $commandExecuted
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to get shell output', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to get output'], 500);
        }
    }

    /**
     * Terminate shell session
     */
    public function terminateShell($sessionId)
    {
        $session = $this->getSession($sessionId);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        try {
            $this->removeSession($sessionId);
            $this->removeFromRegistry($sessionId);

            Log::info('Shell session terminated', [
                'session_id' => $sessionId,
                'namespace' => $session['namespace'],
                'pod' => $session['pod']
            ]);

            return response()->json(['status' => 'terminated']);

        } catch (\Exception $e) {
            Log::error('Failed to terminate shell session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to terminate session'], 500);
        }
    }

    /**
     * List active sessions
     */
    public function listSessions()
    {
        $sessions = [];
        $allSessions = $this->getAllSessions();

        foreach ($allSessions as $sessionId => $session) {
            $sessions[] = [
                'session_id' => $sessionId,
                'namespace' => $session['namespace'],
                'pod' => $session['pod'],
                'container' => $session['container'],
                'is_running' => $session['status'] === 'active',
                'created_at' => $session['created_at'],
                'last_activity' => $session['last_activity']
            ];
        }

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Cleanup inactive sessions
     */
    public function cleanup()
    {
        $cleaned = 0;
        $timeout = now()->subMinutes(30); // 30 minutes timeout
        $allSessions = $this->getAllSessions();

        foreach ($allSessions as $sessionId => $session) {
            $lastActivity = \Carbon\Carbon::parse($session['last_activity']);
            if ($lastActivity < $timeout) {
                $this->removeSession($sessionId);
                $this->removeFromRegistry($sessionId);
                $cleaned++;
            }
        }

        return response()->json(['cleaned_sessions' => $cleaned]);
    }
}
