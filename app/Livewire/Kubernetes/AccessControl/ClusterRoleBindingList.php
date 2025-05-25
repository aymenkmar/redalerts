<?php

namespace App\Livewire\Kubernetes\AccessControl;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class ClusterRoleBindingList extends Component
{
    public $clusterRoleBindings = [];
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
            $this->loadClusterRoleBindings();
        }
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session
        session(['selectedCluster' => $this->selectedCluster]);

        // Load cluster role bindings for the selected cluster
        $this->loadClusterRoleBindings();
    }

    public function loadClusterRoleBindings()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            try {
                $service = new \App\Services\CachedKubernetesService($kubeconfigPath);
                $response = $service->getClusterRoleBindings();
            } catch (\Exception $e) {
                $service = new KubernetesService($kubeconfigPath);
                $response = $service->getClusterRoleBindings();
            }

            if (isset($response['items'])) {
                $this->clusterRoleBindings = $response['items'];
            } else {
                $this->clusterRoleBindings = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load cluster role bindings: ' . $e->getMessage();
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
            $this->loadClusterRoleBindings();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Cluster role bindings data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
        }
    }

    public function getFilteredClusterRoleBindingsProperty()
    {
        if (empty($this->clusterRoleBindings)) {
            return [];
        }

        $clusterRoleBindings = collect($this->clusterRoleBindings);

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $clusterRoleBindings = $clusterRoleBindings->filter(function ($clusterRoleBinding) use ($searchTerm) {
                $name = strtolower($clusterRoleBinding['metadata']['name'] ?? '');
                $roleRef = strtolower($clusterRoleBinding['roleRef']['name'] ?? '');

                return str_contains($name, $searchTerm) ||
                       str_contains($roleRef, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $clusterRoleBindings->count();

        // Reset current page if it's out of bounds
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $maxPage) {
            $this->currentPage = 1;
        }

        // Apply pagination
        $paginatedClusterRoleBindings = $clusterRoleBindings->forPage($this->currentPage, $this->perPage);

        return $paginatedClusterRoleBindings->values()->all();
    }

    public function formatBindings($clusterRoleBinding)
    {
        if (!isset($clusterRoleBinding['subjects']) || empty($clusterRoleBinding['subjects'])) {
            return 'No subjects';
        }

        return collect($clusterRoleBinding['subjects'])
            ->map(function ($subject) {
                $kind = $subject['kind'] ?? '';
                $name = $subject['name'] ?? '';
                $namespace = isset($subject['namespace']) ? "/{$subject['namespace']}" : '';

                return "{$kind}: {$name}{$namespace}";
            })
            ->implode(', ');
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

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function nextPage()
    {
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage < $maxPage) {
            $this->currentPage++;
        }
    }

    public function goToPage($page)
    {
        // Validate the page number to ensure it's within valid range
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        $page = max(1, min($maxPage, (int)$page));

        $this->currentPage = $page;
    }

    public function handleClusterSelected($clusterName)
    {
        $this->selectedCluster = $clusterName;
        $this->loadClusterRoleBindings();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.access-control.cluster-role-binding-list', [
                'filteredClusterRoleBindings' => $this->filteredClusterRoleBindings,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Cluster Role Bindings page: ' . $e->getMessage());

            // Reset pagination to first page
            $this->currentPage = 1;

            // Return the view with an error message
            return view('livewire.kubernetes.access-control.cluster-role-binding-list', [
                'filteredClusterRoleBindings' => [],
                'error' => 'An error occurred while loading cluster role bindings. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
