<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Mecanisme B : lien de co-parrainage d'un affilie sur un evenement ouvert.
class AffiliateEventLink extends Model
{
    protected $fillable = [
        'evenement_id',
        'affiliate_id',
        'code',
        'pourcentage',
        'actif',
        'clics',
    ];

    protected function casts(): array
    {
        return [
            'pourcentage' => 'decimal:2',
            'actif' => 'boolean',
            'clics' => 'integer',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
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
