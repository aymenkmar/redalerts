<div class="min-h-screen bg-gray-50">
    <!-- Sticky Header/Navbar -->
    <header class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('website-monitoring.list') }}" wire:navigate class="text-red-600 hover:text-red-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $website->name }} - History</h1>
                        <p class="text-sm text-gray-600">Monitoring history and downtime tracking</p>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Dropdown -->
                    @livewire('notification-dropdown')

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <div class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-semibold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Checks -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Checks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Uptime Percentage -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Uptime</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['uptime_percentage'] }}%</p>
                    </div>
                </div>
            </div>

            <!-- Online Checks -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Online</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['up']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Offline Checks -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Offline</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['down']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Warning Checks -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Warnings</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['warning']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex">
                    <button wire:click="$set('activeTab', 'logs')"
                            class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'logs' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Monitoring Logs
                    </button>
                    <button wire:click="$set('activeTab', 'incidents')"
                            class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'incidents' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Downtime Incidents
                    </button>
                </nav>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- URL Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                    <select wire:model.live="selectedUrlId" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="all">All URLs</option>
                        @foreach($website->urls as $url)
                            <option value="{{ $url->id }}">{{ Str::limit($url->url, 30) }}</option>
                        @endforeach
                    </select>
                </div>

                @if($activeTab === 'logs')
                    <!-- Check Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check Type</label>
                        <select wire:model.live="checkTypeFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="all">All Types</option>
                            <option value="status">Status</option>
                            <option value="domain">Domain</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select wire:model.live="statusFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="all">All Status</option>
                            <option value="up">Up</option>
                            <option value="down">Down</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                @endif

                <!-- Quick Date Filters -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Filter</label>
                    <div class="flex space-x-2">
                        <button wire:click="setDateRange(1)" class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded">1D</button>
                        <button wire:click="setDateRange(7)" class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded">7D</button>
                        <button wire:click="setDateRange(30)" class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded">30D</button>
                        <button wire:click="setDateRange(90)" class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded">90D</button>
                    </div>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" wire:model.live="startDate"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" wire:model.live="endDate"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($data->count() > 0)
                <div class="overflow-x-auto">
                    @if($activeTab === 'logs')
                        <!-- Monitoring Logs Table -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data as $log)
                                    <tr class="hover:bg-gray-50">
                                        <!-- Timestamp -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>{{ $log->checked_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $log->checked_at->format('H:i:s') }}</div>
                                        </td>

                                        <!-- URL -->
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="max-w-xs truncate">
                                                <a href="{{ $log->websiteUrl->url }}" target="_blank" rel="noopener noreferrer"
                                                   class="text-gray-900 hover:text-blue-600 transition-colors duration-200"
                                                   title="Open {{ $log->websiteUrl->url }} in new tab">
                                                    {{ $log->websiteUrl->url }}
                                                </a>
                                            </div>
                                        </td>

                                        <!-- Check Type -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $typeColor = match($log->check_type) {
                                                    'status' => 'blue',
                                                    'domain' => 'purple',
                                                    'ssl' => 'yellow',
                                                    default => 'gray'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-800">
                                                {{ $log->check_type_display }}
                                            </span>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $statusColor = $log->status_color;
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                                <div class="w-2 h-2 bg-{{ $statusColor }}-400 rounded-full mr-1"></div>
                                                {{ $log->status_text }}
                                            </span>
                                        </td>

                                        <!-- Response Time -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($log->response_time)
                                                <div class="flex items-center">
                                                    <span>{{ $log->formatted_response_time }}</span>
                                                    @if($log->response_time < 500)
                                                        <div class="w-2 h-2 bg-green-400 rounded-full ml-2"></div>
                                                    @elseif($log->response_time < 2000)
                                                        <div class="w-2 h-2 bg-yellow-400 rounded-full ml-2"></div>
                                                    @else
                                                        <div class="w-2 h-2 bg-red-400 rounded-full ml-2"></div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>

                                        <!-- Details -->
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="space-y-1">
                                                @if($log->status_code)
                                                    <div class="text-xs">
                                                        <span class="font-medium">Status:</span> {{ $log->status_code }}
                                                    </div>
                                                @endif

                                                @if($log->error_message)
                                                    <div class="text-xs text-red-600 max-w-xs truncate" title="{{ $log->error_message }}">
                                                        <span class="font-medium">Error:</span> {{ $log->error_message }}
                                                    </div>
                                                @endif

                                                @if($log->additional_data)
                                                    @if($log->check_type === 'ssl' && isset($log->additional_data['days_until_expiry']))
                                                        <div class="text-xs">
                                                            <span class="font-medium">SSL Expires:</span>
                                                            {{ (int) $log->additional_data['days_until_expiry'] }} days
                                                        </div>
                                                    @endif

                                                    @if($log->check_type === 'domain' && isset($log->additional_data['dns_records']))
                                                        <div class="text-xs">
                                                            <span class="font-medium">DNS Records:</span>
                                                            {{ count($log->additional_data['dns_records']) }} found
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <!-- Downtime Incidents Table -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cause</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data as $incident)
                                    <tr class="hover:bg-gray-50">
                                        <!-- Started -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>{{ $incident->started_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $incident->started_at->format('H:i:s') }}</div>
                                        </td>

                                        <!-- URL -->
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="max-w-xs truncate">
                                                <a href="{{ $incident->websiteUrl->url }}" target="_blank" rel="noopener noreferrer"
                                                   class="text-gray-900 hover:text-blue-600 transition-colors duration-200"
                                                   title="Open {{ $incident->websiteUrl->url }} in new tab">
                                                    {{ $incident->websiteUrl->url }}
                                                </a>
                                            </div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($incident->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <div class="w-2 h-2 bg-red-400 rounded-full mr-1"></div>
                                                    Ongoing
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                                                    Resolved
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Duration -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>{{ $incident->formatted_duration }}</div>
                                            @if($incident->ended_at)
                                                <div class="text-xs text-gray-500">Ended: {{ $incident->ended_at->format('H:i:s') }}</div>
                                            @endif
                                        </td>

                                        <!-- Cause -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="capitalize">{{ $incident->cause ?? 'Unknown' }}</span>
                                        </td>

                                        <!-- Error -->
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($incident->error_message)
                                                <div class="max-w-xs truncate" title="{{ $incident->error_message }}">
                                                    {{ $incident->error_message }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $data->links('custom.livewire-pagination') }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No data found</h3>
                    <p class="mt-1 text-sm text-gray-500">No {{ $activeTab === 'logs' ? 'monitoring logs' : 'downtime incidents' }} found for the selected filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>