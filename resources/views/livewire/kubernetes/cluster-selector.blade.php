<div class="relative">
    <!-- Flash Messages -->
    @if(session()->has('success'))
    <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    <div class="flex items-center space-x-3">
        @php
            $selectedClusters = session('selectedClusters', []);
            $activeClusterTab = session('activeClusterTab', null);
        @endphp

        <!-- Cluster Management Dropdown -->
        @if(!empty($selectedClusters))
        <button
            wire:click="toggleDropdown"
            class="flex items-center space-x-2 px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-200"
        >
            <span class="text-sm font-medium">Cluster Management</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        @endif
    </div>

    @if($showDropdown)
    <div
        class="cluster-dropdown-menu absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded shadow-lg"
        style="z-index: 45;"
    >
        <div class="px-4 py-2 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">Cluster Management</h3>
        </div>
        <div class="max-h-60 overflow-y-auto">
            @if(count($clusters) > 0)
                @foreach($clusters as $cluster)
                <div class="flex items-center justify-between hover:bg-gray-100 px-4 py-2">
                    <div class="flex-1">
                        <span class="text-sm text-gray-700">{{ $cluster['name'] }}</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button
                            wire:click="openEditModal({{ $cluster['id'] }})"
                            class="p-1 text-gray-400 hover:text-blue-600"
                            title="Edit cluster name"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            wire:click="openReplaceModal({{ $cluster['id'] }})"
                            class="p-1 text-gray-400 hover:text-green-600"
                            title="Replace kubeconfig"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </button>
                        <button
                            wire:click="openDeleteModal({{ $cluster['id'] }})"
                            class="p-1 text-gray-400 hover:text-red-600"
                            title="Delete cluster"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            @else
                <div class="px-4 py-2 text-gray-500">No clusters available</div>
            @endif
        </div>
        <div class="border-t border-gray-200">
            <form action="{{ route('kubernetes.upload-modal') }}" method="POST" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 font-medium"
                >
                    Upload New Cluster
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($showUploadModal)
    <div
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        onclick="if(event.target === this) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('kubernetes.close-modal') }}';
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Upload Kubeconfig</h2>
                <form action="{{ route('kubernetes.close-modal') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </form>
            </div>
            <livewire:kubernetes.cluster-upload />
        </div>
    </div>
    @endif

    <!-- Edit Cluster Modal -->
    @if($showEditModal && $editingCluster)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Edit Cluster Name</h2>
                <button wire:click="closeEditModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('kubernetes.cluster.edit', $editingCluster['id']) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Cluster Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        wire:model="newClusterName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        required
                    >
                    @error('newClusterName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="closeEditModal"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Replace Kubeconfig Modal -->
    @if($showReplaceModal && $editingCluster)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Replace Kubeconfig</h2>
                <button wire:click="closeReplaceModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('kubernetes.cluster.upload', $editingCluster['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="kubeconfig" class="block text-sm font-medium text-gray-700 mb-2">
                        New Kubeconfig File for "{{ $editingCluster['name'] }}"
                    </label>
                    <input
                        type="file"
                        id="kubeconfig"
                        name="kubeconfig"
                        accept=".yaml,.yml,.config"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        required
                    >
                    @error('replacementFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="closeReplaceModal"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        Replace File
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Cluster Modal -->
    @if($showDeleteModal && $clusterToDelete)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Delete Cluster</h2>
                <button wire:click="closeDeleteModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mb-6">
                <p class="text-gray-700">
                    Are you sure you want to delete the cluster <strong>"{{ $clusterToDelete['name'] }}"</strong>?
                </p>
                <p class="text-red-600 text-sm mt-2">
                    This will permanently delete the kubeconfig file from the server and remove the cluster from the database. This action cannot be undone.
                </p>
            </div>
            <div class="flex justify-end space-x-3">
                <button
                    wire:click="closeDeleteModal"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                >
                    Cancel
                </button>
                <form action="{{ route('kubernetes.cluster.delete', $clusterToDelete['id']) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Delete Cluster
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
