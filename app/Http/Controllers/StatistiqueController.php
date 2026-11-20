<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evenement;
use App\Models\Log;
use App\Services\VentesChartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    private VentesChartService $ventesChart;

    public function __construct(VentesChartService $ventesChart)
    {
        $this->ventesChart = $ventesChart;
    }


    // Page principale des statistiques avec tous les graphiques
    public function index(Request $request)
    {
        $periode = $this->ventesChart->normaliserPeriode($request->input('periode'));

        $startDate = $this->ventesChart->startDate($periode);

        // Récupère les IDs des événements de l'organisateur
        $evenementsIds = \Illuminate\Support\Facades\Auth::id()
            ? \App\Models\Evenement::where('user_id', \Illuminate\Support\Facades\Auth::id())->pluck('id')->toArray()
            : [];

        $query = Ticket::whereIn('evenement_id', $evenementsIds)->where('created_at', '>=', $startDate);
        $prevQuery = Ticket::whereIn('evenement_id', $evenementsIds)->where('created_at', '>=', $this->ventesChart->startDate($periode, true)) // Période précédente
            ->where('created_at', '<', $startDate);

        $stats = $this->computeStats($query, $prevQuery, $periode);
        $ventesParJour = $this->ventesChart->ventesParJour($startDate, $evenementsIds);
        $ventesCategories = $this->ventesChart->categories(Auth::user()?->type);
        $periodeGraphique = $this->ventesChart->libellePeriode($periode);
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
            'ventesCategories',
            'topEvenements',
            'paiementsParReseau',
            'activiteParJourSemaine',
            'resumeFinancier',
            'paiementsEchoues',
            'periode',
            'periodeGraphique'
        ))->with('commissionPct', \Illuminate\Support\Facades\Auth::user()?->commissionPourcentage() ?? 10);
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

        $echecs = Ticket::where('created_at', '>=', $this->ventesChart->startDate($periode))
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
            'periode_graphique' => $this->ventesChart->libellePeriode($periode),
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

    // Répartition des paiements par opérateur mobile
    private function getPaiementsParReseau(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $base = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate);

        $total = (clone $base)->count();

        $reseau = (clone $base)
            ->where('type_paiement', 'mobile_money')
            ->select('methode_paiement', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant'))
            ->groupBy('methode_paiement')
            ->get()
            ->keyBy('methode_paiement');

        $reseaux = [
            'mtn' => [
                'label' => 'MTN MoMo',
                'count' => (int) ($reseau->get('mtn')->total ?? 0),
                'montant' => (int) ($reseau->get('mtn')->montant ?? 0),
                'percentage' => 0,
            ],
            'moov' => [
                'label' => 'Moov Money',
                'count' => (int) ($reseau->get('moov')->total ?? 0),
                'montant' => (int) ($reseau->get('moov')->montant ?? 0),
                'percentage' => 0,
            ],
            'celtiis' => [
                'label' => 'Celtiis',
                'count' => (int) ($reseau->get('celtiis')->total ?? 0),
                'montant' => (int) ($reseau->get('celtiis')->montant ?? 0),
                'percentage' => 0,
            ],
            'autres' => [
                'label' => 'Autres / Indéterminé',
                'count' => (int) $reseau->reject(fn ($data, $key) => in_array($key, ['mtn', 'moov', 'celtiis']))->sum('total'),
                'montant' => (int) $reseau->reject(fn ($data, $key) => in_array($key, ['mtn', 'moov', 'celtiis']))->sum('montant'),
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

    // Résumé financier avec commissions et remboursements
    private function getResumeFinancier(\Carbon\Carbon $startDate, $evenementsIds): array
    {
        $tickets = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->where('created_at', '>=', $startDate)
            ->get();

        $revenusBruts = $tickets->sum('montant');
        $gratuits = $tickets->where('montant', '<', 100)->count();
        $evenements = Evenement::whereIn('id', $evenementsIds)->with('user')->get()->keyBy('id');
        $commission = $tickets->groupBy('evenement_id')->sum(function ($group) use ($evenements) {
            $evenement = $evenements->get($group->first()->evenement_id);
            $taux = $evenement ? $evenement->commissionEffective() : 10;
            return $group->sum('montant') * $taux / 100;
        });
        $remboursements = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'remboursé')
            ->where('created_at', '>=', $startDate)
            ->sum('montant');
        $net = $revenusBruts - $commission - $remboursements;

        return [
            'revenus_bruts' => $revenusBruts,
            'gratuits' => $gratuits,
            'commission' => $commission,
            'remboursements' => $remboursements,
            'net_reverser' => $net,
        ];
    }
}
