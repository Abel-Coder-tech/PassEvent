<?php

namespace App\Services;

use App\Mail\TicketEmail;
use App\Models\DemandeRemboursement;
use App\Models\Evenement;
use App\Models\Log as LogModel;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;class ReconciliationService
{
    protected FedapayService $fedapay;

    public function __construct(FedapayService $fedapay)
    {
        $this->fedapay = $fedapay;
    }

    /**
     * Vérifie une transaction FedaPay via l'API (ne fait jamais confiance aux saisies client).
     */
    public function verifier(string $transactionId): array
    {
        $data = $this->fedapay->getTransaction($transactionId);

        if (!$data) {
            return ['ok' => false, 'message' => 'Transaction introuvable ou API FedaPay injoignable.'];
        }

        $statut = $data['status'] ?? null;

        return [
            'ok' => true,
            'approuve' => in_array($statut, ['approved', 'completed', 'accepted'], true),
            'statut' => $statut,
            'montant' => $data['amount'] ?? null,
            'devise' => $data['currency'] ?? null,
            'telephone' => $data['phone'] ?? null,
            'date' => $data['created_at'] ?? null,
            'customer' => $data['customer'] ?? null,
            'raw' => $data,
        ];
    }

    /**
     * Recherche des tickets (en attente ou payés) par transaction, email, téléphone ou code.
     */
    public function trouverTickets(?string $transactionId = null, ?string $email = null, ?string $telephone = null, ?string $code = null, ?int $evenementId = null): Collection
    {
        $query = Ticket::with('evenement', 'tarif');

        if ($transactionId) {
            $query->where(function ($q) use ($transactionId) {
                $q->where('transaction_id', $transactionId)
                    ->orWhere('fedapay_transaction_id', $transactionId);
            });
        }

        if ($email) {
            $query->where('email_acheteur', $email);
        }

        if ($telephone) {
            $query->where(function ($q) use ($telephone) {
                $q->where('telephone_acheteur', $telephone)
                    ->orWhere('telephone_paiement', $telephone);
            });
        }

        if ($code) {
            $query->where('code_unique', 'like', '%' . $code . '%');
        }

        if ($evenementId) {
            $query->where('evenement_id', $evenementId);
        }

        return $query->orderByDesc('date_achat')->limit(100)->get();
    }

    /**
     * Confirme des tickets en attente (paiement vérifié ou forcé) :
     * passage à payé, quotas, log d'audit et envoi de l'email.
     */
    public function confirmerTickets(Collection $tickets, ?string $transactionId, ?string $methodePaiement = null, ?string $telephonePaiement = null, bool $force = false): array
    {
        if ($tickets->isEmpty()) {
            return ['success' => false, 'message' => 'Aucun ticket sélectionné.'];
        }

        $confirmes = 0;
        $dejaPayes = 0;
        $ticketsConfirmes = [];

        DB::beginTransaction();
        try {
            foreach ($tickets as $ticket) {
                if ($ticket->statut_paiement === 'payé') {
                    $dejaPayes++;
                    continue;
                }

                $ticket->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $transactionId ?? $ticket->transaction_id,
                    'fedapay_transaction_id' => $transactionId ?? $ticket->fedapay_transaction_id,
                    'methode_paiement' => $methodePaiement ?? $ticket->methode_paiement ?? 'mobile_money',
                    'type_paiement' => PaiementMapper::moyenPaiement($methodePaiement ?? $ticket->methode_paiement ?? 'mobile_money'),
                    'telephone_paiement' => $telephonePaiement ?? $ticket->telephone_paiement,
                ]);

                $ticket->load('evenement', 'tarif');
                $ticket->evenement->increment('quota_vendu', $ticket->quantite);
                if ($ticket->tarif) {
                    $ticket->tarif->increment('quantite_vendue', $ticket->quantite);
                }

                $this->log('confirmation_ticket', $ticket, [
                    'transaction_id' => $transactionId,
                    'force' => $force,
                    'par' => $this->acteur(),
                ]);

                $ticketsConfirmes[] = $ticket;
                $confirmes++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ReconciliationService::confirmerTickets - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la confirmation des tickets.'];
        }

        foreach ($ticketsConfirmes as $ticket) {
            if (!$ticket->email_acheteur) {
                continue;
            }
            try {
                Mail::to($ticket->email_acheteur)->send(new TicketEmail(collect([$ticket])));
            } catch (\Exception $e) {
                Log::error('Reconciliation - email ticket non envoyé à ' . $ticket->email_acheteur . ' : ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "{$confirmes} ticket(s) confirmé(s)" . ($dejaPayes ? ", {$dejaPayes} déjà payé(s)" : '') . '.',
            'confirmes' => $confirmes,
            'deja_payes' => $dejaPayes,
        ];
    }

    /**
     * Recrée un ticket purgé ou manquant (source reconciliation) avec quota, log et email.
     */
    public function recreerTicket(array $data): array
    {
        $evenement = Evenement::find($data['evenement_id']);
        if (!$evenement) {
            return ['success' => false, 'message' => 'Événement introuvable.'];
        }

        $tarif = $evenement->tarifs()->where('statut', 'actif')->where('id', $data['tarif_id'] ?? 0)->first()
            ?? $evenement->tarifs()->where('statut', 'actif')->first();

        $nomTarif = $tarif?->nom ?? 'Standard';
        $montant = (float) ($data['montant'] ?? ($tarif?->prix ?? 0));
        $quantite = max(1, (int) ($data['quantite'] ?? 1));

        DB::beginTransaction();
        try {
            $tickets = collect();
            for ($i = 0; $i < $quantite; $i++) {
                $ticket = Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $tarif?->id,
                    'source' => 'reconciliation',
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $data['nom_acheteur'] ?? 'Client',
                    'telephone_acheteur' => $data['telephone'] ?? null,
                    'email_acheteur' => $data['email'] ?? null,
                    'nom_tarif' => $nomTarif,
                    'montant' => $montant,
                    'quantite' => 1,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => $data['methode_paiement'] ?? 'mobile_money',
                    'type_paiement' => PaiementMapper::moyenPaiement($data['methode_paiement'] ?? 'mobile_money'),
                    'transaction_id' => $data['transaction_id'] ?? ('RECON-' . strtoupper(Str::random(8))),
                    'fedapay_transaction_id' => $data['fedapay_transaction_id'] ?? null,
                    'utilise' => false,
                    'date_achat' => $data['date_achat'] ?? now(),
                ]);
                $ticket->update(['code_unique' => Ticket::genererCodeSecurise()]);
                $tickets->push($ticket);
            }

            $evenement->increment('quota_vendu', $quantite);
            if ($tarif) {
                $tarif->increment('quantite_vendue', $quantite);
            }

            foreach ($tickets as $ticket) {
                $this->log('recreation_ticket', $ticket, [
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'par' => $this->acteur(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ReconciliationService::recreerTicket - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la recréation du ticket.'];
        }

        foreach ($tickets as $ticket) {
            try {
                $ticket->load('evenement', 'tarif');
                Mail::to($ticket->email_acheteur)->send(new TicketEmail(collect([$ticket])));
            } catch (\Exception $e) {
                Log::error('Reconciliation - email ticket recréé non envoyé à ' . $ticket->email_acheteur . ' : ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "{$quantite} ticket(s) recréé(s) et envoyé(s).",
            'tickets' => $tickets,
        ];
    }

    /**
     * Supprime des tickets en attente (paiement non abouti). Ne touche jamais aux tickets payés.
     */
    public function supprimerGroupe(Collection $tickets, ?string $motif = null): array
    {
        $supprimes = 0;
        $ignores = 0;

        foreach ($tickets as $ticket) {
            if ($ticket->statut_paiement === 'payé') {
                $ignores++;
                continue;
            }
            $this->log('suppression_ticket', $ticket, ['motif' => $motif, 'par' => $this->acteur()]);
            $ticket->delete();
            $supprimes++;
        }

        return [
            'success' => true,
            'message' => "{$supprimes} ticket(s) supprimé(s)" . ($ignores ? ", {$ignores} payé(s) conservé(s)" : '') . '.',
        ];
    }

    /**
     * Renvoie l'email du ticket à l'acheteur.
     */
    public function renvoyerEmail(Ticket $ticket): array
    {
        $ticket->load('evenement', 'tarif');
        try {
            Mail::to($ticket->email_acheteur)->send(new TicketEmail(collect([$ticket])));
        } catch (\Exception $e) {
            Log::error('Reconciliation - renvoi email ticket ' . $ticket->id . ' : ' . $e->getMessage());
            return ['success' => false, 'message' => "Erreur lors de l'envoi de l'email."];
        }

        $this->log('renvoi_email', $ticket, ['par' => $this->acteur()]);

        return ['success' => true, 'message' => 'Email renvoyé au client.'];
    }

    /**
     * Remboursement direct superadmin (sans passer par l'organisateur).
     * Crée une demande déjà traitée, marque les tickets remboursés et avertit le client.
     */
    public function rembourser(Collection $tickets, string $motif, ?string $notes = null): array
    {
        if ($tickets->isEmpty()) {
            return ['success' => false, 'message' => 'Aucun ticket sélectionné.'];
        }

        $montantTotal = $tickets->sum('montant');

        DB::beginTransaction();
        try {
            $demande = DemandeRemboursement::create([
                'organisateur_id' => null,
                'evenement_id' => $tickets->first()->evenement_id,
                'origine' => 'support_superadmin',
                'type' => $tickets->count() > 1 ? 'groupe' : 'individuel',
                'montant_total' => $montantTotal,
                'motif' => $motif,
                'notes_admin' => $notes,
                'statut' => 'rembourse',
                'traitee_par' => auth('superadmin')->id(),
                'traitee_le' => now(),
            ]);

            $demande->tickets()->attach($tickets->pluck('id'));

            foreach ($tickets as $ticket) {
                if ($ticket->statut_paiement !== 'remboursé') {
                    $ticket->update(['statut_paiement' => 'remboursé']);
                }
                $this->log('remboursement_direct', $ticket, [
                    'demande_id' => $demande->id,
                    'montant' => $ticket->montant,
                    'par' => $this->acteur(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ReconciliationService::rembourser - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors du remboursement.'];
        }

        foreach ($tickets as $ticket) {
            $ticket->load('evenement');
            if (!$ticket->email_acheteur) {
                continue;
            }
            try {
                Mail::raw(
                    "Votre paiement de " . number_format($ticket->montant, 0, ',', ' ') . " F pour \"{$ticket->evenement->titre}\" a été remboursé.\n\n" .
                    "Motif : {$motif}\n\n" .
                    "Si vous avez des questions, contactez le support PaxEvent.",
                    function ($m) use ($ticket) {
                        $m->to($ticket->email_acheteur)
                            ->subject("[PaxEvent] Remboursement effectué - {$ticket->evenement->titre}");
                    }
                );
            } catch (\Exception $e) {
                Log::error('Reconciliation - email remboursement non envoyé à ' . $ticket->email_acheteur);
            }
        }

        return [
            'success' => true,
            'message' => "Remboursement direct de " . number_format($montantTotal, 0, ',', ' ') . " F enregistré (" . $tickets->count() . " ticket(s)).",
        ];
    }

    /**
     * Journalise chaque action de support pour audit.
     */
    protected function log(string $action, Ticket $ticket, array $details = []): void
    {
        LogModel::create([
            'ticket_id' => $ticket->id,
            'type_operation' => 'reconciliation',
            'details' => array_merge(['action' => $action], $details),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function acteur(): string
    {
        return auth('superadmin')->user()?->email ?? 'superadmin';
    }
}
