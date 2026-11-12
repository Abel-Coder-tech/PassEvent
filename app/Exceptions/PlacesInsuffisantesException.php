<?php

namespace App\Exceptions;

use App\Models\Tarif;
use RuntimeException;

/**
 * Levée pendant la réservation d'un panier lorsqu'une ligne n'a plus assez
 * de places. Lever une exception (plutôt que retourner) annule toute la
 * transaction : aucun billet des lignes précédentes n'est conservé.
 */
class PlacesInsuffisantesException extends RuntimeException
{
    public function __construct(public readonly Tarif $tarif, public readonly int $placesRestantes)
    {
        parent::__construct("Places insuffisantes pour le tarif {$tarif->id}");
    }
}
