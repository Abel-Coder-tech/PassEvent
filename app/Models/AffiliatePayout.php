<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Demande de reversement des commissions affilie.
class AffiliatePayout extends Model
{
    protected $fillable = [
        'affiliate_id',
        'montant',
        'operateur',
        'numero_paiement',
        'titulaire',
        'statut',
        'note',
        'traite_le',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'traite_le' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
}
