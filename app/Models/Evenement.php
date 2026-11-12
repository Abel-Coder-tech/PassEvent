<?php

namespace App\Models;

use App\Observers\EvenementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[ObservedBy(EvenementObserver::class)]
class Evenement extends Model
{
    use HasFactory;

    protected $table = 'evenement';

    private ?bool $multiJourCache = null;

    /** @var array<int, int> */
    private array $occupationCache = [];

    // Intervalle (en heures) après l'heure de début pendant lequel le scan reste valide
    public const DUREE_SCAN_HEURES = 6;

    protected $fillable = [
        'user_id',
        'titre',
        'slug',
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
        'co_parrainage_actif',
        'co_parrainage_pourcentage',
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
            'co_parrainage_actif' => 'boolean',
            'co_parrainage_pourcentage' => 'float',
            'max_agents_vente' => 'integer',
            'a_la_une' => 'boolean',
            'a_la_une_ordre' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $evenement) {
            if (empty($evenement->slug)) {
                $evenement->slug = self::genererSlugUnique($evenement->titre ?? '', $evenement->id);
            }
        });

        static::updating(function (self $evenement) {
            if ($evenement->isDirty('titre') || empty($evenement->slug)) {
                $evenement->slug = self::genererSlugUnique($evenement->titre ?? '', $evenement->id);
            }
        });
    }

    // URL par slug (le nom de l'événement), les anciens liens par id restent acceptés
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $evenement = $this->newQuery()->where('slug', $value)->first();
        if ($evenement) {
            return $evenement;
        }

        if (ctype_digit((string) $value)) {
            return $this->newQuery()->whereKey($value)->first();
        }

        return null;
    }

    public static function genererSlugUnique(string $titre, ?int $ignoreId = null): string
    {
        $base = Str::slug($titre) ?: 'evenement';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    // Scope : événements mis à la une, triés par ordre
    public function scopeALaUne($query)
    {
        return $query->where('a_la_une', true)
            ->orderBy('a_la_une_ordre')
            ->orderBy('date_event');
    }

    public function scopeWithUpcomingDate(Builder $query): Builder
    {
        $now = now();

        return $query->where(function (Builder $upcoming) use ($now) {
            $upcoming->whereHas('dates', fn (Builder $dates) => $dates->where('date_debut', '>=', $now))
                ->orWhere(function (Builder $withoutDates) use ($now) {
                    $withoutDates->whereDoesntHave('dates')
                        ->where('date_event', '>=', $now);
                });
        });
    }

    public function getTextes(): array
    {
        return match ($this->type_evenement) {
            'formation' => [
                'type' => 'formation',
                'acheter' => "S'inscrire",
                'acheter_billet' => "S'inscrire à la formation",
                'cloturee' => 'Inscriptions clôturées',
                'billet' => 'Billet',
                'billet_pluriel' => 'Billets',
                'participer_gratuit' => "S'inscrire gratuitement",
                'places' => 'places disponibles',
                'presenter' => 'Présentez votre billet à l\'entrée de l\'événement',
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

    public function ventesPayeesHorsBilletsPhysiques(): HasMany
    {
        return $this->tickets()
            ->where('statut_paiement', 'payé')
            ->whereNull('lot_physique_id')
            ->where(function (Builder $query) {
                $query->whereNull('annule')->orWhere('annule', false);
            })
            ->where(function (Builder $query) {
                $query->whereNull('source')->orWhere('source', '!=', 'physique');
            });
    }

    public function suppressionOrganisateurBloquee(): bool
    {
        if ($this->gratuit) {
            return false;
        }

        $aVentesEligibles = array_key_exists('has_successful_non_physical_sales', $this->attributes)
            ? (bool) $this->attributes['has_successful_non_physical_sales']
            : $this->ventesPayeesHorsBilletsPhysiques()->exists();

        if (! $aVentesEligibles) {
            return false;
        }

        $derniereDate = $this->relationLoaded('dates')
            ? $this->dates->max('date_debut')
            : $this->dates()->max('date_debut');
        $dateEvenement = $derniereDate ?? $this->date_event;

        return $dateEvenement !== null && \Illuminate\Support\Carbon::parse($dateEvenement)->isFuture();
    }

    public function affiliateEventLinks(): HasMany
    {
        return $this->hasMany(AffiliateEventLink::class, 'evenement_id');
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

    // Un événement est considéré multijours dès qu'il possède plus d'une
    // session. La capacité est alors raisonnée par jour.
    public function estMultiJour(): bool
    {
        if ($this->multiJourCache === null) {
            // Réutilise un withCount('dates') ou une relation déjà chargée pour
            // éviter une requête par événement dans les listes.
            $this->multiJourCache = match (true) {
                array_key_exists('dates_count', $this->attributes) => (int) $this->attributes['dates_count'] > 1,
                $this->relationLoaded('dates') => $this->dates->count() > 1,
                default => $this->dates()->count() > 1,
            };
        }

        return $this->multiJourCache;
    }

    public function capacitePourJour(EvenementDate $jour): int
    {
        return $jour->capaciteEffective();
    }

    // Jours couverts par un tarif pour le calcul des places : son jour s'il en
    // a un, sinon les jours pas encore terminés de l'événement (pack). Un jour
    // passé, même complet, ne doit pas bloquer la vente d'un pack.
    public function joursCouvertsParTarif(?Tarif $tarif): Collection
    {
        if ($tarif && $tarif->evenement_date_id) {
            $jour = $tarif->relationLoaded('jour') ? $tarif->jour : $tarif->jour()->first();

            return $jour ? collect([$jour]) : collect();
        }

        return $this->dates()->get()
            ->reject(fn (EvenementDate $jour) => $jour->estTermine())
            ->values();
    }

    // Premier jour payant : un jour est gratuit s'il propose au moins un tarif
    // à la journée et que tous ses tarifs actifs sont à 0 F. Un jour sans tarif
    // propre n'est accessible que par un pack, il est donc considéré payant.
    public function premierJourPayant(): ?EvenementDate
    {
        if ($this->gratuit) {
            return null;
        }

        $tarifsParJour = $this->tarifs()
            ->whereNotNull('evenement_date_id')
            ->where('statut', 'actif')
            ->get(['evenement_date_id', 'prix'])
            ->groupBy('evenement_date_id');

        return $this->dates()->get()->first(function (EvenementDate $jour) use ($tarifsParJour) {
            $tarifs = $tarifsParJour->get($jour->id);

            return $tarifs === null || $tarifs->contains(fn ($tarif) => (float) $tarif->prix > 0);
        });
    }

    // Fin de la vente des packs : minuit du premier jour payant (repli sur le
    // dernier jour si tous les jours sont gratuits). `null` = pas de limite
    // propre aux packs (événement mono-jour ou gratuit).
    public function finVentePacks(): ?\Carbon\CarbonInterface
    {
        if ($this->gratuit || ! $this->estMultiJour()) {
            return null;
        }

        // Sans aucun billet à la journée (événements créés avant les tarifs par
        // jour), le pack est le seul moyen d'entrer : il reste en vente jusqu'au
        // bout, comme avant.
        if (! $this->tarifs()->whereNotNull('evenement_date_id')->where('statut', 'actif')->exists()) {
            return null;
        }

        $jour = $this->premierJourPayant() ?? $this->derniereDate();

        return $jour?->finVente();
    }

    // Un pack VIP se reconnaît à son nom (« Pack VIP », « Pass VIP »…).
    public static function estPackVip(Tarif $tarif): bool
    {
        return $tarif->evenement_date_id === null && Str::contains(Str::lower($tarif->nom), 'vip');
    }

    // Économie d'un pack simple : somme des tarifs à la journée − prix du pack.
    // `null` si le pack est VIP, si un jour n'a pas de tarif à la journée, ou si
    // le pack ne fait rien économiser.
    public function economiePack(Tarif $pack): ?int
    {
        if ($pack->evenement_date_id !== null || self::estPackVip($pack) || ! $this->estMultiJour()) {
            return null;
        }

        $prixParJour = $this->tarifs()
            ->whereNotNull('evenement_date_id')
            ->where('statut', 'actif')
            ->get(['evenement_date_id', 'prix'])
            ->groupBy('evenement_date_id')
            ->map(fn ($tarifs) => (float) $tarifs->min('prix'));

        $jours = $this->dates()->pluck('id');
        if ($jours->isEmpty() || $jours->contains(fn ($id) => ! $prixParJour->has($id))) {
            return null;
        }

        $economie = (int) round($jours->sum(fn ($id) => $prixParJour[$id]) - (float) $pack->prix);

        return $economie > 0 ? $economie : null;
    }

    // Places déjà prises pour un jour : billets rattachés à ce jour + billets
    // valables toutes les dates (packs et billets historiques).
    public function occupationJour(EvenementDate $jour): int
    {
        if (array_key_exists($jour->id, $this->occupationCache)) {
            return $this->occupationCache[$jour->id];
        }

        return $this->occupationCache[$jour->id] = (int) Ticket::query()
            ->where('evenement_id', $this->id)
            ->where(fn (Builder $q) => $q->where('evenement_date_id', $jour->id)->orWhereNull('evenement_date_id'))
            ->actifsPourCapacite()
            ->sum('quantite');
    }

    // Places promises par les tarifs sur un jour : stocks des billets du jour
    // + stocks des packs (qui occupent chaque jour). Les stocks illimités
    // (null) ne comptent pas.
    public function stocksTarifsPourJour(EvenementDate $jour): int
    {
        return (int) $this->tarifs()
            ->where(fn (Builder $q) => $q->whereNull('evenement_date_id')->orWhere('evenement_date_id', $jour->id))
            ->sum('quantite_disponible');
    }

    // Places restantes pour un tarif : minimum sur les jours qu'il couvre.
    // Un événement mono-jour conserve le comportement historique basé sur
    // quota_vendu.
    public function placesRestantesPourTarif(?Tarif $tarif): int
    {
        if (! $this->estMultiJour()) {
            return max(0, (int) $this->capacite - (int) $this->quota_vendu);
        }

        $jours = $this->joursCouvertsParTarif($tarif);

        if ($jours->isEmpty()) {
            return max(0, (int) $this->capacite - (int) $this->quota_vendu);
        }

        return (int) $jours
            ->map(fn (EvenementDate $jour) => max(0, $this->capacitePourJour($jour) - $this->occupationJour($jour)))
            ->min();
    }

    // Total des places encore disponibles sur les jours pas encore terminés.
    // Utilisé pour l'affichage (organisateur, public, agent) : une vente sur un
    // jour non limitant doit tout de même faire baisser ce compteur global,
    // alors que placesRestantesPourTarif() conserve la limite par jour qui
    // plafonne une commande (notamment pour un pack).
    public function placesRestantesTotales(): int
    {
        if (! $this->estMultiJour()) {
            return max(0, (int) $this->capacite - (int) $this->quota_vendu);
        }

        return (int) $this->placesParJour()
            ->reject(fn (array $ligne) => $ligne['termine'])
            ->sum('restantes');
    }

    // Détail de la jauge de chaque jour, pour l'affichage « total + par jour ».
    // @return Collection<int, array{jour: EvenementDate, capacite: int, occupees: int, restantes: int, termine: bool}>
    public function placesParJour(): Collection
    {
        return $this->dates()->get()->map(function (EvenementDate $jour) {
            $capacite = $this->capacitePourJour($jour);
            $occupees = $this->occupationJour($jour);

            return [
                'jour' => $jour,
                'capacite' => $capacite,
                'occupees' => $occupees,
                'restantes' => max(0, $capacite - $occupees),
                'termine' => $jour->estTermine(),
            ];
        });
    }

    // Plus aucune place vendable. Mono-jour : capacité globale. Multijours
    // gratuit : le billet unique couvre tous les jours restants. Multijours
    // payant : aucun tarif encore en vente n'a de place.
    public function estComplet(): bool
    {
        if (! $this->estMultiJour()) {
            return (int) $this->capacite <= 0 || (int) $this->quota_vendu >= (int) $this->capacite;
        }

        if ($this->gratuit) {
            return $this->placesRestantesPourTarif(null) <= 0;
        }

        $tarifs = $this->tarifs()
            ->where('statut', 'actif')
            ->with('jour')
            ->get()
            ->filter(fn (Tarif $tarif) => $tarif->estEncoreVendable());

        if ($tarifs->isEmpty()) {
            return $this->placesRestantesTotales() <= 0;
        }

        return $tarifs->every(fn (Tarif $tarif) => $this->placesRestantesPourTarif($tarif) <= 0);
    }

    // Taux de remplissage en %. Multijours : places occupées sur l'ensemble
    // des jours rapportées à la somme des jauges (quota_vendu n'y est qu'une
    // statistique de billets vendus).
    public function tauxRemplissage(): float
    {
        if (! $this->estMultiJour()) {
            return (int) $this->capacite > 0
                ? ((int) $this->quota_vendu / (int) $this->capacite) * 100
                : 0.0;
        }

        $jours = $this->placesParJour();
        $capaciteTotale = (int) $jours->sum('capacite');

        return $capaciteTotale > 0
            ? min(100, ((int) $jours->sum('occupees') / $capaciteTotale) * 100)
            : 0.0;
    }

    // Jours qui portent déjà des tarifs ou des billets : leur date ne peut
    // plus être déplacée ni supprimée sans casser les ventes en cours.
    public function joursAvecTarifsOuBillets(): Collection
    {
        $ids = $this->tarifs()
            ->whereNotNull('evenement_date_id')
            ->pluck('evenement_date_id')
            ->merge(
                Ticket::query()
                    ->where('evenement_id', $this->id)
                    ->whereNotNull('evenement_date_id')
                    ->pluck('evenement_date_id')
            )
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return EvenementDate::whereIn('id', $ids)
            ->pluck('date_debut')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();
    }

    public function resetCachesCapacite(): void
    {
        $this->multiJourCache = null;
        $this->occupationCache = [];
    }

    // Dernière session de l'événement (pour expiration des ventes et scans)
    public function derniereDate(): ?EvenementDate
    {
        return $this->dates()->reorder('date_debut', 'desc')->first();
    }

    // Dates de début effectives (sessions réelles, repli sur date_event).
    // Utilisées pour l'affichage acheteur : email, confirmation, page billet.
    public function datesDebut(): Collection
    {
        $debuts = $this->relationLoaded('dates')
            ? $this->dates->pluck('date_debut')
            : $this->dates()->pluck('date_debut');

        $debuts = $debuts
            ->map(fn ($date) => Carbon::parse($date))
            ->sort()
            ->values();

        if ($debuts->isEmpty() && $this->date_event) {
            return collect([$this->date_event]);
        }

        return $debuts;
    }

    // L'événement est-il terminé pour le scan ? On se base sur la fin de la
    // dernière fenêtre de scan (début + tolérance), et non sur l'heure de début :
    // une session qui commence la veille et finit après minuit reste scannable.
    public function scanTermine(): bool
    {
        $derniere = $this->derniereDate();

        if ($derniere) {
            return $derniere->finFenetreScan()->isPast();
        }

        return $this->date_event !== null
            && $this->date_event->copy()->addHours(self::DUREE_SCAN_HEURES)->isPast();
    }

    // Date de référence pour la clôture des ventes : dernière session réelle,
    // avec repli sur date_event pour les événements sans sessions détaillées.
    public function derniereDateVente(): ?\Illuminate\Support\Carbon
    {
        return $this->derniereDate()?->date_debut ?? $this->date_event;
    }

    public function joursAvecScansReussis(): Collection
    {
        $joursEvenement = $this->dates()
            ->pluck('date_debut')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->push($this->date_event->toDateString())
            ->unique()
            ->values();

        if ($joursEvenement->isEmpty()) {
            return collect();
        }

        return Log::query()
            ->where('type_operation', 'scan')
            ->where('details->resultat', 'valide')
            ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'), $joursEvenement)
            ->whereHas('ticket', fn (Builder $query) => $query->where('evenement_id', $this->id))
            ->selectRaw('DATE(created_at) as jour_scan')
            ->distinct()
            ->pluck('jour_scan')
            ->map(fn ($jour) => (string) $jour);
    }

    public function aDesScansReussis(): bool
    {
        return Log::query()
            ->where('type_operation', 'scan')
            ->where('details->resultat', 'valide')
            ->whereHas('ticket', fn (Builder $query) => $query->where('evenement_id', $this->id))
            ->exists();
    }

    public function modificationGlobaleVerrouillee(): bool
    {
        if (! $this->aDesScansReussis()) {
            return false;
        }

        $derniereDate = $this->derniereDate();
        $finDerniereDate = $derniereDate
            ? $derniereDate->finFenetreScan()
            : $this->date_event->copy()->addHours(self::DUREE_SCAN_HEURES);

        return $finDerniereDate->copy()->addHours(12)->lte(now());
    }

    // Motif d'interdiction d'accès à l'espace de vente (statut ou événement passé).
    public function raisonBlocageAcces(): ?string
    {
        return match ($this->statut) {
            'annulé' => 'event_cancelled',
            'suspendu' => 'event_suspended',
            'brouillon' => 'event_draft',
            default => $this->evenementPasse() ? 'event_ended' : null,
        };
    }

    // Motif d'interdiction de vente : accès bloqué, ventes fermées ou capacité nulle.
    public function raisonBlocageVente(): ?string
    {
        $acces = $this->raisonBlocageAcces();

        if ($acces !== null) {
            return $acces;
        }

        if ($this->ventes_fermees) {
            return 'sales_closed';
        }

        if ($this->estComplet()) {
            return 'event_full';
        }

        return null;
    }

    public function ventesOuvertes(): bool
    {
        return $this->raisonBlocageVente() === null;
    }

    public function evenementPasse(): bool
    {
        $derniere = $this->derniereDateVente();

        return $derniere !== null && $derniere->isPast();
    }

    // Session (jour) à utiliser pour le scan à l'instant présent.
    // 1) une session dont la fenêtre contient now() (y compris celles qui
    //    commencent la veille et se terminent après minuit) ;
    // 2) sinon la prochaine session du jour (message « pas encore commencée ») ;
    // 3) sinon la dernière session commencée aujourd'hui (message « terminée »).
    public function jourScanActuel(): ?EvenementDate
    {
        $maintenant = now();

        $enCours = $this->dates()
            ->where('date_debut', '<=', $maintenant)
            ->where('date_debut', '>=', $maintenant->copy()->subHours(self::DUREE_SCAN_HEURES))
            ->orderBy('date_debut')
            ->first();

        if ($enCours) {
            return $enCours;
        }

        $aVenir = $this->dates()
            ->where('date_debut', '>', $maintenant)
            ->where('date_debut', '<=', $maintenant->copy()->endOfDay())
            ->orderBy('date_debut')
            ->first();

        if ($aVenir) {
            return $aVenir;
        }

        return $this->dates()
            ->where('date_debut', '<=', $maintenant)
            ->where('date_debut', '>=', $maintenant->copy()->startOfDay())
            ->orderByDesc('date_debut')
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
