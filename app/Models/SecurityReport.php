<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SecurityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'cluster_name',
        'status',
        'scan_started_at',
        'scan_completed_at',
        'scan_duration_seconds',
        'critical_count',
        'high_count',
        'medium_count',
        'low_count',
        'unknown_count',
        'total_vulnerabilities',
        'json_report_path',
        'summary_report_path',
        'pdf_report_path',
        'trivy_version',
        'scan_command',
        'error_message',
        'scan_metadata',
        'is_scheduled',
        'next_scheduled_scan',
    ];

    protected $casts = [
        'scan_started_at' => 'datetime',
        'scan_completed_at' => 'datetime',
        'next_scheduled_scan' => 'datetime',
        'scan_metadata' => 'array',
        'is_scheduled' => 'boolean',
    ];

    /**
     * Get the cluster associated with this security report.
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class, 'cluster_name', 'name');
    }

    /**
     * Get the latest security report for a cluster.
     */
    public static function getLatestForCluster(string $clusterName): ?self
    {
        return self::where('cluster_name', $clusterName)
            ->where('status', 'completed')
            ->latest('scan_completed_at')
            ->first();
    }

    /**
     * Get all completed reports for a cluster.
     */
    public static function getHistoryForCluster(string $clusterName, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('cluster_name', $clusterName)
            ->where('status', 'completed')
            ->latest('scan_completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if a scan is currently running for a cluster.
     */
    public static function isScanRunning(string $clusterName): bool
    {
        return self::where('cluster_name', $clusterName)
            ->whereIn('status', ['pending', 'running'])
            ->exists();
    }

    /**
     * Get the severity level based on vulnerability counts.
     */
    public function getSeverityLevel(): string
    {
        if ($this->critical_count > 0) {
            return 'critical';
        } elseif ($this->high_count > 0) {
            return 'high';
        } elseif ($this->medium_count > 0) {
            return 'medium';
        } elseif ($this->low_count > 0) {
            return 'low';
        }
        
        return 'none';
    }

    /**
     * Get the severity color for UI display.
     */
    public function getSeverityColor(): string
    {
        return match ($this->getSeverityLevel()) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            'none' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get formatted scan duration.
     */
    public function getFormattedDuration(): string
    {
        if (!$this->scan_duration_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->scan_duration_seconds / 60);
        $seconds = $this->scan_duration_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    /**
     * Check if the report files exist.
     */
    public function hasReportFiles(): bool
    {
        return $this->json_report_path && file_exists(storage_path('app/' . $this->json_report_path));
    }

    /**
     * Get the full path to the JSON report.
     */
    public function getJsonReportFullPath(): ?string
    {
        return $this->json_report_path ? storage_path('app/' . $this->json_report_path) : null;
    }

    /**
     * Get the full path to the summary report.
     */
    public function getSummaryReportFullPath(): ?string
    {
        return $this->summary_report_path ? storage_path('app/' . $this->summary_report_path) : null;
    }

    /**
     * Mark scan as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'scan_started_at' => now(),
        ]);
    }

    /**
     * Mark scan as completed.
     */
    public function markAsCompleted(array $data = []): void
    {
        $duration = null;
        if ($this->scan_started_at) {
            $duration = now()->diffInSeconds($this->scan_started_at);
            // Handle negative durations (clock skew) and cap at reasonable limits
            if ($duration < 0) {
                $duration = 0;
            } elseif ($duration > 86400) {
                $duration = 86400; // Cap at 24 hours
            }
        }

        $this->update(array_merge([
            'status' => 'completed',
            'scan_completed_at' => now(),
            'scan_duration_seconds' => $duration,
        ], $data));
    }

    /**
     * Mark scan as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $duration = null;
        if ($this->scan_started_at) {
            $duration = now()->diffInSeconds($this->scan_started_at);
            // Handle negative durations (clock skew) and cap at reasonable limits
            if ($duration < 0) {
                $duration = 0;
            } elseif ($duration > 86400) {
                $duration = 86400; // Cap at 24 hours
            }
        }

        $this->update([
            'status' => 'failed',
            'scan_completed_at' => now(),
            'error_message' => $errorMessage,
            'scan_duration_seconds' => $duration,
        ]);
    }
}
