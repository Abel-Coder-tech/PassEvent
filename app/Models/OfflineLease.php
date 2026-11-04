<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OfflineLease extends Model {
    protected $table = 'offline_leases';
    protected $fillable = ['evenement_id','agent_id','statut','active_slot','snapshot_version','snapshot_checksum','authorized_at','activated_at','expires_at','revocation_requested_at','revoked_at','revocation_acked_at','force_revoked_at','authorized_by','revoked_by','last_seen_at','last_sync_at'];
    protected function casts(): array {
        return ['authorized_at'=>'datetime','activated_at'=>'datetime','expires_at'=>'datetime','revocation_requested_at'=>'datetime','revoked_at'=>'datetime','revocation_acked_at'=>'datetime','force_revoked_at'=>'datetime','last_seen_at'=>'datetime','last_sync_at'=>'datetime'];
    }
    public function evenement(): BelongsTo { return $this->belongsTo(Evenement::class, 'evenement_id'); }
    public function agent(): BelongsTo { return $this->belongsTo(Agent::class, 'agent_id'); }
    public function authorizedBy(): BelongsTo { return $this->belongsTo(User::class, 'authorized_by'); }
    public function revokedBy(): BelongsTo { return $this->belongsTo(User::class, 'revoked_by'); }
    public function events(): HasMany { return $this->hasMany(OfflineLeaseEvent::class, 'offline_lease_id'); }
    public function isActive(): bool { return in_array($this->statut, ['actif', 'revocation_en_attente'], true); }
}
