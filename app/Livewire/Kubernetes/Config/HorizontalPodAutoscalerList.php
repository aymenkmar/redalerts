<?php

namespace App\Livewire\Kubernetes\Config;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\CachedKubernetesService;
use Carbon\Carbon;

class HorizontalPodAutoscalerList extends Component
{
    public $horizontalPodAutoscalers = [];
    public $namespaces = [];
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
                $hpaResponse = $service->getHorizontalPodAutoscalers();
            } catch (\Exception $e) {
                $service = new \App\Services\KubernetesService($kubeconfigPath);
                $hpaResponse = $service->getHorizontalPodAutoscalers();
            }

            if (isset($hpaResponse['items'])) {
                $this->horizontalPodAutoscalers = $hpaResponse['items'];
            } else {
                $this->horizontalPodAutoscalers = [];
                if (isset($hpaResponse['message'])) {
                    throw new \Exception('Kubernetes API error: ' . $hpaResponse['message']);
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

        } catch (\Exception $e) {
            $this->error = 'Failed to load horizontal pod autoscalers: ' . $e->getMessage();
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
                'message' => 'Horizontal pod autoscalers data refreshed successfully'
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
        return view('livewire.kubernetes.config.horizontal-pod-autoscaler-list')->layout('layouts.kubernetes');
    }
}
