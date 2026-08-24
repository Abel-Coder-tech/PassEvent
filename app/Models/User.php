<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES_EQUIPE = [
        'validateur' => [
            'libelle' => 'Validateur',
            'description' => 'Validation des comptes organisateurs et des retraits de fonds',
            'icone' => 'bi-person-check-fill',
        ],
        'support_client' => [
            'libelle' => 'Support client',
            'description' => 'Sujets commerciaux : commissions, contrats, partenariats',
            'icone' => 'bi-headset',
        ],
        'assistant_technique' => [
            'libelle' => 'Assistant technique',
            'description' => 'Problèmes de connexion, inscriptions, émission de tickets, bugs',
            'icone' => 'bi-tools',
        ],
    ];

    protected $fillable = [
        'nom',
        'prenom',
        'organisation',
        'telephone',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'twitter_url',
        'youtube_url',
        'linkedin_url',
        'website_url',
        'email',
        'description',
        'avatar',
        'mot_de_passe',
        'role',
        'permissions',
        'must_change_password',
        'notif_email_evenement',
        'notif_email_ticket',
        'notif_email_paiement',
        'notif_scan',
        'code_acces_scan',
        'pseudo',
        'statut',
        'type',
        'type_detail',
        'document_justificatif',
        'signature',
        'ventes_especes',
        'commission_pourcentage',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'notif_email_evenement' => 'boolean',
            'notif_email_ticket' => 'boolean',
            'notif_email_paiement' => 'boolean',
            'notif_scan' => 'boolean',
            'commission_pourcentage' => 'float',
            'permissions' => 'array',
            'must_change_password' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function estEquipe(): bool
    {
        return $this->role === 'equipe';
    }

    public function estSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function aRole(string ...$slugs): bool
    {
        if ($this->estSuperAdmin()) {
            return true;
        }

        foreach ($slugs as $slug) {
            if (in_array($slug, (array) ($this->permissions ?? []))) {
                return true;
            }
        }

        return false;
    }

    public function libelleRolesEquipe(): string
    {
        $libelles = [];
        foreach ((array) ($this->permissions ?? []) as $slug) {
            $libelles[] = self::ROLES_EQUIPE[$slug]['libelle'] ?? $slug;
        }

        return implode(', ', $libelles) ?: 'Aucun rôle';
    }

    public function evenements(): HasMany
    {
        return $this->hasMany(Evenement::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function lotsPhysiques(): HasMany
    {
        return $this->hasMany(LotPhysique::class, 'user_id');
    }

    public function demandesRemboursement(): HasMany
    {
        return $this->hasMany(DemandeRemboursement::class, 'organisateur_id');
    }

    public function getSoldeAttribute(): float
    {
        $evenementsIds = $this->evenements()->pluck('id');
        $stats = $this->statsFinancieres();
        $enCours = DemandeRemboursement::where('organisateur_id', $this->id)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->sum('montant_total');
        // Commission effective par événement
        $commission = $stats['commissionTotale'];
        // Retraits en attente, en cours ou payés
        $retires = Withdrawal::where('user_id', $this->id)
            ->whereIn('status', ['en_attente', 'en_cours', 'payé'])
            ->sum('montant');

        return max(0, $stats['totalTickets'] - $enCours - $commission - $retires);
    }

    // Commission par défaut de l'organisateur (null = 10 % global)
    public function commissionPourcentage(): float
    {
        return (float) ($this->commission_pourcentage ?? 10);
    }

    // Statistiques financières de l'organisateur avec commissions effectives par événement
    public function statsFinancieres(): array
    {
        $evenementsIds = $this->evenements()->pluck('id');
        $evenements = Evenement::whereIn('id', $evenementsIds)->with('user')->get()->keyBy('id');
        $tickets = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNull('lot_physique_id')
            ->get(['evenement_id', 'montant', 'methode_paiement']);

        // Tickets physiques (lots) : comptés à part, commission attendue séparée (tickets annulés exclus)
        $physiques = Ticket::query()->whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNotNull('lot_physique_id')
            ->where('annule', false)
            ->get(['evenement_id', 'montant', 'methode_paiement']);

        $totalTickets = (float) $tickets->sum('montant');
        $mobileRecettes = (float) $tickets->whereNotIn('methode_paiement', ['cash', 'especes'])->sum('montant');
        $cashRecettes = $totalTickets - $mobileRecettes;

        $commissionTotale = 0.0;
        $commissionMobile = 0.0;
        $commissionCash = 0.0;

        foreach ($evenements as $evenement) {
            $evTickets = $tickets->where('evenement_id', $evenement->id);
            $evMobile = (float) $evTickets->whereNotIn('methode_paiement', ['cash', 'especes'])->sum('montant');
            $evCash = (float) $evTickets->whereIn('methode_paiement', ['cash', 'especes'])->sum('montant');
            $taux = $evenement->commissionEffective();
            $commissionTotale += ($evMobile + $evCash) * $taux / 100;
            $commissionMobile += $evMobile * $taux / 100;
            $commissionCash += $evCash * $taux / 100;
        }

        // Commission attendue sur les ventes physiques (recouvrée à part, hors balance)
        $physiqueRecettes = (float) $physiques->sum('montant');
        $commissionPhysique = 0.0;
        foreach ($evenements as $evenement) {
            $evPhysique = (float) $physiques->where('evenement_id', $evenement->id)->sum('montant');
            $commissionPhysique += $evPhysique * $evenement->commissionEffective() / 100;
        }

        return [
            'totalTickets' => round($totalTickets, 2),
            'mobileRecettes' => round($mobileRecettes, 2),
            'cashRecettes' => round($cashRecettes, 2),
            'commissionTotale' => round($commissionTotale, 2),
            'commissionMobile' => round($commissionMobile, 2),
            'commissionCash' => round($commissionCash, 2),
            'physiqueRecettes' => round($physiqueRecettes, 2),
            'commissionPhysique' => round($commissionPhysique, 2),
        ];
    }
}
