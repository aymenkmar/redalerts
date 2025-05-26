<div class="min-h-screen flex flex-col">
    <style>
        /* Background pattern */
        .bg-pattern {
            background-color: #f9fafb;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e5e7eb' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Card hover effects */
        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .icon-container {
            transition: all 0.3s ease;
        }

        .feature-card:hover .icon-container {
            transform: scale(1.1);
        }
    </style>

    <!-- Header with Logout -->
    <header class="bg-white shadow-sm py-4 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <div class="bg-red-600 p-2 rounded-full mr-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-800">RedAlerts</h1>
            </div>

            <div class="flex items-center space-x-4">
                <div class="relative" x-data="{ open: false }" @click.away="open = false" x-persist="profileDropdown">
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
    </header>

    <!-- Main Content -->
    <div class="flex-grow py-12 px-6 bg-pattern">
        <div class="max-w-7xl mx-auto">
            <!-- Dashboard Header -->
            <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">RedAlerts Dashboard</h1>
                <p class="text-gray-600">Welcome to RedAlerts monitoring platform. Select a monitoring feature below to get started.</p>
            </div>

            <!-- Feature Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Kubernetes Clusters Card -->
                <a href="{{ route('dashboard-kubernetes') }}" wire:navigate class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-red-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Kubernetes Clusters</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Monitor and manage your Kubernetes clusters, pods, deployments, and other resources.</p>
                    </div>
                </a>

                <!-- Website Monitoring Card -->
                <a href="{{ route('website-monitoring.list') }}" wire:navigate class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-red-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Website Monitoring</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Monitor website uptime, SSL certificates, domain expiration, and availability status.</p>
                    </div>
                </a>

                <!-- OVH Server Expiration Card -->
                <a href="#" class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-gray-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">OVH Server Expiration</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Monitor OVH server expiration dates and renewal status.</p>
                    </div>
                </a>
            </div>

            <!-- Admin Dashboard Link -->
            <div class="mt-8 text-center">
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Admin Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
