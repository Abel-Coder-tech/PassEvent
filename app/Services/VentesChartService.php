<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

/**
 * Logique du graphe de ventes partagée entre le tableau de bord et les statistiques.
 * Une seule source de vérité pour la période, les catégories et les données.
 */
class VentesChartService
{
    public const PERIODES = ['7', '30', '90', 'annee', 'tout'];

    public function normaliserPeriode(?string $periode): string
    {
        return in_array($periode, self::PERIODES, true) ? $periode : '30';
    }

    public function startDate(string $periode, bool $previous = false): \Carbon\Carbon
    {
        $now = now();

        return match ($periode) {
            '7' => $now->copy()->subDays($previous ? 14 : 7),
            '30' => $now->copy()->subDays($previous ? 60 : 30),
            '90' => $now->copy()->subDays($previous ? 180 : 90),
            'annee' => $now->copy()->startOfYear()->subYears($previous ? 1 : 0),
            default => $now->copy()->subYears(10),
        };
    }

    public function libellePeriode(string $periode): string
    {
        return match ($periode) {
            '7' => '7 derniers jours',
            '30' => '30 derniers jours',
            '90' => '3 derniers mois',
            'annee' => 'cette année',
            default => 'depuis le début',
        };
    }

    /**
     * Les catégories universitaires n'ont de sens que pour un profil universitaire.
     */
    public function categories(?string $typeProfil): array
    {
        if ($typeProfil === 'universitaire') {
            return [
                ['key' => 'etudiants', 'label' => 'Étudiants', 'couleur' => '#12976e', 'fond' => 'rgba(18,151,110,0.1)'],
                ['key' => 'externes', 'label' => 'Externes', 'couleur' => '#87428b', 'fond' => 'rgba(135,66,139,0.1)'],
                ['key' => 'especes', 'label' => 'Vente en espèce', 'couleur' => '#428c79', 'fond' => 'rgba(66,140,121,0.1)'],
            ];
        }

        return [
            ['key' => 'en_ligne', 'label' => 'Vente en ligne', 'couleur' => '#12976e', 'fond' => 'rgba(18,151,110,0.1)'],
            ['key' => 'especes', 'label' => 'Vente en espèce', 'couleur' => '#87428b', 'fond' => 'rgba(135,66,139,0.1)'],
        ];
    }

    /**
     * Ventes par jour avec ventilation adaptée au type de profil.
     */
    public function ventesParJour(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $data = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw("COUNT(CASE WHEN nom_tarif LIKE '%tudiant%' THEN 1 END) as etudiants"),
                DB::raw("COUNT(CASE WHEN nom_tarif NOT LIKE '%tudiant%' THEN 1 END) as externes"),
                // En ligne : lien public ou vente agent réglée via FedaPay.
                DB::raw("COUNT(CASE WHEN methode_paiement NOT IN ('cash', 'especes') THEN 1 END) as en_ligne"),
                // Espèces : choix « espèce » de l'agent ou de l'organisateur.
                DB::raw("COUNT(CASE WHEN methode_paiement IN ('cash', 'especes') THEN 1 END) as especes")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(fn ($row) => [
            'date' => $row->date,
            'etudiants' => (int) $row->etudiants,
            'externes' => (int) $row->externes,
            'en_ligne' => (int) $row->en_ligne,
            'especes' => (int) $row->especes,
        ])->toArray();
    }
}
