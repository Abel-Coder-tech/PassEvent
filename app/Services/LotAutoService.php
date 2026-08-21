<?php

namespace App\Services;

use App\Models\Log;
use App\Models\LotPhysique;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LotAutoService
{
    // Données du modal de résultat (succès) : lots de la commande + liens de téléchargement
    public static function donneesResultat(string $reference): array
    {
        $lots = LotPhysique::with('tarif')
            ->where('reference_paiement', $reference)
            ->get();

        return [
            'reference' => $reference,
            'lots' => $lots->map(fn ($l) => [
                'nom' => $l->tarif?->nom ?? 'Pass',
                'quantite' => $l->quantite,
                'telecharger' => $l->statut === 'transmis'
                    ? route('admin.lots-physiques.download', $l)
                    : null,
            ])->values()->all(),
        ];
    }

    // Crée les tickets de chaque lot auto-généré et marque les lots transmis.
    // Appelé UNIQUEMENT après vérification du paiement via l'API FedaPay (callback ou webhook).
    // Idempotent : le verrouillage + le contrôle de statut empêchent une double génération
    // si le callback et le webhook arrivent simultanément.
    public static function confirmerLots(Collection $lots, string $transactionId): bool
    {
        return DB::transaction(function () use ($lots, $transactionId) {
            $verrouilles = LotPhysique::whereIn('id', $lots->pluck('id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $premier = $verrouilles->first();
            if (! $premier || $premier->statut !== 'en_attente_paiement') {
                return false; // Déjà traité (callback + webhook simultanés)
            }

            foreach ($verrouilles as $lot) {
                $tarif = $lot->tarif;

                for ($i = 0; $i < $lot->quantite; $i++) {
                    $ticket = Ticket::create([
                        'evenement_id' => $lot->evenement_id,
                        'tarif_id' => $lot->tarif_id,
                        'lot_physique_id' => $lot->id,
                        'source' => 'physique',
                        'code_unique' => 'TMP',
                        'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                        'email_acheteur' => null,
                        'telephone_acheteur' => null,
                        'nom_acheteur' => null,
                        'nom_tarif' => $tarif?->nom,
                        'montant' => (float) ($tarif?->prix ?? 0),
                        'montant_reduction' => 0,
                        'quantite' => 1,
                        'statut_paiement' => 'payé',
                        'methode_paiement' => 'especes',
                        'type_paiement' => 'especes',
                        'transaction_id' => 'PHYS-'.strtoupper(Str::random(8)),
                        'utilise' => false,
                        'date_achat' => now(),
                    ]);
                    $ticket->update([
                        'code_unique' => Ticket::genererCodeSecurise(),
                    ]);
                }

                $lot->update([
                    'statut' => 'transmis',
                    'transmis_at' => now(),
                    'fedapay_transaction_id' => $transactionId,
                ]);

                Log::create([
                    'type_operation' => 'lot_physique_auto',
                    'ticket_id' => null,
                    'details' => [
                        'action' => 'generation_auto_apres_paiement',
                        'lot_id' => $lot->id,
                        'reference_paiement' => $lot->reference_paiement,
                        'quantite' => $lot->quantite,
                        'commission' => (float) $lot->montant_commission,
                        'transaction_id' => $transactionId,
                    ],
                    'ip' => null,
                ]);
            }

            return true;
        });
    }
}
