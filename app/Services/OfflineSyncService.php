<?php
namespace App\Services;
use App\Models\Agent;
use App\Models\Log;
use App\Models\OfflineLease;
use App\Models\Ticket;
use App\Models\TicketScanDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
class OfflineSyncService {
    public function sync(Agent $agent, OfflineLease $lease, array $payload): array {
        $scans = $payload['scans'] ?? [];
        $results = [];
        usort($scans, function($a,$b){ $ta = isset($a['scanned_at'])?strtotime($a['scanned_at']):0; $tb = isset($b['scanned_at'])?strtotime($b['scanned_at']):0; return $ta<=>$tb; });
        foreach($scans as $scan){
            $uuid = $scan['uuid'] ?? null; $code = trim($scan['code'] ?? '');
            if(!$uuid) continue;
            $existingLog = Log::where('client_uuid',$uuid)->first();
            if($existingLog){ $results[]=['uuid'=>$uuid,'statut'=>'accepte','resultat_serveur'=>$existingLog->server_resultat?:$existingLog->resultat,'raison'=>$existingLog->details['raison']??null]; continue; }
            $scannedAt = isset($scan['scanned_at'])?Carbon::parse($scan['scanned_at']):now();
            $timezone = $agent->evenement->fuseau_horaire ?: config('app.timezone');
            $scannedAtTz = $scannedAt->copy()->setTimezone($timezone);
            $scanDate = $scannedAtTz->toDateString();
            try {
                $res = DB::transaction(function() use($agent,$lease,$code,$uuid,$scannedAt,$scanDate){
                    $ticket = Ticket::trouverParCodeSaisi($code);
                    if(!$ticket || $ticket->evenement_id !== $agent->evenement_id){
                        $log = Log::create(['ticket_id'=>$ticket?->id,'agent_id'=>$agent->id,'client_uuid'=>$uuid,'scanned_at'=>$scannedAt,'scan_date'=>$scanDate,'lease_id'=>$lease->id,'source'=>'offline_sync','type_operation'=>'scan','resultat'=>'invalide','server_resultat'=>'invalide','details'=>['code'=>$code,'raison'=>$ticket?'Événement non autorisé':'Ticket introuvable'],'ip'=>request()->ip(),'user_agent'=>request()->userAgent(),'synced_at'=>now()]);
                        return ['statut'=>'rejete','resultat_serveur'=>'invalide','raison'=>$ticket?'Événement non autorisé':'Ticket introuvable'];
                    }
                    if($ticket->statut_paiement!=='payé'){
                        $log = Log::create(['ticket_id'=>$ticket->id,'agent_id'=>$agent->id,'client_uuid'=>$uuid,'scanned_at'=>$scannedAt,'scan_date'=>$scanDate,'lease_id'=>$lease->id,'source'=>'offline_sync','type_operation'=>'scan','resultat'=>'invalide','server_resultat'=>'invalide','details'=>['code'=>$code,'raison'=>'Paiement non confirmé'],'ip'=>request()->ip(),'user_agent'=>request()->userAgent(),'synced_at'=>now()]);
                        return ['statut'=>'rejete','resultat_serveur'=>'invalide','raison'=>'Paiement non confirmé'];
                    }
                    if($ticket->annule){
                        $log = Log::create(['ticket_id'=>$ticket->id,'agent_id'=>$agent->id,'client_uuid'=>$uuid,'scanned_at'=>$scannedAt,'scan_date'=>$scanDate,'lease_id'=>$lease->id,'source'=>'offline_sync','type_operation'=>'scan','resultat'=>'invalide','server_resultat'=>'invalide','details'=>['code'=>$code,'raison'=>'Ticket annulé'],'ip'=>request()->ip(),'user_agent'=>request()->userAgent(),'synced_at'=>now()]);
                        return ['statut'=>'rejete','resultat_serveur'=>'invalide','raison'=>'Ticket annulé'];
                    }
                    $already = TicketScanDay::where('ticket_id',$ticket->id)->where('scan_date',$scanDate)->first();
                    if($already){
                        $log = Log::create(['ticket_id'=>$ticket->id,'agent_id'=>$agent->id,'client_uuid'=>$uuid,'scanned_at'=>$scannedAt,'scan_date'=>$scanDate,'lease_id'=>$lease->id,'source'=>'offline_sync','type_operation'=>'scan','resultat'=>'deja_utilise','server_resultat'=>'deja_utilise','details'=>['code'=>$code,'raison'=>'Déjà scanné aujourd\'hui'],'ip'=>request()->ip(),'user_agent'=>request()->userAgent(),'synced_at'=>now()]);
                        return ['statut'=>'deja_utilise','resultat_serveur'=>'deja_utilise','raison'=>'Déjà scanné aujourd\'hui'];
                    }
                    $log = Log::create(['ticket_id'=>$ticket->id,'agent_id'=>$agent->id,'client_uuid'=>$uuid,'scanned_at'=>$scannedAt,'scan_date'=>$scanDate,'lease_id'=>$lease->id,'source'=>'offline_sync','type_operation'=>'scan','resultat'=>'valide','server_resultat'=>'valide','details'=>['code'=>$code,'raison'=>'Scan réussi'],'ip'=>request()->ip(),'user_agent'=>request()->userAgent(),'synced_at'=>now()]);
                    try { TicketScanDay::create(['ticket_id'=>$ticket->id,'scan_date'=>$scanDate,'log_id'=>$log->id,'agent_id'=>$agent->id]); } catch (\Throwable $e) { $log->update(['resultat'=>'deja_utilise','server_resultat'=>'deja_utilise','details'=>['code'=>$code,'raison'=>'Déjà scanné aujourd\'hui']]); return ['statut'=>'deja_utilise','resultat_serveur'=>'deja_utilise','raison'=>'Déjà scanné aujourd\'hui']; }
                    return ['statut'=>'accepte','resultat_serveur'=>'valide','raison'=>null];
                });
                $lease->update(['last_sync_at'=>now()]);
                $results[] = array_merge(['uuid'=>$uuid], $res);
            } catch (\Throwable $e) {
                $results[] = ['uuid'=>$uuid,'statut'=>'rejete','resultat_serveur'=>'invalide','raison'=>'Erreur serveur'];
            }
        }
        return ['server_time'=>Carbon::now()->toIso8601String(),'lease_state'=>$lease->fresh()->statut,'last_version'=>0,'results'=>$results,'conflicts'=>0,'provisional'=>0];
    }
}
