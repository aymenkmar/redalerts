<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RedAlerts - Kubernetes Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* We're not using custom red colors anymore, using Tailwind's built-in red-600 instead */

        /* VS Code Terminal Style */
        .terminal-vscode {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .terminal-vscode .terminal-header {
            background: #2d2d30;
            border-bottom: 1px solid #3c3c3c;
            border-radius: 8px 8px 0 0;
            padding: 8px 16px;
        }

        .terminal-vscode .terminal-tab {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            border-radius: 4px 4px 0 0;
            padding: 6px 12px;
            margin-right: 4px;
            font-size: 12px;
            color: #cccccc;
        }

        .terminal-vscode .terminal-controls {
            display: flex;
            gap: 8px;
        }

        .terminal-vscode .terminal-btn {
            background: #0e639c;
            border: none;
            border-radius: 4px;
            color: white;
            padding: 4px 8px;
            font-size: 11px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .terminal-vscode .terminal-btn:hover {
            background: #1177bb;
        }

        .terminal-vscode .terminal-btn.danger {
            background: #d73a49;
        }

        .terminal-vscode .terminal-btn.danger:hover {
            background: #e53e3e;
        }

        /* Compact Terminal Buttons */
        .terminal-btn-compact {
            background: #0e639c;
            border: none;
            border-radius: 3px;
            color: white;
            padding: 2px 6px;
            font-size: 10px;
            cursor: pointer;
            transition: background 0.2s;
            min-width: auto;
        }

        .terminal-btn-compact:hover {
            background: #1177bb;
        }

        .terminal-btn-compact.danger {
            background: #d73a49;
        }

        .terminal-btn-compact.danger:hover {
            background: #e53e3e;
        }

        /* Terminal Scrollbar Styling */
        .terminal-scrollable .xterm-viewport {
            overflow-y: auto !important;
            scrollbar-width: thin;
            scrollbar-color: #4a5568 #2d3748;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar {
            width: 12px;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-track {
            background: #2d3748;
            border-radius: 6px;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 6px;
            border: 2px solid #2d3748;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }

        .terminal-scrollable .xterm-viewport::-webkit-scrollbar-thumb:active {
            background: #a0aec0;
        }

        /* Ensure terminal content is scrollable */
        .terminal-scrollable .xterm-screen {
            overflow-y: visible !important;
        }

        /* Terminal container styling */
        .terminal-scrollable {
            overflow: hidden;
            position: relative;
        }

        /* Optimized scrolling performance */
        .terminal-scrollable .xterm-viewport {
            scroll-behavior: auto;
            will-change: scroll-position;
            transform: translateZ(0);
        }

        /* Faster mouse wheel scrolling */
        .terminal-scrollable {
            scroll-behavior: auto;
        }

        /* Logs Container Scrollbar Styling */
        #logs-container {
            overflow-y: scroll !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
            scrollbar-color: #4a5568 #1e1e1e;
            box-sizing: border-box;
            height: 100% !important;
            min-height: 300px !important;
            max-height: 100% !important;
        }

        #logs-container::-webkit-scrollbar {
            width: 14px !important;
            background: #1e1e1e !important;
            display: block !important;
        }

        #logs-container::-webkit-scrollbar-track {
            background: #1e1e1e !important;
            border-radius: 7px;
            display: block !important;
        }

        #logs-container::-webkit-scrollbar-thumb {
            background: #4a5568 !important;
            border-radius: 7px;
            border: 2px solid #1e1e1e;
            min-height: 30px !important;
            display: block !important;
        }

        #logs-container::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }

        #logs-container::-webkit-scrollbar-thumb:active {
            background: #a0aec0;
        }

        #logs-container::-webkit-scrollbar-corner {
            background: #1e1e1e;
        }

        /* Logs content styling for better readability */
        #logs-content {
            font-family: 'Courier New', 'Monaco', 'Menlo', monospace;
            line-height: 1.4;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: calc(100% + 1px);
            padding-bottom: 1px;
        }

        #logs-content pre {
            margin: 0;
            padding: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Force scrollbar visibility for Firefox */
        @-moz-document url-prefix() {
            #logs-container {
                scrollbar-width: auto !important;
            }
        }

        /* Ensure scrollbar is always visible by adding pseudo content */
        #logs-container::after {
            content: '';
            display: block;
            height: 1px;
            width: 1px;
        }

        /* Force scrollbar to appear immediately */
        #logs-panel:not(.hidden) #logs-container {
            overflow-y: scroll !important;
        }
    </style>
