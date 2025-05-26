<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'notification_emails',
        'is_active',
        'overall_status',
        'last_checked_at',
    ];

    protected $casts = [
        'notification_emails' => 'array',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Get the URLs for the website.
     */
    public function urls(): HasMany
    {
        return $this->hasMany(WebsiteUrl::class);
    }

    /**
     * Get all monitoring logs for this website.
     */
    public function monitoringLogs(): HasManyThrough
    {
        return $this->hasManyThrough(WebsiteMonitoringLog::class, WebsiteUrl::class);
    }

    /**
     * Get all downtime incidents for this website.
     */
    public function downtimeIncidents(): HasManyThrough
    {
        return $this->hasManyThrough(WebsiteDowntimeIncident::class, WebsiteUrl::class);
    }

    /**
     * Get the overall status based on all URLs.
     */
    public function updateOverallStatus(): void
    {
        $statuses = $this->urls->pluck('current_status')->unique();

        if ($statuses->contains('down')) {
            $overallStatus = 'down';
        } elseif ($statuses->contains('warning')) {
            $overallStatus = 'warning';
        } elseif ($statuses->contains('up')) {
            $overallStatus = 'up';
        } else {
            $overallStatus = 'unknown';
        }

        $this->update([
            'overall_status' => $overallStatus,
            'last_checked_at' => now(),
        ]);
    }

    /**
     * Get the status counts for this website.
     */
    public function getStatusCountsAttribute(): array
    {
        $counts = $this->urls->groupBy('current_status')->map->count();

        return [
            'up' => $counts->get('up', 0),
            'down' => $counts->get('down', 0),
            'warning' => $counts->get('warning', 0),
            'unknown' => $counts->get('unknown', 0),
        ];
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->overall_status) {
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
        return match ($this->overall_status) {
            'up' => 'All Online',
            'down' => 'Issues Detected',
            'warning' => 'Warnings',
            default => 'Unknown',
        };
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

    /**
     * Get current downtime duration if any URL is down.
     */
    public function getCurrentDowntimeDuration(): ?int
    {
        $activeIncident = $this->downtimeIncidents()
            ->whereNull('ended_at')
            ->orderBy('started_at', 'desc')
            ->first();

        if (!$activeIncident) {
            return null;
        }

        return $activeIncident->started_at->diffInMinutes(now());
    }
}
