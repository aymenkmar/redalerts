<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API Test - RedAlerts</title>
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
                <a href="/dashboard" class="block py-3 px-6 hover:bg-red-700 transition duration-200">Dashboard</a>
                <a href="/api-management" class="block py-3 px-6 hover:bg-red-700 transition duration-200">API Management</a>
                <a href="#" class="block py-3 px-6 hover:bg-red-700 transition duration-200">User Management</a>
                <a href="#" class="block py-3 px-6 hover:bg-red-700 transition duration-200">Settings</a>
                <a href="/auth-test" class="block py-3 px-6 bg-red-700 font-medium">Auth Test</a>
                <a href="https://frontend.redalerts.tn" class="block py-3 px-6 hover:bg-red-700 transition duration-200">Go to Frontend</a>
            </nav>
            <div class="absolute bottom-0 w-64 p-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-red-800 rounded hover:bg-red-900 transition duration-200">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow">
                <div class="py-6 px-8">
                    <h2 class="text-xl font-semibold text-gray-800">API Test</h2>
                </div>
            </header>

            <main class="p-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Test API Endpoints</h3>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Endpoint</label>
                        <div class="flex">
                            <select id="http-method" class="px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-gray-50">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                                <option value="PATCH">PATCH</option>
                                <option value="HEAD">HEAD</option>
                                <option value="OPTIONS">OPTIONS</option>
                            </select>
                            <input type="text" id="api-endpoint" value="/api/clusters"
                                class="flex-1 px-4 py-2 border-y border-r border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <button id="test-api" class="bg-red-600 text-white px-4 py-2 rounded-r-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Test
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Request Body (for POST, PUT, PATCH)</label>
                        <textarea id="request-body" rows="4" placeholder="{\n  \"key\": \"value\"\n}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 font-mono text-sm"></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Authentication</label>
                        <div class="bg-gray-50 p-3 rounded-md border border-gray-300 mb-3">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="use-auth" checked class="mr-2">
                                <label for="use-auth" class="text-sm">Use Authentication Token</label>
                            </div>
                            <div class="flex">
                                <input type="text" id="auth-token" placeholder="Bearer token"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm font-mono">
                                <button id="get-token-btn" class="ml-2 bg-gray-200 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-sm">
                                    Get Token
                                </button>
                            </div>
                        </div>

                        <label class="block text-sm font-medium text-gray-700 mb-2">Headers</label>
                        <div class="bg-gray-50 p-3 rounded-md border border-gray-300">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="header-json" checked class="mr-2">
                                <label for="header-json" class="text-sm">Content-Type: application/json</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="header-accept" checked class="mr-2">
                                <label for="header-accept" class="text-sm">Accept: application/json</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Response</label>
                            <div class="text-sm">
                                <span id="response-status" class="px-2 py-1 rounded-full text-xs font-medium"></span>
                                <span id="response-time" class="text-gray-500 ml-2"></span>
                            </div>
                        </div>
                        <pre id="api-response" class="bg-gray-100 p-4 rounded-md h-64 overflow-auto font-mono text-sm"></pre>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Common API Endpoints</h4>
                        <div class="grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-4">
                            <button class="endpoint-btn px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm text-left truncate" data-endpoint="/api/clusters">/api/clusters</button>
                            <button class="endpoint-btn px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm text-left truncate" data-endpoint="/api/user">/api/user</button>
                            <button class="endpoint-btn px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm text-left truncate" data-endpoint="/api/kpilot/nodes">/api/kpilot/nodes</button>
                            <button class="endpoint-btn px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm text-left truncate" data-endpoint="/api/kpilot/pods">/api/kpilot/pods</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // DOM elements cache
        const elements = {
            httpMethod: document.getElementById('http-method'),
            requestBody: document.getElementById('request-body'),
            apiEndpoint: document.getElementById('api-endpoint'),
            authToken: document.getElementById('auth-token'),
            useAuth: document.getElementById('use-auth'),
            headerJson: document.getElementById('header-json'),
            headerAccept: document.getElementById('header-accept'),
            apiResponse: document.getElementById('api-response'),
            responseStatus: document.getElementById('response-status'),
            responseTime: document.getElementById('response-time'),
            testApiBtn: document.getElementById('test-api'),
            getTokenBtn: document.getElementById('get-token-btn')
        };

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved token from localStorage
            const savedToken = localStorage.getItem('api_auth_token');
            if (savedToken) {
                elements.authToken.value = savedToken;
            }

            // Set initial state of request body visibility
            toggleRequestBodyVisibility();
        });

        // Show/hide request body based on method
        function toggleRequestBodyVisibility() {
            const method = elements.httpMethod.value;
            const bodyContainer = elements.requestBody.parentElement;

            bodyContainer.style.display = ['POST', 'PUT', 'PATCH'].includes(method) ? 'block' : 'none';
        }

        // Event listeners
        elements.httpMethod.addEventListener('change', toggleRequestBodyVisibility);

        // Handle endpoint button clicks
        document.querySelectorAll('.endpoint-btn').forEach(button => {
            button.addEventListener('click', function() {
                elements.apiEndpoint.value = this.dataset.endpoint;
            });
        });

        // Reset response elements
        function resetResponseElements(loadingText = 'Loading...') {
            elements.apiResponse.textContent = loadingText;
            elements.responseStatus.textContent = '';
            elements.responseStatus.className = 'px-2 py-1 rounded-full text-xs font-medium';
            elements.responseTime.textContent = '';
        }

        // Update response status
        function updateResponseStatus(status, statusText, isSuccess) {
            elements.responseStatus.textContent = `${status} ${statusText}`;
            if (isSuccess) {
                elements.responseStatus.classList.add('bg-green-100', 'text-green-800');
            } else {
                elements.responseStatus.classList.add('bg-red-100', 'text-red-800');
            }
        }

        // Get CSRF token
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        }

        // Handle Get Token button click
        elements.getTokenBtn.addEventListener('click', async function() {
            resetResponseElements('Getting token...');

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        email: 'admin@redalerts.tn',
                        password: 'password'
                    }),
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.token) {
                    // Save token to input and localStorage
                    elements.authToken.value = data.token;
                    localStorage.setItem('api_auth_token', data.token);

                    updateResponseStatus('Token obtained', '', true);
                    elements.apiResponse.textContent = JSON.stringify(data, null, 2);
                } else {
                    updateResponseStatus('Failed to get token', '', false);
                    elements.apiResponse.textContent = JSON.stringify(data, null, 2);
                }
            } catch (error) {
                updateResponseStatus('Error', '', false);
                elements.apiResponse.textContent = 'Error: ' + error.message;
            }
        });

        // Function to make API request
        async function makeApiRequest() {
            const method = elements.httpMethod.value;
            const endpoint = elements.apiEndpoint.value;
            const requestBody = elements.requestBody.value;

            resetResponseElements();
            const startTime = performance.now();

            try {
                // Prepare headers
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                };

                // Add authentication token if enabled
                if (elements.useAuth.checked && elements.authToken.value) {
                    const authToken = elements.authToken.value;
                    headers['Authorization'] = authToken.startsWith('Bearer ') ? authToken : `Bearer ${authToken}`;
                }

                if (elements.headerJson.checked) {
                    headers['Content-Type'] = 'application/json';
                }

                if (elements.headerAccept.checked) {
                    headers['Accept'] = 'application/json';
                }

                // Prepare fetch options
                const fetchOptions = {
                    method: method,
                    headers: headers,
                    credentials: 'same-origin'
                };

                // Add body for POST, PUT, PATCH requests
                if (['POST', 'PUT', 'PATCH'].includes(method) && requestBody.trim()) {
                    try {
                        // Try to parse as JSON to validate
                        JSON.parse(requestBody);
                        fetchOptions.body = requestBody;
                    } catch (e) {
                        elements.apiResponse.textContent = 'Error: Invalid JSON in request body';
                        return;
                    }
                }

                // Make the request
                const response = await fetch(endpoint, fetchOptions);
                const endTime = performance.now();
                const duration = Math.round(endTime - startTime);

                // Update status and time
                updateResponseStatus(response.status, response.statusText, response.ok);
                elements.responseTime.textContent = `${duration}ms`;

                // Handle response
                try {
                    const data = await response.json();
                    elements.apiResponse.textContent = JSON.stringify(data, null, 2);
                } catch (e) {
                    // If response is not JSON
                    const text = await response.text();
                    elements.apiResponse.textContent = text || `Status: ${response.status} ${response.statusText}`;
                }
            } catch (error) {
                const endTime = performance.now();
                const duration = Math.round(endTime - startTime);

                updateResponseStatus('Error', '', false);
                elements.responseTime.textContent = `${duration}ms`;
                elements.apiResponse.textContent = 'Error: ' + error.message;
            }
        }

        // Event listeners for API testing
        elements.testApiBtn.addEventListener('click', makeApiRequest);
        elements.apiEndpoint.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                makeApiRequest();
            }
        });
    </script>
</body>
</html>
