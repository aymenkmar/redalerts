<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class TerminalWebSocketHandler implements MessageComponentInterface
{
    protected $clients;
    protected $sessions;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->sessions = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection
        $this->clients->attach($conn);
        
        Log::info("New WebSocket connection ({$conn->resourceId})");
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'connected',
            'message' => 'Terminal WebSocket connected'
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Invalid message format']));
                return;
            }

            switch ($data['type']) {
                case 'start_shell':
                    $this->handleStartShell($from, $data);
                    break;
                    
                case 'input':
                    $this->handleInput($from, $data);
                    break;
                    
                case 'resize':
                    $this->handleResize($from, $data);
                    break;
                    
                case 'ping':
                    $from->send(json_encode(['type' => 'pong']));
                    break;
                    
                default:
                    $from->send(json_encode(['type' => 'error', 'message' => 'Unknown message type']));
            }
            
        } catch (\Exception $e) {
            Log::error('WebSocket message error: ' . $e->getMessage());
            $from->send(json_encode(['type' => 'error', 'message' => 'Server error']));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Clean up session if exists
        if (isset($this->sessions[$conn->resourceId])) {
            $session = $this->sessions[$conn->resourceId];
            if ($session['process']->isRunning()) {
                $session['process']->stop();
            }
            unset($this->sessions[$conn->resourceId]);
        }
        
        $this->clients->detach($conn);
        Log::info("Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error("WebSocket error on connection {$conn->resourceId}: " . $e->getMessage());
        $conn->close();
    }

    protected function handleStartShell(ConnectionInterface $conn, array $data)
    {
        if (!isset($data['namespace'], $data['pod'])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Missing namespace or pod']));
            return;
        }

        $namespace = $data['namespace'];
        $pod = $data['pod'];
        $container = $data['container'] ?? null;
        $cluster = $data['cluster'] ?? session('selectedCluster');

        if (!$cluster) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'No cluster selected']));
            return;
        }

        $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $cluster;

        if (!file_exists($kubeconfigPath)) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Kubeconfig file not found']));
            return;
        }

        // Build kubectl exec command
        $command = [
            'kubectl',
            'exec',
            '-i',
            '-t',
            '-n', $namespace,
            $pod
        ];

        if ($container) {
            $command[] = '-c';
            $command[] = $container;
        }

        $command[] = '--';
        $command[] = 'sh';
        $command[] = '-c';
        $command[] = 'clear; (bash || ash || sh)';

        try {
            $env = array_merge($_ENV, [
                'KUBECONFIG' => $kubeconfigPath,
                'TERM' => 'xterm-256color',
                'COLUMNS' => $data['cols'] ?? '80',
                'LINES' => $data['rows'] ?? '24'
            ]);

            $process = new Process($command, null, $env);
            $process->setTimeout(null);
            $process->setIdleTimeout(null);
            $process->setPty(true);

            // Start the process
            $process->start();

            // Store session
            $this->sessions[$conn->resourceId] = [
                'process' => $process,
                'namespace' => $namespace,
                'pod' => $pod,
                'container' => $container,
                'connection' => $conn
            ];

            // Send success response
            $conn->send(json_encode([
                'type' => 'shell_started',
                'message' => "Connected to {$namespace}/{$pod}" . ($container ? "/{$container}" : '')
            ]));

            // Start output streaming
            $this->startOutputStreaming($conn->resourceId);

        } catch (\Exception $e) {
            Log::error('Failed to start shell process', [
                'namespace' => $namespace,
                'pod' => $pod,
                'error' => $e->getMessage()
            ]);

            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Failed to start shell: ' . $e->getMessage()
            ]));
        }
    }

    protected function handleInput(ConnectionInterface $conn, array $data)
    {
        if (!isset($this->sessions[$conn->resourceId])) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'No active session']));
            return;
        }

        $session = $this->sessions[$conn->resourceId];
        $process = $session['process'];

        if (!$process->isRunning()) {
            $conn->send(json_encode(['type' => 'error', 'message' => 'Process not running']));
            return;
        }

        $input = $data['data'] ?? '';
        
        try {
            $process->getInput()->write($input);
        } catch (\Exception $e) {
            Log::error('Failed to write to process: ' . $e->getMessage());
            $conn->send(json_encode(['type' => 'error', 'message' => 'Failed to send input']));
        }
    }

    protected function handleResize(ConnectionInterface $conn, array $data)
    {
        if (!isset($this->sessions[$conn->resourceId])) {
            return;
        }

        $cols = $data['cols'] ?? 80;
        $rows = $data['rows'] ?? 24;

        // Note: Resizing PTY is complex and may require additional libraries
        // For now, we'll just log the resize request
        Log::info("Terminal resize requested: {$cols}x{$rows}");
    }

    protected function startOutputStreaming($connectionId)
    {
        if (!isset($this->sessions[$connectionId])) {
            return;
        }

        $session = $this->sessions[$connectionId];
        $process = $session['process'];
        $conn = $session['connection'];

        // Use a timer to periodically check for output
        $this->streamOutput($connectionId);
    }

    protected function streamOutput($connectionId)
    {
        if (!isset($this->sessions[$connectionId])) {
            return;
        }

        $session = $this->sessions[$connectionId];
        $process = $session['process'];
        $conn = $session['connection'];

        try {
            if ($process->isRunning()) {
                $output = $process->getIncrementalOutput();
                $errorOutput = $process->getIncrementalErrorOutput();

                if (!empty($output)) {
                    $conn->send(json_encode([
                        'type' => 'output',
                        'data' => $output
                    ]));
                }

                if (!empty($errorOutput)) {
                    $conn->send(json_encode([
                        'type' => 'output',
                        'data' => $errorOutput
                    ]));
                }

                // Schedule next check
                \React\EventLoop\Loop::get()->addTimer(0.1, function() use ($connectionId) {
                    $this->streamOutput($connectionId);
                });
            } else {
                // Process ended
                $conn->send(json_encode([
                    'type' => 'process_ended',
                    'exit_code' => $process->getExitCode()
                ]));
                
                unset($this->sessions[$connectionId]);
            }
        } catch (\Exception $e) {
            Log::error('Output streaming error: ' . $e->getMessage());
            $conn->send(json_encode(['type' => 'error', 'message' => 'Output streaming failed']));
        }
    }
}
