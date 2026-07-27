<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RetraitRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $nomOrganisateur;
    public $emailOrganisateur;
    public $reseau;
    public $montant;
    public $nomBeneficiaire;
    public $mobile;

    public function __construct(string $nomOrganisateur, string $emailOrganisateur, string $reseau, float $montant, string $nomBeneficiaire, string $mobile)
    {
        $this->nomOrganisateur = $nomOrganisateur;
        $this->emailOrganisateur = $emailOrganisateur;
        $this->reseau = $reseau;
        $this->montant = $montant;
        $this->nomBeneficiaire = $nomBeneficiaire;
        $this->mobile = $mobile;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouvelle demande de retrait — {$this->nomOrganisateur} — PaxEvent",
            replyTo: [new Address($this->emailOrganisateur, $this->nomOrganisateur)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.retrait-requested',
            with: [
                'nomOrganisateur' => $this->nomOrganisateur,
                'emailOrganisateur' => $this->emailOrganisateur,
                'reseau' => $this->reseau,
                'montant' => $this->montant,
                'nomBeneficiaire' => $this->nomBeneficiaire,
                'mobile' => $this->mobile,
            ],
        );
    }
}
