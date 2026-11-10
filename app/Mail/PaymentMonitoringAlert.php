<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentMonitoringAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $action,
        public array $details,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PaxEvent] Alerte paiement : '.$this->action,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-monitoring-alert',
            with: [
                'action' => $this->action,
                'details' => $this->details,
            ],
        );
    }
}
