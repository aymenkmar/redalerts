<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🛡️ Security Dashboard</h1>
            <p class="text-gray-600">Trivy security scan reports for Kubernetes clusters</p>
        </div>
        <div class="flex space-x-3">
            <button wire:click="refreshData"
                    onclick="manualRefresh()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200">
                🔄 Refresh
            </button>
            @if($selectedCluster && !$isScanning)
                <button wire:click="startScan" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200">
                    🔍 Start Scan
                </button>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Cluster Selection -->
    @if(empty($clusters))
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-500 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Clusters Available</h3>
            <p class="text-gray-600">Please upload kubeconfig files to start security scanning.</p>
        </div>
    @elseif(!$selectedCluster)
        <!-- Cluster Selection Modal for Security Dashboard -->
        <!-- Show a button to reopen modal if user closed it -->
        <div x-data="{ modalClosed: false }" x-show="!modalClosed" x-init="$watch('modalClosed', value => { if(value) { setTimeout(() => modalClosed = false, 5000); } })">
            <!-- Button to go to cluster overview if modal was closed -->
            <div x-show="modalClosed" class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-yellow-700">
                            No cluster selected. You need to select a cluster to view security reports.
                        </p>
                    </div>
                    <div class="ml-3 flex-shrink-0">
                        <div class="flex space-x-2">
                            <button @click="modalClosed = false" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1 rounded text-sm font-medium transition-colors">
                                Select Cluster
                            </button>
                            <a href="{{ route('dashboard-kubernetes') }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                Go to Overview
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cluster Selection Modal -->
            <div x-data="{
                showClusterModal: true,
                init() {
                    // Listen for cluster selection events
                    if (typeof Livewire !== 'undefined') {
                        Livewire.on('clusterChanged', () => {
                            setTimeout(() => {
                                this.showClusterModal = false;
                            }, 1500);
                        });
                    } else {
                        document.addEventListener('livewire:initialized', () => {
                            Livewire.on('clusterChanged', () => {
                                setTimeout(() => {
                                    this.showClusterModal = false;
                                }, 1500);
                            });
                        });
                    }
                }
            }"
                 x-show="showClusterModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                 @click.self="showClusterModal = false; $parent.modalClosed = true"
                 @keydown.escape.window="showClusterModal = false; $parent.modalClosed = true">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-semibold text-gray-900">Security Dashboard - Cluster Required</h2>
                            </div>
                            <button @click="showClusterModal = false; $parent.modalClosed = true" class="text-gray-400 hover:text-red-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <strong>No cluster selected for security scanning.</strong> You need to select a Kubernetes cluster to view security reports and perform vulnerability scans. Please choose a cluster from the list below or upload a new kubeconfig file.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Cluster Selection Component -->
                        <livewire:kubernetes.cluster-selection-modal />
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Current Scan Status -->
        @if($isScanning)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                    <div>
                        <h3 class="text-lg font-medium text-blue-900">Scan in Progress</h3>
                        <p class="text-blue-700 scan-progress">{{ $scanProgress }}</p>
                        <p class="text-sm text-blue-600 mt-1">
                            <strong>Using namespace-based scanning to avoid timeouts.</strong>
                            Large clusters are scanned namespace by namespace for reliability. This page will update automatically when complete.
                        </p>
                        <div class="mt-2 text-xs text-blue-500">
                            💡 Tip: Namespace-based scanning is faster and more reliable for large clusters like HyperV2.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Latest Report Summary -->
        @if($latestReport)
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Latest Security Report</h2>
                    <p class="text-sm text-gray-600">
                        Scanned {{ $latestReport->scan_completed_at->diffForHumans() }} 
                        ({{ $latestReport->getFormattedDuration() }})
                    </p>
                </div>
                
                <div class="p-6">
                    <!-- Security Metrics Cards -->
                    <div class="grid grid-cols-3 md:grid-cols-5 gap-3 mb-6">
                        <!-- Total Vulnerabilities -->
                        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm aspect-square flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Total Vulnerabilities</div>
                                <div class="text-xl font-bold text-gray-900">{{ $latestReport->total_vulnerabilities }}</div>
                            </div>
                        </div>

                        <!-- Critical -->
                        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm aspect-square flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Critical</div>
                                <div class="text-xl font-bold text-red-600">{{ $latestReport->critical_count }}</div>
                            </div>
                        </div>

                        <!-- High -->
                        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm aspect-square flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="text-xs font-medium text-gray-500 mb-1">High</div>
                                <div class="text-xl font-bold text-orange-600">{{ $latestReport->high_count }}</div>
                            </div>
                        </div>

                        <!-- Medium -->
                        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm aspect-square flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Medium</div>
                                <div class="text-xl font-bold text-yellow-600">{{ $latestReport->medium_count }}</div>
                            </div>
                        </div>

                        <!-- Low & Unknown Combined -->
                        <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm aspect-square flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Low & Other</div>
                                <div class="text-xl font-bold text-blue-600">{{ $latestReport->low_count + $latestReport->unknown_count }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Vulnerability Severity Breakdown -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">Vulnerability Severity Breakdown</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-600 rounded-full mr-3"></div>
                                    <span class="text-sm font-medium text-gray-700">Critical</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $latestReport->critical_count }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                                    <span class="text-sm font-medium text-gray-700">High</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $latestReport->high_count }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                    <span class="text-sm font-medium text-gray-700">Medium</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $latestReport->medium_count }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                    <span class="text-sm font-medium text-gray-700">Low</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $latestReport->low_count }}</span>
                            </div>
                            @if($latestReport->unknown_count > 0)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-400 rounded-full mr-3"></div>
                                    <span class="text-sm font-medium text-gray-700">Unknown</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $latestReport->unknown_count }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Overall Security Status -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">{{ $this->getSeverityIcon($latestReport->getSeverityLevel()) }}</span>
                                <div>
                                    <div class="text-lg font-semibold text-gray-900">
                                        Security Level:
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $this->getSeverityBadgeClass($latestReport->getSeverityLevel()) }}">
                                            {{ ucfirst($latestReport->getSeverityLevel()) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Based on highest severity vulnerabilities found
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Last Scan</div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $latestReport->scan_completed_at->diffForHumans() }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ({{ $latestReport->getFormattedDuration() }})
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Actions -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h3 class="text-base font-semibold text-gray-900 mb-3">Download Reports</h3>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('security.download.json', $latestReport->id) }}"
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center space-x-2 text-sm font-medium">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <span>Download JSON</span>
                            </a>
                            <a href="{{ route('security.download.pdf', $latestReport->id) }}"
                               class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center space-x-2 text-sm font-medium">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <span>Download PDF</span>
                            </a>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 text-center">
                            JSON contains detailed vulnerability data • PDF provides executive summary
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Reports Available -->
            <div class="bg-white rounded-lg shadow p-8 text-center mb-6">
                <div class="text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Security Reports</h3>
                <p class="text-gray-600 mb-4">No security scans have been completed for this cluster yet.</p>
                @if(!$isScanning)
                    <button wire:click="startScan" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md transition duration-200">
                        🔍 Run First Scan
                    </button>
                @endif
            </div>
        @endif

        <!-- Scan History -->
        @if(!empty($scanHistory))
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Scan History</h2>
                    <button wire:click="toggleHistory" 
                            class="text-sm text-gray-600 hover:text-gray-900">
                        {{ $showHistory ? 'Hide' : 'Show' }} History
                    </button>
                </div>
                
                @if($showHistory)
                    <div class="divide-y divide-gray-200">
                        @foreach($scanHistory as $report)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="text-lg mr-3">{{ $this->getSeverityIcon($report->getSeverityLevel()) }}</span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $report->scan_completed_at->format('M j, Y g:i A') }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $report->total_vulnerabilities }} vulnerabilities found
                                            ({{ $report->getFormattedDuration() }})
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $this->getSeverityBadgeClass($report->getSeverityLevel()) }}">
                                        {{ ucfirst($report->getSeverityLevel()) }}
                                    </span>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('security.download.json', $report->id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm px-2 py-1 rounded border border-blue-200 hover:bg-blue-50">
                                            📄 JSON
                                        </a>
                                        <a href="{{ route('security.download.pdf', $report->id) }}"
                                           class="text-red-600 hover:text-red-800 text-sm px-2 py-1 rounded border border-red-200 hover:bg-red-50">
                                            📋 PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endif

    <!-- Auto-refresh system for long-running scans -->
    <script>
        let refreshInterval;
        let isCurrentlyScanning = @json($isScanning);
        let lastScanningState = isCurrentlyScanning;
        let scanStartTime = isCurrentlyScanning ? Date.now() : null;

        function startRefreshInterval() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }

            // More frequent refresh for scanning, especially for large clusters
            let refreshRate = isCurrentlyScanning ? 5000 : 15000; // 5s when scanning, 15s when idle

            refreshInterval = setInterval(() => {
                @this.call('loadSecurityData');

                // Show scan duration for long-running scans
                if (isCurrentlyScanning && scanStartTime) {
                    let duration = Math.floor((Date.now() - scanStartTime) / 1000);
                    let minutes = Math.floor(duration / 60);
                    let seconds = duration % 60;
                    let timeStr = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;

                    // Update scan progress if element exists
                    let progressElement = document.querySelector('.scan-progress');
                    if (progressElement) {
                        progressElement.textContent = `Scan in progress... (${timeStr})`;
                    }
                }
            }, refreshRate);
        }

        function stopRefreshInterval() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // Handle Livewire updates
        document.addEventListener('livewire:updated', function () {
            let wasScanning = isCurrentlyScanning;
            isCurrentlyScanning = @json($isScanning);

            // Track scan start time
            if (!wasScanning && isCurrentlyScanning) {
                scanStartTime = Date.now();
            } else if (wasScanning && !isCurrentlyScanning) {
                scanStartTime = null;
            }

            // If scan just completed, do an immediate refresh
            if (lastScanningState === true && !isCurrentlyScanning) {
                setTimeout(() => {
                    @this.call('loadSecurityData');
                }, 1000);
            }

            lastScanningState = isCurrentlyScanning;

            // Restart interval with appropriate timing
            startRefreshInterval();
        });

        // Start the refresh interval when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startRefreshInterval();
        });

        // Clean up when leaving the page
        window.addEventListener('beforeunload', function() {
            stopRefreshInterval();
        });

        // Manual refresh button enhancement
        window.manualRefresh = function() {
            @this.call('loadSecurityData');
        };
    </script>
</div>
