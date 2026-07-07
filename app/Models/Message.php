<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'evenement_id',
        'nom_complet',
        'email',
        'objet',
        'message',
        'lu',
        'reponse_admin',
        'date_reponse',
    ];

    protected $casts = [
        'lu' => 'boolean',
        'date_reponse' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }
}
