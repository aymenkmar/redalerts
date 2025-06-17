<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use App\Models\Cluster;

class KubernetesDashboard extends Component
{
    public $clusters = [];
    public $selectedClusters = []; // Array of selected clusters
    public $activeClusterTab = null; // Currently active cluster tab
    public $showUploadForm = false;
    public $multiClusterMetrics = []; // Metrics for all selected clusters
    public $loading = true;
    public $error = null;

    // Legacy support for single cluster (for backward compatibility)
    public $selectedCluster = null;
    public $clusterMetrics = [
        'nodes' => ['total' => 0, 'ready' => 0],
        'pods' => ['total' => 0, 'running' => 0],
        'memory' => ['used' => 0, 'total' => 0],
        'cpu' => ['used' => 0, 'total' => 0],
        'deployments' => 0,
        'daemonSets' => 0,
        'statefulSets' => 0,
        'cronJobs' => 0
    ];

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the selected clusters from session
        $this->selectedClusters = session('selectedClusters', []);
        $this->activeClusterTab = session('activeClusterTab', null);

        // Legacy support: if old selectedCluster exists, migrate to new format
        $legacyCluster = session('selectedCluster', null);
        if ($legacyCluster && empty($this->selectedClusters)) {
            $this->selectedClusters = [$legacyCluster];
            $this->activeClusterTab = $legacyCluster;
            session(['selectedClusters' => $this->selectedClusters]);
            session(['activeClusterTab' => $this->activeClusterTab]);
            session()->forget('selectedCluster');
        }

        // Handle case when we want to reset to cluster selection (selectedCluster is null but we have clusters)
        if (!$legacyCluster && !$this->activeClusterTab && !empty($this->selectedClusters)) {
            // This means we're in "add new cluster" mode - keep existing clusters but show selection interface
            // Don't set any active cluster tab
        }

        // Get the list of clusters
        $this->loadClusters();

        // Load metrics for all selected clusters
        if (!empty($this->selectedClusters) && $this->activeClusterTab) {
            $this->loadAllClusterMetrics();
        } else {
            $this->loading = false;
        }

