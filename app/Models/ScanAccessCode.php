<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanAccessCode extends Model
{
    protected $fillable = ['evenement_id', 'code', 'actif'];

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }
}
