<?php

namespace App\Livewire\Kubernetes\Workloads;

class PodList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getPods';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.pod-list', [
            'pods' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
