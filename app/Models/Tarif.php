<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'evenement_id',
        'nom',
        'prix',
        'quantite_disponible',
        'quantite_vendue',
        'statut',
    ];

    public function getLabel(): string
    {
        return $this->nom;
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function codesPromos(): HasMany
    {
        return $this->hasMany(CodePromo::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function lotsPhysiques(): HasMany
    {
        return $this->hasMany(LotPhysique::class);
    }

    public function demandesModification(): HasMany
    {
        return $this->hasMany(DemandeModificationTarif::class, 'tarif_id');
    }
}