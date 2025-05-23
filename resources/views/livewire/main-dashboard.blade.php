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
                <div class="text-gray-700">{{ Auth::user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
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
                <a href="{{ route('dashboard-kubernetes') }}" class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
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

                <!-- SSL Certificates Card -->
                <a href="#" class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-gray-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">SSL Certificates</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Monitor SSL certificate expiration dates and security status.</p>
                    </div>
                </a>

                <!-- Domain Expiration Card -->
                <a href="#" class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-gray-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Domain Expiration</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Track domain name expiration dates and renewal status.</p>
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

                <!-- Site Availability Card -->
                <a href="#" class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-gray-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Site Availability</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Monitor website uptime, response time, and availability status.</p>
                    </div>
                </a>

                <!-- Storage Monitoring Card -->
                <a href="#" class="feature-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="icon-container bg-gray-600 p-3 rounded-lg mr-4">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Storage Monitoring</h2>
                        </div>
                        <p class="text-gray-600 text-sm">Track storage usage and capacity across your infrastructure.</p>
                    </div>
                </a>
            </div>

            <!-- Admin Dashboard Link -->
            <div class="mt-8 text-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Admin Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
