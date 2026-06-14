<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'organisation',
        'telephone',
        'email',
        'description',
        'avatar',
        'mot_de_passe',
        'role',
        'notif_email_evenement',
        'notif_email_ticket',
        'notif_email_paiement',
        'notif_scan',
        'kkiapay_public_key',
        'kkiapay_secret_key',
        'kkiapay_api_key',
        'kkiapay_active',
        'code_acces_scan',
        'pseudo',
        'statut',
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
            'kkiapay_active' => 'boolean',
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
}
