<div>
    <x-kubernetes-table
        title="Events"
        :all-data="$events"
        :columns="$this->getTableColumns()"
        :loading="$loading"
        :error="$error"
        :namespaces="$namespaces"
        :show-namespace-filter="true"
        :show-refresh="true"
        refresh-method="refreshData"
    >

        <template x-for="event in paginatedData" :key="event.metadata.uid">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getEventNamespace(event)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div x-show="hasEventWarnings(event)" class="flex justify-center" :title="getEventWarnings(event)">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getEventTypeBadgeClass(event)"
                        x-text="getEventType(event)">
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getEventReasonBadgeClass(event)"
                        x-text="getEventReason(event)">
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getEventObject(event)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getEventSource(event)"></td>
                <td class="px-6 py-4 text-sm text-gray-900" x-text="getEventMessage(event)" :title="event.message"></td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getEventCountBadgeClass(event)"
                        x-text="getEventCount(event)">
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatAge(event.metadata?.creationTimestamp)"></td>
            </tr>
        </template>

    </x-kubernetes-table>
</div>
