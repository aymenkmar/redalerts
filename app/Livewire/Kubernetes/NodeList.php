<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class NodeList extends Component
{
    public $nodes = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;
    public $searchTerm = '';

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the selected cluster from session
        $this->selectedCluster = session('selectedCluster', null);

        if (!$this->selectedCluster) {
            return redirect()->route('dashboard-kubernetes')->with('error', 'Please select a cluster first');
        }

        $this->loadNodes();
    }

    public function loadNodes()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getNodes();

            if (isset($response['items'])) {
                $this->nodes = $response['items'];
            } else {
                $this->nodes = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load nodes: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function formatAge($timestamp)
    {
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

    public function getNodeStatus($node)
    {
        $readyCondition = collect($node['status']['conditions'] ?? [])
            ->firstWhere('type', 'Ready');

        return $readyCondition && $readyCondition['status'] === 'True' ? 'Ready' : 'Not Ready';
    }

    public function getNodeRoles($node)
    {
        $roles = collect($node['metadata']['labels'] ?? [])
            ->filter(function ($value, $key) {
                return str_starts_with($key, 'node-role.kubernetes.io/');
            })
            ->keys()
            ->map(function ($key) {
                return str_replace('node-role.kubernetes.io/', '', $key);
            })
            ->join(', ');

        return $roles ?: 'worker';
    }

    public function render()
    {
        $filteredNodes = collect($this->nodes);

        if ($this->searchTerm) {
            $filteredNodes = $filteredNodes->filter(function ($node) {
                return str_contains(strtolower($node['metadata']['name']), strtolower($this->searchTerm));
            });
        }

        return view('livewire.kubernetes.node-list', [
            'filteredNodes' => $filteredNodes->toArray()
        ])->layout('layouts.kubernetes');
    }
}
