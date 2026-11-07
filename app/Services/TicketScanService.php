<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Log;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class TicketScanService
{
    public function verifier(Agent $agent, string $code): array
    {
        $ticket = Ticket::trouverParCodeSaisi($code);

        if (! $ticket) {
            $this->logScan($agent, $code, 'invalide', 'Ticket introuvable');

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Ticket introuvable',
                'message' => 'Ticket introuvable.',
                'ticket' => null,
            ];
        }

        if ($ticket->evenement_id !== $agent->evenement_id) {
            $this->logScan($agent, $code, 'invalide', 'Événement non autorisé');

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Événement non autorisé',
                'message' => 'Ce ticket ne correspond pas à cet événement.',
                'ticket' => null,
            ];
        }

        if ($ticket->statut_paiement !== 'payé') {
            $this->logScan($agent, $code, 'invalide', 'Paiement non confirmé');

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Paiement non confirmé',
                'message' => 'Ce ticket n\'a pas été payé.',
                'ticket' => null,
            ];
        }

        if ($ticket->annule) {
            $this->logScan($agent, $code, 'invalide', 'Ticket annulé');

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
            $this->logScan($agent, $code, 'invalide', 'Pas de session aujourd\'hui');

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Pas de session aujourd\'hui',
                'message' => 'L\'événement n\'a pas de session prévue aujourd\'hui.',
                'ticket' => null,
            ];
        }

        $maintenant = now();
        if ($maintenant->lt($jourScan->date_debut)) {
            $this->logScan($agent, $code, 'invalide', 'Session pas commencée');

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Session pas commencée',
                'message' => 'La session d\'aujourd\'hui commence à '.$jourScan->date_debut->format('H:i').'. Revenez à cette heure.',
                'ticket' => null,
            ];
        }

        if ($maintenant->gt($jourScan->finFenetreScan())) {
            $this->logScan($agent, $code, 'invalide', 'Session terminée');

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Session terminée',
                'message' => 'La session d\'aujourd\'hui est terminée. Revenez un autre jour de l\'événement.',
                'ticket' => null,
            ];
        }

        if ($ticket->dejaScanneAujourdhui()) {
            $this->logScan($agent, $code, 'deja_utilise', 'Déjà scanné aujourd\'hui', $ticket->id);

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

        $this->logScan($agent, $code, 'valide', 'Scan réussi', $ticket->id);

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
    }

    private function logScan(Agent $agent, string $code, string $resultat, string $raison, ?int $ticketId = null): void
    {
        Log::create([
            'ticket_id' => $ticketId,
            'agent_id' => $agent->id,
            'type_operation' => 'scan',
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
