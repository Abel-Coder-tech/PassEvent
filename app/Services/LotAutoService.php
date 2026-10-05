<?php

namespace App\Services;

use App\Mail\LotAutoConfirme;
use App\Models\Log;
use App\Models\LotPhysique;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            'estDemande' => (bool) ($lots->first()?->estUneDemande()),
            'lots' => $lots->map(fn ($l) => [
                'nom' => $l->tarif?->nom ?? 'Pass',
                'quantite' => $l->quantite,
                'telecharger' => $l->statut === LotPhysique::STATUT_TRANSMIS
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
                    'statut' => $lot->auto_genere ? LotPhysique::STATUT_TRANSMIS : LotPhysique::STATUT_PAYE,
                    'transmis_at' => $lot->auto_genere ? now() : null,
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

    // Notifications après confirmation du paiement.
    // Commande « Générer mes QR codes » : email à l'organisateur, planches disponibles.
    // Demande au super admin : aucun email à l'organisateur ; le super admin est prévenu
    // (notification tableau de bord + email support) avec le statut payé et le template fourni.
    public static function notifierPaiementAccepte(Collection $lots): void
    {
        $premier = $lots->first();

        if (! $premier) {
            return;
        }

        if ($premier->auto_genere) {
            try {
                Mail::to($premier->email_reception)->send(new LotAutoConfirme($lots));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Email de confirmation de lot non envoyé : '.$e->getMessage());
            }

            return;
        }

        self::notifierSuperAdminDemandePayee($lots);
    }

    // Notification système (super dashboard) + email support : demande de QR codes payée
    private static function notifierSuperAdminDemandePayee(Collection $lots): void
    {
        $premier = $lots->first();
        $evenement = $premier->evenement;
        $organisateur = $premier->user;
        $total = round((float) $lots->sum('montant_commission'), 2);

        $detailLots = $lots->map(function ($lot) {
            $template = $lot->templatePresent() ? 'oui' : 'non';

            return "• ".($lot->tarif?->nom ?? 'Pass')." : {$lot->quantite} QR code(s) — template : {$template}";
        })->implode("\n");

        $message = "Demande de QR codes payée\n"
            ."Commande : {$premier->reference_paiement}\n"
            .'Organisateur : '.($organisateur?->nom ?? '—').' ('.($organisateur?->email ?? '—').")\n"
            .'Événement : '.($evenement?->titre ?? '—')."\n"
            ."Commission réglée : ".number_format($total, 0, ',', ' ')." F\n"
            ."{$detailLots}\n\n"
            ."Étapes restantes : positionner les QR codes sur le template de l'organisateur, "
            .'générer la planche puis la lui transmettre.';

        Message::create([
            'user_id' => null, // Notification système visible côté super admin
            'evenement_id' => $premier->evenement_id,
            'nom_complet' => $organisateur?->nom,
            'email' => $organisateur?->email,
            'objet' => '[Demande payée] '.$premier->nom.' — QR codes à générer',
            'message' => $message,
            'lu' => false,
        ]);

        $supportEmail = config('mail.support_address');

        if ($supportEmail) {
            try {
                Mail::mailer('support')->raw(
                    "Demande de QR codes payée\n\n{$message}",
                    function ($mail) use ($supportEmail) {
                        $mail->to($supportEmail)->subject('[QR codes] Demande payée — '.$evenement?->titre);
                    }
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Email super admin - demande QR codes payée non envoyé : '.$e->getMessage());
            }
        }
    }
}
