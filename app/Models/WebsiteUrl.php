<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebsiteUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'url',
        'monitor_status',
        'monitor_domain',
        'monitor_ssl',
        'current_status',
        'response_time',
        'status_code',
        'last_checked_at',
        'last_status_change',
        'last_error',
        'domain_warning_notification_sent_at',
        'ssl_warning_notification_sent_at',
        'domain_warning_notification_count',
        'ssl_warning_notification_count',
    ];

    protected $casts = [
        'monitor_status' => 'boolean',
        'monitor_domain' => 'boolean',
        'monitor_ssl' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_status_change' => 'datetime',
        'domain_warning_notification_sent_at' => 'datetime',
        'ssl_warning_notification_sent_at' => 'datetime',
        'domain_warning_notification_count' => 'integer',
        'ssl_warning_notification_count' => 'integer',
    ];

    /**
     * Get the website that owns the URL.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the monitoring logs for the URL.
     */
    public function monitoringLogs(): HasMany
    {
        return $this->hasMany(WebsiteMonitoringLog::class);
    }

    /**
     * Get the downtime incidents for the URL.
     */
    public function downtimeIncidents(): HasMany
    {
        return $this->hasMany(WebsiteDowntimeIncident::class);
    }

    /**
     * Get the latest monitoring log for a specific check type.
     */
    public function latestLogForType(string $checkType): ?WebsiteMonitoringLog
    {
        return $this->monitoringLogs()
            ->where('check_type', $checkType)
            ->latest('checked_at')
            ->first();
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->current_status) {
            'up' => 'green',
            'down' => 'red',
            'warning' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get the status text for display.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->current_status) {
            'up' => 'Online',
            'down' => 'Offline',
            'warning' => 'Warning',
            default => 'Unknown',
        };
    }

    /**
     * Get enabled monitoring types.
     */
    public function getEnabledMonitoringTypesAttribute(): array
    {
        $types = [];

        if ($this->monitor_status) {
            $types[] = 'status';
        }

        if ($this->monitor_domain) {
            $types[] = 'domain';
        }

        if ($this->monitor_ssl) {
            $types[] = 'ssl';
        }

        return $types;
    }

    /**
     * Update status and handle status changes.
     */
    public function updateStatus(string $newStatus, ?string $errorMessage = null): void
    {
        $oldStatus = $this->current_status;

        $this->update([
            'current_status' => $newStatus,
            'last_checked_at' => now(),
            'last_error' => $errorMessage,
            'last_status_change' => $oldStatus !== $newStatus ? now() : $this->last_status_change,
        ]);

        // Handle downtime incidents
        if ($oldStatus !== $newStatus) {
            $this->handleStatusChange($oldStatus, $newStatus, $errorMessage);
        }

        // Update website overall status
        $this->website->updateOverallStatus();
    }

    /**
     * Handle status changes and downtime incidents.
     */
    private function handleStatusChange(string $oldStatus, string $newStatus, ?string $errorMessage): void
    {
        // If going from up/warning to down, start a new incident
        if (in_array($oldStatus, ['up', 'warning', 'unknown']) && $newStatus === 'down') {
            WebsiteDowntimeIncident::create([
                'website_url_id' => $this->id,
                'started_at' => now(),
                'cause' => 'status',
                'error_message' => $errorMessage,
            ]);
        }

        // If going from down to up/warning, end the current incident
        if ($oldStatus === 'down' && in_array($newStatus, ['up', 'warning'])) {
            $activeIncident = $this->downtimeIncidents()
                ->whereNull('ended_at')
                ->orderBy('started_at', 'desc')
                ->first();

            if ($activeIncident) {
                $endedAt = now();
                $activeIncident->update([
                    'ended_at' => $endedAt,
                    'duration_minutes' => (int) $activeIncident->started_at->diffInMinutes($endedAt),
                ]);
            }
        }
    }

    /**
     * Get uptime percentage for the last 30 days.
     */
    public function getUptimePercentageAttribute(): float
    {
        $thirtyDaysAgo = now()->subDays(30);

        $totalChecks = $this->monitoringLogs()
            ->where('check_type', 'status')
            ->where('checked_at', '>=', $thirtyDaysAgo)
            ->count();

        if ($totalChecks === 0) {
            return 0;
        }

        $upChecks = $this->monitoringLogs()
            ->where('check_type', 'status')
            ->where('status', 'up')
            ->where('checked_at', '>=', $thirtyDaysAgo)
            ->count();

        return round(($upChecks / $totalChecks) * 100, 2);
    }
}
