<?php

namespace App\Mail;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WithdrawalRequestedMail extends Mailable
{
    use Queueable;

    public Withdrawal $withdrawal;

    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PaxEvent] Nouvelle demande de retrait - ' . $this->withdrawal->user->nom,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.withdrawal-requested',
        );
    }
}