</head>
<body x-data="{ sidebarOpen: false }">
    <div class="min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar-container fixed top-0 left-0 w-64 h-full bg-gray-900 text-white z-40 overflow-y-auto transition-transform duration-300 ease-in-out"
             :class="{ 'sidebar-hidden': sidebarOpen }">
            <style>
                .sidebar-container {
                    transform: translateX(0);
                }
                .sidebar-container.sidebar-hidden {
                    transform: translateX(-100%);
                }
            </style>
            <div class="p-6">
                <a href="{{ route('main-dashboard') }}" wire:navigate class="flex items-center space-x-3 mb-8">
                    <div class="bg-red-600 p-2 rounded">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-base font-medium">Main Dashboard</span>
                </a>

                <div class="bg-red-600 rounded py-3 px-4 mb-4">
                    <a href="{{ route('dashboard-kubernetes') }}" wire:navigate class="flex items-center space-x-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-base font-medium">Cluster Overview</span>
                    </a>
                </div>

                <a href="{{ route('kubernetes.nodes') }}" wire:navigate class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/nodes') ? 'bg-red-600' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <span class="text-base">Nodes</span>
                </a>

                <!-- Workloads Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/workloads*') ? 'true' : 'false' }} }" class="mb-2">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads*') ? 'bg-red-600' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-base">Workloads</span>
                        </div>
                        <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.workloads.pods') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/pods') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Pods</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.deployments') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/deployments') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Deployments</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.daemonsets') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/daemonsets') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Daemon Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.statefulsets') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/statefulsets') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Stateful Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.replicasets') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/replicasets') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Replica Sets</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.replicationcontrollers') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/replicationcontrollers') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Replication Controllers</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.jobs') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/jobs') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Jobs</span>
                        </a>
                        <a href="{{ route('kubernetes.workloads.cronjobs') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/workloads/cronjobs') ? 'bg-red-600' : '' }}">
                            <span class="text-base">Cron Jobs</span>
                        </a>
                    </div>
                </div>

                <!-- Config Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/config*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config*') ? 'bg-red-600' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-base">Config</span>
                        </div>
                        <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.config.configmaps') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/configmaps') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Config Maps</span>
                        </a>
                        <a href="{{ route('kubernetes.config.secrets') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/secrets') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Secrets</span>
                        </a>
                        <a href="{{ route('kubernetes.config.resourcequotas') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/resourcequotas') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Resource Quotas</span>
                        </a>
                        <a href="{{ route('kubernetes.config.limitranges') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/limitranges') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Limit Ranges</span>
                        </a>
                        <a href="{{ route('kubernetes.config.horizontalpodautoscalers') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/config/horizontalpodautoscalers') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Horizontal Pod Autoscalers</span>
                        </a>
                    </div>
                </div>

                <!-- Network Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/network*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network*') ? 'bg-red-600' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            <span class="text-base">Network</span>
                        </div>
                        <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.network.services') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/services') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Services</span>
                        </a>
                        <a href="{{ route('kubernetes.network.endpoints') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/endpoints') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Endpoints</span>
                        </a>
                        <a href="{{ route('kubernetes.network.ingresses') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/ingresses') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Ingresses</span>
                        </a>
                        <a href="{{ route('kubernetes.network.ingressclasses') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/ingressclasses') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Ingress Classes</span>
                        </a>
                        <a href="{{ route('kubernetes.network.networkpolicies') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/network/networkpolicies') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Network Policies</span>
                        </a>
                    </div>
                </div>

                <!-- Storage Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/storage*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage*') ? 'bg-red-600' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            <span class="text-base">Storage</span>
                        </div>
                        <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.storage.persistentvolumeclaims') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage/persistentvolumeclaims') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Persistent Volume Claims</span>
                        </a>
                        <a href="{{ route('kubernetes.storage.persistentvolumes') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage/persistentvolumes') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Persistent Volumes</span>
                        </a>
                        <a href="{{ route('kubernetes.storage.storageclasses') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/storage/storageclasses') ? 'bg-red-600' : '' }}">
                            <span class="text-sm">Storage Classes</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('kubernetes.namespaces') }}" wire:navigate class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/namespaces') ? 'bg-red-600' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-base">Namespaces</span>
                </a>

                <a href="{{ route('kubernetes.events') }}" wire:navigate class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/events') ? 'bg-red-600' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-base">Events</span>
                </a>

                <!-- Security Section -->
                <a href="{{ route('kubernetes.security') }}" wire:navigate class="flex items-center space-x-3 py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/security') ? 'bg-red-600' : '' }}">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-base">🛡️ Security</span>
                </a>

                <!-- Access Control Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/serviceaccounts') || request()->is('kubernetes/clusterroles') || request()->is('kubernetes/roles') || request()->is('kubernetes/clusterrolebindings') || request()->is('kubernetes/rolebindings') ? 'true' : 'false' }} }" class="mb-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/serviceaccounts') || request()->is('kubernetes/clusterroles') || request()->is('kubernetes/roles') || request()->is('kubernetes/clusterrolebindings') || request()->is('kubernetes/rolebindings') ? 'bg-red-600' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-base">Access Control</span>
                        </div>
                        <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.serviceaccounts') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/serviceaccounts') ? 'bg-red-600' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-base">Service Accounts</span>
                        </a>
                        <a href="{{ route('kubernetes.clusterroles') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/clusterroles') ? 'bg-red-600' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-base">Cluster Roles</span>
                        </a>
                        <a href="{{ route('kubernetes.roles') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/roles') ? 'bg-red-600' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-base">Roles</span>
                        </a>
                        <a href="{{ route('kubernetes.clusterrolebindings') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/clusterrolebindings') ? 'bg-red-600' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <span class="text-base">Cluster Role Bindings</span>
                        </a>
                        <a href="{{ route('kubernetes.rolebindings') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/rolebindings') ? 'bg-red-600' : '' }}">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <span class="text-base">Role Bindings</span>
                        </a>
                    </div>
                </div>

                <!-- Custom Resources Section -->
                <div x-data="{ open: {{ request()->is('kubernetes/definitions') || request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') || request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'true' : 'false' }} }" class="mt-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/definitions') || request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') || request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'bg-red-600' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                            <span class="text-base">Custom Resources</span>
                        </div>
                        <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                        <a href="{{ route('kubernetes.definitions') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/definitions') ? 'bg-red-600' : '' }}">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm">Definitions</span>
                        </a>

                        <!-- ACME Resources -->
                        <div x-data="{ open: {{ request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') ? 'true' : 'false' }} }" class="mt-2">
                            <button @click="open = !open" class="w-full flex items-center justify-between py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/challenges') || request()->is('kubernetes/orders') ? 'bg-red-600' : '' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="text-base">acme.cert-manager.io</span>
                                </div>
                                <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                                <a href="{{ route('kubernetes.challenges') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/challenges') ? 'bg-red-600' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm">Challenges</span>
                                </a>
                                <a href="{{ route('kubernetes.orders') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/orders') ? 'bg-red-600' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-sm">Orders</span>
                                </a>
                            </div>
                        </div>

                        <!-- Cert Manager Resources -->
                        <div x-data="{ open: {{ request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'true' : 'false' }} }" class="mt-2">
                            <button @click="open = !open" class="w-full flex items-center justify-between py-3 px-4 hover:bg-gray-800 rounded {{ request()->is('kubernetes/certificates') || request()->is('kubernetes/certificaterequests') || request()->is('kubernetes/issuers') || request()->is('kubernetes/clusterissuers') ? 'bg-red-600' : '' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span class="text-base">cert-manager.io</span>
                                </div>
                                <svg x-show="!open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <svg x-show="open" x-cloak class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="ml-6 mt-2 space-y-2">
                                <a href="{{ route('kubernetes.certificates') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/certificates') ? 'bg-red-600' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="text-sm">Certificates</span>
                                </a>
                                <a href="{{ route('kubernetes.certificaterequests') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/certificaterequests') ? 'bg-red-600' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span class="text-sm">Certificate Requests</span>
                                </a>
                                <a href="{{ route('kubernetes.issuers') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/issuers') ? 'bg-red-600' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-sm">Issuers</span>
                                </a>
                                <a href="{{ route('kubernetes.clusterissuers') }}" wire:navigate class="flex items-center space-x-3 py-2 px-3 hover:bg-gray-800 rounded {{ request()->is('kubernetes/clusterissuers') ? 'bg-red-600' : '' }}">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 919-9" />
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
        <div class="main-content-area" :class="{ 'sidebar-hidden': sidebarOpen }">
            <style>
                .main-content-area {
                    margin-left: 256px;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    transition: margin-left 0.3s ease-in-out;
                }
                .main-content-area.sidebar-hidden {
                    margin-left: 0;
                }
            </style>
            <!-- Show sidebar button (when sidebar is hidden) -->
            <div x-show="sidebarOpen" class="fixed top-4 left-4 z-50">
                <button @click="sidebarOpen = false"
                        class="p-2 rounded-md bg-gray-900 text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500 shadow-lg">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Header with Cluster Selector -->
            <header class="bg-white shadow-sm py-3 px-6 flex items-center justify-between sticky top-0 z-50">
                <div class="flex items-center space-x-4">
                    <!-- Hamburger button in navbar -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Multi-Cluster Tabs -->
                    <livewire:kubernetes.cluster-tabs />
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Cluster Selector Component -->
                    <livewire:kubernetes.cluster-selector />

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

                        <div x-show="open" x-cloak
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
                            <a href="{{ route('sso-management') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                SSO Management
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
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-6 bg-gray-100 overflow-x-hidden">
                <div class="max-w-full">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <!-- Terminal Panel (Hidden by default) - Compact Style -->
        <div id="terminal-panel" class="hidden fixed bottom-0 left-0 right-0 terminal-vscode z-50" style="height: 450px; margin: 8px; bottom: 0;">
            <div class="terminal-header flex items-center justify-between" style="padding: 4px 12px; min-height: 32px;">
                <div class="flex items-center space-x-2">
                    <svg class="w-3 h-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="terminal-title" class="text-xs text-gray-300">Terminal</span>
                </div>
                <div class="terminal-controls" style="gap: 4px;">
                    <button
                        onclick="window.podTerminal?.showHistory()"
                        class="terminal-btn-compact"
                        title="Show Command History (or type 'history')"
                    >
                        📜
                    </button>
                    <button
                        onclick="window.podTerminal?.clear()"
                        class="terminal-btn-compact"
                        title="Clear Terminal"
                    >
                        Clear
                    </button>
                    <button
                        onclick="window.podTerminal?.disconnect()"
                        class="terminal-btn-compact danger"
                        title="Close Terminal"
                    >
                        ✕
                    </button>
                </div>
            </div>
            <div id="terminal-container" class="w-full h-full bg-black terminal-scrollable"></div>
        </div>

        <!-- Logs Panel (Hidden by default) - Similar to Terminal Style -->
        <div id="logs-panel" class="hidden fixed bottom-0 left-0 right-0 terminal-vscode z-50" style="height: 450px; margin: 8px; bottom: 0;">
            <div class="terminal-header flex items-center justify-between" style="padding: 4px 12px; min-height: 32px;">
                <div class="flex items-center space-x-2">
                    <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="logs-title" class="text-xs text-gray-300">Pod Logs</span>
                </div>
                <div class="terminal-controls" style="gap: 4px;">
                    <select id="container-selector" class="text-xs bg-gray-700 text-white border-gray-600 rounded px-2 py-1" style="display: none;">
                        <option value="">Select Container</option>
                    </select>
                    <select id="lines-selector" class="text-xs bg-gray-700 text-white border-gray-600 rounded px-2 py-1">
                        <option value="100">100 lines</option>
                        <option value="500">500 lines</option>
                        <option value="1000" selected>1000 lines</option>
                        <option value="5000">5000 lines</option>
                    </select>
                    <button
                        onclick="scrollLogsToTop()"
                        class="terminal-btn-compact"
                        title="Scroll to Top"
                    >
                        ⬆️
                    </button>
                    <button
                        onclick="scrollLogsToBottom()"
                        class="terminal-btn-compact"
                        title="Scroll to Bottom"
                    >
                        ⬇️
                    </button>
                    <button
                        onclick="refreshLogs()"
                        class="terminal-btn-compact"
                        title="Refresh Logs"
                    >
                        🔄
                    </button>
                    <button
                        onclick="downloadLogs()"
                        class="terminal-btn-compact"
                        title="Download Logs"
                    >
                        💾
                    </button>
                    <button
                        onclick="closeLogs()"
                        class="terminal-btn-compact danger"
                        title="Close Logs"
                    >
                        ✕
                    </button>
                </div>
            </div>
            <div id="logs-container" class="w-full h-full bg-black text-green-400 font-mono text-xs p-4">
                <div id="logs-content">Loading logs...</div>
            </div>
        </div>

        <!-- kubectl-ai Chatbot Widget -->
        @livewire('kubectl-ai-chatbot')
    </div>

    @livewireScripts

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50"></div>

    <script>
        // Set selected cluster for JavaScript (from session)
        // Try activeClusterTab first, then fall back to selectedCluster
        window.selectedCluster = @json(session('activeClusterTab') ?? session('selectedCluster'));

        // Debug cluster selection
        console.log('Debug - Session activeClusterTab:', @json(session('activeClusterTab')));
        console.log('Debug - Session selectedCluster:', @json(session('selectedCluster')));
        console.log('Debug - Final window.selectedCluster:', window.selectedCluster);

        // Ensure all dropdowns are closed on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Force close any Alpine dropdowns on page load immediately
            document.querySelectorAll('[x-data]').forEach(element => {
                if (element.__x && element.__x.$data.open !== undefined) {
                    element.__x.$data.open = false;
                }
                if (element.__x && element.__x.$data.showNamespaceFilter !== undefined) {
                    element.__x.$data.showNamespaceFilter = false;
                }
            });

            // Also set a timeout as backup
            setTimeout(() => {
                document.querySelectorAll('[x-data]').forEach(element => {
                    if (element.__x && element.__x.$data.open !== undefined) {
                        element.__x.$data.open = false;
                    }
                    if (element.__x && element.__x.$data.showNamespaceFilter !== undefined) {
                        element.__x.$data.showNamespaceFilter = false;
                    }
                });
            }, 50);
        });

        document.addEventListener('livewire:initialized', () => {
            // Listen for cluster tabs update events
            Livewire.on('clusterTabsUpdated', () => {
                // Find the cluster tabs component and refresh it
                const clusterTabsComponent = document.querySelector('[wire\\:id]');
                if (clusterTabsComponent) {
                    const componentId = clusterTabsComponent.getAttribute('wire:id');
                    if (componentId) {
                        Livewire.find(componentId).call('refreshTabs');
                    }
                }
            });
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

            // Global click handler to close dropdowns
            document.addEventListener('click', function(event) {
                // Skip if the click is on a button with wire:click (let Livewire handle it)
                if (event.target.closest('[wire\\:click]')) {
                    return;
                }

                // Find all open dropdowns
                const openDropdowns = document.querySelectorAll('.cluster-dropdown-menu');

                openDropdowns.forEach(dropdown => {
                    // Check if the click was outside the dropdown
                    if (!dropdown.contains(event.target) &&
                        !event.target.closest('.cluster-dropdown-toggle')) {
                        // Find the Livewire component ID
                        const componentId = dropdown.closest('[wire\\:id]')?.getAttribute('wire:id');
                        if (componentId) {
                            // Call the Livewire method to close the dropdown
                            Livewire.find(componentId).set('showDropdown', false);
                        }
                    }
                });
            });
        });

        // Handle navigation events to close profile dropdown
        document.addEventListener('livewire:navigating', () => {
            // Close all Alpine dropdowns during navigation
            document.querySelectorAll('[x-data]').forEach(element => {
                if (element.__x && element.__x.$data.open !== undefined) {
                    element.__x.$data.open = false;
                }
                if (element.__x && element.__x.$data.showNamespaceFilter !== undefined) {
                    element.__x.$data.showNamespaceFilter = false;
                }
            });
        });

        // Ensure dropdown state is properly reset after navigation
        document.addEventListener('livewire:navigated', () => {
            // Force close any open dropdowns after navigation
            document.querySelectorAll('[x-data]').forEach(element => {
                if (element.__x && element.__x.$data.open !== undefined) {
                    element.__x.$data.open = false;
                }
                if (element.__x && element.__x.$data.showNamespaceFilter !== undefined) {
                    element.__x.$data.showNamespaceFilter = false;
                }
            });
        });

        // Pod Shell and Logs Functions
        function openPodShell(namespace, podName) {
            try {
                // Get the first container if multiple containers exist
                const container = null; // Will use default container

                // Update terminal title
                const terminalTitle = document.getElementById('terminal-title');
                if (terminalTitle) {
                    terminalTitle.textContent = `${namespace}/${podName}${container ? `/${container}` : ''}`;
                }

                // Connect to pod shell
                if (window.podTerminal) {
                    window.podTerminal.connect(namespace, podName, container).then(success => {
                        if (!success) {
                            alert('Failed to connect to pod shell');
                        }
                    }).catch(error => {
                        console.error('Error opening pod shell:', error);
                        alert('Error opening pod shell: ' + error.message);
                    });
                } else {
                    alert('Pod terminal not available');
                }
            } catch (error) {
                console.error('Error opening pod shell:', error);
                alert('Error opening pod shell: ' + error.message);
            }
        }

        // Global variables for logs functionality
        let currentLogsData = {
            namespace: '',
            pod: '',
            container: '',
            cluster: ''
        };

        function openPodLogs(namespace, podName) {
            try {
                // Store current pod info
                currentLogsData.namespace = namespace;
                currentLogsData.pod = podName;
                currentLogsData.cluster = getSelectedCluster();

                // Check if cluster is selected
                if (!currentLogsData.cluster) {
                    alert('Please select a cluster first before viewing pod logs.');
                    return;
                }

                // Update logs title
                const logsTitle = document.getElementById('logs-title');
                if (logsTitle) {
                    logsTitle.textContent = `${namespace}/${podName} - Logs`;
                }

                // First, get containers for this pod
                fetchPodContainers(namespace, podName).then(() => {
                    // Show logs panel
                    showLogsPanel();
                    // Load initial logs
                    loadPodLogs();
                }).catch(error => {
                    console.error('Error fetching pod containers:', error);
                    // Show logs panel anyway with default container
                    showLogsPanel();
                    loadPodLogs();
                });

            } catch (error) {
                console.error('Error opening pod logs:', error);
                alert('Error opening pod logs: ' + error.message);
            }
        }

        function getSelectedCluster() {
            // Get the selected cluster from the global variable
            const cluster = window.selectedCluster || '';
            console.log('Debug - Selected cluster:', cluster);
            console.log('Debug - Window.selectedCluster:', window.selectedCluster);
            return cluster;
        }

        async function fetchPodContainers(namespace, podName) {
            try {
                const response = await fetch(`/kubernetes/logs/containers?cluster=${encodeURIComponent(currentLogsData.cluster)}&namespace=${encodeURIComponent(namespace)}&pod=${encodeURIComponent(podName)}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch containers');
                }

                const data = await response.json();
                const containerSelector = document.getElementById('container-selector');

                if (data.containers && data.containers.length > 1) {
                    // Multiple containers - show selector
                    containerSelector.style.display = 'block';
                    containerSelector.innerHTML = '<option value="">All containers</option>';

                    data.containers.forEach(container => {
                        const option = document.createElement('option');
                        option.value = container.name;
                        option.textContent = container.name;
                        containerSelector.appendChild(option);
                    });

                    // Set up change handler
                    containerSelector.onchange = function() {
                        currentLogsData.container = this.value;
                        loadPodLogs();
                    };
                } else {
                    // Single or no containers - hide selector
                    containerSelector.style.display = 'none';
                    currentLogsData.container = data.containers && data.containers.length > 0 ? data.containers[0].name : '';
                }

            } catch (error) {
                console.error('Error fetching containers:', error);
                // Hide container selector on error
                document.getElementById('container-selector').style.display = 'none';
            }
        }

        function showLogsPanel() {
            // Hide terminal panel if open
            const terminalPanel = document.getElementById('terminal-panel');
            if (terminalPanel) {
                terminalPanel.classList.add('hidden');
            }

            // Show logs panel
            const logsPanel = document.getElementById('logs-panel');
            if (logsPanel) {
                logsPanel.classList.remove('hidden');

                // Force scrollbar to appear immediately
                const logsContainer = document.getElementById('logs-container');
                if (logsContainer) {
                    // Force a reflow to ensure scrollbar appears
                    logsContainer.style.display = 'none';
                    logsContainer.offsetHeight; // Trigger reflow
                    logsContainer.style.display = 'block';

                    // Ensure scrollbar is visible
                    logsContainer.style.overflowY = 'scroll';
                }
            }
        }

        async function loadPodLogs() {
            const logsContent = document.getElementById('logs-content');
            if (!logsContent) return;

            logsContent.innerHTML = 'Loading logs...';

            try {
                const lines = document.getElementById('lines-selector').value;
                const container = currentLogsData.container;

                const params = new URLSearchParams({
                    cluster: currentLogsData.cluster,
                    namespace: currentLogsData.namespace,
                    pod: currentLogsData.pod,
                    lines: lines
                });

                if (container) {
                    params.append('container', container);
                }

                const response = await fetch(`/kubernetes/logs/get?${params.toString()}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch logs');
                }

                const data = await response.json();

                if (data.logs) {
                    // Format logs with proper line breaks and timestamps
                    const formattedLogs = data.logs
                        .split('\n')
                        .map(line => line.trim())
                        .filter(line => line.length > 0)
                        .join('\n');

                    // Remember current scroll position and check if this is initial load
                    const logsContainer = document.getElementById('logs-container');
                    const isInitialLoad = logsContent.textContent === 'Loading logs...';
                    const wasAtBottom = logsContainer &&
                        (logsContainer.scrollTop + logsContainer.clientHeight >= logsContainer.scrollHeight - 10);

                    logsContent.innerHTML = `<pre>${escapeHtml(formattedLogs)}</pre>`;

                    // Only auto-scroll to bottom if user was already at the bottom or this is initial load
                    if (logsContainer && (wasAtBottom || isInitialLoad)) {
                        setTimeout(() => {
                            logsContainer.scrollTop = logsContainer.scrollHeight;
                        }, 100);
                    }
                } else {
                    logsContent.innerHTML = '<div class="text-yellow-400">No logs available</div>';
                }

            } catch (error) {
                console.error('Error loading logs:', error);
                logsContent.innerHTML = `<div class="text-red-400">Error loading logs: ${error.message}</div>`;
            }
        }

        function refreshLogs() {
            loadPodLogs();
        }

        function downloadLogs() {
            const logsContent = document.getElementById('logs-content');
            if (!logsContent) return;

            const logs = logsContent.textContent || logsContent.innerText;
            const blob = new Blob([logs], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = `${currentLogsData.namespace}-${currentLogsData.pod}-logs.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function closeLogs() {
            const logsPanel = document.getElementById('logs-panel');
            if (logsPanel) {
                logsPanel.classList.add('hidden');
            }
        }

        function scrollLogsToTop() {
            const logsContainer = document.getElementById('logs-container');
            if (logsContainer) {
                logsContainer.scrollTop = 0;
            }
        }

        function scrollLogsToBottom() {
            const logsContainer = document.getElementById('logs-container');
            if (logsContainer) {
                logsContainer.scrollTop = logsContainer.scrollHeight;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Set up lines selector change handler and keyboard shortcuts
        document.addEventListener('DOMContentLoaded', function() {
            const linesSelector = document.getElementById('lines-selector');
            if (linesSelector) {
                linesSelector.onchange = function() {
                    if (currentLogsData.pod) {
                        loadPodLogs();
                    }
                };
            }

            // Add keyboard shortcuts for logs panel
            document.addEventListener('keydown', function(e) {
                const logsPanel = document.getElementById('logs-panel');
                if (!logsPanel || logsPanel.classList.contains('hidden')) {
                    return; // Only work when logs panel is open
                }

                // Ctrl/Cmd + Home: Scroll to top
                if ((e.ctrlKey || e.metaKey) && e.key === 'Home') {
                    e.preventDefault();
                    scrollLogsToTop();
                }
                // Ctrl/Cmd + End: Scroll to bottom
                else if ((e.ctrlKey || e.metaKey) && e.key === 'End') {
                    e.preventDefault();
                    scrollLogsToBottom();
                }
                // Ctrl/Cmd + R: Refresh logs
                else if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    refreshLogs();
                }
                // Escape: Close logs panel
                else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeLogs();
                }
            });
        });
    </script>
</body>
</html>
