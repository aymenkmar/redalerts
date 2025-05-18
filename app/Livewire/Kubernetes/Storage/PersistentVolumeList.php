<?php

namespace App\Livewire\Kubernetes\Storage;

class PersistentVolumeList extends BaseStorageList
{
    protected function getResourceMethod(): string
    {
        return 'getPersistentVolumes';
    }
    
    protected function isNamespaced(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.kubernetes.storage.persistent-volume-list', [
            'persistentVolumes' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
