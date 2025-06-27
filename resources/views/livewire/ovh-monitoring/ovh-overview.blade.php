<div class="min-h-screen bg-gray-50">
    <style>
        /* Card hover effects */
        .service-card {
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .icon-container {
            transition: all 0.3s ease;
        }

        .service-card:hover .icon-container {
            transform: scale(1.1);
        }
    </style>
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
                    <h1 class="text-2xl font-bold text-gray-800">OVH Services Overview</h1>
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
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Page Description -->
        <div class="mb-8 text-center">
            <p class="text-lg text-gray-600">Monitor and manage your OVH services including VPS, dedicated servers, and domains.</p>
            <p class="text-sm text-gray-500 mt-2">Select a service type below to view detailed information and expiration dates.</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Service Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- VPS Services Card -->
            <a href="{{ route('ovh-monitoring.vps') }}" wire:navigate class="service-card bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="icon-container bg-blue-600 p-3 rounded-lg mr-4">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">VPS Services</h3>
                            <p class="text-sm text-gray-500">Virtual Private Servers</p>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Services:</span>
                            <span class="font-semibold text-gray-800">{{ $vpsCount }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-yellow-600">Expiring Soon:</span>
                            <span class="font-semibold text-yellow-600">{{ $vpsExpiring }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-red-600">Expired:</span>
                            <span class="font-semibold text-red-600">{{ $vpsExpired }}</span>
                        </div>
                    </div>

                    <div class="mt-4 text-blue-600 text-sm font-medium">
                        View VPS Services →
                    </div>
                </div>
            </a>

            <!-- Dedicated Servers Card -->
            <a href="{{ route('ovh-monitoring.dedicated-servers') }}" wire:navigate class="service-card bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="icon-container bg-purple-600 p-3 rounded-lg mr-4">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Dedicated Servers</h3>
                            <p class="text-sm text-gray-500">Physical Dedicated Servers</p>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Services:</span>
                            <span class="font-semibold text-gray-800">{{ $dedicatedCount }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-yellow-600">Expiring Soon:</span>
                            <span class="font-semibold text-yellow-600">{{ $dedicatedExpiring }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-red-600">Expired:</span>
                            <span class="font-semibold text-red-600">{{ $dedicatedExpired }}</span>
                        </div>
                    </div>

                    <div class="mt-4 text-purple-600 text-sm font-medium">
                        View Dedicated Servers →
                    </div>
                </div>
            </a>

            <!-- Domains Card -->
            <a href="{{ route('ovh-monitoring.domains') }}" wire:navigate class="service-card bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="icon-container bg-green-600 p-3 rounded-lg mr-4">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s-1.343-9 3-9m-9 9a9 9 0 919-9" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Domains</h3>
                            <p class="text-sm text-gray-500">Domain Registrations</p>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Services:</span>
                            <span class="font-semibold text-gray-800">{{ $domainCount }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-yellow-600">Expiring Soon:</span>
                            <span class="font-semibold text-yellow-600">{{ $domainExpiring }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-red-600">Expired:</span>
                            <span class="font-semibold text-red-600">{{ $domainExpired }}</span>
                        </div>
                    </div>

                    <div class="mt-4 text-green-600 text-sm font-medium">
                        View Domains →
                    </div>
                </div>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                @if($lastSyncTime)
                    <div class="text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Last sync: {{ \Carbon\Carbon::parse($lastSyncTime)->diffForHumans() }}
                        </span>
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-4">
                <button wire:click="syncAllServices"
                        wire:loading.attr="disabled"
                        class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                    <svg wire:loading wire:target="syncAllServices" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="syncAllServices">Sync All Services</span>
                    <span wire:loading wire:target="syncAllServices">Syncing...</span>
                </button>
            </div>
        </div>
    </div>
</div>
