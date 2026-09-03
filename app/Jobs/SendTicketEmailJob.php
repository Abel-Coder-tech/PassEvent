<?php

namespace App\Jobs;

use App\Mail\TicketEmail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        $tickets = Ticket::with('evenement', 'tarif')
            ->whereIn('id', $this->ticketIds)
            ->get();

        if ($tickets->isEmpty()) {
            Log::warning('SendTicketEmailJob - aucun ticket trouvé', ['ids' => $this->ticketIds]);
            return;
        }

        $destinataire = $this->toEmail ?? $tickets->first()->email_acheteur;

        if (!$destinataire) {
            Log::warning('SendTicketEmailJob - aucun destinataire email', ['ids' => $this->ticketIds]);
            return;
        }

        try {
            Mail::to($destinataire)->send(new TicketEmail($tickets));
            Log::info('SendTicketEmailJob - email envoyé', [
                'ids' => $this->ticketIds,
                'to' => $destinataire,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendTicketEmailJob - envoi échoué : ' . $e->getMessage(), [
                'ids' => $this->ticketIds,
                'to' => $destinataire,
            ]);
            throw $e;
        }
    }
}
