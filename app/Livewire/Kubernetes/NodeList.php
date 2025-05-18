<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class NodeList extends Component
{
    public $nodes = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;
    public $searchTerm = '';

    // Pagination properties
    public $perPage = 10;
    public $currentPage = 1;
    public $totalItems = 0;

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

        $this->loadNodes();
    }

    public function loadNodes()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getNodes();

            if (isset($response['items'])) {
                $this->nodes = $response['items'];
            } else {
                $this->nodes = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load nodes: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function formatAge($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        $creationTime = Carbon::parse($timestamp);
        $now = Carbon::now();
        $diffInDays = $creationTime->diffInDays($now);

        // For Lens IDE style formatting
        if ($diffInDays >= 1) {
            return $diffInDays . 'd';
        }

        $diffInHours = $creationTime->diffInHours($now);
        if ($diffInHours >= 1) {
            return $diffInHours . 'h';
        }

        $diffInMinutes = $creationTime->diffInMinutes($now);
        if ($diffInMinutes >= 1) {
            return $diffInMinutes . 'm';
        }

        return $creationTime->diffInSeconds($now) . 's';
    }

    public function getNodeStatus($node)
    {
        $readyCondition = collect($node['status']['conditions'] ?? [])
            ->firstWhere('type', 'Ready');

        return $readyCondition && $readyCondition['status'] === 'True' ? 'Ready' : 'Not Ready';
    }

    public function getNodeRoles($node)
    {
        $roles = collect($node['metadata']['labels'] ?? [])
            ->filter(function ($value, $key) {
                return str_starts_with($key, 'node-role.kubernetes.io/');
            })
            ->keys()
            ->map(function ($key) {
                return str_replace('node-role.kubernetes.io/', '', $key);
            })
            ->join(', ');

        return $roles ?: 'worker';
    }

    public function getFilteredNodesProperty()
    {
        if (empty($this->nodes)) {
            return [];
        }

        $nodes = collect($this->nodes);

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $nodes = $nodes->filter(function ($node) use ($searchTerm) {
                $name = strtolower($node['metadata']['name'] ?? '');
                $roles = strtolower($this->getNodeRoles($node));
                $version = strtolower($node['status']['nodeInfo']['kubeletVersion'] ?? '');

                return str_contains($name, $searchTerm) ||
                       str_contains($roles, $searchTerm) ||
                       str_contains($version, $searchTerm);
            });
        }

        // Update total count for pagination
        $this->totalItems = $nodes->count();

        // Apply pagination
        $paginatedNodes = $nodes->forPage($this->currentPage, $this->perPage);

        return $paginatedNodes->values()->all();
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

    public function render()
    {
        return view('livewire.kubernetes.node-list', [
            'nodes' => $this->filteredNodes,
        ])->layout('layouts.kubernetes');
    }
}
