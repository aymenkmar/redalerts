<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class KubectlAiService
{
    private ?string $kubeconfigPath;
    private array $defaultConfig;
    private string $kubectlAiBinaryPath;

    public function __construct(?string $kubeconfigPath = null)
    {
        $this->kubeconfigPath = $kubeconfigPath;
        $this->kubectlAiBinaryPath = $this->getKubectlAiBinaryPath();
        $this->defaultConfig = [
            'model' => env('KUBECTL_AI_MODEL', 'gemini-2.5-flash-preview-04-17'),
            'provider' => env('KUBECTL_AI_PROVIDER', 'gemini'),
            'max_iterations' => env('KUBECTL_AI_MAX_ITERATIONS', 20),
            'timeout' => env('KUBECTL_AI_TIMEOUT', 120),
        ];
    }

    /**
     * Execute a kubectl-ai query
     */
    public function query(string $message, array $options = []): array
    {
        try {
            $sanitizedMessage = $this->sanitizeMessage($message);
            $command = $this->buildCommand($sanitizedMessage, $options);
            $env = $this->buildEnvironment();

            $process = new Process($command, null, $env);
            $process->setTimeout($this->defaultConfig['timeout']);
            
            Log::info('Executing kubectl-ai command', [
                'command' => implode(' ', array_slice($command, 0, -1)) . ' [MESSAGE]',
                'kubeconfig' => $this->kubeconfigPath,
                'options' => $options
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                return $this->handleError($process, $message);
            }

            $output = $process->getOutput();
            
            return [
                'success' => true,
                'response' => $output,
                'exit_code' => $process->getExitCode()
            ];

        } catch (\Exception $e) {
            Log::error('kubectl-ai service error', [
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'service_error'
            ];
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        try {
            $command = [$this->kubectlAiBinaryPath, 'models'];
            $process = new Process($command);
            $process->setTimeout(30);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            return $this->parseModelsOutput($output);

        } catch (\Exception $e) {
            Log::error('Failed to get kubectl-ai models', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Validate kubectl-ai installation and configuration
     */
    public function validateInstallation(): array
    {
        $validation = [
            'kubectl_ai_installed' => false,
            'kubectl_installed' => false,
            'kubeconfig_exists' => false,
            'api_keys_configured' => false,
            'cluster_accessible' => false,
            'errors' => []
        ];

        // Check kubectl-ai installation
        try {
            $process = new Process([$this->kubectlAiBinaryPath, '--help']);
            $process->setTimeout(10);
            $process->run();
            $validation['kubectl_ai_installed'] = $process->isSuccessful();
        } catch (\Exception $e) {
            $validation['errors'][] = 'kubectl-ai not installed: ' . $e->getMessage();
        }

        // Check kubectl installation
        try {
            $process = new Process(['kubectl', 'version', '--client']);
            $process->setTimeout(10);
            $process->run();
            $validation['kubectl_installed'] = $process->isSuccessful();
        } catch (\Exception $e) {
            $validation['errors'][] = 'kubectl not installed: ' . $e->getMessage();
        }

        // Check kubeconfig
        if ($this->kubeconfigPath) {
            $validation['kubeconfig_exists'] = file_exists($this->kubeconfigPath);
            if (!$validation['kubeconfig_exists']) {
                $validation['errors'][] = 'Kubeconfig file not found: ' . $this->kubeconfigPath;
            }
        }

        // Check API keys
        $apiKeys = [
            'GEMINI_API_KEY' => env('GEMINI_API_KEY'),
            'OPENAI_API_KEY' => env('OPENAI_API_KEY')
        ];
        
        $validation['api_keys_configured'] = !empty(array_filter($apiKeys));
        if (!$validation['api_keys_configured']) {
            $validation['errors'][] = 'No AI provider API keys configured';
        }

        // Test cluster access
        if ($validation['kubectl_installed'] && $validation['kubeconfig_exists']) {
            try {
                $env = ['KUBECONFIG' => $this->kubeconfigPath];
                $process = new Process(['kubectl', 'cluster-info'], null, $env);
                $process->setTimeout(10);
                $process->run();
                $validation['cluster_accessible'] = $process->isSuccessful();
                
                if (!$validation['cluster_accessible']) {
                    $validation['errors'][] = 'Cannot access cluster: ' . $process->getErrorOutput();
                }
            } catch (\Exception $e) {
                $validation['errors'][] = 'Cluster access test failed: ' . $e->getMessage();
            }
        }

        return $validation;
    }

    /**
     * Get service configuration
     */
    public function getConfiguration(): array
    {
        return [
            'default_config' => $this->defaultConfig,
            'kubeconfig_path' => $this->kubeconfigPath,
            'available_providers' => ['gemini', 'openai', 'ollama', 'grok'],
            'validation' => $this->validateInstallation()
        ];
    }

    /**
     * Build kubectl-ai command
     */
    private function buildCommand(string $message, array $options = []): array
    {
        $command = [
            $this->kubectlAiBinaryPath,
            '--quiet',
            '--model', $options['model'] ?? $this->defaultConfig['model'],
            '--llm-provider', $options['provider'] ?? $this->defaultConfig['provider'],
            '--max-iterations', (string)($options['max_iterations'] ?? $this->defaultConfig['max_iterations'])
        ];

        if ($this->kubeconfigPath) {
            $command[] = '--kubeconfig';
            $command[] = $this->kubeconfigPath;
        }

        // Add additional options
        if (!empty($options['skip_permissions'])) {
            $command[] = '--skip-permissions';
        }

        if (!empty($options['enable_tool_use_shim'])) {
            $command[] = '--enable-tool-use-shim';
        }

        $command[] = $message;

        return $command;
    }

    /**
     * Build environment variables
     */
    private function buildEnvironment(): array
    {
        $env = array_merge($_ENV, [
            'GEMINI_API_KEY' => env('GEMINI_API_KEY'),
            'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
            'GROK_API_KEY' => env('GROK_API_KEY'),
        ]);

        if ($this->kubeconfigPath) {
            $env['KUBECONFIG'] = $this->kubeconfigPath;
        }

        return $env;
    }

    /**
     * Handle process errors
     */
    private function handleError(Process $process, string $originalMessage): array
    {
        $errorOutput = $process->getErrorOutput();
        $output = $process->getOutput();
        $exitCode = $process->getExitCode();

        Log::error('kubectl-ai process failed', [
            'exit_code' => $exitCode,
            'error_output' => $errorOutput,
            'output' => $output,
            'original_message' => $originalMessage
        ]);

        // Categorize errors
        if (strpos($errorOutput, 'API key') !== false || strpos($errorOutput, 'authentication') !== false) {
            return [
                'success' => false,
                'error' => 'AI service authentication failed. Please check your API keys.',
                'type' => 'auth_error',
                'exit_code' => $exitCode
            ];
        }

        if (strpos($errorOutput, 'cluster') !== false || strpos($errorOutput, 'connection') !== false) {
            return [
                'success' => false,
                'error' => 'Cannot connect to Kubernetes cluster. Please verify cluster configuration.',
                'type' => 'cluster_error',
                'exit_code' => $exitCode
            ];
        }

        if (strpos($errorOutput, 'timeout') !== false) {
            return [
                'success' => false,
                'error' => 'Request timed out. Please try a simpler query or check your connection.',
                'type' => 'timeout_error',
                'exit_code' => $exitCode
            ];
        }

        return [
            'success' => false,
            'error' => 'kubectl-ai execution failed: ' . ($errorOutput ?: $output ?: 'Unknown error'),
            'type' => 'execution_error',
            'exit_code' => $exitCode
        ];
    }

    /**
     * Sanitize user message
     */
    private function sanitizeMessage(string $message): string
    {
        // Remove potentially dangerous characters
        $message = preg_replace('/[;&|`$(){}[\]\\\\]/', '', $message);
        
        // Limit length
        $message = substr($message, 0, 1000);
        
        return trim($message);
    }

    /**
     * Parse models output
     */
    private function parseModelsOutput(string $output): array
    {
        $lines = explode("\n", trim($output));
        $models = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#')) {
                $models[] = $line;
            }
        }

        return $models;
    }

    /**
     * Get the path to the kubectl-ai binary
     */
    private function getKubectlAiBinaryPath(): string
    {
        // First try the project-local binary
        $projectBinary = base_path('storage/kubectl-ai-google/kubectl-ai');

        if (file_exists($projectBinary) && is_executable($projectBinary)) {
            return $projectBinary;
        }

        // Fallback to system-wide installation
        $systemBinary = '/usr/local/bin/kubectl-ai';
        if (file_exists($systemBinary) && is_executable($systemBinary)) {
            return $systemBinary;
        }

        // Last resort: assume it's in PATH
        return 'kubectl-ai';
    }
}
