<?php

namespace App\Services;

use App\Enums\LogOperationType;
use App\Enums\TicketPaymentStatus;
use App\Jobs\SendTicketEmailJob;
use App\Models\AgentVente;
use App\Models\AgentVenteNotification;
use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\JournalAction;
use App\Models\Log;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Models\VenteIdempotence;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Vente d'un panier multi-tarifs par un agent.
 *
 * La page publique accepte déjà un panier (un ou plusieurs tarifs dans une
 * même commande). L'API agent, elle, ne gérait qu'un seul tarif. Ce service
 * ajoute le même comportement sans toucher à AgentVenteSaleService, qui reste
 * la référence pour les ventes mono-tarif : les deux partagent la même table
 * d'idempotence, le même format de transaction groupée et le même checkout
 * FedaPay (un seul paiement pour tout le panier).
 */
class AgentVentePanierService
{
    public const QUANTITE_MAX = 20;

    public function __construct(
        private AgentVenteSaleService $sales,
        private PaymentMonitoringService $paymentMonitoring,
    ) {}

    /**
     * @param  array{lignes?: array<int|string, int>, tarif_id?: int, quantite?: int, ...}  $data
     * @return array{tickets: Collection, ticket: Ticket, statut: string, montant_total: float, transaction_id: string, idempotent: bool, payment: ?array}
     */
    public function create(AgentVente $agent, array $data, ?string $ip = null, ?string $userAgent = null): array
    {
        $evenement = $agent->evenement()->first() ?? $agent->evenement;

        if (! $evenement) {
            throw ValidationException::withMessages([
                'evenement' => "Aucun événement n'est associé à votre compte.",
            ]);
        }

        // Un panier ne remplace pas la vente mono-tarif : si l'événement est
        // gratuit, le service historique gère l'entrée unique et la création
        // éventuelle du tarif « Gratuit ».
        if ($evenement->gratuit) {
            return $this->sales->create($agent, $data, $ip, $userAgent);
        }

        $demandes = $this->demandes($data);

        if ($demandes === []) {
            throw ValidationException::withMessages([
                'lignes' => 'Choisissez au moins un billet.',
            ]);
        }

        $nombreBillets = array_sum($demandes);

        if ($nombreBillets > self::QUANTITE_MAX) {
            throw ValidationException::withMessages([
                'lignes' => 'Vous ne pouvez pas vendre plus de '.self::QUANTITE_MAX.' billets à la fois.',
            ]);
        }

        $idempotencyKey = trim((string) ($data['idempotency_key'] ?? ''));
        $empreinte = $this->empreinteDemande($data, $demandes);

        if ($idempotencyKey !== '' && ($existante = $this->venteDejaCreee($agent, $idempotencyKey, $empreinte, $evenement)) !== null) {
            return $existante;
        }

        if ($raison = $evenement->raisonBlocageVente()) {
            throw ValidationException::withMessages([
                'evenement' => $this->messageBlocage($raison),
            ]);
        }

        $methode = $this->sales->normaliserMethode($data['methode_paiement'] ?? null);

        if (! in_array($methode, ['cash', 'mobile_money'], true)) {
            throw ValidationException::withMessages([
                'methode_paiement' => 'Méthode de paiement invalide. Choisissez espèces ou mobile money.',
            ]);
        }

        $tarifs = Tarif::query()
            ->where('evenement_id', $evenement->id)
            ->where('statut', 'actif')
            ->whereIn('id', array_keys($demandes))
            ->with('jour')
            ->get()
            ->keyBy('id');

        if ($tarifs->count() !== count($demandes)) {
            throw ValidationException::withMessages([
                'lignes' => "Un des billets choisis n'est plus disponible.",
            ]);
        }

        foreach ($tarifs as $tarif) {
            if (! $tarif->estEncoreVendable()) {
                throw ValidationException::withMessages([
                    'lignes' => "« {$tarif->nom} » n'est plus en vente : la date associée est passée.",
                ]);
            }
        }

        // Vérification rapide avant verrou : le contrôle définitif est refait
        // sous verrou, ligne par ligne, juste avant la création des billets.
        foreach ($demandes as $tarifId => $quantite) {
            $this->verifierDisponibilite($evenement, $tarifs->get($tarifId), $quantite);
        }

        $codePromo = $this->resoudreCodePromo($data['code_promo'] ?? null, $evenement, $demandes);

        $lignes = collect($demandes)->map(function (int $quantite, int $tarifId) use ($tarifs, $codePromo) {
            $tarif = $tarifs->get($tarifId);
            $reduction = $codePromo && (int) $codePromo->tarif_id === (int) $tarif->id
                ? $codePromo->calculerReduction((float) $tarif->prix)
                : 0.0;

            return [
                'tarif' => $tarif,
                'quantite' => $quantite,
                'reduction' => $reduction,
                'unitaire' => max(0, (float) $tarif->prix - $reduction),
                'code' => $codePromo && (int) $codePromo->tarif_id === (int) $tarif->id
                    ? $codePromo->code
                    : null,
            ];
        })->values();

        $montantTotal = round((float) $lignes->sum(
            fn (array $ligne) => $ligne['unitaire'] * $ligne['quantite']
        ), 2);

        if ($methode === 'cash' && $montantTotal > 0 && ! $evenement->ventesEspecesActivees()) {
            if ($evenement->ventesEspecesBloqueesSuperadmin()) {
                throw ValidationException::withMessages([
                    'methode_paiement' => 'Les ventes espèces sont actuellement désactivées pour cet événement. Utilisez le mobile money.',
                ]);
            }

            $cash = $this->sales->cashAvailability($evenement);

            throw ValidationException::withMessages([
                'methode_paiement' => "Ventes espèces bloquées. Vendez d'abord {$cash['seuil']} ticket(s) en ligne. Progression : {$cash['vendus_en_ligne']}/{$cash['seuil']}.",
            ]);
        }

        if ($methode === 'mobile_money' && $montantTotal > 0 && ! $this->sales->paymentConfig()['disponible']) {
            throw ValidationException::withMessages([
                'methode_paiement' => 'Le mobile money est fermé pour le moment. Encaissez en espèces ou contactez l\'organisateur.',
            ]);
        }

        // Espèces ou panier entièrement offert (promo à 100 %) : confirmation
        // immédiate. Sinon les billets restent en attente et réservent leur
        // place jusqu'au paiement.
        $confirmerImmediatement = $methode === 'cash' || $montantTotal <= 0;
        $reservationExpireLe = $confirmerImmediatement
            ? null
            : now()->addMinutes(AgentVenteSaleService::DUREE_RESERVATION_MINUTES);

        $transactionId = $confirmerImmediatement
            ? 'AGENT-'.strtoupper(Str::random(10))
            : 'GRP-'.strtoupper(Str::random(16));

        try {
            $result = DB::transaction(function () use (
                $agent, $evenement, $lignes, $nombreBillets, $montantTotal,
                $codePromo, $confirmerImmediatement, $data, $idempotencyKey,
                $empreinte, $transactionId, $reservationExpireLe
            ) {
                // Même mécanisme que la vente mono-tarif : l'index unique
                // départage deux requêtes concurrentes portant la même clé.
                if ($idempotencyKey !== '') {
                    VenteIdempotence::create([
                        'agent_vente_id' => $agent->id,
                        'cle' => $idempotencyKey,
                        'empreinte' => $empreinte,
                        'transaction_id' => $transactionId,
                    ]);
                }

                $evenementVerrouille = Evenement::whereKey($evenement->id)->lockForUpdate()->firstOrFail();
                $tickets = new Collection;

                foreach ($lignes as $ligne) {
                    $tarifVerrouille = Tarif::whereKey($ligne['tarif']->id)->lockForUpdate()->firstOrFail();

                    // Les billets de la ligne précédente viennent d'être créés :
                    // on vide le cache pour que la jauge du jour les compte.
                    $evenementVerrouille->resetCachesCapacite();
                    $this->verifierDisponibilite($evenementVerrouille, $tarifVerrouille, $ligne['quantite']);

                    for ($i = 0; $i < $ligne['quantite']; $i++) {
                        $tickets->push(Ticket::create([
                            'evenement_id' => $evenement->id,
                            'tarif_id' => $tarifVerrouille->id,
                            'agent_vente_id' => $agent->id,
                            'source' => 'agent_vente',
                            'code_unique' => $confirmerImmediatement
                                ? Ticket::genererCodeSecurise()
                                : Ticket::genererCodeReservation(),
                            'qr_signature' => Str::uuid()->toString(),
                            'email_acheteur' => $data['email_acheteur'],
                            'whatsapp_acheteur' => $data['whatsapp_acheteur'] ?? null,
                            'nom_acheteur' => $data['nom_acheteur'],
                            'nom_tarif' => $tarifVerrouille->nom ?? 'Standard',
                            'montant' => $ligne['unitaire'],
                            'montant_reduction' => $ligne['reduction'],
                            'code_promo_utilise' => $ligne['code'],
                            'quantite' => 1,
                            'statut_paiement' => $confirmerImmediatement
                                ? TicketPaymentStatus::Paid->value
                                : TicketPaymentStatus::Pending->value,
                            'methode_paiement' => $confirmerImmediatement ? 'cash' : 'mobile_money',
                            'type_paiement' => $confirmerImmediatement ? 'especes' : 'mobile_money',
                            'transaction_id' => $transactionId,
                            'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
                            'reservation_expire_le' => $reservationExpireLe,
                            'utilise' => false,
                            'date_achat' => now(),
                        ]));
                    }

                    $evenementVerrouille->increment('quota_vendu', $ligne['quantite']);
                }

                if ($confirmerImmediatement) {
                    foreach ($lignes as $ligne) {
                        Tarif::whereKey($ligne['tarif']->id)
                            ->increment('quantite_vendue', $ligne['quantite']);
                    }

                    if ($codePromo) {
                        $codePromo->increment('nb_utilisations');
                    }

                    $agent->increment('tickets_count', $nombreBillets);
                    $agent->increment('montant_total', $montantTotal);

                    foreach ($tickets as $ticket) {
                        Log::create([
                            'ticket_id' => $ticket->id,
                            'type_operation' => LogOperationType::Purchase->value,
                            'details' => [
                                'transaction_id' => $transactionId,
                                'methode' => 'cash',
                                'agent_vente' => true,
                                'panier' => true,
                            ],
                        ]);
                    }
                }

                return [
                    'tickets' => $tickets,
                    'ticket' => $tickets->first(),
                    'statut' => $confirmerImmediatement ? 'payé' : 'en_attente',
                    'montant_total' => $montantTotal,
                    'transaction_id' => $transactionId,
                ];
            });
        } catch (UniqueConstraintViolationException $exception) {
            if ($idempotencyKey !== '' && ($existante = $this->venteDejaCreee($agent, $idempotencyKey, $empreinte, $evenement)) !== null) {
                return $existante;
            }

            throw $exception;
        }

        if ($result['statut'] === 'payé') {
            SendTicketEmailJob::dispatchAfterResponse(
                $result['tickets']->pluck('id')->all(),
                $result['ticket']->email_acheteur
            );
        }

        $statutVente = $result['statut'] === 'payé' ? 'paye' : 'en_attente';

        if ($result['statut'] === 'en_attente') {
            $this->paymentMonitoring->record(
                'paiement_initie',
                [
                    'transaction_id' => $result['transaction_id'],
                    'event_id' => $evenement->id,
                    'ticket_count' => $result['tickets']->count(),
                    'amount' => (float) $result['montant_total'],
                    'currency' => 'XOF',
                    'source' => 'agent_vente',
                    'payment_status' => 'en_attente',
                    'agent_id' => $agent->id,
                    'panier' => true,
                ],
                null,
                $result['ticket']->id,
                'paiement',
                'info',
                false,
                $ip,
                $userAgent,
            );
        }

        foreach ($result['tickets'] as $ticket) {
            $this->journaliser(
                $ticket,
                $agent,
                'vente_creee',
                null,
                $statutVente,
                $ip,
                $userAgent,
                ['montant' => (float) $ticket->montant, 'methode' => $ticket->methode_paiement]
            );
        }

        AgentVenteNotification::envoyer(
            $agent,
            $result['statut'] === 'payé' ? 'Tickets vendus' : 'Vente enregistrée',
            $result['statut'] === 'payé'
                ? "{$nombreBillets} billet(s) vendu(s) pour un total de ".$this->montantLabel((float) $result['montant_total']).'.'
                : "La vente de {$nombreBillets} billet(s) (".$this->montantLabel((float) $result['montant_total']).') est en attente du paiement mobile.',
            $evenement->id,
            $result['statut'] === 'payé' ? AgentVenteNotification::TYPE_VENTE : AgentVenteNotification::TYPE_PAIEMENT,
            [
                'ticket_id' => $result['ticket']->id,
                'transaction_id' => $result['transaction_id'],
                'montant' => (float) $result['montant_total'],
                'billets' => $nombreBillets,
                'statut' => $statutVente,
            ]
        );

        return [
            ...$result,
            'idempotent' => false,
            'payment' => $confirmerImmediatement
                ? null
                : $this->sales->checkoutPayload($result['tickets'], $evenement),
        ];
    }

