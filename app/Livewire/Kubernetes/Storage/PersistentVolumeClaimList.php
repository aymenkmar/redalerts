<?php

namespace App\Livewire\Kubernetes\Storage;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use Carbon\Carbon;

class PersistentVolumeClaimList extends Component
{
    public $persistentVolumeClaims = [];
    public $namespaces = [];
    public $pods = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;

    protected $queryString = [];

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');
        if ($this->selectedCluster) {
            $this->loadData();
        } else {
            $this->error = 'Please select a cluster first';
            $this->loading = false;
        }
    }

    public function loadData()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;

            if (!file_exists($kubeconfigPath)) {
                throw new \Exception('Kubeconfig file not found: ' . $kubeconfigPath);
            }

            try {
                $service = new CachedKubernetesService($kubeconfigPath);
                $pvcsResponse = $service->getPersistentVolumeClaims();
            } catch (\Exception $e) {
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $pvcsResponse = $service->getPersistentVolumeClaims();
            }

            if (isset($pvcsResponse['items'])) {
                $this->persistentVolumeClaims = $pvcsResponse['items'];
            } else {
                $this->persistentVolumeClaims = [];
                if (isset($pvcsResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $pvcsResponse['message']);
                }
            }

            $namespacesResponse = $service->getNamespaces();
            if (isset($namespacesResponse['items'])) {
                $this->namespaces = collect($namespacesResponse['items'])
                    ->map(function ($namespace) {
                        return $namespace['metadata']['name'];
                    })
                    ->toArray();
            } else {
                $this->namespaces = [];
            }

            // Fetch pods to determine which pods use which PVCs
            $podsResponse = $service->getPods();
            if (isset($podsResponse['items'])) {
                $this->pods = $podsResponse['items'];
            } else {
                $this->pods = [];
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load persistent volume claims: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function refreshData()
    {
        try {
            $this->selectedCluster = session('selectedCluster') ?? session('selected_cluster');

            if (!$this->selectedCluster) {
                $this->error = 'Please select a cluster first';
                return;
            }

            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new CachedKubernetesService($kubeconfigPath);

            $service->clearCache();
            $this->loadData();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Persistent volume claims data refreshed successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to refresh data: ' . $e->getMessage()
            ]);
        }
    }

    public function formatAge($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        $creationTime = Carbon::parse($timestamp);
        $now = Carbon::now();
        $diffInDays = $creationTime->diffInDays($now);

        if ($diffInDays > 0) {
            return $diffInDays . 'd';
        }

        $diffInHours = $creationTime->diffInHours($now);
        if ($diffInHours > 0) {
            return $diffInHours . 'h';
        }

        $diffInMinutes = $creationTime->diffInMinutes($now);
        if ($diffInMinutes > 0) {
            return $diffInMinutes . 'm';
        }

        return $creationTime->diffInSeconds($now) . 's';
    }

    public function render()
    {
        return view('livewire.kubernetes.storage.persistent-volume-claim-list')->layout('layouts.kubernetes');
    }
}
