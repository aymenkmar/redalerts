<?php

namespace App\Livewire\Kubernetes\Workloads;

class StatefulSetList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getStatefulSets';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.stateful-set-list', [
            'statefulSets' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
