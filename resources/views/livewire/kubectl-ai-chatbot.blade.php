<div class="fixed bottom-4 right-4 z-50" x-data="{
    scrollToBottom() {
        this.$nextTick(() => {
            setTimeout(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        });
    }
}">
    <style>
        /* Custom scrollbar styles for the chat messages */
        .chat-messages {
            /* Firefox scrollbar */
            scrollbar-width: auto;
            scrollbar-color: #9ca3af #f3f4f6;
            /* Force scrollbar to always be visible */
            overflow-y: scroll !important;
            overflow-x: hidden;
        }

        /* Webkit browsers (Chrome, Safari, Edge) */
        .chat-messages::-webkit-scrollbar {
            width: 14px;
            background: #f3f4f6;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 8px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 8px;
            border: 2px solid #f3f4f6;
            min-height: 30px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        .chat-messages::-webkit-scrollbar-thumb:active {
            background: #4b5563;
        }

        /* Ensure scrollbar corner is styled */
        .chat-messages::-webkit-scrollbar-corner {
            background: #f3f4f6;
        }

        /* Additional styling to ensure visibility */
        .chat-messages {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
    </style>
    @if($selectedCluster)
        <!-- Chat Button -->
        @if(!$isOpen)
            <button 
                wire:click="toggleChat"
                class="bg-red-600 hover:bg-red-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 hover:scale-105"
                title="Open Kubernetes AI Assistant"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </button>
        @endif

        <!-- Chat Window -->
        @if($isOpen)
            <div class="bg-white rounded-lg shadow-2xl w-96 h-[600px] flex flex-col border border-gray-200">
                <!-- Header -->
                <div class="bg-red-600 text-white p-4 rounded-t-lg flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Kubernetes AI</h3>
                            <p class="text-xs text-red-100">Cluster: {{ $selectedCluster }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            wire:click="toggleSettings"
                            class="text-red-100 hover:text-white transition-colors"
                            title="Settings"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        <button
                            wire:click="clearChat"
                            class="text-red-100 hover:text-white transition-colors text-xs"
                            title="Reset chat"
                        >
                            Reset
                        </button>
                        <button
                            wire:click="toggleChat"
                            class="text-red-100 hover:text-white transition-colors"
                            title="Close chat"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Settings Panel -->
                @if($showSettings && $config)
                    <div class="bg-gray-50 p-3 border-b text-xs">
                        <div class="space-y-1">
                            <div><strong>Model:</strong> {{ $config['model'] ?? 'N/A' }}</div>
                            <div><strong>Provider:</strong> {{ $config['provider'] ?? 'N/A' }}</div>
                            <div><strong>Max Iterations:</strong> {{ $config['maxIterations'] ?? $config['max_iterations'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                @endif

                <!-- Messages -->
                <div
                    class="flex-1 p-3 space-y-3 chat-messages min-h-0 mx-2"
                    style="max-height: 400px; height: 400px;"
                    x-ref="messagesContainer"
                    x-init="scrollToBottom()"
                    wire:updated="scrollToBottom()"
                    wire:loading.class="opacity-90"
                >
                    @foreach($messages as $message)
                        <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg p-3 {{ $message['type'] === 'user' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                <div class="flex items-start space-x-2">
                                    @if($message['type'] === 'bot')
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    @endif
                                    <div class="flex-1">
                                        <pre class="whitespace-pre-wrap text-sm font-sans">{{ $message['content'] }}</pre>
                                        <div class="text-xs opacity-70 mt-1">
                                            {{ $message['timestamp'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($isLoading)
                        <div class="flex justify-start">
                            <div class="bg-gray-100 rounded-lg p-3">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600">Thinking...</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Input -->
                <div class="p-4 border-t">
                    <form wire:submit.prevent="sendMessage" class="flex space-x-2">
                        <input
                            wire:model="inputMessage"
                            wire:keydown.enter.prevent="sendMessage"
                            type="text"
                            placeholder="Ask about your Kubernetes cluster..."
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            {{ $isLoading ? 'disabled' : '' }}
                        />
                        <button
                            type="submit"
                            {{ empty(trim($inputMessage)) || $isLoading ? 'disabled' : '' }}
                            class="bg-red-600 hover:bg-red-700 disabled:bg-gray-300 text-white rounded-lg px-3 py-2 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Listen for cluster change events
        Livewire.on('clusterTabsUpdated', () => {
            @this.call('handleClusterChange');
        });

        // Listen for scroll to bottom events
        Livewire.on('scroll-to-bottom', () => {
            setTimeout(() => {
                const container = document.querySelector('.chat-messages');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 150);
        });
    });
</script>
