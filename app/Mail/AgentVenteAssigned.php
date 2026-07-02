<?php

namespace App\Mail;

use App\Models\AgentVente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentVenteAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AgentVente $agent,
        public string $motDePasse,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous avez été ajouté comme agent de vente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agent-vente-assigned',
        );
    }
}
