<?php

namespace App\Mail;

use App\Models\Agent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public Agent $agent;
    public string $motDePasse;

    /// Code PIN en clair, communiqué une seule fois par e-mail.
    /// La base ne stocke que son hash bcrypt.
    public string $codeAcces;

    public function __construct(Agent $agent, string $motDePasse, string $codeAcces = '')
    {
        $this->agent = $agent;
        $this->motDePasse = $motDePasse;
        $this->codeAcces = $codeAcces;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous êtes chargé du scan - ' . $this->agent->evenement->titre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agent-assigned',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
