<?php

namespace App\Mail;

use App\Models\Evenement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Informe un acheteur que les dates de l'événement ont changé. Aucun billet
// n'est joint : le billet déjà reçu (même QR code) reste valable aux
// nouvelles dates. Ne pas renvoyer de billet évite qu'une seconde copie
// circule et soit donnée à quelqu'un d'autre.
class DatesEvenementModifiees extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $anciennesDates  au format Y-m-d H:i
     * @param  array<int, string>  $nouvellesDates  au format Y-m-d H:i
     */
    public function __construct(
        public Evenement $evenement,
        public array $anciennesDates,
        public array $nouvellesDates,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Changement de date : {$this->evenement->titre} - PaxEvent");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.dates-evenement-modifiees');
    }
}
