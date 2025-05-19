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
        <div class="w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="p-6">
                <a href="{{ route('main-dashboard') }}" class="flex items-center space-x-3 mb-8">
                    <div class="bg-red-600 p-2 rounded">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-base font-medium">Main Dashboard</span>
                </a>

                <div class="bg-red-600 rounded py-3 px-4 mb-4">
                    <a href="{{ route('dashboard-kubernetes') }}" class="flex items-center space-x-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-base font-medium">Cluster Overview</span>
                    </a>
                </div>

                <a href="{{ route('kubernetes.nodes') }}" class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/nodes') ? 'bg-gray-800' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <span class="text-base">Nodes</span>
                </a>

                <!-- Workloads Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/workloads*') ? 'true' : 'false' }} }" class="mb-2">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads*') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-base">Workloads</span>
                        </div>
                        <svg x-show="!open" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.workloads.pods') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/pods') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Pods</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.deployments') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/deployments') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Deployments</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.daemonsets') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/daemonsets') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Daemon Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.statefulsets') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/statefulsets') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Stateful Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.replicasets') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/replicasets') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Replica Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.replicationcontrollers') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/replicationcontrollers') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Replication Controllers</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.jobs') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/jobs') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Jobs</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.cronjobs') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/cronjobs') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Cron Jobs</span>
                        </a>
                    </div>
                </div>

                <!-- Config Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/config*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config*') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-sm">Config</span>
                        </div>
                        <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" class="ml-4 mt-2 space-y-1">
                        <a href="{{ route('kubernetes.config.configmaps') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/configmaps') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Config Maps</span>
                        </a>
                        <a href="{{ route('kubernetes.config.secrets') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/secrets') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Secrets</span>
                        </a>
                        <a href="{{ route('kubernetes.config.resourcequotas') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/resourcequotas') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Resource Quotas</span>
                        </a>
                        <a href="{{ route('kubernetes.config.limitranges') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/limitranges') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Limit Ranges</span>
                        </a>
                        <a href="{{ route('kubernetes.config.horizontalpodautoscalers') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/horizontalpodautoscalers') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Horizontal Pod Autoscalers</span>
                        </a>
                    </div>
                </div>

                <!-- Network Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/network*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network*') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            <span class="text-sm">Network</span>
                        </div>
                        <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" class="ml-4 mt-2 space-y-1">
                        <a href="{{ route('kubernetes.network.services') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/services') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Services</span>
                        </a>
                        <a href="{{ route('kubernetes.network.endpoints') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/endpoints') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Endpoints</span>
                        </a>
                        <a href="{{ route('kubernetes.network.ingresses') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/ingresses') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Ingresses</span>
                        </a>
                        <a href="{{ route('kubernetes.network.ingressclasses') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/ingressclasses') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Ingress Classes</span>
                        </a>
                        <a href="{{ route('kubernetes.network.networkpolicies') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/networkpolicies') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Network Policies</span>
                        </a>
                    </div>
                </div>

                <!-- Storage Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/storage*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage*') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            <span class="text-sm">Storage</span>
                        </div>
                        <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" class="ml-4 mt-2 space-y-1">
                        <a href="{{ route('kubernetes.storage.persistentvolumeclaims') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage/persistentvolumeclaims') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Persistent Volume Claims</span>
                        </a>
                        <a href="{{ route('kubernetes.storage.persistentvolumes') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage/persistentvolumes') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Persistent Volumes</span>
                        </a>
                        <a href="{{ route('kubernetes.storage.storageclasses') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage/storageclasses') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Storage Classes</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('kubernetes.namespaces') }}" class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/namespaces') ? 'bg-gray-800' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-base">Namespaces</span>
                </a>

                <a href="{{ route('kubernetes.events') }}" class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/events') ? 'bg-gray-800' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-base">Events</span>
                </a>

                <!-- Access Control Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/serviceaccounts') || request()->is('kubernetes/clusterroles') || request()->is('kubernetes/roles') || request()->is('kubernetes/clusterrolebindings') || request()->is('kubernetes/rolebindings') ? 'true' : 'false' }} }" class="mb-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/serviceaccounts') || request()->is('kubernetes/clusterroles') || request()->is('kubernetes/roles') || request()->is('kubernetes/clusterrolebindings') || request()->is('kubernetes/rolebindings') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-base">Access Control</span>
                        </div>
                        <svg x-show="!open" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.serviceaccounts') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/serviceaccounts') ? 'bg-gray-800' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-base">Service Accounts</span>
                        </a>
                        <a href="{{ route('kubernetes.clusterroles') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/clusterroles') ? 'bg-gray-800' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-base">Cluster Roles</span>
                        </a>
                        <a href="{{ route('kubernetes.roles') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/roles') ? 'bg-gray-800' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-base">Roles</span>
                        </a>
                        <a href="{{ route('kubernetes.clusterrolebindings') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/clusterrolebindings') ? 'bg-gray-800' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <span class="text-base">Cluster Role Bindings</span>
                        </a>
                        <a href="{{ route('kubernetes.rolebindings') }}" class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/rolebindings') ? 'bg-gray-800' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <span class="text-base">Role Bindings</span>
                        </a>
                    </div>
                </div>

                <!-- Custom Resources Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/definitions') || request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') || request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'true' : 'false' }} }" class="mt-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/definitions') || request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') || request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'bg-gray-800' : '' }}">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                            <span class="text-sm">Custom Resources</span>
                        </div>
                        <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4 mt-2 space-y-1">
                        <a href="{{ route('kubernetes.definitions') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/definitions') ? 'bg-gray-800' : '' }}">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm">Definitions</span>
                        </a>

                        <!-- ACME Resources -->
                        <div x-data="{ open: {{ request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') ? 'true' : 'false' }} }" class="mt-2">
                            <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') ? 'bg-gray-800' : '' }}">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="text-sm">acme.cert-manager.io</span>
                                </div>
                                <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" class="pl-4 mt-2 space-y-1">
                                <a href="{{ route('kubernetes.challenges') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/challenges') ? 'bg-gray-800' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm">Challenges</span>
                                </a>
                                <a href="{{ route('kubernetes.orders') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/orders') ? 'bg-gray-800' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-sm">Orders</span>
                                </a>
                            </div>
                        </div>

                        <!-- Cert Manager Resources -->
                        <div x-data="{ open: {{ request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'true' : 'false' }} }" class="mt-2">
                            <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'bg-gray-800' : '' }}">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span class="text-sm">cert-manager.io</span>
                                </div>
                                <svg x-show="!open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <svg x-show="open" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" class="pl-4 mt-2 space-y-1">
                                <a href="{{ route('kubernetes.certificates') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/certificates') ? 'bg-gray-800' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="text-sm">Certificates</span>
                                </a>
                                <a href="{{ route('kubernetes.certificaterequests') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/certificaterequests') ? 'bg-gray-800' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span class="text-sm">Certificate Requests</span>
                                </a>
                                <a href="{{ route('kubernetes.issuers') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/issuers') ? 'bg-gray-800' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-sm">Issuers</span>
                                </a>
                                <a href="{{ route('kubernetes.clusterissuers') }}" class="flex items-center space-x-2 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/clusterissuers') ? 'bg-gray-800' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <span class="text-sm">Cluster Issuers</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header with Cluster Selector -->
            <header class="bg-white shadow-sm py-3 px-6 flex items-center justify-between">
                <div class="flex items-center">
                    <!-- Empty div to maintain layout -->
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
