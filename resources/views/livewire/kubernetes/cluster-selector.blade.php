<div class="relative">
    <div class="flex items-center">
        <span class="text-sm text-gray-500 mr-2">Cluster:</span>
        <button
            wire:click="toggleDropdown"
            class="flex items-center space-x-1 focus:outline-none"
        >
            <span class="text-sm font-medium text-red-600">
                {{ $selectedCluster ?? 'Select a cluster' }}
            </span>
            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    @if($showDropdown)
    <div class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded shadow-lg z-50">
        <div class="max-h-60 overflow-y-auto">
            @if(count($clusters) > 0)
                @foreach($clusters as $cluster)
                <button
                    wire:click="selectCluster('{{ $cluster['name'] }}')"
                    class="w-full text-left px-4 py-2 hover:bg-gray-100 {{ $selectedCluster === $cluster['name'] ? 'bg-red-50 text-red-600 font-medium' : 'text-gray-700' }}"
                >
                    {{ $cluster['name'] }}
                </button>
                @endforeach
            @else
                <div class="px-4 py-2 text-gray-500">No clusters available</div>
            @endif
        </div>
        <div class="border-t border-gray-200">
            <button
                wire:click="toggleUploadModal"
                class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 font-medium"
            >
                Upload New Cluster
            </button>
        </div>
    </div>
    @endif

    @if($showUploadModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Upload Kubeconfig</h2>
                <button wire:click="toggleUploadModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <livewire:kubernetes.cluster-upload />
        </div>
    </div>
    @endif
</div>
