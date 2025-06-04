<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasKubernetesTable
{
    // Table properties
    public $searchTerm = '';
    public $selectedNamespaces = ['all'];
    public $showNamespaceFilter = false;
    public $sortField = '';
    public $sortDirection = 'asc';
    
    // Pagination properties
    public $perPage = 10;
    public $currentPage = 1;
    public $totalItems = 0;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedNamespaces' => ['except' => ['all']],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'asc'],
        'currentPage' => ['except' => 1],
        'perPage' => ['except' => 10]
    ];

    // Reset pagination when filters change
    public function updatedSearchTerm()
    {
        $this->currentPage = 1;
    }

    public function updatedSelectedNamespaces()
    {
        $this->currentPage = 1;
    }

    public function updatedPerPage()
    {
        $this->currentPage = 1;
    }

    // Namespace filter methods
    public function toggleNamespaceFilter()
    {
        $this->showNamespaceFilter = !$this->showNamespaceFilter;
    }

    public function toggleNamespace($namespace)
    {
        if ($namespace === 'all') {
            $this->selectedNamespaces = ['all'];
        } else {
            // Remove 'all' if it exists
            $this->selectedNamespaces = array_diff($this->selectedNamespaces, ['all']);
            
            if (in_array($namespace, $this->selectedNamespaces)) {
                $this->selectedNamespaces = array_diff($this->selectedNamespaces, [$namespace]);
            } else {
                $this->selectedNamespaces[] = $namespace;
            }
            
            // If no namespaces selected, select all
            if (empty($this->selectedNamespaces)) {
                $this->selectedNamespaces = ['all'];
            }
        }
        
        $this->currentPage = 1;
    }

    public function selectAllNamespaces()
    {
        if (in_array('all', $this->selectedNamespaces)) {
            $this->selectedNamespaces = [];
        } else {
            $this->selectedNamespaces = ['all'];
        }
        $this->currentPage = 1;
    }

    // Sorting methods
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->currentPage = 1;
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

    // Helper methods
    protected function formatAge($timestamp)
    {
        if (!$timestamp) return 'Unknown';
        
        try {
            $created = Carbon::parse($timestamp);
            $now = Carbon::now();
            
            $diff = $created->diff($now);
            
            if ($diff->y > 0) {
                return $diff->y . 'y' . $diff->m . 'd';
            } elseif ($diff->m > 0) {
                return $diff->m . 'm' . $diff->d . 'd';
            } elseif ($diff->d > 0) {
                return $diff->d . 'd' . $diff->h . 'h';
            } elseif ($diff->h > 0) {
                return $diff->h . 'h' . $diff->i . 'm';
            } elseif ($diff->i > 0) {
                return $diff->i . 'm' . $diff->s . 's';
            } else {
                return $diff->s . 's';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    protected function getStatusBadgeClass($status)
    {
        $status = strtolower($status);
        
        switch ($status) {
            case 'running':
            case 'ready':
            case 'active':
            case 'bound':
                return 'bg-green-100 text-green-800';
            case 'pending':
            case 'waiting':
                return 'bg-yellow-100 text-yellow-800';
            case 'failed':
            case 'error':
            case 'crashloopbackoff':
                return 'bg-red-100 text-red-800';
            case 'succeeded':
            case 'completed':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    // Abstract methods that child classes should implement
    abstract protected function getTableData();
    abstract protected function getTableColumns();
}
