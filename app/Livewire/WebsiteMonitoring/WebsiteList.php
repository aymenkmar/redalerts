<?php

namespace App\Livewire\WebsiteMonitoring;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Website;
use App\Services\WebsiteMonitoringService;
use Illuminate\Support\Facades\Auth;

class WebsiteList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $lastRefresh;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function checkWebsite($websiteId)
    {
        $website = Website::with('urls')->findOrFail($websiteId);
        $monitoringService = new WebsiteMonitoringService();

        foreach ($website->urls as $url) {
            if ($url->monitor_status || $url->monitor_domain || $url->monitor_ssl) {
                $monitoringService->monitorWebsiteUrl($url);
            }
        }

        session()->flash('message', 'Website monitoring check completed.');
        $this->dispatch('refreshWebsites');
    }

    public function deleteWebsite($websiteId)
    {
        $website = Website::findOrFail($websiteId);
        $website->delete();

        session()->flash('message', 'Website deleted successfully.');
        $this->dispatch('refreshWebsites');
    }

    public function toggleWebsiteStatus($websiteId)
    {
        $website = Website::findOrFail($websiteId);
        $website->update(['is_active' => !$website->is_active]);

        $message = $website->is_active ? 'Website activated.' : 'Website deactivated.';
        session()->flash('message', $message);
        $this->dispatch('refreshWebsites');
    }

    public function render()
    {
        $query = Website::with(['urls' => function ($query) {
            $query->latest('last_checked_at');
        }]);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== 'all') {
            $query->where('overall_status', $this->statusFilter);
        }

        $websites = $query->latest()->paginate(10);

        return view('livewire.website-monitoring.website-list', [
            'websites' => $websites
        ])->layout('layouts.main');
    }
}
