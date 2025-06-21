<?php

namespace App\Http\Controllers;

use App\Models\SecurityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;

class SecurityReportController extends Controller
{
    public function downloadJson(SecurityReport $report)
    {
        try {
            if ($report->status !== 'completed') {
                abort(404, 'Report is not ready for download.');
            }

            $filePath = $report->getJsonReportFullPath();
            
            if (!$filePath || !file_exists($filePath)) {
                abort(404, 'JSON report file not found.');
            }
            
            if (!is_readable($filePath)) {
                abort(403, 'JSON report file is not readable.');
            }
            
            $clusterName = $this->sanitizeForFilename($report->cluster_name);
            $fileName = "{$clusterName}_security_report_{$report->scan_completed_at->format('Y-m-d_H-i-s')}.json";

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/json',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to download JSON report', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'cluster' => $report->cluster_name
            ]);
            abort(500, 'Failed to download JSON report.');
        }
    }

    public function downloadPdf(SecurityReport $report)
    {
        try {
            if ($report->status !== 'completed') {
                abort(404, 'Report is not ready for download.');
            }

            // Generate ASCII-safe PDF content
            $html = $this->generateSafePdfContent($report);
            
            // Configure PDF options for maximum compatibility
            $options = new Options();
            $options->set('defaultFont', 'Helvetica');
            $options->set('isRemoteEnabled', false);
            $options->set('isHtml5ParserEnabled', false);
            $options->set('debugKeepTemp', false);
            
            // Create PDF with ASCII-only content
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $clusterName = $this->sanitizeForFilename($report->cluster_name);
            $fileName = "{$clusterName}_security_report_{$report->scan_completed_at->format('Y-m-d_H-i-s')}.pdf";
            
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to generate PDF report', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'cluster' => $report->cluster_name,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Failed to generate PDF report.');
        }
    }

    private function sanitizeForFilename($text)
    {
        if (empty($text)) {
            return 'report';
        }
        
        // Remove any non-ASCII characters and replace with safe alternatives
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        $text = preg_replace('/[^a-zA-Z0-9_-]/', '_', $text);
        $text = trim($text, '_');
        
        return $text ?: 'report';
    }

    private function generateSafePdfContent($report)
    {
        // Use only ASCII characters for maximum compatibility
        $clusterName = $this->sanitizeForFilename($report->cluster_name);
        $scanDate = $report->scan_completed_at->format('F j, Y \a\t g:i A');
        $duration = $report->scan_duration_seconds ? gmdate('H:i:s', $report->scan_duration_seconds) : 'N/A';
        $severityLevel = ucfirst($report->getSeverityLevel());
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Report - ' . $clusterName . '</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e5e5e5; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #1f2937; margin-bottom: 10px; }
        .subtitle { font-size: 14px; color: #6b7280; }
        .summary { margin: 20px 0; }
        .summary-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .summary-table th, .summary-table td { padding: 10px; border: 1px solid #e5e5e5; text-align: center; }
        .summary-table th { background-color: #f9fafb; font-weight: bold; }
        .critical { background-color: #fef2f2; color: #dc2626; }
        .high { background-color: #fff7ed; color: #ea580c; }
        .medium { background-color: #fffbeb; color: #d97706; }
        .low { background-color: #eff6ff; color: #2563eb; }
        .unknown { background-color: #f9fafb; color: #6b7280; }
        .info-section { margin: 20px 0; }
        .info-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #1f2937; }
        .info-content { background-color: #f9fafb; padding: 15px; border-radius: 5px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e5e5; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Security Scan Report</div>
        <div class="subtitle">Cluster: ' . $clusterName . '</div>
        <div class="subtitle">Generated on ' . $scanDate . '</div>
    </div>
    
    <div class="info-section">
        <div class="info-title">Scan Information</div>
        <div class="info-content">
            <strong>Cluster Name:</strong> ' . $clusterName . '<br>
            <strong>Scan Date:</strong> ' . $scanDate . '<br>
            <strong>Scan Duration:</strong> ' . $duration . '<br>
            <strong>Total Security Issues:</strong> ' . $report->total_vulnerabilities . '<br>
            <strong>Severity Level:</strong> ' . $severityLevel . '
        </div>
    </div>
    
    <div class="summary">
        <div class="info-title">Vulnerability Summary</div>
        <table class="summary-table">
            <tr>
                <th>Severity</th>
                <th>Count</th>
                <th>Percentage</th>
            </tr>
            <tr class="critical">
                <td>Critical</td>
                <td>' . $report->critical_count . '</td>
                <td>' . ($report->total_vulnerabilities > 0 ? round(($report->critical_count / $report->total_vulnerabilities) * 100, 1) : 0) . '%</td>
            </tr>
            <tr class="high">
                <td>High</td>
                <td>' . $report->high_count . '</td>
                <td>' . ($report->total_vulnerabilities > 0 ? round(($report->high_count / $report->total_vulnerabilities) * 100, 1) : 0) . '%</td>
            </tr>
            <tr class="medium">
                <td>Medium</td>
                <td>' . $report->medium_count . '</td>
                <td>' . ($report->total_vulnerabilities > 0 ? round(($report->medium_count / $report->total_vulnerabilities) * 100, 1) : 0) . '%</td>
            </tr>
            <tr class="low">
                <td>Low</td>
                <td>' . $report->low_count . '</td>
                <td>' . ($report->total_vulnerabilities > 0 ? round(($report->low_count / $report->total_vulnerabilities) * 100, 1) : 0) . '%</td>
            </tr>
            <tr class="unknown">
                <td>Unknown</td>
                <td>' . $report->unknown_count . '</td>
                <td>' . ($report->total_vulnerabilities > 0 ? round(($report->unknown_count / $report->total_vulnerabilities) * 100, 1) : 0) . '%</td>
            </tr>
        </table>
    </div>
    
    <div class="info-section">
        <div class="info-title">Recommendations</div>
        <div class="info-content">
            ' . $this->generateRecommendations($report) . '
        </div>
    </div>
    
    <div class="footer">
        Generated by RedAlerts Security Dashboard<br>
        For detailed vulnerability information, please refer to the JSON report.
    </div>
</body>
</html>';
        
        return $html;
    }

    private function generateRecommendations($report)
    {
        $recommendations = [];
        
        if ($report->critical_count > 0) {
            $recommendations[] = "<strong>Immediate Action Required:</strong> " . $report->critical_count . " critical vulnerabilities found. These should be addressed immediately as they pose severe security risks.";
        }
        
        if ($report->high_count > 0) {
            $recommendations[] = "<strong>High Priority:</strong> " . $report->high_count . " high-severity vulnerabilities detected. Plan to address these within the next few days.";
        }
        
        if ($report->medium_count > 0) {
            $recommendations[] = "<strong>Medium Priority:</strong> " . $report->medium_count . " medium-severity issues found. Include these in your next maintenance cycle.";
        }
        
        if ($report->total_vulnerabilities == 0) {
            $recommendations[] = "<strong>Excellent:</strong> No vulnerabilities detected in this scan. Continue monitoring regularly.";
        }
        
        $recommendations[] = "<strong>Regular Scanning:</strong> Schedule regular security scans to maintain cluster security posture.";
        $recommendations[] = "<strong>Detailed Analysis:</strong> Download the JSON report for complete vulnerability details and remediation steps.";
        
        return implode('<br><br>', $recommendations);
    }
}
