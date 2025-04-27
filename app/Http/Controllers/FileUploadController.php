<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');

        $n8nUrl = env('N8N_URL', 'https://n8n.redalerts.tn');
        $n8nWebhookPath = env('N8N_WEBHOOK_UPLOAD_FILE', '/webhook/upload-file');

        $response = Http::attach(
            'file',
            fopen($file->getRealPath(), 'r'),
            $file->getClientOriginalName()
        )->post($n8nUrl . $n8nWebhookPath);

        return response()->json([
            'message' => 'Upload sent to n8n',
            'n8n_response' => $response->json()
        ]);
    }

    public function checkClusterExists($clusterName)
    {
        $path = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

        if (!is_dir($path)) {
            return response()->json(['error' => 'Kubeconfig path not found'], 500);
        }

        $clusterPath = $path . '/' . $clusterName;
        $exists = file_exists($clusterPath);

        return response()->json([
            'exists' => $exists,
            'clusterName' => $clusterName
        ]);
    }

    public function uploadKubeconfig(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'clusterName' => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/'
        ]);

        $file = $request->file('file');
        $clusterName = $request->input('clusterName');

        try {
            // Get the kubeconfig path from environment
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

            // Ensure the directory exists
            if (!file_exists($kubeconfigPath)) {
                mkdir($kubeconfigPath, 0755, true);
            }

            // Check if a file with the same name already exists
            $filePath = $kubeconfigPath . '/' . $clusterName;
            $fileExists = file_exists($filePath);

            // Save the file
            $content = file_get_contents($file->getRealPath());
            file_put_contents($filePath, $content);

            // Log the successful upload
            Log::info("Kubeconfig file uploaded successfully: {$clusterName}");

            return response()->json([
                'success' => true,
                'message' => $fileExists
                    ? "Cluster '{$clusterName}' has been updated successfully."
                    : "Cluster '{$clusterName}' has been uploaded successfully.",
                'clusterName' => $clusterName
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading kubeconfig: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading kubeconfig: ' . $e->getMessage()
            ], 500);
        }
    }
}
