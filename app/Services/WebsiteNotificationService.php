<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebsiteUrl;
use App\Models\WebsiteDowntimeIncident;
use App\Mail\WebsiteDownNotification;
use App\Mail\WebsiteUpNotification;
use App\Mail\WebsiteStillDownNotification;
use App\Mail\DomainExpiryWarningNotification;
use App\Mail\SslExpiryWarningNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebsiteNotificationService
{
    /**
     * Send downtime notification for a website.
     */
    public function sendDowntimeNotification(WebsiteDowntimeIncident $incident): void
    {
        $websiteUrl = $incident->websiteUrl;
        $website = $websiteUrl->website;

        if (empty($website->notification_emails)) {
            return;
        }

        try {
            foreach ($website->notification_emails as $email) {
                Mail::to($email)->send(new WebsiteDownNotification($incident));
            }

            $incident->update([
                'notification_sent' => true,
                'last_notification_sent_at' => now(),
                'notification_count' => 1,
            ]);

            Log::info("Downtime notification sent for website: {$website->name} ({$websiteUrl->url})");

        } catch (\Exception $e) {
            Log::error("Failed to send downtime notification for website: {$website->name}", [
                'error' => $e->getMessage(),
                'website_id' => $website->id,
                'url_id' => $websiteUrl->id,
            ]);
        }
    }

    /**
     * Send recovery notification for a website.
     */
    public function sendRecoveryNotification(WebsiteDowntimeIncident $incident): void
    {
        $websiteUrl = $incident->websiteUrl;
        $website = $websiteUrl->website;

        if (empty($website->notification_emails)) {
            return;
        }

        try {
            foreach ($website->notification_emails as $email) {
                Mail::to($email)->send(new WebsiteUpNotification($incident));
            }

            $incident->update(['recovery_notification_sent' => true]);

            Log::info("Recovery notification sent for website: {$website->name} ({$websiteUrl->url})");

        } catch (\Exception $e) {
            Log::error("Failed to send recovery notification for website: {$website->name}", [
                'error' => $e->getMessage(),
                'website_id' => $website->id,
                'url_id' => $websiteUrl->id,
            ]);
        }
    }

    /**
     * Check for new downtime incidents and send notifications.
     */
    public function checkAndSendDowntimeNotifications(): void
    {
        $newIncidents = WebsiteDowntimeIncident::where('notification_sent', false)
            ->whereNull('ended_at')
            ->with(['websiteUrl.website'])
            ->get();

        foreach ($newIncidents as $incident) {
            $this->sendDowntimeNotification($incident);
        }
    }

    /**
     * Check for resolved incidents and send recovery notifications.
     */
    public function checkAndSendRecoveryNotifications(): void
    {
        $resolvedIncidents = WebsiteDowntimeIncident::where('recovery_notification_sent', false)
            ->whereNotNull('ended_at')
            ->with(['websiteUrl.website'])
            ->get();

        foreach ($resolvedIncidents as $incident) {
            $this->sendRecoveryNotification($incident);
        }
    }

    /**
     * Send "still down" notification for ongoing incidents (every 15 minutes).
     */
    public function sendStillDownNotification(WebsiteDowntimeIncident $incident): void
    {
        $websiteUrl = $incident->websiteUrl;
        $website = $websiteUrl->website;

        if (empty($website->notification_emails)) {
            return;
        }

        try {
            foreach ($website->notification_emails as $email) {
                Mail::to($email)->send(new WebsiteStillDownNotification($incident));
            }

            $incident->update([
                'last_notification_sent_at' => now(),
                'notification_count' => $incident->notification_count + 1,
            ]);

            Log::info("Still down notification sent for website: {$website->name} ({$websiteUrl->url}) - Count: {$incident->notification_count}");

        } catch (\Exception $e) {
            Log::error("Failed to send still down notification for website: {$website->name}", [
                'error' => $e->getMessage(),
                'website_id' => $website->id,
                'url_id' => $websiteUrl->id,
            ]);
        }
    }

    /**
     * Check for ongoing incidents that need "still down" notifications (every 15 minutes).
     */
    public function checkAndSendStillDownNotifications(): void
    {
        $fifteenMinutesAgo = now()->subMinutes(15);

        $ongoingIncidents = WebsiteDowntimeIncident::whereNull('ended_at')
            ->where('notification_sent', true)
            ->where(function ($query) use ($fifteenMinutesAgo) {
                $query->whereNull('last_notification_sent_at')
                    ->orWhere('last_notification_sent_at', '<=', $fifteenMinutesAgo);
            })
            ->with(['websiteUrl.website'])
            ->get();

        foreach ($ongoingIncidents as $incident) {
            // Only send if the incident has been ongoing for at least 15 minutes
            if ($incident->started_at->diffInMinutes(now()) >= 15) {
                $this->sendStillDownNotification($incident);
            }
        }
    }

    /**
     * Send domain expiry warning notification.
     */
    public function sendDomainExpiryWarning(WebsiteUrl $websiteUrl, int $daysUntilExpiry): void
    {
        $website = $websiteUrl->website;

        if (empty($website->notification_emails)) {
            return;
        }

        try {
            foreach ($website->notification_emails as $email) {
                Mail::to($email)->send(new DomainExpiryWarningNotification($websiteUrl, $daysUntilExpiry));
            }

            $websiteUrl->update([
                'domain_warning_notification_sent_at' => now(),
                'domain_warning_notification_count' => $websiteUrl->domain_warning_notification_count + 1,
            ]);

            Log::info("Domain expiry warning sent for website: {$website->name} ({$websiteUrl->url}) - {$daysUntilExpiry} days left");

        } catch (\Exception $e) {
            Log::error("Failed to send domain expiry warning for website: {$website->name}", [
                'error' => $e->getMessage(),
                'website_id' => $website->id,
                'url_id' => $websiteUrl->id,
            ]);
        }
    }

    /**
     * Send SSL expiry warning notification.
     */
    public function sendSslExpiryWarning(WebsiteUrl $websiteUrl, int $daysUntilExpiry): void
    {
        $website = $websiteUrl->website;

        if (empty($website->notification_emails)) {
            return;
        }

        try {
            foreach ($website->notification_emails as $email) {
                Mail::to($email)->send(new SslExpiryWarningNotification($websiteUrl, $daysUntilExpiry));
            }

            $websiteUrl->update([
                'ssl_warning_notification_sent_at' => now(),
                'ssl_warning_notification_count' => $websiteUrl->ssl_warning_notification_count + 1,
            ]);

            Log::info("SSL expiry warning sent for website: {$website->name} ({$websiteUrl->url}) - {$daysUntilExpiry} days left");

        } catch (\Exception $e) {
            Log::error("Failed to send SSL expiry warning for website: {$website->name}", [
                'error' => $e->getMessage(),
                'website_id' => $website->id,
                'url_id' => $websiteUrl->id,
            ]);
        }
    }

    /**
     * Process all pending notifications.
     */
    public function processAllNotifications(): void
    {
        $this->checkAndSendDowntimeNotifications();
        $this->checkAndSendRecoveryNotifications();
        $this->checkAndSendStillDownNotifications();
    }
}
