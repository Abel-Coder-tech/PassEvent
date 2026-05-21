<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evenement extends Model
{
    use HasFactory;

    protected $table = 'evenement';

    protected $fillable = [
        'user_id',
        'titre',
        'description',
        'date_event',
        'lieu',
        'categorie',
        'capacite',
        'quota_vendu',
        'date_fin_vente',
        'image',
        'statut',
        'gratuit',
    ];

    protected function casts(): array
    {
        return [
            'date_event' => 'datetime',
            'date_fin_vente' => 'datetime',
            'gratuit' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }

    public function codesPromos(): HasMany
    {
        return $this->hasMany(CodePromo::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scanAccessCodes(): HasMany
    {
        return $this->hasMany(ScanAccessCode::class, 'evenement_id');
    }
}