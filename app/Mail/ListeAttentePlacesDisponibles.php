<?php

namespace App\Mail;

use App\Models\Evenement;
use App\Models\EventWaitlist;
use App\Models\Tarif;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Envoyé à tous les inscrits d'un tarif quand l'organisateur ajoute des
// places : premier arrivé, premier servi.
class ListeAttentePlacesDisponibles extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $lienEvenement;

    public function __construct(public EventWaitlist $demande, public Evenement $evenement, public Tarif $tarif)
    {
        $this->lienEvenement = route('evenements.public.show', $evenement).'#reserver';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Des places sont disponibles pour {$this->evenement->titre} - PaxEvent");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.liste-attente-places-disponibles');
    }
}
