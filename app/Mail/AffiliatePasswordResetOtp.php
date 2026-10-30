<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AffiliatePasswordResetOtp extends Mailable
{
    public function __construct(public string $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Code de réinitialisation du mot de passe affilié — PaxEvent',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-password-reset-otp',
        );
    }
}
