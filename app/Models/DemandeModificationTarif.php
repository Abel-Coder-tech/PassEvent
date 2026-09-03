<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeModificationTarif extends Model
{
    protected $table = 'demandes_modification_tarif';

    protected $fillable = [
        'evenement_id',
        'tarif_id',
        'user_id',
        'ancien_prix',
        'nouveau_prix',
        'statut',
        'notes',
        'traitee_le',
    ];

    protected $casts = [
        'ancien_prix' => 'decimal:2',
        'nouveau_prix' => 'decimal:2',
        'traitee_le' => 'datetime',
    ];

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
