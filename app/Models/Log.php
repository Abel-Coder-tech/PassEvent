<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'agent_id',
        'type_operation',
        'details',
        'ip',
        'user_agent',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Scans rattachés à un événement : l'événement est enregistré dans les détails
    // du scan (y compris pour un code inconnu, sans ticket). Repli sur le ticket
    // pour les anciens logs qui n'ont pas cette information.
    public function scopePourEvenement($query, int $evenementId)
    {
        return $query->where(function ($q) use ($evenementId) {
            $q->where('details->evenement_id', $evenementId)
                ->orWhere(function ($q) use ($evenementId) {
                    $q->whereNull('details->evenement_id')
                        ->whereHas('ticket', fn ($t) => $t->where('evenement_id', $evenementId));
                });
        });
    }
}
