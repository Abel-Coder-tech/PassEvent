<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AgentVente extends Authenticatable
{
    protected $table = 'agents_vente';

    protected $fillable = [
        'nom',
        'email',
        'password',
        'evenement_id',
        'code_vente',
        'actif',
        'tickets_count',
        'montant_total',
        'dernier_acces',
    ];

    protected $hidden = [
        'password',
        'code_vente',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'tickets_count' => 'integer',
            'montant_total' => 'decimal:2',
            'dernier_acces' => 'datetime',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'agent_vente_id');
    }
}
