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
        'statut_paiement',
        'transaction_id',
        'methode_paiement',
        'utilise',
        'date_achat',
        'code_promo_utilise',
    ];

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

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}