<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RetraitConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomOrganisateur;
    public string $reseau;
    public float $montant;

    public function __construct(string $nomOrganisateur, string $reseau, float $montant)
    {
        $this->nomOrganisateur = $nomOrganisateur;
        $this->reseau = $reseau;
        $this->montant = $montant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Votre retrait a été effectué — Merci ! PaxEvent",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.retrait-confirmed',
            with: [
                'nomOrganisateur' => $this->nomOrganisateur,
                'reseau' => $this->reseau,
                'montant' => $this->montant,
            ],
        );
    }
}
