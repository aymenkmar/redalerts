<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use Carbon\Carbon;

class NodeList extends Component
{
    public $nodes = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;

    protected $queryString = [];

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Try both session keys for compatibility
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

            // Try cached service first, fallback to regular service
            try {
                $service = new CachedKubernetesService($kubeconfigPath);
                $nodesResponse = $service->getNodes();
            } catch (\Exception $e) {
                // Fallback to regular service if cached service fails
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $nodesResponse = $service->getNodes();
            }

            if (isset($nodesResponse['items'])) {
                $this->nodes = $nodesResponse['items'];
            } else {
                $this->nodes = [];
                // If no items, check if there's an error in the response
                if (isset($nodesResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $nodesResponse['message']);
                }
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load nodes: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function refreshData()
    {
        try {
            // Refresh the selected cluster from session
            $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');

            if (!$this->selectedCluster) {
                $this->error = 'Please select a cluster first';
                return;
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new CachedKubernetesService($kubeconfigPath);

            // Force refresh cache
            $service->clearCache();
            $this->loadData();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Nodes data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
        }
    }

    // Keep for backward compatibility
    public function loadNodes()
    {
        $this->loadData();
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

    public function render()
    {
        return view('livewire.kubernetes.node-list')->layout('layouts.kubernetes');
    }
}
