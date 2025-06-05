<?php

namespace App\Livewire\Kubernetes\Workloads;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use App\Traits\HasKubernetesTable;

class ReplicationControllerList extends Component
{
    use HasKubernetesTable;

    public $replicationControllers = [];
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

        // Load saved namespace selection
        $this->loadNamespaceSelection();

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
                $replicationControllersResponse = $service->getReplicationControllers();
            } catch (\Exception $e) {
                // Fallback to regular service if cached service fails
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $replicationControllersResponse = $service->getReplicationControllers();
            }

            if (isset($replicationControllersResponse['items'])) {
                $this->replicationControllers = $replicationControllersResponse['items'];
            } else {
                $this->replicationControllers = [];
                // If no items, check if there's an error in the response
                if (isset($replicationControllersResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $replicationControllersResponse['message']);
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
            $this->error = 'Failed to load replication controllers: ' . $e->getMessage();
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
                'message' => 'Replication controllers data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
        }
    }

    public function getTableData()
    {
        return $this->replicationControllers;
    }

    public function getTableColumns()
    {
        return [
            [
                'field' => 'name',
                'label' => 'Name',
                'sortable' => true
            ],
            [
                'field' => 'warnings',
                'label' => '<svg class="w-4 h-4 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
                'sortable' => false,
                'is_html' => true
            ],
            [
                'field' => 'namespace',
                'label' => 'Namespace',
                'sortable' => true
            ],
            [
                'field' => 'desired',
                'label' => 'Desired',
                'sortable' => true
            ],
            [
                'field' => 'current',
                'label' => 'Current',
                'sortable' => true
            ],
            [
                'field' => 'ready',
                'label' => 'Ready',
                'sortable' => true
            ],
            [
                'field' => 'selector',
                'label' => 'Selector',
                'sortable' => false
            ],
            [
                'field' => 'age',
                'label' => 'Age',
                'sortable' => true
            ]
        ];
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.replication-controller-list')->layout('layouts.kubernetes');
    }
}
