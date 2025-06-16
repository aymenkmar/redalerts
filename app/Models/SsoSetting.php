<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoSetting extends Model
{
    protected $fillable = [
        'type',
        'value',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active allowed emails
     */
    public static function getAllowedEmails()
    {
        return self::where('type', 'email')
            ->where('is_active', true)
            ->pluck('value')
            ->toArray();
    }

    /**
     * Get all active allowed domains
     */
    public static function getAllowedDomains()
    {
        return self::where('type', 'domain')
            ->where('is_active', true)
            ->pluck('value')
            ->toArray();
    }

    /**
     * Check if an email is allowed for SSO
     */
    public static function isEmailAllowed($email)
    {
        // Check exact email match
        $emailAllowed = self::where('type', 'email')
            ->where('value', $email)
            ->where('is_active', true)
            ->exists();

        if ($emailAllowed) {
            return true;
        }

        // Check domain match
        $domain = substr(strrchr($email, "@"), 1);
        return self::where('type', 'domain')
            ->where('value', $domain)
            ->where('is_active', true)
            ->exists();
    }
}
