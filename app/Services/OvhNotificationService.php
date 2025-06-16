<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\OvhVps;
use App\Models\OvhDedicatedServer;
use App\Models\OvhDomain;
use Illuminate\Support\Facades\Log;

class OvhNotificationService
{
    /**
     * Create OVH VPS expiration notification.
     */
    public function createVpsExpirationNotification(OvhVps $vps, int $daysUntilExpiry): void
    {
        $users = User::all();
        
        $priority = $this->getPriorityByDays($daysUntilExpiry);
        $color = $this->getColorByDays($daysUntilExpiry);
        $isExpired = $daysUntilExpiry < 0;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $isExpired ? 'ovh_vps_expired' : 'ovh_vps_expiring',
                'title' => $isExpired ? "OVH VPS Expired: {$vps->display_name}" : "OVH VPS Expiring: {$vps->display_name}",
                'message' => $isExpired 
                    ? "Your OVH VPS {$vps->display_name} ({$vps->service_name}) has expired."
                    : "Your OVH VPS {$vps->display_name} ({$vps->service_name}) will expire in {$daysUntilExpiry} days.",
                'data' => [
                    'service_name' => $vps->service_name,
                    'display_name' => $vps->display_name,
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiration_date' => $vps->expiration_date?->toISOString(),
                    'renewal_type' => $vps->renewal_type,
                    'service_type' => 'vps',
                ],
                'icon' => $isExpired ? 'x-circle' : 'exclamation-triangle',
                'color' => $color,
                'priority' => $priority,
            ]);
        }

        Log::info("OVH VPS expiration notification created for: {$vps->display_name} ({$daysUntilExpiry} days)");
    }

    /**
     * Create OVH Dedicated Server expiration notification.
     */
    public function createDedicatedServerExpirationNotification(OvhDedicatedServer $server, int $daysUntilExpiry): void
    {
        $users = User::all();
        
        $priority = $this->getPriorityByDays($daysUntilExpiry);
        $color = $this->getColorByDays($daysUntilExpiry);
        $isExpired = $daysUntilExpiry < 0;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $isExpired ? 'ovh_server_expired' : 'ovh_server_expiring',
                'title' => $isExpired ? "OVH Server Expired: {$server->display_name}" : "OVH Server Expiring: {$server->display_name}",
                'message' => $isExpired 
                    ? "Your OVH dedicated server {$server->display_name} ({$server->service_name}) has expired."
                    : "Your OVH dedicated server {$server->display_name} ({$server->service_name}) will expire in {$daysUntilExpiry} days.",
                'data' => [
                    'service_name' => $server->service_name,
                    'display_name' => $server->display_name,
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiration_date' => $server->expiration_date?->toISOString(),
                    'renewal_type' => $server->renewal_type,
                    'service_type' => 'dedicated_server',
                ],
                'icon' => $isExpired ? 'x-circle' : 'exclamation-triangle',
                'color' => $color,
                'priority' => $priority,
            ]);
        }

        Log::info("OVH Dedicated Server expiration notification created for: {$server->display_name} ({$daysUntilExpiry} days)");
    }

    /**
     * Create OVH Domain expiration notification.
     */
    public function createDomainExpirationNotification(OvhDomain $domain, int $daysUntilExpiry): void
    {
        $users = User::all();
        
        $priority = $this->getPriorityByDays($daysUntilExpiry);
        $color = $this->getColorByDays($daysUntilExpiry);
        $isExpired = $daysUntilExpiry < 0;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $isExpired ? 'ovh_domain_expired' : 'ovh_domain_expiring',
                'title' => $isExpired ? "OVH Domain Expired: {$domain->display_name}" : "OVH Domain Expiring: {$domain->display_name}",
                'message' => $isExpired 
                    ? "Your OVH domain {$domain->display_name} has expired."
                    : "Your OVH domain {$domain->display_name} will expire in {$daysUntilExpiry} days.",
                'data' => [
                    'service_name' => $domain->service_name,
                    'display_name' => $domain->display_name,
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiration_date' => $domain->expiration_date?->toISOString(),
                    'renewal_type' => $domain->renewal_type,
                    'service_type' => 'domain',
                ],
                'icon' => $isExpired ? 'x-circle' : 'globe-alt',
                'color' => $color,
                'priority' => $priority,
            ]);
        }

        Log::info("OVH Domain expiration notification created for: {$domain->display_name} ({$daysUntilExpiry} days)");
    }

    /**
     * Check and create notifications for all expiring OVH services.
     */
    public function checkAndNotifyExpiringServices(): array
    {
        $results = [
            'vps_notifications' => 0,
            'server_notifications' => 0,
            'domain_notifications' => 0,
        ];

        // Check VPS services
        $expiringVps = OvhVps::expiringSoon()->get();
        $expiredVps = OvhVps::expired()->get();
        
        foreach ($expiringVps->merge($expiredVps) as $vps) {
            $daysUntilExpiry = $vps->getDaysUntilExpiration();
            if ($this->shouldNotify($daysUntilExpiry)) {
                $this->createVpsExpirationNotification($vps, $daysUntilExpiry);
                $results['vps_notifications']++;
            }
        }

        // Check Dedicated Server services
        $expiringServers = OvhDedicatedServer::expiringSoon()->get();
        $expiredServers = OvhDedicatedServer::expired()->get();
        
        foreach ($expiringServers->merge($expiredServers) as $server) {
            $daysUntilExpiry = $server->getDaysUntilExpiration();
            if ($this->shouldNotify($daysUntilExpiry)) {
                $this->createDedicatedServerExpirationNotification($server, $daysUntilExpiry);
                $results['server_notifications']++;
            }
        }

        // Check Domain services
        $expiringDomains = OvhDomain::expiringSoon()->get();
        $expiredDomains = OvhDomain::expired()->get();
        
        foreach ($expiringDomains->merge($expiredDomains) as $domain) {
            $daysUntilExpiry = $domain->getDaysUntilExpiration();
            if ($this->shouldNotify($daysUntilExpiry)) {
                $this->createDomainExpirationNotification($domain, $daysUntilExpiry);
                $results['domain_notifications']++;
            }
        }

        return $results;
    }

    /**
     * Determine if we should send a notification based on days until expiry.
     */
    private function shouldNotify(int $daysUntilExpiry): bool
    {
        // Notify at 30, 15, 7, 3, 1 days before expiration and daily after expiration
        if ($daysUntilExpiry < 0) {
            return true; // Daily notifications for expired services
        }
        
        return in_array($daysUntilExpiry, [30, 15, 7, 3, 1]);
    }

    /**
     * Get notification priority based on days until expiry.
     */
    private function getPriorityByDays(int $daysUntilExpiry): string
    {
        if ($daysUntilExpiry < 0) return 'urgent';
        if ($daysUntilExpiry <= 3) return 'urgent';
        if ($daysUntilExpiry <= 7) return 'high';
        if ($daysUntilExpiry <= 15) return 'normal';
        return 'low';
    }

    /**
     * Get notification color based on days until expiry.
     */
    private function getColorByDays(int $daysUntilExpiry): string
    {
        if ($daysUntilExpiry < 0) return 'red';
        if ($daysUntilExpiry <= 7) return 'red';
        if ($daysUntilExpiry <= 15) return 'yellow';
        return 'orange';
    }
}
