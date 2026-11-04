<?php
namespace App\Services;
use App\Models\OfflineLease;
use App\Models\SyncChange;
class OfflineDeltaService {
    public function get(OfflineLease $lease, int $since = 0, int $limit = 1000): array {
        $changes = SyncChange::where('evenement_id',$lease->evenement_id)->where('id','>',$since)->orderBy('id')->limit($limit)->get();
        $lastId = $changes->last()?->id ?? $since;
        return ['since'=>$since,'last_version'=>$lastId,'changes'=>$changes->map(fn($c)=>['id'=>$c->id,'entity_type'=>$c->entity_type,'entity_id'=>$c->entity_id,'action'=>$c->action,'created_at'=>$c->created_at?->toIso8601String()])->values()->all(),'has_more'=>$changes->count()===$limit,'lease_state'=>$lease->fresh()->statut];
    }
}
