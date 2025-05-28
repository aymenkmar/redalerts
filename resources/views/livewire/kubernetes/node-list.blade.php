<div x-data="nodesList()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Nodes</h1>
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

    <!-- Search Input -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mb-6">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input
                type="text"
                x-model="searchTerm"
                @input="filterNodes()"
                placeholder="Search nodes..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500 relative z-0"
            >
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conditions</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                            <svg class="w-4 h-4 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taint</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="node in paginatedNodes" :key="node.metadata.name">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="node.metadata.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getNodeConditionsClass(node)"
                                    x-text="getNodeConditions(node)">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div x-show="hasNodeWarnings(node)" class="flex justify-center" :title="getNodeWarnings(node)">
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getNodeTaints(node)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getNodeRoles(node)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="(node.status && node.status.nodeInfo && node.status.nodeInfo.kubeletVersion) || 'unknown'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatAge(node.metadata.creationTimestamp)"></td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr x-show="filteredNodes.length === 0">
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <span x-show="searchTerm">No nodes found matching your search</span>
                            <span x-show="!searchTerm">No nodes found</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Client-side Pagination -->
        <div x-show="!clientLoading && filteredNodes.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-6 py-3 bg-white border-t border-gray-200">
            <div class="text-sm text-gray-700 mb-2 sm:mb-0">
                Showing
                <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                to
                <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredNodes.length)"></span>
                of
                <span class="font-medium" x-text="filteredNodes.length"></span>
                results
            </div>

            <div class="flex items-center space-x-2">
                <!-- First page button -->
                <button
                    @click="goToPage(1)"
                    :disabled="currentPage <= 1"
                    class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Go to first page"
                >
                    First
                </button>

                <!-- Previous button -->
                <button
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage <= 1"
                    class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Previous
                </button>

                <!-- Page numbers -->
                <template x-for="page in getVisiblePages()" :key="page">
                    <button
                        @click="goToPage(page)"
                        class="px-3 py-1 rounded border"
                        :class="currentPage === page ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        x-text="page"
                    ></button>
                </template>

                <!-- Next button -->
                <button
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage >= Math.ceil(filteredNodes.length / perPage)"
                    class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>

                <!-- Last page button -->
                <button
                    @click="goToPage(Math.ceil(filteredNodes.length / perPage))"
                    :disabled="currentPage >= Math.ceil(filteredNodes.length / perPage)"
                    class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Go to last page"
                >
                    Last
                </button>
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
        function nodesList() {
            return {
                // Data from Livewire
                allNodes: @json($nodes),

                // Client-side state
                filteredNodes: [],
                paginatedNodes: [],
                searchTerm: '',
                clientLoading: false,

                // Pagination
                currentPage: 1,
                perPage: 25,

                init() {
                    this.filterNodes();
                },

                filterNodes() {
                    this.clientLoading = true;

                    // Small delay to show loading state
                    setTimeout(() => {
                        let filtered = [...this.allNodes];

                        // Filter by search term
                        if (this.searchTerm.trim()) {
                            const searchLower = this.searchTerm.toLowerCase();
                            filtered = filtered.filter(node => {
                                const name = (node.metadata.name || '').toLowerCase();
                                const roles = this.getNodeRoles(node).toLowerCase();
                                const conditions = this.getNodeConditions(node).toLowerCase();
                                const warnings = this.getNodeWarnings(node).toLowerCase();
                                const taints = this.getNodeTaints(node).toLowerCase();
                                const version = ((node.status?.nodeInfo?.kubeletVersion) || '').toLowerCase();

                                return name.includes(searchLower) ||
                                       roles.includes(searchLower) ||
                                       conditions.includes(searchLower) ||
                                       warnings.includes(searchLower) ||
                                       taints.includes(searchLower) ||
                                       version.includes(searchLower);
                            });
                        }

                        this.filteredNodes = filtered;
                        this.currentPage = 1;
                        this.updatePagination();
                        this.clientLoading = false;
                    }, 100);
                },

                updatePagination() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    this.paginatedNodes = this.filteredNodes.slice(start, end);
                },

                goToPage(page) {
                    const maxPage = Math.ceil(this.filteredNodes.length / this.perPage);
                    if (page >= 1 && page <= maxPage) {
                        this.currentPage = page;
                        this.updatePagination();
                    }
                },

                getVisiblePages() {
                    const totalPages = Math.ceil(this.filteredNodes.length / this.perPage);
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

                getNodeConditions(node) {
                    const conditions = node.status?.conditions || [];
                    const readyCondition = conditions.find(c => c.type === 'Ready');

                    if (readyCondition?.status === 'True') {
                        return 'Ready';
                    }

                    // Check for other problematic conditions
                    const problemConditions = conditions.filter(c =>
                        c.status === 'True' && ['MemoryPressure', 'DiskPressure', 'PIDPressure', 'NetworkUnavailable'].includes(c.type)
                    );

                    if (problemConditions.length > 0) {
                        return problemConditions.map(c => c.type).join(', ');
                    }

                    return readyCondition ? 'Not Ready' : 'Unknown';
                },

                getNodeConditionsClass(node) {
                    const conditions = this.getNodeConditions(node);
                    if (conditions === 'Ready') {
                        return 'bg-green-100 text-green-800';
                    } else if (conditions.includes('Pressure') || conditions.includes('Unavailable')) {
                        return 'bg-yellow-100 text-yellow-800';
                    } else {
                        return 'bg-red-100 text-red-800';
                    }
                },

                getNodeWarnings(node) {
                    const conditions = node.status?.conditions || [];
                    const warnings = [];

                    // Check for warning conditions
                    const warningConditions = conditions.filter(c =>
                        c.status === 'True' && ['MemoryPressure', 'DiskPressure', 'PIDPressure'].includes(c.type)
                    );

                    if (warningConditions.length > 0) {
                        warnings.push(...warningConditions.map(c => c.type));
                    }

                    // Check for unschedulable nodes
                    if (node.spec?.unschedulable) {
                        warnings.push('Unschedulable');
                    }

                    return warnings.length > 0 ? warnings.join(', ') : '-';
                },

                hasNodeWarnings(node) {
                    const warnings = this.getNodeWarnings(node);
                    return warnings !== '-';
                },

                getNodeTaints(node) {
                    const taints = node.spec?.taints || [];
                    return taints.length > 0 ? taints.length.toString() : '0';
                },

                getNodeRoles(node) {
                    const labels = node.metadata?.labels || {};
                    const roles = [];

                    // Check for standard node-role.kubernetes.io/ labels
                    Object.keys(labels).forEach(key => {
                        if (key.startsWith('node-role.kubernetes.io/')) {
                            const role = key.replace('node-role.kubernetes.io/', '');
                            roles.push(role);
                        }
                    });

                    // Check for legacy master labels (older Kubernetes versions)
                    if (labels['kubernetes.io/role'] === 'master') {
                        roles.push('master');
                    }

                    // Check for control-plane labels (newer Kubernetes versions)
                    if (labels['node-role.kubernetes.io/control-plane'] !== undefined) {
                        if (!roles.includes('control-plane')) {
                            roles.push('control-plane');
                        }
                    }

                    // Check for master labels (some distributions)
                    if (labels['node-role.kubernetes.io/master'] !== undefined) {
                        if (!roles.includes('master')) {
                            roles.push('master');
                        }
                    }

                    // Remove duplicates and join
                    const uniqueRoles = [...new Set(roles)];
                    return uniqueRoles.length > 0 ? uniqueRoles.join(', ') : 'worker';
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
                }
            }
        }
    </script>
</div>
