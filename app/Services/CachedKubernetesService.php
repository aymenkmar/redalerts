<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CachedKubernetesService
{
    private $kubernetesService;
    private $cachePrefix;
    private $defaultCacheTtl;

    public function __construct($kubeconfigPath)
    {
        $this->kubernetesService = new KubernetesService($kubeconfigPath);
        $this->cachePrefix = 'k8s_' . md5($kubeconfigPath) . '_';
        $this->defaultCacheTtl = 300; // 5 minutes default cache
    }

    /**
     * Get pods with caching
     */
    public function getPods($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'pods';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching pods from Kubernetes API');
                return $this->kubernetesService->getPods();
            } catch (\Exception $e) {
                Log::error('Failed to fetch pods: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get nodes with caching
     */
    public function getNodes($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'nodes';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching nodes from Kubernetes API');
                return $this->kubernetesService->getNodes();
            } catch (\Exception $e) {
                Log::error('Failed to fetch nodes: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get deployments with caching
     */
    public function getDeployments($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'deployments';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching deployments from Kubernetes API');
                return $this->kubernetesService->getDeployments();
            } catch (\Exception $e) {
                Log::error('Failed to fetch deployments: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get daemon sets with caching
     */
    public function getDaemonSets($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'daemonsets';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching daemon sets from Kubernetes API');
                return $this->kubernetesService->getDaemonSets();
            } catch (\Exception $e) {
                Log::error('Failed to fetch daemon sets: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get stateful sets with caching
     */
    public function getStatefulSets($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'statefulsets';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching stateful sets from Kubernetes API');
                return $this->kubernetesService->getStatefulSets();
            } catch (\Exception $e) {
                Log::error('Failed to fetch stateful sets: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get replica sets with caching
     */
    public function getReplicaSets($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'replicasets';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching replica sets from Kubernetes API');
                return $this->kubernetesService->getReplicaSets();
            } catch (\Exception $e) {
                Log::error('Failed to fetch replica sets: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get replication controllers with caching
     */
    public function getReplicationControllers($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'replicationcontrollers';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching replication controllers from Kubernetes API');
                return $this->kubernetesService->getReplicationControllers();
            } catch (\Exception $e) {
                Log::error('Failed to fetch replication controllers: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get jobs with caching
     */
    public function getJobs($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'jobs';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching jobs from Kubernetes API');
                return $this->kubernetesService->getJobs();
            } catch (\Exception $e) {
                Log::error('Failed to fetch jobs: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get cron jobs with caching
     */
    public function getCronJobs($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'cronjobs';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching cron jobs from Kubernetes API');
                return $this->kubernetesService->getCronJobs();
            } catch (\Exception $e) {
                Log::error('Failed to fetch cron jobs: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get config maps with caching
     */
    public function getConfigMaps($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'configmaps';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching config maps from Kubernetes API');
                return $this->kubernetesService->getConfigMaps();
            } catch (\Exception $e) {
                Log::error('Failed to fetch config maps: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get secrets with caching
     */
    public function getSecrets($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'secrets';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching secrets from Kubernetes API');
                return $this->kubernetesService->getSecrets();
            } catch (\Exception $e) {
                Log::error('Failed to fetch secrets: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get resource quotas with caching (from all namespaces)
     */
    public function getResourceQuotas($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'resourcequotas';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching resource quotas from Kubernetes API');

                // First get all namespaces
                $namespacesResponse = $this->kubernetesService->getNamespaces();
                $allResourceQuotas = ['items' => []];

                if (isset($namespacesResponse['items'])) {
                    foreach ($namespacesResponse['items'] as $namespace) {
                        $namespaceName = $namespace['metadata']['name'];
                        try {
                            $quotasResponse = $this->kubernetesService->getResourceQuotas($namespaceName);
                            if (isset($quotasResponse['items'])) {
                                $allResourceQuotas['items'] = array_merge(
                                    $allResourceQuotas['items'],
                                    $quotasResponse['items']
                                );
                            }
                        } catch (\Exception $e) {
                            // Log but continue with other namespaces
                            Log::warning("Failed to fetch resource quotas for namespace {$namespaceName}: " . $e->getMessage());
                        }
                    }
                }

                return $allResourceQuotas;
            } catch (\Exception $e) {
                Log::error('Failed to fetch resource quotas: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get limit ranges with caching
     */
    public function getLimitRanges($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'limitranges';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching limit ranges from Kubernetes API');
                return $this->kubernetesService->getLimitRanges();
            } catch (\Exception $e) {
                Log::error('Failed to fetch limit ranges: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get horizontal pod autoscalers with caching
     */
    public function getHorizontalPodAutoscalers($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'horizontalpodautoscalers';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching horizontal pod autoscalers from Kubernetes API');
                return $this->kubernetesService->getHorizontalPodAutoscalers();
            } catch (\Exception $e) {
                Log::error('Failed to fetch horizontal pod autoscalers: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get namespaces with caching
     */
    public function getNamespaces($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'namespaces';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl * 2, function () {
            try {
                Log::info('Fetching namespaces from Kubernetes API');
                return $this->kubernetesService->getNamespaces();
            } catch (\Exception $e) {
                Log::error('Failed to fetch namespaces: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Clear all cached data for this cluster
     */
    public function clearCache()
    {
        $keys = [
            $this->cachePrefix . 'pods',
            $this->cachePrefix . 'nodes',
            $this->cachePrefix . 'deployments',
            $this->cachePrefix . 'daemonsets',
            $this->cachePrefix . 'statefulsets',
            $this->cachePrefix . 'replicasets',
            $this->cachePrefix . 'replicationcontrollers',
            $this->cachePrefix . 'jobs',
            $this->cachePrefix . 'cronjobs',
            $this->cachePrefix . 'configmaps',
            $this->cachePrefix . 'secrets',
            $this->cachePrefix . 'resourcequotas',
            $this->cachePrefix . 'limitranges',
            $this->cachePrefix . 'horizontalpodautoscalers',
            $this->cachePrefix . 'namespaces',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Log::info('Cleared Kubernetes cache for cluster');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        $keys = [
            'pods' => $this->cachePrefix . 'pods',
            'nodes' => $this->cachePrefix . 'nodes',
            'deployments' => $this->cachePrefix . 'deployments',
            'daemonsets' => $this->cachePrefix . 'daemonsets',
            'statefulsets' => $this->cachePrefix . 'statefulsets',
            'replicasets' => $this->cachePrefix . 'replicasets',
            'replicationcontrollers' => $this->cachePrefix . 'replicationcontrollers',
            'jobs' => $this->cachePrefix . 'jobs',
            'cronjobs' => $this->cachePrefix . 'cronjobs',
            'configmaps' => $this->cachePrefix . 'configmaps',
            'secrets' => $this->cachePrefix . 'secrets',
            'resourcequotas' => $this->cachePrefix . 'resourcequotas',
            'limitranges' => $this->cachePrefix . 'limitranges',
            'horizontalpodautoscalers' => $this->cachePrefix . 'horizontalpodautoscalers',
            'namespaces' => $this->cachePrefix . 'namespaces',
        ];

        $stats = [];
        foreach ($keys as $resource => $key) {
            $stats[$resource] = [
                'cached' => Cache::has($key),
                'key' => $key,
            ];
        }

        return $stats;
    }

    /**
     * Proxy method to access original service methods
     */
    public function __call($method, $arguments)
    {
        return $this->kubernetesService->$method(...$arguments);
    }
}
