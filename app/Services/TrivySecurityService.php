<?php

namespace App\Services;

use App\Models\SecurityReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;

class TrivySecurityService
{
    private string $kubeconfigPath;
    private string $reportsPath;
    private string $trivyBinary;

    public function __construct()
    {
        $this->kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
        $this->reportsPath = storage_path('app/security-reports');
        $this->trivyBinary = env('TRIVY_BINARY_PATH', 'trivy'); // Default to system PATH
    }

    /**
     * Initiate a security scan for a cluster.
     */
    public function scanCluster(string $clusterName, bool $isScheduled = false): SecurityReport
    {
        // Check if scan is already running
        if (SecurityReport::isScanRunning($clusterName)) {
            throw new \Exception("A scan is already running for cluster: {$clusterName}");
        }

        // Validate cluster exists
        $kubeconfigFile = $this->kubeconfigPath . '/' . $clusterName;
        if (!file_exists($kubeconfigFile)) {
            throw new \Exception("Kubeconfig file not found for cluster: {$clusterName}");
        }

        // Create security report record
        $report = SecurityReport::create([
            'cluster_name' => $clusterName,
            'status' => 'pending',
            'is_scheduled' => $isScheduled,
            'trivy_version' => $this->getTrivyVersion(),
        ]);

        // Start scan asynchronously
        $this->executeScanAsync($report);

        return $report;
    }

    /**
     * Execute scan asynchronously to avoid PHP timeouts.
     */
    private function executeScanAsync(SecurityReport $report): void
    {
        // Create cluster report directory
        $clusterReportDir = $this->reportsPath . '/' . $report->cluster_name;
        if (!is_dir($clusterReportDir)) {
            mkdir($clusterReportDir, 0755, true);
        }

        // Generate timestamp for this scan
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        // Define file paths
        $jsonReportPath = "security-reports/{$report->cluster_name}/{$timestamp}_scan.json";
        $summaryReportPath = "security-reports/{$report->cluster_name}/{$timestamp}_summary.txt";
        
        $jsonFullPath = storage_path('app/' . $jsonReportPath);
        $summaryFullPath = storage_path('app/' . $summaryReportPath);

        // Build Trivy command
        $kubeconfigFile = $this->kubeconfigPath . '/' . $report->cluster_name;
        $command = $this->buildTrivyCommand($kubeconfigFile, $jsonFullPath, $summaryFullPath);

        // Update report with scan command and mark as started
        $report->update([
            'scan_command' => $command,
            'json_report_path' => $jsonReportPath,
            'summary_report_path' => $summaryReportPath,
        ]);
        $report->markAsStarted();

        // Execute scan in background
        $this->runScanCommand($report, $command, $jsonFullPath, $summaryFullPath);
    }

    /**
     * Build the Trivy scan command using shell script.
     */
    private function buildTrivyCommand(string $kubeconfigFile, string $jsonOutputPath, string $summaryOutputPath): string
    {
        $scriptPath = base_path('scripts/trivy-scan.sh');
        $outputDir = dirname($jsonOutputPath);

        return sprintf(
            '%s %s %s %s',
            escapeshellcmd($scriptPath),
            escapeshellarg($this->getClusterNameFromPath($jsonOutputPath)),
            escapeshellarg($kubeconfigFile),
            escapeshellarg($outputDir)
        );
    }

    /**
     * Extract cluster name from JSON output path.
     */
    private function getClusterNameFromPath(string $jsonOutputPath): string
    {
        $pathParts = explode('/', $jsonOutputPath);
        return $pathParts[count($pathParts) - 2]; // Get cluster name from path
    }

