<?php

namespace App\Livewire\Kubernetes\Workloads;

use Livewire\Component;
use App\Services\CachedKubernetesService;
use App\Traits\HasKubernetesTable;
use Carbon\Carbon;

class PodList extends Component
{
    use HasKubernetesTable;

    public $pods = [];
    public $namespaces = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;

    public function mount()
    {
        // Initialize trait properties
        $this->searchTerm = '';
        $this->selectedNamespaces = ['all'];
        $this->showNamespaceFilter = false;
        $this->sortField = '';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
        $this->currentPage = 1;
        $this->totalItems = 0;

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
                $podsResponse = $service->getPods();
            } catch (\Exception $e) {
                // Fallback to regular service if cached service fails
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $podsResponse = $service->getPods();
            }

            if (isset($podsResponse['items'])) {
                $this->pods = $podsResponse['items'];
            } else {
                $this->pods = [];
                // If no items, check if there's an error in the response
                if (isset($podsResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $podsResponse['message']);
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
            $this->error = 'Failed to load pods: ' . $e->getMessage();
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
                'message' => 'Pods data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
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

    protected function getTableData()
    {
        $filteredPods = collect($this->pods);

        // Apply namespace filter
        if (!in_array('all', $this->selectedNamespaces) && !empty($this->selectedNamespaces)) {
            $filteredPods = $filteredPods->filter(function ($pod) {
                $namespace = $pod['metadata']['namespace'] ?? 'default';
                return in_array($namespace, $this->selectedNamespaces);
            });
        }

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $filteredPods = $filteredPods->filter(function ($pod) {
                $name = $pod['metadata']['name'] ?? '';
                $namespace = $pod['metadata']['namespace'] ?? '';
                return stripos($name, $this->searchTerm) !== false ||
                       stripos($namespace, $this->searchTerm) !== false;
            });
        }

        // Apply sorting
        if (!empty($this->sortField)) {
            $filteredPods = $filteredPods->sortBy(function ($pod) {
                switch ($this->sortField) {
                    case 'name':
                        return $pod['metadata']['name'] ?? '';
                    case 'namespace':
                        return $pod['metadata']['namespace'] ?? '';
                    case 'status':
                        return $pod['status']['phase'] ?? '';
                    case 'restarts':
                        return $this->getPodRestarts($pod);
                    case 'age':
                        return $pod['metadata']['creationTimestamp'] ?? '';
                    case 'node':
                        return $pod['spec']['nodeName'] ?? '';
                    case 'qos':
                        return $pod['status']['qosClass'] ?? '';
                    default:
                        return '';
                }
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        // Update total count for pagination
        $this->totalItems = $filteredPods->count();

        // Apply pagination
        $paginatedPods = $filteredPods->forPage($this->currentPage, $this->perPage);

        return $paginatedPods->values()->all();
    }

    protected function getTableColumns()
    {
        return [
            [
                'field' => 'name',
                'label' => 'Name',
                'sortable' => true
            ],
            [
                'field' => 'namespace',
                'label' => 'Namespace',
                'sortable' => true
            ],
            [
                'field' => 'warnings',
                'label' => '<svg class="w-4 h-4 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
                'sortable' => false,
                'is_html' => true
            ],
            [
                'field' => 'ready',
                'label' => 'Ready',
                'sortable' => false
            ],
            [
                'field' => 'status',
                'label' => 'Status',
                'sortable' => true
            ],
            [
                'field' => 'restarts',
                'label' => 'Restarts',
                'sortable' => true
            ],
            [
                'field' => 'node',
                'label' => 'Node',
                'sortable' => true
            ],
            [
                'field' => 'qos',
                'label' => 'QoS',
                'sortable' => true
            ],
            [
                'field' => 'age',
                'label' => 'Age',
                'sortable' => true
            ],
            [
                'field' => 'actions',
                'label' => 'Actions',
                'sortable' => false
            ]
        ];
    }

    private function getPodRestarts($pod)
    {
        $restarts = 0;
        $containerStatuses = $pod['status']['containerStatuses'] ?? [];

        foreach ($containerStatuses as $status) {
            $restarts += $status['restartCount'] ?? 0;
        }

        return $restarts;
    }

    private function getPodReadyContainers($pod)
    {
        $ready = 0;
        $total = 0;
        $containerStatuses = $pod['status']['containerStatuses'] ?? [];

        foreach ($containerStatuses as $status) {
            $total++;
            if ($status['ready'] ?? false) {
                $ready++;
            }
        }

        return "$ready/$total";
    }

    private function isPodRunning($pod)
    {
        return ($pod['status']['phase'] ?? '') === 'Running';
    }

    private function hasPodWarnings($pod)
    {
        $containerStatuses = $pod['status']['containerStatuses'] ?? [];

        foreach ($containerStatuses as $status) {
            if (($status['restartCount'] ?? 0) > 0) {
                return true;
            }

            $state = $status['state'] ?? [];
            if (isset($state['waiting']) || isset($state['terminated'])) {
                return true;
            }
        }

        return false;
    }

    private function getPodWarnings($pod)
    {
        $warnings = [];
        $containerStatuses = $pod['status']['containerStatuses'] ?? [];

        foreach ($containerStatuses as $status) {
            if (($status['restartCount'] ?? 0) > 0) {
                $warnings[] = 'Restarts: ' . $status['restartCount'];
            }

            $state = $status['state'] ?? [];
            if (isset($state['waiting'])) {
                $warnings[] = 'Waiting: ' . ($state['waiting']['reason'] ?? 'Unknown');
            }
            if (isset($state['terminated'])) {
                $warnings[] = 'Terminated: ' . ($state['terminated']['reason'] ?? 'Unknown');
            }
        }

        return implode(', ', $warnings);
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.pod-list')->layout('layouts.kubernetes');
    }
}
