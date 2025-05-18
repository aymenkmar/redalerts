<?php

namespace App\Livewire\Kubernetes\Workloads;

class DeploymentList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getDeployments';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.deployment-list', [
            'deployments' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
