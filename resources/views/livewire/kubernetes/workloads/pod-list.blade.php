<div x-data="podsList()" x-init="init()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Pods</h1>
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

            <div x-show="showNamespaceFilter" x-cloak x-cloak
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
                                   @change="if($event.target.checked) { selectedNamespaces = ['all']; } filterPods();"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">All Namespaces</span>
                        </label>
                        <template x-for="namespace in namespaces" :key="namespace">
                            <label class="flex items-center">
                                <input type="checkbox"
                                       x-model="selectedNamespaces"
                                       :value="namespace"
                                       @change="if($event.target.checked && selectedNamespaces.includes('all')) { selectedNamespaces = selectedNamespaces.filter(ns => ns !== 'all'); } filterPods();"
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
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input
                type="text"
                x-model="searchTerm"
                @input="filterPods()"
                placeholder="Search pods..."
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
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                            <svg class="w-4 h-4 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Namespace</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Containers</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memory</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restarts</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Controlled By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Node</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QoS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="pod in paginatedPods" :key="pod.metadata.name + pod.metadata.namespace">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="pod.metadata.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div x-show="hasPodWarnings(pod)" class="flex justify-center" :title="getPodWarnings(pod)">
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="pod.metadata.namespace || 'default'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1" x-html="getContainerStatusIndicators(pod)"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">N/A</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">N/A</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getRestartCount(pod)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="(pod.metadata.ownerReferences && pod.metadata.ownerReferences[0]) ? pod.metadata.ownerReferences[0].kind : 'N/A'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="pod.spec.nodeName || 'N/A'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="(pod.status && pod.status.qosClass) || 'N/A'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatAge(pod.metadata.creationTimestamp)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getStatusClass(pod.status ? pod.status.phase : 'Unknown')"
                                    x-text="pod.status ? pod.status.phase : 'Unknown'">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <!-- Shell Icon -->
                                    <button
                                        @click="openPodShell(pod)"
                                        :disabled="!isPodRunning(pod)"
                                        class="p-1 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="isPodRunning(pod) ? 'text-gray-600 hover:text-gray-800' : 'text-gray-400'"
                                        title="Open Shell"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr x-show="filteredPods.length === 0">
                        <td colspan="13" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <span x-show="searchTerm || !selectedNamespaces.includes('all')">No pods found matching your filters</span>
                            <span x-show="!searchTerm && selectedNamespaces.includes('all')">No pods found</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Client-side Pagination -->
        <div x-show="!clientLoading && filteredPods.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-6 py-3 bg-white border-t border-gray-200">
            <div class="text-sm text-gray-700 mb-2 sm:mb-0">
                Showing
                <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                to
                <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredPods.length)"></span>
                of
                <span class="font-medium" x-text="filteredPods.length"></span>
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
                    :disabled="currentPage >= Math.ceil(filteredPods.length / perPage)"
                    class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>

                <!-- Last page button -->
                <button
                    @click="goToPage(Math.ceil(filteredPods.length / perPage))"
                    :disabled="currentPage >= Math.ceil(filteredPods.length / perPage)"
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

    <!-- Terminal Panel (Hidden by default) - Compact Style -->
    <div id="terminal-panel" class="hidden fixed bottom-0 left-0 right-0 terminal-vscode z-50" style="height: 450px; margin: 8px; bottom: 0;">
        <div class="terminal-header flex items-center justify-between" style="padding: 4px 12px; min-height: 32px;">
            <div class="flex items-center space-x-2">
                <svg class="w-3 h-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                <span id="terminal-title" class="text-xs text-gray-300">Terminal</span>
            </div>
            <div class="terminal-controls" style="gap: 4px;">
                <button
                    onclick="window.podTerminal?.showHistory()"
                    class="terminal-btn-compact"
                    title="Show Command History (or type 'history')"
                >
                    📜
                </button>
                <button
                    onclick="window.podTerminal?.clear()"
                    class="terminal-btn-compact"
                    title="Clear Terminal"
                >
                    Clear
                </button>
                <button
                    onclick="window.podTerminal?.disconnect()"
                    class="terminal-btn-compact danger"
                    title="Close Terminal"
                >
                    ✕
                </button>
            </div>
        </div>
        <div id="terminal-container" class="w-full h-full bg-black terminal-scrollable"></div>
    </div>

    <script>
        function podsList() {
            return {
                // Data from Livewire
                allPods: @json($pods),
                namespaces: @json($namespaces),

                // Client-side state
                filteredPods: [],
                paginatedPods: [],
                searchTerm: '',
                selectedNamespaces: ['all'],
                showNamespaceFilter: false,
                clientLoading: false,

                // Pagination
                currentPage: 1,
                perPage: 10,

                init() {
                    this.filterPods();
                },

                filterPods() {
                    this.clientLoading = true;

                    // Small delay to show loading state
                    setTimeout(() => {
                        let filtered = [...this.allPods];

                        // Filter by namespace
                        if (!this.selectedNamespaces.includes('all') && this.selectedNamespaces.length > 0) {
                            filtered = filtered.filter(pod =>
                                this.selectedNamespaces.includes(pod.metadata.namespace || 'default')
                            );
                        }

                        // Filter by search term
                        if (this.searchTerm.trim()) {
                            const searchLower = this.searchTerm.toLowerCase();
                            filtered = filtered.filter(pod => {
                                const name = (pod.metadata.name || '').toLowerCase();
                                const namespace = (pod.metadata.namespace || 'default').toLowerCase();
                                const nodeName = (pod.spec.nodeName || '').toLowerCase();
                                const status = (pod.status?.phase || '').toLowerCase();
                                const controlledBy = (pod.metadata.ownerReferences?.[0]?.kind || '').toLowerCase();

                                return name.includes(searchLower) ||
                                       namespace.includes(searchLower) ||
                                       nodeName.includes(searchLower) ||
                                       status.includes(searchLower) ||
                                       controlledBy.includes(searchLower);
                            });
                        }

                        this.filteredPods = filtered;
                        this.currentPage = 1;
                        this.updatePagination();
                        this.clientLoading = false;
                    }, 100);
                },

                updatePagination() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    this.paginatedPods = this.filteredPods.slice(start, end);
                },

                goToPage(page) {
                    const maxPage = Math.ceil(this.filteredPods.length / this.perPage);
                    if (page >= 1 && page <= maxPage) {
                        this.currentPage = page;
                        this.updatePagination();
                    }
                },

                getVisiblePages() {
                    const totalPages = Math.ceil(this.filteredPods.length / this.perPage);
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

                getRestartCount(pod) {
                    if (!pod.status?.containerStatuses) return 0;
                    return pod.status.containerStatuses.reduce((total, container) =>
                        total + (container.restartCount || 0), 0
                    );
                },

                getPodWarnings(pod) {
                    const warnings = [];

                    // Check container statuses for warnings
                    if (pod.status?.containerStatuses) {
                        pod.status.containerStatuses.forEach(container => {
                            if (container.state?.waiting) {
                                warnings.push(`${container.name}: ${container.state.waiting.reason}`);
                            }
                            if (container.state?.terminated && container.state.terminated.exitCode !== 0) {
                                warnings.push(`${container.name}: ${container.state.terminated.reason}`);
                            }
                            if (container.restartCount > 0) {
                                warnings.push(`${container.name}: ${container.restartCount} restarts`);
                            }
                        });
                    }

                    // Check pod conditions
                    if (pod.status?.conditions) {
                        pod.status.conditions.forEach(condition => {
                            if (condition.status === 'False' && ['Ready', 'PodScheduled'].includes(condition.type)) {
                                warnings.push(`${condition.type}: ${condition.reason || 'False'}`);
                            }
                        });
                    }

                    return warnings.join(', ') || 'No warnings';
                },

                hasPodWarnings(pod) {
                    // Check for container issues
                    if (pod.status?.containerStatuses) {
                        for (const container of pod.status.containerStatuses) {
                            if (container.state?.waiting ||
                                (container.state?.terminated && container.state.terminated.exitCode !== 0) ||
                                container.restartCount > 0) {
                                return true;
                            }
                        }
                    }

                    // Check pod conditions
                    if (pod.status?.conditions) {
                        for (const condition of pod.status.conditions) {
                            if (condition.status === 'False' && ['Ready', 'PodScheduled'].includes(condition.type)) {
                                return true;
                            }
                        }
                    }

                    return false;
                },

                getContainerStatusIndicators(pod) {
                    const containers = pod.status?.containerStatuses || [];
                    const initContainers = pod.status?.initContainerStatuses || [];

                    let html = '';

                    // Show init containers first (if any)
                    initContainers.forEach((container, index) => {
                        const color = this.getContainerColor(container, true);
                        const tooltip = this.getContainerTooltip(container, true);
                        html += `<div class="w-3 h-3 rounded-sm ${color}" title="${tooltip}"></div>`;
                    });

                    // Show main containers
                    containers.forEach((container, index) => {
                        const color = this.getContainerColor(container, false);
                        const tooltip = this.getContainerTooltip(container, false);
                        html += `<div class="w-3 h-3 rounded-sm ${color}" title="${tooltip}"></div>`;
                    });

                    // If no containers, show count from spec
                    if (containers.length === 0 && initContainers.length === 0) {
                        const specContainers = pod.spec?.containers || [];
                        specContainers.forEach((container, index) => {
                            html += `<div class="w-3 h-3 rounded-sm bg-gray-400" title="${container.name}: Unknown status"></div>`;
                        });
                    }

                    return html;
                },

                getContainerColor(container, isInit = false) {
                    if (container.state?.running) {
                        return 'bg-green-500'; // Running - green
                    } else if (container.state?.waiting) {
                        return 'bg-yellow-500'; // Waiting - yellow
                    } else if (container.state?.terminated) {
                        if (container.state.terminated.exitCode === 0) {
                            return isInit ? 'bg-blue-500' : 'bg-green-500'; // Completed successfully
                        } else {
                            return 'bg-red-500'; // Failed - red
                        }
                    }
                    return 'bg-gray-400'; // Unknown
                },

                getContainerTooltip(container, isInit = false) {
                    const prefix = isInit ? 'Init: ' : '';

                    if (container.state?.running) {
                        return `${prefix}${container.name}: Running (started: ${container.state.running.startedAt})`;
                    } else if (container.state?.waiting) {
                        return `${prefix}${container.name}: ${container.state.waiting.reason} - ${container.state.waiting.message || 'Waiting'}`;
                    } else if (container.state?.terminated) {
                        const reason = container.state.terminated.reason || 'Terminated';
                        const exitCode = container.state.terminated.exitCode;
                        return `${prefix}${container.name}: ${reason} (exit code: ${exitCode})`;
                    }
                    return `${prefix}${container.name}: Unknown status`;
                },

                getStatusClass(status) {
                    const statusClasses = {
                        'Running': 'bg-green-100 text-green-800',
                        'Pending': 'bg-yellow-100 text-yellow-800',
                        'Succeeded': 'bg-blue-100 text-blue-800',
                        'Failed': 'bg-red-100 text-red-800',
                        'Unknown': 'bg-gray-100 text-gray-800'
                    };
                    return statusClasses[status] || 'bg-gray-100 text-gray-800';
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

                // Pod shell functions
                isPodRunning(pod) {
                    return pod.status?.phase === 'Running';
                },

                async openPodShell(pod) {
                    if (!this.isPodRunning(pod)) {
                        alert('Pod must be in Running state to open shell');
                        return;
                    }

                    const namespace = pod.metadata.namespace;
                    const podName = pod.metadata.name;

                    // Get the first container if multiple containers exist
                    const containers = pod.spec?.containers || [];
                    const container = containers.length > 0 ? containers[0].name : null;

                    try {
                        // Update terminal title
                        const terminalTitle = document.getElementById('terminal-title');
                        if (terminalTitle) {
                            terminalTitle.textContent = `${namespace}/${podName}${container ? `/${container}` : ''}`;
                        }

                        // Connect to pod shell
                        const success = await window.podTerminal.connect(namespace, podName, container);

                        if (!success) {
                            alert('Failed to connect to pod shell');
                        }
                    } catch (error) {
                        console.error('Error opening pod shell:', error);
                        alert('Error opening pod shell: ' + error.message);
                    }
                }
            }
        }
    </script>

    <style>
        /* VS Code Terminal Style */
        .terminal-vscode {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .terminal-vscode .terminal-header {
            background: #2d2d30;
            border-bottom: 1px solid #3c3c3c;
            border-radius: 8px 8px 0 0;
            padding: 8px 16px;
        }

        .terminal-vscode .terminal-tab {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            border-radius: 4px 4px 0 0;
            padding: 6px 12px;
            margin-right: 4px;
            font-size: 12px;
            color: #cccccc;
        }

        .terminal-vscode .terminal-controls {
            display: flex;
            gap: 8px;
        }

        .terminal-vscode .terminal-btn {
            background: #0e639c;
            border: none;
            border-radius: 4px;
            color: white;
            padding: 4px 8px;
            font-size: 11px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .terminal-vscode .terminal-btn:hover {
            background: #1177bb;
        }

        .terminal-vscode .terminal-btn.danger {
            background: #d73a49;
        }

        .terminal-vscode .terminal-btn.danger:hover {
            background: #e53e3e;
        }

        /* Compact Terminal Buttons */
        .terminal-btn-compact {
            background: #0e639c;
            border: none;
            border-radius: 3px;
            color: white;
            padding: 2px 6px;
            font-size: 10px;
            cursor: pointer;
            transition: background 0.2s;
            min-width: auto;
        }

        .terminal-btn-compact:hover {
            background: #1177bb;
        }

        .terminal-btn-compact.danger {
            background: #d73a49;
        }

        .terminal-btn-compact.danger:hover {
            background: #e53e3e;
        }

        /* Terminal Scrollbar Styling */
        .terminal-scrollable .xterm-viewport {
            overflow-y: auto !important;
            scrollbar-width: thin;
            scrollbar-color: #4a5568 #2d3748;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar {
            width: 12px;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-track {
            background: #2d3748;
            border-radius: 6px;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 6px;
            border: 2px solid #2d3748;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-thumb:active {
            background: #a0aec0;
        }

        /* Ensure terminal content is scrollable */
        .terminal-scrollable .xterm-screen {
            overflow-y: visible !important;
        }

        /* Terminal container styling */
        .terminal-scrollable {
            overflow: hidden;
            position: relative;
        }

        /* Optimized scrolling performance */
        .terminal-scrollable .xterm-viewport {
            scroll-behavior: auto;
            will-change: scroll-position;
            transform: translateZ(0);
        }

        /* Faster mouse wheel scrolling */
        .terminal-scrollable {
            scroll-behavior: auto;
        }
    </style>
</div>
