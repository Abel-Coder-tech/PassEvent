<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evenement;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{


    // Page principale des statistiques avec tous les graphiques
    public function index(Request $request)
    {
        $periode = $request->input('periode', '30'); // Défaut : 30 jours

        $startDate = $this->getStartDate($periode);

        // Récupère les IDs des événements de l'organisateur
        $evenementsIds = \Illuminate\Support\Facades\Auth::id()
            ? \App\Models\Evenement::where('user_id', \Illuminate\Support\Facades\Auth::id())->pluck('id')->toArray()
            : [];

        $query = Ticket::whereIn('evenement_id', $evenementsIds)->where('created_at', '>=', $startDate);
        $prevQuery = Ticket::whereIn('evenement_id', $evenementsIds)->where('created_at', '>=', $this->getStartDate($periode, true)) // Période précédente
            ->where('created_at', '<', $startDate);

        $stats = $this->computeStats($query, $prevQuery, $periode);
        $ventesParJour = $this->getVentesParJour($startDate, $evenementsIds);
        $topEvenements = $this->getTopEvenements($evenementsIds);
        $paiementsParReseau = $this->getPaiementsParReseau($startDate, $evenementsIds);
        $activiteParJourSemaine = $this->getActiviteParJourSemaine($startDate, $evenementsIds);
        $resumeFinancier = $this->getResumeFinancier($startDate, $evenementsIds);
        $paiementsEchoues = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', '!=', 'payé')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('admin.statistiques.index', compact(
            'stats',
            'ventesParJour',
            'topEvenements',
            'paiementsParReseau',
            'activiteParJourSemaine',
            'resumeFinancier',
            'paiementsEchoues',
            'periode'
        ));
    }

    // Calcule la date de début selon la période (avec support période précédente)
    private function getStartDate(string $periode, bool $previous = false): \Carbon\Carbon
    {
        $now = now();

        return match ($periode) {
            '7' => $now->copy()->subDays($previous ? 14 : 7),
            '30' => $now->copy()->subDays($previous ? 60 : 30),
            '90' => $now->copy()->subDays($previous ? 180 : 90),
            'annee' => $now->copy()->startOfYear()->subYears($previous ? 1 : 0),
            default => now()->copy()->subYears(10),
        };
    }

    // Calcule les statistiques principales avec évolution par rapport à la période précédente
    private function computeStats($query, $prevQuery, string $periode): array
    {
        $currentTickets = $query->where('statut_paiement', 'payé')->get();
        $prevTickets = $prevQuery->where('statut_paiement', 'payé')->get();

        $ticketsVendus = $currentTickets->count();
        $prevVendus = $prevTickets->count();
        $revenus = $currentTickets->sum('montant');
        $prevRevenus = $prevTickets->sum('montant');

        $scannes = $currentTickets->where('utilise', true)->count();
        $totalPayes = $ticketsVendus;
        $tauxScan = $totalPayes > 0 ? round(($scannes / $totalPayes) * 100, 1) : 0;

        $echecs = Ticket::where('created_at', '>=', $this->getStartDate($periode))
            ->where('statut_paiement', '!=', 'payé')
            ->count();
        $totalTentatives = $ticketsVendus + $echecs;
        $tauxEchec = $totalTentatives > 0 ? round(($echecs / $totalTentatives) * 100, 1) : 0;

        return [
            'tickets_vendus' => $ticketsVendus,
            'tickets_vendus_evolution' => $this->evolution($prevVendus, $ticketsVendus),
            'revenus' => $revenus,
            'revenus_evolution' => $this->evolution($prevRevenus, $revenus),
            'taux_scan' => $tauxScan,
            'taux_echec' => $tauxEchec,
            'periode_label' => $this->getPeriodeLabel($periode),
        ];
    }

    // Calcule le pourcentage d'évolution entre deux valeurs
    private function evolution($previous, $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    // Retourne le libellé de la période sélectionnée
    private function getPeriodeLabel(string $periode): string
    {
        return match ($periode) {
            '7' => '7 jours',
            '30' => '30 jours',
            '90' => '3 mois',
            'annee' => 'Cette annee',
            default => 'Tout',
        };
    }

    // Récupère les ventes par jour avec ventilation étudiants/externes/manuelles
    private function getVentesParJour(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $data = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw("COUNT(CASE WHEN categorie = 'etudiant' THEN 1 END) as etudiants"),
                DB::raw("COUNT(CASE WHEN categorie = 'externe' THEN 1 END) as externes"),
                DB::raw("COUNT(CASE WHEN methode_paiement = 'especes' THEN 1 END) as manuelles")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(function ($row) {
            return [
                'date' => $row->date,
                'etudiants' => (int) $row->etudiants,
                'externes' => (int) $row->externes,
                'manuelles' => (int) $row->manuelles,
            ];
        })->toArray();
    }

    // Top 4 des événements par nombre de tickets vendus
    private function getTopEvenements($evenementsIds): \Illuminate\Database\Eloquent\Collection
    {
        return Evenement::select('evenement.*')
            ->whereIn('evenement.id', $evenementsIds)
            ->selectSub(
                function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('ticket')
                        ->whereColumn('ticket.evenement_id', 'evenement.id')
                        ->where('ticket.statut_paiement', 'payé');
                },
                'nb_tickets'
            )
            ->orderByDesc('nb_tickets')
            ->limit(4)
            ->get();
    }

    // Répartition des paiements par réseau mobile
    private function getPaiementsParReseau(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $total = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->count();

        $reseau = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->select('methode_paiement', DB::raw('COUNT(*) as total'))
            ->groupBy('methode_paiement')
            ->pluck('total', 'methode_paiement');

        $reseaux = [
            'mtn' => [
                'label' => 'MTN MoMo',
                'count' => $reseau->get('mtn', 0),
                'percentage' => 0,
            ],
            'moov' => [
                'label' => 'Moov Money',
                'count' => $reseau->get('moov', 0),
                'percentage' => 0,
            ],
            'celtiis' => [
                'label' => 'Celtiis',
                'count' => $reseau->get('celtiis', 0),
                'percentage' => 0,
            ],
        ];

        foreach ($reseaux as $key => $value) {
            $reseaux[$key]['percentage'] = $total > 0
                ? round(($value['count'] / $total) * 100, 1)
                : 0;
        }

        return $reseaux;
    }

    // Activité de ventes par jour de la semaine
    private function getActiviteParJourSemaine(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

        $data = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DAYOFWEEK(created_at) as jour'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('jour')
            ->pluck('total', 'jour');

        $result = [];
        foreach ($jours as $index => $label) {
            $dayNum = $index + 2;
            if ($dayNum > 7) $dayNum = 1;
            $result[] = [
                'label' => $label,
                'total' => (int) $data->get($dayNum, 0),
            ];
        }

        return $result;
    }

    // Résumé financier avec commissions, frais FedaPay et remboursements
    private function getResumeFinancier(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $tickets = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->get();

        $revenusBruts = $tickets->sum('montant');
        $gratuits = $tickets->where('montant', '<', 100)->count();
        $commissionPct = \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE;
        $commission = $revenusBruts * $commissionPct / 100;
        $remboursements = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'remboursé')
            ->where('created_at', '>=', $startDate)
            ->sum('montant');
        $fraisFedaPay = $revenusBruts * 3.5 / 100;
        $net = $revenusBruts - $commission - $fraisFedaPay - $remboursements;

        return [
            'revenus_bruts' => $revenusBruts,
            'gratuits' => $gratuits,
            'commission' => $commission,
            'frais_fedapay' => $fraisFedaPay,
            'remboursements' => $remboursements,
            'net_reverser' => $net,
        ];
    }
}
