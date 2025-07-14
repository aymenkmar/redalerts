<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use Carbon\Carbon;

class NodeDetails extends Component
{
    public $selectedCluster = null;
    public $nodeName = null;
    public $nodeDetails = null;
    public $podsOnNode = [];
    public $nodeEvents = [];
    public $showInstantly = false;
    public $loading = true;
    public $error = null;

    protected $listeners = ['nodeSelected', 'refreshNodeDetails'];

    public function mount($nodeName = null)
    {
        $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');
        if ($nodeName) {
            $this->nodeName = $nodeName;
            $this->loadNodeDetails();
        }
    }

    public function nodeSelected($nodeName)
    {
        $this->nodeName = $nodeName;
        $this->showInstantly = true;

        // Try to load from cache first for instant display
        $this->loadCachedDataFirst();

        // Then load fresh data in background
        $this->loadNodeDetails();
    }

    private function loadCachedDataFirst()
    {
        if (!$this->nodeName || !$this->selectedCluster) return;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            if (!file_exists($kubeconfigPath)) return;

            $service = new CachedKubernetesService($kubeconfigPath);

            // Try to get cached data without forcing refresh
            $cachedNodeDetails = $service->getNodeDetails($this->nodeName, false);
            $cachedPods = $service->getPodsOnNode($this->nodeName, false);
            $cachedEvents = $service->getNodeEvents($this->nodeName, false);

            // If we have cached data, show it immediately
            if ($cachedNodeDetails && isset($cachedNodeDetails['metadata'])) {
                $this->nodeDetails = $cachedNodeDetails;
                $this->podsOnNode = $cachedPods['items'] ?? [];
                $this->nodeEvents = $cachedEvents['items'] ?? [];
            }
        } catch (\Exception $e) {
            // If cache fails, we'll load fresh data anyway
            \Log::info('Cache load failed for node details: ' . $e->getMessage());
        }
    }

    public function refreshNodeDetails()
    {
        if ($this->nodeName) {
            $this->loadNodeDetails(true);
        }
    }

    public function loadNodeDetails($forceRefresh = false)
    {
        // Only show loading if we don't have cached data already
        if (!$this->nodeDetails) {
            $this->loading = true;
        }
        $this->error = null;

        try {
            if (!$this->selectedCluster) {
                throw new \Exception('No cluster selected');
            }

            if (!$this->nodeName) {
                throw new \Exception('No node selected');
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            if (!file_exists($kubeconfigPath)) {
                throw new \Exception('Kubeconfig file not found: ' . $kubeconfigPath);
            }

            $service = new CachedKubernetesService($kubeconfigPath);

            // Load fresh data (this will update cache)
            $freshNodeDetails = $service->getNodeDetails($this->nodeName, $forceRefresh);
            $freshPodsResponse = $service->getPodsOnNode($this->nodeName, $forceRefresh);
            $freshEventsResponse = $service->getNodeEvents($this->nodeName, $forceRefresh);

            // Update with fresh data
            $this->nodeDetails = $freshNodeDetails;
            $this->podsOnNode = $freshPodsResponse['items'] ?? [];
            $this->nodeEvents = $freshEventsResponse['items'] ?? [];

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function closeDetails()
    {
        $this->nodeName = null;
        $this->nodeDetails = null;
        $this->podsOnNode = [];
        $this->nodeEvents = [];

        // Dispatch to parent component
        $this->dispatch('nodeDetailsClose')->to('kubernetes.node-list');
    }

    // Helper methods for node information
    public function getNodeProperty($path, $default = 'N/A')
    {
        $keys = explode('.', $path);
        $value = $this->nodeDetails;

        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return $default;
            }
        }

        return $value ?: $default;
    }

    public function formatAge($timestamp)
    {
        if (!$timestamp) return 'N/A';
        
        try {
            $created = Carbon::parse($timestamp);
            $now = Carbon::now();
            
            $diff = $created->diff($now);
            
            if ($diff->y > 0) {
                return $diff->y . 'y' . $diff->d . 'd ' . $diff->h . 'h ' . $diff->i . 'm ago';
            } elseif ($diff->m > 0) {
                return $diff->m . 'm' . $diff->d . 'd ago';
            } elseif ($diff->d > 0) {
                return $diff->d . 'd' . $diff->h . 'h ago';
            } elseif ($diff->h > 0) {
                return $diff->h . 'h' . $diff->i . 'm ago';
            } elseif ($diff->i > 0) {
                return $diff->i . 'm' . $diff->s . 's ago';
            } else {
                return $diff->s . 's ago';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    public function formatDateTime($timestamp)
    {
        if (!$timestamp) return 'N/A';
        
        try {
            return Carbon::parse($timestamp)->format('Y-m-d H:i:s T');
        } catch (\Exception $e) {
            return 'Invalid date';
        }
    }

    public function getNodeConditions()
    {
        $conditions = $this->getNodeProperty('status.conditions', []);
        if (!is_array($conditions)) return [];
        
        return collect($conditions)->map(function ($condition) {
            return [
                'type' => $condition['type'] ?? 'Unknown',
                'status' => $condition['status'] ?? 'Unknown',
                'reason' => $condition['reason'] ?? '',
                'message' => $condition['message'] ?? '',
                'lastTransitionTime' => $condition['lastTransitionTime'] ?? '',
            ];
        })->toArray();
    }

    public function getNodeTaints()
    {
        $taints = $this->getNodeProperty('spec.taints', []);
        if (!is_array($taints)) return [];
        
        return collect($taints)->map(function ($taint) {
            return [
                'key' => $taint['key'] ?? '',
                'value' => $taint['value'] ?? '',
                'effect' => $taint['effect'] ?? '',
            ];
        })->toArray();
    }

    public function getNodeLabels()
    {
        $labels = $this->getNodeProperty('metadata.labels', []);
        if (!is_array($labels)) return [];
        
        return $labels;
    }

    public function getNodeAnnotations()
    {
        $annotations = $this->getNodeProperty('metadata.annotations', []);
        if (!is_array($annotations)) return [];
        
        return $annotations;
    }

    public function formatBytes($bytes)
    {
        if (!$bytes) return 'N/A';

        // Handle Kubernetes resource formats (e.g., "1Ki", "1Mi", "1Gi")
        if (is_string($bytes)) {
            if (preg_match('/^(\d+(?:\.\d+)?)([KMGT]i?)$/', $bytes, $matches)) {
                $value = floatval($matches[1]);
                $unit = $matches[2];

                switch ($unit) {
                    case 'Ki': return round($value / 1024, 2) . ' MiB';
                    case 'Mi': return $value . ' MiB';
                    case 'Gi': return $value . ' GiB';
                    case 'Ti': return $value . ' TiB';
                    case 'K': return round($value / 1000, 2) . ' MB';
                    case 'M': return $value . ' MB';
                    case 'G': return $value . ' GB';
                    case 'T': return $value . ' TB';
                }
            }

            // If it's just a number as string, convert to int
            if (is_numeric($bytes)) {
                $bytes = intval($bytes);
            } else {
                return $bytes; // Return as-is if we can't parse it
            }
        }

        if (!is_numeric($bytes)) return $bytes;

        $bytes = intval($bytes);
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function formatCpuCores($cpu)
    {
        if (!$cpu) return 'N/A';

        // Handle millicores (e.g., "100m" = 0.1 cores)
        if (str_ends_with($cpu, 'm')) {
            $millicores = intval(str_replace('m', '', $cpu));
            $cores = $millicores / 1000;
            return $cores . ' cores';
        }

        // Handle regular cores (could be integer or float)
        if (is_numeric($cpu)) {
            return $cpu . ' cores';
        }

        // Return as-is if we can't parse it
        return $cpu;
    }

    public function getPodStatus($pod)
    {
        return $pod['status']['phase'] ?? 'Unknown';
    }

    public function getPodStatusClass($pod)
    {
        $status = $this->getPodStatus($pod);
        switch ($status) {
            case 'Running': return 'bg-green-100 text-green-800';
            case 'Pending': return 'bg-yellow-100 text-yellow-800';
            case 'Failed': return 'bg-red-100 text-red-800';
            case 'Succeeded': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    public function getEventType($event)
    {
        return $event['type'] ?? 'Normal';
    }

    public function getEventTypeClass($event)
    {
        $type = $this->getEventType($event);
        return $type === 'Warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800';
    }

    public function render()
    {
        return view('livewire.kubernetes.node-details');
    }
}
