<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Support\SessionMarker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AgentAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'L\'adresse e-mail est obligatoire.',
                'email.email' => 'L\'adresse e-mail doit être une adresse e-mail valide.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }

        $agent = Agent::where('email', $request->input('email'))->first();

        if (!$agent || !Hash::check($request->input('password'), $agent->password)) {
            return response()->json(['message' => 'invalid_credentials'], 401);
        }

        if (isset($agent->actif) && !$agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        if ($agent->evenement && $agent->evenement->scanTermine()) {
            return response()->json(['message' => 'event_ended'], 403);
        }

        // Un seul appareil peut utiliser ce compte à la fois. La réservation
        // est atomique : la seconde connexion est refusée sans toucher à la
        // session déjà ouverte, même si les deux requêtes arrivent ensemble.
        $cle = SessionMarker::nouvelleCle();
        if (! $agent->reserverSession($cle)) {
            return response()->json([
                'message' => 'deja_connecte',
                'detail' => 'Ce compte est déjà connecté sur un autre appareil. Déconnectez-vous d’abord.',
            ], 409);
        }

        $agent->update(['dernier_acces' => now()]);

        $token = $agent->createToken(
            'agent-app',
            ['agent:read', SessionMarker::ability($cle)],
            now()->addMinutes((int) config('sanctum.expiration'))
        )->plainTextToken;

        $name = trim($agent->nom ?? '');
        if ($name === '') {
            $name = $agent->email ?? '';
        }

        return response()->json([
            'token' => $token,
            'agent' => [
                'id' => $agent->id,
                'name' => $name,
                'email' => $agent->email,
            ],
            'requires_pin' => true,
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $name = trim(($user->nom ?? '') . ' ' . ($user->prenom ?? ''));
        if ($name === '') {
            $name = $user->email ?? '';
        }

        return response()->json([
            'agent' => [
                'id' => $user->id,
                'name' => $name,
                'email' => $user->email,
                'actif' => $user->actif ?? null,
                'evenement_id' => $user->evenement_id ?? null,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $agent = $request->user();
        if ($agent) {
            $cle = SessionMarker::cleDepuis($agent->currentAccessToken());
            $agent->fermerSession($cle);

            // Révoque le token courant et le token de scan du même appareil.
            $current = $agent->currentAccessToken();
            if ($current) {
                $current->delete();
            }
            foreach ($agent->tokens()->get() as $token) {
                if (SessionMarker::correspond(SessionMarker::cleDepuis($token), $cle)) {
                    $token->delete();
                }
            }
        }

        return response()->json(['message' => 'logged_out'], 200);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        /** @var Agent $agent */
        $agent = $request->user();
        $agent->fermerSession();
        $agent->tokens()->delete();

        return response()->json(['message' => 'logged_out_all'], 200);
    }
}
