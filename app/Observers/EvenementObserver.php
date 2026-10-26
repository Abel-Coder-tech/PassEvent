<?php

namespace App\Observers;

use App\Models\Evenement;

/**
 * Observateur des événements.
 * Seul le co-parrainage est conservé ; aucune action d'affiliation automatique n'est nécessaire.
 */
class EvenementObserver
{
    public function saved(Evenement $evenement): void
    {
        // Plus d'action : seul le co-parrainage est conservé.
    }
}
