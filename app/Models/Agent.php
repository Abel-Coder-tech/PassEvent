<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    protected $fillable = [
        'nom',
        'email',
        'password',
        'evenement_id',
        'code_acces',
        'actif',
        'tentatives_code',
        'bloque_jusqua',
        'dernier_acces',
    ];

    protected $hidden = [
        'password',
        'code_acces',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'tentatives_code' => 'integer',
            'bloque_jusqua' => 'datetime',
            'dernier_acces' => 'datetime',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'agent_id');
    }

    // Vérifie si l'agent peut être réaffecté à un autre événement
    public function peutEtreReaffecte(): bool
    {
        if (! $this->evenement) {
            return true;
        }

        $ev = $this->evenement;

        // Événement passé (date dépassée)
        if ($ev->date_event && $ev->date_event->isPast()) {
            return true;
        }

        // Événement suspendu/annulé ou masqué
        if (in_array($ev->statut, ['annulé', 'brouillon'], true)) {
            return true;
        }

        // Agent désactivé (libre)
        if (! $this->actif) {
            return true;
        }

        return false;
    }

    // Récupère tous les logs de scan de cet agent (global, tous événements confondus)
    public function logsGlobaux()
    {
        return Log::where('agent_id', $this->id)->where('type_operation', 'scan')
            ->with(['ticket.evenement'])->latest('created_at');
    }

    // Logs de scan rattachés à un événement précis (via le ticket scanné)
    public function logsEvenement(int $evenementId)
    {
        return Log::where('agent_id', $this->id)->where('type_operation', 'scan')
            ->with('ticket')
            ->whereHas('ticket', fn ($q) => $q->where('evenement_id', $evenementId));
    }

    // Stats globales de cet agent
    public function statsGlobales(): array
    {
        $logsBase = Log::where('agent_id', $this->id)->where('type_operation', 'scan');

        return [
            'total_scans' => $logsBase->count(),
            'scans_ajd' => $logsBase->clone()->whereDate('created_at', today())->count(),
            'valides' => $logsBase->clone()->where('details->resultat', 'valide')->count(),
            'deja_utilises' => $logsBase->clone()->where('details->resultat', 'deja_utilise')->count(),
            'invalides' => $logsBase->clone()->where('details->resultat', '!=', 'valide')->count(),
            'dernier_acces' => $this->dernier_acces,
        ];
    }

    // Stats des scans effectués pour un événement précis
    public function statsParEvenement(int $evenementId): array
    {
        $logsBase = $this->logsEvenement($evenementId);

        return [
            'total_scans' => $logsBase->count(),
            'scans_ajd' => $logsBase->clone()->whereDate('created_at', today())->count(),
            'valides' => $logsBase->clone()->where('details->resultat', 'valide')->count(),
            'deja_utilises' => $logsBase->clone()->where('details->resultat', 'deja_utilise')->count(),
            'invalides' => $logsBase->clone()->where('details->resultat', '!=', 'valide')->count(),
            'dernier_acces' => $this->dernier_acces,
        ];
    }
}
