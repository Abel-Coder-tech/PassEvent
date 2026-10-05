<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// Volontairement sans ShouldQueue : l'utilisateur attend ce code sur l'écran,
// l'envoi est donc direct. Il ne doit dépendre d'aucun worker sur le serveur.
class OtpEmail extends Mailable
{
    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre code de vérification — PaxEvent',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}
