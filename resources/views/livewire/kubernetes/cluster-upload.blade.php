<div>
    <form wire:submit.prevent="checkClusterExists">
        <div class="mb-4">
            <label for="clusterName" class="block text-sm font-medium text-gray-700 mb-1">Cluster Name</label>
            <input 
                type="text" 
                id="clusterName" 
                wire:model="clusterName" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="Enter cluster name"
                {{ $loading ? 'disabled' : '' }}
            >
            @error('clusterName') 
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
            @enderror
            <p class="mt-1 text-xs text-gray-500">Use only letters, numbers, underscores, and hyphens</p>
        </div>

        <div class="mb-4">
            <label for="kubeconfig" class="block text-sm font-medium text-gray-700 mb-1">Kubeconfig File</label>
            <div class="flex items-center">
                <label class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                    <span>{{ $kubeconfig ? $kubeconfig->getClientOriginalName() : 'Select Kubeconfig File' }}</span>
                    <input
                        type="file"
                        id="kubeconfig"
                        wire:model="kubeconfig"
                        class="sr-only"
                        accept=".yml,.yaml"
                        {{ $loading ? 'disabled' : '' }}
                    >
                </label>
            </div>
            @error('kubeconfig')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Only YAML files (.yml, .yaml) or kubeconfig files without extension are allowed</p>
        </div>

        @if($error)
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                {{ $error }}
            </div>
        @endif

        @if($success)
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                {{ $success }}
            </div>
        @endif

        <div class="flex justify-end">
            <button 
                type="submit" 
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                {{ $loading ? 'disabled' : '' }}
            >
                @if($loading)
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Uploading...
                @else
                    Upload Kubeconfig
                @endif
            </button>
        </div>
    </form>

    @if($showConfirmDialog)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Confirm Overwrite</h2>
            <p class="text-gray-600 mb-6">A cluster with the name "{{ $clusterName }}" already exists. Do you want to overwrite it?</p>
            <div class="flex justify-end space-x-3">
                <button 
                    wire:click="cancelUpload"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button 
                    wire:click="confirmUpload"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Overwrite
                </button>
            </div>
        </div>
    </div>
    @endif
</div>


