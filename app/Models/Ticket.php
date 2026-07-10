<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    protected $fillable = [
        'evenement_id',
        'tarif_id',
        'agent_vente_id',
        'code_unique',
        'qr_signature',
        'email_acheteur',
        'telephone_acheteur',
        'telephone_paiement',
        'nom_acheteur',
        'categorie',
        'type',
        'montant',
        'montant_reduction',
        'quantite',
        'statut_paiement',
        'transaction_id',
        'methode_paiement',
        'utilise',
        'date_achat',
        'code_promo_utilise',
        'agent_vente_id',
    ];

    public static function methodePaiementLabel(?string $methode): string
    {
        return match ($methode) {
            'cash', 'especes' => 'Espèces',
            'mtn' => 'MTN MoMo',
            'moov' => 'Moov Money',
            'celtiis' => 'Celtiis Cash',
            'mobile_money', null => 'Mobile',
            default => ucfirst($methode),
        };
    }

    public function getMethodePaiementLabelAttribute(): string
    {
        return static::methodePaiementLabel($this->methode_paiement);
    }

    public function getLabel(): string
    {
        $categorie = ucfirst($this->categorie);
        $type = ucfirst($this->type);
        return "{$categorie} / {$type}";
    }

    protected function casts(): array
    {
        return [
            'utilise' => 'boolean',
            'date_achat' => 'datetime',
            'montant' => 'decimal:2',
            'quantite' => 'integer',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function agentVente(): BelongsTo
    {
        return $this->belongsTo(AgentVente::class, 'agent_vente_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}