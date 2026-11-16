<?php

namespace App\Services;

/**
 * Règles du mode « Gérer l'organisateur » : ce que PaxEvent peut faire au nom
 * d'un organisateur. Tout ce qui n'est pas listé est refusé.
 */
class GestionOrganisateur
{
    public const AUTORISE = 'autorise';

    public const LECTURE = 'lecture';

    public const REFUSE = 'refuse';

    public const DUREE_INACTIVITE_SECONDES = 3600;

    // Toutes les méthodes : profil, événements, tarifs, promos, agents, codes de scan, contrat.
    private const AUTORISEES = [
        'dashboard', 'profil.*', 'admin.tarifs.*', 'admin.codes-promos.*',
        'admin.agents.*', 'admin.agents-vente.*', 'admin.scan-codes.*',
        'contrat-prestation', 'tickets.renvoyer', 'tickets.telecharger',
        'tickets.telecharger-tous', 'tickets.pdf', 'admin.gestion.*',
    ];

    // Consultation seulement (GET) : chiffres, argent et listes.
    private const LECTURE_SEULE = [
        'statistiques.index', 'tickets.index', 'tickets.show', 'tickets.export-csv',
        'admin.retraits.index', 'admin.remboursements.index', 'admin.affiliations.index',
        'admin.lots-physiques.index', 'admin.messages.index', 'admin.messages.show',
        'parametres.index',
    ];

    // Libellés lisibles du journal.
    private const LIBELLES = [
        'admin.evenements.store' => 'Création d\'un événement',
        'admin.evenements.update' => 'Modification d\'un événement',
        'admin.evenements.destroy' => 'Suppression d\'un événement',
        'admin.evenements.billetterie' => 'Ouverture ou fermeture de la billetterie',
        'admin.evenements.coparrainage' => 'Réglage du co-parrainage',
        'admin.evenements.scan-codes.store' => 'Création d\'un code d\'accès scan',
        'admin.tarifs.store' => 'Création d\'un tarif',
        'admin.tarifs.update' => 'Modification d\'un tarif',
        'admin.tarifs.destroy' => 'Suppression d\'un tarif',
        'admin.codes-promos.store' => 'Création d\'un code promo',
        'admin.codes-promos.destroy' => 'Suppression d\'un code promo',
        'admin.agents.store' => 'Création d\'un agent de scan',
        'admin.agents.update' => 'Modification d\'un agent de scan',
        'admin.agents.destroy' => 'Suppression d\'un agent de scan',
        'admin.agents.toggle-actif' => 'Activation ou désactivation d\'un agent de scan',
        'admin.agents-vente.store' => 'Création d\'un agent de vente',
        'admin.agents-vente.update' => 'Modification d\'un agent de vente',
        'admin.agents-vente.destroy' => 'Suppression d\'un agent de vente',
        'admin.agents-vente.toggle-actif' => 'Activation ou désactivation d\'un agent de vente',
        'profil.post-step2' => 'Saisie des informations et documents du profil',
        'profil.submit' => 'Finalisation du profil',
        'tickets.renvoyer' => 'Renvoi d\'un ticket par email',
    ];

    public static function decision(?string $route, string $methode): string
    {
        if ($route === null) {
            return self::REFUSE;
        }

        $lecture = in_array(strtoupper($methode), ['GET', 'HEAD'], true);

        if (str_starts_with($route, 'admin.evenements.') || self::correspond($route, self::AUTORISEES)) {
            return self::AUTORISE;
        }

        if ($lecture && self::correspond($route, self::LECTURE_SEULE)) {
            return self::LECTURE;
        }

        return self::REFUSE;
    }

    public static function libelle(?string $route, string $methode): string
    {
        return self::LIBELLES[$route ?? ''] ?? trim($methode.' '.($route ?? 'action'));
    }

    private static function correspond(string $route, array $motifs): bool
    {
        foreach ($motifs as $motif) {
            if ($motif === $route || (str_ends_with($motif, '.*') && str_starts_with($route, substr($motif, 0, -1)))) {
                return true;
            }
        }

        return false;
    }
}
