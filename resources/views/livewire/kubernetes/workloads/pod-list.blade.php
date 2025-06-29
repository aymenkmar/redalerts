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
                        <!-- Shell Button -->
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
                        <!-- Logs Button -->
                        <button
                            @click="openPodLogs(pod.metadata?.namespace || 'default', pod.metadata?.name || '')"
                            class="p-1 rounded hover:bg-gray-100 text-gray-600 hover:text-gray-800"
                            title="View Logs"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        </template>
    </x-kubernetes-table>

    <!-- Set selected cluster for this component -->
    <script>
        // Override the global cluster with the component's cluster
        if (@json($selectedCluster)) {
            window.selectedCluster = @json($selectedCluster);
            console.log('PodList - Updated window.selectedCluster to:', window.selectedCluster);
        }
    </script>
</div>
