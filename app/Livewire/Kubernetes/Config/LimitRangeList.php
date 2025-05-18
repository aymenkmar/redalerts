<?php

namespace App\Livewire\Kubernetes\Config;

class LimitRangeList extends BaseConfigList
{
    protected function getResourceMethod(): string
    {
        return 'getLimitRanges';
    }

    public function render()
    {
        return view('livewire.kubernetes.config.limit-range-list', [
            'limitRanges' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
