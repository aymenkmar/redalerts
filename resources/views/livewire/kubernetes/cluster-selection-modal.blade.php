<div>
    <!-- Success Message -->
    @if($success)
    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ $success }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Error Message -->
    @if($error)
    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">{{ $error }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Clusters -->
    @if(count($clusters) > 0)
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Available Clusters</h3>
        <p class="text-sm text-gray-600 mb-4">Click on a cluster to add it to your workspace. Multiple clusters can be opened simultaneously.</p>
        <div class="grid gap-3">
            @foreach($clusters as $cluster)
            @php
                $selectedClusters = session('selectedClusters', []);
                $activeCluster = session('activeClusterTab');
                $isOpened = in_array($cluster['name'], $selectedClusters);
                $isActive = $activeCluster === $cluster['name'];
            @endphp
            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 hover:shadow-md transition-all duration-200 cursor-pointer {{ $isOpened ? 'border-green-500 bg-green-50 shadow-md' : 'bg-white hover:bg-gray-50' }} {{ $isActive ? 'border-red-500 bg-red-50' : '' }}"
                 wire:click="selectCluster('{{ $cluster['name'] }}')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br {{ $isOpened ? 'from-green-500 to-green-600' : 'from-blue-500 to-blue-600' }} rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $cluster['name'] }}</h4>
                            <p class="text-xs text-gray-500">
                                Added {{ \Carbon\Carbon::createFromTimestamp($cluster['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($isActive)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Active
                        </span>
                        @elseif($isOpened)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Opened
                        </span>
                        @endif
                        <div class="flex-shrink-0">
                            @if($isOpened)
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Upload New Cluster Section -->
    <div class="border-t border-gray-200 pt-6">
        <button
            wire:click="toggleUploadForm"
            class="w-full flex items-center justify-center px-4 py-3 border border-red-300 rounded-md shadow-sm bg-red-50 text-sm font-medium text-red-700 hover:bg-red-100 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($showUploadForm)
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                @endif
            </svg>
            {{ $showUploadForm ? 'Hide Upload Form' : 'Upload New Cluster' }}
        </button>

        @if($showUploadForm)
        <div class="mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <form wire:submit.prevent="uploadKubeconfig">
                <div class="space-y-4">
                    <div>
                        <label for="clusterName" class="block text-sm font-medium text-gray-700">Cluster Name</label>
                        <input
                            type="text"
                            id="clusterName"
                            wire:model="clusterName"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                            placeholder="Enter a name for your cluster"
                            required
                        >
                        @error('clusterName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="kubeconfig" class="block text-sm font-medium text-gray-700">Kubeconfig File</label>
                        <input
                            type="file"
                            id="kubeconfig"
                            wire:model="kubeconfig"
                            accept=".yaml,.yml,.config"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                            required
                        >
                        @error('kubeconfig') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="toggleUploadForm"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            :disabled="loading"
                        >
                            @if($loading)
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Uploading...
                            @else
                                Upload & Select
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- No Clusters Message -->
    @if(count($clusters) === 0 && !$showUploadForm)
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No clusters available</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by uploading a kubeconfig file.</p>
    </div>
    @endif

    <script>
        // Listen for page refresh event
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refreshPage', () => {
                setTimeout(() => {
                    window.location.reload();
                }, 1000); // Small delay to show success message
            });

            // Listen for cluster change events to close modal
            Livewire.on('clusterChanged', () => {
                // Find and close any open modals
                const modals = document.querySelectorAll('[x-data*="showClusterModal"]');
                modals.forEach(modal => {
                    // Trigger Alpine.js to close the modal
                    if (modal._x_dataStack && modal._x_dataStack[0]) {
                        modal._x_dataStack[0].showClusterModal = false;
                    }
                });
            });
        });
    </script>
</div>
