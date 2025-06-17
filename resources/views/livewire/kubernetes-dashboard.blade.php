<div>
    <h1 class="text-2xl font-bold mb-6">Cluster Overview</h1>

    <!-- Flash Messages -->
    @if(session()->has('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    @if(session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($error)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    </div>
    @endif

    <!-- Upload Form Button -->
    <div class="mb-6 flex justify-end">
        <button
            wire:click="toggleUploadForm"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition duration-200 text-sm"
        >
            {{ $showUploadForm ? 'Hide Upload Form' : 'Upload New Cluster' }}
        </button>
    </div>

    <!-- Upload Form -->
    @if($showUploadForm)
    <div class="bg-white rounded shadow-md p-6 mb-8">
        <livewire:kubernetes.cluster-upload />
    </div>
    @endif

    @if(empty($selectedClusters) || !$activeClusterTab)
        <!-- No Cluster Selected -->
        <div class="bg-white rounded shadow-md p-8 text-center mb-8">
            <p class="text-gray-600">Please select a cluster from the dropdown to view metrics.</p>
        </div>

        <!-- Clusters Grid -->
        @if(count($clusters) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($clusters as $cluster)
            <div class="bg-white rounded shadow-md overflow-hidden">
                <button
                    wire:click="selectCluster('{{ $cluster['name'] }}')"
                    class="w-full h-full p-6 text-left hover:bg-gray-50 transition duration-200"
                >
                    <div class="flex flex-col items-center justify-center">
                        <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $cluster['name'] }}</h2>
                        <p class="text-gray-600 text-sm">
                            Uploaded {{ \Carbon\Carbon::parse($cluster['upload_time'])->diffForHumans() }}
                        </p>
                    </div>
                </button>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded shadow-md p-8 text-center">
            <p class="text-gray-600">No clusters available. Please upload a kubeconfig file.</p>
        </div>
        @endif
    @else
        <!-- Loading State -->
        @if($loading)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
        </div>
        @else
            <!-- Cluster Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Nodes Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-green-500 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nodes Healthy</p>
                            <p class="text-xl font-semibold">
                                {{ $clusterMetrics['nodes']['ready'] }}
                                <span class="text-sm font-normal text-gray-500">/ {{ $clusterMetrics['nodes']['total'] }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pods Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-500 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pods Running</p>
                            <p class="text-xl font-semibold">
                                {{ $clusterMetrics['pods']['running'] }}
                                <span class="text-sm font-normal text-gray-500">/ {{ $clusterMetrics['pods']['total'] }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Memory Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-orange-500 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Memory Usage</p>
                            <p class="text-xl font-semibold">
                                {{ $clusterMetrics['memory']['used'] }}
                                <span class="text-sm font-normal text-gray-500">GB / {{ $clusterMetrics['memory']['total'] }} GB</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- CPU Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-pink-500 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">CPU Usage</p>
                            <p class="text-xl font-semibold">
                                {{ $clusterMetrics['cpu']['used'] }}
                                <span class="text-sm font-normal text-gray-500">Cores / {{ $clusterMetrics['cpu']['total'] }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workloads Summary -->
            <h2 class="text-xl font-bold mb-6">Workloads Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Deployments Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-purple-600 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Deployments</p>
                            <p class="text-xl font-semibold">{{ $clusterMetrics['deployments'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- DaemonSets Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-indigo-600 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">DaemonSets</p>
                            <p class="text-xl font-semibold">{{ $clusterMetrics['daemonSets'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- StatefulSets Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-teal-600 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">StatefulSets</p>
                            <p class="text-xl font-semibold">{{ $clusterMetrics['statefulSets'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- CronJobs Card -->
                <div class="bg-white rounded shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-amber-700 p-3 rounded">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">CronJobs</p>
                            <p class="text-xl font-semibold">{{ $clusterMetrics['cronJobs'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
