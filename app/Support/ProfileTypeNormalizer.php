<?php

namespace App\Support;

/**
 * Ramène les types de profils historiques vers les trois types actuellement
 * utilisés par le parcours organisateur : universitaire, particulier, organisation.
 *
 * Les anciennes valeurs « professionnel », « entreprise », « association » et
 * « club » désignaient toutes une organisation. Le détail est conservé quand il
 * est connu, les clubs étant rangés parmi les associations/ONG.
 */
final class ProfileTypeNormalizer
{
    public const TYPES = ['universitaire', 'particulier', 'organisation'];

    /**
     * @return array{0: string|null, 1: string|null} Le type et son détail normalisés
     */
    public static function normaliser(?string $type, ?string $typeDetail = null): array
    {
        if ($type === null || $type === '') {
            return [null, $typeDetail];
        }

        $valeur = mb_strtolower(trim($type));

        if (in_array($valeur, self::TYPES, true)) {
            return $valeur === 'organisation'
                ? [$valeur, self::normaliserDetail($typeDetail)]
                : [$valeur, null];
        }

        if (in_array($valeur, ['universitaire', 'universite', 'université', 'etudiant', 'étudiant', 'academique', 'académique'], true)) {
            return ['universitaire', null];
        }

        if (in_array($valeur, ['particulier', 'personne physique', 'physique', 'individual', 'personnel'], true)) {
            return ['particulier', null];
        }

        // professionnel / entreprise / association / club et toute valeur inattendue
        // correspondant à un ancien profil « professionnel ».
        return ['organisation', self::normaliserDetail($typeDetail ?: $valeur)];
    }

    private static function normaliserDetail(?string $detail): ?string
    {
        if ($detail === null || $detail === '') {
            return null;
        }

        $valeur = mb_strtolower(trim($detail));

        return match ($valeur) {
            'entreprise', 'entreprises', 'societe', 'société' => 'entreprise',
            'association', 'associations', 'club', 'club sportif', 'ong' => 'association',
            default => null,
        };
    }
}
