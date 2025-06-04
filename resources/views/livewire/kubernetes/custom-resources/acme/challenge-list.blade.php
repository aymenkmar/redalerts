<div>
    <x-kubernetes-table
        title="ACME Challenges"
        :all-data="$challenges"
        :columns="$this->getTableColumns()"
        :loading="$loading"
        :error="$error"
        :namespaces="$namespaces"
        :show-namespace-filter="true"
        :show-refresh="true"
        refresh-method="refreshData"
    >

        <template x-for="challenge in paginatedData" :key="challenge.metadata.uid">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="challenge.metadata?.name || 'Unknown'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="challenge.metadata?.namespace || 'default'"></td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div x-show="hasChallengeWarnings(challenge)" class="flex justify-center" :title="getChallengeWarnings(challenge)">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getChallengeState(challenge)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getChallengeType(challenge)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getChallengeDomain(challenge)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getChallengeIssuer(challenge)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getChallengeReason(challenge)"></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatAge(challenge.metadata?.creationTimestamp)"></td>
            </tr>
        </template>

    </x-kubernetes-table>
</div>
