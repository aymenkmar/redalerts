<?php

namespace App\Livewire\Kubernetes\Storage;

class PersistentVolumeClaimList extends BaseStorageList
{
    protected function getResourceMethod(): string
    {
        return 'getPersistentVolumeClaims';
    }

    public function render()
    {
        return view('livewire.kubernetes.storage.persistent-volume-claim-list', [
            'persistentVolumeClaims' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
