<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentNotification extends Model
{
    protected $fillable = [
        'agent_id',
        'evenement_id',
        'titre',
        'message',
        'lu_at',
    ];

    protected function casts(): array
    {
        return [
            'lu_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }
}
