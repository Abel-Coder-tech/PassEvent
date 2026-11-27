<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Log;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TicketScanService
{
    // Fenêtre pendant laquelle un rescan du même billet par le même agent est
    // confirmé (coupure réseau, réponse perdue) au lieu d'être refusé.
    public const DELAI_RESCAN_SECONDES = 60;

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

        // Comparaison en entiers : selon le pilote SQL, l'un des deux peut arriver en texte ("12" !== 12).
        if ((int) $ticket->evenement_id !== (int) $agent->evenement_id) {
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
                    return $this->pasEncoreOuvert($agent, $code, $ticket, $jourBillet->date_debut, $libelle);
                }

                if (now()->gt($jourBillet->finFenetreScan())) {
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
            // Billet valide, mais aucune session en cours : l'événement n'a pas commencé ou est terminé.
            $prochaine = $agent->evenement->dates()->where('date_debut', '>', now())->orderBy('date_debut')->first();
            if ($prochaine) {
                return $this->pasEncoreOuvert($agent, $code, $ticket, $prochaine->date_debut);
            }

            $derniere = $agent->evenement->dates()->orderByDesc('date_debut')->first();
            if ($derniere && now()->gt($derniere->finFenetreScan())) {
                $this->logScan($agent, $code, 'invalide', 'Événement terminé', $ticket->id);

                return [
                    'success' => false,
                    'resultat' => 'termine',
                    'raison' => 'Événement terminé',
                    'message' => 'Cet événement est terminé depuis le '.$this->dateLisible($derniere->date_debut).'. Le billet n\'est plus utilisable.',
                    'date_fin' => $derniere->finFenetreScan()->toIso8601String(),
                    'ticket' => $this->contexteBillet($ticket),
                ];
            }

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
            return $this->pasEncoreOuvert($agent, $code, $ticket, $jourScan->date_debut);

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

        // Verrou sur la ligne du billet : deux agents qui scannent le même billet
        // au même instant sont traités l'un après l'autre. Sans lui, les deux
        // pouvaient passer le contrôle « déjà scanné » avant que l'un n'enregistre
        // son scan (double entrée avec un seul billet).
        $verdict = DB::transaction(function () use ($agent, $code, $ticket, $jourScan) {
            Ticket::whereKey($ticket->id)->lockForUpdate()->first();

            $dernierValide = Log::where('ticket_id', $ticket->id)
                ->where('type_operation', 'scan')
                ->where('details->resultat', 'valide')
                ->whereBetween('created_at', [$jourScan->date_debut, $jourScan->finFenetreScan()])
                ->latest('id')
                ->first();

            if (! $dernierValide) {
                $this->logScan($agent, $code, 'valide', 'Scan réussi', $ticket->id);

                return ['valide', null];
            }

            // Rescan par le MÊME agent juste après (réseau lent, réponse perdue,
            // double déclenchement) : c'est la même personne devant lui, on
            // confirme au lieu d'afficher « déjà scanné » et de créer un litige.
            if ((int) $dernierValide->agent_id === (int) $agent->id
                && $dernierValide->created_at->gte(now()->subSeconds(self::DELAI_RESCAN_SECONDES))) {
                $this->logScan($agent, $code, 'deja_utilise', 'Rescan du même agent (déjà validé)', $ticket->id);

                return ['revalide', $dernierValide->created_at];
            }

            $this->logScan($agent, $code, 'deja_utilise', 'Déjà scanné pour cette session', $ticket->id);

            return ['deja', null];
        });

        if ($verdict[0] === 'revalide') {
            $secondes = max(1, (int) $verdict[1]->diffInSeconds(now()));

            return [
                'success' => true,
                'resultat' => 'valide',
                'raison' => 'Déjà validé par vous',
                'message' => "Billet déjà validé par vous il y a {$secondes} s : la personne peut entrer.",
                'rescan' => true,
                'ticket' => [
                    ...$this->contexteBillet($ticket),
                    'email' => $ticket->email_acheteur,
                    'montant' => number_format($ticket->montant, 0, ',', ' '),
                    'date_achat' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
                ],
            ];
        }

        if ($verdict[0] === 'deja') {
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

        return [
            'success' => true,
            'resultat' => 'valide',
            'raison' => 'Scan réussi',
            'message' => 'Ticket validé avec succès !',
            'ticket' => [
                ...$this->contexteBillet($ticket),
                'email' => $ticket->email_acheteur,
                'montant' => number_format($ticket->montant, 0, ',', ' '),
                // L'ID de transaction n'est jamais envoyé aux agents de scan (il
                // permet de récupérer les billets sur un autre email).
                'date_achat' => $ticket->date_achat ? $ticket->date_achat->format('d/m/Y H:i') : null,
            ],
        ];
    }

    // Billet valide mais trop tôt : refus non définitif, l'appli affiche « Revenez le ... ».
    private function pasEncoreOuvert(Agent $agent, string $code, Ticket $ticket, $debut, ?string $jour = null): array
    {
        $this->logScan($agent, $code, 'invalide', 'Événement pas encore commencé', $ticket->id);

        $quand = $debut->isSameDay(now())
            ? 'aujourd\'hui à '.$debut->format('H\hi')
            : 'le '.$this->dateLisible($debut);

        return [
            'success' => false,
            'resultat' => 'pas_encore_ouvert',
            'raison' => 'Événement pas encore commencé',
            'message' => $jour
                ? "Ce billet est valide pour {$jour}. Revenez {$quand}."
                : "Ce billet est valide. Revenez {$quand}.",
            'date_debut' => $debut->toIso8601String(),
            'ticket' => $this->contexteBillet($ticket),
        ];
    }

    // Ex. « 31 décembre 2026 à 18h00 ».
    private function dateLisible($date): string
    {
        return $date->copy()->locale('fr')->isoFormat('D MMMM YYYY [à] HH[h]mm');
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
