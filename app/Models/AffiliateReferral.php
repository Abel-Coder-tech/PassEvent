<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Mecanisme A : parrainage direct d'un organisateur par un affilie.
class AffiliateReferral extends Model
{
    protected $fillable = [
        'affiliate_id',
        'organisateur_id',
        'code_parrainage',
        'premier_evenement_id',
        'statut',
        'valide_le',
    ];

    protected function casts(): array
    {
        return [
            'valide_le' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function organisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organisateur_id');
    }

    public function premierEvenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'premier_evenement_id');
    }
}
