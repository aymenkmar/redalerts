<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class NamespaceList extends Component
{
    public $namespaces = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;
    public $searchTerm = '';

    // Pagination properties
    public $perPage = 10;
    public $currentPage = 1;
    public $totalItems = 0;

    protected $listeners = ['clusterSelected' => 'handleClusterSelected'];

    public function mount()
    {
        // Get the selected cluster from session
        $this->selectedCluster = session('selectedCluster');

        if ($this->selectedCluster) {
            $this->loadNamespaces();
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

        // Load namespaces for the selected cluster
        $this->loadNamespaces();
    }

    public function loadNamespaces()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getNamespaces();

            if (isset($response['items'])) {
                $this->namespaces = $response['items'];
            } else {
                $this->namespaces = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load namespaces: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function getFilteredNamespacesProperty()
    {
        if (empty($this->namespaces)) {
            return [];
        }

        $namespaces = collect($this->namespaces);

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $namespaces = $namespaces->filter(function ($namespace) use ($searchTerm) {
                $name = strtolower($namespace['metadata']['name'] ?? '');
                $labels = $namespace['metadata']['labels'] ?? [];
                $labelsString = collect($labels)->map(function ($value, $key) {
                    return strtolower("$key: $value");
                })->implode(' ');

                return str_contains($name, $searchTerm) || str_contains($labelsString, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $namespaces->count();

        // Apply pagination
        $paginatedNamespaces = $namespaces->forPage($this->currentPage, $this->perPage);

        return $paginatedNamespaces->values()->all();
    }

    public function formatAge($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        $creationTime = Carbon::parse($timestamp);
        $now = Carbon::now();
        $diffInDays = $creationTime->diffInDays($now);

        if ($diffInDays > 0) {
            return $diffInDays . 'd';
        }

        $diffInHours = $creationTime->diffInHours($now);
        if ($diffInHours > 0) {
            return $diffInHours . 'h';
        }

        $diffInMinutes = $creationTime->diffInMinutes($now);
        if ($diffInMinutes > 0) {
            return $diffInMinutes . 'm';
        }

        return $creationTime->diffInSeconds($now) . 's';
    }

    public function formatLabels($labels)
    {
        if (!$labels || empty($labels)) {
            return 'No labels';
        }

        return collect($labels)
            ->map(function ($value, $key) {
                return "$key: $value";
            })
            ->implode(', ');
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function nextPage()
    {
        $maxPage = ceil($this->totalItems / $this->perPage);
        if ($this->currentPage < $maxPage) {
            $this->currentPage++;
        }
    }

    public function goToPage($page)
    {
        $this->currentPage = $page;
    }

    public function handleClusterSelected($clusterName)
    {
        $this->selectedCluster = $clusterName;
        $this->loadNamespaces();
    }

    public function render()
    {
        return view('livewire.kubernetes.namespace-list', [
            'filteredNamespaces' => $this->filteredNamespaces,
        ])->layout('layouts.kubernetes');
    }
}
