<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registre d'idempotence des ventes créées par un agent.
 *
 * Une ligne par (agent, clé d'idempotence) garantit qu'une même demande
 * rejouée (double tap, retry réseau, webhook) ne crée jamais deux ventes,
 * même si deux requêtes arrivent en parallèle.
 */
class VenteIdempotence extends Model
{
    protected $table = 'vente_idempotences';

    protected $fillable = [
        'agent_vente_id',
        'cle',
        'empreinte',
        'transaction_id',
    ];
}
