<div>
    <h1 class="text-2xl font-bold mb-6">Jobs</h1>

    @if($error)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mb-6">
        @include('livewire.kubernetes.components.namespace-filter')
        @include('livewire.kubernetes.components.search-input', ['placeholder' => 'Search jobs...'])
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($loading)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Namespace</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($jobs as $job)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $job['metadata']['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $job['metadata']['namespace'] ?? 'default' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if(isset($job['status']['completionTime']))
                                {{ \Carbon\Carbon::parse($job['status']['completionTime'])->format('Y-m-d H:i:s') }}
                            @else
                                Not completed
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $this->formatAge($job['metadata']['creationTimestamp']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $condition = 'Pending';
                                $statusColor = 'bg-yellow-100 text-yellow-800';

                                if (isset($job['status']['conditions'])) {
                                    foreach ($job['status']['conditions'] as $cond) {
                                        if ($cond['type'] === 'Complete' && $cond['status'] === 'True') {
                                            $condition = 'Completed';
                                            $statusColor = 'bg-green-100 text-green-800';
                                            break;
                                        } elseif ($cond['type'] === 'Failed' && $cond['status'] === 'True') {
                                            $condition = 'Failed';
                                            $statusColor = 'bg-red-100 text-red-800';
                                            break;
                                        }
                                    }
                                }

                                if ($condition === 'Pending' && isset($job['status']['active']) && $job['status']['active'] > 0) {
                                    $condition = 'Running';
                                    $statusColor = 'bg-blue-100 text-blue-800';
                                }
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                {{ $condition }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No jobs found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        @include('livewire.kubernetes.components.pagination')
    </div>
</div>
