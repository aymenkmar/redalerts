<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Cluster;
use Illuminate\Support\Facades\Storage;

class ClusterUpload extends Component
{
    use WithFileUploads;

    public $clusterName = '';
    public $kubeconfig = null;
    public $showConfirmDialog = false;
    public $clusterExists = false;
    public $loading = false;
    public $success = null;
    public $error = null;

    protected $rules = [
        'clusterName' => 'required|regex:/^[a-zA-Z0-9_-]+$/',
        'kubeconfig' => 'required|file|max:1024', // max 1MB
    ];

    protected $messages = [
        'clusterName.required' => 'Please enter a cluster name.',
        'clusterName.regex' => 'Cluster name can only contain letters, numbers, underscores, and hyphens.',
        'kubeconfig.required' => 'Please select a kubeconfig file.',
        'kubeconfig.file' => 'The kubeconfig must be a file.',
        'kubeconfig.max' => 'The kubeconfig file size must not exceed 1MB.',
    ];

    public function checkClusterExists()
    {
        $this->validate();

        $this->loading = true;
        $this->error = null;
        $this->success = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
            $filePath = $kubeconfigPath . '/' . $this->clusterName;

            if (file_exists($filePath)) {
                $this->clusterExists = true;
                $this->showConfirmDialog = true;
            } else {
                $this->uploadKubeconfig();
            }
        } catch (\Exception $e) {
            $this->error = 'Error checking if cluster exists: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function confirmUpload()
    {
        $this->showConfirmDialog = false;
        $this->uploadKubeconfig();
    }

    public function cancelUpload()
    {
        $this->showConfirmDialog = false;
        $this->loading = false;
    }

    public function uploadKubeconfig()
    {
        $this->validate();

        $this->loading = true;
        $this->error = null;
        $this->success = null;

        try {
            // Get the kubeconfig path from environment
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

            // Ensure the directory exists
            if (!file_exists($kubeconfigPath)) {
                mkdir($kubeconfigPath, 0755, true);
            }

            // Save the file
            $content = file_get_contents($this->kubeconfig->getRealPath());
            $filePath = $kubeconfigPath . '/' . $this->clusterName;
            file_put_contents($filePath, $content);

            // Save or update cluster information in the database
            Cluster::updateOrCreate(
                ['name' => $this->clusterName],
                ['upload_time' => now()]
            );

            $successMessage = 'Kubeconfig uploaded successfully.';
            $this->success = $successMessage;
            $this->reset(['clusterName', 'kubeconfig']);

            // Notify parent component that a cluster was uploaded
            $this->dispatch('clusterUploaded');

            // Use JavaScript to close the modal and show notification
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $successMessage
            ]);

            // Also use session flash as a backup
            session()->flash('success', $successMessage);
        } catch (\Exception $e) {
            $this->error = 'Failed to upload kubeconfig: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->showConfirmDialog = false;
        }
    }

    public function render()
    {
        return view('livewire.kubernetes.cluster-upload');
    }
}
