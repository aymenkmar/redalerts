<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteDowntimeIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_url_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'cause',
        'error_message',
        'notification_sent',
        'recovery_notification_sent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'notification_sent' => 'boolean',
        'recovery_notification_sent' => 'boolean',
    ];

    /**
     * Get the website URL that owns the incident.
     */
    public function websiteUrl(): BelongsTo
    {
        return $this->belongsTo(WebsiteUrl::class);
    }

    /**
     * Get the website through the URL.
     */
    public function website()
    {
        return $this->websiteUrl->website();
    }

    /**
     * Check if the incident is still active.
     */
    public function getIsActiveAttribute(): bool
    {
        return is_null($this->ended_at);
    }

    /**
     * Get the current duration of the incident.
     */
    public function getCurrentDurationAttribute(): int
    {
        if ($this->ended_at) {
            return $this->duration_minutes;
        }

        return $this->started_at->diffInMinutes(now());
    }

    /**
     * Get formatted duration for display.
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->current_duration;

        if ($minutes < 60) {
            return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $result = $hours . ' hour' . ($hours !== 1 ? 's' : '');

        if ($remainingMinutes > 0) {
            $result .= ' ' . $remainingMinutes . ' minute' . ($remainingMinutes !== 1 ? 's' : '');
        }

        return $result;
    }

    /**
     * Scope to get active incidents.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope to get resolved incidents.
     */
    public function scopeResolved($query)
    {
        return $query->whereNotNull('ended_at');
    }
}
