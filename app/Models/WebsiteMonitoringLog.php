<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteMonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_url_id',
        'check_type',
        'status',
        'response_time',
        'status_code',
        'error_message',
        'additional_data',
        'checked_at',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'checked_at' => 'datetime',
    ];

    /**
     * Get the website URL that owns the log.
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
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'up' => 'green',
            'down' => 'red',
            'warning' => 'yellow',
            'error' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the status text for display.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'up' => 'Online',
            'down' => 'Offline',
            'warning' => 'Warning',
            'error' => 'Error',
            default => 'Unknown',
        };
    }

    /**
     * Get the check type display name.
     */
    public function getCheckTypeDisplayAttribute(): string
    {
        return match ($this->check_type) {
            'status' => 'Status Check',
            'domain' => 'Domain Validation',
            'ssl' => 'SSL Certificate',
            default => ucfirst($this->check_type),
        };
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checked_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by check type.
     */
    public function scopeCheckType($query, $checkType)
    {
        return $query->where('check_type', $checkType);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get response time with appropriate formatting.
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        if (!$this->response_time) {
            return '-';
        }

        if ($this->response_time < 1000) {
            return $this->response_time . 'ms';
        }

        return round($this->response_time / 1000, 2) . 's';
    }
}
