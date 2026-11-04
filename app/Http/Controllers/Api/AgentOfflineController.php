<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\OfflineLease;
use App\Services\OfflineDeltaService;
use App\Services\OfflineLeaseService;
use App\Services\OfflineManifestService;
use App\Services\OfflineSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class AgentOfflineController extends Controller {
    public function state(Request $request, OfflineLeaseService $service): JsonResponse {
        $agent = $request->user();
        $lease = $service->activeForEvent($agent->evenement);
        return response()->json(['offline'=>['enabled'=>(bool)($agent->evenement?->offline_mode_enabled),'lease_state'=>$lease?->statut,'is_holder'=>$lease && (int)$lease->agent_id===(int)$agent->id,'expires_at'=>$lease?->expires_at?->toIso8601String(),'pending_scans'=>0,'last_sync_at'=>$lease?->last_sync_at?->toIso8601String()],'lease'=>$lease?['id'=>$lease->id,'statut'=>$lease->statut,'agent_id'=>$lease->agent_id,'is_holder'=>(int)$lease->agent_id===(int)$agent->id]:null]);
    }
    public function manifest(Request $request, OfflineLeaseService $leaseService, OfflineManifestService $manifestService): JsonResponse {
        $agent = $request->user();
        $lease = $leaseService->activeForEvent($agent->evenement);
        if(!$lease || (int)$lease->agent_id !== (int)$agent->id) return response()->json(['message'=>'offline_not_holder'],403);
        $page = (int)$request->get('page',1); $perPage = (int)$request->get('per_page',500);
        return response()->json($manifestService->build($lease,$page,$perPage));
    }
    public function delta(Request $request, OfflineLeaseService $leaseService, OfflineDeltaService $deltaService): JsonResponse {
        $agent = $request->user();
        $lease = $leaseService->activeForEvent($agent->evenement);
        if(!$lease) return response()->json(['message'=>'no_lease'],404);
        return response()->json($deltaService->get($lease, (int)$request->get('since',0)));
    }
    public function activate(Request $request, OfflineLeaseService $leaseService): JsonResponse {
        $agent = $request->user();
        $lease = $leaseService->activeForEvent($agent->evenement);
        if(!$lease || (int)$lease->agent_id !== (int)$agent->id) return response()->json(['message'=>'offline_not_holder'],403);
        $lease = $leaseService->acknowledgeActivation($lease,['actor_type'=>'agent','actor_id'=>$agent->id]);
        return response()->json(['status'=>'ok','lease_state'=>$lease->statut,'expires_at'=>$lease->expires_at?->toIso8601String()]);
    }
    public function sync(Request $request, OfflineLeaseService $leaseService, OfflineSyncService $syncService): JsonResponse {
        $agent = $request->user();
        $lease = OfflineLease::find($request->input('lease_id'));
        if(!$lease || $lease->evenement_id !== $agent->evenement_id) return response()->json(['message'=>'invalid_lease'],422);
        return response()->json($syncService->sync($agent,$lease,$request->all()));
    }
    public function revocationAck(Request $request, OfflineLeaseService $leaseService): JsonResponse {
        $agent = $request->user();
        $lease = $leaseService->activeForEvent($agent->evenement);
        if(!$lease || (int)$lease->agent_id !== (int)$agent->id) return response()->json(['message'=>'offline_not_holder'],403);
        $lease = $leaseService->acknowledgeRevocation($lease);
        return response()->json(['status'=>'ok','lease_state'=>$lease->statut]);
    }
    public function heartbeat(Request $request, OfflineLeaseService $leaseService): JsonResponse {
        $agent = $request->user();
        $lease = $leaseService->activeForEvent($agent->evenement);
        if($lease && (int)$lease->agent_id === (int)$agent->id) $lease->update(['last_seen_at'=>now()]);
        return response()->json(['status'=>'ok']);
    }
}
