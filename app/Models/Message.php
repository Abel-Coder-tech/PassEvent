<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
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
}
