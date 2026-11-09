<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalAction extends Model
{
    use HasFactory;

    protected $table = 'journal_actions';

    protected $fillable = [
        'ticket_id',
        'agent_vente_id',
        'evenement_id',
        'action',
        'statut_avant',
        'statut_apres',
        'reference',
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

    // Point d'entrée unique pour tracer une action sur une vente.
    public static function enregistrer(array $data): self
    {
        return self::create([
            'ticket_id' => $data['ticket_id'] ?? null,
            'agent_vente_id' => $data['agent_vente_id'] ?? null,
            'evenement_id' => $data['evenement_id'] ?? null,
            'action' => $data['action'],
            'statut_avant' => $data['statut_avant'] ?? null,
            'statut_apres' => $data['statut_apres'] ?? null,
            'reference' => $data['reference'] ?? null,
            'details' => $data['details'] ?? null,
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function agentVente(): BelongsTo
    {
        return $this->belongsTo(AgentVente::class, 'agent_vente_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function payload(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'statut_avant' => $this->statut_avant,
            'statut_apres' => $this->statut_apres,
            'reference' => $this->reference,
            'details' => $this->details ?? [],
            'date' => $this->created_at?->toIso8601String(),
        ];
    }
}
