<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\AppRelease;
use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\Withdrawal;
use App\Services\CodePromoStatisticsService;
use App\Services\TicketStatisticsService;
use App\Services\VentesChartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Tableau de bord principal de l'organisateur avec toutes ses statistiques
    public function index(Request $request, TicketStatisticsService $ticketStatistics, CodePromoStatisticsService $codePromoStatistics, VentesChartService $ventesChart)
    {
        $user = Auth::user();

        // Un compte incomplet n'a accès qu'à la création d'événement et à la
        // finalisation de son profil : aucun indicateur, aucune autre rubrique.
        if ($user->statut === 'incomplet') {
            return view('dashboard-incomplet');
        }

        // Redirige vers page d'attente si le profil n'est pas validé
        if ($user->statut !== 'actif' && $user->statut !== 'incomplet' && $user->statut !== 'corrections_demandees' && $user->statut !== 'corrections_apportees' && $user->statut !== 'bloque') {
            $message = '';
            if ($user->statut === 'en_attente') {
                $message = 'Votre profil est en cours de validation par notre équipe..';
            } elseif ($user->statut === 'corrections_demandees') {
                $message = 'Votre profil nécessite des corrections. Veuillez le modifier et le soumettre à nouveau.';
            } elseif ($user->statut === 'rejete') {
                $message = 'Votre inscription a été rejetée. Contactez PaxEvent pour plus d\'informations.';
            }

            return view('dashboard-pending', ['message' => $message, 'statut' => $user->statut]);
        }

        if ($user->statut === 'corrections_apportees') {
            return view('dashboard-pending', [
                'message' => 'Vos corrections ont été enregistrées et sont en cours de validation par notre équipe.',
                'statut' => $user->statut,
            ]);
        }

        $evenements = Evenement::where('user_id', $user->id)->get();
        $totalEvenements = $evenements->count();
        $evenementsActifs = $evenements->where('statut', EventStatus::Published->value)->count();

        $evenementsIds = $evenements->pluck('id');

        $ticketStats = $ticketStatistics->tableauDeBord($evenementsIds);
        $ticketsVendus = $ticketStats['tickets_vendus'];
        $recettesTotales = $ticketStats['recettes_totales'];
        $mobileRecettes = $ticketStats['recettes_mobile'];
        $cashRecettes = $ticketStats['recettes_especes'];

        // Calcul de la commission et des recettes nettes
        $commissionPct = $user->commissionPourcentage();
        $statsFinancieres = $user->statsFinancieres();
        $commission = $statsFinancieres['commissionTotale'];
        // Commissions des affiliés : à la charge de l'organisateur
        $commissionAffiliation = $statsFinancieres['commissionAffiliation'];
        $recettesNettes = $recettesTotales - $commission - $commissionAffiliation;
        // Formule affichée sous le net total
        $formuleNet = $commissionAffiliation > 0
            ? 'Brut − Commission − Commission affiliation'
            : 'Brut − Commission';

        $totalRetraits = (float) Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['en_attente', 'en_cours', 'payé'])
            ->sum('montant');

        $retirable = max(0, $mobileRecettes - $commission - $commissionAffiliation - $totalRetraits); // Seuls les paiements mobiles sont retirable

        // Taux de scan (tickets utilisés / tickets vendus)
        $ticketsScannes = $ticketStats['tickets_scannes'];

        $tauxScan = $ticketsVendus > 0 ? round(($ticketsScannes / $ticketsVendus) * 100) : 0;

        $ticketsAbsents = $ticketsVendus - $ticketsScannes;

        $evenementsRecents = $evenements->sortByDesc('created_at')->take(4);

        $codesPromos = CodePromo::whereIn('evenement_id', $evenementsIds)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $promoStats = $codePromoStatistics->pourEvenements($evenementsIds);
        $codesGeneres = $promoStats['total'];
        $codesUtilises = $promoStats['utilises'];

        // Graphe de ventes : mêmes données et même filtre que la page Statistiques.
        $periode = $ventesChart->normaliserPeriode($request->input('periode'));
        $ventesParJour = $ventesChart->ventesParJour($ventesChart->startDate($periode), $evenementsIds);
        $ventesCategories = $ventesChart->categories($user->type);
        $periodeGraphique = $ventesChart->libellePeriode($periode);

        $eventEnCours = Evenement::where('user_id', $user->id)
            ->where('statut', 'publié')
            ->withCount(['tickets as tickets_payes' => fn ($q) => $q->where('statut_paiement', 'payé')])
            ->withCount(['tickets as tickets_utilises' => fn ($q) => $q->where('utilise', true)])
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

        $eventAnnule = $evenements->firstWhere('statut', EventStatus::Cancelled->value);

        if ($eventAnnule) {
            $activiteRecents[] = [
                'color' => '#e74c3c',
                'text' => '<strong>'.$eventAnnule->titre.'</strong> annulé',
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

        // Calcul du remplissage moyen de tous les événements
        $remplissageMoyen = $evenements->avg(fn ($e) => $e->tauxRemplissage());

        $appRelease = AppRelease::latest();

        return view('dashboard', compact(
            'totalEvenements',
            'evenementsActifs',
            'ticketsVendus',
            'recettesTotales',
            'mobileRecettes',
            'cashRecettes',
            'commission',
            'recettesNettes',
            'formuleNet',
            'retirable',
            'commissionPct',
            'tauxScan',
            'ticketsScannes',
            'ticketsAbsents',
            'evenementsRecents',
            'codesPromos',
            'codesGeneres',
            'codesUtilises',
            'ventesParJour',
            'ventesCategories',
            'periode',
            'periodeGraphique',
            'eventEnCours',
            'scanTotal',
            'scanValides',
            'scanPct',
            'activiteRecents',
            'remplissageMoyen',
            'appRelease',
        ));
    }
}
