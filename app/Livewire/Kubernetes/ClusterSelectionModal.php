<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;

class ClusterSelectionModal extends Component
{
    use WithFileUploads;

    public $clusters = [];
    public $selectedCluster = null;
    public $showUploadForm = false;
    public $kubeconfig;
    public $clusterName = '';
    public $loading = false;
    public $error = null;
    public $success = null;

    public function mount()
    {
        $this->loadClusters();
        $this->selectedCluster = session('selectedCluster');
    }

    public function loadClusters()
    {
        try {
            $path = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

            if (!is_dir($path)) {
                $this->clusters = [];
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
                    $clusterFiles[] = [
                        'id' => $file,
                        'name' => $file,
                        'path' => $fullPath,
                        'created_at' => filemtime($fullPath)
                    ];
                }
            }

            // Sort by creation time (newest first)
            usort($clusterFiles, function ($a, $b) {
                return $b['created_at'] - $a['created_at'];
            });

            $this->clusters = $clusterFiles;
        } catch (\Exception $e) {
            Log::error('Failed to load clusters: ' . $e->getMessage());
            $this->clusters = [];
        }
    }

    public function selectCluster($clusterName)
    {
        try {
            // Get existing selected clusters from session
            $selectedClusters = session('selectedClusters', []);

            // Add cluster to selected clusters if not already present (multi-cluster support)
            if (!in_array($clusterName, $selectedClusters)) {
                $selectedClusters[] = $clusterName;
                $this->success = "Cluster '{$clusterName}' added to workspace!";
            } else {
                $this->success = "Switched to cluster '{$clusterName}'!";
            }

            // Update session with multi-cluster support
            session(['selectedClusters' => $selectedClusters]);
            session(['activeClusterTab' => $clusterName]);
            session(['selectedCluster' => $clusterName]); // Legacy support

            $this->selectedCluster = $clusterName;

            // Dispatch events to update other components
            $this->dispatch('clusterChanged', cluster: $clusterName);
            $this->dispatch('clusterTabsUpdated');

            // Refresh the page to load data for the newly selected cluster
            $this->dispatch('refreshPage');

        } catch (\Exception $e) {
            $this->error = 'Failed to select cluster: ' . $e->getMessage();
            Log::error('Failed to select cluster: ' . $e->getMessage());
        }
    }

    public function toggleUploadForm()
    {
        $this->showUploadForm = !$this->showUploadForm;
        $this->resetUploadForm();
    }

    public function uploadKubeconfig()
    {
        $this->validate([
            'kubeconfig' => 'required|file|max:10240', // 10MB max
            'clusterName' => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
        ]);

        try {
            $this->loading = true;
            $this->error = null;

            // Get the uploaded file content
            $content = file_get_contents($this->kubeconfig->getRealPath());

            // Validate YAML format
            try {
                Yaml::parse($content);
            } catch (\Exception $e) {
                throw new \Exception('Invalid YAML format: ' . $e->getMessage());
            }

            // Check if cluster name already exists
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
            $targetPath = $kubeconfigPath . '/' . $this->clusterName;

            if (file_exists($targetPath)) {
                throw new \Exception('A cluster with this name already exists. Please choose a different name.');
            }

            // Ensure the kubeconfigs directory exists
            if (!is_dir($kubeconfigPath)) {
                mkdir($kubeconfigPath, 0755, true);
            }

            // Save the file
            file_put_contents($targetPath, $content);

            // Automatically select the newly uploaded cluster
            $this->selectCluster($this->clusterName);

            $this->success = "Kubeconfig uploaded and cluster '{$this->clusterName}' added to workspace!";
            $this->resetUploadForm();
            $this->loadClusters();

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error('Failed to upload kubeconfig: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    private function resetUploadForm()
    {
        $this->kubeconfig = null;
        $this->clusterName = '';
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.kubernetes.cluster-selection-modal');
    }
}
