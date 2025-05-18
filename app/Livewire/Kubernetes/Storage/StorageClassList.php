<?php

namespace App\Livewire\Kubernetes\Storage;

class StorageClassList extends BaseStorageList
{
    protected function getResourceMethod(): string
    {
        return 'getStorageClasses';
    }
    
    protected function isNamespaced(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.kubernetes.storage.storage-class-list', [
            'storageClasses' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
