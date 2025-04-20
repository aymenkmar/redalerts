<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - RedAlerts</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-red-600 text-white">
            <div class="p-6">
                <h1 class="text-2xl font-bold">RedAlerts</h1>
                <p class="text-sm opacity-75">Admin Dashboard</p>
            </div>
            <nav class="mt-6">
                <a href="/dashboard" class="block py-3 px-6 bg-red-700 font-medium">Dashboard</a>
                <a href="/api-management" class="block py-3 px-6 hover:bg-red-700 transition duration-200">API Management</a>
                <a href="#" class="block py-3 px-6 hover:bg-red-700 transition duration-200">User Management</a>
                <a href="#" class="block py-3 px-6 hover:bg-red-700 transition duration-200">Settings</a>
                <a href="/auth-test" class="block py-3 px-6 hover:bg-red-700 transition duration-200">Auth Test</a>
                <a href="https://frontend.redalerts.tn" class="block py-3 px-6 hover:bg-red-700 transition duration-200">Go to Frontend</a>
            </nav>
            <div class="absolute bottom-0 w-64 p-6">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" id="logout-btn" class="w-full py-2 bg-red-800 rounded hover:bg-red-900 transition duration-200">
                        Logout
                    </button>
                </form>
            </div>
        </div>
 
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow">
                <div class="py-6 px-8">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                </div>
            </header>
 
            <main class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Dashboard Cards -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">API Status</h3>
                        <p class="text-3xl font-bold text-green-500">Online</p>
                    </div>
 
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">API Requests</h3>
                        <p class="text-3xl font-bold text-gray-700">1,234</p>
                    </div>
 
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Active Users</h3>
                        <p class="text-3xl font-bold text-gray-700">{{ \App\Models\User::count() }}</p>
                    </div>
                </div>
 
                <div class="mt-8 bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-800">Recent Activity</h3>
                    </div>
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">API Request</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Auth::user()->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ now()->diffForHumans() }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">User Login</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Auth::user()->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ now()->subMinutes(5)->diffForHumans() }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">System Update</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">system</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ now()->subHour()->diffForHumans() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
