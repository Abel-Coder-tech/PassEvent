<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Affiliate extends Authenticatable
{
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'pays',
        'password',
        'code_parrainage',
        'operateur_paiement',
        'numero_paiement',
        'titulaire_paiement',
        'solde_en_attente',
        'solde_disponible',
        'total_gagne',
        'actif',
        'dernier_acces',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dernier_acces' => 'datetime',
            'actif' => 'boolean',
            'solde_en_attente' => 'decimal:2',
            'solde_disponible' => 'decimal:2',
            'total_gagne' => 'decimal:2',
        ];
    }

    // Parrainages directs realises (organisateurs apportes)
    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    // Liens de co-parrainage generes par cet affilie
    public function eventLinks(): HasMany
    {
        return $this->hasMany(AffiliateEventLink::class);
    }

    // Commissions generees (mecanismes A et B)
    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    // Demandes de reversement
    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    // Nom complet affichable
    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ? $this->prenom.' ' : '').$this->nom);
    }

    // Genere un code de parrainage unique (ex. PAXAFF-7K2QP)
    public static function genererCodeParrainage(): string
    {
        do {
            $code = 'PAXAFF-'.strtoupper(Str::random(6));
        } while (static::where('code_parrainage', $code)->exists());

        return $code;
    }
}
