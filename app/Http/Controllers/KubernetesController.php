<?php


namespace App\Http\Controllers;

use App\Services\KubernetesService;
use Illuminate\Http\Request;

class KubernetesController extends Controller
{
    protected function getServiceByConfigName($configName)
    {
        $kubeconfigPath = env('KUBECONFIG_PATH') . '/' . $configName;
        return new KubernetesService($kubeconfigPath);
    }

    public function getNodes($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $nodes = $service->getNodes();
            return response()->json($nodes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPods($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $pods = $service->getPods();
            return response()->json($pods);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getServices($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $services = $service->getServices();
            return response()->json($services);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNamespaces($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $namespaces = $service->getNamespaces();
            return response()->json($namespaces);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEndpoints($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $endpoints = $service->getEndpoints();
            return response()->json($endpoints);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSecrets($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $secrets = $service->getSecrets();
            return response()->json($secrets);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getConfigMaps($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getConfigMaps());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPersistentVolumes($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getPersistentVolumes());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPersistentVolumeClaims($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getPersistentVolumeClaims());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEvents($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getEvents());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getServiceAccounts($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getServiceAccounts());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getReplicationControllers($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getReplicationControllers());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLimitRanges($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getLimitRanges());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getResourceQuotas($configName, $namespace)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $resourceQuotas = $service->getResourceQuotas($namespace);
            return response()->json($resourceQuotas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function listClusters()
    {
        $path = env('KUBECONFIG_PATH');

        if (!is_dir($path)) {
            return response()->json(['error' => 'Kubeconfig path not found'], 500);
        }

        $files = scandir($path);
        $clusters = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path . '/' . $file;

            if (is_file($fullPath)) {
                $clusters[] = $file; // return the full filename as-is (e.g., 'kpilot')
            }
        }

        return response()->json($clusters);
    }



    public function getDeployments($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getDeployments());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getReplicaSets($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getReplicaSets());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDaemonSets($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getDaemonSets());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStatefulSets($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getStatefulSets());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getJobs($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getJobs());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCronJobs($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getCronJobs());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getIngresses($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getIngresses());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNetworkPolicies($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getNetworkPolicies());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getApplications($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getApplications());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getHorizontalPodAutoscalers($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getHorizontalPodAutoscalers());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPodDisruptionBudgets($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getPodDisruptionBudgets());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPriorityClasses($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getPriorityClasses());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRuntimeClasses($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getRuntimeClasses());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLeases($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getLeases());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMutatingWebhookConfigurations($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getMutatingWebhookConfigurations());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getValidatingWebhookConfigurations($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getValidatingWebhookConfigurations());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getIngressClasses($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getIngressClasses());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStorageClasses($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getStorageClasses());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getClusterRoles($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getClusterRoles());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRoles($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getRoles());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getClusterRoleBindings($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getClusterRoleBindings());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRoleBindings($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getRoleBindings());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCustomResourceDefinitions($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getCustomResourceDefinitions());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCertificates($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getCertificates());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCertificateRequests($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getCertificateRequests());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getIssuers($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getIssuers());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getClusterIssuers($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getClusterIssuers());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getChallenges($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getChallenges());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOrders($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getOrders());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //public function createPortForward($configName, $namespace, $pod, Request $request)
    //{
        //try {
            //$service = $this->getServiceByConfigName($configName);
            //$localPort = $request->input('localPort', 8000);
            //$podPort = $request->input('podPort', 80);
            //return response()->json($service->createPortForward($namespace, $pod, $localPort, $podPort));
        //} catch (\Exception $e) {
            //return response()->json(['error' => $e->getMessage()], 500);
        //}
    //}
}

