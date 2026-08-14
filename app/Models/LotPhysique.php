<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotPhysique extends Model
{
    protected $table = 'lots_physiques';

    protected $fillable = [
        'user_id',
        'evenement_id',
        'tarif_id',
        'commission_pourcentage',
        'nom',
        'quantite',
        'statut',
        'download_count',
        'transmis_at',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'download_count' => 'integer',
            'commission_pourcentage' => 'decimal:2',
            'transmis_at' => 'datetime',
        ];
    }

    // Commission effective du lot : spécifique (lot) > événement > organisateur > global 10 %
    public function commissionEffective(): float
    {
        if ($this->commission_pourcentage !== null && $this->commission_pourcentage !== '') {
            return (float) $this->commission_pourcentage;
        }

        return $this->evenement?->commissionEffective() ?? 10;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'lot_physique_id');
    }

    public function ticketsAnnules(): HasMany
    {
        return $this->hasMany(Ticket::class, 'lot_physique_id')->where('annule', true);
    }

    public function getNombreAnnulesAttribute(): int
    {
        return $this->ticketsAnnules()->count();
    }

    public function getNombreRestantsAttribute(): int
    {
        return max(0, $this->quantite - $this->ticketsAnnules()->count());
    }

    public function getEstTransmisAttribute(): bool
    {
        return $this->statut === 'transmis' && $this->transmis_at !== null;
    }
}
