<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
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

    // Valide un code pour un événement + tarif, retourne le code ou lève une erreur de validation
    public static function validerPour(string $code, Evenement $evenement, Tarif $tarif): ?CodePromo
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        $codePromo = self::where('code', $code)
            ->whereHas('tarif', fn($q) => $q->where('evenement_id', $evenement->id))
            ->first();

        if (!$codePromo) {
            throw ValidationException::withMessages(['code_promo' => 'Ce code promo n\'est pas valide pour cet événement.']);
        }

        if (!$codePromo->estValide()) {
            if (!$codePromo->actif) {
                throw ValidationException::withMessages(['code_promo' => 'Ce code promo a été désactivé.']);
            }
            if ($codePromo->date_expiration && $codePromo->date_expiration < now()) {
                throw ValidationException::withMessages(['code_promo' => 'Ce code promo a expiré.']);
            }
            if ($codePromo->max_utilisations && $codePromo->nb_utilisations >= $codePromo->max_utilisations) {
                throw ValidationException::withMessages(['code_promo' => 'Ce code promo a atteint son nombre maximum d\'utilisations.']);
            }
        }

        if ($codePromo->tarif_id !== $tarif->id) {
            throw ValidationException::withMessages(['code_promo' => 'Ce code promo n\'est pas compatible avec le tarif sélectionné.']);
        }

        return $codePromo;
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