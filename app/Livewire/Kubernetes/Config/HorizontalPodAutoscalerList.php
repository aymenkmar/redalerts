<?php

namespace App\Livewire\Kubernetes\Config;

class HorizontalPodAutoscalerList extends BaseConfigList
{
    protected function getResourceMethod(): string
    {
        return 'getHorizontalPodAutoscalers';
    }

    public function render()
    {
        return view('livewire.kubernetes.config.horizontal-pod-autoscaler-list', [
            'horizontalPodAutoscalers' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
