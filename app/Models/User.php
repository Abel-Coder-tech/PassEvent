<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Withdrawal;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
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
        ];
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function evenements(): HasMany
    {
        return $this->hasMany(Evenement::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function demandesRemboursement(): HasMany
    {
        return $this->hasMany(DemandeRemboursement::class, 'organisateur_id');
    }

    public function getSoldeAttribute(): float
    {
        $evenementsIds = $this->evenements()->pluck('id');
        $vendu = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé')->sum('montant');
        $rembourse = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'remboursé')->sum('montant');
        $enCours = DemandeRemboursement::where('organisateur_id', $this->id)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->sum('montant_total');
        // Commission de 10% sur les ventes
        $commission = round($vendu * 10 / 100, 2);
        // Retraits en attente, en cours ou payés
        $retires = Withdrawal::where('user_id', $this->id)
            ->whereIn('status', ['en_attente', 'en_cours', 'payé'])
            ->sum('montant');
        return max(0, $vendu - $rembourse - $enCours - $commission - $retires);
    }
}
