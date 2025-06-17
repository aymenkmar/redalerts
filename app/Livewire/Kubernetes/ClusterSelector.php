<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Cluster;
use Illuminate\Support\Facades\Session;

class ClusterSelector extends Component
{
    use WithFileUploads;

    public $clusters = [];
    public $selectedCluster = null;
    public $showDropdown = false;
    public $showUploadModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showReplaceModal = false;
    public $editingCluster = null;
    public $newClusterName = '';
    public $replacementFile = null;
    public $clusterToDelete = null;

    protected $listeners = [
        'clusterUploaded' => 'handleClusterUploaded',
        'clusterManaged' => 'handleClusterManaged',
        'notify' => 'handleNotification'
    ];

    #[On('clusterTabsUpdated')]
    public function refreshSelector()
    {
        // Reload the component state when cluster tabs are updated
        $this->mount();
    }

    public function mount()
    {
        // Get multi-cluster session data
        $selectedClusters = session('selectedClusters', []);
        $activeClusterTab = session('activeClusterTab', null);
        $legacyCluster = session('selectedCluster', null);

        // Set the current selected cluster from active tab or legacy session
        // If we're in "add new cluster" mode (legacyCluster is null but activeClusterTab exists),
        // show null to display cluster selection interface
        if ($legacyCluster === null && $activeClusterTab === null && !empty($selectedClusters)) {
            // We're in "add new cluster" mode
            $this->selectedCluster = null;
        } else {
            $this->selectedCluster = $activeClusterTab ?? $legacyCluster;
        }

        $this->showUploadModal = session('showUploadModal', false);

        // Clear the session flag
        if ($this->showUploadModal) {
            session()->forget('showUploadModal');
        }

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
                        'id' => $cluster->id,
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
                        'id' => $cluster->id,
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



    public function addNewCluster()
    {
        // Reset to cluster selection mode while keeping existing clusters
        session(['activeClusterTab' => null]);
        session(['selectedCluster' => null]); // Clear current selection to show cluster grid

        // Redirect to the dashboard to show cluster selection
        return redirect()->route('dashboard-kubernetes');
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

    public function handleClusterManaged()
    {
        $this->loadClusters();
        $this->closeEditModal();
        $this->closeReplaceModal();
        $this->closeDeleteModal();
    }

    public function handleNotification($data)
    {
        $this->dispatch('showNotification', $data);
    }

    public function openEditModal($clusterId)
    {
        $cluster = collect($this->clusters)->firstWhere('id', $clusterId);
        if ($cluster) {
            $this->editingCluster = $cluster;
            $this->newClusterName = $cluster['name'];
            $this->showEditModal = true;
            $this->showDropdown = false;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingCluster = null;
        $this->newClusterName = '';
        $this->resetValidation();
    }

    public function openReplaceModal($clusterId)
    {
        $cluster = collect($this->clusters)->firstWhere('id', $clusterId);
        if ($cluster) {
            $this->editingCluster = $cluster;
            $this->showReplaceModal = true;
            $this->showDropdown = false;
        }
    }

    public function closeReplaceModal()
    {
        $this->showReplaceModal = false;
        $this->editingCluster = null;
        $this->replacementFile = null;
        $this->resetValidation();
    }

    public function openDeleteModal($clusterId)
    {
        $cluster = collect($this->clusters)->firstWhere('id', $clusterId);
        if ($cluster) {
            $this->clusterToDelete = $cluster;
            $this->showDeleteModal = true;
            $this->showDropdown = false;
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->clusterToDelete = null;
    }

    public function render()
    {
        // Always reload session data to ensure we have the latest state
        $selectedClusters = session('selectedClusters', []);
        $activeClusterTab = session('activeClusterTab', null);
        $legacyCluster = session('selectedCluster', null);

        // Update the selected cluster based on current session state
        if ($legacyCluster === null && $activeClusterTab === null && !empty($selectedClusters)) {
            $this->selectedCluster = null;
        } else {
            $this->selectedCluster = $activeClusterTab ?? $legacyCluster;
        }

        return view('livewire.kubernetes.cluster-selector');
    }
}
