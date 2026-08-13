<?php

namespace App\Services;

// Mappe le payment_method renvoyé par FedaPay vers nos deux niveaux :
// - le moyen de paiement (mobile_money / bancaire / especes)
// - l'opérateur mobile (mtn / moov / celtiis / … ou null si indéterminé)
class PaiementMapper
{
    public const MOYENS_LABELS = [
        'mobile_money' => 'Mobile Money',
        'bancaire' => 'Carte bancaire',
        'especes' => 'Espèces',
    ];

    public const OPERATEURS_LABELS = [
        'mtn' => 'MTN MoMo',
        'moov' => 'Moov Money',
        'celtiis' => 'Celtiis Cash',
        'orange' => 'Orange Money',
        'togocel' => 'Togocel',
        'wave' => 'Wave',
        'airtel' => 'Airtel Money',
        'free' => 'Free Money',
    ];

    // Détermine le moyen de paiement à partir de la valeur brute
    public static function moyenPaiement(?string $raw): string
    {
        $lower = strtolower(trim((string) $raw));

        if ($lower === '') {
            return 'mobile_money';
        }

        // Espèces
        if (in_array($lower, ['cash', 'especes', 'espèces'], true)) {
            return 'especes';
        }

        // Cartes bancaires
        foreach (['bank_card', 'bankcard', 'bancaire', 'carte', 'card', 'visa', 'mastercard', 'credit', 'debit', 'cb'] as $mot) {
            if (str_contains($lower, $mot)) {
                return 'bancaire';
            }
        }

        // Tout le reste (opérateurs mobile money inclus) est considéré mobile money
        return 'mobile_money';
    }

    // Détermine l'opérateur mobile à partir de la valeur brute (null si indéterminé)
    public static function operateur(?string $raw): ?string
    {
        $lower = strtolower(trim((string) $raw));

        if ($lower === '') {
            return null;
        }

        // "mtn_moov" est un moyen générique (les deux opérateurs) : non attribuable
        if (str_contains($lower, 'mtn_moov')) {
            return null;
        }

        // "sbin" est le mode FedaPay pour Celtiis Bénin
        if ($lower === 'sbin' || str_contains($lower, 'celtiis') || str_contains($lower, 'celti')) {
            return 'celtiis';
        }

        if (str_contains($lower, 'mtn')) {
            return 'mtn';
        }

        if (str_contains($lower, 'togocel') || str_contains($lower, 'togo')) {
            return 'togocel';
        }

        if (str_contains($lower, 'moov')) {
            return 'moov';
        }

        if (str_contains($lower, 'orange')) {
            return 'orange';
        }

        if (str_contains($lower, 'airtel')) {
            return 'airtel';
        }

        if (str_contains($lower, 'wave')) {
            return 'wave';
        }

        if (str_contains($lower, 'free')) {
            return 'free';
        }

        return null;
    }

    public static function moyenLabel(string $moyen): string
    {
        return self::MOYENS_LABELS[$moyen] ?? ucfirst($moyen);
    }

    public static function operateurLabel(?string $operateur): string
    {
        if (! $operateur) {
            return 'Indéterminé';
        }

        return self::OPERATEURS_LABELS[$operateur] ?? ucfirst($operateur);
    }
}
