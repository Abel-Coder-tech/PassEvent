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

        if ($user->statut !== 'actif' && $user->statut !== 'incomplet' && $user->statut !== 'corrections_demandees') {
            $message = '';
            if ($user->statut === 'en_attente') {
                $message = 'Votre profil est en cours de validation par notre équipe..';
            } elseif ($user->statut === 'corrections_demandees') {
                $message = 'Votre profil nécessite des corrections. Veuillez le modifier et le soumettre à nouveau.';
            } elseif ($user->statut === 'rejete') {
                $message = 'Votre inscription a été rejetée. Contactez PaxEvent pour plus d\'informations.';
            } elseif ($user->statut === 'bloque') {
                $message = 'Votre compte a été bloqué. Contactez PaxEvent pour plus d\'informations.';
            }
            return view('dashboard-pending', ['message' => $message, 'statut' => $user->statut]);
        }

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

        $mobileRecettes = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNotIn('methode_paiement', ['cash', 'especes'])
            ->sum('montant');

        $cashRecettes = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereIn('methode_paiement', ['cash', 'especes'])
            ->sum('montant');

        $commissionPct = \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE;
        $commission = round($recettesTotales * $commissionPct / 100, 2);
        $recettesNettes = $recettesTotales - $commission;

        $retirable = max(0, $mobileRecettes - $commission);

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

        $eventEnCours = Evenement::where('user_id', $user->id)
            ->where('statut', 'publié')
            ->withCount(['tickets as tickets_payes' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withCount(['tickets as tickets_utilises' => fn($q) => $q->where('utilise', true)])
            ->orderBy('tickets_payes', 'desc')
            ->first();

        $scanTotal = $eventEnCours?->tickets_payes ?? 0;
        $scanValides = $eventEnCours?->tickets_utilises ?? 0;
        $scanPct = $scanTotal > 0 ? round(($scanValides / $scanTotal) * 100) : 0;

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
                'text' => '<strong>Paiement FedaPay</strong> confirmé',
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

        $remplissageMoyen = $evenements->avg(function ($e) {
            return $e->capacite > 0 ? ($e->quota_vendu / $e->capacite) * 100 : 0;
        });

        return view('dashboard', compact(
            'totalEvenements',
            'evenementsActifs',
            'ticketsVendus',
            'recettesTotales',
            'mobileRecettes',
            'cashRecettes',
            'commission',
            'recettesNettes',
            'retirable',
            'commissionPct',
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
