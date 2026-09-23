<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consentement extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'statut',
        'services',
        'version_politique',
        'ip_visiteur',
    ];

    protected $casts = [
        'services' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listeServices(): array
    {
        return $this->services ?? [];
    }

    public function libelleStatut(): string
    {
        return match ($this->statut) {
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            'personnalise' => 'Personnalisé',
            default => $this->statut,
        };
    }

    public function badgeStatut(): string
    {
        return match ($this->statut) {
            'accepte' => 'success',
            'refuse' => 'danger',
            default => 'warning',
        };
    }
}