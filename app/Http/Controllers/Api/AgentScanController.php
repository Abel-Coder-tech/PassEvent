<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Support\SessionMarker;
use App\Services\AgentScanStatisticsService;
use App\Services\TicketScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class AgentScanController extends Controller
{
    public function pin(Request $request): JsonResponse
    {
        $agent = $request->user();

        $request->validate([
            'code_acces' => ['required', 'digits:4'],
        ], [
            'code_acces.required' => 'Le code d\'accès est obligatoire.',
            'code_acces.digits' => 'Le code d\'accès doit être un code à 4 chiffres.',
        ]);

        if ($agent->evenement && $agent->evenement->scanTermine()) {
            return response()->json(['message' => 'event_ended'], 403);
        }

        if (isset($agent->actif) && !$agent->actif) {
            return response()->json(['message' => 'account_disabled'], 403);
        }

        // Limitation par agent ET par IP (3 essais / 5 min) : un tiers ne peut pas
        // verrouiller un agent depuis une autre connexion, et un agent qui se
        // trompe ne bloque pas ses collègues sur le même wifi (même IP publique).
        $key = 'agent-pin:'.$agent->id.':'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
                'code' => 'pin_throttled',
                'retry_after' => $seconds,
            ], 429)->header('Retry-After', (string) $seconds);
        }

        if (! Hash::check($request->code_acces, (string) $agent->code_acces)) {
            RateLimiter::hit($key, 300);
            $agent->increment('tentatives_code');

            return response()->json([
                'message' => 'Code d\'accès incorrect.',
                'code' => 'pin_invalid',
            ], 422);
        }

        $agent->update([
            'tentatives_code' => 0,
            'bloque_jusqua' => null,
        ]);
        RateLimiter::clear($key);

        // Une seule session par compte : le jeton de scan porte la même clé
        // que le jeton de connexion, pour être révoqué avec lui.
        $cle = SessionMarker::cleDepuis($request->user()?->currentAccessToken())
            ?? $agent->session_cle
            ?? SessionMarker::nouvelleCle();
        $agent->ouvrirSession($cle);
        $agent->tokens()->where('name', 'agent-scan')->delete();

        $token = $agent->createToken(
            'agent-scan',
            ['agent:scan', SessionMarker::ability($cle)],
            now()->addHours(\App\Models\Evenement::DUREE_SCAN_HEURES)
        )->plainTextToken;
        $tokenModel = $agent->tokens()->where('name', 'agent-scan')->latest()->first();

        return response()->json([
            'scan_token' => $token,
            'expires_at' => $tokenModel && $tokenModel->expires_at ? $tokenModel->expires_at->toIso8601String() : now()->addHours(\App\Models\Evenement::DUREE_SCAN_HEURES)->toIso8601String(),
            'evenement' => [
                'titre' => $agent->evenement?->titre,
            ],
        ], 200);
    }

    public function verify(
        Request $request,
        TicketScanService $service,
        AgentScanStatisticsService $statistics
    ): JsonResponse
    {
        $agent = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $result = $service->verifier($agent, $request->code);

        $stats = $statistics->apiCounters($agent);
        $total = $stats['total'];
        $valides = $stats['valides'];
        $invalides = $total - $valides;

        $result['stats'] = [
            'total' => $total,
            'valides' => $valides,
            'invalides' => $invalides,
        ];

        return response()->json($result, 200);
    }

    // Historique paginé de l'agent (onglet Historique de l'appli) : 10 scans
    // par page, filtres jour / résultat, total, nombre de pages et décompte
    // par jour pour le sélecteur de dates.
    public function liste(Request $request): JsonResponse
    {
        $agent = $request->user();
        $donnees = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'jour' => ['nullable', 'date_format:Y-m-d'],
            'filtre' => ['nullable', 'in:all,valid,invalid'],
        ]);
        $parPage = 10;

        $base = fn () => $agent->logs()->where('type_operation', 'scan');
        $duJour = fn () => $base()->when($donnees['jour'] ?? null, fn ($q, $jour) => $q->whereDate('created_at', $jour));

        $total = $duJour()->count();
        $valides = $duJour()->where('details->resultat', 'valide')->count();

        $filtree = match ($donnees['filtre'] ?? 'all') {
            'valid' => $duJour()->where('details->resultat', 'valide'),
            'invalid' => $duJour()->where(fn ($q) => $q->where('details->resultat', '!=', 'valide')->orWhereNull('details->resultat')),
            default => $duJour(),
        };
        $nbFiltres = (clone $filtree)->count();
        $pages = max(1, (int) ceil($nbFiltres / $parPage));
        $page = min((int) ($donnees['page'] ?? 1), $pages);

        $scans = $filtree->latest('created_at')->latest('id')
            ->skip(($page - 1) * $parPage)->take($parPage)->get();

        $jours = $base()
            ->selectRaw('DATE(created_at) as jour, COUNT(*) as total')
            ->groupBy('jour')->orderByDesc('jour')
            ->pluck('total', 'jour');

        return response()->json([
            'scans' => $scans->map(fn ($log) => [
                'date' => $log->created_at->toIso8601String(),
                'code' => $log->details['code'] ?? '',
                'resultat' => $log->details['resultat'] ?? 'invalide',
                'raison' => $log->details['raison'] ?? null,
            ])->values(),
            'page' => $page,
            'par_page' => $parPage,
            'total' => $nbFiltres,
            'pages' => $pages,
            'resume' => ['total' => $total, 'valides' => $valides, 'invalides' => $total - $valides],
            'jours' => $jours->map(fn ($n, $jour) => ['date' => (string) $jour, 'total' => (int) $n])->values(),
        ]);
    }

    public function history(Request $request, AgentScanStatisticsService $statistics): JsonResponse
    {
        $agent = $request->user();

        $stats = $statistics->apiCounters($agent);
        $total = $stats['total'];
        $valides = $stats['valides'];
        $invalides = $total - $valides;

        $recent = $agent->logs()->where('type_operation', 'scan')->latest('created_at')->limit(20)->get();

        return response()->json([
            'stats' => [
                'total' => $total,
                'valides' => $valides,
                'invalides' => $invalides,
                'deja_utilises' => $stats['deja_utilises'],
            ],
            'recent' => $recent->map(function ($log) {
                return [
                    'heure' => $log->created_at->format('H:i:s'),
                    'code' => $log->details['code'] ?? '',
                    'resultat' => $log->details['resultat'] ?? 'invalide',
                    'nom' => $log->ticket?->nom_acheteur,
                    'nom_tarif' => $log->ticket?->getLabel(),
                ];
            })->values(),
        ], 200);
    }

    public function exit(Request $request): JsonResponse
    {
        /** @var Agent|null $agent */
        $agent = $request->user();
        $token = $agent?->currentAccessToken();
        $cle = SessionMarker::cleDepuis($token);

        if ($agent) {
            // Quitter le scan libère le compte : une autre personne pourra se
            // connecter avec le même identifiant.
            $agent->fermerSession($cle);
            foreach ($agent->tokens()->get() as $jeton) {
                if ($cle === null
                    || $jeton->id === $token?->id
                    || SessionMarker::correspond(SessionMarker::cleDepuis($jeton), $cle)) {
                    $jeton->delete();
                }
            }
        }

        return response()->json(['message' => 'scan_exited'], 200);
    }
}
