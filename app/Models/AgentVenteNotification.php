<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentVenteNotification extends Model
{
    protected $table = 'agent_vente_notifications';

    public const TYPE_INFO = 'info';
    public const TYPE_VENTE = 'vente';
    public const TYPE_PAIEMENT = 'paiement';
    public const TYPE_ANNULATION = 'annulation';
    public const TYPE_EXPIRATION = 'expiration';
    public const TYPE_RENVOI = 'renvoi';
    public const TYPE_COMPTE = 'compte';
    public const TYPE_EVENEMENT = 'evenement';

    public const DESTINATAIRE_AGENT = 'agent';
    public const DESTINATAIRE_TOUS_AGENTS = 'tous_agents';
    public const DESTINATAIRE_EVENEMENT = 'evenement';

    protected $fillable = [
        'agent_vente_id',
        'evenement_id',
        'titre',
        'message',
        'type',
        'destinataire_type',
        'data',
        'lu_at',
    ];

    protected function casts(): array
    {
        return [
            'lu_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function agentVente(): BelongsTo
    {
        return $this->belongsTo(AgentVente::class, 'agent_vente_id');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    // Notifications visibles par un agent : les messages globaux + ceux de son événement
    public function scopeVisibleParAgent(Builder $query, AgentVente $agent): Builder
    {
        return $query->where('agent_vente_id', $agent->id)
            ->where(function (Builder $q) use ($agent) {
                $q->whereNull('evenement_id')
                    ->orWhere('evenement_id', $agent->evenement_id);
            });
    }

    public function scopeNonLues(Builder $query): Builder
    {
        return $query->whereNull('lu_at');
    }

    public function scopeDeType(Builder $query, ?string $type): Builder
    {
        return $type === null || $type === '' ? $query : $query->where('type', $type);
    }

    // Point d'entrée unique pour pousser une notification vers une app agent de vente
    public static function envoyer(
        AgentVente $agent,
        string $titre,
        string $message,
        ?int $evenementId = null,
        string $type = self::TYPE_INFO,
        array $data = [],
        string $destinataireType = self::DESTINATAIRE_AGENT
    ): self {
        return self::create([
            'agent_vente_id' => $agent->id,
            'evenement_id' => $evenementId ?? $agent->evenement_id,
            'titre' => $titre,
            'message' => $message,
            'type' => $type,
            'destinataire_type' => $destinataireType,
            'data' => $data ?: null,
        ]);
    }

    // Diffuse une notification à tous les agents actifs d'un événement
    // (ou à tous les agents actifs si aucun événement n'est fourni).
    public static function diffuser(
        string $titre,
        string $message,
        ?Evenement $evenement = null,
        string $type = self::TYPE_EVENEMENT,
        array $data = [],
        ?AgentVente $except = null
    ): int {
        $query = AgentVente::query()->where('actif', true);

        if ($evenement !== null) {
            $query->where('evenement_id', $evenement->id);
        }

        if ($except !== null) {
            $query->where('id', '!=', $except->id);
        }

        $destinataire = $evenement !== null
            ? self::DESTINATAIRE_EVENEMENT
            : self::DESTINATAIRE_TOUS_AGENTS;

        $count = 0;

        $query->select('id', 'evenement_id')->chunkById(200, function ($agents) use (
            $titre, $message, $evenement, $type, $data, $destinataire, &$count
        ) {
            foreach ($agents as $agent) {
                self::create([
                    'agent_vente_id' => $agent->id,
                    'evenement_id' => $evenement?->id ?? $agent->evenement_id,
                    'titre' => $titre,
                    'message' => $message,
                    'type' => $type,
                    'destinataire_type' => $destinataire,
                    'data' => $data ?: null,
                ]);
                $count++;
            }
        });

        return $count;
    }

    public function marquerLue(): bool
    {
        if ($this->lu_at !== null) {
            return true;
        }

        return $this->update(['lu_at' => now()]);
    }

    public function payload(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'message' => $this->message,
            'type' => $this->type ?? self::TYPE_INFO,
            'destinataire_type' => $this->destinataire_type ?? self::DESTINATAIRE_AGENT,
            'data' => $this->data ?? [],
            'lu' => $this->lu_at !== null,
            'lu_at' => $this->lu_at?->toIso8601String(),
            'date' => $this->created_at?->toIso8601String(),
        ];
    }
}
