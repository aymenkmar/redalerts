<?php

namespace App\Livewire\Kubernetes\AccessControl;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class RoleBindingList extends Component
{
    public $roleBindings = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;
    public $searchTerm = '';
    public $selectedNamespaces = ['all'];
    public $namespaces = [];
    public $showNamespaceFilter = false;
    
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
            $this->loadRoleBindings();
        }
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session
        session(['selectedCluster' => $this->selectedCluster]);
        
        // Load role bindings for the selected cluster
        $this->loadNamespaces();
        $this->loadRoleBindings();
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

    public function loadRoleBindings()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getRoleBindings();

            if (isset($response['items'])) {
                $this->roleBindings = $response['items'];
            } else {
                $this->roleBindings = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load role bindings: ' . $e->getMessage();
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

    public function getFilteredRoleBindingsProperty()
    {
        if (empty($this->roleBindings)) {
            return [];
        }

        $roleBindings = collect($this->roleBindings);

        // Filter by namespace
        if (!in_array('all', $this->selectedNamespaces)) {
            $roleBindings = $roleBindings->filter(function ($roleBinding) {
                return in_array($roleBinding['metadata']['namespace'] ?? 'default', $this->selectedNamespaces);
            });
        }

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $roleBindings = $roleBindings->filter(function ($roleBinding) use ($searchTerm) {
                $name = strtolower($roleBinding['metadata']['name'] ?? '');
                $namespace = strtolower($roleBinding['metadata']['namespace'] ?? 'default');
                $roleRef = strtolower($roleBinding['roleRef']['name'] ?? '');
                
                return str_contains($name, $searchTerm) || 
                       str_contains($namespace, $searchTerm) ||
                       str_contains($roleRef, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $roleBindings->count();
        
        // Reset current page if it's out of bounds
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $maxPage) {
            $this->currentPage = 1;
        }

        // Apply pagination
        $paginatedRoleBindings = $roleBindings->forPage($this->currentPage, $this->perPage);

        return $paginatedRoleBindings->values()->all();
    }

    public function formatBindings($roleBinding)
    {
        if (!isset($roleBinding['subjects']) || empty($roleBinding['subjects'])) {
            return 'No subjects';
        }

        return collect($roleBinding['subjects'])
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
        $this->loadNamespaces();
        $this->loadRoleBindings();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.access-control.role-binding-list', [
                'filteredRoleBindings' => $this->filteredRoleBindings,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Role Bindings page: ' . $e->getMessage());
            
            // Reset pagination to first page
            $this->currentPage = 1;
            
            // Return the view with an error message
            return view('livewire.kubernetes.access-control.role-binding-list', [
                'filteredRoleBindings' => [],
                'error' => 'An error occurred while loading role bindings. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
