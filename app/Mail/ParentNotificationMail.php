<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $studentName,
        public readonly string $title,
        public readonly string $message,
        public readonly string $type = 'general',
        public readonly array $data = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[TunAcademy] ' . $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.parent-notification',
        );
    }
}
