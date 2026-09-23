<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ParametreSite extends Model
{
    protected $table = 'parametres_site';

    public $timestamps = false;

    protected $fillable = ['cle', 'valeur'];

    public static function ensemble(): array
    {
        return Cache::remember('parametres_site', now()->addMinutes(5), function () {
            if (! Schema::hasTable('parametres_site')) {
                return [];
            }

            return static::pluck('valeur', 'cle')->all();
        });
    }

    public static function valeur(string $cle, mixed $defaut = null): mixed
    {
        return static::ensemble()[$cle] ?? $defaut;
    }

    public static function definir(array $paires): void
    {
        foreach ($paires as $cle => $valeur) {
            static::updateOrCreate(['cle' => $cle], ['valeur' => is_null($valeur) ? null : (string) $valeur]);
        }

        Cache::forget('parametres_site');
    }

    public static function suivi(): array
    {
        $ensemble = static::ensemble();

        return [
            'gtm_id' => $ensemble['tracking_gtm_id'] ?? null,
            'meta_pixel_id' => $ensemble['tracking_meta_pixel_id'] ?? null,
            'tiktok_pixel_id' => $ensemble['tracking_tiktok_pixel_id'] ?? null,
        ];
    }
}