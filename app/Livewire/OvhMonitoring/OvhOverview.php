<?php

namespace App\Livewire\OvhMonitoring;

use Livewire\Component;
use App\Models\OvhVps;
use App\Models\OvhDedicatedServer;
use App\Models\OvhDomain;
use Illuminate\Support\Facades\Auth;

class OvhOverview extends Component
{
    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
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
        ])->layout('layouts.main');
    }
}
