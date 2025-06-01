<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pod Shell Test - RedAlerts</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Pod Shell Test</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Test Pod Shell Connection
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="namespace" class="block text-sm font-medium text-gray-700">Namespace</label>
                                <input type="text" id="namespace" value="default" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="pod" class="block text-sm font-medium text-gray-700">Pod Name</label>
                                <input type="text" id="pod" placeholder="Enter pod name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="container" class="block text-sm font-medium text-gray-700">Container (optional)</label>
                                <input type="text" id="container" placeholder="Leave empty for first container" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                            
                            <div class="flex space-x-3">
                                <button onclick="testShellConnection()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Connect to Shell
                                </button>
                                
                                <button onclick="disconnectShell()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Disconnect
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-2">Instructions:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>1. Make sure you have a running Kubernetes cluster with kubectl access</li>
                                <li>2. Upload a valid kubeconfig file in the main dashboard</li>
                                <li>3. Enter the namespace and pod name of a running pod</li>
                                <li>4. Click "Connect to Shell" to open the terminal</li>
                                <li>5. The terminal will appear at the bottom of the page</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminal Panel (Hidden by default) -->
    <div id="terminal-panel" class="hidden fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-700 z-50" style="height: 400px;">
        <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                <span id="terminal-title" class="text-sm text-gray-300">Terminal</span>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    onclick="window.podTerminal?.clear()"
                    class="px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded hover:bg-gray-600"
                    title="Clear Terminal"
                >
                    Clear
                </button>
                <button
                    onclick="disconnectShell()"
                    class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700"
                    title="Close Terminal"
                >
                    ✕
                </button>
            </div>
        </div>
        <div id="terminal-container" class="w-full h-full bg-black"></div>
    </div>

    <script>
        async function testShellConnection() {
            const namespace = document.getElementById('namespace').value.trim();
            const pod = document.getElementById('pod').value.trim();
            const container = document.getElementById('container').value.trim() || null;

            if (!namespace || !pod) {
                alert('Please enter both namespace and pod name');
                return;
            }

            try {
                // Update terminal title
                const terminalTitle = document.getElementById('terminal-title');
                if (terminalTitle) {
                    terminalTitle.textContent = `${namespace}/${pod}${container ? `/${container}` : ''}`;
                }

                // Connect to pod shell
                const success = await window.podTerminal.connect(namespace, pod, container);
                
                if (!success) {
                    alert('Failed to connect to pod shell');
                }
            } catch (error) {
                console.error('Error opening pod shell:', error);
                alert('Error opening pod shell: ' + error.message);
            }
        }

        function disconnectShell() {
            if (window.podTerminal) {
                window.podTerminal.disconnect();
            }
        }
    </script>
</body>
</html>
