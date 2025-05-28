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

        // Calculate total difference in various units
        $diffInSeconds = $creationTime->diffInSeconds($now);
        $diffInMinutes = $creationTime->diffInMinutes($now);
        $diffInHours = $creationTime->diffInHours($now);
        $diffInDays = $creationTime->diffInDays($now);

        // Calculate years and remaining days (Lens IDE format: 2y83d)
        $years = intval($diffInDays / 365);
        $remainingDays = $diffInDays % 365;

        if ($years > 0) {
            if ($remainingDays > 0) {
                return $years . 'y' . $remainingDays . 'd';
            } else {
                return $years . 'y';
            }
        }

        // For less than a year, show days
        if ($diffInDays >= 1) {
            return $diffInDays . 'd';
        }

        // For less than a day, show hours
        if ($diffInHours >= 1) {
            return $diffInHours . 'h';
        }

        // For less than an hour, show minutes
        if ($diffInMinutes >= 1) {
            return $diffInMinutes . 'm';
        }

        // For less than a minute, show seconds
        return $diffInSeconds . 's';
    }

    public function getNodeConditions($node)
    {
        $conditions = collect($node['status']['conditions'] ?? []);
        $readyCondition = $conditions->firstWhere('type', 'Ready');

        if ($readyCondition && $readyCondition['status'] === 'True') {
            return 'Ready';
        }

        // Check for problematic conditions
        $problemConditions = $conditions->filter(function ($condition) {
            return $condition['status'] === 'True' &&
                   in_array($condition['type'], ['MemoryPressure', 'DiskPressure', 'PIDPressure', 'NetworkUnavailable']);
        });

        if ($problemConditions->isNotEmpty()) {
            return $problemConditions->pluck('type')->join(', ');
        }

        return $readyCondition ? 'Not Ready' : 'Unknown';
    }

    public function getNodeWarnings($node)
    {
        $conditions = collect($node['status']['conditions'] ?? []);
        $warnings = [];

        // Check for warning conditions
        $warningConditions = $conditions->filter(function ($condition) {
            return $condition['status'] === 'True' &&
                   in_array($condition['type'], ['MemoryPressure', 'DiskPressure', 'PIDPressure']);
        });

        if ($warningConditions->isNotEmpty()) {
            $warnings = array_merge($warnings, $warningConditions->pluck('type')->toArray());
        }

        // Check for unschedulable nodes
        if (isset($node['spec']['unschedulable']) && $node['spec']['unschedulable']) {
            $warnings[] = 'Unschedulable';
        }

        return count($warnings) > 0 ? implode(', ', $warnings) : '-';
    }

    public function getNodeTaints($node)
    {
        $taints = $node['spec']['taints'] ?? [];
        return count($taints);
    }

    public function getNodeRoles($node)
    {
        $labels = $node['metadata']['labels'] ?? [];
        $roles = [];

        // Check for standard node-role.kubernetes.io/ labels
        foreach ($labels as $key => $value) {
            if (str_starts_with($key, 'node-role.kubernetes.io/')) {
                $role = str_replace('node-role.kubernetes.io/', '', $key);
                $roles[] = $role;
            }
        }

        // Check for legacy master labels (older Kubernetes versions)
        if (isset($labels['kubernetes.io/role']) && $labels['kubernetes.io/role'] === 'master') {
            $roles[] = 'master';
        }

        // Check for control-plane labels (newer Kubernetes versions)
        if (isset($labels['node-role.kubernetes.io/control-plane'])) {
            $roles[] = 'control-plane';
        }

        // Check for master labels (some distributions)
        if (isset($labels['node-role.kubernetes.io/master'])) {
            $roles[] = 'master';
        }

        // Remove duplicates and join
        $roles = array_unique($roles);

        return count($roles) > 0 ? implode(', ', $roles) : 'worker';
    }

    public function render()
    {
        return view('livewire.kubernetes.node-list')->layout('layouts.kubernetes');
    }
}
