<div>
    <x-kubernetes-table
        title="Pods"
        :all-data="$pods"
        :columns="$this->getTableColumns()"
        :loading="$loading"
        :error="$error"
        :namespaces="$namespaces"
        :show-namespace-filter="true"
        :show-refresh="true"
        refresh-method="refreshData"
    >

        <template x-for="pod in paginatedData" :key="pod.metadata.name + pod.metadata.namespace">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="pod.metadata?.name || 'Unknown'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="pod.metadata?.namespace || 'default'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div x-show="hasPodWarnings(pod)" class="flex justify-center" :title="getPodWarnings(pod)">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getPodReadyContainers(pod)"></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="getPodStatusClass(pod)"
                          x-text="getPodStatus(pod)">
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getPodRestarts(pod)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="pod.spec?.nodeName || 'N/A'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="pod.status?.qosClass || 'N/A'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatAge(pod.metadata?.creationTimestamp)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex items-center space-x-2">
                        <button
                            x-show="isPodRunning(pod)"
                            @click="openPodShell(pod.metadata?.namespace || 'default', pod.metadata?.name || '')"
                            class="p-1 rounded hover:bg-gray-100 text-gray-600 hover:text-gray-800"
                            title="Open Shell"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <button
                            x-show="!isPodRunning(pod)"
                            disabled
                            class="p-1 rounded opacity-50 cursor-not-allowed text-gray-400"
                            title="Pod not running"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        </template>

    </x-kubernetes-table>

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
        function openPodShell(namespace, podName) {
            try {
                // Get the first container if multiple containers exist
                const container = null; // Will use default container

                // Update terminal title
                const terminalTitle = document.getElementById('terminal-title');
                if (terminalTitle) {
                    terminalTitle.textContent = `${namespace}/${podName}${container ? `/${container}` : ''}`;
                }

                // Connect to pod shell
                if (window.podTerminal) {
                    window.podTerminal.connect(namespace, podName, container).then(success => {
                        if (!success) {
                            alert('Failed to connect to pod shell');
                        }
                    }).catch(error => {
                        console.error('Error opening pod shell:', error);
                        alert('Error opening pod shell: ' + error.message);
                    });
                } else {
                    alert('Pod terminal not available');
                }
            } catch (error) {
                console.error('Error opening pod shell:', error);
                alert('Error opening pod shell: ' + error.message);
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
