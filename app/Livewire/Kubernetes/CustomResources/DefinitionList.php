<?php

namespace App\Livewire\Kubernetes\CustomResources;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class DefinitionList extends Component
{
    public $definitions = [];
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
            $this->loadDefinitions();
        }
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session
        session(['selectedCluster' => $this->selectedCluster]);

        // Load custom resource definitions for the selected cluster
        $this->loadDefinitions();
    }

    public function loadDefinitions()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getCustomResourceDefinitions();

            if (isset($response['items'])) {
                $this->definitions = $response['items'];
            } else {
                $this->definitions = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load custom resource definitions: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function getFilteredDefinitionsProperty()
    {
        if (empty($this->definitions)) {
            return [];
        }

        $definitions = collect($this->definitions);

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $definitions = $definitions->filter(function ($definition) use ($searchTerm) {
                $name = strtolower($definition['metadata']['name'] ?? '');

                return str_contains($name, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $definitions->count();

        // Reset current page if it's out of bounds
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $maxPage) {
            $this->currentPage = 1;
        }

        // Apply pagination
        $paginatedDefinitions = $definitions->forPage($this->currentPage, $this->perPage);

        return $paginatedDefinitions->values()->all();
    }

    public function getStorageVersion($definition)
    {
        if (!isset($definition['spec']['versions']) || empty($definition['spec']['versions'])) {
            return 'N/A';
        }

        $storageVersion = collect($definition['spec']['versions'])
            ->first(function ($version) {
                return isset($version['storage']) && $version['storage'] === true;
            });

        return $storageVersion['name'] ?? 'N/A';
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
        $this->loadDefinitions();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.custom-resources.definition-list', [
                'filteredDefinitions' => $this->filteredDefinitions,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Custom Resource Definitions page: ' . $e->getMessage());

            // Reset pagination to first page
            $this->currentPage = 1;

            // Return the view with an error message
            return view('livewire.kubernetes.custom-resources.definition-list', [
                'filteredDefinitions' => [],
                'error' => 'An error occurred while loading custom resource definitions. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
