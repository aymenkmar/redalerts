<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\KubectlAiService;

class KubectlAiChatbot extends Component
{
    public $isOpen = false;
    public $messages = [];
    public $inputMessage = '';
    public $isLoading = false;
    public $conversationId = null;
    public $showSettings = false;
    public $config = null;

    protected $listeners = [
        'clusterTabsUpdated' => 'handleClusterChange',
        'clusterChanged' => 'handleClusterChange'
    ];

    public function mount()
    {
        $this->loadConfig();
        $this->initializeChat();
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        
        if ($this->isOpen && empty($this->messages)) {
            $this->initializeChat();
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->inputMessage)) || $this->isLoading) {
            return;
        }

        $selectedCluster = session('activeClusterTab') ?? session('selectedCluster');
        
        if (!$selectedCluster) {
            $this->addMessage('bot', 'No cluster selected. Please select a cluster first.');
            return;
        }

        $userMessage = trim($this->inputMessage);
        $this->addMessage('user', $userMessage);
        $this->inputMessage = '';
        $this->isLoading = true;

        try {
            // Use the service directly instead of HTTP call
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $selectedCluster;

            if (!file_exists($kubeconfigPath)) {
                $this->addMessage('bot', 'Error: Cluster configuration not found.');
                return;
            }

            $kubectlAiService = new KubectlAiService($kubeconfigPath);
            $result = $kubectlAiService->query($userMessage);

            if ($result['success']) {
                $this->addMessage('bot', $result['response']);
                // Generate a conversation ID if we don't have one
                if (!$this->conversationId) {
                    $this->conversationId = uniqid('chat_', true);
                }
            } else {
                $this->addMessage('bot', 'Error: ' . $result['error']);
            }
        } catch (\Exception $e) {
            Log::error('kubectl-ai chat error in Livewire', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'cluster' => $selectedCluster
            ]);
            
            $this->addMessage('bot', 'Sorry, I encountered an error. Please try again.');
        } finally {
            $this->isLoading = false;
            // Ensure scroll to bottom after loading finishes
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function clearChat()
    {
        $this->messages = [];
        $this->conversationId = null;

        // Only add welcome message, no debug messages
        $selectedCluster = session('activeClusterTab') ?? session('selectedCluster');

        if ($selectedCluster) {
            $welcomeMessage = "Hello! I'm your Kubernetes AI assistant. I'm currently connected to cluster \"{$selectedCluster}\". You can ask me questions about your cluster, request information about pods, deployments, services, or ask me to perform kubectl operations. How can I help you today?";
            $this->addMessage('bot', $welcomeMessage);
        }
    }



    public function toggleSettings()
    {
        $this->showSettings = !$this->showSettings;
    }

    public function handleClusterChange()
    {
        // Clear current conversation when cluster changes
        $this->messages = [];
        $this->conversationId = null;
        
        if ($this->isOpen) {
            $this->initializeChat();
        }
    }

    private function loadConfig()
    {
        try {
            // Use the service directly instead of HTTP call
            $kubectlAiService = new KubectlAiService();
            $configData = $kubectlAiService->getConfiguration();
            $this->config = $configData['default_config'];
        } catch (\Exception $e) {
            Log::error('Failed to load kubectl-ai config in Livewire', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function initializeChat($includeDebugMessages = true)
    {
        $selectedCluster = session('activeClusterTab') ?? session('selectedCluster');

        if ($selectedCluster) {
            $welcomeMessage = "Hello! I'm your Kubernetes AI assistant. I'm currently connected to cluster \"{$selectedCluster}\". You can ask me questions about your cluster, request information about pods, deployments, services, or ask me to perform kubectl operations. How can I help you today?";
            $this->addMessage('bot', $welcomeMessage);

            // Debug messages disabled for production use
            // You can enable this temporarily by changing false to true for scrollbar testing
            if (false && env('APP_DEBUG', false) && $includeDebugMessages) {
                $this->addMessage('user', 'Can you show me all the nodes in this cluster?');
                $this->addMessage('bot', 'Here are the nodes in your cluster: k8s-worker-node-1, k8s-worker-node-2, k8s-worker-node-4, and master-node. All nodes are in Ready status.');
                $this->addMessage('user', 'What about the pods?');
                $this->addMessage('bot', 'I can show you the pods. Let me get that information for you...');
                $this->addMessage('user', 'How many deployments are running?');
                $this->addMessage('bot', 'There are several deployments running in your cluster. Would you like me to list them all or focus on a specific namespace?');
            }
        }
    }

    private function addMessage($type, $content)
    {
        $this->messages[] = [
            'id' => uniqid(),
            'type' => $type,
            'content' => $content,
            'timestamp' => now()->format('H:i:s'),
            'cluster' => session('activeClusterTab') ?? session('selectedCluster')
        ];

        // Trigger scroll to bottom after adding message
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        $selectedCluster = session('activeClusterTab') ?? session('selectedCluster');
        
        return view('livewire.kubectl-ai-chatbot', [
            'selectedCluster' => $selectedCluster
        ]);
    }
}
