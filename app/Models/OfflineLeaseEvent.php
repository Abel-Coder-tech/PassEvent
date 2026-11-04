<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OfflineLeaseEvent extends Model {
    public $timestamps = false;
    protected $table = 'offline_lease_events';
    protected $fillable = ['offline_lease_id','action','actor_type','actor_id','payload','created_at'];
    protected function casts(): array { return ['payload'=>'array','created_at'=>'datetime']; }
    public function lease(): BelongsTo { return $this->belongsTo(OfflineLease::class, 'offline_lease_id'); }
}
