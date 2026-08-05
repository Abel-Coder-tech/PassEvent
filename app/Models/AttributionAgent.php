<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributionAgent extends Model
{
    use HasFactory;

    protected $table = 'attributions_agents';

    protected $fillable = [
        'user_id',
        'evenement_id',
        'nb_agents_scan',
        'nb_agents_vente',
    ];

    protected function casts(): array
    {
        return [
            'nb_agents_scan' => 'integer',
            'nb_agents_vente' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    // Nombre d'agents de scan autorisés (null = illimité)
    public function nbAgentsScan(): ?int
    {
        return $this->nb_agents_scan > 0 ? (int) $this->nb_agents_scan : null;
    }

    // Nombre d'agents de vente autorisés (null = illimité)
    public function nbAgentsVente(): ?int
    {
        return $this->nb_agents_vente > 0 ? (int) $this->nb_agents_vente : null;
    }
}
