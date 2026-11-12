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

        // Un billet qui n'a jamais été délivré (paiement en attente, échoué ou non
        // confirmé) n'a pas de code entre les mains du client. Il ne doit jamais
        // produire un verdict dédié : on le traite exactement comme un code inconnu.
        if (! $ticket || ! in_array($ticket->statut_paiement, ['payé', 'remboursé'], true)) {
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

        if ($ticket->statut_paiement === 'remboursé') {
            $this->logScan($agent, $code, 'invalide', 'Ticket remboursé', $ticket->id);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Ticket remboursé',
                'message' => 'Ce ticket a été remboursé et n\'est plus valide.',
                'ticket' => $this->contexteBillet($ticket),
            ];
        }

        if ($ticket->annule) {
            $this->logScan($agent, $code, 'invalide', 'Ticket annulé', $ticket->id);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Ticket annulé',
                'message' => 'Ce ticket a été annulé et n\'est plus valide.',
                'ticket' => $this->contexteBillet($ticket),
            ];
        }

        $jourScan = $agent->evenement->jourScanActuel();

        // Billet rattaché à un jour précis : il n'est valable que ce jour-là.
        // On distingue le jour futur, le jour passé et une autre session du
        // même jour pour que le refus soit explicite.
        if ($ticket->evenement_date_id !== null) {
            $jourBillet = $ticket->jour()->first();
            $libelle = $jourBillet?->libelle() ?? 'le jour prévu';
            $dateLabel = $jourBillet?->date_debut?->isoFormat('ddd D MMM') ?? '';

            if (! $jourBillet) {
                $this->logScan($agent, $code, 'invalide', 'Jour du billet introuvable', $ticket->id);

                return [
                    'success' => false,
                    'resultat' => 'invalide',
                    'raison' => 'Jour du billet introuvable',
                    'message' => 'Le jour associé à ce billet est introuvable.',
                    'ticket' => $this->contexteBillet($ticket),
                ];
            }

            $memeSession = $jourScan !== null && (int) $jourScan->id === (int) $jourBillet->id;

            if (! $memeSession) {
                if (now()->lt($jourBillet->date_debut)) {
                    $raison = 'Billet valable un autre jour';
                    $message = "Ce billet est valable {$libelle} ({$dateLabel}). Revenez ce jour-là.";

                    if (now()->toDateString() === $jourBillet->date_debut->toDateString()) {
                        $raison = 'Session pas commencée';
                        $message = 'La session de ce billet commence à '.$jourBillet->date_debut->format('H:i').'. Revenez à cette heure.';
                    }
                } elseif (now()->gt($jourBillet->finFenetreScan())) {
                    $raison = 'Billet valable un autre jour';
                    $message = "Ce billet était valable {$libelle} ({$dateLabel}). Il n'est plus valable.";
                } else {
                    $raison = 'Billet valable une autre session';
                    $message = "Ce billet n'est pas valable pour la session en cours. Il est valable {$libelle} ({$dateLabel}).";
                }

                $this->logScan($agent, $code, 'invalide', $raison, $ticket->id);

                return [
                    'success' => false,
                    'resultat' => 'invalide',
                    'raison' => $raison,
                    'message' => $message,
                    'ticket' => $this->contexteBillet($ticket),
                ];
            }
        }

        if (! $jourScan) {
            $this->logScan($agent, $code, 'invalide', 'Pas de session aujourd\'hui', $ticket->id);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Pas de session aujourd\'hui',
                'message' => 'L\'événement n\'a pas de session prévue aujourd\'hui.',
                'ticket' => $this->contexteBillet($ticket),
            ];
        }

        $maintenant = now();
        if ($maintenant->lt($jourScan->date_debut)) {
            $this->logScan($agent, $code, 'invalide', 'Session pas commencée', $ticket->id);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Session pas commencée',
                'message' => 'La session d\'aujourd\'hui commence à '.$jourScan->date_debut->format('H:i').'. Revenez à cette heure.',
                'ticket' => $this->contexteBillet($ticket),
            ];
        }

        if ($maintenant->gt($jourScan->finFenetreScan())) {
            $this->logScan($agent, $code, 'invalide', 'Session terminée', $ticket->id);

            return [
                'success' => false,
                'resultat' => 'invalide',
                'raison' => 'Session terminée',
                'message' => 'La session d\'aujourd\'hui est terminée. Revenez un autre jour de l\'événement.',
                'ticket' => $this->contexteBillet($ticket),
            ];
        }

        if ($ticket->dejaScanneEntre($jourScan->date_debut, $jourScan->finFenetreScan())) {
            $this->logScan($agent, $code, 'deja_utilise', 'Déjà scanné pour cette session', $ticket->id);

            // Le billet reste valable : seul le scan du jour est refusé.
            $multiJours = $agent->evenement->dates()->count() > 1;

            return [
                'success' => false,
                'resultat' => 'deja_utilise',
                'raison' => 'Déjà scanné aujourd\'hui',
                'message' => $multiJours
                    ? 'Ce billet a déjà été scanné aujourd\'hui. Il reste valable pour les prochains jours.'
                    : 'Ce billet a déjà été scanné aujourd\'hui.',
                'ticket' => $this->contexteBillet($ticket),
            ];
        }

        $this->logScan($agent, $code, 'valide', 'Scan réussi', $ticket->id);

        return [
            'success' => true,
            'resultat' => 'valide',
            'raison' => 'Scan réussi',
            'message' => 'Ticket validé avec succès !',
            'ticket' => [
                ...$this->contexteBillet($ticket),
                'email' => $ticket->email_acheteur,
                'montant' => number_format($ticket->montant, 0, ',', ' '),
                'transaction_id' => $ticket->transaction_id,
                'date_achat' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
            ],
        ];
    }

    // Type de billet (nom du tarif : SIMPLE, STANDARD, VIP, ...) et porteur.
    // Le type n'est jamais préfixé côté client : la valeur parle d'elle-même.
    private function contexteBillet(Ticket $ticket): array
    {
        return [
            'nom' => $ticket->nom_acheteur,
            'nom_tarif' => $ticket->getLabel(),
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
