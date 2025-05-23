<?php

namespace App\Livewire\Kubernetes\Workloads;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

abstract class BaseWorkloadList extends Component
{
    public $resources = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;
    public $searchTerm = '';
    public $selectedNamespaces = ['all'];
    public $namespaces = [];
    public $showNamespaceFilter = false;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedNamespaces' => ['except' => ['all']]
    ];

    // Pagination properties
    public $perPage = 10;
    public $currentPage = 1;
    public $totalItems = 0;

    abstract protected function getResourceMethod(): string;

    // Reset pagination when search term or namespace selection changes
    public function updatedSearchTerm()
    {
        $this->currentPage = 1;
    }

    public function updatedSelectedNamespaces()
    {
        $this->currentPage = 1;
    }

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the selected cluster from session
        $this->selectedCluster = session('selectedCluster', null);

        if (!$this->selectedCluster) {
            return redirect()->route('dashboard-kubernetes')->with('error', 'Please select a cluster first');
        }

        $this->loadNamespaces();
        $this->loadResources();
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

    public function loadResources()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);

            // Get the method name from the child class
            $methodName = $this->getResourceMethod();

            // Call the method on the service
            $response = $service->$methodName();

            if (isset($response['items'])) {
                $this->resources = $response['items'];
            } else {
                $this->resources = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load resources: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function toggleNamespaceFilter()
    {
        $this->showNamespaceFilter = !$this->showNamespaceFilter;
    }

    public function toggleNamespace($namespace)
    {
        if ($namespace === 'all') {
            $this->selectedNamespaces = ['all'];
        } else {
            // Remove 'all' if it's in the array
            $this->selectedNamespaces = array_filter($this->selectedNamespaces, function ($ns) {
                return $ns !== 'all';
            });

            // Toggle the selected namespace
            if (in_array($namespace, $this->selectedNamespaces)) {
                $this->selectedNamespaces = array_filter($this->selectedNamespaces, function ($ns) use ($namespace) {
                    return $ns !== $namespace;
                });

                // If no namespaces are selected, select 'all'
                if (empty($this->selectedNamespaces)) {
                    $this->selectedNamespaces = ['all'];
                }
            } else {
                $this->selectedNamespaces[] = $namespace;
            }
        }
    }

    public function formatAge($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        $creationTime = Carbon::parse($timestamp);
        $now = Carbon::now();
        $diff = $creationTime->diff($now);

        if ($diff->y > 0) {
            return $diff->y . 'y' . ($diff->m > 0 ? ' ' . $diff->m . 'm' : '');
        } elseif ($diff->m > 0) {
            return $diff->m . 'm' . ($diff->d > 0 ? ' ' . $diff->d . 'd' : '');
        } elseif ($diff->d > 0) {
            return $diff->d . 'd' . ($diff->h > 0 ? ' ' . $diff->h . 'h' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . 'h' . ($diff->i > 0 ? ' ' . $diff->i . 'm' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . 'm' . ($diff->s > 0 ? ' ' . $diff->s . 's' : '');
        } else {
            return $diff->s . 's';
        }
    }

    public function getFilteredResourcesProperty()
    {
        if (empty($this->resources)) {
            return [];
        }

        $resources = collect($this->resources);

        // Filter by namespace
        if (!in_array('all', $this->selectedNamespaces)) {
            $resources = $resources->filter(function ($resource) {
                return in_array($resource['metadata']['namespace'] ?? 'default', $this->selectedNamespaces);
            });
        }

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $resources = $resources->filter(function ($resource) use ($searchTerm) {
                $name = strtolower($resource['metadata']['name'] ?? '');
                $namespace = strtolower($resource['metadata']['namespace'] ?? 'default');

                return str_contains($name, $searchTerm) || str_contains($namespace, $searchTerm);
            });
        }

        // Update total count for pagination
        $this->totalItems = $resources->count();

        // Apply pagination
        $paginatedResources = $resources->forPage($this->currentPage, $this->perPage);

        return $paginatedResources->values()->all();
    }

    // Pagination methods
    public function nextPage()
    {
        $maxPage = ceil($this->totalItems / $this->perPage);
        if ($this->currentPage < $maxPage) {
            $this->currentPage++;
        }
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function goToPage($page)
    {
        $this->currentPage = $page;
    }

}
