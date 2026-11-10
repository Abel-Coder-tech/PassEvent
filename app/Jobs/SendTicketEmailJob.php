<?php

namespace App\Jobs;

use App\Mail\TicketEmail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $ticketIds;

    public ?string $toEmail;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(array $ticketIds, ?string $toEmail = null)
    {
        $this->ticketIds = $ticketIds;
        $this->toEmail = $toEmail;
    }

    public function handle(): void
    {
        $debut = microtime(true);

        $tickets = Ticket::with('evenement.dates', 'tarif')
            ->whereIn('id', $this->ticketIds)
            ->get();

        if ($tickets->isEmpty()) {
            Log::warning('SendTicketEmailJob - aucun ticket trouvé', ['ids' => $this->ticketIds]);

            return;
        }

        // Garde-fou absolu : un billet non payé, annulé ou expiré ne doit jamais
        // partir par email. On ignore ces billets sans les marquer comme envoyés.
        $ignores = $tickets->reject->estDelivrable();
        if ($ignores->isNotEmpty()) {
            Log::warning('SendTicketEmailJob - billets non délivrables ignorés', [
                'ids' => $ignores->pluck('id')->all(),
                'statuts' => $ignores->pluck('statut_paiement', 'id')->all(),
            ]);
        }

        $tickets = $tickets->filter->estDelivrable()->values();

        if ($tickets->isEmpty()) {
            return;
        }

        $ids = $tickets->pluck('id')->all();
        $destinataire = $this->toEmail ?? $tickets->first()->email_acheteur;

        if (! $destinataire) {
            Log::warning('SendTicketEmailJob - aucun destinataire email', ['ids' => $ids]);

            return;
        }

        try {
            Mail::to($destinataire)->send(new TicketEmail($tickets));
            $this->enregistrerSucces($ids);
            Log::info('SendTicketEmailJob - email envoyé', [
                'ids' => $ids,
                'to' => $destinataire,
                'billets' => $tickets->count(),
                'duree_ms' => (int) round((microtime(true) - $debut) * 1000),
            ]);
        } catch (\Throwable $e) {
            // Pas de re-throw : l'envoi immédiat ne doit jamais casser la réponse acheteur.
            // Le cron tickets:envoyer-emails reprendra les billets non notifiés.
            $this->enregistrerEchec($e->getMessage(), $ids);
            Log::error('SendTicketEmailJob - envoi échoué : ' . $e->getMessage(), [
                'ids' => $ids,
                'to' => $destinataire,
                'billets' => $tickets->count(),
                'duree_ms' => (int) round((microtime(true) - $debut) * 1000),
            ]);
        }
    }

    // Marque les billets comme notifiés : ils ne seront plus repris par le rattrapage.
    private function enregistrerSucces(array $ids): void
    {
        Ticket::whereIn('id', $ids)->update([
            'email_envoye_at' => now(),
            'email_derniere_erreur' => null,
        ]);
    }

    // Comptabilise la tentative échouée pour le rattrapage automatique.
    private function enregistrerEchec(string $message, array $ids): void
    {
        Ticket::whereIn('id', $ids)
            ->whereNull('email_envoye_at')
            ->increment('email_tentatives', 1, [
                'email_derniere_erreur' => mb_substr($message, 0, 500),
            ]);
    }

    public function failed(\Throwable $e): void
    {
        $this->enregistrerEchec($e->getMessage(), $this->ticketIds);
    }
}
