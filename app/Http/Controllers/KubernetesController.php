<?php


namespace App\Http\Controllers;

use App\Models\Cluster;
use App\Rules\YamlFileValidation;
use App\Services\KubernetesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
        $clusterFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path . '/' . $file;

            if (is_file($fullPath)) {
                $clusterFiles[] = $file; // store the full filename as-is (e.g., 'kpilot')
            }
        }

        // Get cluster information from the database
        $clusterData = [];
        foreach ($clusterFiles as $clusterName) {
            $cluster = Cluster::where('name', $clusterName)->first();

            if ($cluster) {
                $clusterData[] = [
                    'name' => $clusterName,
                    'upload_time' => $cluster->upload_time
                ];
            } else {
                // If not in database yet, add it with current time
                $cluster = Cluster::create([
                    'name' => $clusterName,
                    'upload_time' => now()
                ]);

                $clusterData[] = [
                    'name' => $clusterName,
                    'upload_time' => $cluster->upload_time
                ];
            }
        }

        return response()->json($clusterData);
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

    // Helm API endpoints

    public function getHelmCharts($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getHelmCharts());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getHelmReleases($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            return response()->json($service->getHelmReleases());
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

    /**
     * Handle the cluster selection.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selectCluster(Request $request)
    {
        $clusterName = $request->input('cluster_name');

        if ($clusterName) {
            // Store the selected cluster in the session
            session(['selectedCluster' => $clusterName]);
        }

        // Redirect back to the previous page
        return redirect()->back();
    }

    /**
     * Handle switching between cluster tabs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchCluster(Request $request)
    {
        $clusterName = $request->input('cluster_name');
        $selectedClusters = session('selectedClusters', []);

        if ($clusterName && in_array($clusterName, $selectedClusters)) {
            // Update active cluster tab and legacy session
            session(['activeClusterTab' => $clusterName]);
            session(['selectedCluster' => $clusterName]);
        }

        // Redirect back to the previous page
        return redirect()->back();
    }

    /**
     * Handle closing a cluster tab.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function closeCluster(Request $request)
    {
        $clusterName = $request->input('cluster_name');
        $selectedClusters = session('selectedClusters', []);
        $activeClusterTab = session('activeClusterTab', null);

        // Remove cluster from selected clusters
        $selectedClusters = array_values(array_filter($selectedClusters, function($cluster) use ($clusterName) {
            return $cluster !== $clusterName;
        }));

        // If this was the active tab, switch to another tab or clear
        if ($activeClusterTab === $clusterName) {
            $activeClusterTab = !empty($selectedClusters) ? $selectedClusters[0] : null;
        }

        // Update session
        session(['selectedClusters' => $selectedClusters]);
        session(['activeClusterTab' => $activeClusterTab]);
        session(['selectedCluster' => $activeClusterTab]);

        // If no clusters left, redirect to dashboard to show cluster selection
        if (empty($selectedClusters)) {
            return redirect()->route('dashboard-kubernetes');
        }

        // Redirect back to the previous page
        return redirect()->back();
    }

    /**
     * Show the upload modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showUploadModal(Request $request)
    {
        // Store a flag in the session to show the upload modal
        session(['showUploadModal' => true]);

        // Redirect back to the previous page
        return redirect()->back();
    }

    /**
     * Close the upload modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function closeUploadModal(Request $request)
    {
        // Clear the session flag
        session()->forget('showUploadModal');

        // Redirect back to the previous page
        return redirect()->back();
    }

    /**
     * Edit cluster name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editCluster(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/'
        ]);

        try {
            $cluster = Cluster::findOrFail($id);
            $oldName = $cluster->name;
            $newName = $request->input('name');

            // Check if new name already exists
            if ($newName !== $oldName && Cluster::where('name', $newName)->exists()) {
                return redirect()->back()->with('error', 'A cluster with this name already exists.');
            }

            // Get kubeconfig paths
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
            $oldFilePath = $kubeconfigPath . '/' . $oldName;
            $newFilePath = $kubeconfigPath . '/' . $newName;

            // Rename the kubeconfig file if it exists
            if (file_exists($oldFilePath)) {
                if (!rename($oldFilePath, $newFilePath)) {
                    return redirect()->back()->with('error', 'Failed to rename kubeconfig file.');
                }
            }

            // Update cluster name in database
            $cluster->update(['name' => $newName]);

            // Update session if this was the selected cluster
            if (session('selectedCluster') === $oldName) {
                session(['selectedCluster' => $newName]);
            }

            return redirect()->back()->with('success', 'Cluster name updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update cluster: ' . $e->getMessage());
        }
    }

    /**
     * Replace kubeconfig file for existing cluster.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function replaceKubeconfig(Request $request, $id)
    {
        $request->validate([
            'kubeconfig' => ['required', 'file', new YamlFileValidation()]
        ]);

        try {
            $cluster = Cluster::findOrFail($id);
            $file = $request->file('kubeconfig');

            // Get kubeconfig path
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
            $filePath = $kubeconfigPath . '/' . $cluster->name;

            // Ensure directory exists
            if (!file_exists($kubeconfigPath)) {
                mkdir($kubeconfigPath, 0755, true);
            }

            // Save the new kubeconfig file
            $content = file_get_contents($file->getRealPath());
            file_put_contents($filePath, $content);

            // Update upload time in database
            $cluster->update(['upload_time' => now()]);

            return redirect()->back()->with('success', 'Kubeconfig file updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update kubeconfig: ' . $e->getMessage());
        }
    }

    /**
     * Delete cluster and its kubeconfig file.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCluster($id)
    {
        try {
            $cluster = Cluster::findOrFail($id);
            $clusterName = $cluster->name;

            // Get kubeconfig path
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
            $filePath = $kubeconfigPath . '/' . $clusterName;

            // Delete kubeconfig file if it exists
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    return redirect()->back()->with('error', 'Failed to delete kubeconfig file.');
                }
            }

            // Delete cluster from database
            $cluster->delete();

            // Clear session if this was the selected cluster
            if (session('selectedCluster') === $clusterName) {
                session()->forget('selectedCluster');
            }

            return redirect()->back()->with('success', 'Cluster deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete cluster: ' . $e->getMessage());
        }
    }
}

