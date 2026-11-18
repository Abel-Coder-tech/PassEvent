<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Demande « Me prévenir à l'ouverture » d'un visiteur pour un événement.
class BilletterieAlerte extends Model
{
    protected $table = 'billetterie_alertes';

    protected $fillable = ['evenement_id', 'email', 'notifie_le'];

    protected $casts = ['notifie_le' => 'datetime'];

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }
}
