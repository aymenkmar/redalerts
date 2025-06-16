<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OvhApiService;
use App\Models\OvhVps;
use App\Models\OvhDedicatedServer;
use App\Models\OvhDomain;
use Carbon\Carbon;

class SyncOvhServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:sync-services {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OVH services data from API to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting OVH services synchronization...');

        try {
            $ovhService = new OvhApiService();

            if (!$ovhService->testConnection()) {
                $this->error('❌ OVH API connection failed!');
                return 1;
            }

            $this->info('✅ OVH API connection successful');

            // Sync VPS services
            $this->syncVpsServices($ovhService);

            // Sync Dedicated Server services
            $this->syncDedicatedServerServices($ovhService);

            // Sync Domain services
            $this->syncDomainServices($ovhService);

            $this->info('✅ OVH services synchronization completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error during synchronization: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function syncVpsServices(OvhApiService $ovhService)
    {
        $this->info('Syncing VPS services...');

        $vpsServices = $ovhService->getVpsServices();
        $syncedCount = 0;

        foreach ($vpsServices as $serviceData) {
            OvhVps::updateOrCreate(
                ['service_name' => $serviceData['service_name']],
                [
                    'display_name' => $serviceData['display_name'],
                    'state' => $serviceData['state'],
                    'expiration_date' => $serviceData['expiration_date'],
                    'engagement_date' => $serviceData['engagement_date'],
                    'renewal_type' => $serviceData['renewal_type'],
                    'raw_data' => $serviceData['raw_data'],
                    'last_synced_at' => now(),
                ]
            );
            $syncedCount++;
        }

        $this->info("✅ Synced {$syncedCount} VPS services");
    }

    private function syncDedicatedServerServices(OvhApiService $ovhService)
    {
        $this->info('Syncing Dedicated Server services...');

        $serverServices = $ovhService->getDedicatedServerServices();
        $syncedCount = 0;

        foreach ($serverServices as $serviceData) {
            OvhDedicatedServer::updateOrCreate(
                ['service_name' => $serviceData['service_name']],
                [
                    'display_name' => $serviceData['display_name'],
                    'state' => $serviceData['state'],
                    'expiration_date' => $serviceData['expiration_date'],
                    'engagement_date' => $serviceData['engagement_date'],
                    'renewal_type' => $serviceData['renewal_type'],
                    'raw_data' => $serviceData['raw_data'],
                    'last_synced_at' => now(),
                ]
            );
            $syncedCount++;
        }

        $this->info("✅ Synced {$syncedCount} Dedicated Server services");
    }

    private function syncDomainServices(OvhApiService $ovhService)
    {
        $this->info('Syncing Domain services...');

        $domainServices = $ovhService->getDomainServices();
        $syncedCount = 0;

        foreach ($domainServices as $serviceData) {
            OvhDomain::updateOrCreate(
                ['service_name' => $serviceData['service_name']],
                [
                    'display_name' => $serviceData['display_name'],
                    'state' => $serviceData['state'],
                    'expiration_date' => $serviceData['expiration_date'],
                    'engagement_date' => $serviceData['engagement_date'],
                    'renewal_type' => $serviceData['renewal_type'],
                    'raw_data' => $serviceData['raw_data'],
                    'last_synced_at' => now(),
                ]
            );
            $syncedCount++;
        }

        $this->info("✅ Synced {$syncedCount} Domain services");
    }
}
