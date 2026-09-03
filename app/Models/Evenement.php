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

    // Intervalle (en heures) après l'heure de début pendant lequel le scan reste valide
    public const DUREE_SCAN_HEURES = 6;

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
        'type_evenement',
        'gratuit',
        'ventes_fermees',
        'ventes_especes',
        'commission_pourcentage',
        'max_agents_vente',
        'a_la_une',
        'a_la_une_ordre',
    ];

    protected function casts(): array
    {
        return [
            'date_event' => 'datetime',
            'gratuit' => 'boolean',
            'ventes_fermees' => 'boolean',
            'commission_pourcentage' => 'float',
            'max_agents_vente' => 'integer',
            'a_la_une' => 'boolean',
            'a_la_une_ordre' => 'integer',
        ];
    }

    // Scope : événements mis à la une, triés par ordre
    public function scopeALaUne($query)
    {
        return $query->where('a_la_une', true)
            ->orderBy('a_la_une_ordre')
            ->orderBy('date_event');
    }

    public function getTextes(): array
    {
        return match ($this->type_evenement) {
            'formation' => [
                'type' => 'formation',
                'acheter' => "S'inscrire",
                'acheter_billet' => "S'inscrire à la formation",
                'cloturee' => 'Inscriptions clôturées',
                'billet' => "Confirmation d'inscription",
                'billet_pluriel' => "Confirmations d'inscription",
                'participer_gratuit' => "S'inscrire gratuitement",
                'places' => 'places disponibles',
                'presenter' => "Présentez votre confirmation d'inscription à l'entrée",
            ],
            'conference' => [
                'type' => 'conference',
                'acheter' => "S'inscrire",
                'acheter_billet' => "S'inscrire à la conférence",
                'cloturee' => 'Inscriptions clôturées',
                'billet' => 'Place',
                'billet_pluriel' => 'Places',
                'participer_gratuit' => "S'inscrire gratuitement",
                'places' => 'places disponibles',
                'presenter' => 'Présentez votre confirmation à l\'entrée',
            ],
            default => [
                'type' => 'spectacle',
                'acheter' => 'Acheter un billet',
                'acheter_billet' => 'Acheter un billet',
                'cloturee' => 'Vente clôturée',
                'billet' => 'Billet',
                'billet_pluriel' => 'Billets',
                'participer_gratuit' => 'Participer gratuitement',
                'places' => 'places disponibles',
                'presenter' => 'Présentez votre billet à l\'entrée de l\'événement',
            ],
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }

    public function demandesModificationTarif(): HasMany
    {
        return $this->hasMany(DemandeModificationTarif::class, 'evenement_id');
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

    public function scanAccessCodes(): HasMany
    {
        return $this->hasMany(ScanAccessCode::class);
    }

    // Sessions/jours de l'événement (multi-jours), triées par ordre
    public function dates(): HasMany
    {
        return $this->hasMany(EvenementDate::class, 'evenement_id')->orderBy('ordre')->orderBy('date_debut');
    }

    // Dernière session de l'événement (pour expiration des ventes et scans)
    public function derniereDate(): ?EvenementDate
    {
        return $this->dates()->orderByDesc('date_debut')->first();
    }

    // Session (jour) en cours si aujourd'hui est un jour d'événement, dans l'intervalle de scan
    public function jourScanActuel(): ?EvenementDate
    {
        $debutJour = now()->startOfDay();
        $finJour = now()->copy()->endOfDay();

        return $this->dates()
            ->whereBetween('date_debut', [$debutJour, $finJour])
            ->first();
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

    // Commission effective : spécifique (événement) > défaut (organisateur) > global 10 %
    public function commissionEffective(): float
    {
        return (float) ($this->commission_pourcentage ?? $this->user?->commission_pourcentage ?? 10);
    }

    public function ventesEspecesActivees(): bool
    {
        if ($this->gratuit) {
            return true;
        }

        // Contrôle superadmin : spécifique (événement) > défaut (organisateur) > règle 15 %
        $statut = $this->ventes_especes ?? $this->user?->ventes_especes;

        if ($statut === 'toujours') {
            return true;
        }

        if ($statut === 'jamais') {
            return false;
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

    // Les ventes espèces sont-elles explicitement bloquées par un superadmin ?
    public function ventesEspecesBloqueesSuperadmin(): bool
    {
        if ($this->gratuit) {
            return false;
        }

        return ($this->ventes_especes ?? $this->user?->ventes_especes) === 'jamais';
    }

    // Statut effectif pour l'affichage superadmin : événement passé (dernière date dépassée), sauf annulé
    public function statutEffectif(): string
    {
        $derniereDate = $this->derniereDate();
        if ($derniereDate && $derniereDate->date_debut->isPast() && $this->statut !== 'annulé') {
            return 'passé';
        }

        return $this->statut;
    }

    // Limite d'agents de vente : vide = 2 (défaut), 0 = illimité
    public function limiteAgentsVente(): ?int
    {
        $attribution = $this->attributionAgents();
        if ($attribution) {
            return $attribution->nbAgentsVente();
        }

        if ($this->max_agents_vente === 0) {
            return null; // illimité
        }

        return $this->max_agents_vente ?? 2;
    }

    // Limite d'agents de scan : attribution superadmin (0 = illimité) > défaut 2
    public function limiteAgentsScan(): ?int
    {
        $attribution = $this->attributionAgents();
        if ($attribution) {
            return $attribution->nbAgentsScan();
        }

        return 2;
    }

    // Attribution superadmin (spécifique à l'événement sinon globale au dashboard de l'organisateur)
    public function attributionAgents(): ?AttributionAgent
    {
        return AttributionAgent::where('user_id', $this->user_id)
            ->where(fn ($q) => $q->where('evenement_id', $this->id)->orWhereNull('evenement_id'))
            ->orderByRaw('evenement_id IS NULL') // spécifique d'abord, dashboard ensuite
            ->first();
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
