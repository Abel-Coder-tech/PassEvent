<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvenementDate extends Model
{
    use HasFactory;

    protected $table = 'evenement_dates';

    protected $fillable = [
        'evenement_id',
        'date_debut',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'ordre' => 'integer',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    // Heure de fin de la fenêtre de scan (début + intervalle de tolérance)
    public function finFenetreScan(): \Carbon\CarbonInterface
    {
        return $this->date_debut->copy()->addHours(Evenement::DUREE_SCAN_HEURES);
    }
}