<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
            </svg>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Node Details</h2>
                @if($nodeName)
                    <p class="text-sm text-gray-600">{{ $nodeName }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button
                wire:click="refreshNodeDetails"
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors duration-200"
                title="Refresh node details"
                :disabled="loading"
            >
                <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <button
                wire:click="closeDetails"
                class="p-2 text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors duration-200 shadow-sm"
                title="Close panel"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto">
        @if($loading)
            <div class="flex items-center justify-center h-32">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
                <span class="ml-3 text-gray-600">Loading node details...</span>
            </div>
        @elseif($error)
            <div class="p-4">
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">{{ $error }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($nodeDetails)
            <div class="p-4 space-y-6">
                <!-- Properties Section -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-medium text-gray-900">Properties</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-1 gap-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created:</span>
                                <span class="text-gray-900">{{ $this->formatAge($this->getNodeProperty('metadata.creationTimestamp')) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Name:</span>
                                <span class="text-gray-900">{{ $this->getNodeProperty('metadata.name') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Labels:</span>
                                <span class="text-gray-900">{{ count($this->getNodeLabels()) }} Labels</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Annotations:</span>
                                <span class="text-gray-900">{{ count($this->getNodeAnnotations()) }} Annotations</span>
                            </div>
                        </div>
                        
                        <!-- Addresses -->
                        @php
                            $addresses = $this->getNodeProperty('status.addresses', []);
                        @endphp
                        @if(is_array($addresses) && count($addresses) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Addresses</h4>
                                @foreach($addresses as $address)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $address['type'] ?? 'Unknown' }}:</span>
                                        <span class="text-gray-900">{{ $address['address'] ?? 'N/A' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- System Info -->
                        @php
                            $nodeInfo = $this->getNodeProperty('status.nodeInfo', []);
                        @endphp
                        @if(is_array($nodeInfo) && count($nodeInfo) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">System Information</h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">OS:</span>
                                        <span class="text-gray-900">{{ ($nodeInfo['osImage'] ?? 'Unknown') . ' (' . ($nodeInfo['architecture'] ?? 'unknown') . ')' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">OS Image:</span>
                                        <span class="text-gray-900">{{ $nodeInfo['osImage'] ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Kernel version:</span>
                                        <span class="text-gray-900">{{ $nodeInfo['kernelVersion'] ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Container runtime:</span>
                                        <span class="text-gray-900">{{ $nodeInfo['containerRuntimeVersion'] ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Kubelet version:</span>
                                        <span class="text-gray-900">{{ $nodeInfo['kubeletVersion'] ?? 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Taints -->
                        @php
                            $taints = $this->getNodeTaints();
                        @endphp
                        @if(count($taints) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Taints</h4>
                                @foreach($taints as $taint)
                                    <div class="text-sm text-gray-900">
                                        {{ $taint['key'] }}{{ $taint['value'] ? '=' . $taint['value'] : '' }}:{{ $taint['effect'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Conditions -->
                        @php
                            $conditions = $this->getNodeConditions();
                        @endphp
                        @if(count($conditions) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Conditions</h4>
                                @foreach($conditions as $condition)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $condition['type'] }}:</span>
                                        <span class="text-gray-900 {{ $condition['status'] === 'True' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $condition['status'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Capacity Section -->
                @php
                    $capacity = $this->getNodeProperty('status.capacity', []);
                @endphp
                @if(is_array($capacity) && count($capacity) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-sm font-medium text-gray-900">Capacity</h3>
                        </div>
                        <div class="p-4 space-y-2 text-sm">
                            @if(isset($capacity['cpu']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">CPU:</span>
                                    <span class="text-gray-900">{{ $this->formatCpuCores($capacity['cpu']) }}</span>
                                </div>
                            @endif
                            @if(isset($capacity['memory']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Memory:</span>
                                    <span class="text-gray-900">{{ $this->formatBytes($capacity['memory']) }}</span>
                                </div>
                            @endif
                            @if(isset($capacity['ephemeral-storage']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ephemeral Storage:</span>
                                    <span class="text-gray-900">{{ $this->formatBytes($capacity['ephemeral-storage']) }}</span>
                                </div>
                            @endif
                            @if(isset($capacity['pods']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pods:</span>
                                    <span class="text-gray-900">{{ $capacity['pods'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Allocatable Section -->
                @php
                    $allocatable = $this->getNodeProperty('status.allocatable', []);
                @endphp
                @if(is_array($allocatable) && count($allocatable) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-sm font-medium text-gray-900">Allocatable</h3>
                        </div>
                        <div class="p-4 space-y-2 text-sm">
                            @if(isset($allocatable['cpu']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">CPU:</span>
                                    <span class="text-gray-900">{{ $this->formatCpuCores($allocatable['cpu']) }}</span>
                                </div>
                            @endif
                            @if(isset($allocatable['memory']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Memory:</span>
                                    <span class="text-gray-900">{{ $this->formatBytes($allocatable['memory']) }}</span>
                                </div>
                            @endif
                            @if(isset($allocatable['ephemeral-storage']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ephemeral Storage:</span>
                                    <span class="text-gray-900">{{ $this->formatBytes($allocatable['ephemeral-storage']) }}</span>
                                </div>
                            @endif
                            @if(isset($allocatable['pods']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pods:</span>
                                    <span class="text-gray-900">{{ $allocatable['pods'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Pods Section -->
                @if(count($podsOnNode) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-sm font-medium text-gray-900">Pods ({{ count($podsOnNode) }})</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Namespace</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ready</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($podsOnNode as $pod)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                                {{ $pod['metadata']['name'] ?? 'Unknown' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $pod['metadata']['namespace'] ?? 'default' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @php
                                                    $containerStatuses = $pod['status']['containerStatuses'] ?? [];
                                                    $readyCount = collect($containerStatuses)->where('ready', true)->count();
                                                    $totalCount = count($containerStatuses);
                                                @endphp
                                                {{ $readyCount }}/{{ $totalCount }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getPodStatusClass($pod) }}">
                                                    {{ $this->getPodStatus($pod) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Events Section -->
                @if(count($nodeEvents) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-sm font-medium text-gray-900">Events ({{ count($nodeEvents) }})</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($nodeEvents as $event)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getEventTypeClass($event) }}">
                                                    {{ $this->getEventType($event) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $event['reason'] ?? 'Unknown' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $event['message'] ?? 'No message' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500">
                                                {{ $this->formatAge($event['lastTimestamp'] ?? $event['firstTimestamp'] ?? '') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="flex items-center justify-center h-32 text-gray-500">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    <p>Select a node to view details</p>
                </div>
            </div>
        @endif
    </div>
</div>
