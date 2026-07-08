<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DemandeRemboursement extends Model
{
    protected $table = 'demandes_remboursement';

    protected $fillable = [
        'organisateur_id',
        'evenement_id',
        'type',
        'montant_total',
        'motif',
        'notes_admin',
        'statut',
        'traitee_par',
        'traitee_le',
    ];

    protected $casts = [
        'montant_total' => 'decimal:2',
        'traitee_le' => 'datetime',
    ];

    public function organisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organisateur_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'demande_remboursement_ticket', 'demande_remboursement_id', 'ticket_id');
    }

    public function traiteePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traitee_par');
    }
}
