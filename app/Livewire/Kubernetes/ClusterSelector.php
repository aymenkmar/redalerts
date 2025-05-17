<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use App\Models\Cluster;
use Illuminate\Support\Facades\Session;

class ClusterSelector extends Component
{
    public $clusters = [];
    public $selectedCluster = null;
    public $showDropdown = false;
    public $showUploadModal = false;

    protected $listeners = [
        'clusterUploaded' => 'handleClusterUploaded',
        'notify' => 'handleNotification'
    ];

    public function mount()
    {
        $this->selectedCluster = session('selectedCluster', null);
        $this->loadClusters();
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

    public function selectCluster($clusterName)
    {
        $this->selectedCluster = $clusterName;
        session(['selectedCluster' => $clusterName]);
        $this->showDropdown = false;

        $this->dispatch('clusterSelected', $clusterName);

        // Redirect to refresh the page with the new cluster
        return redirect(request()->header('Referer'));
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function toggleUploadModal()
    {
        $this->showUploadModal = !$this->showUploadModal;
    }

    public function handleClusterUploaded()
    {
        $this->loadClusters();
        $this->showUploadModal = false;

        // Dispatch an event to refresh the page after a short delay
        $this->dispatch('refreshAfterUpload');
    }

    public function handleNotification($data)
    {
        $this->dispatch('showNotification', $data);
    }

    public function render()
    {
        return view('livewire.kubernetes.cluster-selector');
    }
}
