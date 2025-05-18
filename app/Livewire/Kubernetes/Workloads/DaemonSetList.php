<?php

namespace App\Livewire\Kubernetes\Workloads;

class DaemonSetList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getDaemonSets';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.daemon-set-list', [
            'daemonSets' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