        // Set legacy selectedCluster for backward compatibility
        $this->selectedCluster = $this->activeClusterTab;
        if ($this->activeClusterTab && isset($this->multiClusterMetrics[$this->activeClusterTab])) {
            $this->clusterMetrics = $this->multiClusterMetrics[$this->activeClusterTab];
        }
    }

    public function loadClusters()
    {
        try {
            // Get the kubeconfig path from environment
            $path = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

            if (!is_dir($path)) {
                return;
            }

            $files = scandir($path);
            $clusterFiles = [];

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $fullPath = $path . '/' . $file;

                if (is_file($fullPath)) {
                    $clusterFiles[] = $file;
                }
            }

            // Get cluster information from the database
            $clusterData = [];
            foreach ($clusterFiles as $clusterName) {
                $cluster = Cluster::where('name', $clusterName)->first();

                if ($cluster) {
                    $clusterData[] = [
                        'name' => $clusterName,
                        'upload_time' => $cluster->upload_time
                    ];
                } else {
                    // If not in database yet, add it with current time
                    $cluster = Cluster::create([
                        'name' => $clusterName,
                        'upload_time' => now()
                    ]);

                    $clusterData[] = [
                        'name' => $clusterName,
                        'upload_time' => $cluster->upload_time
                    ];
                }
            }

            $this->clusters = $clusterData;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load clusters: ' . $e->getMessage());
        }
    }

    public function loadClusterMetrics($clusterName = null)
    {
        $this->loading = true;
        $this->error = null;

        try {
            $targetCluster = $clusterName ?? $this->selectedCluster ?? $this->activeClusterTab;
            if (!$targetCluster) {
                $this->loading = false;
                return;
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $targetCluster;
            $service = new KubernetesService($kubeconfigPath);

            // Initialize metrics for this cluster
            $metrics = $this->getDefaultMetrics();

            // Get nodes
            $nodesResponse = $service->getNodes();
            if (isset($nodesResponse['items'])) {
                $nodes = $nodesResponse['items'];
                $metrics['nodes']['total'] = count($nodes);
                $metrics['nodes']['ready'] = collect($nodes)->filter(function ($node) {
                    $readyCondition = collect($node['status']['conditions'] ?? [])
                        ->firstWhere('type', 'Ready');
                    return $readyCondition && $readyCondition['status'] === 'True';
                })->count();

                // Calculate total CPU and memory
                $totalCpu = 0;
                $totalMemory = 0;
                foreach ($nodes as $node) {
                    if (isset($node['status']['capacity'])) {
                        $cpuStr = $node['status']['capacity']['cpu'] ?? '0';
                        $memoryStr = $node['status']['capacity']['memory'] ?? '0Ki';

                        // Convert CPU string to number
                        $totalCpu += intval($cpuStr);

                        // Convert memory string to GB
                        $memoryValue = intval(preg_replace('/[^0-9]/', '', $memoryStr));
                        $memoryUnit = preg_replace('/[0-9]/', '', $memoryStr);

                        switch ($memoryUnit) {
                            case 'Ki':
                                $memoryGB = $memoryValue / (1024 * 1024);
                                break;
                            case 'Mi':
                                $memoryGB = $memoryValue / 1024;
                                break;
                            case 'Gi':
                                $memoryGB = $memoryValue;
                                break;
                            default:
                                $memoryGB = $memoryValue / (1024 * 1024 * 1024);
                        }

                        $totalMemory += $memoryGB;
                    }
                }

                $metrics['cpu']['total'] = $totalCpu;
                $metrics['memory']['total'] = round($totalMemory);
            }

            // Get pods
            $podsResponse = $service->getPods();
            if (isset($podsResponse['items'])) {
                $pods = $podsResponse['items'];
                $metrics['pods']['total'] = count($pods);
                $metrics['pods']['running'] = collect($pods)->filter(function ($pod) {
                    return $pod['status']['phase'] === 'Running';
                })->count();

                // Estimate used CPU and memory (simplified)
                $metrics['cpu']['used'] = round($metrics['cpu']['total'] * 0.4, 1); // Simplified estimate
                $metrics['memory']['used'] = round($metrics['memory']['total'] * 0.45, 1); // Simplified estimate
            }

            // Get deployments
            $deploymentsResponse = $service->getDeployments();
            if (isset($deploymentsResponse['items'])) {
                $metrics['deployments'] = count($deploymentsResponse['items']);
            }

            // Get daemonsets
            $daemonSetsResponse = $service->getDaemonSets();
            if (isset($daemonSetsResponse['items'])) {
                $metrics['daemonSets'] = count($daemonSetsResponse['items']);
            }

            // Get statefulsets
            $statefulSetsResponse = $service->getStatefulSets();
            if (isset($statefulSetsResponse['items'])) {
                $metrics['statefulSets'] = count($statefulSetsResponse['items']);
            }

            // Get cronjobs
            $cronJobsResponse = $service->getCronJobs();
            if (isset($cronJobsResponse['items'])) {
                $metrics['cronJobs'] = count($cronJobsResponse['items']);
            }

            // Store metrics for this cluster
            $this->multiClusterMetrics[$targetCluster] = $metrics;

            // Update legacy properties if this is the active cluster
            if ($targetCluster === $this->activeClusterTab) {
                $this->clusterMetrics = $metrics;
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load cluster metrics: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function selectCluster($clusterName)
    {
        // Add cluster to selected clusters if not already present
        if (!in_array($clusterName, $this->selectedClusters)) {
            $this->selectedClusters[] = $clusterName;
        }

        // Set as active cluster tab
        $this->activeClusterTab = $clusterName;

        // Update session
        session(['selectedClusters' => $this->selectedClusters]);
        session(['activeClusterTab' => $this->activeClusterTab]);
        session(['selectedCluster' => $clusterName]); // Update legacy session

        // Load metrics for this cluster if not already loaded
        if (!isset($this->multiClusterMetrics[$clusterName])) {
            $this->loadClusterMetrics($clusterName);
        }

        // Update legacy properties for backward compatibility
        $this->selectedCluster = $clusterName;
        $this->clusterMetrics = $this->multiClusterMetrics[$clusterName] ?? $this->getDefaultMetrics();

        // Dispatch event to refresh the navbar
        $this->dispatch('clusterTabsUpdated');
    }

    public function switchToClusterTab($clusterName)
    {
        if (in_array($clusterName, $this->selectedClusters)) {
            $this->activeClusterTab = $clusterName;
            session(['activeClusterTab' => $this->activeClusterTab]);
            session(['selectedCluster' => $clusterName]); // Update legacy session

            // Update legacy properties
            $this->selectedCluster = $clusterName;
            $this->clusterMetrics = $this->multiClusterMetrics[$clusterName] ?? $this->getDefaultMetrics();

            // Dispatch event to refresh the navbar
            $this->dispatch('clusterTabsUpdated');
        }
    }

    public function closeClusterTab($clusterName)
    {
        // Remove cluster from selected clusters
        $this->selectedClusters = array_values(array_filter($this->selectedClusters, function($cluster) use ($clusterName) {
            return $cluster !== $clusterName;
        }));

        // Remove metrics for this cluster
        unset($this->multiClusterMetrics[$clusterName]);

        // If this was the active tab, switch to another tab or clear
        if ($this->activeClusterTab === $clusterName) {
            $this->activeClusterTab = !empty($this->selectedClusters) ? $this->selectedClusters[0] : null;
            $this->selectedCluster = $this->activeClusterTab;
            $this->clusterMetrics = $this->activeClusterTab ?
                ($this->multiClusterMetrics[$this->activeClusterTab] ?? $this->getDefaultMetrics()) :
                $this->getDefaultMetrics();
        }

        // Update session
        session(['selectedClusters' => $this->selectedClusters]);
        session(['activeClusterTab' => $this->activeClusterTab]);
        session(['selectedCluster' => $this->activeClusterTab]); // Update legacy session

        // Dispatch event to refresh the navbar
        $this->dispatch('clusterTabsUpdated');
    }

    #[On('addNewCluster')]
    public function addNewCluster()
    {
        // Reset to cluster selection mode
        $this->activeClusterTab = null;
        $this->selectedCluster = null;
        $this->clusterMetrics = $this->getDefaultMetrics();
        session(['activeClusterTab' => null]);
        session(['selectedCluster' => null]); // Update legacy session

        // Dispatch event to refresh the navbar
        $this->dispatch('clusterTabsUpdated');
    }

    public function toggleUploadForm()
    {
        $this->showUploadForm = !$this->showUploadForm;
    }

    public function loadAllClusterMetrics()
    {
        foreach ($this->selectedClusters as $clusterName) {
            if (!isset($this->multiClusterMetrics[$clusterName])) {
                $this->loadClusterMetrics($clusterName);
            }
        }
    }

    private function getDefaultMetrics()
    {
        return [
            'nodes' => ['total' => 0, 'ready' => 0],
            'pods' => ['total' => 0, 'running' => 0],
            'memory' => ['used' => 0, 'total' => 0],
            'cpu' => ['used' => 0, 'total' => 0],
            'deployments' => 0,
            'daemonSets' => 0,
            'statefulSets' => 0,
            'cronJobs' => 0
        ];
    }

    public function render()
    {
        return view('livewire.kubernetes-dashboard')
            ->layout('layouts.kubernetes');
    }
}
