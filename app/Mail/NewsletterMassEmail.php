<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMassEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $objet;
    public string $message;
    public string $expediteurNom;
    public string $expediteurEmail;

    public function __construct(string $objet, string $message, string $expediteurNom, string $expediteurEmail)
    {
        $this->objet = $objet;
        $this->message = $message;
        $this->expediteurNom = $expediteurNom;
        $this->expediteurEmail = $expediteurEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->objet,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-masse',
        );
    }
}
