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

    public \Illuminate\Support\Carbon $lienExpireLe;

    public function __construct(
        public AgentVente $agent,
        public string $motDePasse,
    ) {
        $finEvenement = $agent->evenement?->finVente();
        $this->lienApp = AppReleaseController::lienPourAgent('vente', $agent->id, $finEvenement);
        $this->lienExpireLe = AppReleaseController::expirationLienAgent($finEvenement);
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
