<?php

namespace App\Services;

use App\Models\AffiliateCommission;
use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Ticket;
use Illuminate\Support\Collection;

/**
 * Bilan financier d'un ou plusieurs événements, pour le super admin.
 *
 * Sépare clairement :
 *  - les ventes en ligne encaissées par PaxEvent (Mobile Money / FedaPay) ;
 *  - les ventes en espèces (agents de vente, vente manuelle) encaissées par l'organisateur ;
 *  - les billets physiques (lots imprimés), vendus au guichet par l'organisateur ;
 * puis les additionne (« montant généré ») et calcule les commissions.
 *
 * Les filtres reprennent ceux de User::statsFinancieres() pour que les
 * montants restent identiques au solde de retrait de l'organisateur.
 */
class BilanFinancier
{
    private const ESPECES = ['cash', 'especes'];

    /**
     * @param  Collection<int, Evenement>  $evenements
     */
    public function pourEvenements(Collection $evenements): array
    {
        $ids = $evenements->pluck('id');

        // Ventes (hors lots physiques)
        $ventes = Ticket::whereIn('evenement_id', $ids)
            ->where('statut_paiement', 'payé')
            ->whereNull('lot_physique_id')
            ->where(fn ($q) => $q->whereNull('source')->orWhere('source', '!=', 'physique'))
            ->get(['evenement_id', 'montant', 'methode_paiement']);

        $mobile = $ventes->whereNotIn('methode_paiement', self::ESPECES);
        $especes = $ventes->whereIn('methode_paiement', self::ESPECES);

        $commissionVentes = 0.0;
        foreach ($evenements as $evenement) {
            $taux = $evenement->commissionEffective();
            $commissionVentes += (float) $ventes->where('evenement_id', $evenement->id)->sum('montant') * $taux / 100;
        }

        // Billets physiques (lots)
        $physiques = Ticket::whereIn('evenement_id', $ids)
            ->whereNotNull('lot_physique_id')
            ->where('statut_paiement', 'payé')
            ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
            ->get(['montant', 'lot_physique_id']);

        $lots = LotPhysique::whereIn('evenement_id', $ids)->get()->keyBy('id');

        $commissionPhysiqueAttendue = 0.0;
        foreach ($physiques as $ticket) {
            $lot = $lots->get($ticket->lot_physique_id);
            // Lot prépayé : sa commission a déjà été réglée (FedaPay)
            if ($lot && $lot->reference_paiement !== null) {
                continue;
            }
            $commissionPhysiqueAttendue += (float) $ticket->montant * ($lot?->commissionEffective() ?? 10) / 100;
        }

        $commissionPhysiquePayee = (float) $lots
            ->filter(fn (LotPhysique $l) => $l->reference_paiement !== null && $l->fedapay_transaction_id !== null)
            ->sum('montant_commission');

        $commissionAffiliation = (float) AffiliateCommission::whereIn('evenement_id', $ids)
            ->where('statut', '!=', 'annule')
            ->sum('montant');

        $mobileMontant = (float) $mobile->sum('montant');
        $especesMontant = (float) $especes->sum('montant');
        $physiqueValeur = (float) $physiques->sum('montant');
        $commissionPhysique = $commissionPhysiqueAttendue + $commissionPhysiquePayee;
        $montantGenere = $mobileMontant + $especesMontant + $physiqueValeur;

        return [
            'mobile_nb' => $mobile->count(),
            'mobile' => round($mobileMontant, 2),
            'especes_nb' => $especes->count(),
            'especes' => round($especesMontant, 2),
            'ventes_nb' => $ventes->count(),
            'ventes' => round($mobileMontant + $especesMontant, 2),
            'physique_nb' => $physiques->count(),
            'physique' => round($physiqueValeur, 2),
            'montant_genere' => round($montantGenere, 2),
            'commission_ventes' => round($commissionVentes, 2),
            'commission_physique' => round($commissionPhysique, 2),
            'commission_physique_payee' => round($commissionPhysiquePayee, 2),
            'commission_affiliation' => round($commissionAffiliation, 2),
            'commission_totale' => round($commissionVentes + $commissionPhysique, 2),
            'net_organisateur' => round($montantGenere - $commissionVentes - $commissionPhysique - $commissionAffiliation, 2),
        ];
    }
}
