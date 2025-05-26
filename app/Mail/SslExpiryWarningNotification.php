<?php

namespace App\Mail;

use App\Models\WebsiteUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SslExpiryWarningNotification extends Mailable
{
    use Queueable, SerializesModels;

    public WebsiteUrl $websiteUrl;
    public int $daysUntilExpiry;

    /**
     * Create a new message instance.
     */
    public function __construct(WebsiteUrl $websiteUrl, int $daysUntilExpiry)
    {
        $this->websiteUrl = $websiteUrl;
        $this->daysUntilExpiry = $daysUntilExpiry;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $websiteName = $this->websiteUrl->website->name;
        $days = $this->daysUntilExpiry;

        return new Envelope(
            subject: "🔒 SSL Certificate Expiry Warning: {$websiteName} ({$days} days left)",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ssl-expiry-warning',
            with: [
                'websiteUrl' => $this->websiteUrl,
                'website' => $this->websiteUrl->website,
                'daysUntilExpiry' => $this->daysUntilExpiry,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
