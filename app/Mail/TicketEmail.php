<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

class TicketEmail extends Mailable
{
    public Ticket $ticket;
    public string $pdfContent;
    public string $filename;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;

        $ticket->load('evenement', 'tarif');

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 200);

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact('ticket', 'qrCodeDataUri'));
        $pdf->setPaper('a4', 'portrait');

        $this->pdfContent = $pdf->output();
        $this->filename = 'PassEvent-' . $ticket->code_unique . '.pdf';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre billet PassEvent pour ' . $this->ticket->evenement->titre,
            replyTo: ['passevent2026@gmail.com' => 'PassEvent'],
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            textHeaders: [
                'Precedence' => 'bulk',
                'List-Unsubscribe' => '<mailto:passevent2026@gmail.com?subject=Desinscription>',
                'X-Mailer' => 'PassEvent Billetterie',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn() => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
