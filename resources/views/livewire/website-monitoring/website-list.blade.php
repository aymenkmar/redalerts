<div class="min-h-screen bg-gray-50" wire:poll.30s="refreshData">
    <!-- Sticky Header/Navbar -->
    <header class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('main-dashboard') }}" wire:navigate class="text-red-600 hover:text-red-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Website Monitoring</h1>
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
    <div class="max-w-6xl mx-auto px-6 py-6">
        <!-- Add Button and Search Bar -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <!-- Add Website Button (LEFT of search) -->
            <a href="{{ route('website-monitoring.add') }}" wire:navigate
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center whitespace-nowrap border border-green-700 shadow-md"
               style="background-color: #059669 !important; color: white !important; border: 1px solid #047857 !important;">
                <svg class="h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: white !important;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span class="hidden sm:inline text-white font-semibold" style="color: white !important;">Add Website</span>
                <span class="sm:hidden text-white font-semibold" style="color: white !important;">Add</span>
            </a>

            <div class="relative flex-1">
                <input type="text" wire:model.live="search" placeholder="Search monitors..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Auto-refresh Status -->
        <div class="mb-4 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-blue-800">Auto-refresh enabled</span>
                </div>
                <span class="text-sm text-blue-600">Updates every 30 seconds</span>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-sm text-blue-600">
                    Last updated: {{ $lastRefresh }}
                </div>
                <button wire:click="refreshData"
                        class="inline-flex items-center px-3 py-1 border border-blue-300 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh Now
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Websites List -->
        <div class="space-y-4">
            @if($websites->count() > 0)
                @foreach($websites as $website)
                    <!-- Website Card (UptimeRobot Style) -->
                    <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <!-- Website Header -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                                <div class="flex items-center space-x-3 min-w-0 flex-1">
                                    <!-- Status Indicator -->
                                    @php
                                        $statusColor = $website->status_color;
                                        $statusText = $website->status_text;
                                    @endphp
                                    <div class="w-3 h-3 rounded-full bg-{{ $statusColor }}-500 flex-shrink-0"></div>

                                    <!-- Website Info -->
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $website->name }}</h3>
                                        <p class="text-sm text-gray-500 truncate">{{ $website->description }}</p>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                        {{ $statusColor === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $statusColor === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $statusColor === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <!-- Right Side Info -->
                                <div class="flex items-center justify-between lg:justify-end space-x-4 lg:space-x-6 text-sm text-gray-500">
                                    <!-- Uptime -->
                                    <div class="text-center lg:text-right">
                                        <div class="text-lg font-semibold text-gray-900">{{ $website->uptime_percentage }}%</div>
                                        <div class="text-xs">Last 30 days</div>
                                    </div>

                                    <!-- Last Check -->
                                    <div class="text-center lg:text-right hidden sm:block">
                                        @if($website->last_checked_at)
                                            <div class="font-medium">{{ $website->last_checked_at->diffForHumans() }}</div>
                                            <div class="text-xs">{{ $website->last_checked_at->format('M j, Y H:i') }}</div>
                                        @else
                                            <div class="text-gray-400">Never checked</div>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-1 lg:space-x-2">
                                        <!-- Chart Icon -->
                                        <a href="{{ route('website-monitoring.history', $website) }}" wire:navigate
                                           class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="View History">
                                            <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </a>

                                        <!-- Edit Icon -->
                                        <a href="{{ route('website-monitoring.edit', $website) }}" wire:navigate
                                           class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Edit">
                                            <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Refresh Icon -->
                                        <button wire:click="checkWebsite({{ $website->id }})"
                                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Check Now">
                                            <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>

                                        <!-- Delete Icon -->
                                        <button wire:click="deleteWebsite({{ $website->id }})"
                                                wire:confirm="Are you sure you want to delete this website?"
                                                class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100" title="Delete">
                                            <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- URLs List -->
                        <div class="px-4 sm:px-6 py-4">
                            <div class="space-y-3">
                                @foreach($website->urls as $url)
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-3 px-4 bg-gray-50 rounded-lg space-y-2 sm:space-y-0">
                                        <div class="flex items-center space-x-3 min-w-0 flex-1">
                                            <!-- URL Status Indicator -->
                                            @php
                                                $urlStatusColor = $url->status_color;
                                            @endphp
                                            <div class="w-2 h-2 rounded-full bg-{{ $urlStatusColor }}-500 flex-shrink-0"></div>

                                            <!-- URL and Monitoring Types -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ $url->url }}" target="_blank" rel="noopener noreferrer"
                                                       class="text-sm font-medium text-gray-900 hover:text-blue-600 truncate transition-colors duration-200"
                                                       title="Open {{ $url->url }} in new tab">
                                                        {{ $url->url }}
                                                    </a>
                                                    <a href="{{ $url->url }}" target="_blank" rel="noopener noreferrer"
                                                       class="text-gray-400 hover:text-blue-600 transition-colors duration-200"
                                                       title="Open {{ $url->url }} in new tab">
                                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                </div>

                                                <!-- Monitoring Types -->
                                                <div class="flex items-center space-x-2 mt-1 flex-wrap">
                                                    @if($url->monitor_status)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            HTTP
                                                        </span>
                                                    @endif
                                                    @if($url->monitor_domain)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                            DNS
                                                        </span>
                                                    @endif
                                                    @if($url->monitor_ssl)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            SSL
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- URL Status and Time -->
                                        <div class="text-left sm:text-right flex-shrink-0">
                                            <div class="text-sm font-medium text-gray-900">{{ $url->uptime_percentage }}% uptime</div>
                                            @if($url->last_checked_at)
                                                <div class="text-xs text-gray-500">{{ $url->last_checked_at->diffForHumans() }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Email Configuration -->
                                @if($website->notification_emails && count($website->notification_emails) > 0)
                                    <div class="flex items-center space-x-2 text-sm text-gray-500 mt-2">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ count($website->notification_emails) }} email(s) configured</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9 3-9m-9 9a9 9 0 919-9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No monitors</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a website to monitor.</p>
                    <div class="mt-6">
                        <a href="{{ route('website-monitoring.add') }}" wire:navigate
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add New Monitor
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
