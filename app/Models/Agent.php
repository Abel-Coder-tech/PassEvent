<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    protected $fillable = [
        'nom',
        'email',
        'password',
        'evenement_id',
        'code_acces',
        'actif',
        'tentatives_code',
        'bloque_jusqua',
        'dernier_acces',
    ];

    protected $hidden = [
        'password',
        'code_acces',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'tentatives_code' => 'integer',
            'bloque_jusqua' => 'datetime',
            'dernier_acces' => 'datetime',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'agent_id');
    }
}
