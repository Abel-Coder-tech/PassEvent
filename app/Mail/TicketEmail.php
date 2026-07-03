<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public array $pdfs;

    public function __construct($tickets)
    {
        if ($tickets instanceof Ticket) {
            $tickets = collect([$tickets]);
        } elseif (is_array($tickets)) {
            $tickets = collect($tickets);
        }

        $this->tickets = $tickets;
        $this->pdfs = [];

        foreach ($tickets as $ticket) {
            $ticket->load('evenement', 'tarif');

            $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);
            $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo_paxevent.png')));

            $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri', 'logoDataUri'));
            $pdf->setPaper('a4', 'portrait');

            $this->pdfs[] = [
                'content' => $pdf->output(),
                'filename' => 'PaxEvent-' . $ticket->code_unique . '.pdf',
            ];
        }
    }

    public function envelope(): Envelope
    {
        $first = $this->tickets->first();
        $quantite = $this->tickets->count();

        return new Envelope(
            subject: $quantite > 1
                ? "Vos {$quantite} billets PaxEvent pour {$first->evenement->titre}"
                : "Votre billet PaxEvent pour {$first->evenement->titre}",
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
            view: 'emails.ticket',
            with: ['tickets' => $this->tickets],
        );
    }

    public function attachments(): array
    {
        return array_map(fn($pdf) =>
            Attachment::fromData(fn() => $pdf['content'], $pdf['filename'])
                ->withMime('application/pdf'),
            $this->pdfs
        );
    }
}
