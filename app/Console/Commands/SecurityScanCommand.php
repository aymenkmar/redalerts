<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrivySecurityService;
use App\Models\SecurityReport;
use Illuminate\Support\Facades\Log;

class SecurityScanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:scan 
                            {cluster? : Specific cluster to scan (optional)}
                            {--all : Scan all clusters}
                            {--force : Force scan even if one is already running}
                            {--cleanup : Clean up old reports after scanning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Trivy security scans on Kubernetes clusters';

    private TrivySecurityService $securityService;

    public function __construct(TrivySecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🛡️  Starting Trivy Security Scan');
        $this->info('================================');

        $clusterName = $this->argument('cluster');
        $scanAll = $this->option('all');
        $force = $this->option('force');
        $cleanup = $this->option('cleanup');

        try {
            if ($clusterName) {
                // Scan specific cluster
                $this->scanCluster($clusterName, $force);
            } elseif ($scanAll) {
                // Scan all clusters
                $this->scanAllClusters($force);
            } elseif ($cleanup) {
                // Only cleanup, no scanning
                $this->info('🧹 Cleaning up old reports...');
                $this->securityService->cleanupOldReports();
                $this->info('✅ Cleanup completed');
            } else {
                $this->error('Please specify a cluster name, use --all to scan all clusters, or use --cleanup to clean up old reports.');
                return 1;
            }

            if ($cleanup && ($clusterName || $scanAll)) {
                $this->info('🧹 Cleaning up old reports...');
                $this->securityService->cleanupOldReports();
                $this->info('✅ Cleanup completed');
            }

            $this->info('🎉 Security scan process completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Security scan failed: ' . $e->getMessage());
            Log::error('Security scan command failed', [
                'cluster' => $clusterName,
                'scan_all' => $scanAll,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Scan a specific cluster.
     */
    private function scanCluster(string $clusterName, bool $force = false): void
    {
        $this->info("🔍 Scanning cluster: {$clusterName}");

        // Check if scan is already running
        if (!$force && $this->securityService->isScanRunning($clusterName)) {
            $this->warn("⚠️  Scan already running for cluster: {$clusterName}");
            return;
        }

        // If force is enabled, mark any running scans as failed first
        if ($force && $this->securityService->isScanRunning($clusterName)) {
            $this->info("🔄 Force mode enabled - stopping existing scan...");
            $runningReports = SecurityReport::where('cluster_name', $clusterName)
                ->whereIn('status', ['pending', 'running'])
                ->get();

            foreach ($runningReports as $report) {
                $report->markAsFailed('Scan cancelled by force mode');
            }
            $this->info("✅ Existing scans stopped");
        }

        // Validate cluster exists
        $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
        $kubeconfigFile = $kubeconfigPath . '/' . $clusterName;
        
        if (!file_exists($kubeconfigFile)) {
            $this->error("❌ Kubeconfig file not found for cluster: {$clusterName}");
            return;
        }

        try {
            // Start the scan
            $report = $this->securityService->scanCluster($clusterName, true);
            $this->info("✅ Scan started for cluster: {$clusterName} (Report ID: {$report->id})");

            // Wait for scan to complete (with timeout)
            $this->waitForScanCompletion($report, $clusterName);

        } catch (\Exception $e) {
            $this->error("❌ Failed to scan cluster {$clusterName}: " . $e->getMessage());
            Log::error('Failed to scan cluster via command', [
                'cluster' => $clusterName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Scan all available clusters.
     */
    private function scanAllClusters(bool $force = false): void
    {
        $this->info('🔍 Scanning all clusters...');

        // Get all clusters
        $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
        
        if (!is_dir($kubeconfigPath)) {
            $this->error('❌ Kubeconfig directory not found');
            return;
        }

        $files = scandir($kubeconfigPath);
        $clusters = [];

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_file($kubeconfigPath . '/' . $file)) {
                $clusters[] = $file;
            }
        }

        if (empty($clusters)) {
            $this->warn('⚠️  No clusters found to scan');
            return;
        }

        $this->info("📋 Found " . count($clusters) . " clusters to scan");

        $scannedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($clusters as $clusterName) {
            $this->newLine();
            
            try {
                // Check if scan is already running
                if (!$force && $this->securityService->isScanRunning($clusterName)) {
                    $this->warn("⏭️  Skipping {$clusterName} - scan already running");
                    $skippedCount++;
                    continue;
                }

                $this->info("🔍 Starting scan for: {$clusterName}");
                $report = $this->securityService->scanCluster($clusterName, true);
                
                // For batch scanning, we don't wait for completion to avoid blocking
                $this->info("✅ Scan queued for: {$clusterName} (Report ID: {$report->id})");
                $scannedCount++;

                // Small delay between scans to avoid overwhelming the system
                sleep(2);

            } catch (\Exception $e) {
                $this->error("❌ Failed to start scan for {$clusterName}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("📊 Scan Summary:");
        $this->info("   ✅ Started: {$scannedCount}");
        $this->info("   ⏭️  Skipped: {$skippedCount}");
        $this->info("   ❌ Failed: {$failedCount}");
    }

    /**
     * Wait for scan completion with progress updates.
     */
    private function waitForScanCompletion(SecurityReport $report, string $clusterName): void
    {
        $this->info("⏳ Waiting for scan to complete...");

        // Increase timeout for large clusters like HyperV2
        $maxWaitTime = 7200; // 2 hours max for very large clusters
        $checkInterval = 30; // Check every 30 seconds
        $elapsed = 0;

        $this->info("💡 Large clusters may take 10-20 minutes. Maximum wait time: 2 hours.");

        while ($elapsed < $maxWaitTime) {
            sleep($checkInterval);
            $elapsed += $checkInterval;

            // Refresh report from database
            $report->refresh();

            if ($report->status === 'completed') {
                $this->info("🎉 Scan completed successfully!");
                $this->displayScanResults($report);
                return;
            } elseif ($report->status === 'failed') {
                $this->error("❌ Scan failed: " . ($report->error_message ?? 'Unknown error'));
                return;
            }

            // Show progress
            $minutes = floor($elapsed / 60);
            $this->info("⏳ Still scanning... ({$minutes}m elapsed)");
        }

        $this->warn("⚠️  Scan timeout reached. Check scan status later.");
    }

    /**
     * Display scan results.
     */
    private function displayScanResults(SecurityReport $report): void
    {
        $this->newLine();
        $this->info("📊 Scan Results for {$report->cluster_name}:");
        $this->info("   🔴 Critical: {$report->critical_count}");
        $this->info("   🟠 High: {$report->high_count}");
        $this->info("   🟡 Medium: {$report->medium_count}");
        $this->info("   🔵 Low: {$report->low_count}");
        $this->info("   ⚪ Unknown: {$report->unknown_count}");
        $this->info("   📈 Total: {$report->total_vulnerabilities}");
        $this->info("   ⏱️  Duration: {$report->getFormattedDuration()}");
        $this->info("   🏷️  Severity: " . ucfirst($report->getSeverityLevel()));
    }
}
