<?php

namespace App\Mail;

use App\Models\WebsiteDowntimeIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteUpNotification extends Mailable
{
    use Queueable, SerializesModels;

    public WebsiteDowntimeIncident $incident;

    /**
     * Create a new message instance.
     */
    public function __construct(WebsiteDowntimeIncident $incident)
    {
        $this->incident = $incident;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $websiteName = $this->incident->websiteUrl->website->name;
        
        return new Envelope(
            subject: "✅ Website Recovered: {$websiteName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.website-up',
            with: [
                'incident' => $this->incident,
                'website' => $this->incident->websiteUrl->website,
                'websiteUrl' => $this->incident->websiteUrl,
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
