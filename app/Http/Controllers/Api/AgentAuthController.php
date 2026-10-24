<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }

        $agent = Agent::where('email', $request->input('email'))->first();

        if (!$agent || !Hash::check($request->input('password'), $agent->password)) {
            return response()->json(['message' => 'invalid_credentials'], 401);
        }

        $token = $agent->createToken('agent-app')->plainTextToken;

        $name = trim($agent->nom ?? '');
        if ($name === '') {
            $name = $agent->email ?? '';
        }

        return response()->json([
            'token' => $token,
            'agent' => [
                'id' => $agent->id,
                'name' => $name,
            ],
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
        if ($name === '') {
            $name = $user->email ?? '';
        }

        return response()->json([
            'agent' => [
                'id' => $user->id,
                'name' => $name,
                'email' => $user->email,
                'role' => $user->role ?? null,
            ],
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
}
