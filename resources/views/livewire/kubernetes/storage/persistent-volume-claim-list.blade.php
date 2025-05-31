<div x-data="pvcList()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Persistent Volume Claims</h1>
        <button
            wire:click="refreshData"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            :disabled="loading"
        >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh
        </button>
    </div>

    @if($error)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
        @if(str_contains($error, 'select a cluster'))
        <div class="mt-3">
            <p class="text-sm">Available clusters:</p>
            @php
                $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
                $clusters = [];
                if (is_dir($kubeconfigPath)) {
                    $files = scandir($kubeconfigPath);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..' && is_file($kubeconfigPath . '/' . $file)) {
                            $clusters[] = $file;
                        }
                    }
                }
            @endphp
            @if(count($clusters) > 0)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($clusters as $cluster)
                        <button
                            onclick="selectCluster('{{ $cluster }}')"
                            class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                        >
                            {{ $cluster }}
                        </button>
                    @endforeach
                </div>
                <script>
                    function selectCluster(clusterName) {
                        fetch('/kubernetes/select-cluster', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ cluster_name: clusterName })
                        }).then(() => {
                            location.reload();
                        });
                    }
                </script>
            @else
                <p class="text-sm text-gray-600 mt-2">No clusters found. Please upload a kubeconfig file first.</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    <!-- Filters and Search -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mb-6">
        <!-- Namespace Filter -->
        <div class="relative">
            <button
                @click="showNamespaceFilter = !showNamespaceFilter"
                class="flex items-center px-4 py-2 border border-gray-300 rounded-md bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                </svg>
                Namespaces
                <span x-show="selectedNamespaces.length > 0 && !selectedNamespaces.includes('all')"
                      class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full"
                      x-text="selectedNamespaces.length">
                </span>
            </button>

            <div x-show="showNamespaceFilter" x-cloak
                 @click.away="showNamespaceFilter = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute z-10 mt-2 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                <div class="p-4">
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="selectedNamespaces"
                                   value="all"
                                   @change="if($event.target.checked) { selectedNamespaces = ['all']; } filterPVCs();"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">All Namespaces</span>
                        </label>
                        <template x-for="namespace in namespaces" :key="namespace">
                            <label class="flex items-center">
                                <input type="checkbox"
                                       x-model="selectedNamespaces"
                                       :value="namespace"
                                       @change="if($event.target.checked && selectedNamespaces.includes('all')) { selectedNamespaces = selectedNamespaces.filter(ns => ns !== 'all'); } filterPVCs();"
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700" x-text="namespace"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <input
                type="text"
                x-model="searchTerm"
                @input="filterPVCs()"
                placeholder="Search persistent volume claims..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500 relative z-0"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($loading)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
        </div>
        @else
        <!-- Loading state for client-side operations -->
        <div x-show="clientLoading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
        </div>

        <!-- Table -->
        <div x-show="!clientLoading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                            <svg class="w-4 h-4 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Namespace</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Storage class</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pods</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="pvc in paginatedPVCs" :key="pvc.metadata.name + pvc.metadata.namespace">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="pvc.metadata.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div x-show="hasPVCWarnings(pvc)" class="flex justify-center" :title="getPVCWarnings(pvc)">
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="pvc.metadata.namespace || 'default'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getStorageClass(pvc)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getCapacity(pvc)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getPods(pvc)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatAge(pvc.metadata.creationTimestamp)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getStatusColor(pvc)"
                                    x-text="getStatus(pvc)">
                                </span>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr x-show="filteredPVCs.length === 0">
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <span x-show="searchTerm || !selectedNamespaces.includes('all')">No persistent volume claims found matching your filters</span>
                            <span x-show="!searchTerm && selectedNamespaces.includes('all')">No persistent volume claims found</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Client-side Pagination -->
        <div x-show="!clientLoading && filteredPVCs.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-6 py-3 bg-white border-t border-gray-200">
            <div class="text-sm text-gray-700 mb-2 sm:mb-0">
                Showing
                <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                to
                <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredPVCs.length)"></span>
                of
                <span class="font-medium" x-text="filteredPVCs.length"></span>
                results
            </div>

            <div class="flex items-center space-x-2">
                <button @click="goToPage(1)" :disabled="currentPage <= 1" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" title="Go to first page">First</button>
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <template x-for="page in getVisiblePages()" :key="page">
                    <button @click="goToPage(page)" class="px-3 py-1 rounded border" :class="currentPage === page ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" x-text="page"></button>
                </template>
                <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= Math.ceil(filteredPVCs.length / perPage)" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                <button @click="goToPage(Math.ceil(filteredPVCs.length / perPage))" :disabled="currentPage >= Math.ceil(filteredPVCs.length / perPage)" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" title="Go to last page">Last</button>
            </div>

            <div class="hidden sm:flex items-center space-x-2 mt-2 sm:mt-0">
                <span class="text-sm text-gray-700">Items per page:</span>
                <select x-model="perPage" @change="currentPage = 1; updatePagination()" class="border-gray-300 rounded-md text-sm focus:ring-red-500 focus:border-red-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        @endif
    </div>

    <script>
        function pvcList() {
            return {
                allPVCs: @json($persistentVolumeClaims),
                namespaces: @json($namespaces),
                allPods: @json($pods),
                filteredPVCs: [],
                paginatedPVCs: [],
                searchTerm: '',
                selectedNamespaces: ['all'],
                showNamespaceFilter: false,
                clientLoading: false,
                currentPage: 1,
                perPage: 10,

                init() { this.filterPVCs(); },

                filterPVCs() {
                    this.clientLoading = true;
                    setTimeout(() => {
                        let filtered = [...this.allPVCs];
                        if (!this.selectedNamespaces.includes('all') && this.selectedNamespaces.length > 0) {
                            filtered = filtered.filter(pvc => this.selectedNamespaces.includes(pvc.metadata.namespace || 'default'));
                        }
                        if (this.searchTerm.trim()) {
                            const searchLower = this.searchTerm.toLowerCase();
                            filtered = filtered.filter(pvc => {
                                const name = (pvc.metadata.name || '').toLowerCase();
                                const namespace = (pvc.metadata.namespace || 'default').toLowerCase();
                                const status = this.getStatus(pvc).toLowerCase();
                                const storageClass = this.getStorageClass(pvc).toLowerCase();
                                const capacity = this.getCapacity(pvc).toLowerCase();
                                const warnings = this.getPVCWarnings(pvc).toLowerCase();
                                const pods = this.getPods(pvc).toLowerCase();

                                return name.includes(searchLower) || namespace.includes(searchLower) ||
                                       status.includes(searchLower) || storageClass.includes(searchLower) ||
                                       capacity.includes(searchLower) || warnings.includes(searchLower) ||
                                       pods.includes(searchLower);
                            });
                        }
                        this.filteredPVCs = filtered;
                        this.currentPage = 1;
                        this.updatePagination();
                        this.clientLoading = false;
                    }, 100);
                },

                updatePagination() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    this.paginatedPVCs = this.filteredPVCs.slice(start, end);
                },

                goToPage(page) {
                    const maxPage = Math.ceil(this.filteredPVCs.length / this.perPage);
                    if (page >= 1 && page <= maxPage) {
                        this.currentPage = page;
                        this.updatePagination();
                    }
                },

                getVisiblePages() {
                    const totalPages = Math.ceil(this.filteredPVCs.length / this.perPage);
                    const maxVisible = 5;
                    const halfVisible = Math.floor(maxVisible / 2);
                    let startPage = Math.max(1, this.currentPage - halfVisible);
                    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                    if (endPage - startPage + 1 < maxVisible) {
                        startPage = Math.max(1, endPage - maxVisible + 1);
                    }
                    const pages = [];
                    for (let i = startPage; i <= endPage; i++) {
                        pages.push(i);
                    }
                    return pages;
                },

                getStatus(pvc) {
                    return (pvc.status && pvc.status.phase) || 'Unknown';
                },

                getStatusColor(pvc) {
                    const status = this.getStatus(pvc);
                    switch(status) {
                        case 'Bound': return 'bg-green-100 text-green-800';
                        case 'Pending': return 'bg-yellow-100 text-yellow-800';
                        case 'Lost': return 'bg-red-100 text-red-800';
                        default: return 'bg-gray-100 text-gray-800';
                    }
                },

                getVolumeName(pvc) {
                    return (pvc.spec && pvc.spec.volumeName) || '—';
                },

                getCapacity(pvc) {
                    if (pvc.status && pvc.status.capacity && pvc.status.capacity.storage) {
                        return pvc.status.capacity.storage;
                    }
                    if (pvc.spec && pvc.spec.resources && pvc.spec.resources.requests && pvc.spec.resources.requests.storage) {
                        return pvc.spec.resources.requests.storage;
                    }
                    return '—';
                },

                getAccessModes(pvc) {
                    if (pvc.spec && pvc.spec.accessModes && Array.isArray(pvc.spec.accessModes)) {
                        return pvc.spec.accessModes.join(', ');
                    }
                    return '—';
                },

                getStorageClass(pvc) {
                    return (pvc.spec && pvc.spec.storageClassName) || '—';
                },

                formatAge(timestamp) {
                    if (!timestamp) return 'N/A';
                    const now = new Date();
                    const created = new Date(timestamp);
                    const diffMs = now - created;

                    // Calculate total difference in various units
                    const diffSeconds = Math.floor(diffMs / 1000);
                    const diffMinutes = Math.floor(diffMs / (1000 * 60));
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                    // Calculate years and remaining days (Lens IDE format: 2y83d)
                    const years = Math.floor(diffDays / 365);
                    const remainingDays = diffDays % 365;

                    if (years > 0) {
                        if (remainingDays > 0) {
                            return years + 'y' + remainingDays + 'd';
                        } else {
                            return years + 'y';
                        }
                    }

                    // For less than a year, show days
                    if (diffDays >= 1) {
                        return diffDays + 'd';
                    }

                    // For less than a day, show hours
                    if (diffHours >= 1) {
                        return diffHours + 'h';
                    }

                    // For less than an hour, show minutes
                    if (diffMinutes >= 1) {
                        return diffMinutes + 'm';
                    }

                    // For less than a minute, show seconds
                    return diffSeconds + 's';
                },

                getPVCWarnings(pvc) {
                    const warnings = [];
                    const status = this.getStatus(pvc);

                    // Check for problematic statuses
                    if (status === 'Pending') {
                        // Check if it's been pending for too long (more than 5 minutes)
                        const now = new Date();
                        const created = new Date(pvc.metadata.creationTimestamp);
                        const diffMinutes = Math.floor((now - created) / (1000 * 60));

                        if (diffMinutes > 5) {
                            warnings.push('Long Pending');
                        } else {
                            warnings.push('Pending');
                        }
                    } else if (status === 'Lost') {
                        warnings.push('Volume Lost');
                    }

                    // Check for conditions indicating problems
                    if (pvc.status && pvc.status.conditions) {
                        const problemConditions = pvc.status.conditions.filter(c =>
                            c.status === 'False' && ['Resizing'].includes(c.type)
                        );

                        if (problemConditions.length > 0) {
                            warnings.push(...problemConditions.map(c => c.type + ' Failed'));
                        }
                    }

                    return warnings.length > 0 ? warnings.join(', ') : '-';
                },

                hasPVCWarnings(pvc) {
                    const warnings = this.getPVCWarnings(pvc);
                    return warnings !== '-';
                },

                getPods(pvc) {
                    const pvcName = pvc.metadata.name;
                    const pvcNamespace = pvc.metadata.namespace || 'default';

                    // Find pods that use this PVC
                    const usingPods = this.allPods.filter(pod => {
                        if ((pod.metadata.namespace || 'default') !== pvcNamespace) {
                            return false;
                        }

                        // Check if any volume in the pod references this PVC
                        const volumes = pod.spec?.volumes || [];
                        return volumes.some(volume =>
                            volume.persistentVolumeClaim &&
                            volume.persistentVolumeClaim.claimName === pvcName
                        );
                    });

                    if (usingPods.length === 0) {
                        return '—';
                    }

                    // Return pod names, limit to 3 and add "..." if more
                    const podNames = usingPods.map(pod => pod.metadata.name);
                    if (podNames.length <= 3) {
                        return podNames.join(', ');
                    } else {
                        return podNames.slice(0, 3).join(', ') + '...';
                    }
                }
            }
        }
    </script>
</div>
