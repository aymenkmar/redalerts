<div>
    @if(count($selectedClusters) > 0)
    <div class="flex items-center space-x-2">
        @foreach($selectedClusters as $clusterName)
        <div class="flex items-center">
            @if(count($selectedClusters) > 1)
            <form action="{{ route('kubernetes.switch-cluster') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="cluster_name" value="{{ $clusterName }}">
                <button
                    type="submit"
                    class="px-3 py-1 text-sm font-medium rounded transition-colors duration-200 {{ $activeClusterTab === $clusterName ? 'bg-red-600 text-white' : 'text-gray-600 hover:text-red-600 hover:bg-red-50' }}"
                >
                    {{ $clusterName }}
                </button>
            </form>
            @else
            <!-- Single cluster - show as active but not clickable -->
            <span class="px-3 py-1 text-sm font-medium rounded bg-red-600 text-white">
                {{ $clusterName }}
            </span>
            @endif
            <!-- Always show close button -->
            <form action="{{ route('kubernetes.close-cluster') }}" method="POST" class="inline ml-1">
                @csrf
                <input type="hidden" name="cluster_name" value="{{ $clusterName }}">
                <button
                    type="submit"
                    class="p-1 text-gray-400 hover:text-red-600 transition-colors duration-200"
                    title="Close cluster tab"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
        </div>
        @endforeach

        <!-- Add New Cluster Button -->
        <button
            wire:click="addNewCluster"
            class="p-1 text-gray-400 hover:text-red-600 transition-colors duration-200 ml-2"
            title="Add another cluster"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>
    @endif
</div>
