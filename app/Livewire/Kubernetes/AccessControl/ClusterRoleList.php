<?php

namespace App\Livewire\Kubernetes\AccessControl;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class ClusterRoleList extends Component
{
    public $clusterRoles = [];
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
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getClusterRoles();

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

    public function getFilteredClusterRolesProperty()
    {
        if (empty($this->clusterRoles)) {
            return [];
        }

        $clusterRoles = collect($this->clusterRoles);

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $clusterRoles = $clusterRoles->filter(function ($clusterRole) use ($searchTerm) {
                $name = strtolower($clusterRole['metadata']['name'] ?? '');

                return str_contains($name, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $clusterRoles->count();

        // Reset current page if it's out of bounds
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $maxPage) {
            $this->currentPage = 1;
        }

        // Apply pagination
        $paginatedClusterRoles = $clusterRoles->forPage($this->currentPage, $this->perPage);

        return $paginatedClusterRoles->values()->all();
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
