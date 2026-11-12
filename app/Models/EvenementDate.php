<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvenementDate extends Model
{
    use HasFactory;

    protected $table = 'evenement_dates';

    protected $fillable = [
        'evenement_id',
        'date_debut',
        'ordre',
        'capacite',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'ordre' => 'integer',
            'capacite' => 'integer',
        ];
    }

    // Jauge effective du jour : celle du jour si elle est définie, sinon
    // celle de l'événement (compatibilité et repli pour les événements
    // mono-jour).
    public function capaciteEffective(): int
    {
        return (int) ($this->capacite ?? $this->evenement?->capacite ?? 0);
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class, 'evenement_date_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'evenement_date_id');
    }

    // Heure de fin de la fenêtre de scan (début + intervalle de tolérance)
    public function finFenetreScan(): \Carbon\CarbonInterface
    {
        return $this->date_debut->copy()->addHours(Evenement::DUREE_SCAN_HEURES);
    }

    // Fin de la vente pour ce jour : minuit du jour de la session.
    public function finVente(): \Carbon\CarbonInterface
    {
        return $this->date_debut->copy()->endOfDay();
    }

    // Jour terminé pour la vente (minuit passé).
    public function estTermine(): bool
    {
        return now()->gt($this->finVente());
    }

    // Libellé affiché pour ce jour (ex. « Jour 2 »).
    public function libelle(): string
    {
        return 'Jour '.($this->ordre + 1);
    }
}
