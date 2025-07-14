<div class="relative h-full" x-data="nodeListPanel()">
    <!-- Main content area -->
    <div class="w-full h-full">
        <x-kubernetes-table
            title="Nodes"
            :all-data="$nodes"
            :columns="$this->getTableColumns()"
            :loading="$loading"
            :error="$error"
            :namespaces="[]"
            :show-namespace-filter="false"
            :show-refresh="true"
            refresh-method="refreshData"
        >

            <template x-for="node in paginatedData" :key="node.metadata.name">
                <tr class="hover:bg-gray-50 cursor-pointer {{ $selectedNode ? 'transition-colors' : '' }}"
                    :class="'{{ $selectedNode }}' === node.metadata?.name ? 'bg-blue-50 border-l-4 border-blue-500' : ''"
                    @click="$wire.selectNode(node.metadata?.name); showDetails = true;">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="node.metadata?.name || 'Unknown'"></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                              :class="getNodeStatusClass(node)"
                              x-text="getNodeStatus(node)">
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div x-show="hasNodeWarnings(node)" class="flex justify-center" :title="getNodeWarnings(node)">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getNodeRoles(node)"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatAge(node.metadata?.creationTimestamp)"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="node.status?.nodeInfo?.kubeletVersion || 'Unknown'"></td>
                </tr>
            </template>

        </x-kubernetes-table>
    </div>

    <!-- Node Details Panel Overlay -->
    @if($showNodeDetails)
        <!-- Panel -->
        <div x-show="showDetails"
             x-transition:enter="transition-transform ease-out duration-300"
             x-transition:enter-start="transform translate-x-full"
             x-transition:enter-end="transform translate-x-0"
             x-transition:leave="transition-transform ease-in duration-200"
             x-transition:leave-start="transform translate-x-0"
             x-transition:leave-end="transform translate-x-full"
             class="fixed right-0 z-60 shadow-2xl bg-white border-l-2 border-gray-300"
             :style="`width: ${panelWidth}px; top: 64px; height: calc(100vh - 64px);`"
             @click.stop>

            <!-- Resize Handle -->
            <div class="absolute left-0 top-0 w-2 cursor-col-resize group flex items-center justify-center"
                 style="height: calc(100vh - 64px);"
                 @mousedown="startResize($event)"
                 @dblclick="resetPanelWidth()"
                 title="Drag to resize panel • Double-click to reset to default width">
                <!-- Invisible wider hit area -->
                <div class="absolute -left-1 -right-1 top-0 bottom-0"></div>
                <!-- Visual handle -->
                <div class="w-1 h-full bg-gray-400 group-hover:bg-blue-500 transition-colors duration-200 relative">
                    <!-- Grip dots -->
                    <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 flex flex-col space-y-1">
                        <div class="w-1 h-1 bg-gray-600 group-hover:bg-blue-700 rounded-full transition-colors duration-200"></div>
                        <div class="w-1 h-1 bg-gray-600 group-hover:bg-blue-700 rounded-full transition-colors duration-200"></div>
                        <div class="w-1 h-1 bg-gray-600 group-hover:bg-blue-700 rounded-full transition-colors duration-200"></div>
                    </div>
                </div>
            </div>

            <!-- Panel Content -->
            <div class="h-full ml-2">
                <livewire:kubernetes.node-details :nodeName="$selectedNode" :key="'node-details-' . $selectedNode" />
            </div>
        </div>
    @endif
</div>

<script>
function nodeListPanel() {
    return {
        showDetails: @json($showNodeDetails),
        panelWidth: @json($panelWidth),
        isResizing: false,
        startX: 0,
        startWidth: 0,

        init() {
            // Load saved panel width from localStorage
            const savedWidth = localStorage.getItem('nodeDetailsPanelWidth');
            if (savedWidth) {
                this.panelWidth = parseInt(savedWidth);
                this.$wire.updatePanelWidth(this.panelWidth);
            }

            // Listen for Livewire updates
            this.$wire.on('nodeSelected', () => {
                this.showDetails = true;
            });

            this.$wire.on('nodeDetailsClose', () => {
                this.showDetails = false;
            });

            // Close panel on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.showDetails) {
                    this.closePanel();
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

        startResize(event) {
            event.preventDefault();
            event.stopPropagation();

            this.isResizing = true;
            this.startX = event.clientX;
            this.startWidth = this.panelWidth;

            // Add visual feedback
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            document.body.classList.add('resizing-panel');

            // Add temporary styles
            const style = document.createElement('style');
            style.id = 'resize-panel-styles';
            style.textContent = `
                .resizing-panel * {
                    cursor: col-resize !important;
                    user-select: none !important;
                }
            `;
            document.head.appendChild(style);
        },

        handleResize(event) {
            if (!this.isResizing) return;

            event.preventDefault();

            // Calculate new width (resize from left edge, so we subtract the delta)
            const deltaX = this.startX - event.clientX;
            const newWidth = Math.max(300, Math.min(800, this.startWidth + deltaX));

            this.panelWidth = newWidth;

            // Update Livewire component
            this.$wire.updatePanelWidth(newWidth);
        },

        stopResize() {
            if (!this.isResizing) return;

            this.isResizing = false;

            // Save panel width to localStorage
            localStorage.setItem('nodeDetailsPanelWidth', this.panelWidth);

            // Remove visual feedback
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            document.body.classList.remove('resizing-panel');

            // Remove temporary styles
            const style = document.getElementById('resize-panel-styles');
            if (style) {
                style.remove();
            }
        },

        resetPanelWidth() {
            this.panelWidth = 384; // Reset to default width (24rem)
            this.$wire.updatePanelWidth(this.panelWidth);
            localStorage.setItem('nodeDetailsPanelWidth', this.panelWidth);
        },

        closePanel() {
            this.showDetails = false;
            this.$wire.closeNodeDetails();
        }
    }
}
</script>
