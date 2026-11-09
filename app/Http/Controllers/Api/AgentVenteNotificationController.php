<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentVente;
use App\Models\AgentVenteNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentVenteNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $validated = $request->validate([
            'non_lues' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer'],
        ]);

        $perPage = in_array((int) ($validated['per_page'] ?? 0), [10, 20, 50, 100], true)
            ? (int) $validated['per_page']
            : 20;

        $query = AgentVenteNotification::query()
            ->visibleParAgent($agent)
            ->deType($validated['type'] ?? null)
            ->when((bool) ($validated['non_lues'] ?? false), fn ($q) => $q->whereNull('lu_at'))
            ->latest();

        $notifications = $query->paginate($perPage);
        $nonLues = AgentVenteNotification::query()
            ->visibleParAgent($agent)
            ->whereNull('lu_at')
            ->count();

        return response()->json([
            'notifications' => collect($notifications->items())
                ->map(fn (AgentVenteNotification $notification) => $notification->payload())
                ->values(),
            'non_lues' => $nonLues,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ], 200);
    }

    public function read(Request $request, AgentVenteNotification $notification): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        if ((int) $notification->agent_vente_id !== (int) $agent->id
            || ($notification->evenement_id !== null
                && (int) $notification->evenement_id !== (int) $agent->evenement_id)) {
            abort(404);
        }

        $notification->marquerLue();

        return response()->json([
            'message' => 'Notification marquée comme lue.',
            'notification' => $notification->fresh()->payload(),
        ], 200);
    }

    public function readAll(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $count = AgentVenteNotification::query()
            ->visibleParAgent($agent)
            ->whereNull('lu_at')
            ->update(['lu_at' => now()]);

        return response()->json([
            'message' => 'Notifications marquées comme lues.',
            'notifications_lues' => $count,
        ], 200);
    }
}
