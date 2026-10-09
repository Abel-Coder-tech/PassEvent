<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AgentVente extends Authenticatable
{
    protected $table = 'agents_vente';

    protected $fillable = [
        'nom',
        'email',
        'password',
        'evenement_id',
        'actif',
        'tickets_count',
        'montant_total',
        'dernier_acces',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'tickets_count' => 'integer',
            'montant_total' => 'decimal:2',
            'dernier_acces' => 'datetime',
        ];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'agent_vente_id');
    }

    // Vérifie si l'agent de vente peut être réaffecté à un autre événement
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

    // Tous les tickets de cet agent (global)
    public function ticketsGlobaux()
    {
        return Ticket::where('agent_vente_id', $this->id)
            ->with(['tarif', 'evenement'])->latest('date_achat');
    }

    // Stats globales de cet agent de vente
    public function statsGlobales(): array
    {
        $ticketsBase = Ticket::where('agent_vente_id', $this->id);

        $parTarif = $ticketsBase->clone()
            ->selectRaw('tarif_id, COUNT(*) as total, SUM(montant) as montant')
            ->groupBy('tarif_id')
            ->with('tarif')
            ->get();

        $parMethode = $ticketsBase->clone()
            ->selectRaw('methode_paiement, COUNT(*) as total')
            ->groupBy('methode_paiement')
            ->get();

        return [
            'total_tickets' => $ticketsBase->clone()->count(),
            'montant_total' => $ticketsBase->clone()->sum('montant'),
            'aujourd_hui' => $ticketsBase->clone()->whereDate('date_achat', today())->count(),
            'par_tarif' => $parTarif,
            'par_methode' => $parMethode,
        ];
    }

    // Stats pour un événement précis
    public function statsParEvenement(int $evenementId): array
    {
        $ticketsBase = Ticket::where('agent_vente_id', $this->id)
            ->where('evenement_id', $evenementId);

        $parTarif = $ticketsBase->clone()
            ->selectRaw('tarif_id, COUNT(*) as total, SUM(montant) as montant')
            ->groupBy('tarif_id')
            ->with('tarif')
            ->get();

        $parMethode = $ticketsBase->clone()
            ->selectRaw('methode_paiement, COUNT(*) as total')
            ->groupBy('methode_paiement')
            ->get();

        return [
            'total_tickets' => $ticketsBase->clone()->count(),
            'montant_total' => $ticketsBase->clone()->sum('montant'),
            'aujourd_hui' => $ticketsBase->clone()->whereDate('date_achat', today())->count(),
            'par_tarif' => $parTarif,
            'par_methode' => $parMethode,
        ];
    }
}
