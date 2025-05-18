<?php

namespace App\Livewire\Kubernetes\Network;

class ServiceList extends BaseNetworkList
{
    protected function getResourceMethod(): string
    {
        return 'getServices';
    }

    public function render()
    {
        return view('livewire.kubernetes.network.service-list', [
            'services' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
