<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use App\Services\TrivySecurityService;
use App\Models\SecurityReport;
use App\Models\Cluster;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class SecurityDashboard extends Component
{
    use WithPagination;

    public $selectedCluster = null;
    public $clusters = [];
    public $latestReport = null;
    public $scanHistory = [];
    public $isScanning = false;
    public $scanProgress = '';
    public $showHistory = false;

    protected $listeners = [
        'clusterChanged' => 'handleClusterChange',
        'refreshSecurity' => 'loadSecurityData',
        'clusterTabsUpdated' => 'handleClusterTabsUpdated'
    ];

    public function mount()
    {
        try {
            $this->loadClusters();
            $this->loadSelectedClusterFromSession();

            // Check if a cluster is selected, similar to other Kubernetes components
            if (!$this->selectedCluster) {
                // Don't load security data if no cluster is selected
                return;
            }

            $this->loadSecurityData();
        } catch (\Exception $e) {
            Log::error('SecurityDashboard mount failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Set a user-friendly error message
            session()->flash('error', 'Failed to initialize security dashboard. Please try refreshing the page.');
        }
    }

    public function loadClusters()
    {
        try {
            $path = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

            if (!is_dir($path)) {
                return;
            }

            $files = scandir($path);
            $clusterFiles = [];

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $fullPath = $path . '/' . $file;
                if (is_file($fullPath)) {
                    $clusterFiles[] = $file;
                }
            }

            $this->clusters = $clusterFiles;
        } catch (\Exception $e) {
            Log::error('Failed to load clusters for security dashboard: ' . $e->getMessage());
        }
    }

    public function loadSelectedClusterFromSession()
    {
        // Get the currently selected cluster from session (same logic as other components)
        $activeClusterTab = session('activeClusterTab', null);
        $legacyCluster = session('selectedCluster', null);

        // Use the active cluster tab first, then fall back to legacy session
        $sessionCluster = $activeClusterTab ?? $legacyCluster;

        if ($sessionCluster && in_array($sessionCluster, $this->clusters)) {
            $this->selectedCluster = $sessionCluster;
        } else {
            // Don't auto-select a cluster - require explicit selection like other components
            $this->selectedCluster = null;
        }

        Log::info('SecurityDashboard cluster selection', [
            'activeClusterTab' => $activeClusterTab,
            'legacyCluster' => $legacyCluster,
            'selectedCluster' => $this->selectedCluster,
            'availableClusters' => $this->clusters
        ]);
    }

    public function handleClusterChange($cluster)
    {
        Log::info('SecurityDashboard handling cluster change', [
            'newCluster' => $cluster,
            'previousCluster' => $this->selectedCluster
        ]);

        $this->selectedCluster = $cluster;
        $this->loadSecurityData();
    }

    public function handleClusterTabsUpdated()
    {
        // Reload the selected cluster from session when cluster tabs are updated
        $this->loadSelectedClusterFromSession();
        $this->loadSecurityData();
    }



    public function loadSecurityData()
    {
        if (!$this->selectedCluster) {
            return;
        }

        try {
            $securityService = new TrivySecurityService();

            // Store previous scanning state
            $wasScanning = $this->isScanning;

            // Get latest report
            $this->latestReport = $securityService->getLatestReport($this->selectedCluster);

            // Get scan history
            $this->scanHistory = $securityService->getScanHistory($this->selectedCluster, 10);

            // Check if scan is running
            $this->isScanning = $securityService->isScanRunning($this->selectedCluster);

            if ($this->isScanning) {
                // Check if this is a large cluster and adjust message
                $isLargeCluster = in_array($this->selectedCluster, ['HyperV2', 'Production', 'Staging']);
                if ($isLargeCluster) {
                    $this->scanProgress = 'Scanning namespaces individually to avoid timeouts (large cluster - may take 5-10 minutes)...';
                } else {
                    $this->scanProgress = 'Scanning cluster namespaces...';
                }
            } else {
                $this->scanProgress = '';

                // If we just finished scanning, show a success message
                if ($wasScanning && !$this->isScanning && $this->latestReport) {
                    session()->flash('success', 'Security scan completed successfully!');
                }
            }

            Log::info('Security data loaded', [
                'cluster' => $this->selectedCluster,
                'isScanning' => $this->isScanning,
                'wasScanning' => $wasScanning,
                'hasLatestReport' => $this->latestReport !== null,
                'reportId' => $this->latestReport?->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load security data: ' . $e->getMessage());
            session()->flash('error', 'Failed to load security data: ' . $e->getMessage());
        }
    }

    public function startScan()
    {
        if (!$this->selectedCluster) {
            session()->flash('error', 'Please select a cluster first.');
            return;
        }

        try {
            $securityService = new TrivySecurityService();

            // Check if scan is already running
            if ($securityService->isScanRunning($this->selectedCluster)) {
                session()->flash('warning', 'A scan is already running for this cluster.');
                return;
            }

            // Start the scan in background to avoid timeouts
            $this->startBackgroundScan();

            $this->isScanning = true;
            $this->scanProgress = 'Scan started in background...';

            session()->flash('success', 'Security scan started successfully. Large clusters may take several minutes to complete.');

            Log::info('Security scan started via UI', [
                'cluster' => $this->selectedCluster,
                'user_initiated' => true,
                'background_mode' => true
            ]);

            // Refresh data to update UI
            $this->loadSecurityData();

        } catch (\Exception $e) {
            Log::error('Failed to start security scan: ' . $e->getMessage());
            session()->flash('error', 'Failed to start scan: ' . $e->getMessage());
        }
    }

    private function startBackgroundScan()
    {
        // Use the Artisan command to run the scan in background
        // Redirect output to a log file for debugging if needed
        $logFile = storage_path("logs/security-scan-{$this->selectedCluster}-" . date('Y-m-d-H-i-s') . ".log");
        $command = "cd " . base_path() . " && php artisan security:scan {$this->selectedCluster} > {$logFile} 2>&1 &";

        Log::info('Starting background scan', [
            'cluster' => $this->selectedCluster,
            'command' => $command,
            'log_file' => $logFile
        ]);

        // Execute the command in background
        exec($command);

        // Give the process a moment to start
        usleep(500000); // 0.5 seconds
    }

    // Download methods removed - now handled by SecurityReportController via direct routes











    public function toggleHistory()
    {
        $this->showHistory = !$this->showHistory;
    }

    public function refreshData()
    {
        $this->loadSelectedClusterFromSession();
        $this->loadSecurityData();
        session()->flash('success', 'Security data refreshed.');
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session when manually changed
        session(['selectedCluster' => $this->selectedCluster]);
        session(['activeClusterTab' => $this->selectedCluster]);

        // Load security data for the new cluster
        $this->loadSecurityData();
    }

    public function getSeverityBadgeClass($severity)
    {
        return match ($severity) {
            'critical' => 'bg-red-100 text-red-800 border-red-200',
            'high' => 'bg-orange-100 text-orange-800 border-orange-200',
            'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'low' => 'bg-blue-100 text-blue-800 border-blue-200',
            'none' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getSeverityIcon($severity)
    {
        return match ($severity) {
            'critical' => '🔴',
            'high' => '🟠',
            'medium' => '🟡',
            'low' => '🔵',
            'none' => '🟢',
            default => '⚪',
        };
    }

    public function render()
    {
        // Always ensure we have the latest cluster selection from session
        $this->loadSelectedClusterFromSession();

        return view('livewire.kubernetes.security-dashboard')
            ->layout('layouts.kubernetes');
    }
}
