<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventWaitlist extends Model
{
    protected $table = 'event_waitlist';

    protected $fillable = [
        'evenement_id',
        'tarif_id',
        'nom_acheteur',
        'email_acheteur',
        'telephone_acheteur',
        'quantite',
        'code_promo_utilise',
        'montant_unitaire',
        'montant_reduction',
        'statut',
        'place_offerte_le',
        'expire_le',
    ];

    protected function casts(): array
    {
        return [
            'quantite'           => 'integer',
            'montant_unitaire'   => 'integer',
            'montant_reduction'  => 'integer',
            'place_offerte_le'   => 'datetime',
            'expire_le'          => 'datetime',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }
}
