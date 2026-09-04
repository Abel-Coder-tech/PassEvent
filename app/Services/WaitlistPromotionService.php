<?php

namespace App\Services;

use App\Http\Controllers\EvenementPublicController;
use App\Mail\WaitlistPlaceDisponible;
use App\Models\Evenement;
use App\Models\EventWaitlist;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Propose automatiquement aux personnes en file d'attente une place dès qu'elle se libère.
 * La place libérée est MOMENTANÉMENT re-réservée pour le premier de la file (ticket en_attente)
 * afin de garantir qu'il ne soit pas devancé par un nouvel acheteur ; il paie via le lien reçu.
 */
class WaitlistPromotionService
{
    /**
     * Re-réserve les places libérées pour la file d'attente de l'événement, tant qu'il reste
     * des places ET des inscrits en attente. Idempotent et atomique.
     */
    public function promouvoirEvenement(int $evenementId): int
    {
        return DB::transaction(function () use ($evenementId) {
            $promus = 0;

            $evenement = Evenement::whereKey($evenementId)->lockForUpdate()->first();
            if (! $evenement) {
                return 0;
            }

            while (true) {
                $placesRestantes = $evenement->capacite - $evenement->quota_vendu;
                if ($placesRestantes < 1) {
                    break; // Plus de place libre
                }

                // Premier inscrit en attente (ordre d'arrivée) — verrouillé pour éviter les doublons
                $candidat = EventWaitlist::where('evenement_id', $evenementId)
                    ->where('statut', 'en_attente')
                    ->orderBy('created_at')
                    ->first();

                if (! $candidat) {
                    break; // Plus personne en file d'attente
                }

                // On "réserve" la place pour le premier de la file
                $promu = EventWaitlist::whereKey($candidat->id)
                    ->where('statut', 'en_attente')
                    ->lockForUpdate()
                    ->first();

                if (! $promu) {
                    break; // Déjà pris par un autre processus
                }

                $promu->update([
                    'statut' => 'place_offerte',
                    'place_offerte_le' => now(),
                ]);

                // Crée un ticket de réservation pour que le bénéficiaire paie via le flux classique
                $tarif = $promu->tarif;

                $ticket = Ticket::create([
                    'evenement_id' => $evenementId,
                    'tarif_id' => $promu->tarif_id,
                    'source' => 'waitlist',
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', (string) Str::uuid(), config('app.key') ?? 'fallback'),
                    'email_acheteur' => strtolower($promu->email_acheteur),
                    'telephone_acheteur' => $promu->telephone_acheteur,
                    'nom_acheteur' => $promu->nom_acheteur,
                    'nom_tarif' => $tarif?->nom ?? 'Standard',
                    'montant' => $promu->montant_unitaire,
                    'montant_reduction' => $promu->montant_reduction,
                    'quantite' => $promu->quantite ?: 1,
                    'statut_paiement' => 'en_attente',
                    'transaction_id' => 'GRP-'.strtoupper(Str::random(16)),
                    'reservation_expire_le' => now()->addMinutes(EvenementPublicController::DUREE_RESERVATION_MINUTES),
                    'date_achat' => now(),
                    'code_promo_utilise' => $promu->code_promo_utilise,
                ]);
                $ticket->update(['code_unique' => Ticket::genererCodeSecurise()]);

                // La place est (re)comptée grâce au ticket en_attente
                $evenement->increment('quota_vendu', $ticket->quantite);

                try {
                    Mail::to($ticket->email_acheteur)
                        ->queue(new WaitlistPlaceDisponible($ticket, $evenement));
                } catch (\Exception $e) {
                    Log::error('Waitlist - email place disponible non envoyé : '.$e->getMessage());
                }

                $promus++;
            }

            return $promus;
        });
    }
}
