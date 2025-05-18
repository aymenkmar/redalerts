<?php

namespace App\Livewire\Kubernetes\Network;

class EndpointList extends BaseNetworkList
{
    protected function getResourceMethod(): string
    {
        return 'getEndpoints';
    }

    public function render()
    {
        return view('livewire.kubernetes.network.endpoint-list', [
            'endpoints' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
