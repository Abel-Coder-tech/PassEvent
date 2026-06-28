<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegistrationPending extends Mailable
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre demande de compte PaxEvent est en cours de traitement',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-pending',
        );
    }
}
