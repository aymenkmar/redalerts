<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteUrl;
use App\Models\WebsiteDowntimeIncident;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a website down notification.
     */
    public function createWebsiteDownNotification(WebsiteDowntimeIncident $incident): void
    {
        $websiteUrl = $incident->websiteUrl;
        $website = $websiteUrl->website;

        // Get all users (in a multi-user system, you might want to filter by website ownership)
        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'website_down',
                'title' => "Website Down: {$website->name}",
                'message' => "Your website {$website->name} ({$websiteUrl->url}) is currently offline and not responding to monitoring checks.",
                'data' => [
                    'website_name' => $website->name,
                    'url' => $websiteUrl->url,
                    'error_message' => $incident->error_message,
                    'started_at' => $incident->started_at->toISOString(),
                ],
                'icon' => 'exclamation-triangle',
                'color' => 'red',
                'priority' => 'high',
                'website_id' => $website->id,
                'website_url_id' => $websiteUrl->id,
            ]);
        }

        Log::info("Website down notification created for: {$website->name}");
    }

    /**
     * Create a website up notification.
     */
    public function createWebsiteUpNotification(WebsiteDowntimeIncident $incident): void
    {
        $websiteUrl = $incident->websiteUrl;
        $website = $websiteUrl->website;

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'website_up',
                'title' => "Website Recovered: {$website->name}",
                'message' => "Your website {$website->name} ({$websiteUrl->url}) is back online after {$incident->formatted_duration} of downtime.",
                'data' => [
                    'website_name' => $website->name,
                    'url' => $websiteUrl->url,
                    'downtime_duration' => $incident->formatted_duration,
                    'started_at' => $incident->started_at->toISOString(),
                    'ended_at' => $incident->ended_at->toISOString(),
                ],
                'icon' => 'check-circle',
                'color' => 'green',
                'priority' => 'normal',
                'website_id' => $website->id,
                'website_url_id' => $websiteUrl->id,
            ]);
        }

        Log::info("Website up notification created for: {$website->name}");
    }

    /**
     * Create a website still down notification.
     */
    public function createWebsiteStillDownNotification(WebsiteDowntimeIncident $incident): void
    {
        $websiteUrl = $incident->websiteUrl;
        $website = $websiteUrl->website;

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'website_still_down',
                'title' => "Website Still Down: {$website->name}",
                'message' => "Your website {$website->name} has been offline for {$incident->formatted_duration}. This is notification #{$incident->notification_count}.",
                'data' => [
                    'website_name' => $website->name,
                    'url' => $websiteUrl->url,
                    'downtime_duration' => $incident->formatted_duration,
                    'notification_count' => $incident->notification_count,
                    'error_message' => $incident->error_message,
                ],
                'icon' => 'exclamation-triangle',
                'color' => 'red',
                'priority' => 'high',
                'website_id' => $website->id,
                'website_url_id' => $websiteUrl->id,
            ]);
        }

        Log::info("Website still down notification created for: {$website->name} (#{$incident->notification_count})");
    }

    /**
     * Create an SSL expiry warning notification.
     */
    public function createSslExpiryNotification(WebsiteUrl $websiteUrl, int $daysUntilExpiry): void
    {
        $website = $websiteUrl->website;

        // Get all users
        $users = User::all();

        $priority = $daysUntilExpiry <= 7 ? 'urgent' : ($daysUntilExpiry <= 15 ? 'high' : 'normal');
        $color = $daysUntilExpiry <= 7 ? 'red' : 'yellow';

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'ssl_expiry',
                'title' => "SSL Certificate Expiry Warning: {$website->name}",
                'message' => "The SSL certificate for {$website->name} ({$websiteUrl->url}) will expire in {$daysUntilExpiry} days.",
                'data' => [
                    'website_name' => $website->name,
                    'url' => $websiteUrl->url,
                    'days_until_expiry' => $daysUntilExpiry,
                    'domain' => parse_url($websiteUrl->url, PHP_URL_HOST),
                ],
                'icon' => 'shield-exclamation',
                'color' => $color,
                'priority' => $priority,
                'website_id' => $website->id,
                'website_url_id' => $websiteUrl->id,
            ]);
        }

        Log::info("SSL expiry notification created for: {$website->name} ({$daysUntilExpiry} days)");
    }

    /**
     * Create a domain expiry warning notification.
     */
    public function createDomainExpiryNotification(WebsiteUrl $websiteUrl, int $daysUntilExpiry): void
    {
        $website = $websiteUrl->website;

        // Get all users
        $users = User::all();

        $priority = $daysUntilExpiry <= 7 ? 'urgent' : ($daysUntilExpiry <= 15 ? 'high' : 'normal');
        $color = $daysUntilExpiry <= 7 ? 'red' : 'yellow';

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'domain_expiry',
                'title' => "Domain Expiry Warning: {$website->name}",
                'message' => "The domain for {$website->name} ({$websiteUrl->url}) will expire in {$daysUntilExpiry} days.",
                'data' => [
                    'website_name' => $website->name,
                    'url' => $websiteUrl->url,
                    'days_until_expiry' => $daysUntilExpiry,
                    'domain' => parse_url($websiteUrl->url, PHP_URL_HOST),
                ],
                'icon' => 'globe-alt',
                'color' => $color,
                'priority' => $priority,
                'website_id' => $website->id,
                'website_url_id' => $websiteUrl->id,
            ]);
        }

        Log::info("Domain expiry notification created for: {$website->name} ({$daysUntilExpiry} days)");
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentNotifications(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::forUser($userId)
            ->with(['website', 'websiteUrl'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::forUser($userId)->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)->unread()->update(['read_at' => now()]);
    }

    /**
     * Clean up old notifications (keep last 30 days).
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }
}
