<?php

namespace App\Livewire\Kubernetes\Storage;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use App\Traits\HasKubernetesTable;

class PersistentVolumeClaimList extends Component
{
    use HasKubernetesTable;

    public $persistentVolumeClaims = [];
    public $namespaces = [];
    public $pods = [];
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

        if (!Auth::check()) {
            return redirect()->route('login');
        }

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

            try {
                $service = new CachedKubernetesService($kubeconfigPath);
                $pvcsResponse = $service->getPersistentVolumeClaims();
            } catch (\Exception $e) {
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $pvcsResponse = $service->getPersistentVolumeClaims();
            }

            if (isset($pvcsResponse['items'])) {
                $this->persistentVolumeClaims = $pvcsResponse['items'];
            } else {
                $this->persistentVolumeClaims = [];
                if (isset($pvcsResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $pvcsResponse['message']);
                }
            }

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

            // Fetch pods to determine which pods use which PVCs
            $podsResponse = $service->getPods();
            if (isset($podsResponse['items'])) {
                $this->pods = $podsResponse['items'];
            } else {
                $this->pods = [];
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load persistent volume claims: ' . $e->getMessage();
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
            $service = new CachedKubernetesService($kubeconfigPath);

            $service->clearCache();
            $this->loadData();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Persistent volume claims data refreshed successfully'
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
        return $this->persistentVolumeClaims;
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
                'field' => 'status',
                'label' => 'Status',
                'sortable' => true
            ],
            [
                'field' => 'volume',
                'label' => 'Volume',
                'sortable' => false
            ],
            [
                'field' => 'capacity',
                'label' => 'Capacity',
                'sortable' => true
            ],
            [
                'field' => 'access_modes',
                'label' => 'Access Modes',
                'sortable' => false
            ],
            [
                'field' => 'storage_class',
                'label' => 'Storage Class',
                'sortable' => true
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
        return view('livewire.kubernetes.storage.persistent-volume-claim-list')->layout('layouts.kubernetes');
    }
}
