<?php

namespace App\Livewire\Kubernetes\Workloads;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use Carbon\Carbon;

class CronJobList extends Component
{
    public $cronJobs = [];
    public $namespaces = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;

    protected $queryString = [];

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Try both session keys for compatibility
        $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');
        if ($this->selectedCluster) {
            $this->loadData();
        } else {
            $this->error = 'Please select a cluster first';
            $this->loading = false;
        }
    }

    public function loadData()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            if (!file_exists($kubeconfigPath)) {
                throw new \Exception('Kubeconfig file not found: ' . $kubeconfigPath);
            }

            // Try cached service first, fallback to regular service
            try {
                $service = new CachedKubernetesService($kubeconfigPath);
                $cronJobsResponse = $service->getCronJobs();
            } catch (\Exception $e) {
                // Fallback to regular service if cached service fails
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $cronJobsResponse = $service->getCronJobs();
            }

            if (isset($cronJobsResponse['items'])) {
                $this->cronJobs = $cronJobsResponse['items'];
            } else {
                $this->cronJobs = [];
                // If no items, check if there's an error in the response
                if (isset($cronJobsResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $cronJobsResponse['message']);
                }
            }

            // Load namespaces (use the same service instance)
            $namespacesResponse = $service->getNamespaces();
            if (isset($namespacesResponse['items'])) {
                $this->namespaces = collect($namespacesResponse['items'])
                    ->map(function ($namespace) {
                        return $namespace['metadata']['name'];
                    })
                    ->toArray();
            } else {
                $this->namespaces = [];
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load cron jobs: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function refreshData()
    {
        try {
            // Refresh the selected cluster from session
            $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');

            if (!$this->selectedCluster) {
                $this->error = 'Please select a cluster first';
                return;
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new CachedKubernetesService($kubeconfigPath);

            // Force refresh cache
            $service->clearCache();
            $this->loadData();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Cron jobs data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
        }
    }

    public function calculateNextExecution($schedule, $lastScheduleTime = null, $timeZone = 'UTC')
    {
        try {
            // Parse the cron schedule
            $parts = explode(' ', $schedule);
            if (count($parts) < 5) {
                return 'Invalid schedule';
            }

            // Create a CronExpression
            $cron = new \Cron\CronExpression($schedule);

            // Create a DateTime object with the correct timezone
            $now = new \DateTime('now', new \DateTimeZone($timeZone));

            // Get the next run date
            $nextRun = $cron->getNextRunDate($now);

            return $nextRun->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return 'Error calculating next execution: ' . $e->getMessage();
        }
    }

    public function formatAge($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        $creationTime = Carbon::parse($timestamp);
        $now = Carbon::now();

        // Calculate total difference in various units
        $diffInSeconds = $creationTime->diffInSeconds($now);
        $diffInMinutes = $creationTime->diffInMinutes($now);
        $diffInHours = $creationTime->diffInHours($now);
        $diffInDays = $creationTime->diffInDays($now);

        // Calculate years and remaining days (Lens IDE format: 2y83d)
        $years = intval($diffInDays / 365);
        $remainingDays = $diffInDays % 365;

        if ($years > 0) {
            if ($remainingDays > 0) {
                return $years . 'y' . $remainingDays . 'd';
            } else {
                return $years . 'y';
            }
        }

        // For less than a year, show days
        if ($diffInDays >= 1) {
            return $diffInDays . 'd';
        }

        // For less than a day, show hours
        if ($diffInHours >= 1) {
            return $diffInHours . 'h';
        }

        // For less than an hour, show minutes
        if ($diffInMinutes >= 1) {
            return $diffInMinutes . 'm';
        }

        // For less than a minute, show seconds
        return $diffInSeconds . 's';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.cron-job-list')->layout('layouts.kubernetes');
    }
}
