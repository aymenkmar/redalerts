<?php

namespace App\Livewire\Kubernetes\AccessControl;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use App\Traits\HasKubernetesTable;

class ClusterRoleList extends Component
{
    use HasKubernetesTable;

    public $clusterRoles = [];
    public $namespaces = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;

    protected $listeners = ['clusterSelected' => 'handleClusterSelected'];

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

        // Get the selected cluster from session
        $this->selectedCluster = session('selectedCluster');

        if ($this->selectedCluster) {
            $this->loadClusterRoles();
        } else {
            // Set error message when no cluster is selected
            $this->error = 'Please select a cluster first';
            $this->loading = false;
        }
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session
        session(['selectedCluster' => $this->selectedCluster]);

        // Load cluster roles for the selected cluster
        $this->loadClusterRoles();
    }

    public function loadClusterRoles()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            try {
                $service = new \App\Services\CachedKubernetesService($kubeconfigPath);
                $response = $service->getClusterRoles();
            } catch (\Exception $e) {
                $service = new KubernetesService($kubeconfigPath);
                $response = $service->getClusterRoles();
            }

            if (isset($response['items'])) {
                $this->clusterRoles = $response['items'];
            } else {
                $this->clusterRoles = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load cluster roles: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function refreshData()
    {
        try {
            $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');

            if (!$this->selectedCluster) {
                $this->error = 'Please select a cluster first';
                return;
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new \App\Services\CachedKubernetesService($kubeconfigPath);

            $service->clearCache();
            $this->loadClusterRoles();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Cluster roles data refreshed successfully'
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
        return $this->clusterRoles;
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
                'field' => 'rules_count',
                'label' => 'Rules',
                'sortable' => true
            ],
            [
                'field' => 'api_groups',
                'label' => 'API Groups',
                'sortable' => false
            ],
            [
                'field' => 'resources',
                'label' => 'Resources',
                'sortable' => false
            ],
            [
                'field' => 'verbs',
                'label' => 'Verbs',
                'sortable' => false
            ],
            [
                'field' => 'aggregation_rule',
                'label' => 'Aggregation',
                'sortable' => false
            ],
            [
                'field' => 'age',
                'label' => 'Age',
                'sortable' => true
            ]
        ];
    }

    public function handleClusterSelected($clusterName)
    {
        $this->selectedCluster = $clusterName;
        $this->loadClusterRoles();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.access-control.cluster-role-list', [
                'filteredClusterRoles' => $this->filteredClusterRoles,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Cluster Roles page: ' . $e->getMessage());

            // Reset pagination to first page
            $this->currentPage = 1;

            // Return the view with an error message
            return view('livewire.kubernetes.access-control.cluster-role-list', [
                'filteredClusterRoles' => [],
                'error' => 'An error occurred while loading cluster roles. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
