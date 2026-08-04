<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class PerPage
{
    public const ALLOWED = [10, 20, 50];
    public const DEFAULT = 10;

    // Résout le nombre d'éléments par page depuis la requête (10 par défaut)
    public static function resolve(): int
    {
        $value = (int) Request::input('per_page', self::DEFAULT);

        return in_array($value, self::ALLOWED, true) ? $value : self::DEFAULT;
    }
}
