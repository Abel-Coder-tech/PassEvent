<?php

namespace App\Support;

use Laravel\Sanctum\PersonalAccessToken;

/**
 * Marqueur de session placé dans les abilities des jetons Sanctum.
 * Il permet de rattacher un jeton (de connexion ou de scan) à la session
 * unique ouverte pour le compte, et de la fermer à la déconnexion.
 */
final class SessionMarker
{
    private const PREFIXE = 'session_cle:';

    public static function nouvelleCle(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function ability(string $cle): string
    {
        return self::PREFIXE.$cle;
    }

    public static function cleDepuis(?PersonalAccessToken $jeton): ?string
    {
        foreach ($jeton?->abilities ?? [] as $ability) {
            if (is_string($ability) && str_starts_with($ability, self::PREFIXE)) {
                return substr($ability, strlen(self::PREFIXE));
            }
        }

        return null;
    }

    public static function correspond(?string $cle, ?string $reference): bool
    {
        return $cle !== null && $reference !== null && hash_equals($reference, $cle);
    }
}
