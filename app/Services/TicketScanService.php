<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Log;
use App\Models\OfflineLease;
use App\Models\Ticket;
use App\Models\TicketScanDay;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TicketScanService
{
    public function verifier(Agent $agent, string $code, ?Carbon $at = null, string $source = 'online'): array
    {
        $now = $at ?: now();
        $evenement = $agent->evenement;

        if ($evenement && $evenement->offline_mode_enabled) {
            $lease = OfflineLease::where('evenement_id', $evenement->id)
                ->whereIn('statut', ['actif', 'revocation_en_attente'])
                ->lockForUpdate()
                ->first();

            if ($lease && (int) $lease->agent_id !== (int) $agent->id) {
                $this->logScan($agent, $code, 'invalide', 'Mode hors ligne réservé à un autre agent', null, $source);
                return [
                    'success' => false,
                    'resultat' => 'invalide',
                    'raison' => 'offline_mode_reserved',
                    'message' => 'Le scan est réservé à l\'agent désigné pour le mode hors ligne.',
                    'ticket' => null,
                ];
            }
        }

        $ticket = Ticket::trouverParCodeSaisi($code);

        if (! $ticket) {
            $this->logScan($agent, $code, 'invalide', 'Ticket introuvable', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Ticket introuvable',
                'message' => 'Ticket introuvable.',
                'ticket' => null,
            ];
        }

        if ($ticket->evenement_id !== $agent->evenement_id) {
            $this->logScan($agent, $code, 'invalide', 'Événement non autorisé', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Événement non autorisé',
                'message' => 'Ce ticket ne correspond pas à cet événement.',
                'ticket' => null,
            ];
        }

        if ($ticket->statut_paiement !== 'payé') {
            $this->logScan($agent, $code, 'invalide', 'Paiement non confirmé', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Paiement non confirmé',
                'message' => 'Ce ticket n\'a pas été payé.',
                'ticket' => null,
            ];
        }

        if ($ticket->annule) {
            $this->logScan($agent, $code, 'invalide', 'Ticket annulé', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Ticket annulé',
                'message' => 'Ce ticket a été annulé et n\'est plus valide.',
                'ticket' => null,
            ];
        }

        $jourScan = $agent->evenement->jourScanActuel();

        if (! $jourScan) {
            $this->logScan($agent, $code, 'invalide', 'Pas de session aujourd\'hui', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Pas de session aujourd\'hui',
                'message' => 'L\'événement n\'a pas de session prévue aujourd\'hui.',
                'ticket' => null,
            ];
        }

        if ($now->lt($jourScan->date_debut)) {
            $this->logScan($agent, $code, 'invalide', 'Session pas commencée', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Session pas commencée',
                'message' => 'La session d\'aujourd\'hui commence à '.$jourScan->date_debut->format('H:i').'. Revenez à cette heure.',
                'ticket' => null,
            ];
        }

        if ($now->gt($jourScan->finFenetreScan())) {
            $this->logScan($agent, $code, 'invalide', 'Session terminée', null, $source);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Session terminée',
                'message' => 'La session d\'aujourd\'hui est terminée. Revenez un autre jour de l\'événement.',
                'ticket' => null,
            ];
        }

        // Utiliser ticket_scan_days si disponible
        $scanDate = $now->copy()->setTimezone($agent->evenement->fuseau_horaire ?: config('app.timezone'))->toDateString();
        $dejaScanne = TicketScanDay::where('ticket_id', $ticket->id)->where('scan_date', $scanDate)->exists();
        if (! $dejaScanne) {
            $dejaScanne = $ticket->dejaScanneAujourdhui();
        }

        if ($dejaScanne) {
            $this->logScan($agent, $code, 'deja_utilise', 'Déjà scanné aujourd\'hui', $ticket->id, $source);

            return [
                'success' => false,
                'resultat' => 'deja_utilise',
                'raison' => 'Déjà scanné aujourd\'hui',
                'message' => 'Ce billet a déjà été scanné aujourd\'hui. Il reste valable pour les prochains jours.',
                'ticket' => [
                    'nom' => $ticket->nom_acheteur,
                    'nom_tarif' => $ticket->nom_tarif,
                ],
            ];
        }

        return DB::transaction(function () use ($agent, $code, $ticket, $source, $now, $scanDate) {
            // Double check
            if (TicketScanDay::where('ticket_id', $ticket->id)->where('scan_date', $scanDate)->exists()) {
                $this->logScan($agent, $code, 'deja_utilise', 'Déjà scanné aujourd\'hui', $ticket->id, $source);
                return [
                    'success' => false,
                    'resultat' => 'deja_utilise',
                    'raison' => 'Déjà scanné aujourd\'hui',
                    'message' => 'Ce billet a déjà été scanné aujourd\'hui. Il reste valable pour les prochains jours.',
                    'ticket' => ['nom' => $ticket->nom_acheteur, 'nom_tarif' => $ticket->nom_tarif],
                ];
            }

            $log = $this->logScan($agent, $code, 'valide', 'Scan réussi', $ticket->id, $source, $now, $scanDate);

            try {
                TicketScanDay::create([
                    'ticket_id' => $ticket->id,
                    'scan_date' => $scanDate,
                    'log_id' => $log->id,
                    'agent_id' => $agent->id,
                ]);
            } catch (\Throwable $e) {
                // Already scanned
            }

            return [
                'success' => true,
                'resultat' => 'valide',
                'raison' => 'Scan réussi',
                'message' => 'Ticket validé avec succès !',
                'ticket' => [
                    'nom' => $ticket->nom_acheteur,
                    'email' => $ticket->email_acheteur,
                    'nom_tarif' => $ticket->nom_tarif,
                    'montant' => number_format($ticket->montant, 0, ',', ' '),
                    'transaction_id' => $ticket->transaction_id,
                    'date_achat' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
                ],
            ];
        });
    }

    private function logScan(Agent $agent, string $code, string $resultat, string $raison, ?int $ticketId = null, string $source = 'online', ?Carbon $at = null, ?string $scanDate = null): Log
    {
        $timezone = $agent->evenement->fuseau_horaire ?: config('app.timezone');
        $scannedAt = $at ?: now();
        $scanDate = $scanDate ?: $scannedAt->copy()->setTimezone($timezone)->toDateString();

        return Log::create([
            'ticket_id' => $ticketId,
            'agent_id' => $agent->id,
            'scanned_at' => $scannedAt,
            'scan_date' => $scanDate,
            'source' => $source,
            'server_resultat' => $resultat,
            'type_operation' => 'scan',
            'resultat' => $resultat,
            'details' => [
                'code' => $code,
                'resultat' => $resultat,
                'raison' => $raison,
                'evenement_id' => $agent->evenement_id,
            ],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
