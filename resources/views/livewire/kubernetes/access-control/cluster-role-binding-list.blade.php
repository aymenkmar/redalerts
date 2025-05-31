<div x-data="clusterRoleBindingsList()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Cluster Role Bindings</h1>
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

    <!-- Search Input (No namespace filter since cluster role bindings are cluster-scoped) -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mb-6">
        <div class="relative flex-1 max-w-md">
            <input
                type="text"
                x-model="searchTerm"
                @input="filterClusterRoleBindings()"
                placeholder="Search cluster role bindings..."
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
        @elseif(!$selectedCluster)
        <div class="p-8 text-center">
            <p class="text-gray-600">Please select a cluster from the dropdown to view cluster role bindings.</p>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bindings</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="clusterRoleBinding in paginatedClusterRoleBindings" :key="clusterRoleBinding.metadata.name">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="clusterRoleBinding.metadata.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div x-show="hasClusterRoleBindingWarnings(clusterRoleBinding)" class="flex justify-center" :title="getClusterRoleBindingWarnings(clusterRoleBinding)">
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatBindings(clusterRoleBinding)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatAge(clusterRoleBinding.metadata.creationTimestamp)"></td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr x-show="filteredClusterRoleBindings.length === 0">
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <span x-show="searchTerm">No cluster role bindings found matching your search</span>
                            <span x-show="!searchTerm">No cluster role bindings found</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Client-side Pagination -->
        <div x-show="!clientLoading && filteredClusterRoleBindings.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-6 py-3 bg-white border-t border-gray-200">
            <div class="text-sm text-gray-700 mb-2 sm:mb-0">
                Showing
                <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                to
                <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredClusterRoleBindings.length)"></span>
                of
                <span class="font-medium" x-text="filteredClusterRoleBindings.length"></span>
                results
            </div>

            <div class="flex items-center space-x-2">
                <button @click="goToPage(1)" :disabled="currentPage <= 1" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" title="Go to first page">First</button>
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <template x-for="page in getVisiblePages()" :key="page">
                    <button @click="goToPage(page)" class="px-3 py-1 rounded border" :class="currentPage === page ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" x-text="page"></button>
                </template>
                <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= Math.ceil(filteredClusterRoleBindings.length / perPage)" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                <button @click="goToPage(Math.ceil(filteredClusterRoleBindings.length / perPage))" :disabled="currentPage >= Math.ceil(filteredClusterRoleBindings.length / perPage)" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" title="Go to last page">Last</button>
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
        function clusterRoleBindingsList() {
            return {
                allClusterRoleBindings: @json($clusterRoleBindings),
                filteredClusterRoleBindings: [],
                paginatedClusterRoleBindings: [],
                searchTerm: '',
                clientLoading: false,
                currentPage: 1,
                perPage: 25,

                init() { this.filterClusterRoleBindings(); },

                filterClusterRoleBindings() {
                    this.clientLoading = true;
                    setTimeout(() => {
                        let filtered = [...this.allClusterRoleBindings];
                        if (this.searchTerm.trim()) {
                            const searchLower = this.searchTerm.toLowerCase();
                            filtered = filtered.filter(clusterRoleBinding => {
                                const name = (clusterRoleBinding.metadata.name || '').toLowerCase();
                                const bindings = this.formatBindings(clusterRoleBinding).toLowerCase();
                                const warnings = this.getClusterRoleBindingWarnings(clusterRoleBinding).toLowerCase();
                                return name.includes(searchLower) || bindings.includes(searchLower) || warnings.includes(searchLower);
                            });
                        }
                        this.filteredClusterRoleBindings = filtered;
                        this.currentPage = 1;
                        this.updatePagination();
                        this.clientLoading = false;
                    }, 100);
                },

                updatePagination() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    this.paginatedClusterRoleBindings = this.filteredClusterRoleBindings.slice(start, end);
                },

                goToPage(page) {
                    const maxPage = Math.ceil(this.filteredClusterRoleBindings.length / this.perPage);
                    if (page >= 1 && page <= maxPage) {
                        this.currentPage = page;
                        this.updatePagination();
                    }
                },

                getVisiblePages() {
                    const totalPages = Math.ceil(this.filteredClusterRoleBindings.length / this.perPage);
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

                formatBindings(clusterRoleBinding) {
                    if (!clusterRoleBinding.subjects || !Array.isArray(clusterRoleBinding.subjects)) {
                        return 'No bindings';
                    }
                    return clusterRoleBinding.subjects.map(subject => {
                        const kind = subject.kind || 'Unknown';
                        const name = subject.name || 'Unknown';
                        return `${kind}/${name}`;
                    }).join(', ');
                },

                formatAge(timestamp) {
                    if (!timestamp) return 'N/A';
                    const now = new Date();
                    const created = new Date(timestamp);
                    const diffMs = now - created;
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                    if (diffDays > 0) return diffDays + 'd';
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    if (diffHours > 0) return diffHours + 'h';
                    const diffMinutes = Math.floor(diffMs / (1000 * 60));
                    if (diffMinutes > 0) return diffMinutes + 'm';
                    const diffSeconds = Math.floor(diffMs / 1000);
                    return diffSeconds + 's';
                },

                getClusterRoleBindingWarnings(clusterRoleBinding) {
                    const warnings = [];

                    // Check for missing subjects
                    if (!clusterRoleBinding.subjects || clusterRoleBinding.subjects.length === 0) {
                        warnings.push('No Subjects');
                    }

                    // Check for missing roleRef
                    if (!clusterRoleBinding.roleRef) {
                        warnings.push('No Role Reference');
                    }

                    // Check for extremely dangerous cluster-wide bindings
                    if (clusterRoleBinding.subjects && Array.isArray(clusterRoleBinding.subjects)) {
                        clusterRoleBinding.subjects.forEach(subject => {
                            // Check for system:anonymous bindings (extremely dangerous at cluster level)
                            if (subject.name === 'system:anonymous') {
                                warnings.push('Anonymous User Binding');
                            }

                            // Check for system:unauthenticated bindings (extremely dangerous at cluster level)
                            if (subject.name === 'system:unauthenticated') {
                                warnings.push('Unauthenticated User Binding');
                            }

                            // Check for wildcard user bindings (extremely dangerous at cluster level)
                            if (subject.kind === 'User' && subject.name === '*') {
                                warnings.push('Wildcard User Binding');
                            }

                            // Check for system:masters group (cluster admin)
                            if (subject.kind === 'Group' && subject.name === 'system:masters') {
                                warnings.push('System Masters Group');
                            }
                        });
                    }

                    // Check for cluster-admin role bindings (extremely dangerous)
                    if (clusterRoleBinding.roleRef && clusterRoleBinding.roleRef.name === 'cluster-admin') {
                        warnings.push('Cluster Admin Role');
                    }

                    // Check for system:admin role bindings
                    if (clusterRoleBinding.roleRef && clusterRoleBinding.roleRef.name === 'system:admin') {
                        warnings.push('System Admin Role');
                    }

                    return warnings.length > 0 ? [...new Set(warnings)].join(', ') : '-';
                },

                hasClusterRoleBindingWarnings(clusterRoleBinding) {
                    const warnings = this.getClusterRoleBindingWarnings(clusterRoleBinding);
                    return warnings !== '-';
                }
            }
        }
    </script>
</div>
