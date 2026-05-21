<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\CodePromo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $evenements = Evenement::where('user_id', $user->id)->get();
        $totalEvenements = $evenements->count();
        $evenementsActifs = $evenements->where('statut', 'publié')->count();

        $evenementsIds = $evenements->pluck('id');

        $ticketsVendus = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->count();

        $recettesTotales = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->sum('montant');

        $ticketsScannes = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('utilise', true)
            ->count();

        $tauxScan = $ticketsVendus > 0 ? round(($ticketsScannes / $ticketsVendus) * 100) : 0;

        $ticketsAbsents = $ticketsVendus - $ticketsScannes;

        $evenementsRecents = Evenement::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $codesPromos = CodePromo::whereIn('evenement_id', $evenementsIds)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $codesGeneres = CodePromo::whereIn('evenement_id', $evenementsIds)->count();
        $codesUtilises = CodePromo::whereIn('evenement_id', $evenementsIds)
            ->where('nb_utilisations', '>', 0)
            ->count();

        // Ventes des 7 derniers jours
        $joursLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $ventes7Jours = [];
        $today = now();

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $count = Ticket::whereIn('evenement_id', $evenementsIds)
                ->where('statut_paiement', 'payé')
                ->whereDate('date_achat', $date->format('Y-m-d'))
                ->count();
            $ventes7Jours[] = $count;
        }

        $ventesToday = $ventes7Jours[6];

        // Event en cours avec le plus de scans
        $eventEnCours = Evenement::where('user_id', $user->id)
            ->where('statut', 'publié')
            ->withCount(['tickets as tickets_payes' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withCount(['tickets as tickets_utilises' => fn($q) => $q->where('utilise', true)])
            ->orderBy('tickets_payes', 'desc')
            ->first();

        $scanTotal = $eventEnCours?->tickets_payes ?? 0;
        $scanValides = $eventEnCours?->tickets_utilises ?? 0;
        $scanPct = $scanTotal > 0 ? round(($scanValides / $scanTotal) * 100) : 0;

        // Activité récente (simulée à partir des données réelles)
        $activiteRecents = [];

        $dernierTicketScanne = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('utilise', true)
            ->latest('date_achat')
            ->first();

        if ($dernierTicketScanne) {
            $diff = $dernierTicketScanne->date_achat->diffForHumans();
            $activiteRecents[] = [
                'color' => '#12976e',
                'text' => '<strong>Ticket scanné</strong> — Entrée validée',
                'time' => $diff,
            ];
        }

        $dernierPaiement = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->latest('date_achat')
            ->first();

        if ($dernierPaiement) {
            $diff = $dernierPaiement->date_achat->diffForHumans();
            $activiteRecents[] = [
                'color' => '#b2e0d6',
                'text' => '<strong>Paiement KKiaPay</strong> confirmé',
                'time' => $diff,
            ];
        }

        $dernierCodePromo = CodePromo::whereIn('evenement_id', $evenementsIds)
            ->where('nb_utilisations', '>', 0)
            ->latest('updated_at')
            ->first();

        if ($dernierCodePromo) {
            $activiteRecents[] = [
                'color' => '#87428b',
                'text' => '<strong>Code promo</strong> utilisé',
                'time' => $dernierCodePromo->updated_at->diffForHumans(),
            ];
        }

        $eventAnnule = Evenement::where('user_id', $user->id)
            ->where('statut', 'annulé')
            ->first();

        if ($eventAnnule) {
            $activiteRecents[] = [
                'color' => '#e74c3c',
                'text' => '<strong>' . $eventAnnule->titre . '</strong> annulé',
                'time' => $eventAnnule->updated_at->diffForHumans(),
            ];
        }

        while (count($activiteRecents) < 5) {
            $activiteRecents[] = [
                'color' => '#98919b',
                'text' => '<strong>Activité système</strong> enregistrée',
                'time' => 'récemment',
            ];
        }

        $activiteRecents = array_slice($activiteRecents, 0, 5);

        // Remplissage moyen
        $remplissageMoyen = $evenements->avg(function ($e) {
            return $e->capacite > 0 ? ($e->quota_vendu / $e->capacite) * 100 : 0;
        });

        return view('dashboard', compact(
            'totalEvenements',
            'evenementsActifs',
            'ticketsVendus',
            'recettesTotales',
            'tauxScan',
            'ticketsScannes',
            'ticketsAbsents',
            'evenementsRecents',
            'codesPromos',
            'codesGeneres',
            'codesUtilises',
            'ventes7Jours',
            'joursLabels',
            'ventesToday',
            'eventEnCours',
            'scanTotal',
            'scanValides',
            'scanPct',
            'activiteRecents',
            'remplissageMoyen',
        ));
    }
}
