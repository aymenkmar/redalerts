<?php

namespace App\Livewire\Kubernetes\Network;

class IngressList extends BaseNetworkList
{
    protected function getResourceMethod(): string
    {
        return 'getIngresses';
    }

    public function render()
    {
        return view('livewire.kubernetes.network.ingress-list', [
            'ingresses' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
