<?php

namespace App\Mail;

use App\Models\Evenement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Envoyé aux visiteurs inscrits via « Me prévenir » quand la billetterie ouvre.
class BilletterieOuverte extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $lienEvenement;

    public function __construct(public Evenement $evenement)
    {
        $this->lienEvenement = route('evenements.public.show', $evenement).'#reserver';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "La billetterie est ouverte : {$this->evenement->titre} - PaxEvent");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.billetterie-ouverte');
    }
}
