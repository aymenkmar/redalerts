<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use App\Models\Cluster;

class KubernetesDashboard extends Component
{
    public $clusters = [];
    public $selectedCluster = null;
    public $showUploadForm = false;
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
    public $loading = true;
    public $error = null;

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the selected cluster from session or localStorage
        $this->selectedCluster = session('selectedCluster', null);

        // Get the list of clusters
        $this->loadClusters();

        // Load metrics if a cluster is selected
        if ($this->selectedCluster) {
            $this->loadClusterMetrics();
        } else {
            $this->loading = false;
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

    public function loadClusterMetrics()
    {
        $this->loading = true;
        $this->error = null;

        try {
            if (!$this->selectedCluster) {
                $this->loading = false;
                return;
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);

            // Get nodes
            $nodesResponse = $service->getNodes();
            if (isset($nodesResponse['items'])) {
                $nodes = $nodesResponse['items'];
                $this->clusterMetrics['nodes']['total'] = count($nodes);
                $this->clusterMetrics['nodes']['ready'] = collect($nodes)->filter(function ($node) {
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

                $this->clusterMetrics['cpu']['total'] = $totalCpu;
                $this->clusterMetrics['memory']['total'] = round($totalMemory);
            }

            // Get pods
            $podsResponse = $service->getPods();
            if (isset($podsResponse['items'])) {
                $pods = $podsResponse['items'];
                $this->clusterMetrics['pods']['total'] = count($pods);
                $this->clusterMetrics['pods']['running'] = collect($pods)->filter(function ($pod) {
                    return $pod['status']['phase'] === 'Running';
                })->count();

                // Estimate used CPU and memory (simplified)
                $this->clusterMetrics['cpu']['used'] = round($this->clusterMetrics['cpu']['total'] * 0.4, 1); // Simplified estimate
                $this->clusterMetrics['memory']['used'] = round($this->clusterMetrics['memory']['total'] * 0.45, 1); // Simplified estimate
            }

            // Get deployments
            $deploymentsResponse = $service->getDeployments();
            if (isset($deploymentsResponse['items'])) {
                $this->clusterMetrics['deployments'] = count($deploymentsResponse['items']);
            }

            // Get daemonsets
            $daemonSetsResponse = $service->getDaemonSets();
            if (isset($daemonSetsResponse['items'])) {
                $this->clusterMetrics['daemonSets'] = count($daemonSetsResponse['items']);
            }

            // Get statefulsets
            $statefulSetsResponse = $service->getStatefulSets();
            if (isset($statefulSetsResponse['items'])) {
                $this->clusterMetrics['statefulSets'] = count($statefulSetsResponse['items']);
            }

            // Get cronjobs
            $cronJobsResponse = $service->getCronJobs();
            if (isset($cronJobsResponse['items'])) {
                $this->clusterMetrics['cronJobs'] = count($cronJobsResponse['items']);
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load cluster metrics: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function selectCluster($clusterName)
    {
        $this->selectedCluster = $clusterName;
        session(['selectedCluster' => $clusterName]);
        $this->loadClusterMetrics();
    }

    public function toggleUploadForm()
    {
        $this->showUploadForm = !$this->showUploadForm;
    }

    public function render()
    {
        return view('livewire.kubernetes-dashboard')
            ->layout('layouts.kubernetes');
    }
}
