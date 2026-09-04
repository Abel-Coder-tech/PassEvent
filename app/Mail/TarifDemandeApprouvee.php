<?php

namespace App\Mail;

use App\Models\DemandeModificationTarif;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class TarifDemandeApprouvee extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public DemandeModificationTarif $demande;

    public function __construct(DemandeModificationTarif $demande)
    {
        $this->demande = $demande;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Modification de tarif approuvée — PaxEvent',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tarif-demande-approuvee',
        );
    }
}
