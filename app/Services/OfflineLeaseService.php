<?php
namespace App\Services;
use App\Models\Agent;
use App\Models\Evenement;
use App\Models\OfflineLease;
use App\Models\OfflineLeaseEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class OfflineLeaseService {
    public function activeForEvent(Evenement $evenement): ?OfflineLease {
        return OfflineLease::where('evenement_id', $evenement->id)->whereIn('statut', ['autorise', 'arme', 'activation_en_attente', 'actif', 'revocation_en_attente'])->latest()->first();
    }
    public function canAgentScan(Agent $agent): array {
        if (! $agent->evenement || ! $agent->evenement->offline_mode_enabled) return ['allowed' => true];
        $lease = $this->activeForEvent($agent->evenement);
        if (! $lease) return ['allowed' => true];
        if (in_array($lease->statut, ['actif', 'revocation_en_attente'], true)) {
            if ((int)$lease->agent_id !== (int)$agent->id) return ['allowed' => false, 'reason' => 'offline_mode_reserved'];
        }
        return ['allowed' => true, 'lease' => $lease];
    }
    public function authorize(Evenement $evenement, Agent $agent, User $user): OfflineLease {
        return DB::transaction(function () use ($evenement, $agent, $user) {
            $existing = OfflineLease::where('evenement_id', $evenement->id)->whereIn('statut', ['autorise', 'arme', 'activation_en_attente', 'actif', 'revocation_en_attente'])->lockForUpdate()->first();
            if ($existing) {
                if ((int)$existing->agent_id !== (int)$agent->id) throw new \Exception('Un bail est déjà actif ou en cours pour cet événement.');
                return $existing;
            }
            $lease = OfflineLease::create(['evenement_id'=>$evenement->id,'agent_id'=>$agent->id,'statut'=>'autorise','authorized_at'=>now(),'authorized_by'=>$user->id]);
            $this->logEvent($lease, 'authorize', 'user', $user->id);
            return $lease;
        });
    }
    public function requestActivation(OfflineLease $lease): OfflineLease {
        return DB::transaction(function () use ($lease) {
            $lease = OfflineLease::where('id',$lease->id)->lockForUpdate()->first();
            if (in_array($lease->statut, ['arme','autorise'], true)) { $lease->update(['statut'=>'activation_en_attente']); $this->logEvent($lease,'activation_requested'); }
            return $lease;
        });
    }
    public function acknowledgeActivation(OfflineLease $lease, array $meta = []): OfflineLease {
        return DB::transaction(function () use ($lease, $meta) {
            $lease = OfflineLease::where('id',$lease->id)->lockForUpdate()->first();
            $evenement = $lease->evenement;
            $lastSession = $evenement->dates()->orderBy('date_debut','desc')->first();
            $expiresAt = $lastSession && $lastSession->date_debut ? $lastSession->date_debut->copy()->addHours(6) : now()->addHours(6);
            $lease->update(['statut'=>'actif','activated_at'=>now(),'active_slot'=>$lease->evenement_id,'expires_at'=>$expiresAt,'last_seen_at'=>now()]);
            $this->logEvent($lease,'activated', $meta['actor_type']??'agent', $meta['actor_id']??$lease->agent_id, $meta);
            return $lease->fresh();
        });
    }
    public function requestRevocation(OfflineLease $lease, User $user): OfflineLease {
        return DB::transaction(function () use ($lease, $user) {
            $lease = OfflineLease::where('id',$lease->id)->lockForUpdate()->first();
            if ($lease->statut==='actif') { $lease->update(['statut'=>'revocation_en_attente','revocation_requested_at'=>now(),'revoked_by'=>$user->id]); $this->logEvent($lease,'revocation_requested','user',$user->id); }
            return $lease;
        });
    }
    public function acknowledgeRevocation(OfflineLease $lease): OfflineLease {
        return DB::transaction(function () use ($lease) {
            $lease = OfflineLease::where('id',$lease->id)->lockForUpdate()->first();
            $lease->update(['statut'=>'revoque','revocation_acked_at'=>now(),'revoked_at'=>$lease->revoked_at?:now(),'active_slot'=>null]);
            $this->logEvent($lease,'revocation_acknowledged','agent',$lease->agent_id);
            return $lease;
        });
    }
    public function forceRevoke(OfflineLease $lease, User $user): OfflineLease {
        return DB::transaction(function () use ($lease, $user) {
            $lease = OfflineLease::where('id',$lease->id)->lockForUpdate()->first();
            $lease->update(['statut'=>'revoque','revoked_at'=>now(),'force_revoked_at'=>now(),'revoked_by'=>$user->id,'active_slot'=>null]);
            $this->logEvent($lease,'force_revoked','user',$user->id);
            return $lease;
        });
    }
    public function expireIfNeeded(): int {
        $count=0;
        $leases = OfflineLease::where('statut','actif')->whereNotNull('expires_at')->where('expires_at','<',now())->get();
        foreach($leases as $lease){
            DB::transaction(function() use($lease,&$count){
                $l = OfflineLease::where('id',$lease->id)->lockForUpdate()->first();
                if($l && $l->statut==='actif'){ $l->update(['statut'=>'expire','active_slot'=>null]); $this->logEvent($l,'expired'); $count++; }
            });
        }
        return $count;
    }
    private function logEvent(OfflineLease $lease, string $action, string $actorType=null, $actorId=null, array $payload=[]): void {
        OfflineLeaseEvent::create(['offline_lease_id'=>$lease->id,'action'=>$action,'actor_type'=>$actorType,'actor_id'=>$actorId,'payload'=>empty($payload)?null:$payload,'created_at'=>now()]);
    }
}
