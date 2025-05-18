<?php

namespace App\Livewire\Kubernetes\Config;

class SecretList extends BaseConfigList
{
    protected function getResourceMethod(): string
    {
        return 'getSecrets';
    }

    public function render()
    {
        return view('livewire.kubernetes.config.secret-list', [
            'secrets' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
