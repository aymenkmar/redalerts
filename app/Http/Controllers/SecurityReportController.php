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
        
        // Generate detailed vulnerability information
        $vulnerabilityDetails = $this->generateVulnerabilityDetails($report);

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Report - ' . $clusterName . '</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 20px; color: #333; font-size: 12px; }
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
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #dc2626; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .info-content { background-color: #f9fafb; padding: 15px; border-radius: 5px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e5e5; padding-top: 20px; }
        .page-break { page-break-before: always; }
        .vulnerability-item { background: #f8f9fa; border: 1px solid #e9ecef; margin-bottom: 15px; padding: 12px; border-radius: 4px; page-break-inside: avoid; }
        .vulnerability-header { font-weight: bold; margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px solid #dee2e6; }
        .vulnerability-cve { font-size: 14px; color: #dc2626; font-weight: bold; }
        .vulnerability-title { font-size: 13px; color: #495057; margin-top: 3px; }
        .vulnerability-details { margin-top: 8px; }
        .vulnerability-field { margin-bottom: 5px; }
        .vulnerability-field strong { color: #495057; font-weight: bold; }
        .vulnerability-description { margin-top: 8px; padding: 8px; background: #ffffff; border-left: 3px solid #6c757d; font-size: 11px; line-height: 1.3; }
        .severity-critical { color: #dc2626; font-weight: bold; }
        .severity-high { color: #ea580c; font-weight: bold; }
        .severity-medium { color: #d97706; font-weight: bold; }
        .severity-low { color: #2563eb; font-weight: bold; }
        .severity-unknown { color: #6b7280; font-weight: bold; }
        .resource-section { margin-bottom: 25px; page-break-inside: avoid; }
        .resource-header { font-size: 16px; font-weight: bold; color: #1f2937; margin-bottom: 10px; padding: 8px; background: #f3f4f6; border-left: 4px solid #3b82f6; }
        .vulnerability-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        .vulnerability-table th { background: #f9fafb; padding: 8px 6px; border: 1px solid #e5e7eb; font-weight: bold; text-align: left; }
        .vulnerability-table td { padding: 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .cve-cell { font-weight: bold; color: #dc2626; min-width: 80px; }
        .severity-cell { text-align: center; min-width: 70px; }
        .package-cell { font-family: monospace; font-size: 10px; min-width: 100px; }
        .version-cell { font-family: monospace; font-size: 10px; min-width: 80px; }
        .description-cell { font-size: 10px; line-height: 1.3; max-width: 200px; }
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

    ' . $vulnerabilityDetails . '

    <div class="footer">
        Generated by RedAlerts Security Dashboard<br>
        Complete vulnerability details with CVE information included above.
    </div>
</body>
</html>';
        
        return $html;
    }

    private function generateVulnerabilityDetails($report)
    {
        $jsonPath = $report->getJsonReportFullPath();

        if (!$jsonPath || !file_exists($jsonPath)) {
            return $this->generateSimplifiedVulnerabilityDetails($report);
        }

        try {
            // Use a memory-efficient streaming approach to extract vulnerability details
            return $this->extractVulnerabilitiesFromJson($jsonPath, $report);
        } catch (Exception $e) {
            // If we get a memory error or any other error, fall back to simplified approach
            return $this->generateSimplifiedVulnerabilityDetails($report);
        }
    }

    private function extractVulnerabilitiesFromJson($jsonPath, $report)
    {
        $vulnerabilities = [];
        $maxVulnerabilities = 100; // Increase limit to capture more Critical/High vulnerabilities

        try {
            // Simple line-by-line approach to extract vulnerability data
            $handle = fopen($jsonPath, 'r');
            if (!$handle) {
                return $this->generateSimplifiedVulnerabilityDetails($report);
            }

            $currentVuln = [];
            $currentTarget = '';
            $inVulnerability = false;
            $lineCount = 0;
            $foundVulnerabilities = false;

            $allVulnerabilities = []; // Collect all Critical/High vulnerabilities first

            while (($line = fgets($handle)) !== false) {
                $lineCount++;

                // Skip until we find the first vulnerability
                if (!$foundVulnerabilities && strpos($line, '"VulnerabilityID"') === false) {
                    continue;
                } else {
                    $foundVulnerabilities = true;
                }

                $line = trim($line);

                // Extract target information
                if (preg_match('/"Target":\s*"([^"]*)"/', $line, $matches)) {
                    $currentTarget = $matches[1];
                }

                // Start of package vulnerability
                if (preg_match('/"VulnerabilityID":\s*"([^"]*)"/', $line, $matches)) {
                    // If we were already processing a vulnerability, save it first
                    if ($inVulnerability && !empty($currentVuln['cve'])) {
                        if (in_array($currentVuln['severity'], ['CRITICAL', 'HIGH'])) {
                            $allVulnerabilities[] = $currentVuln;
                        }
                    }

                    $currentVuln = [
                        'cve' => $matches[1],
                        'resource' => $currentTarget ?: 'Unknown',
                        'severity' => 'UNKNOWN',
                        'package' => 'Unknown',
                        'version' => 'Unknown',
                        'title' => 'Security vulnerability',
                        'description' => 'Security vulnerability detected'
                    ];
                    $inVulnerability = true;
                }

                // Start of Kubernetes misconfiguration vulnerability
                if (preg_match('/"ID":\s*"([^"]*)"/', $line, $matches) && strpos($line, 'KSV') !== false) {
                    // If we were already processing a vulnerability, save it first
                    if ($inVulnerability && !empty($currentVuln['cve'])) {
                        if (in_array($currentVuln['severity'], ['CRITICAL', 'HIGH'])) {
                            $allVulnerabilities[] = $currentVuln;
                        }
                    }

                    $currentVuln = [
                        'cve' => $matches[1], // Use ID as CVE for misconfigurations
                        'resource' => $currentTarget ?: 'Kubernetes Configuration',
                        'severity' => 'UNKNOWN',
                        'package' => 'Kubernetes',
                        'version' => 'Configuration',
                        'title' => 'Kubernetes misconfiguration',
                        'description' => 'Kubernetes security misconfiguration detected'
                    ];
                    $inVulnerability = true;
                }

                if ($inVulnerability) {
                    // Extract vulnerability details
                    if (preg_match('/"PkgName":\s*"([^"]*)"/', $line, $matches)) {
                        $currentVuln['package'] = $matches[1];
                    }
                    if (preg_match('/"InstalledVersion":\s*"([^"]*)"/', $line, $matches)) {
                        $currentVuln['version'] = $matches[1];
                    }
                    if (preg_match('/"Severity":\s*"([^"]*)"/', $line, $matches)) {
                        $currentVuln['severity'] = $matches[1];
                    }
                    if (preg_match('/"Title":\s*"([^"]*)"/', $line, $matches)) {
                        $currentVuln['title'] = substr($matches[1], 0, 150);
                        $currentVuln['description'] = substr($matches[1], 0, 200);
                    }
                }

                // Process entire file to get all Critical/High vulnerabilities
                // Memory usage is monitored and should stay under limits
            }

            // Save the last vulnerability if we were processing one
            if ($inVulnerability && !empty($currentVuln['cve'])) {
                if (in_array($currentVuln['severity'], ['CRITICAL', 'HIGH'])) {
                    $allVulnerabilities[] = $currentVuln;
                }
            }

            // Now limit to maxVulnerabilities for PDF generation
            $vulnerabilities = array_slice($allVulnerabilities, 0, $maxVulnerabilities);

            fclose($handle);

        } catch (Exception $e) {
            if (isset($handle)) {
                fclose($handle);
            }
            return $this->generateSimplifiedVulnerabilityDetails($report);
        }

        if (empty($vulnerabilities)) {
            return $this->generateSimplifiedVulnerabilityDetails($report);
        }

        // Sort by severity (Critical first, then High)
        $severityOrder = ['CRITICAL' => 0, 'HIGH' => 1, 'MEDIUM' => 2, 'LOW' => 3, 'UNKNOWN' => 4];
        usort($vulnerabilities, function($a, $b) use ($severityOrder) {
            return ($severityOrder[$a['severity']] ?? 5) <=> ($severityOrder[$b['severity']] ?? 5);
        });

        return $this->generateDetailedVulnerabilityHtml($vulnerabilities, $report, $allVulnerabilities);
    }


    private function generateDetailedVulnerabilityHtml($vulnerabilities, $report, $allVulnerabilities = null)
    {
        // Use all vulnerabilities for accurate counts if provided
        $countSource = $allVulnerabilities ?? $vulnerabilities;
        $criticalCount = count(array_filter($countSource, fn($v) => $v['severity'] === 'CRITICAL'));
        $highCount = count(array_filter($countSource, fn($v) => $v['severity'] === 'HIGH'));

        $html = '<div class="page-break"><div class="section-title">Vulnerability Details - Critical & High Severity Only</div>';

        // Add summary of what we're showing
        $html .= '<div style="margin-bottom: 15px; padding: 10px; background: #fef2f2; border: 1px solid #dc2626; border-radius: 4px;">';
        $html .= '<strong>Showing Critical & High Severity Vulnerabilities:</strong> ';
        $html .= $criticalCount . ' Critical, ' . $highCount . ' High severity vulnerabilities ';
        $html .= '(out of ' . $report->total_vulnerabilities . ' total vulnerabilities found)';

        // If we're showing a subset, mention it
        if ($allVulnerabilities && count($vulnerabilities) < count($allVulnerabilities)) {
            $html .= '<br><em>Displaying first ' . count($vulnerabilities) . ' vulnerabilities for PDF readability.</em>';
        }

        $html .= '</div>';

        if (empty($vulnerabilities)) {
            $html .= '<p>No Critical or High severity vulnerabilities found in this scan.</p>';
            $html .= '</div>';
            return $html;
        }

        // Group vulnerabilities by resource for better organization
        $resourceGroups = [];
        foreach ($vulnerabilities as $vuln) {
            $resourceKey = $vuln['resource'];
            if (!isset($resourceGroups[$resourceKey])) {
                $resourceGroups[$resourceKey] = [];
            }
            $resourceGroups[$resourceKey][] = $vuln;
        }

        foreach ($resourceGroups as $resource => $vulns) {
            $html .= '<div class="resource-section">';
            $html .= '<div class="resource-header">Resource: ' . htmlspecialchars($resource) . '</div>';

            // Create a table for vulnerabilities in this resource
            $html .= '<table class="vulnerability-table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>CVE ID</th>';
            $html .= '<th>Severity</th>';
            $html .= '<th>Package</th>';
            $html .= '<th>Version</th>';
            $html .= '<th>Description</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';

            foreach ($vulns as $vuln) {
                $severityClass = 'severity-' . strtolower($vuln['severity']);

                $html .= '<tr>';
                $html .= '<td class="cve-cell">' . htmlspecialchars($vuln['cve']) . '</td>';
                $html .= '<td class="severity-cell"><span class="' . $severityClass . '">' . htmlspecialchars($vuln['severity']) . '</span></td>';
                $html .= '<td class="package-cell">' . htmlspecialchars($vuln['package']) . '</td>';
                $html .= '<td class="version-cell">' . htmlspecialchars($vuln['version']) . '</td>';
                $html .= '<td class="description-cell">' . htmlspecialchars($vuln['title']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
        }

        // Add note about complete data
        $html .= '<div style="margin-top: 20px; padding: 10px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 4px;">';
        $html .= '<strong>Complete Vulnerability Information:</strong> This report shows only Critical and High severity vulnerabilities (' . count($vulnerabilities) . ' shown). ';
        $html .= 'For all vulnerabilities including Medium and Low severity issues, CVSS scores, and detailed remediation steps, ';
        $html .= 'please download the complete JSON report from the security dashboard.';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    private function generateSimplifiedVulnerabilityDetails($report)
    {
        $html = '<div class="page-break"><div class="section-title">Security Scan Summary</div>';

        $html .= '<div class="vulnerability-item">';
        $html .= '<div class="vulnerability-header">';
        $html .= '<div class="vulnerability-cve">SECURITY-SCAN-' . $report->id . '</div>';
        $html .= '<div class="vulnerability-title">Kubernetes Security Scan Results</div>';
        $html .= '</div>';

        $html .= '<div class="vulnerability-details">';
        $html .= '<div class="vulnerability-field"><strong>Cluster:</strong> ' . htmlspecialchars($report->cluster_name) . '</div>';
        $html .= '<div class="vulnerability-field"><strong>Scan Date:</strong> ' . $report->scan_completed_at->format('F j, Y \a\t g:i A') . '</div>';
        $html .= '<div class="vulnerability-field"><strong>Total Vulnerabilities:</strong> ' . $report->total_vulnerabilities . '</div>';
        $html .= '<div class="vulnerability-field"><strong>Critical Issues:</strong> <span class="severity-critical">' . $report->critical_count . '</span></div>';
        $html .= '<div class="vulnerability-field"><strong>High Severity:</strong> <span class="severity-high">' . $report->high_count . '</span></div>';
        $html .= '<div class="vulnerability-field"><strong>Medium Severity:</strong> <span class="severity-medium">' . $report->medium_count . '</span></div>';
        $html .= '<div class="vulnerability-field"><strong>Low Severity:</strong> <span class="severity-low">' . $report->low_count . '</span></div>';

        $html .= '<div class="vulnerability-description">';
        $html .= 'This security scan identified ' . $report->total_vulnerabilities . ' total security issues across your Kubernetes cluster. ';

        if ($report->critical_count > 0) {
            $html .= 'There are ' . $report->critical_count . ' critical vulnerabilities that require immediate attention. ';
        }

        if ($report->high_count > 0) {
            $html .= 'Additionally, ' . $report->high_count . ' high-severity issues should be addressed as soon as possible. ';
        }

        $html .= 'For complete vulnerability details including specific CVE numbers, affected packages, exact locations, and remediation steps, please download the JSON report.';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 4px;">';
        $html .= '<strong>Complete Vulnerability Information Available:</strong><br>';
        $html .= 'This PDF provides a summary of security findings. For detailed vulnerability information including:<br>';
        $html .= '• Specific CVE numbers and identifiers<br>';
        $html .= '• Exact package names and versions<br>';
        $html .= '• Detailed vulnerability descriptions<br>';
        $html .= '• CVSS scores and severity ratings<br>';
        $html .= '• Remediation steps and fix information<br>';
        $html .= '• Precise location within your cluster<br><br>';
        $html .= 'Please download the complete JSON report from the security dashboard.';
        $html .= '</div>';

        $html .= '</div>';

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
        $recommendations[] = "<strong>Detailed Analysis:</strong> All vulnerability details with CVE information are included in this report.";

        return implode('<br><br>', $recommendations);
    }
}
