<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evenement extends Model
{
    use HasFactory;

    protected $table = 'evenement';

    protected $fillable = [
        'user_id',
        'titre',
        'description',
        'date_event',
        'lieu',
        'categorie',
        'capacite',
        'quota_vendu',
        'image',
        'statut',
        'gratuit',
        'ventes_fermees',
    ];

    protected function casts(): array
    {
        return [
            'date_event' => 'datetime',
            'gratuit' => 'boolean',
            'ventes_fermees' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }

    public function codesPromos(): HasMany
    {
        return $this->hasMany(CodePromo::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scanAccessCodes(): HasMany
    {
        return $this->hasMany(ScanAccessCode::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function agentsVentes(): HasMany
    {
        return $this->hasMany(AgentVente::class, 'evenement_id');
    }

    // Seuil mobile money atteint pour débloquer les ventes espèces (15 % de la capacité)
    public const SEUIL_ESPECES_PCT = 15;

    public function ventesEspecesActivees(): bool
    {
        if ($this->gratuit) {
            return true;
        }

        $seuil = (int) ceil($this->capacite * self::SEUIL_ESPECES_PCT / 100);

        if ($seuil <= 0) {
            return true;
        }

        $vendusEnLigne = $this->tickets()
            ->where('statut_paiement', 'payé')
            ->where('methode_paiement', '!=', 'especes')
            ->where('methode_paiement', '!=', 'cash')
            ->whereNotNull('methode_paiement')
            ->count();

        return $vendusEnLigne >= $seuil;
    }

    // Nombre de tickets vendus en ligne (hors espèces) pour le suivi du seuil
    public function ticketsEnLigneCount(): int
    {
        return $this->tickets()
            ->where('statut_paiement', 'payé')
            ->where('methode_paiement', '!=', 'especes')
            ->where('methode_paiement', '!=', 'cash')
            ->whereNotNull('methode_paiement')
            ->count();
    }

    // Nombre de tickets nécessaires pour atteindre le seuil
    public function ticketsRestantsSeuil(): int
    {
        $seuil = (int) ceil($this->capacite * self::SEUIL_ESPECES_PCT / 100);
        $vendus = $this->ticketsEnLigneCount();

        return max(0, $seuil - $vendus);
    }
}