    /**
     * Panier demandé : tarif_id => quantité. Les lignes à zéro sont ignorées.
     *
     * @return array<int, int>
     */
    private function demandes(array $data): array
    {
        $lignes = is_array($data['lignes'] ?? null) ? $data['lignes'] : [];

        // Rétrocompatibilité : un appelant peut encore envoyer tarif_id +
        // quantite sans lignes.
        if ($lignes === [] && ! empty($data['tarif_id'])) {
            $lignes = [$data['tarif_id'] => max(1, (int) ($data['quantite'] ?? 1))];
        }

        $demandes = [];

        foreach ($lignes as $tarifId => $quantite) {
            $tarifId = (int) $tarifId;
            $quantite = (int) $quantite;

            if ($tarifId > 0 && $quantite > 0) {
                $demandes[$tarifId] = ($demandes[$tarifId] ?? 0) + $quantite;
            }
        }

        ksort($demandes);

        return $demandes;
    }

    private function resoudreCodePromo(?string $code, Evenement $evenement, array $demandes): ?CodePromo
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        $codePromo = CodePromo::where('code', $code)
            ->whereHas('tarif', fn ($q) => $q->where('evenement_id', $evenement->id))
            ->first();

        if (! $codePromo) {
            throw ValidationException::withMessages([
                'code_promo' => "Ce code promo n'est pas valide pour cet événement.",
            ]);
        }

