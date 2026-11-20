<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une version publiée de l'application mobile.
 *
 * Le fichier APK vit hors du webroot (storage/app/private/apps) : la colonne
 * `path` ne contient qu'un nom de fichier, jamais un chemin absolu.
 */
class AppRelease extends Model
{
    protected $fillable = [
        'platform',
        'package_name',
        'version_code',
        'version_name',
        'sha256',
        'size_bytes',
        'path',
        'notes',
        'mandatory',
        'published_at',
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'published_at' => 'datetime',
        'version_code' => 'integer',
        'size_bytes' => 'integer',
    ];

    /** Dernière version publiée pour une plateforme. */
    public static function latest(string $platform = 'android'): ?self
    {
        return static::query()
            ->where('platform', $platform)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('version_code')
            ->first();
    }
}
