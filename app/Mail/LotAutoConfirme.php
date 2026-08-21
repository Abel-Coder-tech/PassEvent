<?php

namespace App\Mail;

use App\Models\LotPhysique;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

class LotAutoConfirme extends Mailable
{
    public function __construct(public $lots)
    {
        $this->lots = collect($lots)->map(fn ($lot) => $lot->load('evenement', 'tarif'));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos QR codes sont prêts — PaxEvent',
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
            view: 'emails.lot-auto-confirme',
            with: [
                'lots' => $this->lots,
                'total' => round((float) $this->lots->sum('montant_commission'), 2),
                'premier' => $this->lots->first(),
            ],
        );
    }
}
