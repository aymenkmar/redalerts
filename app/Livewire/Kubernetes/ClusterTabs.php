<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Livewire\Attributes\On;

class ClusterTabs extends Component
{
    public $selectedClusters = [];
    public $activeClusterTab = null;

    public function mount()
    {
        $this->loadClusterTabs();
    }

    #[On('clusterTabsUpdated')]
    public function refreshTabs()
    {
        $this->loadClusterTabs();
    }

    public function addNewCluster()
    {
        // Dispatch event to the main dashboard to add a new cluster
        $this->dispatch('addNewCluster');
    }

    private function loadClusterTabs()
    {
        $this->selectedClusters = session('selectedClusters', []);
        $this->activeClusterTab = session('activeClusterTab', null);
    }

    public function render()
    {
        // Always reload session data to ensure we have the latest state
        $this->loadClusterTabs();
        return view('livewire.kubernetes.cluster-tabs');
    }
}
