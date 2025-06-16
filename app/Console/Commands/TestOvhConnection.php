<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OvhApiService;

class TestOvhConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OVH API connection and fetch basic information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing OVH API connection...');

        try {
            $ovhService = new OvhApiService();

            if ($ovhService->testConnection()) {
                $this->info('✅ OVH API connection successful!');

                $this->info('Fetching services...');

                // Test VPS services
                $vpsServices = $ovhService->getVpsServices();
                $this->info("Found " . count($vpsServices) . " VPS services");

                // Test Dedicated Server services
                $dedicatedServices = $ovhService->getDedicatedServerServices();
                $this->info("Found " . count($dedicatedServices) . " dedicated server services");

                // Test Domain services
                $domainServices = $ovhService->getDomainServices();
                $this->info("Found " . count($domainServices) . " domain services");

                // Display sample data
                if (!empty($vpsServices)) {
                    $this->info("\nSample VPS service:");
                    $sample = $vpsServices[0];
                    $this->line("- Name: " . $sample['service_name']);
                    $this->line("- Display Name: " . $sample['display_name']);
                    $this->line("- State: " . $sample['state']);
                    $this->line("- Expiration: " . ($sample['expiration_date'] ? $sample['expiration_date']->format('Y-m-d H:i:s') : 'N/A'));
                }

            } else {
                $this->error('❌ OVH API connection failed!');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
