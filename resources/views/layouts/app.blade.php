<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>RedAlerts - Kubernetes Monitoring Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #e5e5e5;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #d1d1d1;
        }

        /* Subtle animations */
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: rgba(255, 255, 255, 0.1);
            transition: width 0.3s ease;
        }

        .nav-link:hover::before {
            width: 4px;
        }

        .nav-link.active::before {
            width: 4px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        /* Background pattern */
        .bg-pattern {
            background-color: #f9fafb;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e5e7eb' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-pattern min-h-screen" x-data>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-red-600 to-red-700 text-white shadow-xl z-10">
            <div class="p-6 flex items-center space-x-3 border-b border-red-500 border-opacity-30">
                <div class="bg-white p-2 rounded-full">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold">RedAlerts</h1>
                    <p class="text-xs text-red-200">Monitoring Platform</p>
                </div>
            </div>

            <nav class="mt-8 px-4">
                <div class="space-y-2">
                    <a href="{{ route('main-dashboard') }}"
                       class="nav-link flex items-center space-x-3 py-3 px-4 rounded-lg {{ request()->routeIs('main-dashboard') ? 'bg-red-800 bg-opacity-50 active' : 'hover:bg-red-800 hover:bg-opacity-30' }} transition duration-200">
                        <svg class="h-5 w-5 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Main Dashboard</span>
                    </a>

                    <a href="{{ route('dashboard') }}"
                       class="nav-link flex items-center space-x-3 py-3 px-4 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-red-800 bg-opacity-50 active' : 'hover:bg-red-800 hover:bg-opacity-30' }} transition duration-200">
                        <svg class="h-5 w-5 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Admin Dashboard</span>
                    </a>
                </div>

                <div class="mt-10">
                    <h3 class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Kubernetes</h3>
                    <div class="mt-3 space-y-2">
                        <a href="#"
                           class="nav-link flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-red-800 hover:bg-opacity-30 transition duration-200">
                            <svg class="h-5 w-5 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>Clusters</span>
                        </a>

                        <a href="#"
                           class="nav-link flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-red-800 hover:bg-opacity-30 transition duration-200">
                            <svg class="h-5 w-5 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                            <span>Nodes</span>
                        </a>

                        <a href="#"
                           class="nav-link flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-red-800 hover:bg-opacity-30 transition duration-200">
                            <svg class="h-5 w-5 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Workloads</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="absolute bottom-0 w-64 p-4">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" id="logout-btn" class="w-full py-2.5 px-4 bg-red-800 bg-opacity-50 rounded-lg hover:bg-opacity-70 transition duration-200 flex items-center justify-center space-x-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="py-4 px-8 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $header ?? 'Dashboard' }}</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Dropdown -->
                        @livewire('notification-dropdown')

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

            <main>
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts

    <script>
        // Handle navigation events to close profile dropdown
        document.addEventListener('livewire:navigating', () => {
            // Close profile dropdown during navigation
            const profileDropdown = document.querySelector('[x-persist="profileDropdown"]');
            if (profileDropdown && profileDropdown.__x) {
                profileDropdown.__x.$data.open = false;
            }
        });

        // Ensure dropdown state is properly reset after navigation
        document.addEventListener('livewire:navigated', () => {
            // Force close any open dropdowns after navigation
            const profileDropdown = document.querySelector('[x-persist="profileDropdown"]');
            if (profileDropdown && profileDropdown.__x) {
                profileDropdown.__x.$data.open = false;
            }
        });
    </script>
</body>
</html>
