<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Code QR d'un lot externe : imprimé sur le ticket, non lié à un billet de la plateforme
class CodeQrExterne extends Model
{
    protected $table = 'codes_qr_externes';

    protected $fillable = ['lot_physique_id', 'code'];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(LotPhysique::class, 'lot_physique_id');
    }
}
