<?php

namespace App\Mail;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduleNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $student,
        public readonly Schedule $schedule
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[TunAcademy] Đến giờ tự học: ' . $this->schedule->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.schedule-notification',
        );
    }
}
