<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebsiteUrl;
use App\Models\WebsiteDowntimeIncident;
use App\Mail\WebsiteDownNotification;
use App\Mail\WebsiteUpNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

            $incident->update(['notification_sent' => true]);

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
     * Process all pending notifications.
     */
    public function processAllNotifications(): void
    {
        $this->checkAndSendDowntimeNotifications();
        $this->checkAndSendRecoveryNotifications();
    }
}
