<?php

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;

/**
 * Session unique par compte : une clé aléatoire est enregistrée à la
 * connexion et effacée à la déconnexion. Tant que la clé est active, toute
 * autre connexion avec le même identifiant est refusée.
 */
trait HasSessionUnique
{
    public function sessionEstActive(): bool
    {
        if (empty($this->session_cle)) {
            return false;
        }

        return empty($this->session_expire_le)
            || Carbon::parse($this->session_expire_le)->isFuture();
    }

    public function ouvrirSession(string $cle): void
    {
        $expireLe = now()->addMinutes((int) config('sanctum.expiration', 20160));

        $this->forceFill([
            'session_cle' => $cle,
            'session_expire_le' => $expireLe,
        ])->save();
    }

    /**
     * Réserve la session en une seule requête : deux connexions simultanées
     * ne peuvent pas toutes deux réussir. Retourne false si une session est
     * déjà active pour ce compte.
     */
    public function reserverSession(string $cle): bool
    {
        $expireLe = now()->addMinutes((int) config('sanctum.expiration', 20160));

        $reserve = $this->newQuery()
            ->whereKey($this->getKey())
            ->where(function ($query) {
                $query->whereNull('session_cle')
                    ->orWhereNull('session_expire_le')
                    ->orWhere('session_expire_le', '<=', now());
            })
            ->update([
                'session_cle' => $cle,
                'session_expire_le' => $expireLe,
            ]);

        if ($reserve) {
            $this->forceFill([
                'session_cle' => $cle,
                'session_expire_le' => $expireLe,
            ]);
        }

        return (bool) $reserve;
    }

    // Sans clé fournie, la session est fermée quoi qu'il arrive (logout global,
    // changement de mot de passe). Avec une clé, seul le bon appareil peut la fermer.
    public function fermerSession(?string $cle = null): void
    {
        if ($cle !== null && ! hash_equals((string) $this->session_cle, $cle)) {
            return;
        }

        $this->forceFill([
            'session_cle' => null,
            'session_expire_le' => null,
        ])->save();
    }
}
