<?php

namespace App\Mail;

use App\Models\LotPhysique;
use App\Services\LotPhysiquePdfService;
use App\Services\LotPhysiqueTemplatePdfService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Log;

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

    /**
     * Planche PDF (template + QR codes) jointe à l'email.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $tickets = $this->lot->tickets()->where('annule', false)->orderBy('code_unique')->get();

        if ($tickets->isEmpty()) {
            return [];
        }

        try {
            $pdf = $this->lot->aUnTemplate()
                ? LotPhysiqueTemplatePdfService::generer($this->lot, $tickets)
                : LotPhysiquePdfService::generer($this->lot, $tickets);

            return [
                Attachment::fromData(fn () => $pdf->output(), 'Planche-'.$this->lot->nom.'.pdf')
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            Log::error('Lot physique - Erreur génération PDF pièce jointe : '.$e->getMessage());

            return [];
        }
    }
}
