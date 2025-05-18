<?php

namespace App\Livewire\Kubernetes\Config;

class ResourceQuotaList extends BaseConfigList
{
    protected function getResourceMethod(): string
    {
        return 'getResourceQuotas';
    }
    
    protected function needsNamespaceParameter(): bool
    {
        return true;
    }

    public function render()
    {
        return view('livewire.kubernetes.config.resource-quota-list', [
            'resourceQuotas' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
