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
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
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
        try {
            $website = Website::with('urls')->findOrFail($websiteId);
            $monitoringService = new WebsiteMonitoringService();

            foreach ($website->urls as $url) {
                if ($url->monitor_status || $url->monitor_domain || $url->monitor_ssl) {
                    $monitoringService->monitorWebsiteUrl($url);
                }
            }

            // Process notifications for any status changes
            $notificationService = new \App\Services\WebsiteNotificationService();
            $notificationService->processAllNotifications();

            session()->flash('message', 'Website monitoring check completed.');
            $this->dispatch('refreshWebsites');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Website monitoring check failed', [
                'website_id' => $websiteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show user-friendly error message
            session()->flash('error', 'Failed to check website: ' . $e->getMessage());
            $this->dispatch('refreshWebsites');
        }
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
            $searchTerm = strtolower($this->search);
            $query->where(function ($q) use ($searchTerm) {
                // Search in website name (case-insensitive)
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                  // Search in website URLs (case-insensitive)
                  ->orWhereHas('urls', function ($urlQuery) use ($searchTerm) {
                      $urlQuery->whereRaw('LOWER(url) LIKE ?', ['%' . $searchTerm . '%']);
                  });
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('overall_status', $this->statusFilter);
        }

        $websites = $query->latest()->paginate($this->perPage);

        return view('livewire.website-monitoring.website-list', [
            'websites' => $websites
        ])->layout('layouts.main');
    }
}
