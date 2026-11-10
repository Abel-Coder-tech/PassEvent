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
use App\Services\PaymentMonitoringService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AgentVenteSaleService
{
    // Durée pendant laquelle une vente mobile en attente garde sa place réservée,
    // alignée sur l'expiration des ventes en attente (30 minutes).
    public const DUREE_RESERVATION_MINUTES = 30;

    public function __construct(
        private FedapayService $fedapay,
        private PaymentMonitoringService $paymentMonitoring,
    ) {}

    // Marque comme expirées les ventes mobiles restées en attente depuis plus
    // de 30 minutes, sans les supprimer : elles restent consultables dans
    // l'historique et une trace est écrite dans journal_actions.
    public function expirePending(AgentVente $agent, ?string $ip = null, ?string $userAgent = null): int
    {
        $tickets = $agent->tickets()
            ->where('statut_paiement', TicketPaymentStatus::Pending->value)
            ->whereNull('expire_at')
            ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
            ->where('date_achat', '<', now()->subMinutes(self::DUREE_RESERVATION_MINUTES))
            ->get();

        $expires = 0;

        foreach ($tickets->groupBy('transaction_id') as $groupe) {
            foreach ($groupe as $ticket) {
                $this->libererReservation($ticket);
                $ticket->update(['expire_at' => now()]);
                $this->journaliser($ticket, $agent, 'vente_expiree', 'en_attente', 'expire', $ip, $userAgent);
                $expires++;
            }

            $premier = $groupe->first();

            AgentVenteNotification::envoyer(
                $agent,
                'Vente expirée',
                "La vente {$premier->referenceAffichage()} a expiré après ".self::DUREE_RESERVATION_MINUTES." minutes sans paiement.",
                $premier->evenement_id,
                AgentVenteNotification::TYPE_EXPIRATION,
                ['ticket_id' => $premier->id, 'transaction_id' => $premier->transaction_id]
            );
        }

        return $expires;
    }

    // État du déblocage des ventes espèces (seuil 15 % de la capacité)
    public function cashAvailability(Evenement $evenement): array
    {
        $seuil = $evenement->gratuit
            ? 0
            : (int) ceil($evenement->capacite * Evenement::SEUIL_ESPECES_PCT / 100);
        $vendus = $evenement->ticketsEnLigneCount();

        return [
            'disponible' => $evenement->ventesEspecesActivees(),
            'bloque_superadmin' => $evenement->ventesEspecesBloqueesSuperadmin(),
            'seuil' => $seuil,
            'vendus_en_ligne' => $vendus,
            'restants' => max(0, $seuil - $vendus),
            'progression_pct' => $seuil > 0
                ? min(100, round($vendus / $seuil * 100, 1))
                : 100.0,
        ];
    }

    public function tarifs(Evenement $evenement): array
    {
        return $evenement->tarifs()
            ->where('statut', 'actif')
            ->orderBy('prix')
            ->get()
            ->map(fn (Tarif $tarif) => $this->tarifPayload($tarif))
            ->values()
            ->all();
    }

    public function tarifPayload(Tarif $tarif): array
    {
        // Les ventes en attente n'ont pas encore de tarif comptabilisé mais
        // bloquent déjà du stock : on les retire des places affichées.
        $restantes = $tarif->quantite_disponible === null
            ? null
            : max(0, (int) $tarif->quantite_disponible
                - (int) $tarif->quantite_vendue
                - $this->quantiteReservee($tarif));

        return [
            'id' => $tarif->id,
            'nom' => $tarif->getLabel(),
            'prix' => (float) $tarif->prix,
            'prix_label' => (float) $tarif->prix > 0
                ? number_format((float) $tarif->prix, 0, ',', ' ').' FCFA'
                : 'Gratuit',
            'quantite_disponible' => $tarif->quantite_disponible,
            'quantite_vendue' => (int) $tarif->quantite_vendue,
            'restantes' => $restantes,
            'epuise' => $restantes !== null && $restantes <= 0,
        ];
    }

    // Informations de paiement FedaPay à transmettre au mobile
    public function paymentConfig(): array
    {
        $publicKey = $this->fedapay->getPublicKey();

        return [
            'disponible' => $publicKey !== null && $publicKey !== '',
            'provider' => 'fedapay',
            'public_key' => $publicKey,
            'sandbox' => $this->fedapay->isSandbox(),
            'devise' => 'XOF',
        ];
    }

    public function normaliserMethode(?string $methode): ?string
    {
        return match (strtolower(trim((string) $methode))) {
            'cash', 'especes', 'espèces' => 'cash',
            'mobile', 'mobile_money', 'mobile-money' => 'mobile_money',
            default => null,
        };
    }

    /**
     * Crée une vente (espèces confirmée immédiatement, mobile money en attente).
     *
     * @return array{tickets: Collection, ticket: Ticket, statut: string, montant_total: float, payment: ?array}
     */
    public function create(AgentVente $agent, array $data, ?string $ip = null, ?string $userAgent = null): array
    {
        // Rechargement explicite : l'état des ventes peut avoir changé depuis
        // le chargement initial de la relation (fermeture organisateur, statut).
        $evenement = $agent->evenement()->first() ?? $agent->evenement;

        if (! $evenement) {
            throw ValidationException::withMessages([
                'evenement' => "Aucun événement n'est associé à votre compte.",
            ]);
        }

        // Idempotence : une même clé renvoie la vente déjà créée au lieu
        // de générer des billets en double (retry réseau, double tap, ...).
        $idempotencyKey = trim((string) ($data['idempotency_key'] ?? ''));
        $empreinte = $this->empreinteDemande($data);

        if ($idempotencyKey !== '' && ($existante = $this->venteDejaCreee($agent, $idempotencyKey, $empreinte, $evenement)) !== null) {
            return $existante;
        }

        // Blocage organisateur, statut de l'événement, dernière date réelle
        // et capacité : la vente est refusée dans tous ces cas.
        if ($raison = $evenement->raisonBlocageVente()) {
            throw ValidationException::withMessages([
                'evenement' => $this->messageBlocage($raison),
            ]);
        }

        $quantite = max(1, (int) ($data['quantite'] ?? 1));
        $methode = $evenement->gratuit
            ? 'cash'
            : $this->normaliserMethode($data['methode_paiement'] ?? null);

        if (! $evenement->gratuit && ! in_array($methode, ['cash', 'mobile_money'], true)) {
            throw ValidationException::withMessages([
                'methode_paiement' => 'Méthode de paiement invalide. Choisissez espèces ou mobile money.',
            ]);
        }

        $tarif = $evenement->tarifs()->where('statut', 'actif')->find($data['tarif_id'] ?? null);

        if (! $tarif && $evenement->gratuit && empty($data['tarif_id'])) {
            $tarif = $evenement->tarifs()->where('statut', 'actif')->first();

            if (! $tarif) {
                $tarif = Tarif::create([
                    'evenement_id' => $evenement->id,
                    'nom' => 'Gratuit',
                    'prix' => 0,
                    'quantite_disponible' => $evenement->capacite,
                    'quantite_vendue' => 0,
                    'statut' => 'actif',
                ]);
            }
        }

        if (! $tarif) {
            throw ValidationException::withMessages([
                'tarif_id' => "Le tarif sélectionné n'est pas disponible pour cet événement.",
            ]);
        }

        try {
            $this->verifierDisponibilites($evenement, $tarif, $quantite);
        } catch (ValidationException $exception) {
            // Une requête concurrente a pu consommer la dernière place : si la
            // vente demandée existe déjà, on la renvoie plutôt que d'échouer.
            if ($idempotencyKey !== '' && ($existante = $this->venteDejaCreee($agent, $idempotencyKey, $empreinte, $evenement)) !== null) {
                return $existante;
            }

            throw $exception;
        }

        // Code promo (comptabilisé à la confirmation de la vente)
        $codePromo = null;
        $montantReduction = 0;
        $prixUnitaire = (float) $tarif->prix;

        if (! $evenement->gratuit && filled($data['code_promo'] ?? null)) {
            $codePromo = CodePromo::validerPour($data['code_promo'], $evenement, $tarif);

            if ($codePromo) {
                $montantReduction = $codePromo->calculerReduction((float) $tarif->prix);
                $prixUnitaire = max(0, (float) $tarif->prix - $montantReduction);
            }
        }

        if ($evenement->gratuit) {
            $prixUnitaire = 0;
            $montantReduction = 0;
            $codePromo = null;
        }

        $montantTotal = round($prixUnitaire * $quantite, 2);

        // Blocage espèces si le seuil mobile money n'est pas atteint
        if ($methode === 'cash' && $montantTotal > 0 && ! $evenement->ventesEspecesActivees()) {
            if ($evenement->ventesEspecesBloqueesSuperadmin()) {
                throw ValidationException::withMessages([
                    'methode_paiement' => 'Les ventes espèces sont actuellement désactivées pour cet événement. Utilisez le mobile money.',
                ]);
            }

            $cash = $this->cashAvailability($evenement);

            throw ValidationException::withMessages([
                'methode_paiement' => "Ventes espèces bloquées. Vendez d'abord {$cash['seuil']} ticket(s) en ligne. Progression : {$cash['vendus_en_ligne']}/{$cash['seuil']}.",
            ]);
        }

        if ($methode === 'mobile_money' && $montantTotal > 0 && ! $this->paymentConfig()['disponible']) {
            throw ValidationException::withMessages([
                'methode_paiement' => 'Le paiement mobile est temporairement indisponible. Utilisez les espèces.',
            ]);
        }

        // Un billet à 0 FCFA (événement gratuit ou promo à 100 %) est confirmé immédiatement
        $confirmerImmediatement = $methode === 'cash' || $montantTotal <= 0;

        // Une vente mobile en attente réserve sa place dès sa création : la
        // capacité est comptée dans quota_vendu et libérée si le paiement
        // n'aboutit pas (expiration ou annulation).
        $reservationExpireLe = $confirmerImmediatement
            ? null
            : now()->addMinutes(self::DUREE_RESERVATION_MINUTES);

        $transactionId = $confirmerImmediatement
            ? 'AGENT-'.strtoupper(Str::random(10))
            : 'GRP-'.strtoupper(Str::random(16));

        try {
            $result = DB::transaction(function () use (
                $agent, $evenement, $tarif, $quantite, $prixUnitaire,
                $montantReduction, $codePromo, $montantTotal, $confirmerImmediatement, $data,
                $idempotencyKey, $empreinte, $transactionId, $reservationExpireLe
            ) {
                // Réclamation atomique de la clé d'idempotence avant toute écriture :
                // en cas de doublon concurrent, l'index unique fait échouer la
                // transaction entière, puis la vente déjà enregistrée est relue.
                if ($idempotencyKey !== '') {
                    VenteIdempotence::create([
                        'agent_vente_id' => $agent->id,
                        'cle' => $idempotencyKey,
                        'empreinte' => $empreinte,
                        'transaction_id' => $transactionId,
                    ]);
                }

                // Re-vérification sous verrou pour éviter la survente en cas de ventes concurrentes
                $evenementVerrouille = Evenement::whereKey($evenement->id)->lockForUpdate()->first();
                $tarifVerrouille = Tarif::whereKey($tarif->id)->lockForUpdate()->first();
                $this->verifierDisponibilites($evenementVerrouille, $tarifVerrouille, $quantite);

                $tickets = new Collection;

                for ($i = 0; $i < $quantite; $i++) {
                    $tickets->push(Ticket::create([
                        'evenement_id' => $evenement->id,
                        'tarif_id' => $tarif->id,
                        'agent_vente_id' => $agent->id,
                        'source' => 'agent_vente',
                        'code_unique' => $confirmerImmediatement
                            ? Ticket::genererCodeSecurise()
                            : Ticket::genererCodeReservation(),
                        'qr_signature' => Str::uuid()->toString(),
                        'email_acheteur' => $data['email_acheteur'],
                        'whatsapp_acheteur' => $data['whatsapp_acheteur'] ?? null,
                        'nom_acheteur' => $data['nom_acheteur'],
                        'nom_tarif' => $tarif->nom ?? 'Standard',
                        'montant' => $prixUnitaire,
                        'montant_reduction' => $montantReduction,
                        'code_promo_utilise' => $codePromo?->code,
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

                // Toute vente consomme une place : réservation immédiate pour le
                // mobile money, consommation définitive pour les espèces.
                $evenementVerrouille->increment('quota_vendu', $quantite);

                if ($confirmerImmediatement) {
                    $tarifVerrouille->increment('quantite_vendue', $quantite);

                    if ($codePromo) {
                        $codePromo->increment('nb_utilisations');
                    }

                    $agent->increment('tickets_count', $quantite);
                    $agent->increment('montant_total', $montantTotal);

                    foreach ($tickets as $ticket) {
                        Log::create([
                            'ticket_id' => $ticket->id,
                            'type_operation' => LogOperationType::Purchase->value,
                            'details' => [
                                'transaction_id' => $transactionId,
                                'methode' => 'cash',
                                'agent_vente' => true,
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
            // Deux requêtes concurrentes avec la même clé : la perdante relit
            // la vente déjà enregistrée par la gagnante.
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
            $result['statut'] === 'payé' ? 'Ticket vendu' : 'Vente enregistrée',
            $result['statut'] === 'payé'
                ? "Le billet {$result['ticket']->referenceAffichage()} a été vendu ({$result['ticket']->montant} FCFA)."
                : "La vente {$result['ticket']->referenceAffichage()} est en attente du paiement mobile.",
            $evenement->id,
            $result['statut'] === 'payé' ? AgentVenteNotification::TYPE_VENTE : AgentVenteNotification::TYPE_PAIEMENT,
            [
                'ticket_id' => $result['ticket']->id,
                'transaction_id' => $result['transaction_id'],
                'montant' => (float) $result['montant_total'],
                'statut' => $statutVente,
            ]
        );

        return [
            ...$result,
            'idempotent' => false,
            'payment' => $confirmerImmediatement
                ? null
                : $this->checkoutPayload($result['tickets'], $evenement),
        ];
    }

    /**
     * Renvoie une vente déjà enregistrée pour cette clé d'idempotence.
     *
     * @return array{tickets: Collection, ticket: Ticket, statut: string, montant_total: float, transaction_id: string, payment: ?array, idempotent: bool}|null
     */
    private function venteDejaCreee(AgentVente $agent, string $cle, string $empreinte, Evenement $evenement): ?array
    {
        $trace = VenteIdempotence::query()
            ->where('agent_vente_id', $agent->id)
            ->where('cle', $cle)
            ->first();

        if (! $trace) {
            return null;
        }

        // Une clé ne doit jamais servir à deux ventes différentes : le client
        // a réutilisé sa clé pour une autre commande.
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
            // Trace orpheline (vente supprimée manuellement) : on repart proprement.
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
                ? $this->checkoutPayload($existants, $evenement)
                : null,
            'idempotent' => true,
        ];
    }

    // Empreinte normalisée de la demande : sert à détecter la réutilisation
    // d'une même clé pour un contenu différent.
    private function empreinteDemande(array $data): string
    {
        return hash('sha256', json_encode([
            'nom_acheteur' => trim((string) ($data['nom_acheteur'] ?? '')),
            'email_acheteur' => strtolower(trim((string) ($data['email_acheteur'] ?? ''))),
            'whatsapp_acheteur' => trim((string) ($data['whatsapp_acheteur'] ?? '')),
            'tarif_id' => $data['tarif_id'] ?? null,
            'methode_paiement' => $this->normaliserMethode($data['methode_paiement'] ?? null),
            'code_promo' => strtoupper(trim((string) ($data['code_promo'] ?? ''))),
            'quantite' => max(1, (int) ($data['quantite'] ?? 1)),
        ]));
    }

    private function messageBlocage(string $raison): string
    {
        return match ($raison) {
            'event_cancelled' => "L'événement a été annulé. Les ventes sont fermées.",
            'event_suspended' => "L'événement est suspendu. Les ventes sont fermées.",
            'event_draft' => "L'événement n'est pas encore publié. Les ventes sont fermées.",
            'event_ended' => "L'événement est terminé. Les ventes sont fermées.",
            'sales_closed' => "L'organisateur a fermé les ventes pour cet événement.",
            'event_full' => "L'événement est complet. Les ventes sont fermées.",
            default => "Les ventes sont fermées pour cet événement.",
        };
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

    // Vérifie les places restantes de l'événement et du tarif
    private function verifierDisponibilites(?Evenement $evenement, ?Tarif $tarif, int $quantite): void
    {
        if (! $evenement || ! $tarif) {
            throw ValidationException::withMessages([
                'tarif_id' => "Le tarif sélectionné n'est pas disponible pour cet événement.",
            ]);
        }

        $placesRestantes = max(0, (int) $evenement->capacite - (int) $evenement->quota_vendu);

        if ($placesRestantes < $quantite) {
            throw ValidationException::withMessages([
                'quantite' => "Places restantes insuffisantes. Il ne reste que {$placesRestantes} place(s) disponible(s).",
            ]);
        }

        if ($tarif->quantite_disponible !== null) {
            // Les ventes en attente ont réservé la capacité mais leur tarif n'est
            // comptabilisé qu'à la confirmation : on les déduit du stock pour
            // éviter de vendre deux fois la même unité de tarif.
            $stock = (int) $tarif->quantite_disponible
                - (int) $tarif->quantite_vendue
                - $this->quantiteReservee($tarif);

            if ($stock < $quantite) {
                $stock = max(0, $stock);

                throw ValidationException::withMessages([
                    'quantite' => "Quantité restante insuffisante pour ce tarif. Il n'en reste que {$stock}.",
                ]);
            }
        }
    }

    // Somme des places déjà réservées par des ventes en attente pour ce tarif.
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

    // Libère la place réservée par une vente en attente, une seule fois.
    private function libererReservation(Ticket $ticket): bool
    {
        return DB::transaction(function () use ($ticket) {
            $locked = Ticket::whereKey($ticket->id)->lockForUpdate()->first();

            if (! $locked
                || $locked->statut_paiement !== TicketPaymentStatus::Pending->value
                || $locked->reservation_expire_le === null) {
                return false;
            }

            $evenement = $locked->evenement()->lockForUpdate()->first();

            if ($evenement) {
                $nouveauQuota = max(0, (int) $evenement->quota_vendu - max(1, (int) $locked->quantite));
                $evenement->update(['quota_vendu' => $nouveauQuota]);
            }

            $locked->update(['reservation_expire_le' => null]);

            return true;
        });
    }

    // Données de checkout FedaPay renvoyées à l'application mobile
    public function checkoutPayload(Collection $tickets, Evenement $evenement): array
    {
        $ticket = $tickets->first();
        $montant = (float) $tickets->sum('montant');
        $config = $this->paymentConfig();

        return [
            ...$config,
            'montant' => $montant,
            'description' => 'Ticket - '.$evenement->titre,
            'external_id' => (string) $ticket->id,
            'merchant_reference' => (string) $ticket->id,
            'transaction_id' => $ticket->transaction_id,
            'custom_metadata' => [
                'ticket_id' => (string) $ticket->id,
                'group_transaction_id' => (string) $ticket->transaction_id,
            ],
            'customer' => [
                'email' => $ticket->email_acheteur,
                'firstname' => $this->prenomAcheteur($ticket->nom_acheteur),
                'lastname' => $this->nomAcheteur($ticket->nom_acheteur),
                'phone' => $ticket->whatsapp_acheteur,
            ],
            'callback_url' => route('paiement.callback', [
                'ticket' => $ticket->id,
                'source' => 'agent_vente',
            ]),
            'confirm_url' => route('agent-vente.api.sales.confirm', ['ticket' => $ticket->id]),
            'statut_url' => route('agent-vente.api.sales.show', ['ticket' => $ticket->id]),
        ];
    }

    private function prenomAcheteur(?string $nom): string
    {
        $parts = preg_split('/\s+/', trim((string) $nom)) ?: [];

        return count($parts) > 1
            ? implode(' ', array_slice($parts, 0, -1))
            : ($parts[0] ?? 'Client');
    }

    private function nomAcheteur(?string $nom): string
    {
        $parts = preg_split('/\s+/', trim((string) $nom)) ?: [];

        return count($parts) > 1 ? end($parts) : '';
    }

    public function groupTickets(Ticket $ticket, ?AgentVente $agent = null): Collection
    {
        // Après un passage par le callback/webhook FedaPay, la référence du groupe
        // peut avoir été remplacée par l'identifiant de transaction FedaPay.
        // Tous les billets d'une même vente d'agent partagent la même référence,
        // et le groupe est toujours restreint à l'agent propriétaire.
        $query = Ticket::query()->with('tarif');

        if ($agent !== null) {
            $query->where('agent_vente_id', $agent->id);
        }

        if ($ticket->transaction_id) {
            $query->where('transaction_id', $ticket->transaction_id);
        } else {
            $query->whereKey($ticket->id);
        }

        return $query->orderBy('id')->get();
    }

    // Vérifie qu'un ticket appartient bien à l'agent connecté
    public function ticketDeAgent(Ticket $ticket, AgentVente $agent): Ticket
    {
        if ((int) $ticket->agent_vente_id !== (int) $agent->id) {
            throw new AccessDeniedHttpException('Ce billet ne vous appartient pas.');
        }

        return $ticket;
    }

    public function ticketPayload(Ticket $ticket, ?AgentVente $agent = null): array
    {
        $ticket->loadMissing(['tarif', 'evenement']);

        $max = (int) config('app.max_downloads', 3);
        $downloadCount = (int) ($ticket->download_count ?? 0);
        $annule = (bool) $ticket->annule || $ticket->annule_at !== null;
        $expire = $ticket->expire_at !== null;
        $paye = $ticket->statut_paiement === TicketPaymentStatus::Paid->value && ! $annule && ! $expire;

        $statutVente = match (true) {
            $annule => 'annule',
            $expire => 'expire',
            $ticket->statut_paiement === TicketPaymentStatus::Paid->value => 'paye',
            $ticket->statut_paiement === 'remboursé' => 'rembourse',
            $ticket->statut_paiement === 'échoué' => 'echoue',
            default => 'en_attente',
        };

        return [
            'id' => $ticket->id,
            'code_unique' => $ticket->aUnCodeDefinitif() ? $ticket->code_unique : null,
            'reference' => $ticket->referenceAffichage(),
            'nom_acheteur' => $ticket->nom_acheteur,
            'email_acheteur' => $ticket->email_acheteur,
            'whatsapp_acheteur' => $ticket->whatsapp_acheteur,
            'tarif_id' => $ticket->tarif_id,
            'tarif' => $ticket->tarif?->getLabel() ?? $ticket->nom_tarif ?? 'Standard',
            'quantite' => (int) ($ticket->quantite ?? 1),
            'montant' => (float) $ticket->montant,
            'montant_label' => (float) $ticket->montant > 0
                ? number_format((float) $ticket->montant, 0, ',', ' ').' FCFA'
                : 'Gratuit',
            'montant_reduction' => (float) ($ticket->montant_reduction ?? 0),
            'code_promo' => $ticket->code_promo_utilise,
            'devise' => 'XOF',
            'methode_paiement' => $ticket->methode_paiement,
            'methode_label' => Ticket::methodePaiementLabel($ticket->methode_paiement, $ticket->type_paiement),
            'type_paiement' => $ticket->type_paiement,
            'statut_paiement' => $ticket->statut_paiement,
            'statut_vente' => $statutVente,
            'paye' => $paye,
            'annule' => $annule,
            'annule_at' => $ticket->annule_at?->toIso8601String(),
            'expire' => $expire,
            'expire_at' => $ticket->expire_at?->toIso8601String(),
            'transaction_id' => $ticket->transaction_id,
            'date_achat' => $ticket->date_achat?->toIso8601String(),
            'telechargeable' => $paye && $downloadCount < $max,
            'peut_renvoyer' => $paye && filter_var((string) $ticket->email_acheteur, FILTER_VALIDATE_EMAIL) !== false,
            'pdf_url' => $paye
                ? route('agent-vente.api.sales.pdf', ['ticket' => $ticket->id])
                : null,
            'download_count' => $downloadCount,
            'max_downloads' => $max,
        ];
    }

    // Confirme un paiement mobile en vérifiant la transaction auprès de FedaPay
    public function confirmMobile(
        AgentVente $agent,
        Ticket $ticket,
        string $transactionId,
        ?string $paymentMethod = null,
        ?string $phone = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): array {
        $this->ticketDeAgent($ticket, $agent);
        $groupTickets = $this->groupTickets($ticket, $agent);

        if ($this->groupePaye($groupTickets)) {
            return $this->reponseGroupe($agent, $groupTickets, true, true);
        }

        if ($ticket->annule || $ticket->expire_at !== null) {
            throw ValidationException::withMessages([
                'statut' => 'Cette vente est annulée ou expirée. Elle ne peut plus être confirmée.',
            ]);
        }

        if ($ticket->statut_paiement !== TicketPaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'statut' => 'Cette vente ne peut plus être confirmée.',
            ]);
        }

        $txData = $this->fedapay->getTransaction($transactionId);

        if (! is_array($txData)) {
            throw ValidationException::withMessages([
                'transaction_id' => 'Impossible de vérifier la transaction auprès de FedaPay.',
            ]);
        }

        return $this->confirmerDepuisTransaction(
            $agent,
            $ticket,
            $groupTickets,
            $txData,
            $transactionId,
            $paymentMethod,
            $phone,
            $ip,
            $userAgent
        );
    }

    // Crée une transaction FedaPay côté serveur et renvoie une URL de paiement
    public function createPayment(AgentVente $agent, Ticket $ticket, ?string $ip = null, ?string $userAgent = null): array
    {
        $this->ticketDeAgent($ticket, $agent);
        $groupTickets = $this->groupTickets($ticket, $agent);

        if ($this->groupePaye($groupTickets)) {
            return $this->reponseGroupe($agent, $groupTickets, true, true);
        }

        if ($ticket->annule || $ticket->expire_at !== null) {
            throw ValidationException::withMessages([
                'statut' => 'Cette vente est annulée ou expirée.',
            ]);
        }

        $pending = $groupTickets
            ->filter(fn (Ticket $t) => $t->statut_paiement === TicketPaymentStatus::Pending->value
                && ! $t->annule
                && $t->expire_at === null)
            ->values();

        if ($pending->isEmpty()) {
            throw ValidationException::withMessages([
                'statut' => 'Aucun billet en attente de paiement pour cette vente.',
            ]);
        }

        $montant = (float) $pending->sum('montant');

        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Cette vente ne nécessite pas de paiement.',
            ]);
        }

        if (! $this->paymentConfig()['disponible'] || ! config('services.fedapay.secret_key')) {
            throw ValidationException::withMessages([
                'paiement_mobile' => 'Le paiement mobile est temporairement indisponible.',
            ]);
        }

        $premier = $pending->first();

        $txData = null;

        // FedaPay impose un `merchant_reference` unique par transaction, et nous
        // utilisons l'id du premier billet comme référence. Recréer une transaction
        // pour le même billet échoue donc à chaque reprise (réouverture du checkout,
        // réponse perdue...). On reprend la transaction existante et on régénère
        // simplement son lien de paiement.
        $fedapayIdExistant = trim((string) $groupTickets
            ->first(fn (Ticket $t) => filled($t->fedapay_transaction_id))
            ?->fedapay_transaction_id);

        $existante = $fedapayIdExistant !== ''
            ? $this->fedapay->getTransaction($fedapayIdExistant)
            : $this->fedapay->findTransactionByExternalId((string) $premier->id);

        if (is_array($existante) && ! empty($existante['id'])) {
            $statutExistant = strtolower(trim((string) ($existante['status'] ?? '')));

            if (in_array($statutExistant, ['approved', 'completed', 'accepted'], true)) {
                // Paiement déjà validé chez FedaPay mais webhook non reçu :
                // on confirme la vente au lieu de renvoyer un checkout mort.
                return $this->confirmerDepuisTransaction(
                    $agent,
                    $ticket,
                    $groupTickets,
                    $existante,
                    (string) $existante['id'],
                    null,
                    null,
                    $ip,
                    $userAgent
                );
            }

            if (in_array($statutExistant, ['canceled', 'cancelled', 'declined', 'failed', 'expired'], true)) {
                throw ValidationException::withMessages([
                    'paiement_mobile' => 'La transaction FedaPay précédente a échoué. Annulez cette vente et créez-en une nouvelle.',
                ]);
            }

            $montantExistant = (int) round((float) ($existante['amount'] ?? 0));

            if ($montantExistant > 0 && $montantExistant !== (int) round($montant)) {
                throw ValidationException::withMessages([
                    'montant' => 'Le montant de cette vente a changé. Annulez cette vente et créez-en une nouvelle.',
                ]);
            }

            $tokenExistant = $this->fedapay->generateToken((string) $existante['id']);
            $lienExistant = is_array($tokenExistant)
                ? ($tokenExistant['url'] ?? $tokenExistant['payment_url'] ?? null)
                : null;

            if (is_string($lienExistant) && $lienExistant !== '') {
                $existante['payment_url'] = $lienExistant;
                $existante['token'] = $tokenExistant['token'] ?? null;
                $txData = $existante;
            }
        }

        if (! is_array($txData)) {
            $customer = array_filter([
                'firstname' => $this->prenomAcheteur($premier->nom_acheteur),
                'lastname' => $this->nomAcheteur($premier->nom_acheteur),
                'email' => $premier->email_acheteur,
                'phone' => $premier->whatsapp_acheteur,
                'phone_number' => $premier->whatsapp_acheteur,
            ], fn ($value) => $value !== null && $value !== '');

            try {
                $txData = $this->fedapay->createTransaction([
                    'description' => 'Ticket - '.$ticket->evenement->titre,
                    'amount' => (int) round($montant),
                    'currency' => ['iso' => 'XOF'],
                    'callback_url' => route('paiement.callback', [
                        'ticket' => $ticket->id,
                        'source' => 'agent_vente',
                    ]),
                    'customer' => $customer,
                    'custom_metadata' => [
                        'ticket_id' => (string) $premier->id,
                        'group_transaction_id' => (string) $premier->transaction_id,
                    ],
                    'merchant_reference' => (string) $premier->id,
                ]);
            } catch (\Throwable $e) {
                $txData = null;
            }
        }

        if (! is_array($txData)) {
            throw ValidationException::withMessages([
                'paiement_mobile' => 'FedaPay est injoignable. Réessayez dans un instant.',
            ]);
        }

        $paymentUrl = $txData['payment_url'] ?? $txData['checkout_url'] ?? $txData['url'] ?? null;

        if (! $paymentUrl) {
            throw ValidationException::withMessages([
                'paiement_mobile' => "FedaPay n'a pas renvoyé de lien de paiement.",
            ]);
        }

        $fedapayId = trim((string) ($txData['id'] ?? $txData['transaction_id'] ?? ''));

        if ($fedapayId !== '') {
            foreach ($pending as $item) {
                $item->update(['fedapay_transaction_id' => $fedapayId]);
            }
        }

        foreach ($pending as $item) {
            $this->journaliser($item, $agent, 'paiement_initie', 'en_attente', 'en_attente', $ip, $userAgent, [
                'fedapay_transaction_id' => $fedapayId,
                'montant' => (float) $item->montant,
            ]);
        }

        return [
            'payment_url' => $paymentUrl,
            'token' => $txData['token'] ?? null,
            'fedapay_transaction_id' => $fedapayId !== '' ? $fedapayId : null,
            'transaction_id' => $premier->transaction_id,
            'montant' => $montant,
            'montant_label' => number_format($montant, 0, ',', ' ').' FCFA',
            'devise' => 'XOF',
        ];
    }

    // Resynchronise une vente mobile à partir de l'external_id / de l'ID FedaPay
    public function syncMobile(AgentVente $agent, Ticket $ticket, ?string $ip = null, ?string $userAgent = null): array
    {
        $this->ticketDeAgent($ticket, $agent);
        $groupTickets = $this->groupTickets($ticket, $agent);

        if ($this->groupePaye($groupTickets)) {
            return $this->reponseGroupe($agent, $groupTickets, true, true);
        }

        if ($ticket->annule || $ticket->expire_at !== null) {
            throw ValidationException::withMessages([
                'statut' => 'Cette vente est annulée ou expirée.',
            ]);
        }

        if ($ticket->statut_paiement !== TicketPaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'statut' => 'Cette vente ne peut plus être confirmée.',
            ]);
        }

        $fedapayId = trim((string) $ticket->fedapay_transaction_id);

        if ($fedapayId === '') {
            $fedapayId = trim((string) $groupTickets
                ->first(fn (Ticket $t) => filled($t->fedapay_transaction_id))
                ?->fedapay_transaction_id);
        }

        $txData = $fedapayId !== '' ? $this->fedapay->getTransaction($fedapayId) : null;

        if (! is_array($txData)) {
            $txData = $this->fedapay->findTransactionByExternalId((string) $ticket->id);
        }

        if (! is_array($txData)) {
            return [
                'confirme' => false,
                'deja_confirme' => false,
                'statut_fedapay' => null,
                'message' => "Aucune transaction FedaPay n'a été trouvée pour cette vente.",
                'tickets' => $groupTickets
                    ->map(fn (Ticket $t) => $this->ticketPayload($t, $agent))
                    ->values()
                    ->all(),
            ];
        }

        $status = $txData['status'] ?? null;

        if (! in_array($status, ['approved', 'completed', 'accepted'], true)) {
            return [
                'confirme' => false,
                'deja_confirme' => false,
                'statut_fedapay' => $status,
                'message' => "Le paiement n'a pas encore été confirmé par FedaPay.",
                'tickets' => $groupTickets
                    ->map(fn (Ticket $t) => $this->ticketPayload($t, $agent))
                    ->values()
                    ->all(),
            ];
        }

        return $this->confirmerDepuisTransaction(
            $agent,
            $ticket,
            $groupTickets,
            $txData,
            $fedapayId !== '' ? $fedapayId : null,
            null,
            null,
            $ip,
            $userAgent
        );
    }

    // Renvoie par email le ou les billets payés d'une vente
    public function resend(AgentVente $agent, Ticket $ticket, ?string $ip = null, ?string $userAgent = null): array
    {
        $this->ticketDeAgent($ticket, $agent);
        $groupTickets = $this->groupTickets($ticket, $agent);

        $aRenvoyer = $groupTickets
            ->filter(fn (Ticket $t) => $t->statut_paiement === TicketPaymentStatus::Paid->value
                && ! $t->annule
                && $t->expire_at === null)
            ->values();

        if ($aRenvoyer->isEmpty()) {
            throw ValidationException::withMessages([
                'ticket' => 'Aucun billet payé à renvoyer pour cette vente.',
            ]);
        }

        $email = trim((string) $ticket->email_acheteur);

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw ValidationException::withMessages([
                'email_acheteur' => "L'adresse email de l'acheteur est invalide.",
            ]);
        }

        SendTicketEmailJob::dispatchAfterResponse($aRenvoyer->pluck('id')->all(), $email);

        foreach ($aRenvoyer as $item) {
            $item->update([
                'dernier_renvoi_at' => now(),
                'renvoi_count' => (int) $item->renvoi_count + 1,
            ]);

            $this->journaliser($item, $agent, 'billet_renvoye', 'paye', 'paye', $ip, $userAgent, [
                'email' => $email,
                'renvoi_count' => (int) $item->renvoi_count,
            ]);
        }

        AgentVenteNotification::envoyer(
            $agent,
            'Billet renvoyé',
            $aRenvoyer->count() > 1
                ? "Les {$aRenvoyer->count()} billets ont été renvoyés à {$email}."
                : "Le billet {$ticket->code_unique} a été renvoyé à {$email}.",
            $ticket->evenement_id,
            AgentVenteNotification::TYPE_RENVOI,
            [
                'ticket_ids' => $aRenvoyer->pluck('id')->values()->all(),
                'email' => $email,
            ]
        );

        return [
            'email' => $email,
            'tickets_envoyes' => $aRenvoyer->count(),
            'ticket_ids' => $aRenvoyer->pluck('id')->values()->all(),
        ];
    }

    // Annule une vente mobile encore en attente sans supprimer les billets
    public function cancelPending(AgentVente $agent, Ticket $ticket, ?string $ip = null, ?string $userAgent = null): array
    {
        $this->ticketDeAgent($ticket, $agent);

        if ($ticket->statut_paiement !== TicketPaymentStatus::Pending->value
            || $ticket->annule
            || $ticket->expire_at !== null) {
            throw ValidationException::withMessages([
                'statut' => 'Seule une vente en attente de paiement peut être annulée.',
            ]);
        }

        $annules = 0;

        foreach ($this->groupTickets($ticket, $agent) as $item) {
            if ($item->statut_paiement === TicketPaymentStatus::Pending->value
                && ! $item->annule
                && $item->expire_at === null) {
                $this->libererReservation($item);
                $item->update(['annule' => true, 'annule_at' => now()]);
                $this->journaliser($item, $agent, 'vente_annulee', 'en_attente', 'annule', $ip, $userAgent);
                $annules++;
            }
        }

        if ($annules > 0) {
            AgentVenteNotification::envoyer(
                $agent,
                'Vente annulée',
                "La vente {$ticket->referenceAffichage()} a été annulée.",
                $ticket->evenement_id,
                AgentVenteNotification::TYPE_ANNULATION,
                ['ticket_id' => $ticket->id, 'tickets_annules' => $annules]
            );
        }

        return ['tickets_annules' => $annules];
    }

    // Vérifie le statut FedaPay, la liaison et le montant, puis confirme le groupe
    private function confirmerDepuisTransaction(
        AgentVente $agent,
        Ticket $ticket,
        Collection $groupTickets,
        array $txData,
        ?string $transactionId,
        ?string $paymentMethod,
        ?string $phone,
        ?string $ip,
        ?string $userAgent
    ): array {
        $status = $txData['status'] ?? null;
        $transactionId = trim((string) ($transactionId ?? ($txData['id'] ?? '')));

        if (! in_array($status, ['approved', 'completed', 'accepted'], true)) {
            if ($transactionId !== '') {
                foreach ($groupTickets as $pending) {
                    if ($pending->statut_paiement === TicketPaymentStatus::Pending->value) {
                        $pending->update(['fedapay_transaction_id' => $transactionId]);
                    }
                }
            }

            foreach ($groupTickets as $pending) {
                $this->journaliser($pending, $agent, 'paiement_echoue', 'en_attente', $status ?: 'inconnu', $ip, $userAgent, [
                    'fedapay_status' => $status,
                ]);
            }

            AgentVenteNotification::envoyer(
                $agent,
                'Paiement non confirmé',
                "Le paiement de la vente {$ticket->referenceAffichage()} n'a pas encore été confirmé (statut : ".($status ?: 'inconnu').').',
                $ticket->evenement_id,
                AgentVenteNotification::TYPE_PAIEMENT,
                ['ticket_id' => $ticket->id, 'statut' => $status]
            );

            throw ValidationException::withMessages([
                'transaction_id' => "Le paiement n'a pas encore été confirmé par FedaPay.",
            ]);
        }

        if (! $this->transactionLiee($txData, $ticket, $groupTickets)) {
            throw ValidationException::withMessages([
                'transaction_id' => 'Impossible de rattacher cette transaction à votre vente.',
            ]);
        }

        $montantAttendu = (float) $groupTickets->sum('montant');
        $montantTransaction = (float) ($txData['amount'] ?? 0);

        if ($montantTransaction <= 0 || abs($montantAttendu - $montantTransaction) >= 1) {
            throw ValidationException::withMessages([
                'transaction_id' => 'Le montant de la transaction ne correspond pas à votre vente.',
            ]);
        }

        $apiMethod = $txData['mode'] ?? null;

        if (! $apiMethod && isset($txData['payment_method'])) {
            $apiMethod = is_array($txData['payment_method'])
                ? ($txData['payment_method']['provider'] ?? $txData['payment_method']['name'] ?? null)
                : $txData['payment_method'];
        }

        $rawMethod = $apiMethod ?: $paymentMethod ?: 'mobile_money';
        $moyenPaiement = PaiementMapper::moyenPaiement($rawMethod);
        $operateur = PaiementMapper::operateur($rawMethod);

        $confirme = $this->confirmTickets(
            $groupTickets,
            $transactionId,
            $moyenPaiement,
            $operateur,
            $phone ?? ($txData['phone'] ?? $ticket->telephone_acheteur ?? $ticket->whatsapp_acheteur),
            $ip,
            $userAgent
        );

        if ($confirme) {
            // Recharge le billet : le code définitif vient d'être attribué par
            // TicketObserver, l'instance d'origine porte encore le code TMP-.
            $ticket->refresh();

            SendTicketEmailJob::dispatchAfterResponse(
                $groupTickets->pluck('id')->all(),
                $ticket->email_acheteur
            );

            AgentVenteNotification::envoyer(
                $agent,
                'Paiement confirmé',
                "Le paiement du billet {$ticket->referenceAffichage()} ({$ticket->montant} FCFA) a été confirmé.",
                $ticket->evenement_id,
                AgentVenteNotification::TYPE_PAIEMENT,
                ['ticket_id' => $ticket->id, 'transaction_id' => $transactionId]
            );
        }

        $groupTickets = Ticket::whereIn('id', $groupTickets->pluck('id'))
            ->with('tarif')
            ->orderBy('id')
            ->get();

        return [
            'confirme' => $confirme,
            'deja_confirme' => false,
            'statut_fedapay' => $status,
            'tickets' => $groupTickets
                ->map(fn (Ticket $t) => $this->ticketPayload($t, $agent))
                ->values()
                ->all(),
        ];
    }

    private function groupePaye(Collection $groupTickets): bool
    {
        return $groupTickets->isNotEmpty()
            && $groupTickets->every(fn (Ticket $t) => $t->statut_paiement === TicketPaymentStatus::Paid->value
                && ! $t->annule
                && $t->expire_at === null);
    }

    private function reponseGroupe(AgentVente $agent, Collection $groupTickets, bool $confirme, bool $dejaConfirme): array
    {
        return [
            'confirme' => $confirme,
            'deja_confirme' => $dejaConfirme,
            'tickets' => $groupTickets
                ->map(fn (Ticket $t) => $this->ticketPayload($t, $agent))
                ->values()
                ->all(),
        ];
    }

    // Vérifie la liaison entre la transaction FedaPay et le groupe de billets
    private function transactionLiee(?array $txData, Ticket $ticket, ?Collection $group = null): bool
    {
        if (! is_array($txData)) {
            return false;
        }

        $metadata = $txData['custom_metadata'] ?? [];

        if (! is_array($metadata)) {
            $metadata = [];
        }

        $metadataGroup = trim((string) ($metadata['group_transaction_id'] ?? ''));
        $metadataTicketId = trim((string) ($metadata['ticket_id'] ?? ''));
        $fedapayId = trim((string) ($txData['id'] ?? ''));
        $externalId = trim((string) ($txData['external_id'] ?? $txData['merchant_reference'] ?? ''));
        $transactionGroup = (string) $ticket->transaction_id;

        $ids = ($group ?? collect([$ticket]))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if ($externalId !== '' && in_array($externalId, $ids, true)) {
            return true;
        }

        if ($metadataTicketId !== '' && in_array($metadataTicketId, $ids, true)) {
            return true;
        }

        if ($metadataGroup !== '' && $transactionGroup !== '' && $metadataGroup === $transactionGroup) {
            return true;
        }

        if ($fedapayId !== '' && $transactionGroup !== '' && $fedapayId === $transactionGroup) {
            return true;
        }

        if ($fedapayId !== '' && ($group ?? collect())->contains(
            fn (Ticket $t) => (string) $t->fedapay_transaction_id === $fedapayId
        )) {
            return true;
        }

        return $metadataGroup !== '' && $fedapayId !== '' && $metadataGroup === $fedapayId;
    }

    // Confirme atomiquement un groupe de tickets et met à jour les compteurs
    private function confirmTickets(
        Collection $groupTickets,
        string $transactionId,
        string $moyenPaiement,
        ?string $operateur,
        ?string $telephonePaiement
    ): bool {
        return DB::transaction(function () use ($groupTickets, $transactionId, $moyenPaiement, $operateur, $telephonePaiement) {
            $confirme = false;
            $codePromoIncremente = false;
            $ticketsConfirmes = 0;
            $montantConfirme = 0.0;

            foreach ($groupTickets as $ticket) {
                $locked = Ticket::whereKey($ticket->id)->lockForUpdate()->first();

                if (! $locked || $locked->statut_paiement !== TicketPaymentStatus::Pending->value
                    || $locked->annule
                    || $locked->expire_at !== null) {
                    continue;
                }

                $evenement = $locked->evenement()->lockForUpdate()->first();

                if (! $evenement) {
                    continue;
                }

                // Une vente mobile a déjà réservé sa place à la création : on ne
                // recompte pas la capacité, on lève seulement la réservation.
                $reservationDejaComptee = $locked->reservation_expire_le !== null;

                if (! $reservationDejaComptee) {
                    $nouveauQuota = (int) $evenement->quota_vendu + (int) $locked->quantite;
                    $evenement->update(['quota_vendu' => min($nouveauQuota, (int) $evenement->capacite)]);
                }

                $locked->update([
                    'statut_paiement' => TicketPaymentStatus::Paid->value,
                    'fedapay_transaction_id' => $transactionId,
                    'methode_paiement' => $operateur ?? ($locked->methode_paiement ?: 'mobile_money'),
                    'type_paiement' => $moyenPaiement,
                    'telephone_paiement' => $telephonePaiement,
                    'reservation_expire_le' => null,
                ]);

                if ($locked->tarif) {
                    $tarif = $locked->tarif()->lockForUpdate()->first();

                    if ($tarif) {
                        $tarif->increment('quantite_vendue', (int) $locked->quantite);
                    }
                }

                if ($locked->code_promo_utilise && ! $codePromoIncremente) {
                    $codePromo = CodePromo::where('code', $locked->code_promo_utilise)->first();

                    if ($codePromo) {
                        $codePromo->increment('nb_utilisations');
                        $codePromoIncremente = true;
                    }
                }

                Log::create([
                    'ticket_id' => $locked->id,
                    'type_operation' => LogOperationType::Purchase->value,
                    'details' => [
                        'transaction_id' => $transactionId,
                        'methode' => 'fedapay',
                        'agent_vente' => true,
                    ],
                ]);

                $confirme = true;
                $ticketsConfirmes++;
                $montantConfirme += (float) $locked->montant;
            }

            if ($confirme) {
                $agentVenteId = $groupTickets->first()?->agent_vente_id;

                if ($agentVenteId) {
                    $agent = AgentVente::find($agentVenteId);

                    if ($agent) {
                        $agent->increment('tickets_count', $ticketsConfirmes);
                        $agent->increment('montant_total', $montantConfirme);
                    }
                }
            }

            return $confirme;
        });
    }
}
