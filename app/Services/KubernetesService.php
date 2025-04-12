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

    


    
    // More methods like getDeployments, getServices, etc., can be added similarly
}

