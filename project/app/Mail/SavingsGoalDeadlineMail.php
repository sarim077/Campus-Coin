<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SavingsGoalDeadlineMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly float $goalAmount,
        public readonly float $savedAmount,
        public readonly float $shortfall,
        public readonly string $dueDate,
        public readonly string $notificationType,
        public readonly string $currencySymbol,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->notificationType === 'achieved'
            ? 'Congratulations on reaching your Campus Coin savings goal!'
            : 'Your Campus Coin savings goal is due tomorrow';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.savings-goal-deadline');
    }
}
