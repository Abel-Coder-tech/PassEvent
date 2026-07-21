<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class PaymentErrorAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomAcheteur;
    public string $titreEvenement;
    public string $transactionId;

    public function __construct(string $nomAcheteur, string $titreEvenement, string $transactionId = '')
    {
        $this->nomAcheteur = $nomAcheteur;
        $this->titreEvenement = $titreEvenement;
        $this->transactionId = $transactionId;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Incident technique sur votre tentative de paiement — PaxEvent',
            replyTo: [new Address('contact@paxevent.com', 'PaxEvent')],
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'Precedence' => 'bulk',
                'X-Mailer' => 'PaxEvent Billetterie',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-error',
            with: [
                'nomAcheteur' => $this->nomAcheteur,
                'titreEvenement' => $this->titreEvenement,
                'transactionId' => $this->transactionId,
            ],
        );
    }
}
