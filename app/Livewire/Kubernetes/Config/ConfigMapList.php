<?php

namespace App\Livewire\Kubernetes\Config;

class ConfigMapList extends BaseConfigList
{
    protected function getResourceMethod(): string
    {
        return 'getConfigMaps';
    }

    public function render()
    {
        return view('livewire.kubernetes.config.config-map-list', [
            'configMaps' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
