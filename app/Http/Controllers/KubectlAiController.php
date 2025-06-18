<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\KubectlAiService;

class KubectlAiController extends Controller
{
    /**
     * Handle kubectl-ai chat requests
     */
    public function chat(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'cluster' => 'required|string|max:255',
            'conversation_id' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        $message = $request->input('message');
        $clusterName = $request->input('cluster');
        $conversationId = $request->input('conversation_id', uniqid('chat_', true));

        try {
            // Validate cluster exists
            $kubeconfigPath = $this->getKubeconfigPath($clusterName);
            if (!file_exists($kubeconfigPath)) {
                return response()->json([
                    'error' => 'Cluster not found',
                    'cluster' => $clusterName
                ], 404);
            }

            // Create kubectl-ai service instance
            $kubectlAiService = new KubectlAiService($kubeconfigPath);

            // Execute query
            $result = $kubectlAiService->query($message);

            if (!$result['success']) {
                return response()->json([
                    'error' => $result['error'],
                    'type' => $result['type'] ?? 'unknown',
                    'cluster' => $clusterName
                ], 500);
            }

            return response()->json([
                'success' => true,
                'response' => $result['response'],
                'conversation_id' => $conversationId,
                'cluster' => $clusterName,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('kubectl-ai chat error', [
                'message' => $message,
                'cluster' => $clusterName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to process request',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available models for kubectl-ai
     */
    public function getModels(): JsonResponse
    {
        try {
            $kubectlAiService = new KubectlAiService();
            $models = $kubectlAiService->getAvailableModels();

            return response()->json([
                'success' => true,
                'models' => $models
            ]);

        } catch (\Exception $e) {
            Log::error('kubectl-ai models error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get models',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kubectl-ai configuration
     */
    public function getConfig(): JsonResponse
    {
        try {
            $kubectlAiService = new KubectlAiService();
            $config = $kubectlAiService->getConfiguration();

            return response()->json([
                'success' => true,
                'config' => $config['default_config'],
                'validation' => $config['validation'],
                'available_providers' => $config['available_providers']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get configuration',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate kubectl-ai installation and configuration for a specific cluster
     */
    public function validateCluster(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cluster' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        $clusterName = $request->input('cluster');

        try {
            $kubeconfigPath = $this->getKubeconfigPath($clusterName);
            $kubectlAiService = new KubectlAiService($kubeconfigPath);
            $validation = $kubectlAiService->validateInstallation();

            return response()->json([
                'success' => true,
                'cluster' => $clusterName,
                'validation' => $validation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to validate cluster',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test kubectl-ai installation and basic functionality
     */
    public function test(): JsonResponse
    {
        try {
            $kubectlAiService = new KubectlAiService();
            $validation = $kubectlAiService->validateInstallation();

            return response()->json([
                'success' => true,
                'validation' => $validation,
                'message' => 'kubectl-ai integration test completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Test failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kubeconfig path for a cluster
     */
    private function getKubeconfigPath(string $clusterName): string
    {
        $kubeconfigDir = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
        return $kubeconfigDir . '/' . $clusterName;
    }
}
