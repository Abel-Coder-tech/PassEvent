<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumeroRetrait extends Model
{
    protected $table = 'numeros_retrait';

    protected $fillable = [
        'user_id',
        'operateur',
        'nom',
        'mobile',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
