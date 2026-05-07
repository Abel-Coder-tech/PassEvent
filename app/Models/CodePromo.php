<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Evenement;
use App\Models\Tarif;

class CodePromo extends Model
{
    use HasFactory;

    protected $table = 'codes_promos';

    protected $fillable = [
        'evenement_id',
        'tarif_id',
        'code',
        'type_reduction',
        'valeur_reduction',
        'max_utilisations',
        'nb_utilisations',
        'date_expiration',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'date_expiration' => 'datetime',
            'valeur_reduction' => 'decimal:2',
        ];
    }
        public function estValide(): bool
    {
        if (!$this->actif) {
            return false;
        }

        if ($this->date_expiration && $this->date_expiration < now()) {
            return false;
        }

        if ($this->max_utilisations && $this->nb_utilisations >= $this->max_utilisations) {
            return false;
        }

        return true;
    }

    public function calculerReduction(float $prixUnitaire): float
    {
        if ($this->type_reduction === 'fixe') {
            return min($this->valeur_reduction, $prixUnitaire);
        }

        return round($prixUnitaire * ($this->valeur_reduction / 100), 2);
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'code', 'code_promo_utilise');
    }
}