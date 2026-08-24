<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EquipeMembreCree extends Mailable
{
    use Queueable, SerializesModels;

    public User $membre;
    public string $motDePasse;
    public bool $reinitialisation;

    public function __construct(User $membre, string $motDePasse, bool $reinitialisation = false)
    {
        $this->membre = $membre;
        $this->motDePasse = $motDePasse;
        $this->reinitialisation = $reinitialisation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->reinitialisation ? 'Réinitialisation' : 'Création') . ' de votre compte équipe PaxEvent',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.equipe-membre-cree',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
