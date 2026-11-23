<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * IP bloquée par le superadmin (temporairement ou définitivement).
 */
class IpBloquee extends Model
{
    protected $table = 'ips_bloquees';

    protected $fillable = ['ip', 'motif', 'bloque_par', 'expire_le'];

    protected $casts = ['expire_le' => 'datetime'];

    private const CACHE = 'securite:ips-bloquees';

    protected static function booted(): void
    {
        // La liste est mise en cache : on la vide à chaque changement
        static::saved(fn () => Cache::forget(self::CACHE));
        static::deleted(fn () => Cache::forget(self::CACHE));
    }

    public static function estBloquee(string $ip): bool
    {
        try {
            $liste = Cache::remember(self::CACHE, 300, fn () => static::query()
                ->where(fn ($q) => $q->whereNull('expire_le')->orWhere('expire_le', '>', now()))
                ->get(['ip', 'expire_le'])
                ->mapWithKeys(fn ($b) => [$b->ip => $b->expire_le?->getTimestamp()])
                ->all());
        } catch (Throwable) {
            return false; // table absente (avant migration) : on ne bloque personne
        }

        if (! array_key_exists($ip, $liste)) {
            return false;
        }

        return $liste[$ip] === null || $liste[$ip] > time();
    }

    public function estActive(): bool
    {
        return $this->expire_le === null || $this->expire_le->isFuture();
    }
}
