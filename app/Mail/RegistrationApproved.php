<?php

namespace App\Mail;

use App\Models\User;
use App\Services\ContratService;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegistrationApproved extends Mailable
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PaxEvent] Compte approuvé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-approved',
        );
    }

    public function attachments(): array
    {
        $contrat = app(ContratService::class)->pdf($this->user);

        return [
            Attachment::fromData(
                fn () => $contrat->output(),
                'Contrat-Prestation-PaxEvent-' . $this->user->id . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}