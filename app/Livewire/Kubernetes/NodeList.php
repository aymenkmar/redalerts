<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use App\Traits\HasKubernetesTable;
use Carbon\Carbon;

class NodeList extends Component
{
    use HasKubernetesTable;

    public $nodes = [];
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
                $nodesResponse = $service->getNodes();
            } catch (\Exception $e) {
                // Fallback to regular service if cached service fails
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $nodesResponse = $service->getNodes();
            }

            if (isset($nodesResponse['items'])) {
                $this->nodes = $nodesResponse['items'];
            } else {
                $this->nodes = [];
                // If no items, check if there's an error in the response
                if (isset($nodesResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $nodesResponse['message']);
                }
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load nodes: ' . $e->getMessage();
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
                'message' => 'Nodes data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
        }
    }

    // Keep for backward compatibility
    public function loadNodes()
    {
        $this->loadData();
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

    public function getNodeConditions($node)
    {
        $conditions = collect($node['status']['conditions'] ?? []);
        $readyCondition = $conditions->firstWhere('type', 'Ready');

        if ($readyCondition && $readyCondition['status'] === 'True') {
            return 'Ready';
        }

        // Check for problematic conditions
        $problemConditions = $conditions->filter(function ($condition) {
            return $condition['status'] === 'True' &&
                   in_array($condition['type'], ['MemoryPressure', 'DiskPressure', 'PIDPressure', 'NetworkUnavailable']);
        });

        if ($problemConditions->isNotEmpty()) {
            return $problemConditions->pluck('type')->join(', ');
        }

        return $readyCondition ? 'Not Ready' : 'Unknown';
    }

    public function getNodeWarnings($node)
    {
        $conditions = collect($node['status']['conditions'] ?? []);
        $warnings = [];

        // Check for warning conditions
        $warningConditions = $conditions->filter(function ($condition) {
            return $condition['status'] === 'True' &&
                   in_array($condition['type'], ['MemoryPressure', 'DiskPressure', 'PIDPressure']);
        });

        if ($warningConditions->isNotEmpty()) {
            $warnings = array_merge($warnings, $warningConditions->pluck('type')->toArray());
        }

        // Check for unschedulable nodes
        if (isset($node['spec']['unschedulable']) && $node['spec']['unschedulable']) {
            $warnings[] = 'Unschedulable';
        }

        return count($warnings) > 0 ? implode(', ', $warnings) : '-';
    }

    public function getNodeTaints($node)
    {
        $taints = $node['spec']['taints'] ?? [];
        return count($taints);
    }

    public function getNodeRoles($node)
    {
        $labels = $node['metadata']['labels'] ?? [];
        $roles = [];

        // Check for standard node-role.kubernetes.io/ labels
        foreach ($labels as $key => $value) {
            if (str_starts_with($key, 'node-role.kubernetes.io/')) {
                $role = str_replace('node-role.kubernetes.io/', '', $key);
                $roles[] = $role;
            }
        }

        // Check for legacy master labels (older Kubernetes versions)
        if (isset($labels['kubernetes.io/role']) && $labels['kubernetes.io/role'] === 'master') {
            $roles[] = 'master';
        }

        // Check for control-plane labels (newer Kubernetes versions)
        if (isset($labels['node-role.kubernetes.io/control-plane'])) {
            $roles[] = 'control-plane';
        }

        // Check for master labels (some distributions)
        if (isset($labels['node-role.kubernetes.io/master'])) {
            $roles[] = 'master';
        }

        // Remove duplicates and join
        $roles = array_unique($roles);

        return count($roles) > 0 ? implode(', ', $roles) : 'worker';
    }

    protected function getTableData()
    {
        $filteredNodes = collect($this->nodes);

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $filteredNodes = $filteredNodes->filter(function ($node) {
                $name = $node['metadata']['name'] ?? '';
                return stripos($name, $this->searchTerm) !== false;
            });
        }

        // Apply sorting
        if (!empty($this->sortField)) {
            $filteredNodes = $filteredNodes->sortBy(function ($node) {
                switch ($this->sortField) {
                    case 'name':
                        return $node['metadata']['name'] ?? '';
                    case 'status':
                        return $this->getNodeStatus($node);
                    case 'roles':
                        return $this->getNodeRoles($node);
                    case 'age':
                        return $node['metadata']['creationTimestamp'] ?? '';
                    case 'version':
                        return $node['status']['nodeInfo']['kubeletVersion'] ?? '';
                    default:
                        return '';
                }
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        // Update total count for pagination
        $this->totalItems = $filteredNodes->count();

        // Apply pagination
        $paginatedNodes = $filteredNodes->forPage($this->currentPage, $this->perPage);

        return $paginatedNodes->values()->all();
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
                'field' => 'status',
                'label' => 'Status',
                'sortable' => true
            ],
            [
                'field' => 'warnings',
                'label' => '<svg class="w-4 h-4 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
                'sortable' => false,
                'is_html' => true
            ],
            [
                'field' => 'roles',
                'label' => 'Roles',
                'sortable' => true
            ],
            [
                'field' => 'age',
                'label' => 'Age',
                'sortable' => true
            ],
            [
                'field' => 'version',
                'label' => 'Version',
                'sortable' => true
            ]
        ];
    }

    private function getNodeStatus($node)
    {
        $conditions = $node['status']['conditions'] ?? [];

        foreach ($conditions as $condition) {
            if ($condition['type'] === 'Ready') {
                return $condition['status'] === 'True' ? 'Ready' : 'Not Ready';
            }
        }

        return 'Unknown';
    }

    private function hasNodeWarnings($node)
    {
        $conditions = $node['status']['conditions'] ?? [];

        foreach ($conditions as $condition) {
            if ($condition['type'] !== 'Ready' && $condition['status'] === 'True') {
                return true;
            }
        }

        return false;
    }



    public function render()
    {
        return view('livewire.kubernetes.node-list')->layout('layouts.kubernetes');
    }
}
