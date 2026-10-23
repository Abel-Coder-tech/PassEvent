<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateEventLink;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use App\Models\Evenement;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de calcul des commissions d'affiliation.
 *
 * Mécanisme A (parrainage direct) : 20 % de la commission PaxEvent, sur chaque
 * vente payée du premier événement publié de l'organisateur parrainé.
 *
 * Mécanisme B (co-parrainage) : pourcentage fixé par l'organisateur, sur chaque
 * vente payée dont le billet est rattaché au lien de l'affilié.
 *
 * Sont exclus : événements gratuits, billets physiques (lots QR) et ventes dont
 * le montant est nul.
 */
class AffiliationService
{
    // Part de la commission PaxEvent reversée à l'affilié (mécanisme A).
    public const PART_PARRAINAGE_DIRECT = 20.0;

    // Délai de maturation après la date de l'événement (couvre les remboursements).
    public const DELAI_MATURATION_HEURES = 6;

    /**
     * Point d'entrée appelé dès qu'un billet passe à « payé ».
     * Idempotent : la contrainte unique (ticket_id, type) empêche tout doublon.
     */
    public function enregistrerPourTicket(Ticket $ticket): void
    {
        if ($ticket->statut_paiement !== 'payé') {
            return;
        }

        // Billets physiques (lots de QR codes) : jamais de commission d'affiliation.
        if ($ticket->lot_physique_id) {
            return;
        }

        $evenement = $ticket->evenement;
        if (! $evenement || $evenement->gratuit) {
            return;
        }

        $base = (float) $ticket->montant;
        if ($base <= 0) {
            return;
        }

        DB::transaction(function () use ($ticket, $evenement, $base) {
            $this->creerCoParrainage($ticket, $evenement, $base);
            $this->creerParrainageDirect($ticket, $evenement, $base);
        });
    }

    /**
     * Mécanisme B : le billet a été acheté via le lien d'un affilié.
     */
    private function creerCoParrainage(Ticket $ticket, Evenement $evenement, float $base): void
    {
        if (! $ticket->affiliate_event_link_id) {
            return;
        }

        $lien = AffiliateEventLink::whereKey($ticket->affiliate_event_link_id)
            ->where('evenement_id', $evenement->id)
            ->where('actif', true)
            ->first();

        if (! $lien || (float) $lien->pourcentage <= 0) {
            return;
        }

        $montant = round($base * (float) $lien->pourcentage / 100, 2);
        if ($montant <= 0) {
            return;
        }

        $this->creerCommission(
            $ticket,
            $lien->affiliate_id,
            AffiliateCommission::TYPE_CO_PARRAINAGE,
            $base,
            (float) $lien->pourcentage,
            $montant,
            $evenement,
            $lien->id
        );
    }

    /**
     * Mécanisme A : 20 % de la commission PaxEvent sur le premier événement payé
     * de l'organisateur parrainé.
     */
    private function creerParrainageDirect(Ticket $ticket, Evenement $evenement, float $base): void
    {
        $referral = AffiliateReferral::where('organisateur_id', $evenement->user_id)
            ->whereIn('statut', ['en_attente', 'valide'])
            ->first();

        if (! $referral) {
            return;
        }

        $premierId = $referral->premier_evenement_id ?: $this->premierEvenementId($evenement->user_id, $evenement->id);
        if ((int) $premierId !== (int) $evenement->id) {
            return;
        }

        // Commission PaxEvent de la vente, puis part revenant à l'affilié.
        $commissionPax = round($base * $evenement->commissionEffective() / 100, 2);
        if ($commissionPax <= 0) {
            return;
        }

        $montant = round($commissionPax * self::PART_PARRAINAGE_DIRECT / 100, 2);
        if ($montant <= 0) {
            return;
        }

        $this->creerCommission(
            $ticket,
            $referral->affiliate_id,
            AffiliateCommission::TYPE_PARRAINAGE,
            $commissionPax,
            self::PART_PARRAINAGE_DIRECT,
            $montant,
            $evenement,
            null
        );

        // Fige le premier événement couvert par le parrainage direct.
        if (! $referral->premier_evenement_id) {
            $referral->update([
                'premier_evenement_id' => $evenement->id,
                'statut' => 'valide',
                'valide_le' => now(),
            ]);
        }
    }

