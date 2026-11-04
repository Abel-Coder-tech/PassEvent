<?php
namespace App\Services;
use App\Models\OfflineLease;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
class OfflineManifestService {
    public function build(OfflineLease $lease, int $page = 1, int $perPage = 500): array {
        $evenement = $lease->evenement()->with(['dates','tarifs'])->first();
        $now = Carbon::now($evenement->fuseau_horaire ?: config('app.timezone'));
        $query = Ticket::where('evenement_id',$evenement->id)->where('statut_paiement','payé')->where('annule',false)->with('tarif');
        $total = $query->count();
        $tickets = $query->skip(($page-1)*$perPage)->take($perPage)->get();
        $items = [];
        foreach($tickets as $ticket){
            $items[] = ['code'=>$ticket->code_ticket,'ticket_id'=>$ticket->id,'tarif_id'=>$ticket->tarif_id,'nom_tarif'=>$ticket->nom_tarif,'montant'=>$ticket->montant,'scanned_today'=>$ticket->dejaScanneAujourdhui(),'sessions'=>$evenement->dates->map(fn($d)=>['id'=>$d->id,'date_debut'=>$d->date_debut?->toIso8601String(),'date_fin'=>$d->date_fin?->toIso8601String()])->values()->all()];
        }
        $checksum = hash('sha256', json_encode($items));
        return ['evenement'=>['id'=>$evenement->id,'titre'=>$evenement->titre,'fuseau_horaire'=>$evenement->fuseau_horaire?:config('app.timezone'),'dates'=>$evenement->dates->map(fn($d)=>['id'=>$d->id,'date_debut'=>$d->date_debut?->toIso8601String(),'date_fin'=>$d->date_fin?->toIso8601String()])->values()->all()],'lease'=>['id'=>$lease->id,'statut'=>$lease->statut,'expires_at'=>$lease->expires_at?->toIso8601String()],'server_time'=>$now->toIso8601String(),'snapshot_version'=>$lease->snapshot_version,'checksum'=>$checksum,'page'=>$page,'per_page'=>$perPage,'total'=>$total,'has_more'=>($page*$perPage)<$total,'tickets'=>$items,'messages'=>['valide'=>'Ticket validé avec succès !','invalide'=>'Ticket invalide','deja_utilise'=>'Ce billet a déjà été scanné aujourd\'hui.','session_ko'=>'Hors session / fenêtre de scan']];
    }
}
