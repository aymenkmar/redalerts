<?php

namespace App\Livewire\Kubernetes\Workloads;

class JobList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getJobs';
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.job-list', [
            'jobs' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