    /**
     * Crée la commission (une seule par billet et par type) et rafraîchit les soldes.
     */
    private function creerCommission(
        Ticket $ticket,
        int $affiliateId,
        string $type,
        float $baseMontant,
        float $pourcentage,
        float $montant,
        Evenement $evenement,
        ?int $lienId
    ): void {
        $commission = AffiliateCommission::firstOrCreate(
            ['ticket_id' => $ticket->id, 'type' => $type],
            [
                'affiliate_id' => $affiliateId,
                'evenement_id' => $evenement->id,
                'affiliate_event_link_id' => $lienId,
                'base_montant' => $baseMontant,
                'pourcentage' => $pourcentage,
                'montant' => $montant,
                'statut' => 'en_attente',
                'disponible_le' => $this->dateMaturation($evenement),
            ]
        );

        if ($commission->wasRecentlyCreated && ($affiliate = Affiliate::find($affiliateId))) {
            $this->recalculerSoldes($affiliate);
        }
    }

    /**
     * Annule les commissions d'un billet qui n'est plus payé (annulation, remboursement).
     * Les commissions déjà réservées par une demande de reversement ne sont pas touchées.
     */
    public function annulerPourTicket(Ticket $ticket): void
    {
        $commissions = AffiliateCommission::where('ticket_id', $ticket->id)
            ->whereIn('statut', ['en_attente', 'disponible'])
            ->get();

        $affectes = [];
        foreach ($commissions as $commission) {
            // Déjà engagée dans un reversement : on la laisse au traitement manuel.
            if ($commission->affiliate_payout_id) {
                continue;
            }

            $commission->update(['statut' => 'annule']);
            $affectes[$commission->affiliate_id] = true;
        }

        foreach (array_keys($affectes) as $affiliateId) {
            if ($affiliate = Affiliate::find($affiliateId)) {
                $this->recalculerSoldes($affiliate);
            }
        }
    }

