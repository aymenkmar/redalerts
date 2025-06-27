<div class="min-h-screen bg-gray-50" wire:poll.60s="refreshData">
    <!-- Sticky Header/Navbar -->
    <header class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('ovh-monitoring.overview') }}" wire:navigate class="text-red-600 hover:text-red-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">OVH VPS Monitoring</h1>
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
        <!-- Sync Button and Search Bar -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <!-- Sync Services Button -->
            <button wire:click="syncServices"
                    wire:loading.attr="disabled"
                    class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 disabled:cursor-not-allowed text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center whitespace-nowrap border border-red-700 shadow-md">
                <svg wire:loading.remove wire:target="syncServices" class="h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="syncServices" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="syncServices" class="hidden sm:inline text-white font-semibold">Sync Services</span>
                <span wire:loading.remove wire:target="syncServices" class="sm:hidden text-white font-semibold">Sync</span>
                <span wire:loading wire:target="syncServices" class="text-white font-semibold">Syncing...</span>
            </button>

            <div class="relative flex-1">
                <input type="text" wire:model.live="search" placeholder="Search VPS services..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-4">
            <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all">All States</option>
                <option value="running">Running</option>
                <option value="stopped">Stopped</option>
                <option value="rebooting">Rebooting</option>
            </select>

            <select wire:model.live="expirationFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all">All Services</option>
                <option value="expiring_soon">Expiring Soon (30 days)</option>
                <option value="expired">Expired</option>
            </select>
        </div>

        <!-- Auto-refresh Status -->
        <div class="mb-4 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-blue-800">Auto-refresh enabled</span>
                </div>
                <span class="text-sm text-blue-600">Updates every 60 seconds</span>
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

        <!-- VPS Services List -->
        <div class="space-y-4">
            @if($vpsServices->count() > 0)
                @foreach($vpsServices as $vps)
                    <!-- VPS Service Card -->
                    <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <!-- VPS Header -->
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                                <div class="flex items-center space-x-3 min-w-0 flex-1">
                                    <!-- Status Indicator -->
                                    @php
                                        $statusColor = match($vps->state) {
                                            'running' => 'green',
                                            'stopped' => 'red',
                                            'rebooting' => 'yellow',
                                            default => 'gray'
                                        };
                                    @endphp
                                    <div class="w-3 h-3 rounded-full bg-{{ $statusColor }}-500 flex-shrink-0"></div>

                                    <!-- VPS Info -->
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $vps->display_name }}</h3>
                                        <p class="text-sm text-gray-500 truncate">{{ $vps->service_name }}</p>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                        {{ $statusColor === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $statusColor === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $statusColor === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($vps->state) }}
                                    </span>
                                </div>

                                <!-- Right Side Info -->
                                <div class="flex items-center justify-between lg:justify-end space-x-6 lg:space-x-8 text-sm text-gray-500">
                                    <!-- Expiration Date -->
                                    <div class="text-center lg:text-right">
                                        @if($vps->expiration_date)
                                            @php
                                                $daysUntilExpiration = $vps->getDaysUntilExpiration();
                                                $isExpiringSoon = $vps->isExpiringSoon();
                                                $isExpired = $vps->isExpired();
                                            @endphp
                                            <div class="text-lg font-semibold
                                                {{ $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-yellow-600' : 'text-gray-900') }}">
                                                {{ $vps->expiration_date->format('M j, Y') }}
                                            </div>
                                            <div class="text-xs mt-1">Next Billing</div>
                                        @else
                                            <div class="text-gray-400">No expiration</div>
                                        @endif
                                    </div>

                                    <!-- Engagement Date -->
                                    <div class="text-center lg:text-right hidden sm:block">
                                        @if($vps->engagement_date)
                                            <div class="font-medium">{{ $vps->engagement_date->format('M j, Y') }}</div>
                                            <div class="text-xs mt-1">Engagement</div>
                                        @else
                                            <div class="text-gray-400">No engagement</div>
                                        @endif
                                    </div>

                                    <!-- Renewal Type -->
                                    <div class="text-center lg:text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $vps->renewal_type === 'automatic' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                            {{ ucfirst($vps->renewal_type) }}
                                        </span>
                                        <div class="text-xs mt-1">Renewal</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="px-4 sm:px-6 py-3 bg-gray-50">
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <div class="flex items-center space-x-4">
                                    @if($vps->last_synced_at)
                                        <span>Last synced: {{ $vps->last_synced_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                @if($vps->isExpiringSoon() || $vps->isExpired())
                                    <div class="flex items-center space-x-1 text-{{ $vps->isExpired() ? 'red' : 'yellow' }}-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <span class="font-medium">
                                            {{ $vps->isExpired() ? 'Service Expired' : 'Expires Soon' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $vpsServices->links() }}
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No VPS services</h3>
                    <p class="mt-1 text-sm text-gray-500">Sync your OVH services to see them here.</p>
                    <div class="mt-6">
                        <button wire:click="syncServices"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 disabled:bg-red-400 disabled:cursor-not-allowed">
                            <svg wire:loading.remove wire:target="syncServices" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg wire:loading wire:target="syncServices" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="syncServices">Sync VPS Services</span>
                            <span wire:loading wire:target="syncServices">Syncing...</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
