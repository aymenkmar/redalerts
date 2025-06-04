<div>
    <x-kubernetes-table
        title="Cluster Issuers"
        :all-data="$clusterIssuers"
        :columns="$this->getTableColumns()"
        :loading="$loading"
        :error="$error"
        :namespaces="[]"
        :show-namespace-filter="false"
        :show-refresh="true"
        refresh-method="refreshData"
    >

        <template x-for="clusterIssuer in paginatedData" :key="clusterIssuer.metadata.uid">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="clusterIssuer.metadata?.name || 'Unknown'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div x-show="hasClusterIssuerWarnings(clusterIssuer)" class="flex justify-center" :title="getClusterIssuerWarnings(clusterIssuer)">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span :class="getClusterIssuerReady(clusterIssuer) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          x-text="getClusterIssuerReady(clusterIssuer) ? 'Yes' : 'No'">
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getClusterIssuerType(clusterIssuer)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getClusterIssuerServer(clusterIssuer)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getClusterIssuerEmail(clusterIssuer)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                          x-text="getClusterIssuerScope(clusterIssuer)">
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatAge(clusterIssuer.metadata?.creationTimestamp)"></td>
            </tr>
        </template>

    </x-kubernetes-table>
</div>
