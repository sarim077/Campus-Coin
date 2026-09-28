<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $adminName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Campus Coin password was reset');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-password-reset');
    }
}
