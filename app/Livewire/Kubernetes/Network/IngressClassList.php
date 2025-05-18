<?php

namespace App\Livewire\Kubernetes\Network;

class IngressClassList extends BaseNetworkList
{
    protected function getResourceMethod(): string
    {
        return 'getIngressClasses';
    }
    
    protected function isNamespaced(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.kubernetes.network.ingress-class-list', [
            'ingressClasses' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
