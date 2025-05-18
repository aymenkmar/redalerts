<?php

namespace App\Livewire\Kubernetes\Network;

class NetworkPolicyList extends BaseNetworkList
{
    protected function getResourceMethod(): string
    {
        return 'getNetworkPolicies';
    }

    public function render()
    {
        return view('livewire.kubernetes.network.network-policy-list', [
            'networkPolicies' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
