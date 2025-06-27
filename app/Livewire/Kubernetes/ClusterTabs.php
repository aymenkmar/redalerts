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
        // Store the current active cluster before clearing it
        $activeClusterTab = session('activeClusterTab');
        if ($activeClusterTab) {
            session(['previousActiveCluster' => $activeClusterTab]);
        }

        // Reset to cluster selection mode
        session(['activeClusterTab' => null]);
        session(['selectedCluster' => null]); // Update legacy session

        // Redirect to the dashboard to show cluster selection
        return redirect()->route('dashboard-kubernetes');
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
