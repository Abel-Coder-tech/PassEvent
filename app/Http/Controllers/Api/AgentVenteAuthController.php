<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentVente;
use App\Models\AgentVenteNotification;
use App\Models\JournalAction;
use App\Services\AgentVenteSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentVenteAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail doit être une adresse e-mail valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
        ]);

        $agent = AgentVente::where('email', $request->input('email'))->first();

        if (! $agent || ! Hash::check($request->input('password'), $agent->password)) {
            return response()->json(['message' => 'invalid_credentials'], 401);
        }

        if (! $agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        $evenement = $agent->evenement;

        if (! $evenement) {
            return response()->json(['message' => 'no_event'], 403);
        }

        // L'accès est refusé si l'événement est annulé, suspendu, en brouillon
        // ou si sa dernière session réelle est passée. Une fermeture manuelle
        // des ventes (ventes_fermees) laisse l'accès ouvert mais bloque la vente.
        if ($raison = $evenement->raisonBlocageAcces()) {
            return response()->json(['message' => $raison], 403);
        }

        $agent->update(['dernier_acces' => now()]);

        $expiresAt = now()->addDays(30);
        $token = $agent->createToken(
            'agent-vente-app',
            ['agent_vente:read', 'agent_vente:sell'],
            $expiresAt
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'agent' => $this->agentPayload($agent),
            'requires_pin' => false,
        ], 200);
    }

    public function me(Request $request, AgentVenteSaleService $sales): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $agent->loadMissing('evenement');

        $evenement = $agent->evenement;

        return response()->json([
            'agent' => $this->agentPayload($agent),
            'event' => $evenement ? $this->eventSummary($evenement) : null,
            'vente_disponible' => $this->venteDisponible($agent),
            'paiement_mobile' => $sales->paymentConfig(),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'logged_out'], 200);
    }

    // Change le mot de passe de l'agent et révoque les autres sessions
    public function changePassword(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
        ]);

        if (! Hash::check($validated['current_password'], $agent->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors' => ['current_password' => ['Le mot de passe actuel est incorrect.']],
            ], 422);
        }

        if (Hash::check($validated['password'], $agent->password)) {
            return response()->json([
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien.',
                'errors' => ['password' => ['Le nouveau mot de passe doit être différent de l\'ancien.']],
            ], 422);
        }

        $agent->update(['password' => Hash::make($validated['password'])]);

        // Révoque toutes les sessions sauf celle qui effectue le changement
        $current = $agent->currentAccessToken();

        $agent->tokens()
            ->when($current, fn ($query) => $query->where('id', '!=', $current->id))
            ->delete();

        JournalAction::enregistrer([
            'agent_vente_id' => $agent->id,
            'evenement_id' => $agent->evenement_id,
            'action' => 'mot_de_passe_modifie',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        AgentVenteNotification::envoyer(
            $agent,
            'Mot de passe modifié',
            'Votre mot de passe a été modifié. Les autres sessions ont été déconnectées.',
            $agent->evenement_id,
            AgentVenteNotification::TYPE_COMPTE
        );

        return response()->json(['message' => 'Mot de passe modifié avec succès.'], 200);
    }

    // Fait tourner le token d'accès sans redemander les identifiants
    public function refreshToken(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $current = $agent->currentAccessToken();

        $expiresAt = now()->addDays(30);

        $token = $agent->createToken(
            'agent-vente-app',
            ['agent_vente:read', 'agent_vente:sell'],
            $expiresAt
        )->plainTextToken;

        if ($current) {
            $current->delete();
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
        ], 200);
    }

    private function agentPayload(AgentVente $agent): array
    {
        $evenement = $agent->evenement;

        return [
            'id' => $agent->id,
            'name' => trim((string) $agent->nom) !== '' ? $agent->nom : $agent->email,
            'nom' => $agent->nom,
            'email' => $agent->email,
            'actif' => (bool) $agent->actif,
            'evenement_id' => $agent->evenement_id,
            'tickets_count' => (int) $agent->tickets_count,
            'montant_total' => (float) $agent->montant_total,
            'dernier_acces' => $agent->dernier_acces?->toIso8601String(),
            'event_titre' => $evenement?->titre,
        ];
    }

    private function eventSummary($evenement): array
    {
        return [
            'id' => $evenement->id,
            'titre' => $evenement->titre,
            'date_event' => $evenement->date_event?->toIso8601String(),
            'derniere_date' => $evenement->derniereDateVente()?->toIso8601String(),
            'lieu' => $evenement->lieu,
            'statut' => $evenement->statut,
            'gratuit' => (bool) $evenement->gratuit,
            'ventes_fermees' => (bool) $evenement->ventes_fermees,
            'ventes_ouvertes' => $evenement->ventesOuvertes(),
            'raison_indisponible' => $evenement->raisonBlocageVente(),
        ];
    }

    private function venteDisponible(AgentVente $agent): bool
    {
        return (bool) ($agent->actif && $agent->evenement && $agent->evenement->ventesOuvertes());
    }
}
