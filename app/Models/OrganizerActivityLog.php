<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizerActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'organizer_activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'status',
        'subject_type',
        'subject_id',
        'description',
        'details',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function labelAction(): string
    {
        return match ($this->action) {
            'evenement.create' => 'Création d\'événement',
            'agent_scan.create' => 'Création d\'agent de scan',
            'agent_vente.create' => 'Création d\'agent de vente',
            'agent_scan.toggle' => 'Activation/désactivation agent scan',
            'agent_vente.toggle' => 'Activation/désactivation agent vente',
            'agent_scan.delete' => 'Suppression agent scan',
            'agent_vente.delete' => 'Suppression agent vente',
            'agent_scan.reassign' => 'Réaffectation agent scan',
            'agent_vente.reassign' => 'Réaffectation agent vente',
            default => $this->action,
        };
    }
}