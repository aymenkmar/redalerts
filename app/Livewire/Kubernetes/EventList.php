<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use App\Traits\HasKubernetesTable;

class EventList extends Component
{
    use HasKubernetesTable;

    public $events = [];
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
            $this->loadNamespaces();
            $this->loadEvents();
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

        // Load events for the selected cluster
        $this->loadNamespaces();
        $this->loadEvents();
    }

    public function loadNamespaces()
    {
        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getNamespaces();

            if (isset($response['items'])) {
                $this->namespaces = collect($response['items'])
                    ->map(function ($namespace) {
                        return $namespace['metadata']['name'];
                    })
                    ->toArray();
            } else {
                $this->namespaces = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load namespaces: ' . $e->getMessage();
        }
    }

    public function loadEvents()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            try {
                $service = new \App\Services\CachedKubernetesService($kubeconfigPath);
                $response = $service->getEvents();
            } catch (\Exception $e) {
                $service = new KubernetesService($kubeconfigPath);
                $response = $service->getEvents();
            }

            if (isset($response['items'])) {
                $this->events = $response['items'];
            } else {
                $this->events = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load events: ' . $e->getMessage();
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
            $this->loadNamespaces();
            $this->loadEvents();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Events data refreshed successfully'
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
        return $this->events;
    }

    public function getTableColumns()
    {
        return [
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
                'field' => 'type',
                'label' => 'Type',
                'sortable' => true
            ],
            [
                'field' => 'reason',
                'label' => 'Reason',
                'sortable' => true
            ],
            [
                'field' => 'object',
                'label' => 'Object',
                'sortable' => true
            ],
            [
                'field' => 'source',
                'label' => 'Source',
                'sortable' => false
            ],
            [
                'field' => 'message',
                'label' => 'Message',
                'sortable' => false
            ],
            [
                'field' => 'count',
                'label' => 'Count',
                'sortable' => true
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
        $this->loadNamespaces();
        $this->loadEvents();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.event-list', [
                'filteredEvents' => $this->filteredEvents,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Events page: ' . $e->getMessage());

            // Reset pagination to first page
            $this->currentPage = 1;

            // Return the view with an error message
            return view('livewire.kubernetes.event-list', [
                'filteredEvents' => [],
                'error' => 'An error occurred while loading events. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
