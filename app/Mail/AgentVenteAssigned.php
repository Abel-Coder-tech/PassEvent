<?php

namespace App\Mail;

use App\Http\Controllers\AppReleaseController;
use App\Models\AgentVente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentVenteAssigned extends Mailable
{
    use Queueable, SerializesModels;

    /// Lien signé (7 jours) vers l'APK PaxAgent, null si aucune version publiée.
    public ?string $lienApp;

    public function __construct(
        public AgentVente $agent,
        public string $motDePasse,
    ) {
        $this->lienApp = AppReleaseController::lienPourAgent('vente', $agent->id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous avez été ajouté comme agent de vente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agent-vente-assigned',
        );
    }
}
