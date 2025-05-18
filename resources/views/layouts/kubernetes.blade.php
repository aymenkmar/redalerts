<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>RedAlerts - Kubernetes Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-52 bg-gray-900 text-white flex-shrink-0">
            <div class="p-4">
                <a href="{{ route('main-dashboard') }}" class="flex items-center space-x-2 mb-6">
                    <div class="bg-red-600 p-1 rounded">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium">Main Dashboard</span>
                </a>

                <div class="bg-red-600 rounded py-2 px-3 mb-2">
                    <a href="{{ route('dashboard-kubernetes') }}" class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-sm font-medium">Cluster Overview</span>
                    </a>
                </div>

                <a href="{{ route('kubernetes.nodes') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <span class="text-sm">Nodes</span>
                </a>

                <!-- Workloads Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/workloads*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads*') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-sm">Workloads</span>
                        </div>
                        <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" class="ml-4 mt-2 space-y-1">
                        <a href="{{ route('kubernetes.workloads.pods') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/pods') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Pods</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.deployments') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/deployments') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Deployments</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.daemonsets') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/daemonsets') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Daemon Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.statefulsets') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/statefulsets') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Stateful Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.replicasets') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/replicasets') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Replica Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.replicationcontrollers') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/replicationcontrollers') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Replication Controllers</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.jobs') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/jobs') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Jobs</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.cronjobs') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/cronjobs') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Cron Jobs</span>
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-sm">Config</span>
                    <svg class="h-4 w-4 text-gray-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                <div class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                    <span class="text-sm">Network</span>
                    <svg class="h-4 w-4 text-gray-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                <div class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    <span class="text-sm">Storage</span>
                    <svg class="h-4 w-4 text-gray-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header with Cluster Selector -->
            <header class="bg-white shadow-sm py-3 px-6 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('main-dashboard') }}" class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Dashboard
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Cluster Selector Component -->
                    <livewire:kubernetes.cluster-selector />

                    <div class="text-gray-700">{{ Auth::user()->name }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition duration-200 flex items-center text-sm">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-6 bg-gray-100">
                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50"></div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('showNotification', (data) => {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = data.type === 'success'
                    ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 flex items-center'
                    : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 flex items-center';

                // Add icon
                const icon = document.createElement('svg');
                icon.className = 'w-5 h-5 mr-2';
                icon.setAttribute('fill', 'none');
                icon.setAttribute('viewBox', '0 0 24 24');
                icon.setAttribute('stroke', 'currentColor');

                const path = document.createElement('path');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
                path.setAttribute('stroke-width', '2');

                if (data.type === 'success') {
                    path.setAttribute('d', 'M5 13l4 4L19 7');
                } else {
                    path.setAttribute('d', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z');
                }

                icon.appendChild(path);
                notification.appendChild(icon);

                // Add message
                const message = document.createElement('span');
                message.textContent = data.message;
                notification.appendChild(message);

                // Add to container
                document.getElementById('notification-container').appendChild(notification);

                // Remove after 5 seconds
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            });

            // Handle refresh after upload
            Livewire.on('refreshAfterUpload', () => {
                // Wait a short time to allow the notification to be shown
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });
        });
    </script>
</body>
</html>
