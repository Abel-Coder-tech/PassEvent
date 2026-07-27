<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RetraitRejected extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomOrganisateur;
    public string $reseau;
    public float $montant;
    public string $raisons;

    public function __construct(string $nomOrganisateur, string $reseau, float $montant, string $raisons)
    {
        $this->nomOrganisateur = $nomOrganisateur;
        $this->reseau = $reseau;
        $this->montant = $montant;
        $this->raisons = $raisons;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Votre demande de retrait a été rejetée — PaxEvent",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.retrait-rejected',
            with: [
                'nomOrganisateur' => $this->nomOrganisateur,
                'reseau' => $this->reseau,
                'montant' => $this->montant,
                'raisons' => $this->raisons,
            ],
        );
    }
}
