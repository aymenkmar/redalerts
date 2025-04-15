<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class KubernetesService
{
    protected $kubeconfigPath;

    public function __construct($kubeconfigPath)
    {
        $this->kubeconfigPath = $kubeconfigPath;
    }

    private function getKubeconfig()
    {
        // Read kubeconfig file
        $config = yaml_parse_file($this->kubeconfigPath);

        // Extract API server, certificate authority, client certificate, and client key
        $cluster = $config['clusters'][0]['cluster'] ?? null;
        $user = $config['users'][0]['user'] ?? null;

        if (!$cluster || !$user) {
            throw new \Exception('Cluster or User information missing in kubeconfig');
        }

        return [
            'apiServer' => $cluster['server'],
            'caCertData' => $cluster['certificate-authority-data'] ?? null,
            'clientCertData' => $user['client-certificate-data'] ?? null,
            'clientKeyData' => $user['client-key-data'] ?? null,
        ];
    }

    private function getTempFile($data)
    {
        $tempFile = tempnam(sys_get_temp_dir(), uniqid());
        file_put_contents($tempFile, base64_decode($data));
        return $tempFile;
    }

    private function makeRequest($url, $caCertFile, $clientCertFile, $clientKeyFile)
    {
        // cURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // TLS verification settings
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $caCertFile);

        // Use client certificate and key for authentication
        curl_setopt($ch, CURLOPT_SSLCERT, $clientCertFile);
        curl_setopt($ch, CURLOPT_SSLKEY, $clientKeyFile);

        // Execute request
        $response = curl_exec($ch);

        // Check if the response was successful
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }

    // Generic function to make any Kubernetes request
    private function makeK8SRequest($endpoint)
    {
        $kubeconfig = $this->getKubeconfig();

        // Handle certificate-authority-data (decode and save to a temp file)
        $caCertFile = $kubeconfig['caCertData'] ? $this->getTempFile($kubeconfig['caCertData']) : null;

        // Handle client-certificate-data and client-key-data (decode and save to temp files)
        $clientCertFile = $kubeconfig['clientCertData'] ? $this->getTempFile($kubeconfig['clientCertData']) : null;
        $clientKeyFile = $kubeconfig['clientKeyData'] ? $this->getTempFile($kubeconfig['clientKeyData']) : null;

        // API URL for getting the specified endpoint (e.g., nodes, pods)
        $url = $kubeconfig['apiServer'] . $endpoint;

        // Make the request
        $response = $this->makeRequest($url, $caCertFile, $clientCertFile, $clientKeyFile);

        // Clean up temp files
        unlink($caCertFile);
        unlink($clientCertFile);
        unlink($clientKeyFile);

        return json_decode($response, true);
    }

    // Example method to get nodes
    public function getNodes()
    {
        return $this->makeK8SRequest('/api/v1/nodes');
    }

    // Example method to get pods
    public function getPods()
    {
        return $this->makeK8SRequest('/api/v1/pods');
    }

    // You can easily add more methods for other endpoints
    public function getServices()
    {
        return $this->makeK8SRequest('/api/v1/services');
    }

    public function getNamespaces()
    {
        return $this->makeK8SRequest('/api/v1/namespaces');
    }


    public function getEndpoints()
    {
        return $this->makeK8SRequest('/api/v1/endpoints');
    }

    public function getSecrets()
    {
        return $this->makeK8SRequest('/api/v1/secrets');
    }

    public function getConfigMaps()
    {
        return $this->makeK8SRequest('/api/v1/configmaps');
    }

    public function getPersistentVolumes()
    {
        return $this->makeK8SRequest('/api/v1/persistentvolumes');
    }

    public function getPersistentVolumeClaims()
    {
        return $this->makeK8SRequest('/api/v1/persistentvolumeclaims');
    }

    public function getEvents()
    {
        return $this->makeK8SRequest('/api/v1/events');
    }

    public function getServiceAccounts()
    {
        return $this->makeK8SRequest('/api/v1/serviceaccounts');
    }

    public function getReplicationControllers()
    {
        return $this->makeK8SRequest('/api/v1/replicationcontrollers');
    }

    public function getLimitRanges()
    {
        return $this->makeK8SRequest('/api/v1/limitranges');
    }

    public function getResourceQuotas($namespace)
    {
        return $this->makeK8SRequest("/api/v1/namespaces/{$namespace}/resourcequotas");
    }




    // Apps API Group: apis/apps/v1

    public function getDeployments()
    {
        return $this->makeK8SRequest('/apis/apps/v1/deployments');
    }

    public function getReplicaSets()
    {
        return $this->makeK8SRequest('/apis/apps/v1/replicasets');
    }

    public function getDaemonSets()
    {
        return $this->makeK8SRequest('/apis/apps/v1/daemonsets');
    }

    public function getStatefulSets()
    {
        return $this->makeK8SRequest('/apis/apps/v1/statefulsets');
    }

    //Batch API Group: apis/batch/v1

    public function getJobs()
    {
        return $this->makeK8SRequest('/apis/batch/v1/jobs');
    }

    public function getCronJobs()
    {
        return $this->makeK8SRequest('/apis/batch/v1/cronjobs');
    }


    //Networking API Group

    public function getIngresses()
    {
        return $this->makeK8SRequest('/apis/networking.k8s.io/v1/ingresses');
    }

    public function getNetworkPolicies()
    {
        return $this->makeK8SRequest('/apis/networking.k8s.io/v1/networkpolicies');
    }

    // Applications
    public function getApplications()
    {
        return $this->makeK8SRequest('/apis/app.k8s.io/v1beta1/applications');
    }

    // Horizontal Pod Autoscalers
    public function getHorizontalPodAutoscalers()
    {
        return $this->makeK8SRequest('/apis/autoscaling/v2/horizontalpodautoscalers');
    }

    // Pod Disruption Budgets
    public function getPodDisruptionBudgets()
    {
        return $this->makeK8SRequest('/apis/policy/v1/poddisruptionbudgets');
    }

    // Priority Classes
    public function getPriorityClasses()
    {
        return $this->makeK8SRequest('/apis/scheduling.k8s.io/v1/priorityclasses');
    }

    // Runtime Classes
    public function getRuntimeClasses()
    {
        return $this->makeK8SRequest('/apis/node.k8s.io/v1/runtimeclasses');
    }

    // Leases
    public function getLeases()
    {
        return $this->makeK8SRequest('/apis/coordination.k8s.io/v1/leases');
    }

    // Mutating Webhook Configuration
    public function getMutatingWebhookConfigurations()
    {
        return $this->makeK8SRequest('/apis/admissionregistration.k8s.io/v1/mutatingwebhookconfigurations');
    }

    // Validating Webhook Configuration
    public function getValidatingWebhookConfigurations()
    {
        return $this->makeK8SRequest('/apis/admissionregistration.k8s.io/v1/validatingwebhookconfigurations');
    }

    // Ingress Classes
    public function getIngressClasses()
    {
        return $this->makeK8SRequest('/apis/networking.k8s.io/v1/ingressclasses');
    }

    // Storage Classes
    public function getStorageClasses()
    {
        return $this->makeK8SRequest('/apis/storage.k8s.io/v1/storageclasses');
    }

    // RBAC API Group
    // Cluster Roles
    public function getClusterRoles()
    {
        return $this->makeK8SRequest('/apis/rbac.authorization.k8s.io/v1/clusterroles');
    }

    // Roles
    public function getRoles()
    {
        return $this->makeK8SRequest('/apis/rbac.authorization.k8s.io/v1/roles');
    }

    // Cluster Role Bindings
    public function getClusterRoleBindings()
    {
        return $this->makeK8SRequest('/apis/rbac.authorization.k8s.io/v1/clusterrolebindings');
    }

    // Role Bindings
    public function getRoleBindings()
    {
        return $this->makeK8SRequest('/apis/rbac.authorization.k8s.io/v1/rolebindings');
    }

    // Port Forwarding - This is a special case as it's not a standard API resource
    // It requires a different implementation approach using the Kubernetes API
    //public function createPortForward($namespace, $pod, $localPort, $podPort)
    //{
        // This is a placeholder for port forwarding functionality
        // Actual implementation would require a more complex approach with websockets
        // or using the kubectl port-forward command
        //return [
            //'status' => 'Not implemented',
            //'message' => 'Port forwarding requires a different implementation approach'
        //];
    //}
}

