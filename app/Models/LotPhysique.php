<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotPhysique extends Model
{
    protected $table = 'lots_physiques';

    // Taux de commission fixe et non négociable pour l'auto-génération
    public const TAUX_AUTO = 5.0;

    protected $fillable = [
        'user_id',
        'evenement_id',
        'tarif_id',
        'commission_pourcentage',
        'nom',
        'quantite',
        'statut',
        'auto_genere',
        'montant_commission',
        'email_reception',
        'fedapay_transaction_id',
        'reference_paiement',
        'download_count',
        'transmis_at',
        'template_path',
        'qr_x',
        'qr_y',
        'qr_size',
        'pdf_par_page',
        'format',
    ];

    // Formats de tickets physiques (taille nominale → slot A4 avec marges/gouttières)
    public const FORMATS = [
        's1' => [
            'label' => 'Standard (14×5)',
            'largeur' => 137, // mm (slot, ratio 2.8:1 préservé)
            'hauteur' => 49,
            'orientation' => 'landscape',
            'colonnes' => 2,
            'lignes' => 4,
            'qr_defaut' => 30,
        ],
        's2' => [
            'label' => 'Standard 2 (14×7)',
            'largeur' => 132,
            'hauteur' => 66,
            'orientation' => 'landscape',
            'colonnes' => 2,
            'lignes' => 3,
            'qr_defaut' => 34,
        ],
        'v1' => [
            'label' => 'VIP (18×7)',
            'largeur' => 180,
            'hauteur' => 70,
            'orientation' => 'portrait',
            'colonnes' => 1,
            'lignes' => 3,
            'qr_defaut' => 48,
        ],
        'v2' => [
            'label' => 'VIP 2 (9,9×7)',
            'largeur' => 99,
            'hauteur' => 70,
            'orientation' => 'portrait',
            'colonnes' => 1,
            'lignes' => 3,
            'qr_defaut' => 42,
        ],
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'download_count' => 'integer',
            'commission_pourcentage' => 'decimal:2',
            'auto_genere' => 'boolean',
            'montant_commission' => 'decimal:2',
            'transmis_at' => 'datetime',
            'qr_x' => 'integer',
            'qr_y' => 'integer',
            'qr_size' => 'integer',
            'pdf_par_page' => 'integer',
            'format' => 'string',
        ];
    }

    // Détails du format choisi (fallback s1)
    public function formatDetails(): array
    {
        $format = isset(self::FORMATS[$this->format]) ? $this->format : 's1';

        return self::FORMATS[$format];
    }

    // Commission effective du lot : spécifique (lot) > événement > organisateur > global 10 %
    public function commissionEffective(): float
    {
        if ($this->commission_pourcentage !== null && $this->commission_pourcentage !== '') {
            return (float) $this->commission_pourcentage;
        }

        return $this->evenement?->commissionEffective() ?? 10;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'lot_physique_id');
    }

    public function ticketsAnnules(): HasMany
    {
        return $this->hasMany(Ticket::class, 'lot_physique_id')->where('annule', true);
    }

    public function getNombreAnnulesAttribute(): int
    {
        return $this->ticketsAnnules()->count();
    }

    public function getNombreRestantsAttribute(): int
    {
        return max(0, $this->quantite - $this->ticketsAnnules()->count());
    }

    public function getEstTransmisAttribute(): bool
    {
        return $this->statut === 'transmis' && $this->transmis_at !== null;
    }

    public function aUnTemplate(): bool
    {
        if ($this->template_path === null || $this->qr_x === null || $this->qr_y === null) {
            return false;
        }

        return file_exists(storage_path("app/public/{$this->template_path}"));
    }

    public function getTemplateUrlAttribute(): ?string
    {
        if (! $this->template_path) {
            return null;
        }

        return asset('storage/'.$this->template_path);
    }
}
