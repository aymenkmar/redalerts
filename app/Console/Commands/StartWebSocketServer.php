<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\TerminalWebSocketHandler;

class StartWebSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve {--port=6001}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WebSocket server for terminal connections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $port = $this->option('port');
        
        $this->info("Starting WebSocket server on port {$port}...");

        try {
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new TerminalWebSocketHandler()
                    )
                ),
                $port
            );

            $this->info("WebSocket server started successfully on port {$port}");
            $this->info("Terminal connections available at ws://localhost:{$port}/terminal");
            
            $server->run();
            
        } catch (\Exception $e) {
            $this->error("Failed to start WebSocket server: " . $e->getMessage());
            return 1;
        }
    }
}
