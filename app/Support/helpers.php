<?php

use Illuminate\Support\Facades\File;

/**
 * Retourne une URL d'asset avec un cache-busting basé sur la date de
 * modification du fichier. Permet de forcer le rafraîchissement des
 * images (logo, hero...) malgré le cache navigateur d'un mois défini
 * dans public/.htaccess, sans changer le nom du fichier.
 */
function asset_v(string $path): string
{
    $full = public_path($path);
    $version = File::exists($full) ? filemtime($full) : time();

    return asset($path . '?v=' . $version);
}
