<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Ticket;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'canal',
        'statut',
        'message',
        'erreur',
        'tentative',
        'date_envoi',
    ];

    protected function casts(): array
    {
        return [
            'date_envoi' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}