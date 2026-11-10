<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Services\QrCodeService;
use App\Services\TicketPdfService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Collection;

class TicketEmail extends Mailable
{
    public Collection $tickets;

    public array $pdfs = [];

    private array $ticketIds = [];

    private bool $loaded = false;

    public function __construct(Ticket|array|Collection $tickets)
    {
        if ($tickets instanceof Ticket) {
            $tickets = collect([$tickets]);
        } elseif (is_array($tickets)) {
            $tickets = collect($tickets);
        }

        $this->ticketIds = $tickets->pluck('id')->all();
        $this->tickets = new Collection();
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;
        $this->tickets = Ticket::with('evenement.dates', 'tarif')->whereIn('id', $this->ticketIds)->get();

        if ($this->tickets->isEmpty()) {
            throw new \RuntimeException('Aucun ticket trouvé pour l\'envoi de l\'email.');
        }

        // Assets communs calculés une seule fois pour toute la commande.
        $logoDataUri = Ticket::logoVioletDataUri();
        $sharedParEvenement = [];

        foreach ($this->tickets as $ticket) {
            $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 170, 'H');

            // Photo d'événement + favicon : lus une seule fois par événement, pas par billet.
            $evenementId = (int) ($ticket->evenement_id ?? 0);
            $shared = $sharedParEvenement[$evenementId] ??= [
                ...TicketPdfService::sharedForTicket($ticket),
                // Conserve le rendu actuel de l'e-mail (logo violet).
                'logo' => $logoDataUri,
            ];

            $this->pdfs[] = [
                'content' => TicketPdfService::generer($ticket, $qrCodeDataUri, $logoDataUri, $shared)->output(),
                'filename' => 'PaxEvent-' . $ticket->code_unique . '.pdf',
            ];
        }
    }

    public function envelope(): Envelope
    {
        $this->load();
        $first = $this->tickets->first();
        $quantite = $this->tickets->count();

        return new Envelope(
            subject: $quantite > 1
                ? "Vos {$quantite} tickets PaxEvent pour {$first->evenement->titre}"
                : "Votre ticket PaxEvent pour {$first->evenement->titre}",
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
        $this->load();

        return new Content(
            view: 'emails.ticket',
            with: ['tickets' => $this->tickets],
        );
    }

    public function attachments(): array
    {
        $this->load();

        return array_map(fn($pdf) =>
            Attachment::fromData(fn() => $pdf['content'], $pdf['filename'])
                ->withMime('application/pdf'),
            $this->pdfs
        );
    }
}
