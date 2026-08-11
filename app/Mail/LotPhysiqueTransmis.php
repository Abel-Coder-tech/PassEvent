<?php

namespace App\Mail;

use App\Models\LotPhysique;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

class LotPhysiqueTransmis extends Mailable
{
    public function __construct(public LotPhysique $lot, public ?string $note = null)
    {
        $this->lot->load('evenement', 'tarif', 'user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tickets physiques disponibles — ' . ($this->lot->evenement?->titre ?? 'Événement'),
            replyTo: [new Address('contact@paxevent.com', 'PaxEvent')],
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'Precedence' => 'bulk',
                'List-Unsubscribe' => '<mailto:contact@paxevent.com?subject=Desinscription>',
                'X-Mailer' => 'PaxEvent Billetterie',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lot-physique-transmis',
            with: ['lot' => $this->lot, 'note' => $this->note],
        );
    }
}
