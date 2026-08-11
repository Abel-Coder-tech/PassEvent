<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Ticket;
use App\Services\LotPhysiquePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LotPhysiqueController extends Controller
{
    // Page « Vente physique » de l'organisateur avec mini-dashboard
    public function index()
    {
        $user = Auth::user();
        $evenementIds = $user->evenements()->pluck('id');

        $lots = LotPhysique::with('evenement', 'tarif')
            ->withCount(['tickets as nb_tickets'])
            ->withCount(['tickets as nb_annules' => fn($q) => $q->where('annule', true)])
            ->withCount(['tickets as nb_scannes' => fn($q) => $q->where('utilise', true)])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(\App\Support\PerPage::resolve());

        $ticketsPhysiques = Ticket::whereIn('evenement_id', $evenementIds)
            ->whereNotNull('lot_physique_id')
            ->where('statut_paiement', 'payé');

        // Tickets valides uniquement (les annulés n'ont pas de valeur : pas de recette ni de commission)
        $ticketsPhysiquesValides = (clone $ticketsPhysiques)->where('annule', false);

        $nbTickets = (clone $ticketsPhysiques)->count();
        $nbAnnules = (clone $ticketsPhysiques)->where('annule', true)->count();
        $nbScannes = (clone $ticketsPhysiques)->where('utilise', true)->count();
        $recettesPhysiques = (float) (clone $ticketsPhysiquesValides)->sum('montant');

        // Commission attendue sur le physique (taux effectif par événement)
        $evenements = Evenement::whereIn('id', $evenementIds)->with('user')->get()->keyBy('id');
        $commissionPhysique = 0.0;
        foreach ($evenements as $evenement) {
            $montant = (clone $ticketsPhysiquesValides)->where('evenement_id', $evenement->id)->sum('montant');
            $commissionPhysique += $montant * $evenement->commissionEffective() / 100;
        }
        $commissionPhysique = round($commissionPhysique, 2);

        return view('admin.lots-physiques.index', compact(
            'lots', 'nbTickets', 'nbAnnules', 'nbScannes',
            'recettesPhysiques', 'commissionPhysique',
        ));
    }

    // Télécharge la planche de QR codes (3 téléchargements max, lot transmis requis)
    public function download(LotPhysique $lot)
    {
        if ($lot->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$lot->estTransmis) {
            return back()->with('error', 'Ce lot n\'a pas encore été transmis par le super admin.');
        }

        if ($lot->download_count >= 3) {
            return back()->with('error', 'Limite de téléchargements atteinte (3 maximum). Contactez le support si nécessaire.');
        }

        $tickets = $lot->tickets()->where('annule', false)->orderBy('code_unique')->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Aucun ticket valide à imprimer dans ce lot.');
        }

        $lot->increment('download_count');

        $pdf = LotPhysiquePdfService::generer($lot, $tickets);

        return $pdf->download('Planche-' . $lot->nom . '-' . $lot->evenement?->titre . '.pdf');
    }
}