    /**
     * Execute the scan command and process results.
     */
    private function runScanCommand(SecurityReport $report, string $command, string $jsonPath, string $summaryPath): void
    {
        try {
            Log::info("Starting Trivy scan for cluster: {$report->cluster_name}", [
                'report_id' => $report->id,
                'command' => $command
            ]);

            // Execute the command
            $process = Process::timeout(3600)->run($command); // 1 hour timeout

            $exitCode = $process->exitCode();
            $output = $process->output();
            $errorOutput = $process->errorOutput();

            if ($exitCode === 0) {
                // Parse shell script output for vulnerability counts
                $scanResults = $this->parseShellScriptOutput($output);

                // Update report with results
                $report->markAsCompleted([
                    'critical_count' => $scanResults['critical'],
                    'high_count' => $scanResults['high'],
                    'medium_count' => $scanResults['medium'],
                    'low_count' => $scanResults['low'],
                    'unknown_count' => $scanResults['unknown'],
                    'total_vulnerabilities' => $scanResults['total'],
                    'scan_duration_seconds' => $scanResults['duration'],
                ]);

                Log::info("Trivy scan completed successfully for cluster: {$report->cluster_name}", [
                    'report_id' => $report->id,
                    'vulnerabilities' => $scanResults['total'],
                    'duration' => $scanResults['duration']
                ]);
            } else {
                // Scan failed
                $errorMessage = "Trivy scan failed with exit code {$exitCode}. Error: {$errorOutput}";
                $report->markAsFailed($errorMessage);

                Log::error("Trivy scan failed for cluster: {$report->cluster_name}", [
                    'report_id' => $report->id,
                    'exit_code' => $exitCode,
                    'error' => $errorOutput
                ]);
            }
        } catch (\Exception $e) {
            $report->markAsFailed($e->getMessage());
            Log::error("Exception during Trivy scan for cluster: {$report->cluster_name}", [
                'report_id' => $report->id,
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parse shell script output to extract scan results.
     */
    private function parseShellScriptOutput(string $output): array
    {
        $defaultResults = [
            'duration' => 0,
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'unknown' => 0,
            'total' => 0
        ];

        // Look for SUCCESS line: SUCCESS:duration:critical:high:medium:low:unknown:total
        if (preg_match('/SUCCESS:(\d+):(\d+):(\d+):(\d+):(\d+):(\d+):(\d+)/', $output, $matches)) {
            return [
                'duration' => (int)$matches[1],
                'critical' => (int)$matches[2],
                'high' => (int)$matches[3],
                'medium' => (int)$matches[4],
                'low' => (int)$matches[5],
                'unknown' => (int)$matches[6],
                'total' => (int)$matches[7]
            ];
        }

        return $defaultResults;
    }



    /**
     * Get Trivy version.
     */
    private function getTrivyVersion(): ?string
    {
        try {
            $process = Process::run($this->trivyBinary . ' --version');
            if ($process->successful()) {
                $output = trim($process->output());
                // Extract just the version number from the first line
                $lines = explode("\n", $output);
                if (!empty($lines)) {
                    return trim($lines[0]);
                }
                return $output;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to get Trivy version: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Get the latest security report for a cluster.
     */
    public function getLatestReport(string $clusterName): ?SecurityReport
    {
        return SecurityReport::getLatestForCluster($clusterName);
    }

    /**
     * Get scan history for a cluster.
     */
    public function getScanHistory(string $clusterName, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return SecurityReport::getHistoryForCluster($clusterName, $limit);
    }

    /**
     * Check if a scan is currently running for a cluster.
     */
    public function isScanRunning(string $clusterName): bool
    {
        return SecurityReport::isScanRunning($clusterName);
    }

    /**
     * Clean up old reports (called by cron job).
     */
    public function cleanupOldReports(int $daysToKeep = 30): void
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        $oldReports = SecurityReport::where('created_at', '<', $cutoffDate)
            ->where('status', 'completed')
            ->get();

        foreach ($oldReports as $report) {
            // Don't delete if it's the latest report for the cluster
            $latestReport = SecurityReport::getLatestForCluster($report->cluster_name);
            if ($latestReport && $latestReport->id === $report->id) {
                continue;
            }

            // Delete report files
            $this->deleteReportFiles($report);
            
            // Delete database record
            $report->delete();
        }

        Log::info("Cleaned up {$oldReports->count()} old security reports");
    }

    /**
     * Delete report files for a security report.
     */
    private function deleteReportFiles(SecurityReport $report): void
    {
        $filePaths = [
            $report->getJsonReportFullPath(),
            $report->getSummaryReportFullPath(),
        ];

        foreach ($filePaths as $filePath) {
            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
