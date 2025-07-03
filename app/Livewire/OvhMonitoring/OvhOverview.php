<?php

namespace App\Livewire\OvhMonitoring;

use Livewire\Component;
use App\Models\OvhVps;
use App\Models\OvhDedicatedServer;
use App\Models\OvhDomain;
use App\Services\OvhApiService;
use Illuminate\Support\Facades\Auth;

class OvhOverview extends Component
{
    public $isLoading = false;

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function syncAllServices()
    {
        $this->isLoading = true;

        try {
            $ovhService = new OvhApiService();
            $syncedCount = 0;

            // Sync VPS Services
            $vpsServices = $ovhService->getVpsServices();
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

            // Sync Dedicated Server Services
            $serverServices = $ovhService->getDedicatedServerServices();
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

            // Sync Domain Services
            $domainServices = $ovhService->getDomainServices();
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

            // Check for expiring services and create notifications
            $notificationService = new \App\Services\OvhNotificationService();
            $notificationResults = $notificationService->checkAndNotifyExpiringServices();

            $totalNotifications = array_sum($notificationResults);
            $notificationMessage = $totalNotifications > 0
                ? " Created {$totalNotifications} expiration notifications."
                : "";

            session()->flash('message', "Successfully synced {$syncedCount} services across all OVH service types.{$notificationMessage}");
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync services: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        // Get counts for each service type
        $vpsCount = OvhVps::count();
        $vpsExpiring = OvhVps::expiringSoon()->count();
        $vpsExpired = OvhVps::expired()->count();

        $dedicatedCount = OvhDedicatedServer::count();
        $dedicatedExpiring = OvhDedicatedServer::expiringSoon()->count();
        $dedicatedExpired = OvhDedicatedServer::expired()->count();

        $domainCount = OvhDomain::count();
        $domainExpiring = OvhDomain::expiringSoon()->count();
        $domainExpired = OvhDomain::expired()->count();

        // Get last sync time
        $lastSyncTime = collect([
            OvhVps::max('last_synced_at'),
            OvhDedicatedServer::max('last_synced_at'),
            OvhDomain::max('last_synced_at')
        ])->filter()->max();

        return view('livewire.ovh-monitoring.ovh-overview', [
            'vpsCount' => $vpsCount,
            'vpsExpiring' => $vpsExpiring,
            'vpsExpired' => $vpsExpired,
            'dedicatedCount' => $dedicatedCount,
            'dedicatedExpiring' => $dedicatedExpiring,
            'dedicatedExpired' => $dedicatedExpired,
            'domainCount' => $domainCount,
            'domainExpiring' => $domainExpiring,
            'domainExpired' => $domainExpired,
            'lastSyncTime' => $lastSyncTime,
        ])->layout('layouts.main');
    }
}
