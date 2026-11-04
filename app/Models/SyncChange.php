<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SyncChange extends Model {
    public $timestamps = false;
    protected $table = 'sync_changes';
    protected $fillable = ['evenement_id','entity_type','action','entity_id','created_at'];
    protected function casts(): array { return ['created_at'=>'datetime']; }
}
