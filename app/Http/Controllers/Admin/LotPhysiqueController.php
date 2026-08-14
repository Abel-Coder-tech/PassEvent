<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\LotPhysique;
use App\Models\Ticket;
use App\Services\LotPhysiquePdfService;
use App\Support\PerPage;
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
            ->withCount(['tickets as nb_annules' => fn ($q) => $q->where('annule', true)])
            ->withCount(['tickets as nb_scannes' => fn ($q) => $q->where('utilise', true)])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(PerPage::resolve());

        $ticketsPhysiques = Ticket::whereIn('evenement_id', $evenementIds)
            ->whereNotNull('lot_physique_id')
            ->where('statut_paiement', 'payé');

        // Tickets valides uniquement (les annulés n'ont pas de valeur : pas de recette ni de commission)
        $ticketsPhysiquesValides = (clone $ticketsPhysiques)->where('annule', false);

        $nbTickets = (clone $ticketsPhysiques)->count();
        $nbAnnules = (clone $ticketsPhysiques)->where('annule', true)->count();
        $nbScannes = (clone $ticketsPhysiques)->where('utilise', true)->count();
        $recettesPhysiques = (float) (clone $ticketsPhysiquesValides)->sum('montant');

        // Commission attendue sur le physique : taux par lot si défini, sinon taux effectif de l'événement
        $lotsCharges = LotPhysique::where('user_id', $user->id)->get()->keyBy('id');
        $evenements = Evenement::whereIn('id', $evenementIds)->get()->keyBy('id');
        $commissionPhysique = 0.0;
        foreach ($ticketsPhysiquesValides->get() as $ticket) {
            $lot = $ticket->lot_physique_id ? $lotsCharges->get($ticket->lot_physique_id) : null;
            $taux = $lot?->commissionEffective() ?? $evenements->get($ticket->evenement_id)?->commissionEffective() ?? 10;
            $commissionPhysique += (float) $ticket->montant * $taux / 100;
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

        if (! $lot->estTransmis) {
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

        return $pdf->download('Planche-'.$lot->nom.'-'.$lot->evenement?->titre.'.pdf');
    }
}
