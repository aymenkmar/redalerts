<?php

namespace App\Livewire\Kubernetes\CustomResources\CertManager;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class CertificateRequestList extends Component
{
    public $certificateRequests = [];
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
            $this->loadCertificateRequests();
        }
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session
        session(['selectedCluster' => $this->selectedCluster]);
        
        // Load certificate requests for the selected cluster
        $this->loadNamespaces();
        $this->loadCertificateRequests();
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

    public function loadCertificateRequests()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getCertificateRequests();

            if (isset($response['items'])) {
                $this->certificateRequests = $response['items'];
            } else {
                $this->certificateRequests = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load certificate requests: ' . $e->getMessage();
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

    public function getFilteredCertificateRequestsProperty()
    {
        if (empty($this->certificateRequests)) {
            return [];
        }

        $certificateRequests = collect($this->certificateRequests);

        // Filter by namespace
        if (!in_array('all', $this->selectedNamespaces)) {
            $certificateRequests = $certificateRequests->filter(function ($certificateRequest) {
                return in_array($certificateRequest['metadata']['namespace'] ?? 'default', $this->selectedNamespaces);
            });
        }

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $certificateRequests = $certificateRequests->filter(function ($certificateRequest) use ($searchTerm) {
                $name = strtolower($certificateRequest['metadata']['name'] ?? '');
                $namespace = strtolower($certificateRequest['metadata']['namespace'] ?? 'default');
                $issuer = strtolower($certificateRequest['spec']['issuerRef']['name'] ?? '');
                
                return str_contains($name, $searchTerm) || 
                       str_contains($namespace, $searchTerm) ||
                       str_contains($issuer, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $certificateRequests->count();
        
        // Reset current page if it's out of bounds
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $maxPage) {
            $this->currentPage = 1;
        }

        // Apply pagination
        $paginatedCertificateRequests = $certificateRequests->forPage($this->currentPage, $this->perPage);

        return $paginatedCertificateRequests->values()->all();
    }

    public function isApproved($certificateRequest)
    {
        if (!isset($certificateRequest['status']['conditions'])) {
            return false;
        }

        foreach ($certificateRequest['status']['conditions'] as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'Approved' && 
                isset($condition['status']) && $condition['status'] === 'True') {
                return true;
            }
        }

        return false;
    }

    public function isDenied($certificateRequest)
    {
        if (!isset($certificateRequest['status']['conditions'])) {
            return false;
        }

        foreach ($certificateRequest['status']['conditions'] as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'Denied' && 
                isset($condition['status']) && $condition['status'] === 'True') {
                return true;
            }
        }

        return false;
    }

    public function isReady($certificateRequest)
    {
        if (!isset($certificateRequest['status']['conditions'])) {
            return false;
        }

        foreach ($certificateRequest['status']['conditions'] as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'Ready' && 
                isset($condition['status']) && $condition['status'] === 'True') {
                return true;
            }
        }

        return false;
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
        $this->loadCertificateRequests();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.custom-resources.cert-manager.certificate-request-list', [
                'filteredCertificateRequests' => $this->filteredCertificateRequests,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Certificate Requests page: ' . $e->getMessage());
            
            // Reset pagination to first page
            $this->currentPage = 1;
            
            // Return the view with an error message
            return view('livewire.kubernetes.custom-resources.cert-manager.certificate-request-list', [
                'filteredCertificateRequests' => [],
                'error' => 'An error occurred while loading certificate requests. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
