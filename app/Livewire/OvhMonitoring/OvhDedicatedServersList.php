<?php

namespace App\Livewire\OvhMonitoring;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OvhDedicatedServer;
use App\Services\OvhApiService;
use Illuminate\Support\Facades\Auth;

class OvhDedicatedServersList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $expirationFilter = 'all';
    public $lastRefresh;
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'expirationFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->lastRefresh = now()->format('H:i:s');
    }

    public function refreshData()
    {
        $this->lastRefresh = now()->format('H:i:s');
        // The render method will automatically fetch fresh data
    }

    public function syncServices()
    {
        try {
            $ovhService = new OvhApiService();
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
            }

            session()->flash('message', 'Dedicated server services synced successfully.');
            $this->refreshData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync dedicated server services: ' . $e->getMessage());
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingExpirationFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = OvhDedicatedServer::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('service_name', 'like', '%' . $this->search . '%')
                  ->orWhere('display_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('state', $this->statusFilter);
        }

        if ($this->expirationFilter === 'expiring_soon') {
            $query->expiringSoon();
        } elseif ($this->expirationFilter === 'expired') {
            $query->expired();
        }

        $dedicatedServers = $query->latest('last_synced_at')->paginate($this->perPage);

        return view('livewire.ovh-monitoring.ovh-dedicated-servers-list', [
            'dedicatedServers' => $dedicatedServers
        ])->layout('layouts.main');
    }
}
