<?php

namespace App\Livewire\Kubernetes\Workloads;

class ReplicationControllerList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getReplicationControllers';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.replication-controller-list', [
            'replicationControllers' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
