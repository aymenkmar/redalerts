<?php

namespace App\Livewire\WebsiteMonitoring;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Website;
use App\Models\WebsiteMonitoringLog;
use App\Models\WebsiteDowntimeIncident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WebsiteHistory extends Component
{
    use WithPagination;

    public Website $website;
    public $selectedUrlId = 'all';
    public $checkTypeFilter = 'all';
    public $statusFilter = 'all';
    public $startDate = '';
    public $endDate = '';
    public $activeTab = 'logs'; // logs, incidents

    protected $queryString = [
        'selectedUrlId' => ['except' => 'all'],
        'checkTypeFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'activeTab' => ['except' => 'logs'],
    ];

    public function mount(Website $website)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->website = $website->load('urls');
        $this->setDefaultDateRange();
    }

    public function updatingSelectedUrlId()
    {
        $this->resetPage();
    }

    public function updatingCheckTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingActiveTab()
    {
        $this->resetPage();
    }

    public function setDefaultDateRange()
    {
        $now = Carbon::now();
        $this->startDate = $now->subDays(7)->format('Y-m-d');
        $this->endDate = $now->format('Y-m-d');
    }

    public function setDateRange($days)
    {
        $now = Carbon::now();
        $this->endDate = $now->format('Y-m-d');
        
        switch ($days) {
            case 1:
                $this->startDate = $now->subDay()->format('Y-m-d');
                break;
            case 7:
                $this->startDate = $now->subDays(7)->format('Y-m-d');
                break;
            case 30:
                $this->startDate = $now->subDays(30)->format('Y-m-d');
                break;
            case 90:
                $this->startDate = $now->subDays(90)->format('Y-m-d');
                break;
        }
        
        $this->resetPage();
    }

    private function getHistoryStats()
    {
        $query = WebsiteMonitoringLog::whereHas('websiteUrl', function ($q) {
            $q->where('website_id', $this->website->id);
            
            if ($this->selectedUrlId !== 'all') {
                $q->where('id', $this->selectedUrlId);
            }
        });

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('checked_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        if ($this->checkTypeFilter !== 'all') {
            $query->where('check_type', $this->checkTypeFilter);
        }

        $total = $query->count();
        $upCount = $query->where('status', 'up')->count();
        $downCount = $query->where('status', 'down')->count();
        $warningCount = $query->where('status', 'warning')->count();

        return [
            'total' => $total,
            'up' => $upCount,
            'down' => $downCount,
            'warning' => $warningCount,
            'uptime_percentage' => $total > 0 ? round(($upCount / $total) * 100, 2) : 0,
        ];
    }

    public function render()
    {
        $stats = $this->getHistoryStats();

        if ($this->activeTab === 'logs') {
            $query = WebsiteMonitoringLog::with('websiteUrl')
                ->whereHas('websiteUrl', function ($q) {
                    $q->where('website_id', $this->website->id);
                    
                    if ($this->selectedUrlId !== 'all') {
                        $q->where('id', $this->selectedUrlId);
                    }
                });

            if ($this->checkTypeFilter !== 'all') {
                $query->where('check_type', $this->checkTypeFilter);
            }

            if ($this->statusFilter !== 'all') {
                $query->where('status', $this->statusFilter);
            }

            if ($this->startDate && $this->endDate) {
                $query->whereBetween('checked_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            }

            $data = $query->latest('checked_at')->paginate(20);
        } else {
            // Incidents tab
            $query = WebsiteDowntimeIncident::with('websiteUrl')
                ->whereHas('websiteUrl', function ($q) {
                    $q->where('website_id', $this->website->id);
                    
                    if ($this->selectedUrlId !== 'all') {
                        $q->where('id', $this->selectedUrlId);
                    }
                });

            if ($this->startDate && $this->endDate) {
                $query->whereBetween('started_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            }

            $data = $query->latest('started_at')->paginate(20);
        }

        return view('livewire.website-monitoring.website-history', [
            'data' => $data,
            'stats' => $stats,
        ])->layout('layouts.main');
    }
}
