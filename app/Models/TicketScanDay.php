<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TicketScanDay extends Model {
    protected $table = 'ticket_scan_days';
    protected $fillable = ['ticket_id','scan_date','log_id','agent_id'];
    protected function casts(): array { return ['scan_date'=>'date']; }
    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function log(): BelongsTo { return $this->belongsTo(Log::class); }
    public function agent(): BelongsTo { return $this->belongsTo(Agent::class); }
}
