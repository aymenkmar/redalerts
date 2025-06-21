<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TrivySecurityService;
use App\Models\SecurityReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class SecurityController extends Controller
{
    private TrivySecurityService $securityService;

    public function __construct(TrivySecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Start a security scan for a cluster.
     */
    public function startScan(Request $request)
    {
        $request->validate([
            'cluster_name' => 'required|string|max:255',
        ]);

        $clusterName = $request->input('cluster_name');

        try {
            // Check if scan is already running
            if ($this->securityService->isScanRunning($clusterName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A scan is already running for this cluster.'
                ], 409);
            }

            // Start the scan
            $report = $this->securityService->scanCluster($clusterName, false);

            return response()->json([
                'success' => true,
                'message' => 'Security scan started successfully.',
                'report_id' => $report->id,
                'status' => $report->status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start security scan via API', [
                'cluster_name' => $clusterName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the latest security report for a cluster.
     */
    public function getLatestReport(Request $request, string $clusterName)
    {
        try {
            $report = $this->securityService->getLatestReport($clusterName);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'No security reports found for this cluster.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'report' => [
                    'id' => $report->id,
                    'cluster_name' => $report->cluster_name,
                    'status' => $report->status,
                    'scan_completed_at' => $report->scan_completed_at,
                    'scan_duration_seconds' => $report->scan_duration_seconds,
                    'critical_count' => $report->critical_count,
                    'high_count' => $report->high_count,
                    'medium_count' => $report->medium_count,
                    'low_count' => $report->low_count,
                    'unknown_count' => $report->unknown_count,
                    'total_vulnerabilities' => $report->total_vulnerabilities,
                    'severity_level' => $report->getSeverityLevel(),
                    'trivy_version' => $report->trivy_version,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get latest security report via API', [
                'cluster_name' => $clusterName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scan history for a cluster.
     */
    public function getScanHistory(Request $request, string $clusterName)
    {
        $limit = $request->input('limit', 10);

        try {
            $reports = $this->securityService->getScanHistory($clusterName, $limit);

            $formattedReports = $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'status' => $report->status,
                    'scan_completed_at' => $report->scan_completed_at,
                    'scan_duration_seconds' => $report->scan_duration_seconds,
                    'total_vulnerabilities' => $report->total_vulnerabilities,
                    'severity_level' => $report->getSeverityLevel(),
                    'critical_count' => $report->critical_count,
                    'high_count' => $report->high_count,
                    'medium_count' => $report->medium_count,
                    'low_count' => $report->low_count,
                ];
            });

            return response()->json([
                'success' => true,
                'reports' => $formattedReports
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get scan history via API', [
                'cluster_name' => $clusterName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get scan history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a security report.
     */
    public function downloadReport(Request $request, int $reportId)
    {
        $format = $request->input('format', 'json');

        try {
            $report = SecurityReport::findOrFail($reportId);

            if ($report->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Report is not ready for download.'
                ], 400);
            }

            $filePath = null;
            $fileName = null;
            $mimeType = 'application/octet-stream';

            switch ($format) {
                case 'json':
                    $filePath = $report->getJsonReportFullPath();
                    $fileName = "{$report->cluster_name}_security_report_{$report->created_at->format('Y-m-d_H-i-s')}.json";
                    $mimeType = 'application/json';
                    break;
                case 'summary':
                    $filePath = $report->getSummaryReportFullPath();
                    $fileName = "{$report->cluster_name}_security_summary_{$report->created_at->format('Y-m-d_H-i-s')}.txt";
                    $mimeType = 'text/plain';
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid report format. Use "json" or "summary".'
                    ], 400);
            }

            if (!$filePath || !file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report file not found.'
                ], 404);
            }

            return Response::download($filePath, $fileName, [
                'Content-Type' => $mimeType,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to download security report via API', [
                'report_id' => $reportId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scan status for a cluster.
     */
    public function getScanStatus(Request $request, string $clusterName)
    {
        try {
            $isScanning = $this->securityService->isScanRunning($clusterName);
            
            $runningReport = null;
            if ($isScanning) {
                $runningReport = SecurityReport::where('cluster_name', $clusterName)
                    ->whereIn('status', ['pending', 'running'])
                    ->latest()
                    ->first();
            }

            return response()->json([
                'success' => true,
                'is_scanning' => $isScanning,
                'running_report' => $runningReport ? [
                    'id' => $runningReport->id,
                    'status' => $runningReport->status,
                    'scan_started_at' => $runningReport->scan_started_at,
                ] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get scan status via API', [
                'cluster_name' => $clusterName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get scan status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get security overview for all clusters.
     */
    public function getSecurityOverview(Request $request)
    {
        try {
            // Get all clusters
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));
            $clusters = [];
            
            if (is_dir($kubeconfigPath)) {
                $files = scandir($kubeconfigPath);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && is_file($kubeconfigPath . '/' . $file)) {
                        $clusters[] = $file;
                    }
                }
            }

            $overview = [];
            foreach ($clusters as $clusterName) {
                $latestReport = $this->securityService->getLatestReport($clusterName);
                $isScanning = $this->securityService->isScanRunning($clusterName);

                $overview[] = [
                    'cluster_name' => $clusterName,
                    'has_report' => $latestReport !== null,
                    'is_scanning' => $isScanning,
                    'latest_report' => $latestReport ? [
                        'scan_completed_at' => $latestReport->scan_completed_at,
                        'total_vulnerabilities' => $latestReport->total_vulnerabilities,
                        'severity_level' => $latestReport->getSeverityLevel(),
                        'critical_count' => $latestReport->critical_count,
                        'high_count' => $latestReport->high_count,
                    ] : null
                ];
            }

            return response()->json([
                'success' => true,
                'overview' => $overview
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get security overview via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get security overview: ' . $e->getMessage()
            ], 500);
        }
    }
}
