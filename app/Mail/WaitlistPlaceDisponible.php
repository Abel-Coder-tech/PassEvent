<?php

namespace App\Mail;

use App\Models\Evenement;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class WaitlistPlaceDisponible extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public Ticket $ticket;

    public Evenement $evenement;

    public string $paiementUrl;

    public function __construct(Ticket $ticket, Evenement $evenement)
    {
        $this->ticket = $ticket;
        $this->evenement = $evenement;
        $this->paiementUrl = URL::signedRoute('paiement.acces-lien', ['ticket' => $ticket->id], now()->addMinutes(60));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Une place s'est libérée pour {$this->evenement->titre} - PaxEvent",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.waitlist-place-disponible',
        );
    }
}
