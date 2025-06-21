@props([
    'title' => '',
    'allData' => [],
    'columns' => [],
    'loading' => false,
    'error' => null,
    'namespaces' => [],
    'showNamespaceFilter' => true,
    'showRefresh' => true,
    'refreshMethod' => 'refreshData'
])

<div x-data="kubernetesTable()" x-init="init()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <div class="flex space-x-2">
            <!-- Reset Column Widths Button -->
            <button
                @click="resetColumnWidths()"
                class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200"
                title="Reset column widths to default"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                </svg>
                Reset Columns
            </button>

            @if($showRefresh)
            <button
                wire:click="{{ $refreshMethod }}"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-200"
                :disabled="loading"
            >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            @endif
        </div>
    </div>

    <!-- Error Message -->
    @if($error)
    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error</h3>
                <div class="mt-2 text-sm text-red-700">{{ $error }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 space-y-4">
        <!-- Search and Namespace Filter Row -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
            <!-- Namespace Filter Button -->
            @if($showNamespaceFilter && count($namespaces) > 0)
            <div class="relative">
                <button
                    @click="showNamespaceFilter = !showNamespaceFilter"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Namespaces
                    <span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full" x-text="selectedNamespaces.includes('all') ? 'All' : selectedNamespaces.length">
                    </span>
                </button>
            </div>
            @endif

            <!-- Search Input -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        x-model="searchTerm"
                        @input="filterData()"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-red-500 focus:border-red-500 sm:text-sm"
                        placeholder="Search..."
                    >
                </div>
            </div>
        </div>

        <!-- Namespace Filter Dropdown -->
        @if($showNamespaceFilter && count($namespaces) > 0)
        <div x-show="showNamespaceFilter" x-transition x-cloak class="bg-white border border-gray-200 rounded-md shadow-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">Filter by Namespace</h4>
                <button
                    @click="selectAllNamespaces()"
                    class="text-xs text-red-600 hover:text-red-800"
                    x-text="selectedNamespaces.includes('all') ? 'Deselect All' : 'Select All'"
                >
                </button>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        @click="toggleNamespace('all')"
                        :checked="selectedNamespaces.includes('all')"
                        class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">All namespaces</span>
                </label>
                @foreach($namespaces as $namespace)
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        @click="toggleNamespace('{{ $namespace }}')"
                        :checked="selectedNamespaces.includes('{{ $namespace }}')"
                        class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">{{ $namespace }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Loading State -->
    <div x-show="$wire.loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
        <span class="ml-3 text-gray-600">Loading...</span>
    </div>

    <!-- Table -->
    <div x-show="!$wire.loading" class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($columns as $index => $column)
                        <th scope="col" class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 {{ ($column['field'] === 'warnings') ? 'text-center' : '' }}"
                            :style="'width: ' + (columnWidths[{{ $index }}] || '150px')"
                            x-data="{ hovering: false }"
                            x-init="if (!columnWidths[{{ $index }}]) columnWidths[{{ $index }}] = getDefaultWidth('{{ $column['field'] }}')"

                            <!-- Column Content -->
                            <div class="flex items-center space-x-1" @mouseenter="hovering = true" @mouseleave="hovering = false">
                                @if($column['sortable'] ?? false)
                                <button
                                    @click="sortBy('{{ $column['field'] }}')"
                                    class="group inline-flex items-center space-x-1 hover:text-gray-900 focus:outline-none"
                                >
                                    <span>
                                        @if($column['is_html'] ?? false)
                                            {!! $column['label'] !!}
                                        @else
                                            {{ $column['label'] }}
                                        @endif
                                    </span>
                                    <span class="flex flex-col">
                                        <!-- Up Arrow -->
                                        <svg
                                            :class="sortField === '{{ $column['field'] }}' && sortDirection === 'asc' ? 'text-gray-900' : 'text-gray-400'"
                                            class="w-3 h-3 -mb-1"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                        <!-- Down Arrow -->
                                        <svg
                                            :class="sortField === '{{ $column['field'] }}' && sortDirection === 'desc' ? 'text-gray-900' : 'text-gray-400'"
                                            class="w-3 h-3"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                @else
                                    <span>
                                        @if($column['is_html'] ?? false)
                                            {!! $column['label'] !!}
                                        @else
                                            {{ $column['label'] }}
                                        @endif
                                    </span>
                                @endif
                            </div>

                            @if($index < count($columns) - 1)
                            <!-- Column Resize Handle -->
                            <div class="absolute right-0 top-0 bottom-0 w-2 cursor-col-resize group flex items-center justify-center"
                                 @mousedown="startResize($event, {{ $index }})"
                                 :class="isResizing && resizingColumn === {{ $index }} ? 'bg-blue-500' : ''"
                                 title="Glisser pour redimensionner la colonne">

                                <!-- Resize Handle Visual -->
                                <div class="w-1 h-8 bg-gray-300 group-hover:bg-blue-400 transition-colors duration-150 rounded-sm"
                                     :class="isResizing && resizingColumn === {{ $index }} ? 'bg-blue-500' : ''">
                                </div>

                                <!-- Invisible wider hit area -->
                                <div class="absolute inset-0 w-4 -ml-1"></div>
                            </div>
                            @endif
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div x-show="!$wire.loading && filteredData.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-6 py-3 bg-white border-t border-gray-200">
        <div class="text-sm text-gray-700 mb-2 sm:mb-0">
            Showing
            <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
            to
            <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredData.length)"></span>
            of
            <span class="font-medium" x-text="filteredData.length"></span>
            results
        </div>

        <div class="flex space-x-1">
            <!-- First button -->
            <button
                @click="goToPage(1)"
                :class="currentPage <= 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-3 py-1 rounded border"
                :disabled="currentPage <= 1"
            >
                First
            </button>

            <!-- Previous button -->
            <button
                @click="goToPage(currentPage - 1)"
                :class="currentPage <= 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-3 py-1 rounded border"
                :disabled="currentPage <= 1"
            >
                Previous
            </button>

            <!-- Page numbers - Simple 5-page window -->
            <template x-for="page in getVisiblePages()" :key="page">
                <button
                    @click="goToPage(page)"
                    :class="currentPage == page ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    class="px-3 py-1 rounded border"
                    x-text="page"
                ></button>
            </template>

            <!-- Next button -->
            <button
                @click="goToPage(currentPage + 1)"
                :class="currentPage >= Math.ceil(filteredData.length / perPage) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-3 py-1 rounded border"
                :disabled="currentPage >= Math.ceil(filteredData.length / perPage)"
            >
                Next
            </button>

            <!-- Last button -->
            <button
                @click="goToPage(Math.ceil(filteredData.length / perPage))"
                :class="currentPage >= Math.ceil(filteredData.length / perPage) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-3 py-1 rounded border"
                :disabled="currentPage >= Math.ceil(filteredData.length / perPage)"
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

    <script>
        function kubernetesTable() {
            return {
                // Data from Livewire
                allData: @json($allData),
                namespaces: @json($namespaces),

                // Client-side state
                filteredData: [],
                paginatedData: [],
                searchTerm: '',
                selectedNamespaces: ['all'],
                showNamespaceFilter: false,

                // Sorting
                sortField: '',
                sortDirection: 'asc',

                // Column Resizing
                columnWidths: {},
                isResizing: false,
                resizingColumn: null,
                startX: 0,
                startWidth: 0,

                // Pagination
                currentPage: 1,
                perPage: 10,

                init() {
                    console.log('Initializing table...');
                    // Ensure namespace filter starts closed
                    this.showNamespaceFilter = false;
                    // Load saved namespace selection
                    this.loadNamespaceSelection();
                    this.loadColumnWidths();
                    this.initColumnResizing();
                    this.filterData();
                    console.log('Column widths after init:', this.columnWidths);
                },

                getDefaultWidth(field) {
                    switch (field) {
                        case 'warnings':
                            return '60px';
                        case 'name':
                            return '200px';
                        case 'namespace':
                            return '150px';
                        case 'age':
                            return '100px';
                        case 'ready':
                        case 'status':
                            return '120px';
                        case 'type':
                        case 'scope':
                            return '130px';
                        case 'server':
                        case 'email':
                            return '180px';
                        default:
                            return '150px';
                    }
                },

                initColumnResizing() {
                    // Initialize default column widths if not already set
                    const columns = @json($columns);
                    columns.forEach((column, index) => {
                        if (!this.columnWidths[index]) {
                            this.columnWidths[index] = this.getDefaultWidth(column.field);
                        }
                    });

                    // Add global mouse event listeners for resizing
                    document.addEventListener('mousemove', (e) => this.handleResize(e));
                    document.addEventListener('mouseup', () => this.stopResize());

                    // Prevent text selection during resize
                    document.addEventListener('selectstart', (e) => {
                        if (this.isResizing) {
                            e.preventDefault();
                        }
                    });
                },

                startResize(event, columnIndex) {
                    console.log('Starting resize for column:', columnIndex);
                    event.preventDefault();
                    event.stopPropagation();

                    this.isResizing = true;
                    this.resizingColumn = columnIndex;
                    this.startX = event.clientX;

                    // Get current width - ensure we have a valid width
                    const currentWidth = this.columnWidths[columnIndex];
                    this.startWidth = parseInt(currentWidth) || 150;

                    console.log('Current width:', currentWidth, 'Start width:', this.startWidth);

                    // Add visual feedback
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                    document.body.classList.add('resizing-column');

                    // Add temporary styles
                    const style = document.createElement('style');
                    style.id = 'resize-styles';
                    style.textContent = `
                        .resizing-column * {
                            cursor: col-resize !important;
                            user-select: none !important;
                        }
                    `;
                    document.head.appendChild(style);
                },

                handleResize(event) {
                    if (!this.isResizing || this.resizingColumn === null) return;

                    event.preventDefault();

                    const deltaX = event.clientX - this.startX;
                    const newWidth = Math.max(60, this.startWidth + deltaX); // Minimum width of 60px

                    console.log('Resizing column:', this.resizingColumn, 'New width:', newWidth + 'px');
                    this.columnWidths[this.resizingColumn] = newWidth + 'px';
                },

                stopResize() {
                    if (!this.isResizing) return;

                    this.isResizing = false;
                    this.resizingColumn = null;

                    // Remove visual feedback
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                    document.body.classList.remove('resizing-column');

                    // Remove temporary styles
                    const style = document.getElementById('resize-styles');
                    if (style) {
                        style.remove();
                    }

                    // Save column widths to localStorage
                    this.saveColumnWidths();
                },

                saveColumnWidths() {
                    const tableName = '{{ $title ?? "kubernetes-table" }}';
                    localStorage.setItem(`columnWidths_${tableName}`, JSON.stringify(this.columnWidths));
                },

                loadColumnWidths() {
                    const tableName = '{{ $title ?? "kubernetes-table" }}';
                    const saved = localStorage.getItem(`columnWidths_${tableName}`);
                    if (saved) {
                        this.columnWidths = { ...this.columnWidths, ...JSON.parse(saved) };
                    }
                },

                resetColumnWidths() {
                    this.columnWidths = {};
                    this.initColumnResizing();
                    this.saveColumnWidths();
                },

                filterData() {
                    // Instant filtering - no loading delay for better UX
                    let filtered = [...this.allData];

                    // Filter by namespace (if applicable)
                    if (this.namespaces.length > 0 && !this.selectedNamespaces.includes('all') && this.selectedNamespaces.length > 0) {
                        filtered = filtered.filter(item => {
                            const namespace = item.metadata?.namespace || 'default';
                            return this.selectedNamespaces.includes(namespace);
                        });
                    }

                    // Filter by search term
                    if (this.searchTerm.trim()) {
                        const searchLower = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(item => {
                            // Search in name and namespace
                            const name = (item.metadata?.name || '').toLowerCase();
                            const namespace = (item.metadata?.namespace || '').toLowerCase();

                            return name.includes(searchLower) || namespace.includes(searchLower);
                        });
                    }

                    // Apply sorting
                    if (this.sortField) {
                        filtered = this.sortData(filtered);
                    }

                    this.filteredData = filtered;
                    this.currentPage = 1;
                    this.updatePagination();
                },

                sortBy(field) {
                    if (this.sortField === field) {
                        // Toggle direction if same field
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        // New field, default to ascending
                        this.sortField = field;
                        this.sortDirection = 'asc';
                    }
                    this.filterData();
                },

                sortData(data) {
                    return data.sort((a, b) => {
                        let aValue = this.getSortValue(a, this.sortField);
                        let bValue = this.getSortValue(b, this.sortField);

                        // Handle null/undefined values
                        if (aValue === null || aValue === undefined) aValue = '';
                        if (bValue === null || bValue === undefined) bValue = '';

                        // Convert to strings for comparison
                        aValue = String(aValue).toLowerCase();
                        bValue = String(bValue).toLowerCase();

                        let result = 0;
                        if (aValue < bValue) result = -1;
                        if (aValue > bValue) result = 1;

                        return this.sortDirection === 'desc' ? -result : result;
                    });
                },

                getSortValue(item, field) {
                    switch (field) {
                        case 'name':
                            return item.metadata?.name || '';
                        case 'namespace':
                            return item.metadata?.namespace || 'default';
                        case 'age':
                            return item.metadata?.creationTimestamp || '';
                        case 'ready':
                            // For boolean fields, convert to sortable string
                            if (typeof this.getNodeStatus === 'function') {
                                return this.getNodeStatus(item) === 'Ready' ? 'a' : 'z';
                            }
                            if (typeof this.getPodStatus === 'function') {
                                return this.getPodStatus(item) === 'Running' ? 'a' : 'z';
                            }
                            return '';
                        case 'status':
                            if (typeof this.getNodeStatus === 'function') {
                                return this.getNodeStatus(item);
                            }
                            if (typeof this.getPodStatus === 'function') {
                                return this.getPodStatus(item);
                            }
                            return '';
                        case 'roles':
                            if (typeof this.getNodeRoles === 'function') {
                                return this.getNodeRoles(item);
                            }
                            return '';
                        case 'containers':
                            if (typeof this.getPodReadyContainers === 'function') {
                                return this.getPodReadyContainers(item);
                            }
                            return '';
                        case 'restarts':
                            if (typeof this.getPodRestarts === 'function') {
                                return this.getPodRestarts(item);
                            }
                            return 0;
                        default:
                            return item.metadata?.name || '';
                    }
                },

                updatePagination() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    this.paginatedData = this.filteredData.slice(start, end);
                },

                goToPage(page) {
                    const maxPage = Math.ceil(this.filteredData.length / this.perPage);
                    if (page >= 1 && page <= maxPage) {
                        this.currentPage = page;
                        this.updatePagination();
                    }
                },

                getVisiblePages() {
                    const totalPages = Math.ceil(this.filteredData.length / this.perPage);
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

                // Namespace filter methods
                loadNamespaceSelection() {
                    const saved = localStorage.getItem('selectedNamespaces');
                    if (saved) {
                        try {
                            this.selectedNamespaces = JSON.parse(saved);
                        } catch (e) {
                            console.error('Failed to parse saved namespace selection:', e);
                            this.selectedNamespaces = ['all'];
                        }
                    }
                },

                saveNamespaceSelection() {
                    localStorage.setItem('selectedNamespaces', JSON.stringify(this.selectedNamespaces));
                },

                toggleNamespace(namespace) {
                    if (namespace === 'all') {
                        this.selectedNamespaces = ['all'];
                    } else {
                        // Remove 'all' if it exists
                        this.selectedNamespaces = this.selectedNamespaces.filter(ns => ns !== 'all');

                        if (this.selectedNamespaces.includes(namespace)) {
                            this.selectedNamespaces = this.selectedNamespaces.filter(ns => ns !== namespace);
                        } else {
                            this.selectedNamespaces.push(namespace);
                        }

                        // If no namespaces selected, select all
                        if (this.selectedNamespaces.length === 0) {
                            this.selectedNamespaces = ['all'];
                        }
                    }

                    // Save selection for persistence
                    this.saveNamespaceSelection();
                    this.filterData();
                },

                selectAllNamespaces() {
                    if (this.selectedNamespaces.includes('all')) {
                        this.selectedNamespaces = [];
                    } else {
                        this.selectedNamespaces = ['all'];
                    }

                    // Save selection for persistence
                    this.saveNamespaceSelection();
                    this.filterData();
                },

                // Helper functions for nodes
                getNodeStatus(node) {
                    const conditions = node.status?.conditions || [];
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    return readyCondition?.status === 'True' ? 'Ready' : 'Not Ready';
                },

                getNodeStatusClass(node) {
                    const status = this.getNodeStatus(node);
                    return status === 'Ready'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800';
                },

                getNodeRoles(node) {
                    const labels = node.metadata?.labels || {};
                    const roles = Object.keys(labels)
                        .filter(key => key.startsWith('node-role.kubernetes.io/'))
                        .map(key => key.replace('node-role.kubernetes.io/', ''));
                    return roles.length > 0 ? roles.join(', ') : 'worker';
                },

                hasNodeWarnings(node) {
                    const conditions = node.status?.conditions || [];
                    return conditions.some(condition =>
                        condition.type !== 'Ready' && condition.status === 'True'
                    );
                },

                getNodeWarnings(node) {
                    const conditions = node.status?.conditions || [];
                    const warnings = conditions
                        .filter(condition => condition.type !== 'Ready' && condition.status === 'True')
                        .map(condition => condition.type);
                    return warnings.join(', ') || 'No warnings';
                },

                // Helper functions for pods
                getPodStatus(pod) {
                    return pod.status?.phase || 'Unknown';
                },

                getPodStatusClass(pod) {
                    const status = this.getPodStatus(pod);
                    switch (status) {
                        case 'Running': return 'bg-green-100 text-green-800';
                        case 'Pending': return 'bg-yellow-100 text-yellow-800';
                        case 'Failed': return 'bg-red-100 text-red-800';
                        case 'Succeeded': return 'bg-blue-100 text-blue-800';
                        default: return 'bg-gray-100 text-gray-800';
                    }
                },

                getPodReadyContainers(pod) {
                    const containerStatuses = pod.status?.containerStatuses || [];
                    const readyCount = containerStatuses.filter(status => status.ready).length;
                    const totalCount = containerStatuses.length;
                    return `${readyCount}/${totalCount}`;
                },

                getPodRestarts(pod) {
                    const containerStatuses = pod.status?.containerStatuses || [];
                    return containerStatuses.reduce((total, status) => total + (status.restartCount || 0), 0);
                },

                hasPodWarnings(pod) {
                    const status = pod.status?.phase;
                    if (status === 'Pending' || status === 'Failed') return true;

                    const containerStatuses = pod.status?.containerStatuses || [];
                    return containerStatuses.some(status => !status.ready || status.restartCount > 0);
                },

                getPodWarnings(pod) {
                    const warnings = [];
                    const status = pod.status?.phase;

                    if (status === 'Pending') warnings.push('Pod Pending');
                    if (status === 'Failed') warnings.push('Pod Failed');

                    const containerStatuses = pod.status?.containerStatuses || [];
                    containerStatuses.forEach(status => {
                        if (!status.ready) warnings.push(`Container ${status.name} not ready`);
                        if (status.restartCount > 0) warnings.push(`Container ${status.name} restarted ${status.restartCount} times`);
                    });

                    return warnings.join(', ') || 'No warnings';
                },

                isPodRunning(pod) {
                    return pod.status?.phase === 'Running';
                },

                // General helper functions
                formatAge(timestamp) {
                    if (!timestamp) return 'N/A';

                    const now = new Date();
                    const created = new Date(timestamp);
                    const diffSeconds = Math.floor((now - created) / 1000);
                    const diffMinutes = Math.floor(diffSeconds / 60);
                    const diffHours = Math.floor(diffMinutes / 60);
                    const diffDays = Math.floor(diffHours / 24);

                    if (diffDays > 0) return diffDays + 'd';
                    if (diffHours > 0) return diffHours + 'h';
                    if (diffMinutes > 0) return diffMinutes + 'm';
                    return diffSeconds + 's';
                },

                // Pod shell functionality
                openPodShell(namespace, podName) {
                    // Call the global function if it exists
                    if (typeof window.openPodShell === 'function') {
                        window.openPodShell(namespace, podName);
                    }
                },

                // Helper functions for deployments
                getDeploymentStatus(deployment) {
                    const replicas = deployment.spec?.replicas || 0;
                    const readyReplicas = deployment.status?.readyReplicas || 0;
                    const availableReplicas = deployment.status?.availableReplicas || 0;

                    if (replicas === 0) return 'Stopped';
                    if (readyReplicas === replicas && availableReplicas === replicas) return 'Running';
                    if (readyReplicas > 0) return 'Partial';
                    return 'Failed';
                },

                getDeploymentStatusClass(deployment) {
                    const status = this.getDeploymentStatus(deployment);
                    switch (status) {
                        case 'Running': return 'bg-green-100 text-green-800';
                        case 'Partial': return 'bg-yellow-100 text-yellow-800';
                        case 'Failed': return 'bg-red-100 text-red-800';
                        case 'Stopped': return 'bg-gray-100 text-gray-800';
                        default: return 'bg-gray-100 text-gray-800';
                    }
                },

                getPodsStatus(deployment) {
                    const readyReplicas = deployment.status?.readyReplicas || 0;
                    const replicas = deployment.spec?.replicas || 0;
                    return `${readyReplicas}/${replicas}`;
                },

                hasDeploymentWarnings(deployment) {
                    const replicas = deployment.spec?.replicas || 0;
                    const readyReplicas = deployment.status?.readyReplicas || 0;
                    const availableReplicas = deployment.status?.availableReplicas || 0;

                    // Warning if not all replicas are ready or available
                    return replicas > 0 && (readyReplicas < replicas || availableReplicas < replicas);
                },

                getDeploymentWarnings(deployment) {
                    const warnings = [];
                    const replicas = deployment.spec?.replicas || 0;
                    const readyReplicas = deployment.status?.readyReplicas || 0;
                    const availableReplicas = deployment.status?.availableReplicas || 0;
                    const unavailableReplicas = deployment.status?.unavailableReplicas || 0;

                    if (replicas > readyReplicas) {
                        warnings.push(`${replicas - readyReplicas} pods not ready`);
                    }
                    if (replicas > availableReplicas) {
                        warnings.push(`${replicas - availableReplicas} pods not available`);
                    }
                    if (unavailableReplicas > 0) {
                        warnings.push(`${unavailableReplicas} pods unavailable`);
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for daemon sets
                getDaemonSetDesired(daemonSet) {
                    return daemonSet.status?.desiredNumberScheduled || 0;
                },

                getDaemonSetCurrent(daemonSet) {
                    return daemonSet.status?.currentNumberScheduled || 0;
                },

                getDaemonSetReady(daemonSet) {
                    return daemonSet.status?.numberReady || 0;
                },

                getDaemonSetUpToDate(daemonSet) {
                    return daemonSet.status?.updatedNumberScheduled || 0;
                },

                getDaemonSetAvailable(daemonSet) {
                    return daemonSet.status?.numberAvailable || 0;
                },

                getDaemonSetNodeSelector(daemonSet) {
                    const nodeSelector = daemonSet.spec?.template?.spec?.nodeSelector;
                    if (!nodeSelector || Object.keys(nodeSelector).length === 0) {
                        return 'None';
                    }

                    return Object.entries(nodeSelector)
                        .map(([key, value]) => `${key}: ${value}`)
                        .join(', ');
                },

                hasDaemonSetWarnings(daemonSet) {
                    const desired = this.getDaemonSetDesired(daemonSet);
                    const ready = this.getDaemonSetReady(daemonSet);
                    const available = this.getDaemonSetAvailable(daemonSet);
                    const upToDate = this.getDaemonSetUpToDate(daemonSet);

                    // Warning if not all pods are ready, available, or up-to-date
                    return desired > 0 && (ready < desired || available < desired || upToDate < desired);
                },

                getDaemonSetWarnings(daemonSet) {
                    const warnings = [];
                    const desired = this.getDaemonSetDesired(daemonSet);
                    const ready = this.getDaemonSetReady(daemonSet);
                    const available = this.getDaemonSetAvailable(daemonSet);
                    const upToDate = this.getDaemonSetUpToDate(daemonSet);
                    const current = this.getDaemonSetCurrent(daemonSet);

                    if (desired > ready) {
                        warnings.push(`${desired - ready} pods not ready`);
                    }
                    if (desired > available) {
                        warnings.push(`${desired - available} pods not available`);
                    }
                    if (desired > upToDate) {
                        warnings.push(`${desired - upToDate} pods not up-to-date`);
                    }
                    if (desired > current) {
                        warnings.push(`${desired - current} pods not scheduled`);
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for stateful sets
                getStatefulSetPodsStatus(statefulSet) {
                    const ready = statefulSet.status?.readyReplicas || 0;
                    const total = statefulSet.status?.replicas || 0;
                    return ready + '/' + total;
                },

                getStatefulSetReplicas(statefulSet) {
                    return statefulSet.spec?.replicas || 0;
                },

                hasStatefulSetWarnings(statefulSet) {
                    const desired = this.getStatefulSetReplicas(statefulSet);
                    const ready = statefulSet.status?.readyReplicas || 0;
                    const current = statefulSet.status?.replicas || 0;
                    const updated = statefulSet.status?.updatedReplicas || 0;

                    // Warning if not all pods are ready, current, or updated
                    return desired > 0 && (ready < desired || current < desired || updated < desired);
                },

                getStatefulSetWarnings(statefulSet) {
                    const warnings = [];
                    const desired = this.getStatefulSetReplicas(statefulSet);
                    const ready = statefulSet.status?.readyReplicas || 0;
                    const current = statefulSet.status?.replicas || 0;
                    const updated = statefulSet.status?.updatedReplicas || 0;
                    const available = statefulSet.status?.availableReplicas || 0;

                    if (ready < desired) {
                        warnings.push(`${desired - ready} pods not ready`);
                    }
                    if (current < desired) {
                        warnings.push(`${desired - current} pods not running`);
                    }
                    if (updated < desired) {
                        warnings.push(`${desired - updated} pods not updated`);
                    }
                    if (available < desired) {
                        warnings.push(`${desired - available} pods not available`);
                    }

                    // Check StatefulSet conditions
                    if (statefulSet.status?.conditions) {
                        statefulSet.status.conditions.forEach(condition => {
                            if (condition.status === 'False') {
                                warnings.push(`${condition.type}: ${condition.reason || 'False'}`);
                            }
                        });
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for replica sets
                getReplicaSetDesired(replicaSet) {
                    return replicaSet.spec?.replicas || 0;
                },

                getReplicaSetCurrent(replicaSet) {
                    return replicaSet.status?.replicas || 0;
                },

                getReplicaSetReady(replicaSet) {
                    return replicaSet.status?.readyReplicas || 0;
                },

                hasReplicaSetWarnings(replicaSet) {
                    const desired = this.getReplicaSetDesired(replicaSet);
                    const current = this.getReplicaSetCurrent(replicaSet);
                    const ready = this.getReplicaSetReady(replicaSet);

                    // Warning if not all replicas are current or ready
                    return desired > 0 && (current < desired || ready < desired);
                },

                getReplicaSetWarnings(replicaSet) {
                    const warnings = [];
                    const desired = this.getReplicaSetDesired(replicaSet);
                    const current = this.getReplicaSetCurrent(replicaSet);
                    const ready = this.getReplicaSetReady(replicaSet);

                    if (current < desired) {
                        warnings.push(`${desired - current} replicas not running`);
                    }
                    if (ready < desired) {
                        warnings.push(`${desired - ready} replicas not ready`);
                    }

                    // Check ReplicaSet conditions
                    if (replicaSet.status?.conditions) {
                        replicaSet.status.conditions.forEach(condition => {
                            if (condition.status === 'False') {
                                warnings.push(`${condition.type}: ${condition.reason || 'False'}`);
                            }
                        });
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for replication controllers
                getReplicationControllerDesired(rc) {
                    return rc.spec?.replicas || 0;
                },

                getReplicationControllerCurrent(rc) {
                    return rc.status?.replicas || 0;
                },

                getReplicationControllerReady(rc) {
                    return rc.status?.readyReplicas || 0;
                },

                getReplicationControllerSelector(rc) {
                    const selector = rc.spec?.selector;
                    if (!selector || Object.keys(selector).length === 0) {
                        return 'None';
                    }

                    return Object.entries(selector)
                        .map(([key, value]) => `${key}: ${value}`)
                        .join(', ');
                },

                hasReplicationControllerWarnings(rc) {
                    const desired = this.getReplicationControllerDesired(rc);
                    const current = this.getReplicationControllerCurrent(rc);
                    const ready = this.getReplicationControllerReady(rc);

                    // Warning if not all replicas are current or ready
                    return desired > 0 && (current < desired || ready < desired);
                },

                getReplicationControllerWarnings(rc) {
                    const warnings = [];
                    const desired = this.getReplicationControllerDesired(rc);
                    const current = this.getReplicationControllerCurrent(rc);
                    const ready = this.getReplicationControllerReady(rc);

                    if (current < desired) {
                        warnings.push(`${desired - current} replicas not running`);
                    }
                    if (ready < desired) {
                        warnings.push(`${desired - ready} replicas not ready`);
                    }

                    // Check ReplicationController conditions
                    if (rc.status?.conditions) {
                        rc.status.conditions.forEach(condition => {
                            if (condition.status === 'False') {
                                warnings.push(`${condition.type}: ${condition.reason || 'False'}`);
                            }
                        });
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for jobs
                getJobCompletions(job) {
                    const succeeded = job.status?.succeeded || 0;
                    const completions = job.spec?.completions || 1;
                    return `${succeeded}/${completions}`;
                },

                getJobDuration(job) {
                    const startTime = job.status?.startTime;
                    const completionTime = job.status?.completionTime;

                    if (!startTime) {
                        return 'Not started';
                    }

                    const start = new Date(startTime);
                    const end = completionTime ? new Date(completionTime) : new Date();
                    const diffMs = end - start;

                    const diffSeconds = Math.floor(diffMs / 1000);
                    const diffMinutes = Math.floor(diffMs / (1000 * 60));
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                    if (diffDays >= 1) {
                        return diffDays + 'd';
                    }
                    if (diffHours >= 1) {
                        return diffHours + 'h';
                    }
                    if (diffMinutes >= 1) {
                        return diffMinutes + 'm';
                    }
                    return diffSeconds + 's';
                },

                getJobStatus(job) {
                    if (job.status?.conditions) {
                        const completeCondition = job.status.conditions.find(c => c.type === 'Complete');
                        const failedCondition = job.status.conditions.find(c => c.type === 'Failed');

                        if (completeCondition && completeCondition.status === 'True') {
                            return 'Complete';
                        } else if (failedCondition && failedCondition.status === 'True') {
                            return 'Failed';
                        }
                    }

                    if ((job.status?.active || 0) > 0) {
                        return 'Running';
                    }

                    return 'Pending';
                },

                getJobStatusClass(job) {
                    const status = this.getJobStatus(job);
                    switch (status) {
                        case 'Complete':
                            return 'bg-green-100 text-green-800';
                        case 'Failed':
                            return 'bg-red-100 text-red-800';
                        case 'Running':
                            return 'bg-blue-100 text-blue-800';
                        default:
                            return 'bg-yellow-100 text-yellow-800';
                    }
                },

                hasJobWarnings(job) {
                    // Check for failed condition
                    if (job.status?.conditions) {
                        const failedCondition = job.status.conditions.find(c => c.type === 'Failed');
                        if (failedCondition && failedCondition.status === 'True') {
                            return true;
                        }
                    }

                    // Check for incomplete job that should be complete
                    const succeeded = job.status?.succeeded || 0;
                    const completions = job.spec?.completions || 1;
                    const failed = job.status?.failed || 0;

                    if (failed > 0) {
                        return true;
                    }

                    // Check if job is taking too long (more than 1 hour and not complete)
                    if (job.status?.startTime && succeeded < completions) {
                        const start = new Date(job.status.startTime);
                        const now = new Date();
                        const diffHours = (now - start) / (1000 * 60 * 60);
                        if (diffHours > 1) {
                            return true;
                        }
                    }

                    return false;
                },

                getJobWarnings(job) {
                    const warnings = [];

                    // Check for failed condition
                    if (job.status?.conditions) {
                        const failedCondition = job.status.conditions.find(c => c.type === 'Failed');
                        if (failedCondition && failedCondition.status === 'True') {
                            warnings.push(`Job failed: ${failedCondition.reason || 'Unknown reason'}`);
                        }
                    }

                    const succeeded = job.status?.succeeded || 0;
                    const completions = job.spec?.completions || 1;
                    const failed = job.status?.failed || 0;

                    if (failed > 0) {
                        warnings.push(`${failed} pod(s) failed`);
                    }

                    if (succeeded < completions) {
                        warnings.push(`${succeeded}/${completions} completions`);
                    }

                    // Check if job is taking too long
                    if (job.status?.startTime && succeeded < completions) {
                        const start = new Date(job.status.startTime);
                        const now = new Date();
                        const diffHours = (now - start) / (1000 * 60 * 60);
                        if (diffHours > 1) {
                            warnings.push(`Running for ${Math.floor(diffHours)}h`);
                        }
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for cron jobs
                getCronJobSchedule(cronJob) {
                    return cronJob.spec?.schedule || 'N/A';
                },

                getCronJobActiveJobs(cronJob) {
                    const active = cronJob.status?.active || [];
                    return active.length.toString();
                },

                getCronJobLastSchedule(cronJob) {
                    const lastScheduleTime = cronJob.status?.lastScheduleTime;
                    if (!lastScheduleTime) {
                        return 'Never';
                    }

                    const lastSchedule = new Date(lastScheduleTime);
                    const now = new Date();
                    const diffMs = now - lastSchedule;

                    const diffSeconds = Math.floor(diffMs / 1000);
                    const diffMinutes = Math.floor(diffMs / (1000 * 60));
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                    if (diffDays >= 1) {
                        return diffDays + 'd ago';
                    }
                    if (diffHours >= 1) {
                        return diffHours + 'h ago';
                    }
                    if (diffMinutes >= 1) {
                        return diffMinutes + 'm ago';
                    }
                    return diffSeconds + 's ago';
                },

                getCronJobNextExecution(cronJob) {
                    // Check if suspended
                    if (cronJob.spec?.suspend === true) {
                        return 'Suspended';
                    }

                    const schedule = cronJob.spec?.schedule;
                    if (!schedule) {
                        return 'N/A';
                    }

                    // Simple next execution calculation
                    // For basic schedules, estimate next execution
                    try {
                        const nextExecution = this.calculateNextCronExecution(schedule);
                        if (nextExecution) {
                            const now = new Date();
                            const diffMs = nextExecution - now;

                            if (diffMs <= 0) {
                                return 'Now';
                            }

                            const diffSeconds = Math.floor(diffMs / 1000);
                            const diffMinutes = Math.floor(diffMs / (1000 * 60));
                            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                            if (diffDays >= 1) {
                                return 'in ' + diffDays + 'd';
                            }
                            if (diffHours >= 1) {
                                return 'in ' + diffHours + 'h';
                            }
                            if (diffMinutes >= 1) {
                                return 'in ' + diffMinutes + 'm';
                            }
                            return 'in ' + diffSeconds + 's';
                        }
                    } catch (e) {
                        // Fallback for complex schedules
                        return 'Unknown';
                    }

                    return 'Unknown';
                },

                calculateNextCronExecution(schedule) {
                    // Basic cron parsing for common patterns
                    const parts = schedule.split(' ');
                    if (parts.length !== 5) {
                        return null;
                    }

                    const [minute, hour, dayOfMonth, month, dayOfWeek] = parts;
                    const now = new Date();

                    // Handle simple cases
                    if (schedule === '0 0 * * *') { // Daily at midnight
                        const next = new Date(now);
                        next.setHours(0, 0, 0, 0);
                        next.setDate(next.getDate() + 1);
                        return next;
                    }

                    if (schedule === '0 * * * *') { // Every hour
                        const next = new Date(now);
                        next.setMinutes(0, 0, 0);
                        next.setHours(next.getHours() + 1);
                        return next;
                    }

                    if (schedule === '*/5 * * * *') { // Every 5 minutes
                        const next = new Date(now);
                        const currentMinute = next.getMinutes();
                        const nextMinute = Math.ceil(currentMinute / 5) * 5;
                        next.setMinutes(nextMinute, 0, 0);
                        if (nextMinute >= 60) {
                            next.setHours(next.getHours() + 1);
                            next.setMinutes(0, 0, 0);
                        }
                        return next;
                    }

                    // For other patterns, estimate based on current time + 1 hour
                    const next = new Date(now);
                    next.setHours(next.getHours() + 1);
                    return next;
                },

                getCronJobTimeZone(cronJob) {
                    return cronJob.spec?.timeZone || 'UTC';
                },

                hasCronJobWarnings(cronJob) {
                    // Check if suspended
                    if (cronJob.spec?.suspend === true) {
                        return true;
                    }

                    // Check for failed jobs
                    if (cronJob.status?.lastScheduleTime) {
                        const lastSchedule = new Date(cronJob.status.lastScheduleTime);
                        const now = new Date();
                        const diffHours = (now - lastSchedule) / (1000 * 60 * 60);

                        // Warning if last schedule was more than 24 hours ago for daily jobs
                        if (diffHours > 24 && cronJob.spec?.schedule?.includes('0 0')) {
                            return true;
                        }
                    }

                    // Check for too many active jobs
                    const activeJobs = cronJob.status?.active || [];
                    if (activeJobs.length > 3) {
                        return true;
                    }

                    return false;
                },

                getCronJobWarnings(cronJob) {
                    const warnings = [];

                    // Check if suspended
                    if (cronJob.spec?.suspend === true) {
                        warnings.push('CronJob is suspended');
                    }

                    // Check for stale last schedule
                    if (cronJob.status?.lastScheduleTime) {
                        const lastSchedule = new Date(cronJob.status.lastScheduleTime);
                        const now = new Date();
                        const diffHours = (now - lastSchedule) / (1000 * 60 * 60);

                        if (diffHours > 24 && cronJob.spec?.schedule?.includes('0 0')) {
                            warnings.push(`Last schedule was ${Math.floor(diffHours)}h ago`);
                        }
                    }

                    // Check for too many active jobs
                    const activeJobs = cronJob.status?.active || [];
                    if (activeJobs.length > 3) {
                        warnings.push(`${activeJobs.length} active jobs (may indicate stuck jobs)`);
                    }

                    // Check for missing schedule
                    if (!cronJob.spec?.schedule) {
                        warnings.push('No schedule defined');
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for config maps
                getConfigMapKeys(configMap) {
                    const data = configMap.data || {};
                    const keys = Object.keys(data);

                    if (keys.length === 0) {
                        return 'No keys';
                    }

                    if (keys.length <= 3) {
                        return keys.join(', ');
                    }

                    return `${keys.slice(0, 3).join(', ')} +${keys.length - 3} more`;
                },

                getConfigMapSize(configMap) {
                    const data = configMap.data || {};
                    let totalSize = 0;

                    Object.values(data).forEach(value => {
                        if (typeof value === 'string') {
                            totalSize += new Blob([value]).size;
                        }
                    });

                    if (totalSize === 0) {
                        return '0 B';
                    }

                    const units = ['B', 'KB', 'MB', 'GB'];
                    let unitIndex = 0;
                    let size = totalSize;

                    while (size >= 1024 && unitIndex < units.length - 1) {
                        size /= 1024;
                        unitIndex++;
                    }

                    return `${Math.round(size * 100) / 100} ${units[unitIndex]}`;
                },

                hasConfigMapWarnings(configMap) {
                    const data = configMap.data || {};
                    const keys = Object.keys(data);

                    // Warning if no data
                    if (keys.length === 0) {
                        return true;
                    }

                    // Warning if very large (>1MB total)
                    let totalSize = 0;
                    Object.values(data).forEach(value => {
                        if (typeof value === 'string') {
                            totalSize += new Blob([value]).size;
                        }
                    });

                    if (totalSize > 1024 * 1024) { // 1MB
                        return true;
                    }

                    // Warning if too many keys (>50)
                    if (keys.length > 50) {
                        return true;
                    }

                    // Warning if any key has suspicious content
                    for (const [key, value] of Object.entries(data)) {
                        if (typeof value === 'string') {
                            // Check for potential secrets (base64 encoded content)
                            if (value.length > 100 && /^[A-Za-z0-9+/]+=*$/.test(value)) {
                                return true;
                            }

                            // Check for very long values (>100KB)
                            if (value.length > 100 * 1024) {
                                return true;
                            }
                        }
                    }

                    return false;
                },

                getConfigMapWarnings(configMap) {
                    const warnings = [];
                    const data = configMap.data || {};
                    const keys = Object.keys(data);

                    // Check if no data
                    if (keys.length === 0) {
                        warnings.push('ConfigMap has no data');
                    }

                    // Check total size
                    let totalSize = 0;
                    Object.values(data).forEach(value => {
                        if (typeof value === 'string') {
                            totalSize += new Blob([value]).size;
                        }
                    });

                    if (totalSize > 1024 * 1024) { // 1MB
                        const sizeMB = Math.round(totalSize / (1024 * 1024) * 100) / 100;
                        warnings.push(`Large size: ${sizeMB}MB (consider using volumes)`);
                    }

                    // Check number of keys
                    if (keys.length > 50) {
                        warnings.push(`Too many keys: ${keys.length} (consider splitting)`);
                    }

                    // Check for potential secrets
                    let suspiciousKeys = 0;
                    for (const [key, value] of Object.entries(data)) {
                        if (typeof value === 'string') {
                            // Check for potential secrets (base64 encoded content)
                            if (value.length > 100 && /^[A-Za-z0-9+/]+=*$/.test(value)) {
                                suspiciousKeys++;
                            }

                            // Check for very long values
                            if (value.length > 100 * 1024) {
                                const sizeKB = Math.round(value.length / 1024);
                                warnings.push(`Large value in "${key}": ${sizeKB}KB`);
                            }
                        }
                    }

                    if (suspiciousKeys > 0) {
                        warnings.push(`${suspiciousKeys} key(s) may contain secrets (use Secret instead)`);
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                // Helper functions for secrets
                getSecretType(secret) {
                    return secret.type || 'Opaque';
                },

                getSecretKeys(secret) {
                    const data = secret.data || {};
                    const keys = Object.keys(data);

                    if (keys.length === 0) {
                        return 'No keys';
                    }

                    if (keys.length <= 3) {
                        return keys.join(', ');
                    }

                    return `${keys.slice(0, 3).join(', ')} +${keys.length - 3} more`;
                },

                getSecretSize(secret) {
                    const data = secret.data || {};
                    let totalSize = 0;

                    Object.values(data).forEach(value => {
                        if (typeof value === 'string') {
                            // Base64 encoded data - calculate decoded size
                            try {
                                const decodedSize = atob(value).length;
                                totalSize += decodedSize;
                            } catch (e) {
                                // If not valid base64, use string length
                                totalSize += new Blob([value]).size;
                            }
                        }
                    });

                    if (totalSize === 0) {
                        return '0 B';
                    }

                    const units = ['B', 'KB', 'MB', 'GB'];
                    let unitIndex = 0;
                    let size = totalSize;

                    while (size >= 1024 && unitIndex < units.length - 1) {
                        size /= 1024;
                        unitIndex++;
                    }

                    return `${Math.round(size * 100) / 100} ${units[unitIndex]}`;
                },

                getSecretTypeClass(secret) {
                    const type = secret.type || 'Opaque';
                    switch (type) {
                        case 'kubernetes.io/service-account-token':
                            return 'bg-blue-100 text-blue-800';
                        case 'kubernetes.io/dockercfg':
                        case 'kubernetes.io/dockerconfigjson':
                            return 'bg-purple-100 text-purple-800';
                        case 'kubernetes.io/tls':
                            return 'bg-green-100 text-green-800';
                        case 'kubernetes.io/ssh-auth':
                            return 'bg-orange-100 text-orange-800';
                        case 'kubernetes.io/basic-auth':
                            return 'bg-red-100 text-red-800';
                        case 'Opaque':
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                hasSecretWarnings(secret) {
                    const data = secret.data || {};
                    const keys = Object.keys(data);

                    // Warning if no data
                    if (keys.length === 0) {
                        return true;
                    }

                    // Warning if very large (>1MB total)
                    let totalSize = 0;
                    Object.values(data).forEach(value => {
                        if (typeof value === 'string') {
                            try {
                                const decodedSize = atob(value).length;
                                totalSize += decodedSize;
                            } catch (e) {
                                totalSize += new Blob([value]).size;
                            }
                        }
                    });

                    if (totalSize > 1024 * 1024) { // 1MB
                        return true;
                    }

                    // Warning if too many keys (>20 for secrets)
                    if (keys.length > 20) {
                        return true;
                    }

                    // Warning for expired certificates
                    if (secret.type === 'kubernetes.io/tls') {
                        const certData = secret.data['tls.crt'];
                        if (certData && this.isCertificateExpired(certData)) {
                            return true;
                        }
                    }

                    // Warning for old service account tokens
                    if (secret.type === 'kubernetes.io/service-account-token') {
                        const creationTime = new Date(secret.metadata?.creationTimestamp);
                        const now = new Date();
                        const ageInDays = (now - creationTime) / (1000 * 60 * 60 * 24);
                        if (ageInDays > 365) { // 1 year old
                            return true;
                        }
                    }

                    return false;
                },

                getSecretWarnings(secret) {
                    const warnings = [];
                    const data = secret.data || {};
                    const keys = Object.keys(data);

                    // Check if no data
                    if (keys.length === 0) {
                        warnings.push('Secret has no data');
                    }

                    // Check total size
                    let totalSize = 0;
                    Object.values(data).forEach(value => {
                        if (typeof value === 'string') {
                            try {
                                const decodedSize = atob(value).length;
                                totalSize += decodedSize;
                            } catch (e) {
                                totalSize += new Blob([value]).size;
                            }
                        }
                    });

                    if (totalSize > 1024 * 1024) { // 1MB
                        const sizeMB = Math.round(totalSize / (1024 * 1024) * 100) / 100;
                        warnings.push(`Large size: ${sizeMB}MB (consider external secret management)`);
                    }

                    // Check number of keys
                    if (keys.length > 20) {
                        warnings.push(`Too many keys: ${keys.length} (consider splitting)`);
                    }

                    // Check for expired certificates
                    if (secret.type === 'kubernetes.io/tls') {
                        const certData = secret.data['tls.crt'];
                        if (certData && this.isCertificateExpired(certData)) {
                            warnings.push('TLS certificate is expired or expiring soon');
                        }
                    }

                    // Check for old service account tokens
                    if (secret.type === 'kubernetes.io/service-account-token') {
                        const creationTime = new Date(secret.metadata?.creationTimestamp);
                        const now = new Date();
                        const ageInDays = (now - creationTime) / (1000 * 60 * 60 * 24);
                        if (ageInDays > 365) {
                            warnings.push(`Service account token is ${Math.floor(ageInDays)} days old`);
                        }
                    }

                    // Check for missing required keys based on type
                    if (secret.type === 'kubernetes.io/tls') {
                        if (!data['tls.crt']) warnings.push('Missing tls.crt key');
                        if (!data['tls.key']) warnings.push('Missing tls.key key');
                    } else if (secret.type === 'kubernetes.io/basic-auth') {
                        if (!data['username']) warnings.push('Missing username key');
                        if (!data['password']) warnings.push('Missing password key');
                    } else if (secret.type === 'kubernetes.io/ssh-auth') {
                        if (!data['ssh-privatekey']) warnings.push('Missing ssh-privatekey key');
                    }

                    return warnings.length > 0 ? warnings.join(', ') : 'No warnings';
                },

                isCertificateExpired(certData) {
                    try {
                        // Basic certificate expiration check
                        // This is a simplified check - in production you'd want a proper certificate parser
                        const cert = atob(certData);

                        // Look for validity period in the certificate
                        // This is a very basic implementation
                        const notAfterMatch = cert.match(/Not After\s*:\s*(.+)/);
                        if (notAfterMatch) {
                            const expiryDate = new Date(notAfterMatch[1]);
                            const now = new Date();
                            const daysUntilExpiry = (expiryDate - now) / (1000 * 60 * 60 * 24);

                            // Warn if expires within 30 days or already expired
                            return daysUntilExpiry < 30;
                        }

                        return false;
                    } catch (e) {
                        // If we can't parse the certificate, don't show warning
                        return false;
                    }
                },

                // Helper functions for resource quotas
                getResourceQuotaResources(quota) {
                    if (!quota.spec || !quota.spec.hard) {
                        return 'No limits set';
                    }

                    const resources = Object.keys(quota.spec.hard);
                    if (resources.length === 0) {
                        return 'No limits set';
                    }

                    if (resources.length <= 3) {
                        return resources.join(', ');
                    }

                    return `${resources.slice(0, 3).join(', ')} +${resources.length - 3} more`;
                },

                getResourceQuotaUsage(quota) {
                    if (!quota.status || !quota.status.hard || !quota.status.used) {
                        return 'No usage data';
                    }

                    const usageItems = [];
                    const hard = quota.status.hard;
                    const used = quota.status.used;

                    // Show top 3 most critical resources
                    const resourceUsage = [];

                    for (const resource in hard) {
                        if (used[resource]) {
                            const hardValue = this.parseResourceValue(hard[resource]);
                            const usedValue = this.parseResourceValue(used[resource]);

                            if (hardValue > 0) {
                                const percentage = Math.round((usedValue / hardValue) * 100);
                                resourceUsage.push({
                                    resource,
                                    percentage,
                                    used: used[resource],
                                    hard: hard[resource]
                                });
                            }
                        }
                    }

                    // Sort by percentage (highest first) and take top 3
                    resourceUsage.sort((a, b) => b.percentage - a.percentage);
                    const topResources = resourceUsage.slice(0, 3);

                    if (topResources.length === 0) {
                        return 'No usage data';
                    }

                    return topResources.map(item => {
                        const color = item.percentage >= 90 ? 'text-red-600' :
                                     item.percentage >= 75 ? 'text-yellow-600' : 'text-green-600';
                        return `<span class="${color}">${item.resource}: ${item.percentage}%</span>`;
                    }).join(', ');
                },

                parseResourceValue(value) {
                    if (!value) return 0;

                    // Handle string values with units
                    if (typeof value === 'string') {
                        // Remove units and convert to number
                        const numericValue = parseFloat(value.replace(/[^0-9.]/g, ''));

                        // Handle different units
                        if (value.includes('Ki')) return numericValue * 1024;
                        if (value.includes('Mi')) return numericValue * 1024 * 1024;
                        if (value.includes('Gi')) return numericValue * 1024 * 1024 * 1024;
                        if (value.includes('Ti')) return numericValue * 1024 * 1024 * 1024 * 1024;
                        if (value.includes('k')) return numericValue * 1000;
                        if (value.includes('M')) return numericValue * 1000 * 1000;
                        if (value.includes('G')) return numericValue * 1000 * 1000 * 1000;
                        if (value.includes('T')) return numericValue * 1000 * 1000 * 1000 * 1000;
                        if (value.includes('m')) return numericValue / 1000; // millicores

                        return numericValue;
                    }

                    return parseFloat(value) || 0;
                },

                hasResourceQuotaWarnings(quota) {
                    // Check if quota has no status
                    if (!quota.status) {
                        return true;
                    }

                    // Check if any resource is at or near its limit
                    if (quota.status.hard && quota.status.used) {
                        for (const resource in quota.status.hard) {
                            const hard = quota.status.hard[resource];
                            const used = quota.status.used[resource];

                            if (hard && used) {
                                const hardValue = this.parseResourceValue(hard);
                                const usedValue = this.parseResourceValue(used);

                                if (hardValue > 0 && usedValue >= hardValue * 0.9) {
                                    return true; // 90% or more usage
                                }
                            }
                        }
                    }

                    // Check if quota has no hard limits set
                    if (!quota.spec || !quota.spec.hard || Object.keys(quota.spec.hard).length === 0) {
                        return true;
                    }

                    return false;
                },

                getResourceQuotaWarnings(quota) {
                    const warnings = [];

                    // Check if quota has no status
                    if (!quota.status) {
                        warnings.push('No status information available');
                    }

                    // Check if quota has no hard limits
                    if (!quota.spec || !quota.spec.hard || Object.keys(quota.spec.hard).length === 0) {
                        warnings.push('No resource limits configured');
                    }

                    // Check resource usage
                    if (quota.status && quota.status.hard && quota.status.used) {
                        const criticalResources = [];
                        const warningResources = [];

                        for (const resource in quota.status.hard) {
                            const hard = quota.status.hard[resource];
                            const used = quota.status.used[resource];

                            if (hard && used) {
                                const hardValue = this.parseResourceValue(hard);
                                const usedValue = this.parseResourceValue(used);

                                if (hardValue > 0) {
                                    const percentage = (usedValue / hardValue) * 100;

                                    if (percentage >= 95) {
                                        criticalResources.push(`${resource} at ${Math.round(percentage)}%`);
                                    } else if (percentage >= 80) {
                                        warningResources.push(`${resource} at ${Math.round(percentage)}%`);
                                    }
                                }
                            }
                        }

                        if (criticalResources.length > 0) {
                            warnings.push(`Critical usage: ${criticalResources.join(', ')}`);
                        }

                        if (warningResources.length > 0) {
                            warnings.push(`High usage: ${warningResources.join(', ')}`);
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getResourceQuotaStatusClass(quota) {
                    if (!quota.status || !quota.status.hard || !quota.status.used) {
                        return 'bg-gray-100 text-gray-800';
                    }

                    let maxUsagePercentage = 0;

                    for (const resource in quota.status.hard) {
                        const hard = quota.status.hard[resource];
                        const used = quota.status.used[resource];

                        if (hard && used) {
                            const hardValue = this.parseResourceValue(hard);
                            const usedValue = this.parseResourceValue(used);

                            if (hardValue > 0) {
                                const percentage = (usedValue / hardValue) * 100;
                                maxUsagePercentage = Math.max(maxUsagePercentage, percentage);
                            }
                        }
                    }

                    if (maxUsagePercentage >= 95) {
                        return 'bg-red-100 text-red-800';
                    } else if (maxUsagePercentage >= 80) {
                        return 'bg-yellow-100 text-yellow-800';
                    } else if (maxUsagePercentage > 0) {
                        return 'bg-green-100 text-green-800';
                    } else {
                        return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for limit ranges
                getLimitRangeLimits(limitRange) {
                    if (!limitRange.spec || !limitRange.spec.limits || limitRange.spec.limits.length === 0) {
                        return 'No limits set';
                    }

                    const limits = limitRange.spec.limits;
                    const limitTypes = limits.map(limit => limit.type || 'Unknown').filter((type, index, self) => self.indexOf(type) === index);

                    if (limitTypes.length <= 2) {
                        return limitTypes.join(', ');
                    }

                    return `${limitTypes.slice(0, 2).join(', ')} +${limitTypes.length - 2} more`;
                },

                getLimitRangeDefaults(limitRange) {
                    if (!limitRange.spec || !limitRange.spec.limits || limitRange.spec.limits.length === 0) {
                        return 'No defaults';
                    }

                    const defaults = [];
                    limitRange.spec.limits.forEach(limit => {
                        if (limit.default) {
                            Object.keys(limit.default).forEach(resource => {
                                if (!defaults.includes(resource)) {
                                    defaults.push(resource);
                                }
                            });
                        }
                    });

                    if (defaults.length === 0) {
                        return 'No defaults';
                    }

                    if (defaults.length <= 3) {
                        return defaults.join(', ');
                    }

                    return `${defaults.slice(0, 3).join(', ')} +${defaults.length - 3} more`;
                },

                hasLimitRangeWarnings(limitRange) {
                    // Check if limit range has no limits
                    if (!limitRange.spec || !limitRange.spec.limits || limitRange.spec.limits.length === 0) {
                        return true;
                    }

                    // Check for conflicting or problematic limits
                    const limits = limitRange.spec.limits;

                    for (const limit of limits) {
                        // Check for missing max values
                        if (limit.min && !limit.max) {
                            return true;
                        }

                        // Check for min > max conflicts
                        if (limit.min && limit.max) {
                            for (const resource in limit.min) {
                                if (limit.max[resource]) {
                                    const minValue = this.parseResourceValue(limit.min[resource]);
                                    const maxValue = this.parseResourceValue(limit.max[resource]);

                                    if (minValue > maxValue) {
                                        return true; // Min is greater than max
                                    }
                                }
                            }
                        }

                        // Check for default values outside min/max range
                        if (limit.default) {
                            for (const resource in limit.default) {
                                const defaultValue = this.parseResourceValue(limit.default[resource]);

                                if (limit.min && limit.min[resource]) {
                                    const minValue = this.parseResourceValue(limit.min[resource]);
                                    if (defaultValue < minValue) {
                                        return true; // Default is less than min
                                    }
                                }

                                if (limit.max && limit.max[resource]) {
                                    const maxValue = this.parseResourceValue(limit.max[resource]);
                                    if (defaultValue > maxValue) {
                                        return true; // Default is greater than max
                                    }
                                }
                            }
                        }

                        // Check for very restrictive limits
                        if (limit.max) {
                            // Warn if CPU limit is very low (less than 100m)
                            if (limit.max.cpu) {
                                const cpuValue = this.parseResourceValue(limit.max.cpu);
                                if (cpuValue < 0.1) { // 100m = 0.1 CPU
                                    return true;
                                }
                            }

                            // Warn if memory limit is very low (less than 64Mi)
                            if (limit.max.memory) {
                                const memValue = this.parseResourceValue(limit.max.memory);
                                if (memValue < 64 * 1024 * 1024) { // 64Mi
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                },

                getLimitRangeWarnings(limitRange) {
                    const warnings = [];

                    // Check if limit range has no limits
                    if (!limitRange.spec || !limitRange.spec.limits || limitRange.spec.limits.length === 0) {
                        warnings.push('No limits configured');
                        return warnings.join('; ');
                    }

                    const limits = limitRange.spec.limits;

                    for (const limit of limits) {
                        const limitType = limit.type || 'Unknown';

                        // Check for missing max values
                        if (limit.min && !limit.max) {
                            warnings.push(`${limitType}: Has min but no max limits`);
                        }

                        // Check for min > max conflicts
                        if (limit.min && limit.max) {
                            for (const resource in limit.min) {
                                if (limit.max[resource]) {
                                    const minValue = this.parseResourceValue(limit.min[resource]);
                                    const maxValue = this.parseResourceValue(limit.max[resource]);

                                    if (minValue > maxValue) {
                                        warnings.push(`${limitType}: Min ${resource} (${limit.min[resource]}) > Max (${limit.max[resource]})`);
                                    }
                                }
                            }
                        }

                        // Check for default values outside min/max range
                        if (limit.default) {
                            for (const resource in limit.default) {
                                const defaultValue = this.parseResourceValue(limit.default[resource]);

                                if (limit.min && limit.min[resource]) {
                                    const minValue = this.parseResourceValue(limit.min[resource]);
                                    if (defaultValue < minValue) {
                                        warnings.push(`${limitType}: Default ${resource} (${limit.default[resource]}) < Min (${limit.min[resource]})`);
                                    }
                                }

                                if (limit.max && limit.max[resource]) {
                                    const maxValue = this.parseResourceValue(limit.max[resource]);
                                    if (defaultValue > maxValue) {
                                        warnings.push(`${limitType}: Default ${resource} (${limit.default[resource]}) > Max (${limit.max[resource]})`);
                                    }
                                }
                            }
                        }

                        // Check for very restrictive limits
                        if (limit.max) {
                            if (limit.max.cpu) {
                                const cpuValue = this.parseResourceValue(limit.max.cpu);
                                if (cpuValue < 0.1) {
                                    warnings.push(`${limitType}: Very low CPU limit (${limit.max.cpu})`);
                                }
                            }

                            if (limit.max.memory) {
                                const memValue = this.parseResourceValue(limit.max.memory);
                                if (memValue < 64 * 1024 * 1024) {
                                    warnings.push(`${limitType}: Very low memory limit (${limit.max.memory})`);
                                }
                            }
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getLimitRangeTypeClass(limitType) {
                    switch (limitType) {
                        case 'Container':
                            return 'bg-blue-100 text-blue-800';
                        case 'Pod':
                            return 'bg-green-100 text-green-800';
                        case 'PersistentVolumeClaim':
                            return 'bg-purple-100 text-purple-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for horizontal pod autoscalers
                getHPATarget(hpa) {
                    if (!hpa.spec || !hpa.spec.scaleTargetRef) {
                        return 'No target';
                    }

                    const target = hpa.spec.scaleTargetRef;
                    const kind = target.kind || 'Unknown';
                    const name = target.name || 'Unknown';

                    return `${kind}/${name}`;
                },

                getHPAReplicas(hpa) {
                    const current = hpa.status?.currentReplicas || 0;
                    const desired = hpa.status?.desiredReplicas || 0;
                    const min = hpa.spec?.minReplicas || 1;
                    const max = hpa.spec?.maxReplicas || 'N/A';

                    return `${current}/${desired} (${min}-${max})`;
                },

                getHPAMetrics(hpa) {
                    if (!hpa.spec || !hpa.spec.metrics || hpa.spec.metrics.length === 0) {
                        return 'No metrics';
                    }

                    const metrics = hpa.spec.metrics;
                    const metricTypes = [];

                    metrics.forEach(metric => {
                        if (metric.type === 'Resource') {
                            const resource = metric.resource?.name || 'unknown';
                            const target = metric.resource?.target;

                            if (target?.type === 'Utilization') {
                                metricTypes.push(`${resource}: ${target.averageUtilization || 'N/A'}%`);
                            } else if (target?.type === 'AverageValue') {
                                metricTypes.push(`${resource}: ${target.averageValue || 'N/A'}`);
                            } else {
                                metricTypes.push(`${resource}`);
                            }
                        } else if (metric.type === 'Pods') {
                            const target = metric.pods?.target;
                            metricTypes.push(`pods: ${target?.averageValue || 'N/A'}`);
                        } else if (metric.type === 'Object') {
                            const target = metric.object?.target;
                            metricTypes.push(`object: ${target?.value || 'N/A'}`);
                        } else if (metric.type === 'External') {
                            const target = metric.external?.target;
                            metricTypes.push(`external: ${target?.value || 'N/A'}`);
                        } else {
                            metricTypes.push(metric.type || 'unknown');
                        }
                    });

                    if (metricTypes.length <= 2) {
                        return metricTypes.join(', ');
                    }

                    return `${metricTypes.slice(0, 2).join(', ')} +${metricTypes.length - 2} more`;
                },

                hasHPAWarnings(hpa) {
                    // Check if HPA has no target
                    if (!hpa.spec || !hpa.spec.scaleTargetRef) {
                        return true;
                    }

                    // Check if HPA has no metrics
                    if (!hpa.spec.metrics || hpa.spec.metrics.length === 0) {
                        return true;
                    }

                    // Check for invalid replica ranges
                    const min = hpa.spec.minReplicas || 1;
                    const max = hpa.spec.maxReplicas;

                    if (max && min > max) {
                        return true; // Min replicas > max replicas
                    }

                    // Check for very low max replicas (might not handle load)
                    if (max && max < 2) {
                        return true;
                    }

                    // Check for very high max replicas (might cause resource issues)
                    if (max && max > 100) {
                        return true;
                    }

                    // Check for status conditions indicating problems
                    if (hpa.status && hpa.status.conditions) {
                        for (const condition of hpa.status.conditions) {
                            if (condition.type === 'ScalingActive' && condition.status === 'False') {
                                return true;
                            }
                            if (condition.type === 'AbleToScale' && condition.status === 'False') {
                                return true;
                            }
                            if (condition.type === 'ScalingLimited' && condition.status === 'True') {
                                return true;
                            }
                        }
                    }

                    // Check for missing current metrics
                    if (hpa.status && !hpa.status.currentMetrics) {
                        return true;
                    }

                    return false;
                },

                getHPAWarnings(hpa) {
                    const warnings = [];

                    // Check if HPA has no target
                    if (!hpa.spec || !hpa.spec.scaleTargetRef) {
                        warnings.push('No scale target configured');
                    }

                    // Check if HPA has no metrics
                    if (!hpa.spec.metrics || hpa.spec.metrics.length === 0) {
                        warnings.push('No metrics configured');
                    }

                    // Check for invalid replica ranges
                    const min = hpa.spec.minReplicas || 1;
                    const max = hpa.spec.maxReplicas;

                    if (max && min > max) {
                        warnings.push(`Min replicas (${min}) > Max replicas (${max})`);
                    }

                    // Check for very low max replicas
                    if (max && max < 2) {
                        warnings.push(`Very low max replicas (${max}) - may not handle load spikes`);
                    }

                    // Check for very high max replicas
                    if (max && max > 100) {
                        warnings.push(`Very high max replicas (${max}) - may cause resource exhaustion`);
                    }

                    // Check for status conditions indicating problems
                    if (hpa.status && hpa.status.conditions) {
                        for (const condition of hpa.status.conditions) {
                            if (condition.type === 'ScalingActive' && condition.status === 'False') {
                                warnings.push(`Scaling inactive: ${condition.reason || 'Unknown reason'}`);
                            }
                            if (condition.type === 'AbleToScale' && condition.status === 'False') {
                                warnings.push(`Unable to scale: ${condition.reason || 'Unknown reason'}`);
                            }
                            if (condition.type === 'ScalingLimited' && condition.status === 'True') {
                                warnings.push(`Scaling limited: ${condition.reason || 'Unknown reason'}`);
                            }
                        }
                    }

                    // Check for missing current metrics
                    if (hpa.status && !hpa.status.currentMetrics) {
                        warnings.push('No current metrics available');
                    }

                    // Check for target not found
                    if (hpa.status && hpa.status.conditions) {
                        const targetNotFound = hpa.status.conditions.find(c =>
                            c.reason === 'FailedGetScale' ||
                            c.reason === 'InvalidTargetType' ||
                            c.message?.includes('not found')
                        );
                        if (targetNotFound) {
                            warnings.push('Scale target not found or invalid');
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getHPAStatusClass(hpa) {
                    if (!hpa.status) {
                        return 'bg-gray-100 text-gray-800';
                    }

                    // Check conditions for status
                    if (hpa.status.conditions) {
                        const scalingActive = hpa.status.conditions.find(c => c.type === 'ScalingActive');
                        const ableToScale = hpa.status.conditions.find(c => c.type === 'AbleToScale');

                        if (ableToScale && ableToScale.status === 'False') {
                            return 'bg-red-100 text-red-800'; // Unable to scale
                        }

                        if (scalingActive && scalingActive.status === 'False') {
                            return 'bg-yellow-100 text-yellow-800'; // Scaling inactive
                        }

                        if (scalingActive && scalingActive.status === 'True') {
                            return 'bg-green-100 text-green-800'; // Active and working
                        }
                    }

                    // Check if current replicas match desired
                    const current = hpa.status.currentReplicas || 0;
                    const desired = hpa.status.desiredReplicas || 0;

                    if (current === desired && current > 0) {
                        return 'bg-green-100 text-green-800'; // Stable
                    } else if (current !== desired) {
                        return 'bg-blue-100 text-blue-800'; // Scaling
                    }

                    return 'bg-gray-100 text-gray-800'; // Unknown
                },

                // Helper functions for services
                getServiceType(service) {
                    return service.spec?.type || 'ClusterIP';
                },

                getServiceClusterIP(service) {
                    const clusterIP = service.spec?.clusterIP;
                    if (!clusterIP || clusterIP === 'None') {
                        return 'None';
                    }
                    return clusterIP;
                },

                getServiceExternalIP(service) {
                    const spec = service.spec || {};
                    const status = service.status || {};

                    // For LoadBalancer services, check status first
                    if (spec.type === 'LoadBalancer') {
                        if (status.loadBalancer && status.loadBalancer.ingress) {
                            const ingress = status.loadBalancer.ingress[0];
                            if (ingress.ip) {
                                return ingress.ip;
                            }
                            if (ingress.hostname) {
                                return ingress.hostname;
                            }
                        }
                        return '<pending>';
                    }

                    // For other services, check externalIPs
                    if (spec.externalIPs && spec.externalIPs.length > 0) {
                        if (spec.externalIPs.length === 1) {
                            return spec.externalIPs[0];
                        }
                        return `${spec.externalIPs[0]} +${spec.externalIPs.length - 1} more`;
                    }

                    // For NodePort services, show node port info
                    if (spec.type === 'NodePort') {
                        return '<nodes>';
                    }

                    return '—';
                },

                getServicePorts(service) {
                    const ports = service.spec?.ports || [];

                    if (ports.length === 0) {
                        return 'No ports';
                    }

                    if (ports.length === 1) {
                        const port = ports[0];
                        let portStr = `${port.port}`;

                        if (port.protocol && port.protocol !== 'TCP') {
                            portStr += `/${port.protocol}`;
                        }

                        if (port.nodePort) {
                            portStr += `:${port.nodePort}`;
                        }

                        return portStr;
                    }

                    // Multiple ports - show first port and count
                    const firstPort = ports[0];
                    let portStr = `${firstPort.port}`;

                    if (firstPort.protocol && firstPort.protocol !== 'TCP') {
                        portStr += `/${firstPort.protocol}`;
                    }

                    if (firstPort.nodePort) {
                        portStr += `:${firstPort.nodePort}`;
                    }

                    return `${portStr} +${ports.length - 1} more`;
                },

                hasServiceWarnings(service) {
                    const spec = service.spec || {};
                    const status = service.status || {};

                    // Check for LoadBalancer without external IP for extended time
                    if (spec.type === 'LoadBalancer') {
                        if (!status.loadBalancer || !status.loadBalancer.ingress) {
                            // Check if service is old (more than 5 minutes)
                            const created = new Date(service.metadata?.creationTimestamp);
                            const now = new Date();
                            const ageMinutes = (now - created) / (1000 * 60);

                            if (ageMinutes > 5) {
                                return true; // LoadBalancer pending for too long
                            }
                        }
                    }

                    // Check for services without selectors (might be misconfigured)
                    if (!spec.selector || Object.keys(spec.selector).length === 0) {
                        // Only warn if it's not a headless service or external service
                        if (spec.type !== 'ExternalName' && spec.clusterIP !== 'None') {
                            return true;
                        }
                    }

                    // Check for services without ports
                    if (!spec.ports || spec.ports.length === 0) {
                        return true;
                    }

                    // Check for NodePort services with conflicting ports
                    if (spec.type === 'NodePort' && spec.ports) {
                        const nodePorts = spec.ports.filter(p => p.nodePort).map(p => p.nodePort);
                        const uniqueNodePorts = [...new Set(nodePorts)];
                        if (nodePorts.length !== uniqueNodePorts.length) {
                            return true; // Duplicate node ports
                        }
                    }

                    // Check for services with very high port numbers (might be typos)
                    if (spec.ports) {
                        for (const port of spec.ports) {
                            if (port.port > 65535 || port.targetPort > 65535) {
                                return true; // Invalid port number
                            }
                            if (port.nodePort && (port.nodePort < 30000 || port.nodePort > 32767)) {
                                return true; // NodePort out of valid range
                            }
                        }
                    }

                    return false;
                },

                getServiceWarnings(service) {
                    const warnings = [];
                    const spec = service.spec || {};
                    const status = service.status || {};

                    // Check for LoadBalancer without external IP
                    if (spec.type === 'LoadBalancer') {
                        if (!status.loadBalancer || !status.loadBalancer.ingress) {
                            const created = new Date(service.metadata?.creationTimestamp);
                            const now = new Date();
                            const ageMinutes = (now - created) / (1000 * 60);

                            if (ageMinutes > 5) {
                                warnings.push(`LoadBalancer pending for ${Math.floor(ageMinutes)} minutes`);
                            }
                        }
                    }

                    // Check for services without selectors
                    if (!spec.selector || Object.keys(spec.selector).length === 0) {
                        if (spec.type !== 'ExternalName' && spec.clusterIP !== 'None') {
                            warnings.push('No selector configured - traffic may not reach pods');
                        }
                    }

                    // Check for services without ports
                    if (!spec.ports || spec.ports.length === 0) {
                        warnings.push('No ports configured');
                    }

                    // Check for NodePort conflicts
                    if (spec.type === 'NodePort' && spec.ports) {
                        const nodePorts = spec.ports.filter(p => p.nodePort).map(p => p.nodePort);
                        const uniqueNodePorts = [...new Set(nodePorts)];
                        if (nodePorts.length !== uniqueNodePorts.length) {
                            warnings.push('Duplicate NodePort numbers detected');
                        }
                    }

                    // Check for invalid port numbers
                    if (spec.ports) {
                        for (const port of spec.ports) {
                            if (port.port > 65535) {
                                warnings.push(`Invalid port number: ${port.port}`);
                            }
                            if (port.targetPort > 65535) {
                                warnings.push(`Invalid target port: ${port.targetPort}`);
                            }
                            if (port.nodePort && (port.nodePort < 30000 || port.nodePort > 32767)) {
                                warnings.push(`NodePort ${port.nodePort} outside valid range (30000-32767)`);
                            }
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getServiceTypeClass(service) {
                    const type = service.spec?.type || 'ClusterIP';

                    switch (type) {
                        case 'ClusterIP':
                            return 'bg-blue-100 text-blue-800';
                        case 'NodePort':
                            return 'bg-green-100 text-green-800';
                        case 'LoadBalancer':
                            return 'bg-purple-100 text-purple-800';
                        case 'ExternalName':
                            return 'bg-orange-100 text-orange-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for endpoints
                getEndpointAddresses(endpoint) {
                    const subsets = endpoint.subsets || [];

                    if (subsets.length === 0) {
                        return 'No endpoints';
                    }

                    let allAddresses = [];

                    subsets.forEach(subset => {
                        const addresses = subset.addresses || [];
                        const notReadyAddresses = subset.notReadyAddresses || [];

                        // Add ready addresses
                        addresses.forEach(addr => {
                            allAddresses.push({
                                ip: addr.ip,
                                ready: true,
                                hostname: addr.hostname,
                                nodeName: addr.nodeName
                            });
                        });

                        // Add not ready addresses
                        notReadyAddresses.forEach(addr => {
                            allAddresses.push({
                                ip: addr.ip,
                                ready: false,
                                hostname: addr.hostname,
                                nodeName: addr.nodeName
                            });
                        });
                    });

                    if (allAddresses.length === 0) {
                        return 'No addresses';
                    }

                    if (allAddresses.length === 1) {
                        const addr = allAddresses[0];
                        let display = addr.ip;
                        if (!addr.ready) {
                            display += ' (not ready)';
                        }
                        return display;
                    }

                    // Multiple addresses - show first few and count
                    const readyCount = allAddresses.filter(a => a.ready).length;
                    const notReadyCount = allAddresses.filter(a => !a.ready).length;

                    let display = allAddresses[0].ip;
                    if (allAddresses.length > 1) {
                        display += ` +${allAddresses.length - 1} more`;
                    }

                    if (notReadyCount > 0) {
                        display += ` (${notReadyCount} not ready)`;
                    }

                    return display;
                },

                getEndpointPorts(endpoint) {
                    const subsets = endpoint.subsets || [];

                    if (subsets.length === 0) {
                        return 'No ports';
                    }

                    let allPorts = [];

                    subsets.forEach(subset => {
                        const ports = subset.ports || [];
                        ports.forEach(port => {
                            const portInfo = {
                                port: port.port,
                                protocol: port.protocol || 'TCP',
                                name: port.name
                            };

                            // Avoid duplicates
                            const exists = allPorts.some(p =>
                                p.port === portInfo.port &&
                                p.protocol === portInfo.protocol
                            );

                            if (!exists) {
                                allPorts.push(portInfo);
                            }
                        });
                    });

                    if (allPorts.length === 0) {
                        return 'No ports';
                    }

                    if (allPorts.length === 1) {
                        const port = allPorts[0];
                        let display = `${port.port}`;
                        if (port.protocol !== 'TCP') {
                            display += `/${port.protocol}`;
                        }
                        if (port.name) {
                            display += ` (${port.name})`;
                        }
                        return display;
                    }

                    // Multiple ports
                    const firstPort = allPorts[0];
                    let display = `${firstPort.port}`;
                    if (firstPort.protocol !== 'TCP') {
                        display += `/${firstPort.protocol}`;
                    }

                    if (allPorts.length > 1) {
                        display += ` +${allPorts.length - 1} more`;
                    }

                    return display;
                },

                hasEndpointWarnings(endpoint) {
                    const subsets = endpoint.subsets || [];

                    // Check if endpoint has no subsets
                    if (subsets.length === 0) {
                        return true;
                    }

                    let hasReadyAddresses = false;
                    let hasNotReadyAddresses = false;
                    let hasPorts = false;

                    subsets.forEach(subset => {
                        const addresses = subset.addresses || [];
                        const notReadyAddresses = subset.notReadyAddresses || [];
                        const ports = subset.ports || [];

                        if (addresses.length > 0) {
                            hasReadyAddresses = true;
                        }

                        if (notReadyAddresses.length > 0) {
                            hasNotReadyAddresses = true;
                        }

                        if (ports.length > 0) {
                            hasPorts = true;
                        }
                    });

                    // Warn if no ready addresses
                    if (!hasReadyAddresses) {
                        return true;
                    }

                    // Warn if there are not ready addresses
                    if (hasNotReadyAddresses) {
                        return true;
                    }

                    // Warn if no ports defined
                    if (!hasPorts) {
                        return true;
                    }

                    return false;
                },

                getEndpointWarnings(endpoint) {
                    const warnings = [];
                    const subsets = endpoint.subsets || [];

                    // Check if endpoint has no subsets
                    if (subsets.length === 0) {
                        warnings.push('No endpoint subsets defined');
                        return warnings.join('; ');
                    }

                    let hasReadyAddresses = false;
                    let hasNotReadyAddresses = false;
                    let hasPorts = false;
                    let notReadyCount = 0;

                    subsets.forEach(subset => {
                        const addresses = subset.addresses || [];
                        const notReadyAddresses = subset.notReadyAddresses || [];
                        const ports = subset.ports || [];

                        if (addresses.length > 0) {
                            hasReadyAddresses = true;
                        }

                        if (notReadyAddresses.length > 0) {
                            hasNotReadyAddresses = true;
                            notReadyCount += notReadyAddresses.length;
                        }

                        if (ports.length > 0) {
                            hasPorts = true;
                        }
                    });

                    // Warn if no ready addresses
                    if (!hasReadyAddresses) {
                        warnings.push('No ready endpoint addresses');
                    }

                    // Warn if there are not ready addresses
                    if (hasNotReadyAddresses) {
                        warnings.push(`${notReadyCount} endpoint(s) not ready`);
                    }

                    // Warn if no ports defined
                    if (!hasPorts) {
                        warnings.push('No ports defined');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for ingresses
                getIngressClass(ingress) {
                    // Check for ingressClassName (newer API)
                    if (ingress.spec?.ingressClassName) {
                        return ingress.spec.ingressClassName;
                    }

                    // Check for annotation (older API)
                    const annotations = ingress.metadata?.annotations || {};
                    const classAnnotation = annotations['kubernetes.io/ingress.class'] ||
                                          annotations['nginx.ingress.kubernetes.io/ingress.class'];

                    if (classAnnotation) {
                        return classAnnotation;
                    }

                    return 'default';
                },

                getIngressHosts(ingress) {
                    const rules = ingress.spec?.rules || [];

                    if (rules.length === 0) {
                        return '*';
                    }

                    const hosts = rules.map(rule => rule.host || '*').filter(host => host);

                    if (hosts.length === 0) {
                        return '*';
                    }

                    if (hosts.length === 1) {
                        return hosts[0];
                    }

                    if (hosts.length <= 3) {
                        return hosts.join(', ');
                    }

                    return `${hosts.slice(0, 2).join(', ')} +${hosts.length - 2} more`;
                },

                getIngressAddress(ingress) {
                    const status = ingress.status || {};
                    const loadBalancer = status.loadBalancer || {};
                    const ingresses = loadBalancer.ingress || [];

                    if (ingresses.length === 0) {
                        return '<pending>';
                    }

                    const addresses = [];
                    ingresses.forEach(ing => {
                        if (ing.ip) {
                            addresses.push(ing.ip);
                        } else if (ing.hostname) {
                            addresses.push(ing.hostname);
                        }
                    });

                    if (addresses.length === 0) {
                        return '<pending>';
                    }

                    if (addresses.length === 1) {
                        return addresses[0];
                    }

                    return `${addresses[0]} +${addresses.length - 1} more`;
                },

                getIngressPorts(ingress) {
                    const tls = ingress.spec?.tls || [];
                    const rules = ingress.spec?.rules || [];

                    const ports = new Set();

                    // Check if TLS is configured
                    if (tls.length > 0) {
                        ports.add('443');
                    }

                    // Check for HTTP rules
                    if (rules.length > 0) {
                        ports.add('80');
                    }

                    // Check for custom ports in annotations
                    const annotations = ingress.metadata?.annotations || {};
                    const serverPortAnnotation = annotations['nginx.ingress.kubernetes.io/server-port'];
                    if (serverPortAnnotation) {
                        ports.add(serverPortAnnotation);
                    }

                    if (ports.size === 0) {
                        return '80';
                    }

                    return Array.from(ports).sort().join(', ');
                },

                hasIngressWarnings(ingress) {
                    const spec = ingress.spec || {};
                    const status = ingress.status || {};

                    // Check for ingress without rules
                    if (!spec.rules || spec.rules.length === 0) {
                        return true;
                    }

                    // Check for rules without paths
                    const hasValidPaths = spec.rules.some(rule => {
                        if (!rule.http || !rule.http.paths) {
                            return false;
                        }
                        return rule.http.paths.length > 0;
                    });

                    if (!hasValidPaths) {
                        return true;
                    }

                    // Check for missing backend services
                    const hasInvalidBackends = spec.rules.some(rule => {
                        if (!rule.http || !rule.http.paths) {
                            return false;
                        }

                        return rule.http.paths.some(path => {
                            const backend = path.backend;
                            if (!backend) {
                                return true;
                            }

                            // Check for service backend
                            if (backend.service) {
                                return !backend.service.name;
                            }

                            // Check for resource backend
                            if (backend.resource) {
                                return !backend.resource.name;
                            }

                            return true;
                        });
                    });

                    if (hasInvalidBackends) {
                        return true;
                    }

                    // Check for TLS configuration issues
                    const tls = spec.tls || [];
                    const hasInvalidTLS = tls.some(tlsConfig => {
                        // Check for TLS without secret name
                        if (!tlsConfig.secretName) {
                            return true;
                        }

                        // Check for TLS without hosts
                        if (!tlsConfig.hosts || tlsConfig.hosts.length === 0) {
                            return true;
                        }

                        return false;
                    });

                    if (hasInvalidTLS) {
                        return true;
                    }

                    // Check for pending load balancer
                    if (!status.loadBalancer || !status.loadBalancer.ingress || status.loadBalancer.ingress.length === 0) {
                        // Check if ingress is old (more than 5 minutes)
                        const created = new Date(ingress.metadata?.creationTimestamp);
                        const now = new Date();
                        const ageMinutes = (now - created) / (1000 * 60);

                        if (ageMinutes > 5) {
                            return true; // Load balancer pending for too long
                        }
                    }

                    return false;
                },

                getIngressWarnings(ingress) {
                    const warnings = [];
                    const spec = ingress.spec || {};
                    const status = ingress.status || {};

                    // Check for ingress without rules
                    if (!spec.rules || spec.rules.length === 0) {
                        warnings.push('No ingress rules configured');
                    }

                    // Check for rules without paths
                    const hasValidPaths = spec.rules.some(rule => {
                        if (!rule.http || !rule.http.paths) {
                            return false;
                        }
                        return rule.http.paths.length > 0;
                    });

                    if (!hasValidPaths) {
                        warnings.push('No HTTP paths configured in rules');
                    }

                    // Check for missing backend services
                    const invalidBackends = [];
                    spec.rules.forEach((rule, ruleIndex) => {
                        if (!rule.http || !rule.http.paths) {
                            return;
                        }

                        rule.http.paths.forEach((path, pathIndex) => {
                            const backend = path.backend;
                            if (!backend) {
                                invalidBackends.push(`Rule ${ruleIndex + 1}, Path ${pathIndex + 1}: No backend`);
                                return;
                            }

                            // Check for service backend
                            if (backend.service && !backend.service.name) {
                                invalidBackends.push(`Rule ${ruleIndex + 1}, Path ${pathIndex + 1}: No service name`);
                            }

                            // Check for resource backend
                            if (backend.resource && !backend.resource.name) {
                                invalidBackends.push(`Rule ${ruleIndex + 1}, Path ${pathIndex + 1}: No resource name`);
                            }
                        });
                    });

                    if (invalidBackends.length > 0) {
                        warnings.push(`Invalid backends: ${invalidBackends.join('; ')}`);
                    }

                    // Check for TLS configuration issues
                    const tls = spec.tls || [];
                    const tlsIssues = [];
                    tls.forEach((tlsConfig, index) => {
                        if (!tlsConfig.secretName) {
                            tlsIssues.push(`TLS ${index + 1}: No secret name`);
                        }

                        if (!tlsConfig.hosts || tlsConfig.hosts.length === 0) {
                            tlsIssues.push(`TLS ${index + 1}: No hosts specified`);
                        }
                    });

                    if (tlsIssues.length > 0) {
                        warnings.push(`TLS issues: ${tlsIssues.join('; ')}`);
                    }

                    // Check for pending load balancer
                    if (!status.loadBalancer || !status.loadBalancer.ingress || status.loadBalancer.ingress.length === 0) {
                        const created = new Date(ingress.metadata?.creationTimestamp);
                        const now = new Date();
                        const ageMinutes = (now - created) / (1000 * 60);

                        if (ageMinutes > 5) {
                            warnings.push(`Load balancer pending for ${Math.floor(ageMinutes)} minutes`);
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getIngressClassBadgeClass(ingress) {
                    const ingressClass = this.getIngressClass(ingress);

                    switch (ingressClass.toLowerCase()) {
                        case 'nginx':
                            return 'bg-green-100 text-green-800';
                        case 'traefik':
                            return 'bg-blue-100 text-blue-800';
                        case 'haproxy':
                            return 'bg-purple-100 text-purple-800';
                        case 'istio':
                            return 'bg-indigo-100 text-indigo-800';
                        case 'ambassador':
                            return 'bg-pink-100 text-pink-800';
                        case 'contour':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'default':
                            return 'bg-gray-100 text-gray-800';
                        default:
                            return 'bg-orange-100 text-orange-800';
                    }
                },

                // Helper functions for ingress classes
                getIngressClassController(ingressClass) {
                    return ingressClass.spec?.controller || 'Unknown';
                },

                getIngressClassParameters(ingressClass) {
                    const parameters = ingressClass.spec?.parameters;

                    if (!parameters) {
                        return 'None';
                    }

                    if (parameters.apiGroup && parameters.kind && parameters.name) {
                        return `${parameters.kind}/${parameters.name}`;
                    }

                    if (parameters.kind && parameters.name) {
                        return `${parameters.kind}/${parameters.name}`;
                    }

                    if (parameters.name) {
                        return parameters.name;
                    }

                    return 'Configured';
                },

                isIngressClassDefault(ingressClass) {
                    // Check for default annotation
                    const annotations = ingressClass.metadata?.annotations || {};
                    const isDefault = annotations['ingressclass.kubernetes.io/is-default-class'];

                    return isDefault === 'true';
                },

                hasIngressClassWarnings(ingressClass) {
                    const spec = ingressClass.spec || {};

                    // Check for missing controller
                    if (!spec.controller) {
                        return true;
                    }

                    // Check for invalid controller format
                    if (spec.controller && !spec.controller.includes('/')) {
                        return true; // Controller should be in domain/name format
                    }

                    // Check for parameters configuration issues
                    if (spec.parameters) {
                        const params = spec.parameters;

                        // Check for missing required fields
                        if (!params.kind || !params.name) {
                            return true;
                        }

                        // Check for invalid API group
                        if (params.apiGroup === '') {
                            return true; // Empty API group should be null
                        }
                    }

                    return false;
                },

                getIngressClassWarnings(ingressClass) {
                    const warnings = [];
                    const spec = ingressClass.spec || {};

                    // Check for missing controller
                    if (!spec.controller) {
                        warnings.push('No controller specified');
                    }

                    // Check for invalid controller format
                    if (spec.controller && !spec.controller.includes('/')) {
                        warnings.push('Controller should be in domain/name format (e.g., k8s.io/ingress-nginx)');
                    }

                    // Check for parameters configuration issues
                    if (spec.parameters) {
                        const params = spec.parameters;

                        // Check for missing required fields
                        if (!params.kind) {
                            warnings.push('Parameters missing kind field');
                        }

                        if (!params.name) {
                            warnings.push('Parameters missing name field');
                        }

                        // Check for invalid API group
                        if (params.apiGroup === '') {
                            warnings.push('Parameters API group should be null for core resources, not empty string');
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getIngressClassControllerBadgeClass(ingressClass) {
                    const controller = ingressClass.spec?.controller || '';

                    if (controller.includes('nginx')) {
                        return 'bg-green-100 text-green-800';
                    } else if (controller.includes('traefik')) {
                        return 'bg-blue-100 text-blue-800';
                    } else if (controller.includes('haproxy')) {
                        return 'bg-purple-100 text-purple-800';
                    } else if (controller.includes('istio')) {
                        return 'bg-indigo-100 text-indigo-800';
                    } else if (controller.includes('ambassador')) {
                        return 'bg-pink-100 text-pink-800';
                    } else if (controller.includes('contour')) {
                        return 'bg-yellow-100 text-yellow-800';
                    } else if (controller.includes('kong')) {
                        return 'bg-cyan-100 text-cyan-800';
                    } else {
                        return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for network policies
                getNetworkPolicyPodSelector(networkPolicy) {
                    const podSelector = networkPolicy.spec?.podSelector;

                    if (!podSelector) {
                        return 'All pods';
                    }

                    const matchLabels = podSelector.matchLabels || {};
                    const matchExpressions = podSelector.matchExpressions || [];

                    // If no selectors, it matches all pods in the namespace
                    if (Object.keys(matchLabels).length === 0 && matchExpressions.length === 0) {
                        return 'All pods';
                    }

                    const selectors = [];

                    // Add match labels
                    Object.entries(matchLabels).forEach(([key, value]) => {
                        selectors.push(`${key}=${value}`);
                    });

                    // Add match expressions
                    matchExpressions.forEach(expr => {
                        const operator = expr.operator || 'In';
                        const values = expr.values || [];

                        switch (operator) {
                            case 'In':
                                if (values.length === 1) {
                                    selectors.push(`${expr.key}=${values[0]}`);
                                } else {
                                    selectors.push(`${expr.key} in (${values.join(',')})`);
                                }
                                break;
                            case 'NotIn':
                                selectors.push(`${expr.key} not in (${values.join(',')})`);
                                break;
                            case 'Exists':
                                selectors.push(`${expr.key}`);
                                break;
                            case 'DoesNotExist':
                                selectors.push(`!${expr.key}`);
                                break;
                            default:
                                selectors.push(`${expr.key} ${operator} ${values.join(',')}`);
                        }
                    });

                    if (selectors.length === 0) {
                        return 'All pods';
                    }

                    if (selectors.length === 1) {
                        return selectors[0];
                    }

                    if (selectors.length <= 2) {
                        return selectors.join(', ');
                    }

                    return `${selectors[0]} +${selectors.length - 1} more`;
                },

                getNetworkPolicyTypes(networkPolicy) {
                    const policyTypes = networkPolicy.spec?.policyTypes || [];

                    if (policyTypes.length === 0) {
                        return 'None';
                    }

                    return policyTypes.join(', ');
                },

                hasNetworkPolicyWarnings(networkPolicy) {
                    const spec = networkPolicy.spec || {};

                    // Check for policy without any rules
                    const hasIngress = spec.ingress && spec.ingress.length > 0;
                    const hasEgress = spec.egress && spec.egress.length > 0;
                    const policyTypes = spec.policyTypes || [];

                    // Check if policy types are specified but no corresponding rules
                    if (policyTypes.includes('Ingress') && !hasIngress) {
                        return true; // Ingress policy type but no ingress rules (denies all)
                    }

                    if (policyTypes.includes('Egress') && !hasEgress) {
                        return true; // Egress policy type but no egress rules (denies all)
                    }

                    // Check for empty pod selector (affects all pods in namespace)
                    const podSelector = spec.podSelector;
                    if (!podSelector || (Object.keys(podSelector.matchLabels || {}).length === 0 &&
                                        (podSelector.matchExpressions || []).length === 0)) {
                        return true; // Policy affects all pods in namespace
                    }

                    // Check for overly broad selectors
                    if (spec.ingress) {
                        for (const rule of spec.ingress) {
                            if (!rule.from || rule.from.length === 0) {
                                return true; // Allows traffic from anywhere
                            }

                            // Check for very broad selectors
                            for (const from of rule.from) {
                                if (from.namespaceSelector &&
                                    Object.keys(from.namespaceSelector.matchLabels || {}).length === 0 &&
                                    (from.namespaceSelector.matchExpressions || []).length === 0) {
                                    return true; // Allows from all namespaces
                                }
                            }
                        }
                    }

                    if (spec.egress) {
                        for (const rule of spec.egress) {
                            if (!rule.to || rule.to.length === 0) {
                                return true; // Allows traffic to anywhere
                            }

                            // Check for very broad selectors
                            for (const to of rule.to) {
                                if (to.namespaceSelector &&
                                    Object.keys(to.namespaceSelector.matchLabels || {}).length === 0 &&
                                    (to.namespaceSelector.matchExpressions || []).length === 0) {
                                    return true; // Allows to all namespaces
                                }
                            }
                        }
                    }

                    return false;
                },

                getNetworkPolicyWarnings(networkPolicy) {
                    const warnings = [];
                    const spec = networkPolicy.spec || {};

                    // Check for policy without any rules
                    const hasIngress = spec.ingress && spec.ingress.length > 0;
                    const hasEgress = spec.egress && spec.egress.length > 0;
                    const policyTypes = spec.policyTypes || [];

                    // Check if policy types are specified but no corresponding rules
                    if (policyTypes.includes('Ingress') && !hasIngress) {
                        warnings.push('Ingress policy type specified but no ingress rules (denies all ingress)');
                    }

                    if (policyTypes.includes('Egress') && !hasEgress) {
                        warnings.push('Egress policy type specified but no egress rules (denies all egress)');
                    }

                    // Check for empty pod selector
                    const podSelector = spec.podSelector;
                    if (!podSelector || (Object.keys(podSelector.matchLabels || {}).length === 0 &&
                                        (podSelector.matchExpressions || []).length === 0)) {
                        warnings.push('Policy affects all pods in namespace');
                    }

                    // Check for overly broad ingress rules
                    if (spec.ingress) {
                        for (const rule of spec.ingress) {
                            if (!rule.from || rule.from.length === 0) {
                                warnings.push('Ingress rule allows traffic from anywhere');
                            } else {
                                for (const from of rule.from) {
                                    if (from.namespaceSelector &&
                                        Object.keys(from.namespaceSelector.matchLabels || {}).length === 0 &&
                                        (from.namespaceSelector.matchExpressions || []).length === 0) {
                                        warnings.push('Ingress rule allows traffic from all namespaces');
                                    }
                                }
                            }
                        }
                    }

                    // Check for overly broad egress rules
                    if (spec.egress) {
                        for (const rule of spec.egress) {
                            if (!rule.to || rule.to.length === 0) {
                                warnings.push('Egress rule allows traffic to anywhere');
                            } else {
                                for (const to of rule.to) {
                                    if (to.namespaceSelector &&
                                        Object.keys(to.namespaceSelector.matchLabels || {}).length === 0 &&
                                        (to.namespaceSelector.matchExpressions || []).length === 0) {
                                        warnings.push('Egress rule allows traffic to all namespaces');
                                    }
                                }
                            }
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getNetworkPolicyTypesBadgeClass(networkPolicy) {
                    const policyTypes = networkPolicy.spec?.policyTypes || [];

                    if (policyTypes.length === 0) {
                        return 'bg-gray-100 text-gray-800';
                    }

                    if (policyTypes.includes('Ingress') && policyTypes.includes('Egress')) {
                        return 'bg-purple-100 text-purple-800'; // Both ingress and egress
                    } else if (policyTypes.includes('Ingress')) {
                        return 'bg-blue-100 text-blue-800'; // Ingress only
                    } else if (policyTypes.includes('Egress')) {
                        return 'bg-green-100 text-green-800'; // Egress only
                    }

                    return 'bg-gray-100 text-gray-800';
                },

                // Helper functions for persistent volume claims
                getPVCStatus(pvc) {
                    return pvc.status?.phase || 'Unknown';
                },

                getPVCVolume(pvc) {
                    return pvc.spec?.volumeName || 'Unbound';
                },

                getPVCCapacity(pvc) {
                    const requests = pvc.spec?.resources?.requests;
                    if (requests && requests.storage) {
                        return requests.storage;
                    }

                    // If bound, get actual capacity from status
                    const capacity = pvc.status?.capacity;
                    if (capacity && capacity.storage) {
                        return capacity.storage;
                    }

                    return 'Unknown';
                },

                getPVCAccessModes(pvc) {
                    const accessModes = pvc.spec?.accessModes || [];

                    if (accessModes.length === 0) {
                        return 'None';
                    }

                    // Convert to short forms
                    const shortModes = accessModes.map(mode => {
                        switch (mode) {
                            case 'ReadWriteOnce':
                                return 'RWO';
                            case 'ReadOnlyMany':
                                return 'ROX';
                            case 'ReadWriteMany':
                                return 'RWX';
                            case 'ReadWriteOncePod':
                                return 'RWOP';
                            default:
                                return mode;
                        }
                    });

                    return shortModes.join(', ');
                },

                getPVCStorageClass(pvc) {
                    return pvc.spec?.storageClassName || 'default';
                },

                hasPVCWarnings(pvc) {
                    const status = pvc.status?.phase;

                    // Check for pending state
                    if (status === 'Pending') {
                        return true;
                    }

                    // Check for lost state
                    if (status === 'Lost') {
                        return true;
                    }

                    // Check for no storage class
                    if (!pvc.spec?.storageClassName) {
                        return true;
                    }

                    // Check for no access modes
                    if (!pvc.spec?.accessModes || pvc.spec.accessModes.length === 0) {
                        return true;
                    }

                    // Check for no storage request
                    if (!pvc.spec?.resources?.requests?.storage) {
                        return true;
                    }

                    // Check for conditions indicating problems
                    const conditions = pvc.status?.conditions || [];
                    const hasProblems = conditions.some(condition => {
                        return condition.type === 'Resizing' && condition.status === 'False' ||
                               condition.type === 'FileSystemResizePending' && condition.status === 'True';
                    });

                    if (hasProblems) {
                        return true;
                    }

                    return false;
                },

                getPVCWarnings(pvc) {
                    const warnings = [];
                    const status = pvc.status?.phase;

                    // Check for pending state
                    if (status === 'Pending') {
                        warnings.push('PVC is pending - waiting for volume provisioning');
                    }

                    // Check for lost state
                    if (status === 'Lost') {
                        warnings.push('PVC is lost - bound volume no longer exists');
                    }

                    // Check for no storage class
                    if (!pvc.spec?.storageClassName) {
                        warnings.push('No storage class specified - using default');
                    }

                    // Check for no access modes
                    if (!pvc.spec?.accessModes || pvc.spec.accessModes.length === 0) {
                        warnings.push('No access modes specified');
                    }

                    // Check for no storage request
                    if (!pvc.spec?.resources?.requests?.storage) {
                        warnings.push('No storage size requested');
                    }

                    // Check for conditions indicating problems
                    const conditions = pvc.status?.conditions || [];
                    conditions.forEach(condition => {
                        if (condition.type === 'Resizing' && condition.status === 'False') {
                            warnings.push(`Volume resize failed: ${condition.message || 'Unknown error'}`);
                        }
                        if (condition.type === 'FileSystemResizePending' && condition.status === 'True') {
                            warnings.push('File system resize pending - restart pod to complete');
                        }
                    });

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getPVCStatusBadgeClass(pvc) {
                    const status = pvc.status?.phase;

                    switch (status) {
                        case 'Bound':
                            return 'bg-green-100 text-green-800';
                        case 'Pending':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'Lost':
                            return 'bg-red-100 text-red-800';
                        case 'Available':
                            return 'bg-blue-100 text-blue-800';
                        case 'Released':
                            return 'bg-gray-100 text-gray-800';
                        case 'Failed':
                            return 'bg-red-100 text-red-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for persistent volumes
                getPVCapacity(pv) {
                    const capacity = pv.spec?.capacity;
                    if (capacity && capacity.storage) {
                        return capacity.storage;
                    }
                    return 'Unknown';
                },

                getPVAccessModes(pv) {
                    const accessModes = pv.spec?.accessModes || [];

                    if (accessModes.length === 0) {
                        return 'None';
                    }

                    // Convert to short forms
                    const shortModes = accessModes.map(mode => {
                        switch (mode) {
                            case 'ReadWriteOnce':
                                return 'RWO';
                            case 'ReadOnlyMany':
                                return 'ROX';
                            case 'ReadWriteMany':
                                return 'RWX';
                            case 'ReadWriteOncePod':
                                return 'RWOP';
                            default:
                                return mode;
                        }
                    });

                    return shortModes.join(', ');
                },

                getPVReclaimPolicy(pv) {
                    return pv.spec?.persistentVolumeReclaimPolicy || 'Retain';
                },

                getPVStatus(pv) {
                    return pv.status?.phase || 'Unknown';
                },

                getPVClaim(pv) {
                    const claimRef = pv.spec?.claimRef;
                    if (claimRef) {
                        const namespace = claimRef.namespace || 'default';
                        const name = claimRef.name;
                        return `${namespace}/${name}`;
                    }
                    return 'Unbound';
                },

                getPVStorageClass(pv) {
                    return pv.spec?.storageClassName || 'default';
                },

                getPVReason(pv) {
                    return pv.status?.reason || '—';
                },

                hasPVWarnings(pv) {
                    const status = pv.status?.phase;

                    // Check for failed state
                    if (status === 'Failed') {
                        return true;
                    }

                    // Check for pending state
                    if (status === 'Pending') {
                        return true;
                    }

                    // Check for released state (might indicate orphaned volume)
                    if (status === 'Released') {
                        return true;
                    }

                    // Check for no capacity
                    if (!pv.spec?.capacity?.storage) {
                        return true;
                    }

                    // Check for no access modes
                    if (!pv.spec?.accessModes || pv.spec.accessModes.length === 0) {
                        return true;
                    }

                    // Check for no reclaim policy
                    if (!pv.spec?.persistentVolumeReclaimPolicy) {
                        return true;
                    }

                    return false;
                },

                getPVWarnings(pv) {
                    const warnings = [];
                    const status = pv.status?.phase;

                    // Check for failed state
                    if (status === 'Failed') {
                        const reason = pv.status?.reason || 'Unknown error';
                        warnings.push(`Volume failed: ${reason}`);
                    }

                    // Check for pending state
                    if (status === 'Pending') {
                        warnings.push('Volume is pending - waiting for provisioning');
                    }

                    // Check for released state
                    if (status === 'Released') {
                        warnings.push('Volume is released - claim was deleted but volume not reclaimed');
                    }

                    // Check for no capacity
                    if (!pv.spec?.capacity?.storage) {
                        warnings.push('No storage capacity specified');
                    }

                    // Check for no access modes
                    if (!pv.spec?.accessModes || pv.spec.accessModes.length === 0) {
                        warnings.push('No access modes specified');
                    }

                    // Check for no reclaim policy
                    if (!pv.spec?.persistentVolumeReclaimPolicy) {
                        warnings.push('No reclaim policy specified');
                    }

                    // Check for bound volume without claim reference
                    if (status === 'Bound' && !pv.spec?.claimRef) {
                        warnings.push('Volume is bound but has no claim reference');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getPVStatusBadgeClass(pv) {
                    const status = pv.status?.phase;

                    switch (status) {
                        case 'Available':
                            return 'bg-blue-100 text-blue-800';
                        case 'Bound':
                            return 'bg-green-100 text-green-800';
                        case 'Released':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'Failed':
                            return 'bg-red-100 text-red-800';
                        case 'Pending':
                            return 'bg-orange-100 text-orange-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                getPVReclaimPolicyBadgeClass(pv) {
                    const policy = pv.spec?.persistentVolumeReclaimPolicy;

                    switch (policy) {
                        case 'Retain':
                            return 'bg-blue-100 text-blue-800';
                        case 'Delete':
                            return 'bg-red-100 text-red-800';
                        case 'Recycle':
                            return 'bg-yellow-100 text-yellow-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for storage classes
                getStorageClassProvisioner(storageClass) {
                    return storageClass.provisioner || 'Unknown';
                },

                getStorageClassReclaimPolicy(storageClass) {
                    return storageClass.reclaimPolicy || 'Delete';
                },

                getStorageClassVolumeBindingMode(storageClass) {
                    return storageClass.volumeBindingMode || 'Immediate';
                },

                getStorageClassAllowVolumeExpansion(storageClass) {
                    return storageClass.allowVolumeExpansion === true ? 'Yes' : 'No';
                },

                isStorageClassDefault(storageClass) {
                    // Check for default annotation
                    const annotations = storageClass.metadata?.annotations || {};
                    const isDefault = annotations['storageclass.kubernetes.io/is-default-class'];

                    return isDefault === 'true';
                },

                hasStorageClassWarnings(storageClass) {
                    // Check for missing provisioner
                    if (!storageClass.provisioner) {
                        return true;
                    }

                    // Check for deprecated provisioners
                    const deprecatedProvisioners = [
                        'kubernetes.io/aws-ebs',
                        'kubernetes.io/azure-disk',
                        'kubernetes.io/azure-file',
                        'kubernetes.io/cinder',
                        'kubernetes.io/gce-pd',
                        'kubernetes.io/glusterfs',
                        'kubernetes.io/host-path',
                        'kubernetes.io/iscsi',
                        'kubernetes.io/nfs',
                        'kubernetes.io/rbd',
                        'kubernetes.io/vsphere-volume'
                    ];

                    if (deprecatedProvisioners.includes(storageClass.provisioner)) {
                        return true;
                    }

                    // Check for invalid reclaim policy
                    const validReclaimPolicies = ['Delete', 'Retain'];
                    if (storageClass.reclaimPolicy && !validReclaimPolicies.includes(storageClass.reclaimPolicy)) {
                        return true;
                    }

                    // Check for invalid volume binding mode
                    const validBindingModes = ['Immediate', 'WaitForFirstConsumer'];
                    if (storageClass.volumeBindingMode && !validBindingModes.includes(storageClass.volumeBindingMode)) {
                        return true;
                    }

                    return false;
                },

                getStorageClassWarnings(storageClass) {
                    const warnings = [];

                    // Check for missing provisioner
                    if (!storageClass.provisioner) {
                        warnings.push('No provisioner specified');
                    }

                    // Check for deprecated provisioners
                    const deprecatedProvisioners = [
                        'kubernetes.io/aws-ebs',
                        'kubernetes.io/azure-disk',
                        'kubernetes.io/azure-file',
                        'kubernetes.io/cinder',
                        'kubernetes.io/gce-pd',
                        'kubernetes.io/glusterfs',
                        'kubernetes.io/host-path',
                        'kubernetes.io/iscsi',
                        'kubernetes.io/nfs',
                        'kubernetes.io/rbd',
                        'kubernetes.io/vsphere-volume'
                    ];

                    if (deprecatedProvisioners.includes(storageClass.provisioner)) {
                        warnings.push(`Deprecated provisioner: ${storageClass.provisioner}`);
                    }

                    // Check for invalid reclaim policy
                    const validReclaimPolicies = ['Delete', 'Retain'];
                    if (storageClass.reclaimPolicy && !validReclaimPolicies.includes(storageClass.reclaimPolicy)) {
                        warnings.push(`Invalid reclaim policy: ${storageClass.reclaimPolicy}`);
                    }

                    // Check for invalid volume binding mode
                    const validBindingModes = ['Immediate', 'WaitForFirstConsumer'];
                    if (storageClass.volumeBindingMode && !validBindingModes.includes(storageClass.volumeBindingMode)) {
                        warnings.push(`Invalid volume binding mode: ${storageClass.volumeBindingMode}`);
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getStorageClassProvisionerBadgeClass(storageClass) {
                    const provisioner = storageClass.provisioner || '';

                    // AWS provisioners
                    if (provisioner.includes('aws') || provisioner.includes('ebs')) {
                        return 'bg-orange-100 text-orange-800';
                    }
                    // Azure provisioners
                    else if (provisioner.includes('azure')) {
                        return 'bg-blue-100 text-blue-800';
                    }
                    // GCP provisioners
                    else if (provisioner.includes('gce') || provisioner.includes('gcp')) {
                        return 'bg-green-100 text-green-800';
                    }
                    // CSI provisioners
                    else if (provisioner.includes('csi')) {
                        return 'bg-purple-100 text-purple-800';
                    }
                    // Local storage
                    else if (provisioner.includes('local') || provisioner.includes('hostpath')) {
                        return 'bg-gray-100 text-gray-800';
                    }
                    // NFS
                    else if (provisioner.includes('nfs')) {
                        return 'bg-yellow-100 text-yellow-800';
                    }
                    // Deprecated kubernetes.io provisioners
                    else if (provisioner.startsWith('kubernetes.io/')) {
                        return 'bg-red-100 text-red-800';
                    }
                    // Default
                    else {
                        return 'bg-indigo-100 text-indigo-800';
                    }
                },

                getStorageClassReclaimPolicyBadgeClass(storageClass) {
                    const policy = storageClass.reclaimPolicy;

                    switch (policy) {
                        case 'Retain':
                            return 'bg-blue-100 text-blue-800';
                        case 'Delete':
                            return 'bg-red-100 text-red-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                getStorageClassVolumeBindingModeBadgeClass(storageClass) {
                    const mode = storageClass.volumeBindingMode;

                    switch (mode) {
                        case 'Immediate':
                            return 'bg-green-100 text-green-800';
                        case 'WaitForFirstConsumer':
                            return 'bg-yellow-100 text-yellow-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for namespaces
                getNamespaceStatus(namespace) {
                    return namespace.status?.phase || 'Unknown';
                },

                getNamespaceLabels(namespace) {
                    const labels = namespace.metadata?.labels || {};

                    if (Object.keys(labels).length === 0) {
                        return 'None';
                    }

                    const labelStrings = Object.entries(labels).map(([key, value]) => `${key}=${value}`);

                    if (labelStrings.length === 1) {
                        return labelStrings[0];
                    }

                    if (labelStrings.length <= 2) {
                        return labelStrings.join(', ');
                    }

                    return `${labelStrings[0]} +${labelStrings.length - 1} more`;
                },

                getNamespaceAnnotations(namespace) {
                    const annotations = namespace.metadata?.annotations || {};

                    // Filter out system annotations
                    const userAnnotations = Object.entries(annotations).filter(([key]) => {
                        return !key.startsWith('kubectl.kubernetes.io/') &&
                               !key.startsWith('deployment.kubernetes.io/') &&
                               !key.startsWith('control-plane.alpha.kubernetes.io/') &&
                               key !== 'kubernetes.io/managed-by';
                    });

                    if (userAnnotations.length === 0) {
                        return 'None';
                    }

                    if (userAnnotations.length === 1) {
                        const [key, value] = userAnnotations[0];
                        return `${key}=${value}`;
                    }

                    if (userAnnotations.length <= 2) {
                        return userAnnotations.map(([key, value]) => `${key}=${value}`).join(', ');
                    }

                    const [key, value] = userAnnotations[0];
                    return `${key}=${value} +${userAnnotations.length - 1} more`;
                },

                hasNamespaceWarnings(namespace) {
                    const status = namespace.status?.phase;

                    // Check for terminating state
                    if (status === 'Terminating') {
                        return true;
                    }

                    // Check for failed state
                    if (status === 'Failed') {
                        return true;
                    }

                    // Check for conditions indicating problems
                    const conditions = namespace.status?.conditions || [];
                    const hasProblems = conditions.some(condition => {
                        return condition.type === 'NamespaceDeletionDiscoveryFailure' ||
                               condition.type === 'NamespaceDeletionContentFailure' ||
                               condition.type === 'NamespaceDeletionGroupVersionParsingFailure' ||
                               condition.type === 'NamespaceContentRemaining';
                    });

                    if (hasProblems) {
                        return true;
                    }

                    // Check for stuck in terminating state (more than 5 minutes)
                    if (status === 'Terminating') {
                        const now = new Date();
                        const deletionTimestamp = namespace.metadata?.deletionTimestamp;
                        if (deletionTimestamp) {
                            const deletion = new Date(deletionTimestamp);
                            const diffMinutes = (now - deletion) / (1000 * 60);
                            if (diffMinutes > 5) {
                                return true;
                            }
                        }
                    }

                    return false;
                },

                getNamespaceWarnings(namespace) {
                    const warnings = [];
                    const status = namespace.status?.phase;

                    // Check for terminating state
                    if (status === 'Terminating') {
                        const now = new Date();
                        const deletionTimestamp = namespace.metadata?.deletionTimestamp;
                        if (deletionTimestamp) {
                            const deletion = new Date(deletionTimestamp);
                            const diffMinutes = (now - deletion) / (1000 * 60);
                            if (diffMinutes > 5) {
                                warnings.push(`Stuck in terminating state for ${Math.floor(diffMinutes)} minutes`);
                            } else {
                                warnings.push('Namespace is being deleted');
                            }
                        } else {
                            warnings.push('Namespace is being deleted');
                        }
                    }

                    // Check for failed state
                    if (status === 'Failed') {
                        warnings.push('Namespace is in failed state');
                    }

                    // Check for conditions indicating problems
                    const conditions = namespace.status?.conditions || [];
                    conditions.forEach(condition => {
                        if (condition.status === 'False' || condition.status === 'Unknown') {
                            switch (condition.type) {
                                case 'NamespaceDeletionDiscoveryFailure':
                                    warnings.push('Failed to discover resources for deletion');
                                    break;
                                case 'NamespaceDeletionContentFailure':
                                    warnings.push('Failed to delete namespace content');
                                    break;
                                case 'NamespaceDeletionGroupVersionParsingFailure':
                                    warnings.push('Failed to parse API group versions');
                                    break;
                                case 'NamespaceContentRemaining':
                                    warnings.push('Some resources still remain in namespace');
                                    break;
                            }
                        }
                    });

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getNamespaceStatusBadgeClass(namespace) {
                    const status = namespace.status?.phase;

                    switch (status) {
                        case 'Active':
                            return 'bg-green-100 text-green-800';
                        case 'Terminating':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'Failed':
                            return 'bg-red-100 text-red-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                // Helper functions for events
                getEventType(event) {
                    return event.type || 'Normal';
                },

                getEventReason(event) {
                    return event.reason || 'Unknown';
                },

                getEventObject(event) {
                    if (!event.involvedObject) {
                        return 'Unknown';
                    }

                    const kind = event.involvedObject.kind || '';
                    const name = event.involvedObject.name || '';

                    return kind && name ? `${kind}/${name}` : 'Unknown';
                },

                getEventSource(event) {
                    if (!event.source) {
                        return 'Unknown';
                    }

                    const component = event.source.component || '';
                    const host = event.source.host || '';

                    if (component && host) {
                        return `${component} (${host})`;
                    } else if (component) {
                        return component;
                    } else if (host) {
                        return host;
                    }

                    return 'Unknown';
                },

                getEventMessage(event) {
                    const message = event.message || 'No message';

                    // Truncate long messages for table display
                    if (message.length > 100) {
                        return message.substring(0, 97) + '...';
                    }

                    return message;
                },

                getEventCount(event) {
                    return event.count || 1;
                },

                getEventNamespace(event) {
                    return event.metadata?.namespace || 'default';
                },

                hasEventWarnings(event) {
                    const type = event.type;
                    const reason = event.reason;

                    // Warning events
                    if (type === 'Warning') {
                        return true;
                    }

                    // Failed events
                    if (reason && (
                        reason.includes('Failed') ||
                        reason.includes('Error') ||
                        reason.includes('Unhealthy') ||
                        reason.includes('BackOff') ||
                        reason.includes('FailedMount') ||
                        reason.includes('FailedScheduling') ||
                        reason.includes('FailedCreatePodSandBox') ||
                        reason.includes('NetworkNotReady') ||
                        reason.includes('Rebooted')
                    )) {
                        return true;
                    }

                    // High event count (potential issue)
                    const count = event.count || 1;
                    if (count > 10) {
                        return true;
                    }

                    return false;
                },

                getEventWarnings(event) {
                    const warnings = [];
                    const type = event.type;
                    const reason = event.reason;
                    const count = event.count || 1;

                    // Warning events
                    if (type === 'Warning') {
                        warnings.push(`Warning event: ${reason || 'Unknown reason'}`);
                    }

                    // Failed events
                    if (reason) {
                        if (reason.includes('Failed')) {
                            warnings.push('Operation failed');
                        } else if (reason.includes('Error')) {
                            warnings.push('Error occurred');
                        } else if (reason.includes('Unhealthy')) {
                            warnings.push('Health check failed');
                        } else if (reason.includes('BackOff')) {
                            warnings.push('Container restart backoff');
                        } else if (reason.includes('FailedMount')) {
                            warnings.push('Volume mount failed');
                        } else if (reason.includes('FailedScheduling')) {
                            warnings.push('Pod scheduling failed');
                        } else if (reason.includes('FailedCreatePodSandBox')) {
                            warnings.push('Pod sandbox creation failed');
                        } else if (reason.includes('NetworkNotReady')) {
                            warnings.push('Network not ready');
                        } else if (reason.includes('Rebooted')) {
                            warnings.push('Node rebooted');
                        }
                    }

                    // High event count
                    if (count > 10) {
                        warnings.push(`High event count: ${count} occurrences`);
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                getEventTypeBadgeClass(event) {
                    const type = event.type;

                    switch (type) {
                        case 'Normal':
                            return 'bg-green-100 text-green-800';
                        case 'Warning':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'Error':
                            return 'bg-red-100 text-red-800';
                        default:
                            return 'bg-gray-100 text-gray-800';
                    }
                },

                getEventReasonBadgeClass(event) {
                    const reason = event.reason || '';

                    // Success/positive reasons
                    if (reason.includes('Created') ||
                        reason.includes('Started') ||
                        reason.includes('Pulled') ||
                        reason.includes('Scheduled') ||
                        reason.includes('SuccessfulCreate') ||
                        reason.includes('SuccessfulDelete')) {
                        return 'bg-green-100 text-green-800';
                    }

                    // Warning reasons
                    if (reason.includes('BackOff') ||
                        reason.includes('Unhealthy') ||
                        reason.includes('Killing') ||
                        reason.includes('Preempting')) {
                        return 'bg-yellow-100 text-yellow-800';
                    }

                    // Error/failure reasons
                    if (reason.includes('Failed') ||
                        reason.includes('Error') ||
                        reason.includes('FailedMount') ||
                        reason.includes('FailedScheduling') ||
                        reason.includes('FailedCreatePodSandBox')) {
                        return 'bg-red-100 text-red-800';
                    }

                    // Info reasons
                    return 'bg-blue-100 text-blue-800';
                },

                getEventCountBadgeClass(event) {
                    const count = event.count || 1;

                    if (count === 1) {
                        return 'bg-gray-100 text-gray-800';
                    } else if (count <= 5) {
                        return 'bg-blue-100 text-blue-800';
                    } else if (count <= 10) {
                        return 'bg-yellow-100 text-yellow-800';
                    } else {
                        return 'bg-red-100 text-red-800';
                    }
                },

                // Helper functions for service accounts
                getServiceAccountSecrets(serviceAccount) {
                    const secrets = serviceAccount.secrets || [];

                    if (secrets.length === 0) {
                        return 'None';
                    }

                    if (secrets.length === 1) {
                        return secrets[0].name || 'Unknown';
                    }

                    return `${secrets[0].name || 'Unknown'} +${secrets.length - 1} more`;
                },

                getServiceAccountImagePullSecrets(serviceAccount) {
                    const imagePullSecrets = serviceAccount.imagePullSecrets || [];

                    if (imagePullSecrets.length === 0) {
                        return 'None';
                    }

                    if (imagePullSecrets.length === 1) {
                        return imagePullSecrets[0].name || 'Unknown';
                    }

                    return `${imagePullSecrets[0].name || 'Unknown'} +${imagePullSecrets.length - 1} more`;
                },

                getServiceAccountMountableSecrets(serviceAccount) {
                    // Check if automountServiceAccountToken is disabled
                    if (serviceAccount.automountServiceAccountToken === false) {
                        return 'Disabled';
                    }

                    const secrets = serviceAccount.secrets || [];
                    const mountableSecrets = secrets.filter(secret => {
                        // Service account tokens are typically mountable
                        return secret.name && (
                            secret.name.includes('token') ||
                            secret.name.includes(serviceAccount.metadata?.name || '')
                        );
                    });

                    if (mountableSecrets.length === 0) {
                        return 'None';
                    }

                    return mountableSecrets.length.toString();
                },

                getServiceAccountTokens(serviceAccount) {
                    const secrets = serviceAccount.secrets || [];
                    const tokenSecrets = secrets.filter(secret => {
                        return secret.name && secret.name.includes('token');
                    });

                    if (tokenSecrets.length === 0) {
                        return 'None';
                    }

                    return tokenSecrets.length.toString();
                },

                hasServiceAccountWarnings(serviceAccount) {
                    // Check for missing secrets
                    const secrets = serviceAccount.secrets || [];
                    if (secrets.length === 0) {
                        return true;
                    }

                    // Check for disabled automount
                    if (serviceAccount.automountServiceAccountToken === false) {
                        // This might be intentional, but worth noting
                        return true;
                    }

                    // Check for missing token secrets
                    const tokenSecrets = secrets.filter(secret => {
                        return secret.name && secret.name.includes('token');
                    });

                    if (tokenSecrets.length === 0) {
                        return true;
                    }

                    // Check for default service account with additional secrets (potential security risk)
                    const name = serviceAccount.metadata?.name || '';
                    if (name === 'default' && secrets.length > 1) {
                        return true;
                    }

                    return false;
                },

                getServiceAccountWarnings(serviceAccount) {
                    const warnings = [];
                    const secrets = serviceAccount.secrets || [];
                    const name = serviceAccount.metadata?.name || '';

                    // Check for missing secrets
                    if (secrets.length === 0) {
                        warnings.push('No secrets attached');
                    }

                    // Check for disabled automount
                    if (serviceAccount.automountServiceAccountToken === false) {
                        warnings.push('Service account token automount disabled');
                    }

                    // Check for missing token secrets
                    const tokenSecrets = secrets.filter(secret => {
                        return secret.name && secret.name.includes('token');
                    });

                    if (tokenSecrets.length === 0 && serviceAccount.automountServiceAccountToken !== false) {
                        warnings.push('No token secrets found');
                    }

                    // Check for default service account with additional secrets
                    if (name === 'default' && secrets.length > 1) {
                        warnings.push('Default service account has additional secrets (potential security risk)');
                    }

                    // Check for service accounts with many image pull secrets (potential misconfiguration)
                    const imagePullSecrets = serviceAccount.imagePullSecrets || [];
                    if (imagePullSecrets.length > 5) {
                        warnings.push(`Many image pull secrets (${imagePullSecrets.length})`);
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for roles and cluster roles
                getRoleRulesCount(role) {
                    const rules = role.rules || [];
                    return rules.length.toString();
                },

                getRoleApiGroups(role) {
                    const rules = role.rules || [];
                    const apiGroups = new Set();

                    rules.forEach(rule => {
                        const groups = rule.apiGroups || [''];
                        groups.forEach(group => {
                            if (group === '') {
                                apiGroups.add('core');
                            } else {
                                apiGroups.add(group);
                            }
                        });
                    });

                    const groupArray = Array.from(apiGroups);

                    if (groupArray.length === 0) {
                        return 'None';
                    }

                    if (groupArray.length === 1) {
                        return groupArray[0];
                    }

                    if (groupArray.length <= 3) {
                        return groupArray.join(', ');
                    }

                    return `${groupArray.slice(0, 2).join(', ')} +${groupArray.length - 2} more`;
                },

                getRoleResources(role) {
                    const rules = role.rules || [];
                    const resources = new Set();

                    rules.forEach(rule => {
                        const ruleResources = rule.resources || [];
                        ruleResources.forEach(resource => {
                            resources.add(resource);
                        });
                    });

                    const resourceArray = Array.from(resources);

                    if (resourceArray.length === 0) {
                        return 'None';
                    }

                    if (resourceArray.length === 1) {
                        return resourceArray[0];
                    }

                    if (resourceArray.length <= 3) {
                        return resourceArray.join(', ');
                    }

                    return `${resourceArray.slice(0, 2).join(', ')} +${resourceArray.length - 2} more`;
                },

                getRoleVerbs(role) {
                    const rules = role.rules || [];
                    const verbs = new Set();

                    rules.forEach(rule => {
                        const ruleVerbs = rule.verbs || [];
                        ruleVerbs.forEach(verb => {
                            verbs.add(verb);
                        });
                    });

                    const verbArray = Array.from(verbs);

                    if (verbArray.length === 0) {
                        return 'None';
                    }

                    if (verbArray.length === 1) {
                        return verbArray[0];
                    }

                    if (verbArray.length <= 4) {
                        return verbArray.join(', ');
                    }

                    return `${verbArray.slice(0, 3).join(', ')} +${verbArray.length - 3} more`;
                },

                getClusterRoleAggregationRule(clusterRole) {
                    const aggregationRule = clusterRole.aggregationRule;

                    if (!aggregationRule) {
                        return 'None';
                    }

                    const clusterRoleSelectors = aggregationRule.clusterRoleSelectors || [];

                    if (clusterRoleSelectors.length === 0) {
                        return 'Empty';
                    }

                    return `${clusterRoleSelectors.length} selector(s)`;
                },

                hasRoleWarnings(role) {
                    const rules = role.rules || [];

                    // Check for empty rules
                    if (rules.length === 0) {
                        return true;
                    }

                    // Check for overly permissive rules
                    const hasWildcardVerbs = rules.some(rule => {
                        const verbs = rule.verbs || [];
                        return verbs.includes('*');
                    });

                    if (hasWildcardVerbs) {
                        return true;
                    }

                    // Check for wildcard resources
                    const hasWildcardResources = rules.some(rule => {
                        const resources = rule.resources || [];
                        return resources.includes('*');
                    });

                    if (hasWildcardResources) {
                        return true;
                    }

                    // Check for dangerous permissions
                    const hasDangerousPerms = rules.some(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];

                        // Check for escalate/bind permissions
                        if (verbs.includes('escalate') || verbs.includes('bind')) {
                            return true;
                        }

                        // Check for secrets access with get/list
                        if (resources.includes('secrets') && (verbs.includes('get') || verbs.includes('list'))) {
                            return true;
                        }

                        // Check for pod exec/attach
                        if (resources.includes('pods/exec') || resources.includes('pods/attach')) {
                            return true;
                        }

                        return false;
                    });

                    if (hasDangerousPerms) {
                        return true;
                    }

                    return false;
                },

                getRoleWarnings(role) {
                    const warnings = [];
                    const rules = role.rules || [];

                    // Check for empty rules
                    if (rules.length === 0) {
                        warnings.push('No rules defined');
                    }

                    // Check for overly permissive rules
                    const hasWildcardVerbs = rules.some(rule => {
                        const verbs = rule.verbs || [];
                        return verbs.includes('*');
                    });

                    if (hasWildcardVerbs) {
                        warnings.push('Wildcard verbs (*) - overly permissive');
                    }

                    // Check for wildcard resources
                    const hasWildcardResources = rules.some(rule => {
                        const resources = rule.resources || [];
                        return resources.includes('*');
                    });

                    if (hasWildcardResources) {
                        warnings.push('Wildcard resources (*) - overly permissive');
                    }

                    // Check for dangerous permissions
                    const escalateRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        return verbs.includes('escalate') || verbs.includes('bind');
                    });

                    if (escalateRules.length > 0) {
                        warnings.push('Privilege escalation permissions (escalate/bind)');
                    }

                    // Check for secrets access
                    const secretRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        return resources.includes('secrets') && (verbs.includes('get') || verbs.includes('list'));
                    });

                    if (secretRules.length > 0) {
                        warnings.push('Secrets read access - potential security risk');
                    }

                    // Check for pod exec/attach
                    const execRules = rules.filter(rule => {
                        const resources = rule.resources || [];
                        return resources.includes('pods/exec') || resources.includes('pods/attach');
                    });

                    if (execRules.length > 0) {
                        warnings.push('Pod exec/attach permissions - high privilege');
                    }

                    // Check for cluster-admin like permissions
                    const adminLikeRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        const apiGroups = rule.apiGroups || [];

                        return verbs.includes('*') && resources.includes('*') &&
                               (apiGroups.includes('*') || apiGroups.length === 0);
                    });

                    if (adminLikeRules.length > 0) {
                        warnings.push('Cluster-admin like permissions');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Additional helper functions for cluster roles (extends role functions)
                hasClusterRoleWarnings(clusterRole) {
                    // Use the same logic as roles but with additional cluster-specific checks
                    const hasRoleWarnings = this.hasRoleWarnings(clusterRole);

                    if (hasRoleWarnings) {
                        return true;
                    }

                    // Additional cluster-specific warnings
                    const rules = clusterRole.rules || [];

                    // Check for cluster-wide dangerous permissions
                    const hasClusterDangerousPerms = rules.some(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];

                        // Check for node access
                        if (resources.includes('nodes') && verbs.some(v => ['get', 'list', 'create', 'update', 'patch', 'delete'].includes(v))) {
                            return true;
                        }

                        // Check for cluster role/binding manipulation
                        if ((resources.includes('clusterroles') || resources.includes('clusterrolebindings')) &&
                            verbs.some(v => ['create', 'update', 'patch', 'delete'].includes(v))) {
                            return true;
                        }

                        // Check for persistent volume access
                        if (resources.includes('persistentvolumes') && verbs.some(v => ['create', 'update', 'patch', 'delete'].includes(v))) {
                            return true;
                        }

                        return false;
                    });

                    return hasClusterDangerousPerms;
                },

                getClusterRoleWarnings(clusterRole) {
                    // Get base role warnings
                    const baseWarnings = this.getRoleWarnings(clusterRole);
                    const warnings = baseWarnings !== 'No warnings' ? [baseWarnings] : [];

                    const rules = clusterRole.rules || [];

                    // Additional cluster-specific warnings
                    const nodeRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        return resources.includes('nodes') && verbs.some(v => ['get', 'list', 'create', 'update', 'patch', 'delete'].includes(v));
                    });

                    if (nodeRules.length > 0) {
                        warnings.push('Node access permissions - cluster infrastructure risk');
                    }

                    // Check for cluster role/binding manipulation
                    const clusterRoleRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        return (resources.includes('clusterroles') || resources.includes('clusterrolebindings')) &&
                               verbs.some(v => ['create', 'update', 'patch', 'delete'].includes(v));
                    });

                    if (clusterRoleRules.length > 0) {
                        warnings.push('Cluster RBAC modification permissions - privilege escalation risk');
                    }

                    // Check for persistent volume access
                    const pvRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        return resources.includes('persistentvolumes') && verbs.some(v => ['create', 'update', 'patch', 'delete'].includes(v));
                    });

                    if (pvRules.length > 0) {
                        warnings.push('Persistent volume management - storage infrastructure risk');
                    }

                    // Check for namespace management
                    const namespaceRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        return resources.includes('namespaces') && verbs.some(v => ['create', 'update', 'patch', 'delete'].includes(v));
                    });

                    if (namespaceRules.length > 0) {
                        warnings.push('Namespace management permissions');
                    }

                    // Check for custom resource definitions
                    const crdRules = rules.filter(rule => {
                        const verbs = rule.verbs || [];
                        const resources = rule.resources || [];
                        return resources.includes('customresourcedefinitions') && verbs.some(v => ['create', 'update', 'patch', 'delete'].includes(v));
                    });

                    if (crdRules.length > 0) {
                        warnings.push('Custom Resource Definition management - API extension risk');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for role bindings and cluster role bindings
                getBindingClusterRole(binding) {
                    const roleRef = binding.roleRef || {};
                    return roleRef.name || 'Unknown';
                },

                getBindingSubjectsCount(binding) {
                    const subjects = binding.subjects || [];
                    return subjects.length.toString();
                },

                getBindingSubjectTypes(binding) {
                    const subjects = binding.subjects || [];

                    if (subjects.length === 0) {
                        return 'None';
                    }

                    const types = new Set();
                    subjects.forEach(subject => {
                        types.add(subject.kind || 'Unknown');
                    });

                    const typeArray = Array.from(types);

                    if (typeArray.length === 1) {
                        return typeArray[0];
                    }

                    return typeArray.join(', ');
                },

                getBindingSubjectDetails(binding) {
                    const subjects = binding.subjects || [];

                    if (subjects.length === 0) {
                        return 'No subjects';
                    }

                    if (subjects.length === 1) {
                        const subject = subjects[0];
                        const kind = subject.kind || 'Unknown';
                        const name = subject.name || 'Unknown';
                        const namespace = subject.namespace ? ` (${subject.namespace})` : '';
                        return `${kind}: ${name}${namespace}`;
                    }

                    if (subjects.length <= 3) {
                        return subjects.map(subject => {
                            const kind = subject.kind || 'Unknown';
                            const name = subject.name || 'Unknown';
                            const namespace = subject.namespace ? ` (${subject.namespace})` : '';
                            return `${kind}: ${name}${namespace}`;
                        }).join(', ');
                    }

                    const firstTwo = subjects.slice(0, 2).map(subject => {
                        const kind = subject.kind || 'Unknown';
                        const name = subject.name || 'Unknown';
                        const namespace = subject.namespace ? ` (${subject.namespace})` : '';
                        return `${kind}: ${name}${namespace}`;
                    }).join(', ');

                    return `${firstTwo} +${subjects.length - 2} more`;
                },

                hasBindingWarnings(binding) {
                    const subjects = binding.subjects || [];
                    const roleRef = binding.roleRef || {};

                    // Check for no subjects
                    if (subjects.length === 0) {
                        return true;
                    }

                    // Check for dangerous cluster roles
                    const roleName = roleRef.name || '';
                    const dangerousRoles = [
                        'cluster-admin',
                        'admin',
                        'system:admin',
                        'system:cluster-admin'
                    ];

                    if (dangerousRoles.some(dangerous => roleName.includes(dangerous))) {
                        return true;
                    }

                    // Check for system service accounts with high privileges
                    const hasSystemServiceAccount = subjects.some(subject => {
                        return subject.kind === 'ServiceAccount' &&
                               (subject.name || '').startsWith('system:') &&
                               dangerousRoles.some(dangerous => roleName.includes(dangerous));
                    });

                    if (hasSystemServiceAccount) {
                        return true;
                    }

                    // Check for wildcard users/groups
                    const hasWildcardSubject = subjects.some(subject => {
                        const name = subject.name || '';
                        return name.includes('*') || name === 'system:authenticated' || name === 'system:unauthenticated';
                    });

                    if (hasWildcardSubject) {
                        return true;
                    }

                    return false;
                },

                getBindingWarnings(binding) {
                    const warnings = [];
                    const subjects = binding.subjects || [];
                    const roleRef = binding.roleRef || {};

                    // Check for no subjects
                    if (subjects.length === 0) {
                        warnings.push('No subjects bound - ineffective binding');
                    }

                    // Check for dangerous cluster roles
                    const roleName = roleRef.name || '';
                    const dangerousRoles = [
                        'cluster-admin',
                        'admin',
                        'system:admin',
                        'system:cluster-admin'
                    ];

                    const matchedDangerousRole = dangerousRoles.find(dangerous => roleName.includes(dangerous));
                    if (matchedDangerousRole) {
                        warnings.push(`High privilege role binding (${matchedDangerousRole}) - security risk`);
                    }

                    // Check for system service accounts with high privileges
                    const systemServiceAccounts = subjects.filter(subject => {
                        return subject.kind === 'ServiceAccount' &&
                               (subject.name || '').startsWith('system:');
                    });

                    if (systemServiceAccounts.length > 0 && matchedDangerousRole) {
                        warnings.push('System service account with high privileges');
                    }

                    // Check for wildcard users/groups
                    const wildcardSubjects = subjects.filter(subject => {
                        const name = subject.name || '';
                        return name.includes('*') || name === 'system:authenticated' || name === 'system:unauthenticated';
                    });

                    if (wildcardSubjects.length > 0) {
                        warnings.push('Wildcard or broad subject binding - potential security risk');
                    }

                    // Check for external users with high privileges
                    const externalUsers = subjects.filter(subject => {
                        return subject.kind === 'User' && !(subject.name || '').startsWith('system:');
                    });

                    if (externalUsers.length > 0 && matchedDangerousRole) {
                        warnings.push('External user with high privileges');
                    }

                    // Check for groups with high privileges
                    const groups = subjects.filter(subject => subject.kind === 'Group');
                    if (groups.length > 0 && matchedDangerousRole) {
                        warnings.push('Group with high privileges - review group membership');
                    }

                    // Check for missing role reference
                    if (!roleRef.name) {
                        warnings.push('Missing or invalid role reference');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for role bindings (namespace-scoped)
                getBindingRole(binding) {
                    const roleRef = binding.roleRef || {};
                    return roleRef.name || 'Unknown';
                },

                getBindingNamespace(binding) {
                    return binding.metadata?.namespace || 'default';
                },

                // Additional helper functions for role bindings with namespace-specific warnings
                hasRoleBindingWarnings(binding) {
                    // Use the same base logic as cluster role bindings
                    const hasBaseWarnings = this.hasBindingWarnings(binding);

                    if (hasBaseWarnings) {
                        return true;
                    }

                    // Additional namespace-specific warnings
                    const subjects = binding.subjects || [];
                    const roleRef = binding.roleRef || {};
                    const namespace = this.getBindingNamespace(binding);

                    // Check for cross-namespace bindings (subjects from different namespaces)
                    const hasCrossNamespaceSubjects = subjects.some(subject => {
                        return subject.namespace && subject.namespace !== namespace;
                    });

                    if (hasCrossNamespaceSubjects) {
                        return true;
                    }

                    // Check for admin roles in non-system namespaces
                    const roleName = roleRef.name || '';
                    const isAdminRole = ['admin', 'edit', 'view'].some(role => roleName.includes(role));
                    const isSystemNamespace = ['kube-system', 'kube-public', 'kube-node-lease', 'default'].includes(namespace);

                    if (isAdminRole && !isSystemNamespace) {
                        return true;
                    }

                    return false;
                },

                getRoleBindingWarnings(binding) {
                    // Get base binding warnings
                    const baseWarnings = this.getBindingWarnings(binding);
                    const warnings = baseWarnings !== 'No warnings' ? [baseWarnings] : [];

                    const subjects = binding.subjects || [];
                    const roleRef = binding.roleRef || {};
                    const namespace = this.getBindingNamespace(binding);

                    // Additional namespace-specific warnings
                    const crossNamespaceSubjects = subjects.filter(subject => {
                        return subject.namespace && subject.namespace !== namespace;
                    });

                    if (crossNamespaceSubjects.length > 0) {
                        warnings.push(`Cross-namespace binding - subjects from ${crossNamespaceSubjects.length} different namespace(s)`);
                    }

                    // Check for admin roles in user namespaces
                    const roleName = roleRef.name || '';
                    const isAdminRole = ['admin', 'edit'].some(role => roleName.includes(role));
                    const isSystemNamespace = ['kube-system', 'kube-public', 'kube-node-lease', 'default'].includes(namespace);

                    if (isAdminRole && !isSystemNamespace) {
                        warnings.push(`Admin role in user namespace (${namespace}) - review permissions`);
                    }

                    // Check for view role with many subjects (potential over-sharing)
                    const isViewRole = roleName.includes('view');
                    if (isViewRole && subjects.length > 5) {
                        warnings.push(`View role with many subjects (${subjects.length}) - potential over-sharing`);
                    }

                    // Check for service accounts with admin roles
                    const serviceAccountsWithAdmin = subjects.filter(subject => {
                        return subject.kind === 'ServiceAccount' && isAdminRole;
                    });

                    if (serviceAccountsWithAdmin.length > 0) {
                        warnings.push('Service account with admin role - review automation permissions');
                    }

                    // Check for default service account bindings
                    const defaultServiceAccounts = subjects.filter(subject => {
                        return subject.kind === 'ServiceAccount' && subject.name === 'default';
                    });

                    if (defaultServiceAccounts.length > 0) {
                        warnings.push('Default service account binding - security risk');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for Custom Resource Definitions (CRDs)
                getCrdResource(crd) {
                    const spec = crd.spec || {};
                    const names = spec.names || {};
                    return names.kind || 'Unknown';
                },

                getCrdGroup(crd) {
                    const spec = crd.spec || {};
                    return spec.group || 'Unknown';
                },

                getCrdVersions(crd) {
                    const spec = crd.spec || {};
                    const versions = spec.versions || [];

                    if (versions.length === 0) {
                        return 'None';
                    }

                    if (versions.length === 1) {
                        return versions[0].name || 'Unknown';
                    }

                    if (versions.length <= 3) {
                        return versions.map(v => v.name || 'Unknown').join(', ');
                    }

                    const firstTwo = versions.slice(0, 2).map(v => v.name || 'Unknown').join(', ');
                    return `${firstTwo} +${versions.length - 2} more`;
                },

                getCrdStorageVersion(crd) {
                    const spec = crd.spec || {};
                    const versions = spec.versions || [];

                    if (versions.length === 0) {
                        return 'None';
                    }

                    // Find the storage version
                    const storageVersion = versions.find(v => v.storage === true);
                    if (storageVersion) {
                        return storageVersion.name || 'Unknown';
                    }

                    // Fallback to first version if no storage version is marked
                    return versions[0].name || 'Unknown';
                },

                getCrdScope(crd) {
                    const spec = crd.spec || {};
                    return spec.scope || 'Unknown';
                },

                getCrdCategories(crd) {
                    const spec = crd.spec || {};
                    const names = spec.names || {};
                    const categories = names.categories || [];

                    if (categories.length === 0) {
                        return 'None';
                    }

                    if (categories.length === 1) {
                        return categories[0];
                    }

                    if (categories.length <= 3) {
                        return categories.join(', ');
                    }

                    const firstTwo = categories.slice(0, 2).join(', ');
                    return `${firstTwo} +${categories.length - 2} more`;
                },

                hasCrdWarnings(crd) {
                    const spec = crd.spec || {};
                    const versions = spec.versions || [];

                    // Check for no versions
                    if (versions.length === 0) {
                        return true;
                    }

                    // Check for no storage version
                    const hasStorageVersion = versions.some(v => v.storage === true);
                    if (!hasStorageVersion) {
                        return true;
                    }

                    // Check for deprecated versions
                    const hasDeprecatedVersions = versions.some(v => v.deprecated === true);
                    if (hasDeprecatedVersions) {
                        return true;
                    }

                    // Check for missing schema
                    const hasMissingSchema = versions.some(v => {
                        const schema = v.schema || {};
                        const openAPIV3Schema = schema.openAPIV3Schema || {};
                        return !openAPIV3Schema.type && !openAPIV3Schema.properties;
                    });

                    if (hasMissingSchema) {
                        return true;
                    }

                    // Check for conversion strategy issues
                    const conversion = spec.conversion || {};
                    if (conversion.strategy === 'Webhook' && !conversion.webhook) {
                        return true;
                    }

                    return false;
                },

                getCrdWarnings(crd) {
                    const warnings = [];
                    const spec = crd.spec || {};
                    const versions = spec.versions || [];

                    // Check for no versions
                    if (versions.length === 0) {
                        warnings.push('No versions defined');
                    }

                    // Check for no storage version
                    const storageVersions = versions.filter(v => v.storage === true);
                    if (storageVersions.length === 0) {
                        warnings.push('No storage version marked');
                    } else if (storageVersions.length > 1) {
                        warnings.push('Multiple storage versions marked');
                    }

                    // Check for deprecated versions
                    const deprecatedVersions = versions.filter(v => v.deprecated === true);
                    if (deprecatedVersions.length > 0) {
                        warnings.push(`${deprecatedVersions.length} deprecated version(s)`);
                    }

                    // Check for missing schema
                    const versionsWithoutSchema = versions.filter(v => {
                        const schema = v.schema || {};
                        const openAPIV3Schema = schema.openAPIV3Schema || {};
                        return !openAPIV3Schema.type && !openAPIV3Schema.properties;
                    });

                    if (versionsWithoutSchema.length > 0) {
                        warnings.push(`${versionsWithoutSchema.length} version(s) without schema`);
                    }

                    // Check for conversion strategy issues
                    const conversion = spec.conversion || {};
                    if (conversion.strategy === 'Webhook' && !conversion.webhook) {
                        warnings.push('Webhook conversion strategy without webhook configuration');
                    }

                    // Check for missing names
                    const names = spec.names || {};
                    if (!names.plural) {
                        warnings.push('Missing plural name');
                    }
                    if (!names.singular) {
                        warnings.push('Missing singular name');
                    }
                    if (!names.kind) {
                        warnings.push('Missing kind name');
                    }

                    // Check for scope issues
                    if (!spec.scope || (spec.scope !== 'Namespaced' && spec.scope !== 'Cluster')) {
                        warnings.push('Invalid or missing scope');
                    }

                    // Check for group issues
                    if (!spec.group) {
                        warnings.push('Missing API group');
                    } else if (spec.group.includes('kubernetes.io') && !spec.group.startsWith('*.')) {
                        warnings.push('Using reserved kubernetes.io group');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for ACME Challenges
                getChallengeState(challenge) {
                    const status = challenge.status || {};
                    return status.state || 'Unknown';
                },

                getChallengeType(challenge) {
                    const spec = challenge.spec || {};
                    const solver = spec.solver || {};

                    if (solver.http01) {
                        return 'HTTP-01';
                    } else if (solver.dns01) {
                        return 'DNS-01';
                    } else {
                        return 'Unknown';
                    }
                },

                getChallengeDomain(challenge) {
                    const spec = challenge.spec || {};
                    return spec.dnsName || spec.url || 'Unknown';
                },

                getChallengeIssuer(challenge) {
                    const spec = challenge.spec || {};
                    const issuerRef = spec.issuerRef || {};
                    return issuerRef.name || 'Unknown';
                },

                getChallengeReason(challenge) {
                    const status = challenge.status || {};
                    return status.reason || '-';
                },

                hasChallengeWarnings(challenge) {
                    const status = challenge.status || {};
                    const state = status.state || '';

                    // Check for failed states
                    if (state === 'invalid' || state === 'expired' || state === 'revoked') {
                        return true;
                    }

                    // Check for pending challenges that are too old
                    if (state === 'pending') {
                        const creationTime = new Date(challenge.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 1) { // Pending for more than 1 hour
                            return true;
                        }
                    }

                    // Check for processing challenges that are too old
                    if (state === 'processing') {
                        const creationTime = new Date(challenge.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffMinutes = (now - creationTime) / (1000 * 60);

                        if (diffMinutes > 30) { // Processing for more than 30 minutes
                            return true;
                        }
                    }

                    // Check for missing solver configuration
                    const spec = challenge.spec || {};
                    const solver = spec.solver || {};
                    if (!solver.http01 && !solver.dns01) {
                        return true;
                    }

                    return false;
                },

                getChallengeWarnings(challenge) {
                    const warnings = [];
                    const status = challenge.status || {};
                    const state = status.state || '';
                    const reason = status.reason || '';

                    // Check for failed states
                    if (state === 'invalid') {
                        warnings.push(`Challenge invalid: ${reason || 'Unknown reason'}`);
                    } else if (state === 'expired') {
                        warnings.push('Challenge expired');
                    } else if (state === 'revoked') {
                        warnings.push('Challenge revoked');
                    }

                    // Check for pending challenges that are too old
                    if (state === 'pending') {
                        const creationTime = new Date(challenge.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 1) {
                            warnings.push(`Challenge pending for ${Math.floor(diffHours)} hours - may be stuck`);
                        }
                    }

                    // Check for processing challenges that are too old
                    if (state === 'processing') {
                        const creationTime = new Date(challenge.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffMinutes = (now - creationTime) / (1000 * 60);

                        if (diffMinutes > 30) {
                            warnings.push(`Challenge processing for ${Math.floor(diffMinutes)} minutes - may be stuck`);
                        }
                    }

                    // Check for missing solver configuration
                    const spec = challenge.spec || {};
                    const solver = spec.solver || {};
                    if (!solver.http01 && !solver.dns01) {
                        warnings.push('No valid solver configuration (missing HTTP-01 or DNS-01)');
                    }

                    // Check for missing domain
                    if (!spec.dnsName && !spec.url) {
                        warnings.push('Missing domain name or URL');
                    }

                    // Check for missing issuer reference
                    const issuerRef = spec.issuerRef || {};
                    if (!issuerRef.name) {
                        warnings.push('Missing issuer reference');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for ACME Orders
                getOrderState(order) {
                    const status = order.status || {};
                    return status.state || 'Unknown';
                },

                getOrderDomains(order) {
                    const spec = order.spec || {};
                    const dnsNames = spec.dnsNames || [];

                    if (dnsNames.length === 0) {
                        return 'None';
                    }

                    if (dnsNames.length === 1) {
                        return dnsNames[0];
                    }

                    if (dnsNames.length <= 3) {
                        return dnsNames.join(', ');
                    }

                    return `${dnsNames.slice(0, 2).join(', ')} +${dnsNames.length - 2} more`;
                },

                getOrderIssuer(order) {
                    const spec = order.spec || {};
                    const issuerRef = spec.issuerRef || {};
                    return issuerRef.name || 'Unknown';
                },

                getOrderChallenges(order) {
                    const status = order.status || {};
                    const challenges = status.challenges || [];
                    return challenges.length.toString();
                },

                getOrderReason(order) {
                    const status = order.status || {};
                    return status.reason || '-';
                },

                hasOrderWarnings(order) {
                    const status = order.status || {};
                    const state = status.state || '';

                    // Check for failed states
                    if (state === 'invalid' || state === 'errored') {
                        return true;
                    }

                    // Check for pending orders that are too old
                    if (state === 'pending') {
                        const creationTime = new Date(order.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 2) { // Pending for more than 2 hours
                            return true;
                        }
                    }

                    // Check for processing orders that are too old
                    if (state === 'processing') {
                        const creationTime = new Date(order.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 1) { // Processing for more than 1 hour
                            return true;
                        }
                    }

                    // Check for missing domains
                    const spec = order.spec || {};
                    const dnsNames = spec.dnsNames || [];
                    if (dnsNames.length === 0) {
                        return true;
                    }

                    return false;
                },

                getOrderWarnings(order) {
                    const warnings = [];
                    const status = order.status || {};
                    const state = status.state || '';
                    const reason = status.reason || '';

                    // Check for failed states
                    if (state === 'invalid') {
                        warnings.push(`Order invalid: ${reason || 'Unknown reason'}`);
                    } else if (state === 'errored') {
                        warnings.push(`Order errored: ${reason || 'Unknown reason'}`);
                    }

                    // Check for pending orders that are too old
                    if (state === 'pending') {
                        const creationTime = new Date(order.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 2) {
                            warnings.push(`Order pending for ${Math.floor(diffHours)} hours - may be stuck`);
                        }
                    }

                    // Check for processing orders that are too old
                    if (state === 'processing') {
                        const creationTime = new Date(order.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 1) {
                            warnings.push(`Order processing for ${Math.floor(diffHours)} hours - may be stuck`);
                        }
                    }

                    // Check for missing domains
                    const spec = order.spec || {};
                    const dnsNames = spec.dnsNames || [];
                    if (dnsNames.length === 0) {
                        warnings.push('No DNS names specified');
                    }

                    // Check for missing issuer reference
                    const issuerRef = spec.issuerRef || {};
                    if (!issuerRef.name) {
                        warnings.push('Missing issuer reference');
                    }

                    // Check for expired orders
                    const notAfter = status.notAfter;
                    if (notAfter) {
                        const expiryTime = new Date(notAfter);
                        const now = new Date();

                        if (expiryTime < now) {
                            warnings.push('Order expired');
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for Certificates
                getCertificateReady(certificate) {
                    const status = certificate.status || {};
                    const conditions = status.conditions || [];

                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    return readyCondition && readyCondition.status === 'True';
                },

                getCertificateSecret(certificate) {
                    const spec = certificate.spec || {};
                    return spec.secretName || 'Unknown';
                },

                getCertificateIssuer(certificate) {
                    const spec = certificate.spec || {};
                    const issuerRef = spec.issuerRef || {};

                    if (issuerRef.kind === 'ClusterIssuer') {
                        return `${issuerRef.name} (Cluster)`;
                    } else if (issuerRef.kind === 'Issuer') {
                        return `${issuerRef.name} (Namespace)`;
                    } else {
                        return issuerRef.name || 'Unknown';
                    }
                },

                getCertificateDomains(certificate) {
                    const spec = certificate.spec || {};
                    const dnsNames = spec.dnsNames || [];
                    const commonName = spec.commonName;

                    // Combine common name and DNS names
                    let allDomains = [];
                    if (commonName) {
                        allDomains.push(commonName);
                    }

                    // Add DNS names that aren't already included
                    dnsNames.forEach(domain => {
                        if (!allDomains.includes(domain)) {
                            allDomains.push(domain);
                        }
                    });

                    if (allDomains.length === 0) {
                        return 'None';
                    }

                    if (allDomains.length === 1) {
                        return allDomains[0];
                    }

                    if (allDomains.length <= 3) {
                        return allDomains.join(', ');
                    }

                    return `${allDomains.slice(0, 2).join(', ')} +${allDomains.length - 2} more`;
                },

                getCertificateExpiry(certificate) {
                    const status = certificate.status || {};
                    const notAfter = status.notAfter;

                    if (!notAfter) {
                        return 'Unknown';
                    }

                    const expiryDate = new Date(notAfter);
                    const now = new Date();
                    const diffMs = expiryDate - now;
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                    if (diffDays < 0) {
                        return 'Expired';
                    } else if (diffDays === 0) {
                        return 'Today';
                    } else if (diffDays === 1) {
                        return '1 day';
                    } else if (diffDays < 30) {
                        return `${diffDays} days`;
                    } else if (diffDays < 365) {
                        const months = Math.floor(diffDays / 30);
                        return months === 1 ? '1 month' : `${months} months`;
                    } else {
                        const years = Math.floor(diffDays / 365);
                        return years === 1 ? '1 year' : `${years} years`;
                    }
                },

                hasCertificateWarnings(certificate) {
                    const status = certificate.status || {};
                    const conditions = status.conditions || [];

                    // Check if certificate is not ready
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (!readyCondition || readyCondition.status !== 'True') {
                        return true;
                    }

                    // Check for expiry warnings
                    const notAfter = status.notAfter;
                    if (notAfter) {
                        const expiryDate = new Date(notAfter);
                        const now = new Date();
                        const diffMs = expiryDate - now;
                        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                        // Warn if expired or expiring within 30 days
                        if (diffDays <= 30) {
                            return true;
                        }
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Ready'
                    );
                    if (failedConditions.length > 0) {
                        return true;
                    }

                    // Check for missing domains
                    const spec = certificate.spec || {};
                    const dnsNames = spec.dnsNames || [];
                    const commonName = spec.commonName;
                    if (!commonName && dnsNames.length === 0) {
                        return true;
                    }

                    // Check for missing issuer
                    const issuerRef = spec.issuerRef || {};
                    if (!issuerRef.name) {
                        return true;
                    }

                    return false;
                },

                getCertificateWarnings(certificate) {
                    const warnings = [];
                    const status = certificate.status || {};
                    const conditions = status.conditions || [];
                    const spec = certificate.spec || {};

                    // Check if certificate is not ready
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (!readyCondition) {
                        warnings.push('Certificate status unknown');
                    } else if (readyCondition.status !== 'True') {
                        const reason = readyCondition.reason || 'Unknown reason';
                        const message = readyCondition.message || '';
                        warnings.push(`Certificate not ready: ${reason}${message ? ' - ' + message : ''}`);
                    }

                    // Check for expiry warnings
                    const notAfter = status.notAfter;
                    if (notAfter) {
                        const expiryDate = new Date(notAfter);
                        const now = new Date();
                        const diffMs = expiryDate - now;
                        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                        if (diffDays < 0) {
                            warnings.push('Certificate has expired');
                        } else if (diffDays <= 7) {
                            warnings.push(`Certificate expires in ${diffDays} day(s) - renewal needed`);
                        } else if (diffDays <= 30) {
                            warnings.push(`Certificate expires in ${diffDays} days - consider renewal`);
                        }
                    } else {
                        warnings.push('Certificate expiry date unknown');
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Ready'
                    );
                    failedConditions.forEach(condition => {
                        const reason = condition.reason || 'Unknown reason';
                        const message = condition.message || '';
                        warnings.push(`${condition.type} failed: ${reason}${message ? ' - ' + message : ''}`);
                    });

                    // Check for missing domains
                    const dnsNames = spec.dnsNames || [];
                    const commonName = spec.commonName;
                    if (!commonName && dnsNames.length === 0) {
                        warnings.push('No domains specified (missing commonName and dnsNames)');
                    }

                    // Check for missing issuer
                    const issuerRef = spec.issuerRef || {};
                    if (!issuerRef.name) {
                        warnings.push('Missing issuer reference');
                    }

                    // Check for missing secret name
                    if (!spec.secretName) {
                        warnings.push('Missing secret name');
                    }

                    // Check for renewal issues
                    const renewalTime = status.renewalTime;
                    if (renewalTime) {
                        const renewalDate = new Date(renewalTime);
                        const now = new Date();

                        if (renewalDate < now) {
                            warnings.push('Certificate renewal overdue');
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for Certificate Requests
                getCertificateRequestApproved(certificateRequest) {
                    const status = certificateRequest.status || {};
                    const conditions = status.conditions || [];

                    const approvedCondition = conditions.find(condition => condition.type === 'Approved');
                    return approvedCondition && approvedCondition.status === 'True';
                },

                getCertificateRequestDenied(certificateRequest) {
                    const status = certificateRequest.status || {};
                    const conditions = status.conditions || [];

                    const deniedCondition = conditions.find(condition => condition.type === 'Denied');
                    return deniedCondition && deniedCondition.status === 'True';
                },

                getCertificateRequestReady(certificateRequest) {
                    const status = certificateRequest.status || {};
                    const conditions = status.conditions || [];

                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    return readyCondition && readyCondition.status === 'True';
                },

                getCertificateRequestIssuer(certificateRequest) {
                    const spec = certificateRequest.spec || {};
                    const issuerRef = spec.issuerRef || {};

                    if (issuerRef.kind === 'ClusterIssuer') {
                        return `${issuerRef.name} (Cluster)`;
                    } else if (issuerRef.kind === 'Issuer') {
                        return `${issuerRef.name} (Namespace)`;
                    } else {
                        return issuerRef.name || 'Unknown';
                    }
                },

                getCertificateRequestRequester(certificateRequest) {
                    const spec = certificateRequest.spec || {};
                    return spec.username || spec.uid || 'Unknown';
                },

                hasCertificateRequestWarnings(certificateRequest) {
                    const status = certificateRequest.status || {};
                    const conditions = status.conditions || [];

                    // Check if request is denied
                    const deniedCondition = conditions.find(condition => condition.type === 'Denied');
                    if (deniedCondition && deniedCondition.status === 'True') {
                        return true;
                    }

                    // Check if request is not approved and not ready
                    const approvedCondition = conditions.find(condition => condition.type === 'Approved');
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');

                    if (!approvedCondition || approvedCondition.status !== 'True') {
                        // Check if request is old (more than 1 hour without approval)
                        const creationTime = new Date(certificateRequest.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 1) {
                            return true;
                        }
                    }

                    // Check if approved but not ready for too long
                    if (approvedCondition && approvedCondition.status === 'True' &&
                        (!readyCondition || readyCondition.status !== 'True')) {
                        const creationTime = new Date(certificateRequest.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffMinutes = (now - creationTime) / (1000 * 60);

                        if (diffMinutes > 30) { // Approved but not ready for more than 30 minutes
                            return true;
                        }
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Approved' && condition.type !== 'Ready'
                    );
                    if (failedConditions.length > 0) {
                        return true;
                    }

                    // Check for missing issuer
                    const spec = certificateRequest.spec || {};
                    const issuerRef = spec.issuerRef || {};
                    if (!issuerRef.name) {
                        return true;
                    }

                    return false;
                },

                getCertificateRequestWarnings(certificateRequest) {
                    const warnings = [];
                    const status = certificateRequest.status || {};
                    const conditions = status.conditions || [];
                    const spec = certificateRequest.spec || {};

                    // Check if request is denied
                    const deniedCondition = conditions.find(condition => condition.type === 'Denied');
                    if (deniedCondition && deniedCondition.status === 'True') {
                        const reason = deniedCondition.reason || 'Unknown reason';
                        const message = deniedCondition.message || '';
                        warnings.push(`Certificate request denied: ${reason}${message ? ' - ' + message : ''}`);
                    }

                    // Check approval status
                    const approvedCondition = conditions.find(condition => condition.type === 'Approved');
                    if (!approvedCondition) {
                        const creationTime = new Date(certificateRequest.metadata?.creationTimestamp);
                        const now = new Date();
                        const diffHours = (now - creationTime) / (1000 * 60 * 60);

                        if (diffHours > 1) {
                            warnings.push(`Certificate request pending approval for ${Math.floor(diffHours)} hours`);
                        }
                    } else if (approvedCondition.status !== 'True') {
                        const reason = approvedCondition.reason || 'Unknown reason';
                        const message = approvedCondition.message || '';
                        warnings.push(`Certificate request not approved: ${reason}${message ? ' - ' + message : ''}`);
                    }

                    // Check ready status
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (approvedCondition && approvedCondition.status === 'True') {
                        if (!readyCondition) {
                            warnings.push('Certificate request approved but status unknown');
                        } else if (readyCondition.status !== 'True') {
                            const reason = readyCondition.reason || 'Unknown reason';
                            const message = readyCondition.message || '';
                            warnings.push(`Certificate request not ready: ${reason}${message ? ' - ' + message : ''}`);

                            // Check if it's taking too long
                            const creationTime = new Date(certificateRequest.metadata?.creationTimestamp);
                            const now = new Date();
                            const diffMinutes = (now - creationTime) / (1000 * 60);

                            if (diffMinutes > 30) {
                                warnings.push(`Certificate request processing for ${Math.floor(diffMinutes)} minutes - may be stuck`);
                            }
                        }
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Approved' && condition.type !== 'Ready'
                    );
                    failedConditions.forEach(condition => {
                        const reason = condition.reason || 'Unknown reason';
                        const message = condition.message || '';
                        warnings.push(`${condition.type} failed: ${reason}${message ? ' - ' + message : ''}`);
                    });

                    // Check for missing issuer
                    const issuerRef = spec.issuerRef || {};
                    if (!issuerRef.name) {
                        warnings.push('Missing issuer reference');
                    }

                    // Check for missing requester information
                    if (!spec.username && !spec.uid) {
                        warnings.push('Missing requester information');
                    }

                    // Check for missing CSR
                    if (!spec.request) {
                        warnings.push('Missing certificate signing request (CSR)');
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for Issuers
                getIssuerReady(issuer) {
                    const status = issuer.status || {};
                    const conditions = status.conditions || [];

                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    return readyCondition && readyCondition.status === 'True';
                },

                getIssuerType(issuer) {
                    const spec = issuer.spec || {};

                    if (spec.acme) {
                        return 'ACME';
                    } else if (spec.ca) {
                        return 'CA';
                    } else if (spec.vault) {
                        return 'Vault';
                    } else if (spec.selfSigned) {
                        return 'SelfSigned';
                    } else if (spec.venafi) {
                        return 'Venafi';
                    } else {
                        return 'Unknown';
                    }
                },

                getIssuerServer(issuer) {
                    const spec = issuer.spec || {};

                    if (spec.acme && spec.acme.server) {
                        // Extract domain from ACME server URL
                        try {
                            const url = new URL(spec.acme.server);
                            return url.hostname;
                        } catch (e) {
                            return spec.acme.server;
                        }
                    } else if (spec.vault && spec.vault.server) {
                        try {
                            const url = new URL(spec.vault.server);
                            return url.hostname;
                        } catch (e) {
                            return spec.vault.server;
                        }
                    } else if (spec.venafi && spec.venafi.zone) {
                        return spec.venafi.zone;
                    } else {
                        return '-';
                    }
                },

                getIssuerEmail(issuer) {
                    const spec = issuer.spec || {};

                    if (spec.acme && spec.acme.email) {
                        return spec.acme.email;
                    } else {
                        return '-';
                    }
                },

                hasIssuerWarnings(issuer) {
                    const status = issuer.status || {};
                    const conditions = status.conditions || [];

                    // Check if issuer is not ready
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (!readyCondition || readyCondition.status !== 'True') {
                        return true;
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Ready'
                    );
                    if (failedConditions.length > 0) {
                        return true;
                    }

                    // Check for missing configuration
                    const spec = issuer.spec || {};
                    if (!spec.acme && !spec.ca && !spec.vault && !spec.selfSigned && !spec.venafi) {
                        return true;
                    }

                    // Check ACME specific issues
                    if (spec.acme) {
                        if (!spec.acme.server) {
                            return true;
                        }
                        if (!spec.acme.email) {
                            return true;
                        }
                        if (!spec.acme.privateKeySecretRef || !spec.acme.privateKeySecretRef.name) {
                            return true;
                        }
                    }

                    // Check CA specific issues
                    if (spec.ca) {
                        if (!spec.ca.secretName) {
                            return true;
                        }
                    }

                    // Check Vault specific issues
                    if (spec.vault) {
                        if (!spec.vault.server) {
                            return true;
                        }
                        if (!spec.vault.path) {
                            return true;
                        }
                    }

                    return false;
                },

                getIssuerWarnings(issuer) {
                    const warnings = [];
                    const status = issuer.status || {};
                    const conditions = status.conditions || [];
                    const spec = issuer.spec || {};

                    // Check if issuer is not ready
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (!readyCondition) {
                        warnings.push('Issuer status unknown');
                    } else if (readyCondition.status !== 'True') {
                        const reason = readyCondition.reason || 'Unknown reason';
                        const message = readyCondition.message || '';
                        warnings.push(`Issuer not ready: ${reason}${message ? ' - ' + message : ''}`);
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Ready'
                    );
                    failedConditions.forEach(condition => {
                        const reason = condition.reason || 'Unknown reason';
                        const message = condition.message || '';
                        warnings.push(`${condition.type} failed: ${reason}${message ? ' - ' + message : ''}`);
                    });

                    // Check for missing configuration
                    if (!spec.acme && !spec.ca && !spec.vault && !spec.selfSigned && !spec.venafi) {
                        warnings.push('No issuer type configured (missing ACME, CA, Vault, SelfSigned, or Venafi)');
                    }

                    // Check ACME specific issues
                    if (spec.acme) {
                        if (!spec.acme.server) {
                            warnings.push('ACME server URL not specified');
                        }
                        if (!spec.acme.email) {
                            warnings.push('ACME email not specified');
                        }
                        if (!spec.acme.privateKeySecretRef || !spec.acme.privateKeySecretRef.name) {
                            warnings.push('ACME private key secret not specified');
                        }

                        // Check for deprecated ACME servers
                        if (spec.acme.server) {
                            if (spec.acme.server.includes('acme-v01.api.letsencrypt.org') ||
                                spec.acme.server.includes('acme-staging.api.letsencrypt.org')) {
                                warnings.push('Using deprecated ACME v1 server - upgrade to ACME v2');
                            }
                        }
                    }

                    // Check CA specific issues
                    if (spec.ca) {
                        if (!spec.ca.secretName) {
                            warnings.push('CA secret name not specified');
                        }
                    }

                    // Check Vault specific issues
                    if (spec.vault) {
                        if (!spec.vault.server) {
                            warnings.push('Vault server URL not specified');
                        }
                        if (!spec.vault.path) {
                            warnings.push('Vault path not specified');
                        }
                        if (!spec.vault.auth) {
                            warnings.push('Vault authentication method not specified');
                        }
                    }

                    // Check SelfSigned specific issues
                    if (spec.selfSigned) {
                        // SelfSigned issuers don't require additional configuration
                        // but warn about production usage
                        warnings.push('SelfSigned issuer - not recommended for production use');
                    }

                    // Check Venafi specific issues
                    if (spec.venafi) {
                        if (!spec.venafi.zone) {
                            warnings.push('Venafi zone not specified');
                        }
                        if (!spec.venafi.tpp && !spec.venafi.cloud) {
                            warnings.push('Venafi TPP or Cloud configuration not specified');
                        }
                    }

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                },

                // Helper functions for Cluster Issuers
                getClusterIssuerReady(clusterIssuer) {
                    const status = clusterIssuer.status || {};
                    const conditions = status.conditions || [];

                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    return readyCondition && readyCondition.status === 'True';
                },

                getClusterIssuerType(clusterIssuer) {
                    const spec = clusterIssuer.spec || {};

                    if (spec.acme) {
                        return 'ACME';
                    } else if (spec.ca) {
                        return 'CA';
                    } else if (spec.vault) {
                        return 'Vault';
                    } else if (spec.selfSigned) {
                        return 'SelfSigned';
                    } else if (spec.venafi) {
                        return 'Venafi';
                    } else {
                        return 'Unknown';
                    }
                },

                getClusterIssuerServer(clusterIssuer) {
                    const spec = clusterIssuer.spec || {};

                    if (spec.acme && spec.acme.server) {
                        // Extract domain from ACME server URL
                        try {
                            const url = new URL(spec.acme.server);
                            return url.hostname;
                        } catch (e) {
                            return spec.acme.server;
                        }
                    } else if (spec.vault && spec.vault.server) {
                        try {
                            const url = new URL(spec.vault.server);
                            return url.hostname;
                        } catch (e) {
                            return spec.vault.server;
                        }
                    } else if (spec.venafi && spec.venafi.zone) {
                        return spec.venafi.zone;
                    } else {
                        return '-';
                    }
                },

                getClusterIssuerEmail(clusterIssuer) {
                    const spec = clusterIssuer.spec || {};

                    if (spec.acme && spec.acme.email) {
                        return spec.acme.email;
                    } else {
                        return '-';
                    }
                },

                getClusterIssuerScope(clusterIssuer) {
                    // ClusterIssuers are always cluster-scoped
                    return 'Cluster';
                },

                hasClusterIssuerWarnings(clusterIssuer) {
                    const status = clusterIssuer.status || {};
                    const conditions = status.conditions || [];

                    // Check if cluster issuer is not ready
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (!readyCondition || readyCondition.status !== 'True') {
                        return true;
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Ready'
                    );
                    if (failedConditions.length > 0) {
                        return true;
                    }

                    // Check for missing configuration
                    const spec = clusterIssuer.spec || {};
                    if (!spec.acme && !spec.ca && !spec.vault && !spec.selfSigned && !spec.venafi) {
                        return true;
                    }

                    // Check ACME specific issues
                    if (spec.acme) {
                        if (!spec.acme.server) {
                            return true;
                        }
                        if (!spec.acme.email) {
                            return true;
                        }
                        if (!spec.acme.privateKeySecretRef || !spec.acme.privateKeySecretRef.name) {
                            return true;
                        }
                    }

                    // Check CA specific issues
                    if (spec.ca) {
                        if (!spec.ca.secretName) {
                            return true;
                        }
                    }

                    // Check Vault specific issues
                    if (spec.vault) {
                        if (!spec.vault.server) {
                            return true;
                        }
                        if (!spec.vault.path) {
                            return true;
                        }
                    }

                    return false;
                },

                getClusterIssuerWarnings(clusterIssuer) {
                    const warnings = [];
                    const status = clusterIssuer.status || {};
                    const conditions = status.conditions || [];
                    const spec = clusterIssuer.spec || {};

                    // Check if cluster issuer is not ready
                    const readyCondition = conditions.find(condition => condition.type === 'Ready');
                    if (!readyCondition) {
                        warnings.push('Cluster issuer status unknown');
                    } else if (readyCondition.status !== 'True') {
                        const reason = readyCondition.reason || 'Unknown reason';
                        const message = readyCondition.message || '';
                        warnings.push(`Cluster issuer not ready: ${reason}${message ? ' - ' + message : ''}`);
                    }

                    // Check for failed conditions
                    const failedConditions = conditions.filter(condition =>
                        condition.status === 'False' && condition.type !== 'Ready'
                    );
                    failedConditions.forEach(condition => {
                        const reason = condition.reason || 'Unknown reason';
                        const message = condition.message || '';
                        warnings.push(`${condition.type} failed: ${reason}${message ? ' - ' + message : ''}`);
                    });

                    // Check for missing configuration
                    if (!spec.acme && !spec.ca && !spec.vault && !spec.selfSigned && !spec.venafi) {
                        warnings.push('No issuer type configured (missing ACME, CA, Vault, SelfSigned, or Venafi)');
                    }

                    // Check ACME specific issues
                    if (spec.acme) {
                        if (!spec.acme.server) {
                            warnings.push('ACME server URL not specified');
                        }
                        if (!spec.acme.email) {
                            warnings.push('ACME email not specified');
                        }
                        if (!spec.acme.privateKeySecretRef || !spec.acme.privateKeySecretRef.name) {
                            warnings.push('ACME private key secret not specified');
                        }

                        // Check for deprecated ACME servers
                        if (spec.acme.server) {
                            if (spec.acme.server.includes('acme-v01.api.letsencrypt.org') ||
                                spec.acme.server.includes('acme-staging.api.letsencrypt.org')) {
                                warnings.push('Using deprecated ACME v1 server - upgrade to ACME v2');
                            }
                        }
                    }

                    // Check CA specific issues
                    if (spec.ca) {
                        if (!spec.ca.secretName) {
                            warnings.push('CA secret name not specified');
                        }
                    }

                    // Check Vault specific issues
                    if (spec.vault) {
                        if (!spec.vault.server) {
                            warnings.push('Vault server URL not specified');
                        }
                        if (!spec.vault.path) {
                            warnings.push('Vault path not specified');
                        }
                        if (!spec.vault.auth) {
                            warnings.push('Vault authentication method not specified');
                        }
                    }

                    // Check SelfSigned specific issues
                    if (spec.selfSigned) {
                        // SelfSigned issuers don't require additional configuration
                        // but warn about production usage
                        warnings.push('SelfSigned cluster issuer - not recommended for production use');
                    }

                    // Check Venafi specific issues
                    if (spec.venafi) {
                        if (!spec.venafi.zone) {
                            warnings.push('Venafi zone not specified');
                        }
                        if (!spec.venafi.tpp && !spec.venafi.cloud) {
                            warnings.push('Venafi TPP or Cloud configuration not specified');
                        }
                    }

                    // Cluster-specific warnings
                    warnings.push('Cluster-wide issuer - affects all namespaces');

                    return warnings.length > 0 ? warnings.join('; ') : 'No warnings';
                }
            }
        }
    </script>
</div>
