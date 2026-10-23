<?php

namespace App\Observers;

use App\Models\Evenement;
use App\Services\AffiliationService;

/**
 * Dès qu'un organisateur publie son premier événement, on le rattache au
 * parrain direct qui l'a apporté (mécanisme A).
 */
class EvenementObserver
{
    public function __construct(private AffiliationService $affiliation)
    {
    }

    public function saved(Evenement $evenement): void
    {
        if ($evenement->statut !== 'publié') {
            return;
        }

        if (! $evenement->wasRecentlyCreated && ! $evenement->wasChanged('statut')) {
            return;
        }

        $this->affiliation->lierPremierEvenement($evenement);
    }
}
