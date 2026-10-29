<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
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

        if ($agent->evenement && $agent->evenement->date_event < now()) {
            return response()->json(['message' => 'event_ended'], 403);
        }

        $agent->update(['dernier_acces' => now()]);

        // Multi-appareils autorisé pour le login
        $token = $agent->createToken('agent-app', ['agent:read'], now()->addDays(30))->plainTextToken;

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
            // Révoque le token courant (auth ou scan) + tous les tokens scan
            $current = $agent->currentAccessToken();
            if ($current) {
                $current->delete();
            }
            $agent->tokens()->where('name', 'agent-scan')->delete();
        }

        return response()->json(['message' => 'logged_out'], 200);
    }
}
