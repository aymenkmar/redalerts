<?php

namespace App\Livewire\Kubernetes\Workloads;

use App\Services\KubernetesService;
use Illuminate\Support\Facades\Log;

class ReplicaSetList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getReplicaSets';
    }

    // Override the loadResources method to handle large datasets more efficiently
    public function loadResources()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);

            // Get the method name from the child class
            $methodName = $this->getResourceMethod();

            // Call the method on the service
            $response = $service->$methodName();

            if (isset($response['items'])) {
                // Process the items to extract only the necessary data
                $this->resources = $this->processReplicaSets($response['items']);
            } else {
                $this->resources = [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to load ReplicaSets: ' . $e->getMessage());
            $this->error = 'Failed to load resources: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // Process ReplicaSets to extract only the necessary data
    protected function processReplicaSets($items)
    {
        $processedItems = [];

        foreach ($items as $item) {
            // Extract only the necessary fields to reduce memory usage
            $processedItems[] = [
                'metadata' => [
                    'name' => $item['metadata']['name'] ?? 'Unknown',
                    'namespace' => $item['metadata']['namespace'] ?? 'default',
                    'creationTimestamp' => $item['metadata']['creationTimestamp'] ?? null,
                ],
                'spec' => [
                    'replicas' => $item['spec']['replicas'] ?? 0,
                ],
                'status' => [
                    'replicas' => $item['status']['replicas'] ?? 0,
                    'readyReplicas' => $item['status']['readyReplicas'] ?? 0,
                ],
            ];
        }

        return $processedItems;
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.replica-set-list', [
            'replicaSets' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}
