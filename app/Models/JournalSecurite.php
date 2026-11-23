<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Événement de sécurité : connexion échouée, lien suspect, tentative d'attaque, robot…
 * Les champs sensibles (mots de passe, jetons) sont masqués avant enregistrement.
 */
class JournalSecurite extends Model
{
    protected $table = 'journal_securite';

    public const UPDATED_AT = null;

    protected $fillable = [
        'type', 'regle', 'ip', 'methode', 'url', 'espace', 'identifiant', 'donnees', 'user_agent',
    ];

    protected $casts = [
        'donnees' => 'array',
        'created_at' => 'datetime',
    ];

    public const TYPES = [
        'connexion_echouee' => ['label' => 'Connexion échouée', 'color' => 'var(--sa-warning)'],
        'connexion_bloquee' => ['label' => 'Connexion bloquée (trop d\'essais)', 'color' => 'var(--sa-danger)'],
        'lien_suspect' => ['label' => 'Lien suspect', 'color' => 'var(--sa-warning)'],
        'attaque' => ['label' => 'Tentative d\'attaque', 'color' => 'var(--sa-danger)'],
        'robot' => ['label' => 'Robot d\'attaque (bloqué)', 'color' => 'var(--sa-danger)'],
        'ip_bloquee' => ['label' => 'IP bloquée refoulée', 'color' => 'var(--sa-text-muted)'],
    ];
}
