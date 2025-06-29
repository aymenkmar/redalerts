<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KubernetesService;
use Illuminate\Support\Facades\Log;

class PodLogsController extends Controller
{
    public function getLogs(Request $request)
    {
        try {
            $request->validate([
                'cluster' => 'required|string',
                'namespace' => 'required|string',
                'pod' => 'required|string',
                'container' => 'nullable|string',
                'lines' => 'nullable|integer|min:1|max:10000',
                'follow' => 'nullable|boolean'
            ]);

            $cluster = $request->input('cluster');
            $namespace = $request->input('namespace');
            $pod = $request->input('pod');
            $container = $request->input('container');
            $lines = $request->input('lines', 1000);
            $follow = $request->input('follow', false);

            // Get kubeconfig path
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $cluster;

            if (!file_exists($kubeconfigPath)) {
                return response()->json(['error' => 'Kubeconfig file not found'], 404);
            }

            $service = new KubernetesService($kubeconfigPath);
            
            // Get pod logs
            $logs = $service->getPodLogs($namespace, $pod, $container, $lines, $follow);

            Log::info('Pod logs retrieved', [
                'cluster' => $cluster,
                'namespace' => $namespace,
                'pod' => $pod,
                'container' => $container,
                'lines_requested' => $lines,
                'logs_length' => strlen($logs)
            ]);

            return response()->json([
                'logs' => $logs,
                'pod' => $pod,
                'namespace' => $namespace,
                'container' => $container,
                'lines' => $lines
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get pod logs', [
                'error' => $e->getMessage(),
                'cluster' => $request->input('cluster'),
                'namespace' => $request->input('namespace'),
                'pod' => $request->input('pod')
            ]);

            return response()->json([
                'error' => 'Failed to get pod logs: ' . $e->getMessage()
            ], 500);
        }
    }



    public function getPodContainers(Request $request)
    {
        try {
            $request->validate([
                'cluster' => 'required|string',
                'namespace' => 'required|string',
                'pod' => 'required|string'
            ]);

            $cluster = $request->input('cluster');
            $namespace = $request->input('namespace');
            $pod = $request->input('pod');

            // Get kubeconfig path
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $cluster;

            if (!file_exists($kubeconfigPath)) {
                return response()->json(['error' => 'Kubeconfig file not found'], 404);
            }

            $service = new KubernetesService($kubeconfigPath);

            // Get pod details to extract containers
            $podDetails = $service->getPodDetails($namespace, $pod);
            
            $containers = [];
            if (isset($podDetails['spec']['containers'])) {
                foreach ($podDetails['spec']['containers'] as $container) {
                    $containers[] = [
                        'name' => $container['name'],
                        'image' => $container['image'] ?? 'unknown'
                    ];
                }
            }

            return response()->json([
                'containers' => $containers,
                'pod' => $pod,
                'namespace' => $namespace
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get pod containers', [
                'error' => $e->getMessage(),
                'cluster' => $request->input('cluster'),
                'namespace' => $request->input('namespace'),
                'pod' => $request->input('pod')
            ]);

            return response()->json([
                'error' => 'Failed to get pod containers: ' . $e->getMessage()
            ], 500);
        }
    }
}
