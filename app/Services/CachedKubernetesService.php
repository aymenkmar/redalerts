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
     * Get services with caching
     */
    public function getServices($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'services';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching services from Kubernetes API');
                return $this->kubernetesService->getServices();
            } catch (\Exception $e) {
                Log::error('Failed to fetch services: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get endpoints with caching
     */
    public function getEndpoints($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'endpoints';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching endpoints from Kubernetes API');
                return $this->kubernetesService->getEndpoints();
            } catch (\Exception $e) {
                Log::error('Failed to fetch endpoints: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get ingresses with caching
     */
    public function getIngresses($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'ingresses';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching ingresses from Kubernetes API');
                return $this->kubernetesService->getIngresses();
            } catch (\Exception $e) {
                Log::error('Failed to fetch ingresses: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get ingress classes with caching
     */
    public function getIngressClasses($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'ingressclasses';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching ingress classes from Kubernetes API');
                return $this->kubernetesService->getIngressClasses();
            } catch (\Exception $e) {
                Log::error('Failed to fetch ingress classes: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get network policies with caching
     */
    public function getNetworkPolicies($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'networkpolicies';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching network policies from Kubernetes API');
                return $this->kubernetesService->getNetworkPolicies();
            } catch (\Exception $e) {
                Log::error('Failed to fetch network policies: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get persistent volume claims with caching
     */
    public function getPersistentVolumeClaims($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'persistentvolumeclaims';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching persistent volume claims from Kubernetes API');
                return $this->kubernetesService->getPersistentVolumeClaims();
            } catch (\Exception $e) {
                Log::error('Failed to fetch persistent volume claims: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get persistent volumes with caching
     */
    public function getPersistentVolumes($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'persistentvolumes';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching persistent volumes from Kubernetes API');
                return $this->kubernetesService->getPersistentVolumes();
            } catch (\Exception $e) {
                Log::error('Failed to fetch persistent volumes: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get storage classes with caching
     */
    public function getStorageClasses($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'storageclasses';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching storage classes from Kubernetes API');
                return $this->kubernetesService->getStorageClasses();
            } catch (\Exception $e) {
                Log::error('Failed to fetch storage classes: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get events with caching
     */
    public function getEvents($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'events';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching events from Kubernetes API');
                return $this->kubernetesService->getEvents();
            } catch (\Exception $e) {
                Log::error('Failed to fetch events: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get service accounts with caching
     */
    public function getServiceAccounts($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'serviceaccounts';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching service accounts from Kubernetes API');
                return $this->kubernetesService->getServiceAccounts();
            } catch (\Exception $e) {
                Log::error('Failed to fetch service accounts: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get cluster roles with caching
     */
    public function getClusterRoles($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'clusterroles';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching cluster roles from Kubernetes API');
                return $this->kubernetesService->getClusterRoles();
            } catch (\Exception $e) {
                Log::error('Failed to fetch cluster roles: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get roles with caching
     */
    public function getRoles($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'roles';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching roles from Kubernetes API');
                return $this->kubernetesService->getRoles();
            } catch (\Exception $e) {
                Log::error('Failed to fetch roles: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get cluster role bindings with caching
     */
    public function getClusterRoleBindings($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'clusterrolebindings';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching cluster role bindings from Kubernetes API');
                return $this->kubernetesService->getClusterRoleBindings();
            } catch (\Exception $e) {
                Log::error('Failed to fetch cluster role bindings: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get role bindings with caching
     */
    public function getRoleBindings($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'rolebindings';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching role bindings from Kubernetes API');
                return $this->kubernetesService->getRoleBindings();
            } catch (\Exception $e) {
                Log::error('Failed to fetch role bindings: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get custom resource definitions with caching
     */
    public function getCustomResourceDefinitions($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'customresourcedefinitions';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching custom resource definitions from Kubernetes API');
                return $this->kubernetesService->getCustomResourceDefinitions();
            } catch (\Exception $e) {
                Log::error('Failed to fetch custom resource definitions: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get ACME challenges with caching
     */
    public function getChallenges($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'challenges';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching ACME challenges from Kubernetes API');
                return $this->kubernetesService->getChallenges();
            } catch (\Exception $e) {
                Log::error('Failed to fetch ACME challenges: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get ACME orders with caching
     */
    public function getOrders($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'orders';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching ACME orders from Kubernetes API');
                return $this->kubernetesService->getOrders();
            } catch (\Exception $e) {
                Log::error('Failed to fetch ACME orders: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get certificates with caching
     */
    public function getCertificates($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'certificates';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching certificates from Kubernetes API');
                return $this->kubernetesService->getCertificates();
            } catch (\Exception $e) {
                Log::error('Failed to fetch certificates: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get certificate requests with caching
     */
    public function getCertificateRequests($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'certificaterequests';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching certificate requests from Kubernetes API');
                return $this->kubernetesService->getCertificateRequests();
            } catch (\Exception $e) {
                Log::error('Failed to fetch certificate requests: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get issuers with caching
     */
    public function getIssuers($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'issuers';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching issuers from Kubernetes API');
                return $this->kubernetesService->getIssuers();
            } catch (\Exception $e) {
                Log::error('Failed to fetch issuers: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get cluster issuers with caching
     */
    public function getClusterIssuers($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . 'clusterissuers';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->defaultCacheTtl, function () {
            try {
                Log::info('Fetching cluster issuers from Kubernetes API');
                return $this->kubernetesService->getClusterIssuers();
            } catch (\Exception $e) {
                Log::error('Failed to fetch cluster issuers: ' . $e->getMessage());
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
            $this->cachePrefix . 'services',
            $this->cachePrefix . 'endpoints',
            $this->cachePrefix . 'ingresses',
            $this->cachePrefix . 'ingressclasses',
            $this->cachePrefix . 'networkpolicies',
            $this->cachePrefix . 'persistentvolumeclaims',
            $this->cachePrefix . 'persistentvolumes',
            $this->cachePrefix . 'storageclasses',
            $this->cachePrefix . 'events',
            $this->cachePrefix . 'serviceaccounts',
            $this->cachePrefix . 'clusterroles',
            $this->cachePrefix . 'roles',
            $this->cachePrefix . 'clusterrolebindings',
            $this->cachePrefix . 'rolebindings',
            $this->cachePrefix . 'customresourcedefinitions',
            $this->cachePrefix . 'challenges',
            $this->cachePrefix . 'orders',
            $this->cachePrefix . 'certificates',
            $this->cachePrefix . 'certificaterequests',
            $this->cachePrefix . 'issuers',
            $this->cachePrefix . 'clusterissuers',
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
            'services' => $this->cachePrefix . 'services',
            'endpoints' => $this->cachePrefix . 'endpoints',
            'ingresses' => $this->cachePrefix . 'ingresses',
            'ingressclasses' => $this->cachePrefix . 'ingressclasses',
            'networkpolicies' => $this->cachePrefix . 'networkpolicies',
            'persistentvolumeclaims' => $this->cachePrefix . 'persistentvolumeclaims',
            'persistentvolumes' => $this->cachePrefix . 'persistentvolumes',
            'storageclasses' => $this->cachePrefix . 'storageclasses',
            'events' => $this->cachePrefix . 'events',
            'serviceaccounts' => $this->cachePrefix . 'serviceaccounts',
            'clusterroles' => $this->cachePrefix . 'clusterroles',
            'roles' => $this->cachePrefix . 'roles',
            'clusterrolebindings' => $this->cachePrefix . 'clusterrolebindings',
            'rolebindings' => $this->cachePrefix . 'rolebindings',
            'customresourcedefinitions' => $this->cachePrefix . 'customresourcedefinitions',
            'challenges' => $this->cachePrefix . 'challenges',
            'orders' => $this->cachePrefix . 'orders',
            'certificates' => $this->cachePrefix . 'certificates',
            'certificaterequests' => $this->cachePrefix . 'certificaterequests',
            'issuers' => $this->cachePrefix . 'issuers',
            'clusterissuers' => $this->cachePrefix . 'clusterissuers',
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
