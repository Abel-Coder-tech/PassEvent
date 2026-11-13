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
        'evenement_date_id',
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

    // Jour auquel ce tarif est rattaché. `null` = valable toutes les dates
    // de l'événement (packs et tarifs historiques).
    public function jour(): BelongsTo
    {
        return $this->belongsTo(EvenementDate::class, 'evenement_date_id');
    }

    // Le stock propre du tarif (illimité si null) permet-il `$quantite` ventes de plus ?
    public function aStockPour(int $quantite): bool
    {
        return $this->quantite_disponible === null
            || (int) $this->quantite_vendue + $quantite <= (int) $this->quantite_disponible;
    }

    public function estPack(): bool
    {
        return $this->evenement_date_id === null;
    }

    // Billet à la journée : vendable jusqu'à minuit de son jour.
    // Pack : vendable jusqu'à minuit du premier jour payant de l'événement
    // (un premier jour gratuit ne ferme donc pas la vente des packs).
    public function estEncoreVendable(): bool
    {
        if ($this->evenement_date_id !== null) {
            $jour = $this->relationLoaded('jour') ? $this->jour : $this->jour()->first();

            return $jour !== null && ! $jour->estTermine();
        }

        $fin = $this->evenement?->finVentePacks();

        return $fin === null || now()->lte($fin);
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