        if (! $codePromo->estValide()) {
            $message = match (true) {
                ! $codePromo->actif => 'Ce code promo a été désactivé.',
                $codePromo->date_expiration && $codePromo->date_expiration < now() => 'Ce code promo a expiré.',
                $codePromo->max_utilisations && $codePromo->nb_utilisations >= $codePromo->max_utilisations => 'Ce code promo a atteint son nombre maximum d\'utilisations.',
                default => "Ce code promo n'est pas valide.",
            };

            throw ValidationException::withMessages(['code_promo' => $message]);
        }

        if (! array_key_exists((int) $codePromo->tarif_id, $demandes)) {
            throw ValidationException::withMessages([
                'code_promo' => "Ce code promo ne s'applique à aucun des billets choisis.",
            ]);
        }

        return $codePromo;
    }

    private function verifierDisponibilite(?Evenement $evenement, ?Tarif $tarif, int $quantite): void
    {
        if (! $evenement || ! $tarif) {
            throw ValidationException::withMessages([
                'lignes' => "Le tarif sélectionné n'est pas disponible pour cet événement.",
            ]);
        }

        $placesRestantes = $evenement->placesRestantesPourTarif($tarif);

        if ($placesRestantes < $quantite) {
            throw ValidationException::withMessages([
                'lignes' => "Places restantes insuffisantes pour « {$tarif->nom} ». Il ne reste que {$placesRestantes} place(s).",
            ]);
        }

        if ($tarif->quantite_disponible !== null) {
            $stock = (int) $tarif->quantite_disponible
                - (int) $tarif->quantite_vendue
                - $this->quantiteReservee($tarif);

            if ($stock < $quantite) {
                $stock = max(0, $stock);

                throw ValidationException::withMessages([
                    'lignes' => "Quantité restante insuffisante pour « {$tarif->nom} ». Il n'en reste que {$stock}.",
                ]);
            }
        }
    }

    private function quantiteReservee(Tarif $tarif): int
    {
        return (int) Ticket::query()
            ->where('tarif_id', $tarif->id)
            ->where('statut_paiement', TicketPaymentStatus::Pending->value)
            ->whereNull('expire_at')
            ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
            ->whereNotNull('reservation_expire_le')
            ->sum('quantite');
    }

    private function empreinteDemande(array $data, array $demandes): string
    {
        ksort($demandes);

        return hash('sha256', json_encode([
            'nom_acheteur' => trim((string) ($data['nom_acheteur'] ?? '')),
            'email_acheteur' => strtolower(trim((string) ($data['email_acheteur'] ?? ''))),
            'whatsapp_acheteur' => trim((string) ($data['whatsapp_acheteur'] ?? '')),
            'lignes' => $demandes,
            'methode_paiement' => $this->sales->normaliserMethode($data['methode_paiement'] ?? null),
            'code_promo' => strtoupper(trim((string) ($data['code_promo'] ?? ''))),
        ]));
    }

    private function venteDejaCreee(AgentVente $agent, string $cle, string $empreinte, Evenement $evenement): ?array
    {
        $trace = VenteIdempotence::query()
            ->where('agent_vente_id', $agent->id)
            ->where('cle', $cle)
            ->first();

        if (! $trace) {
            return null;
        }

        if (! hash_equals($trace->empreinte, $empreinte)) {
            throw ValidationException::withMessages([
                'idempotency_key' => "Cette clé d'idempotence a déjà servi pour une autre vente. Utilisez une nouvelle clé.",
            ]);
        }

        $existants = $agent->tickets()
            ->where('transaction_id', $trace->transaction_id)
            ->with(['tarif', 'evenement'])
            ->orderBy('id')
            ->get();

        $premier = $existants->first();

        if (! $premier) {
            $trace->delete();

            return null;
        }

        return [
            'tickets' => $existants,
            'ticket' => $premier,
            'statut' => $premier->statut_paiement,
            'montant_total' => (float) $existants->sum('montant'),
            'transaction_id' => $premier->transaction_id,
            'payment' => $premier->statut_paiement === TicketPaymentStatus::Pending->value
                ? $this->sales->checkoutPayload($existants, $evenement)
                : null,
            'idempotent' => true,
        ];
    }

    private function journaliser(
        Ticket $ticket,
        AgentVente $agent,
        string $action,
        ?string $statutAvant,
        ?string $statutApres,
        ?string $ip = null,
        ?string $userAgent = null,
        array $details = []
    ): void {
        JournalAction::enregistrer([
            'ticket_id' => $ticket->id,
            'agent_vente_id' => $agent->id,
            'evenement_id' => $ticket->evenement_id,
            'action' => $action,
            'statut_avant' => $statutAvant,
            'statut_apres' => $statutApres,
            'reference' => $ticket->transaction_id,
            'details' => $details,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    private function messageBlocage(string $raison): string
    {
        return match ($raison) {
            'event_cancelled' => "L'événement a été annulé. Les ventes sont fermées.",
            'event_suspended' => "L'événement est suspendu. Les ventes sont fermées.",
            'event_draft' => "L'événement n'est pas encore publié. Les ventes sont fermées.",
            'event_ended' => "L'événement est terminé. Les ventes sont fermées.",
            'sales_not_open' => "La billetterie de cet événement n'est pas encore ouverte.",
            'sales_closed' => "L'organisateur a fermé les ventes pour cet événement.",
            'event_full' => "L'événement est complet. Les ventes sont fermées.",
            default => 'Les ventes sont fermées pour cet événement.',
        };
    }

    private function montantLabel(float $montant): string
    {
        return number_format($montant, 0, ',', ' ').' FCFA';
    }
}
