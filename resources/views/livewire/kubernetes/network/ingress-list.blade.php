<div>
    <h1 class="text-2xl font-bold mb-6">Ingresses</h1>

    @if($error)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mb-6">
        @include('livewire.kubernetes.components.namespace-filter')
        @include('livewire.kubernetes.components.search-input')
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($loading)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Namespace</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hosts</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ports</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ingresses as $ingress)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $ingress['metadata']['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ingress['metadata']['namespace'] ?? 'default' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if(isset($ingress['spec']['rules']))
                                @php
                                    $hosts = [];
                                    foreach ($ingress['spec']['rules'] as $rule) {
                                        if (isset($rule['host'])) {
                                            $hosts[] = $rule['host'];
                                        }
                                    }
                                @endphp
                                {{ implode(', ', $hosts) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if(isset($ingress['status']['loadBalancer']['ingress']))
                                @php
                                    $addresses = [];
                                    foreach ($ingress['status']['loadBalancer']['ingress'] as $item) {
                                        if (isset($item['ip'])) {
                                            $addresses[] = $item['ip'];
                                        } elseif (isset($item['hostname'])) {
                                            $addresses[] = $item['hostname'];
                                        }
                                    }
                                @endphp
                                {{ implode(', ', $addresses) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if(isset($ingress['spec']['tls']))
                                443
                            @else
                                80
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $this->formatAge($ingress['metadata']['creationTimestamp']) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No ingresses found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @include('livewire.kubernetes.components.pagination')
        @endif
    </div>
</div>
