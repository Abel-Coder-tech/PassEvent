<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Commission d'affiliation (mecanisme A ou B) rattachee a un billet.
class AffiliateCommission extends Model
{
    public const TYPE_PARRAINAGE = 'parrainage_direct';
    public const TYPE_CO_PARRAINAGE = 'co_parrainage';

    protected $fillable = [
        'affiliate_id',
        'evenement_id',
        'ticket_id',
        'affiliate_event_link_id',
        'type',
        'base_montant',
        'pourcentage',
        'montant',
        'statut',
        'disponible_le',
        'paye_le',
        'affiliate_payout_id',
    ];

    protected function casts(): array
    {
        return [
            'base_montant' => 'decimal:2',
            'pourcentage' => 'decimal:2',
            'montant' => 'decimal:2',
            'disponible_le' => 'datetime',
            'paye_le' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function eventLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateEventLink::class, 'affiliate_event_link_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayout::class, 'affiliate_payout_id');
    }
}
