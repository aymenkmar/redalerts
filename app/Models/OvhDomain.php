<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class OvhDomain extends Model
{
    use HasFactory;

    protected $table = 'ovh_domains';

    protected $fillable = [
        'service_name',
        'display_name',
        'state',
        'expiration_date',
        'engagement_date',
        'renewal_type',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'engagement_date' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Check if service is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        // Check if the expiration date is within 30 days from now
        $daysUntilExpiration = now()->diffInDays($this->expiration_date, false);

        return $daysUntilExpiration <= 30 && $daysUntilExpiration >= 0;
    }

    /**
     * Check if service is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->isPast();
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }

        return now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Scope for services expiring soon
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('expiration_date', '<=', now()->addDays(30))
                    ->where('expiration_date', '>', now());
    }

    /**
     * Scope for expired services
     */
    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }
}
