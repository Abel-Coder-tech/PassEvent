<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentVente;
use App\Models\AgentVenteNotification;
use App\Models\JournalAction;
use App\Services\AgentVenteSaleService;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AgentVenteAuthController extends Controller
{
    // Marqueur stocké dans les abilities du token : début de la session, utilisé
    // pour plafonner la durée totale même après plusieurs rafraîchissements.
    private const SESSION_STARTED_MARKER = 'session_started_at:';

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

        $sessionStartedAt = now();
        $expiresAt = $sessionStartedAt->copy()->addMinutes((int) config('sanctum.expiration'));
        $token = $agent->createToken(
            'agent-vente-app',
            $this->tokenAbilities($sessionStartedAt),
            $expiresAt
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'absolute_expires_at' => $this->absoluteExpiresAt($sessionStartedAt)->toIso8601String(),
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

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'logged_out_all'], 200);
    }

    // Change le mot de passe de l'agent et révoque toutes les sessions
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

        $agent->tokens()->delete();

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
            'Votre mot de passe a été modifié. Toutes les sessions ont été déconnectées.',
            $agent->evenement_id,
            AgentVenteNotification::TYPE_COMPTE
        );

        return response()->json(['message' => 'Mot de passe modifié avec succès.'], 200);
    }

    // Fait tourner le token d'accès sans redemander les identifiants.
    // La session reste plafonnée par « sanctum.absolute_expiration ».
    public function refreshToken(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $current = $agent->currentAccessToken();

        $sessionStartedAt = $this->sessionStartedAt($current instanceof PersonalAccessToken ? $current : null);
        $absoluteExpiresAt = $this->absoluteExpiresAt($sessionStartedAt);

        if ($absoluteExpiresAt->isPast()) {
            if ($current instanceof PersonalAccessToken) {
                $current->delete();
            }

            return response()->json([
                'message' => 'session_expired',
            ], 401);
        }

        $expiresAt = now()->addMinutes((int) config('sanctum.expiration'));

        // Le token glissant ne peut jamais dépasser la fin absolue de session.
        if ($expiresAt->greaterThan($absoluteExpiresAt)) {
            $expiresAt = $absoluteExpiresAt->copy();
        }

        $token = $agent->createToken(
            'agent-vente-app',
            $this->tokenAbilities($sessionStartedAt),
            $expiresAt
        )->plainTextToken;

        if ($current instanceof PersonalAccessToken) {
            $current->delete();
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'absolute_expires_at' => $absoluteExpiresAt->toIso8601String(),
        ], 200);
    }

    private function tokenAbilities(CarbonInterface $sessionStartedAt): array
    {
        return [
            'agent_vente:read',
            'agent_vente:sell',
            // ISO 8601 pour conserver explicitement le fuseau horaire.
            self::SESSION_STARTED_MARKER.$sessionStartedAt->toIso8601String(),
        ];
    }

    private function sessionStartedAt(?PersonalAccessToken $token): CarbonInterface
    {
        foreach ($token?->abilities ?? [] as $ability) {
            if (is_string($ability) && str_starts_with($ability, self::SESSION_STARTED_MARKER)) {
                $valeur = substr($ability, strlen(self::SESSION_STARTED_MARKER));

                try {
                    return Carbon::parse($valeur);
                } catch (\Throwable) {
                    // Marqueur illisible : on retombe sur la date de création.
                }
            }
        }

        // Token émis avant l'introduction du marqueur : la date de création fait foi.
        return $token?->created_at ?? now();
    }

    private function absoluteExpiresAt(CarbonInterface $sessionStartedAt): Carbon
    {
        $minutes = max(1, (int) config('sanctum.absolute_expiration', 43200));

        return Carbon::parse($sessionStartedAt)->addMinutes($minutes);
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
