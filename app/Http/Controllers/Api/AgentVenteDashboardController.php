<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentVente;
use App\Models\AgentVenteNotification;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Services\AgentVenteSaleService;
use App\Services\AgentVenteStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentVenteDashboardController extends Controller
{
    public function show(
        Request $request,
        AgentVenteStatisticsService $statistics,
        AgentVenteSaleService $sales
    ): JsonResponse {
        /** @var AgentVente $agent */
        $agent = $request->user();

        // Nettoyage des ventes mobiles abandonnées
        $sales->expirePending($agent);

        $evenement = Evenement::with(['user', 'tarifs', 'dates'])
            ->find($agent->evenement_id);

        if (! $evenement) {
            return response()->json(['message' => 'no_event'], 403);
        }

        $visible = $agent->tickets()
            ->where('evenement_id', $evenement->id)
            ->where(fn ($query) => $query->whereNull('annule')->orWhere('annule', false))
            ->whereNull('expire_at');

        $ventesDuJour = (clone $visible)
            ->where('date_achat', '>=', today()->startOfDay())
            ->where('date_achat', '<', today()->addDay()->startOfDay())
            ->with(['tarif', 'evenement'])
            ->latest('date_achat')
            ->limit(50)
            ->get()
            ->map(fn (Ticket $ticket) => $sales->ticketPayload($ticket, $agent))
            ->values();

        $ventesEnAttente = (clone $visible)
            ->where('statut_paiement', 'en_attente')
            ->with(['tarif', 'evenement'])
            ->latest('date_achat')
            ->limit(20)
            ->get()
            ->map(fn (Ticket $ticket) => $sales->ticketPayload($ticket, $agent))
            ->values();

        $ventesRecentes = (clone $visible)
            ->with(['tarif', 'evenement'])
            ->latest('date_achat')
            ->limit(10)
            ->get()
            ->map(fn (Ticket $ticket) => $sales->ticketPayload($ticket, $agent))
            ->values();

        $notifications = AgentVenteNotification::query()
            ->visibleParAgent($agent)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (AgentVenteNotification $notification) => $notification->payload())
            ->values();

        $nonLues = AgentVenteNotification::query()
            ->visibleParAgent($agent)
            ->whereNull('lu_at')
            ->count();

        $stats = $statistics->apiStats($agent, $evenement);
        $cash = $sales->cashAvailability($evenement);

        return response()->json([
            'agent' => [
                'id' => $agent->id,
                'name' => trim((string) $agent->nom) !== '' ? $agent->nom : $agent->email,
                'nom' => $agent->nom,
                'email' => $agent->email,
                'actif' => (bool) $agent->actif,
                'tickets_count' => (int) $agent->tickets_count,
                'montant_total' => (float) $agent->montant_total,
                'dernier_acces' => $agent->dernier_acces?->toIso8601String(),
            ],
            'event' => [
                'id' => $evenement->id,
                'titre' => $evenement->titre,
                'description' => $evenement->description,
                'date_event' => $evenement->date_event?->toIso8601String(),
                'derniere_date' => $evenement->derniereDateVente()?->toIso8601String(),
                'lieu' => $evenement->lieu,
                'categorie' => $evenement->categorie,
                'type_evenement' => $evenement->type_evenement,
                'statut' => $evenement->statut,
                'gratuit' => (bool) $evenement->gratuit,
                'ventes_fermees' => (bool) $evenement->ventes_fermees,
                'ventes_ouvertes' => $evenement->ventesOuvertes(),
                'raison_indisponible' => $evenement->raisonBlocageVente(),
                'capacite' => (int) $evenement->capacite,
                'quota_vendu' => (int) $evenement->quota_vendu,
                'places_restantes' => max(0, (int) $evenement->capacite - (int) $evenement->quota_vendu),
                'image_url' => $evenement->image ? asset('storage/'.$evenement->image) : null,
                'dates' => $evenement->dates
                    ->map(fn ($date) => [
                        'id' => $date->id,
                        'date_debut' => $date->date_debut?->toIso8601String(),
                    ])
                    ->values(),
                'textes' => $evenement->getTextes(),
            ],
            'organisateur' => $evenement->user ? [
                'nom' => trim(($evenement->user->prenom ?? '').' '.($evenement->user->nom ?? '')),
                'email' => $evenement->user->email,
            ] : null,
            'tarifs' => $sales->tarifs($evenement),
            'ventes_especes' => $cash,
            'paiement_mobile' => $sales->paymentConfig(),
            'stats' => $stats,
            'ventes_du_jour' => $ventesDuJour,
            'ventes_en_attente' => $ventesEnAttente,
            'ventes_en_attente_count' => $ventesEnAttente->count(),
            'ventes_recentes' => $ventesRecentes,
            'notifications' => $notifications,
            'notifications_non_lues' => $nonLues,
            'vente_disponible' => $this->venteDisponible($agent, $evenement),
        ], 200);
    }

    private function venteDisponible(AgentVente $agent, Evenement $evenement): bool
    {
        return (bool) ($agent->actif && $evenement->ventesOuvertes());
    }
}