    /**
     * Associe le premier événement publié de l'organisateur à son parrain.
     */
    public function lierPremierEvenement(Evenement $evenement): void
    {
        $referral = AffiliateReferral::where('organisateur_id', $evenement->user_id)
            ->whereNull('premier_evenement_id')
            ->first();

        if (! $referral) {
            return;
        }

        $premier = Evenement::where('user_id', $evenement->user_id)
            ->whereIn('statut', ['publié', 'terminé'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();

        if ($premier && (int) $premier->id === (int) $evenement->id) {
            $referral->update([
                'premier_evenement_id' => $evenement->id,
                'statut' => 'valide',
                'valide_le' => now(),
            ]);
        }
    }

    /**
     * Libère les commissions arrivées à maturité (événement terminé + délai).
     *
     * @return int nombre de commissions libérées
     */
    public function libererEcheues(): int
    {
        $commissions = AffiliateCommission::where('statut', 'en_attente')
            ->whereNotNull('disponible_le')
            ->where('disponible_le', '<=', now())
            ->get();

        $affectes = [];
        foreach ($commissions as $commission) {
            $commission->update(['statut' => 'disponible']);
            $affectes[$commission->affiliate_id] = true;
        }

        foreach (array_keys($affectes) as $affiliateId) {
            if ($affiliate = Affiliate::find($affiliateId)) {
                $this->recalculerSoldes($affiliate);
            }
        }

        return $commissions->count();
    }

    /**
     * Crée une demande de reversement en réservant les commissions disponibles.
     * Le montant réellement reversé peut être légèrement supérieur au montant
     * demandé afin de ne jamais payer une commission partiellement.
     */
    public function creerDemandeReversement(Affiliate $affiliate, float $montantDemande): AffiliatePayout
    {
        if ($montantDemande <= 0) {
            throw new \InvalidArgumentException('Le montant du reversement doit être supérieur à zéro.');
        }

        return DB::transaction(function () use ($affiliate, $montantDemande) {
            $affiliate = Affiliate::whereKey($affiliate->id)->lockForUpdate()->firstOrFail();

            $this->recalculerSoldes($affiliate);

            if ($montantDemande > (float) $affiliate->solde_disponible) {
                throw new \InvalidArgumentException('Le montant demandé dépasse votre solde disponible.');
            }

            $commissions = $affiliate->commissions()
                ->where('statut', 'disponible')
                ->whereNull('affiliate_payout_id')
                ->orderBy('disponible_le')
                ->orderBy('id')
                ->get();

            if ($commissions->isEmpty()) {
                throw new \InvalidArgumentException('Aucune commission disponible pour un reversement.');
            }

            $payout = $affiliate->payouts()->create([
                'montant' => 0,
                'operateur' => $affiliate->operateur_paiement,
                'numero_paiement' => $affiliate->numero_paiement,
                'titulaire' => $affiliate->titulaire_paiement,
                'statut' => 'demande',
            ]);

            $cumul = 0.0;
            foreach ($commissions as $commission) {
                if ($cumul >= $montantDemande) {
                    break;
                }
                $commission->update(['affiliate_payout_id' => $payout->id]);
                $cumul += (float) $commission->montant;
            }

            $payout->update(['montant' => round($cumul, 2)]);
            $this->recalculerSoldes($affiliate);

            return $payout->fresh();
        });
    }

    /**
     * Passe une demande en cours de traitement.
     */
    public function approuverReversement(AffiliatePayout $payout): bool
    {
        $updated = AffiliatePayout::whereKey($payout->id)
            ->where('statut', 'demande')
            ->update(['statut' => 'en_cours', 'traite_le' => now()]);

        return (bool) $updated;
    }

    /**
     * Confirme le paiement : les commissions réservées passent à « payé ».
     */
    public function confirmerReversement(AffiliatePayout $payout): bool
    {
        return DB::transaction(function () use ($payout) {
            $updated = AffiliatePayout::whereKey($payout->id)
                ->where('statut', 'en_cours')
                ->update(['statut' => 'paye', 'traite_le' => now()]);

            if (! $updated) {
                return false;
            }

            AffiliateCommission::where('affiliate_payout_id', $payout->id)
                ->where('statut', 'disponible')
                ->update(['statut' => 'paye', 'paye_le' => now()]);

            $payout->refresh();
            if ($affiliate = $payout->affiliate) {
                $this->recalculerSoldes($affiliate);
            }

            return true;
        });
    }

    /**
     * Rejette une demande : les commissions redeviennent disponibles.
     */
    public function rejeterReversement(AffiliatePayout $payout, ?string $note = null): bool
    {
        if (! in_array($payout->statut, ['demande', 'en_cours'], true)) {
            return false;
        }

        return DB::transaction(function () use ($payout, $note) {
            $payout->update([
                'statut' => 'refuse',
                'note' => $note ?? $payout->note,
                'traite_le' => now(),
            ]);

            AffiliateCommission::where('affiliate_payout_id', $payout->id)
                ->where('statut', 'disponible')
                ->update(['affiliate_payout_id' => null]);

            if ($affiliate = $payout->affiliate) {
                $this->recalculerSoldes($affiliate);
            }

            return true;
        });
    }

    /**
     * Recalcule les soldes de l'affilié à partir de ses commissions.
     * Une commission réservée par une demande de reversement n'est plus disponible.
     */
    public function recalculerSoldes(Affiliate $affiliate): void
    {
        $total = (float) $affiliate->commissions()->where('statut', '!=', 'annule')->sum('montant');
        $attente = (float) $affiliate->commissions()->where('statut', 'en_attente')->sum('montant');
        $disponible = (float) $affiliate->commissions()
            ->where('statut', 'disponible')
            ->whereNull('affiliate_payout_id')
            ->sum('montant');

        $affiliate->update([
            'total_gagne' => round($total, 2),
            'solde_en_attente' => round($attente, 2),
            'solde_disponible' => round(max(0, $disponible), 2),
        ]);
    }

    /**
     * Identifiant du premier événement publié/terminé de l'organisateur.
     */
    private function premierEvenementId(int $organisateurId, int $evenementCourantId): ?int
    {
        $premier = Evenement::where('user_id', $organisateurId)
            ->whereIn('statut', ['publié', 'terminé'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();

        // Si l'événement courant est le seul publié, il est bien le premier.
        return $premier?->id ?? $evenementCourantId;
    }

    /**
     * Date à laquelle la commission devient disponible.
     */
    private function dateMaturation(Evenement $evenement): \Illuminate\Support\Carbon
    {
        $date = $evenement->date_event
            ? $evenement->date_event->copy()->addHours(self::DELAI_MATURATION_HEURES)
            : now();

        return $date->isPast() ? now() : $date;
    }
}
