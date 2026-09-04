<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class RegistrationCorrections extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public User $user;
    public string $reason;

    public function __construct(User $user, string $reason)
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PaxEvent] Corrections demandées sur votre profil',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-corrections',
        );
    }
}
