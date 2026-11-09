<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentVente;
use App\Models\CodePromo;
use App\Models\Ticket;
use App\Services\AgentVenteSaleService;
use App\Services\AgentVenteStatisticsService;
use App\Services\QrCodeService;
use App\Services\TicketPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AgentVenteSaleController extends Controller
{
    public function __construct(
        private AgentVenteSaleService $sales,
        private AgentVenteStatisticsService $statistics
    ) {}

    // Crée une vente : espèces (confirmation immédiate) ou mobile money (checkout FedaPay)
    public function store(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $evenement = $agent->evenement;

        $validated = $request->validate([
            'nom_acheteur' => ['required', 'string', 'max:255'],
            'email_acheteur' => ['required', 'email', 'max:255'],
            'whatsapp_acheteur' => ['nullable', 'string', 'max:30'],
            'tarif_id' => ['nullable', 'integer', 'exists:tarifs,id'],
            'methode_paiement' => ['nullable', 'string', 'in:cash,mobile_money,especes,mobile'],
            'code_promo' => ['nullable', 'string', 'max:50'],
            'quantite' => ['nullable', 'integer', 'min:1', 'max:20'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'external_id' => ['nullable', 'string', 'max:100'],
        ], [
            'nom_acheteur.required' => 'Veuillez saisir le nom de l\'acheteur.',
            'email_acheteur.required' => 'Veuillez saisir l\'adresse e-mail de l\'acheteur.',
            'email_acheteur.email' => 'Veuillez saisir une adresse e-mail valide.',
            'tarif_id.exists' => 'Le tarif sélectionné est invalide.',
            'methode_paiement.in' => 'Méthode de paiement invalide. Choisissez espèces ou mobile money.',
            'quantite.max' => 'Vous ne pouvez pas vendre plus de 20 billets à la fois.',
        ]);

        // La clé d'idempotence peut venir de l'en-tête Idempotency-Key ou du corps.
        $validated['idempotency_key'] = trim((string) (
            $request->header('Idempotency-Key')
            ?: ($validated['idempotency_key'] ?? $validated['external_id'] ?? '')
        ));

        if ($evenement && ! $evenement->gratuit) {
            if (empty($validated['tarif_id'])) {
                throw ValidationException::withMessages([
                    'tarif_id' => 'Veuillez sélectionner un tarif.',
                ]);
            }

            if (empty($validated['methode_paiement'])) {
                throw ValidationException::withMessages([
                    'methode_paiement' => 'Veuillez sélectionner une méthode de paiement.',
                ]);
            }
        }

        $result = $this->sales->create($agent, $validated, $request->ip(), $request->userAgent());

        $montant = (float) $result['montant_total'];
        $immediat = $result['statut'] === 'payé';
        $idempotent = (bool) ($result['idempotent'] ?? false);

        return response()->json([
            'message' => $idempotent
                ? 'Cette vente a déjà été enregistrée.'
                : ($immediat
                    ? 'Ticket vendu avec succès.'
                    : 'Vente enregistrée. En attente du paiement mobile.'),
            'statut' => $result['statut'],
            'idempotent' => $idempotent,
            'montant_total' => $montant,
            'montant_label' => $montant > 0
                ? number_format($montant, 0, ',', ' ').' FCFA'
                : 'Gratuit',
            'ticket' => $this->sales->ticketPayload($result['ticket'], $agent),
            'tickets' => $result['tickets']
                ->map(fn (Ticket $ticket) => $this->sales->ticketPayload($ticket, $agent))
                ->values(),
            'payment' => $result['payment'],
        ], $idempotent ? 200 : 201);
    }

    // Détail d'une vente (permet aussi de suivre un paiement mobile en attente)
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $this->sales->ticketDeAgent($ticket, $agent);

        $group = $this->sales->groupTickets($ticket, $agent);
        $payment = null;

        if ($ticket->statut_paiement === 'en_attente'
            && ! $ticket->annule
            && $ticket->expire_at === null
            && $ticket->evenement) {
            $payment = $this->sales->checkoutPayload($group, $ticket->evenement);
        }

        return response()->json([
            'ticket' => $this->sales->ticketPayload($ticket, $agent),
            'tickets' => $group
                ->map(fn (Ticket $item) => $this->sales->ticketPayload($item, $agent))
                ->values(),
            'journal' => $ticket->journalActions()
                ->latest('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($action) => $action->payload())
                ->values(),
            'payment' => $payment,
        ], 200);
    }

    // Historique paginé et filtrable des ventes de l'agent
    public function history(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        [$debut, $fin, $periode] = $this->resolvePeriode($request);

        $validated = $request->validate([
            'statut' => ['nullable', 'string', 'in:paye,en_attente,annule,expire,echoue,rembourse,tout'],
            'agent_vente_id' => ['nullable', 'integer'],
            'recherche' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer'],
        ]);

        $statut = $validated['statut'] ?? 'tout';
        $perPage = in_array((int) ($validated['per_page'] ?? 0), [10, 20, 50, 100], true)
            ? (int) $validated['per_page']
            : 20;

        $query = $agent->tickets()
            ->with(['tarif', 'evenement'])
            ->when($debut !== null, fn ($q) => $q->where('date_achat', '>=', $debut))
            ->when($fin !== null, fn ($q) => $q->where('date_achat', '<', $fin))
            ->when(filled($validated['recherche'] ?? null), function ($q) use ($validated) {
                $terme = '%'.trim($validated['recherche']).'%';
                $q->where(function ($sub) use ($terme) {
                    $sub->where('code_unique', 'like', $terme)
                        ->orWhere('nom_acheteur', 'like', $terme)
                        ->orWhere('email_acheteur', 'like', $terme)
                        ->orWhere('transaction_id', 'like', $terme);
                });
            })
            // Le filtre agent reste borné aux ventes de l'agent connecté :
            // un identifiant étranger ne renvoie donc jamais les ventes d'autrui.
            ->when(
                isset($validated['agent_vente_id']) && (int) $validated['agent_vente_id'] !== (int) $agent->id,
                fn ($q) => $q->whereRaw('1 = 0')
            );

        match ($statut) {
            'paye' => $query->where('statut_paiement', 'payé')
                ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
                ->whereNull('expire_at'),
            'en_attente' => $query->where('statut_paiement', 'en_attente')
                ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
                ->whereNull('expire_at'),
            'annule' => $query->where(fn ($q) => $q->where('annule', true)->orWhereNotNull('annule_at')),
            'expire' => $query->whereNotNull('expire_at'),
            'echoue' => $query->where('statut_paiement', 'échoué'),
            'rembourse' => $query->where('statut_paiement', 'remboursé'),
            default => null,
        };

        $montantFiltre = (float) (clone $query)
            ->where('statut_paiement', 'payé')
            ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
            ->whereNull('expire_at')
            ->sum('montant');
        $tickets = $query->latest('date_achat')->latest('id')->paginate($perPage);

        return response()->json([
            'periode' => $periode,
            'date_debut' => $debut?->toIso8601String(),
            'date_fin' => $fin?->toIso8601String(),
            'montant_filtre' => $montantFiltre,
            'tickets' => collect($tickets->items())
                ->map(fn (Ticket $ticket) => $this->sales->ticketPayload($ticket, $agent))
                ->values(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'from' => $tickets->firstItem(),
                'to' => $tickets->lastItem(),
            ],
        ], 200);
    }

    // Statistiques détaillées (période optionnelle, événement courant par défaut)
    public function statistics(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        [$debut, $fin, $periode] = $this->resolvePeriode($request);

        $stats = $this->statistics->apiStats($agent, $agent->evenement, $debut, $fin);

        return response()->json([
            'periode' => $periode,
            'date_debut' => $debut?->toIso8601String(),
            'date_fin' => $fin?->toIso8601String(),
            'evenement_id' => $agent->evenement_id,
            'stats' => $stats,
        ], 200);
    }

    // Liste des tarifs vendables + règles de vente
    public function tarifs(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $evenement = $agent->evenement;

        if (! $evenement) {
            return response()->json(['message' => 'no_event'], 403);
        }

        return response()->json([
            'event_id' => $evenement->id,
            'gratuit' => (bool) $evenement->gratuit,
            'tarifs' => $this->sales->tarifs($evenement),
            'ventes_especes' => $this->sales->cashAvailability($evenement),
            'paiement_mobile' => $this->sales->paymentConfig(),
        ], 200);
    }

    // Vérification d'un code promo avant la vente
    public function verifierCodePromo(Request $request): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $evenement = $agent->evenement;

        $validated = $request->validate([
            'tarif_id' => ['required', 'integer', 'exists:tarifs,id'],
            'code' => ['required', 'string', 'max:50'],
        ]);

        $tarif = $evenement?->tarifs()->find($validated['tarif_id']);

        if (! $tarif) {
            return response()->json([
                'valide' => false,
                'message' => 'Le tarif sélectionné est invalide.',
            ], 200);
        }

        try {
            $codePromo = CodePromo::validerPour($validated['code'], $evenement, $tarif);
        } catch (ValidationException $e) {
            return response()->json([
                'valide' => false,
                'message' => $e->validator->errors()->first('code_promo'),
            ], 200);
        }

        if (! $codePromo) {
            return response()->json([
                'valide' => false,
                'message' => 'Ce code promo n\'est pas valide.',
            ], 200);
        }

        $reduction = $codePromo->calculerReduction((float) $tarif->prix);

        return response()->json([
            'valide' => true,
            'code' => $codePromo->code,
            'reduction' => (float) $reduction,
            'montant_unitaire' => max(0, (float) $tarif->prix - $reduction),
        ], 200);
    }

    // Confirmation d'un paiement mobile après retour du checkout FedaPay
    public function confirm(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $result = $this->sales->confirmMobile(
            $agent,
            $ticket,
            $validated['transaction_id'],
            $validated['payment_method'] ?? null,
            $validated['phone'] ?? null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => $result['confirme']
                ? 'Paiement confirmé.'
                : 'Le paiement n\'a pas encore été confirmé.',
            ...$result,
        ], 200);
    }

    // Crée une session de paiement FedaPay côté serveur et renvoie l'URL de checkout
    public function payment(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $result = $this->sales->createPayment(
            $agent,
            $ticket,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => isset($result['payment_url'])
                ? 'Lien de paiement généré.'
                : 'Cette vente est déjà payée.',
            ...$result,
        ], 200);
    }

    // Resynchronise une vente mobile via external_id / ID FedaPay (sans dépendre du callback)
    public function sync(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $result = $this->sales->syncMobile(
            $agent,
            $ticket,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => $result['confirme']
                ? 'Paiement confirmé.'
                : ($result['message'] ?? 'Le paiement n\'a pas encore été confirmé.'),
            ...$result,
        ], 200);
    }

    // Renvoie le ou les billets payés par e-mail
    public function resend(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $result = $this->sales->resend(
            $agent,
            $ticket,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Billet(s) renvoyé(s) par e-mail.',
            ...$result,
        ], 200);
    }

    // Annule une vente mobile encore en attente (sans suppression, avec trace)
    public function cancel(Request $request, Ticket $ticket): JsonResponse
    {
        /** @var AgentVente $agent */
        $agent = $request->user();

        $result = $this->sales->cancelPending(
            $agent,
            $ticket,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => 'Vente annulée.',
            ...$result,
        ], 200);
    }

    // Téléchargement du PDF du billet (limite anti-abus)
    public function pdf(Request $request, Ticket $ticket): Response
    {
        /** @var AgentVente $agent */
        $agent = $request->user();
        $this->sales->ticketDeAgent($ticket, $agent);

        if ($ticket->statut_paiement !== 'payé' || $ticket->annule || $ticket->expire_at !== null) {
            abort(403, 'Le paiement de ce ticket n\'a pas été confirmé.');
        }

        $max = (int) config('app.max_downloads', 3);

        if ((int) $ticket->download_count >= $max) {
            abort(403, 'Limite de téléchargements atteinte ('.$max.' maximum).');
        }

        $qrCodeDataUri = QrCodeService::generateDataUri($ticket->code_unique, 170, 'H');
        $logoDataUri = Ticket::logoVioletDataUri();
        $pdf = TicketPdfService::generer($ticket, $qrCodeDataUri, $logoDataUri);

        $ticket->increment('download_count', 1);

        \App\Models\JournalAction::enregistrer([
            'ticket_id' => $ticket->id,
            'agent_vente_id' => $agent->id,
            'evenement_id' => $ticket->evenement_id,
            'action' => 'billet_telecharge',
            'statut_avant' => 'paye',
            'statut_apres' => 'paye',
            'reference' => $ticket->transaction_id,
            'details' => ['download_count' => (int) $ticket->download_count + 1],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $pdf->download('ticket-'.$ticket->code_unique.'.pdf');
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function resolvePeriode(Request $request): array
    {
        $validated = $request->validate([
            'periode' => ['nullable', 'string', 'in:aujourdhui,hier,semaine,mois,tout'],
            'date_debut' => ['nullable', 'date_format:Y-m-d'],
            'date_fin' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_debut'],
        ], [
            'periode.in' => 'Période invalide. Choisissez parmi : aujourdhui, hier, semaine, mois, tout.',
            'date_debut.date_format' => 'La date de début doit être au format AAAA-MM-JJ.',
            'date_fin.date_format' => 'La date de fin doit être au format AAAA-MM-JJ.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ]);

        if (! empty($validated['date_debut'])) {
            $debut = Carbon::parse($validated['date_debut'])->startOfDay();
            $fin = ! empty($validated['date_fin'])
                ? Carbon::parse($validated['date_fin'])->addDay()->startOfDay()
                : $debut->copy()->addDay();

            return [$debut, $fin, 'personnalisee'];
        }

        $periode = $validated['periode'] ?? 'tout';

        [$debut, $fin] = match ($periode) {
            'hier' => [now()->subDay()->startOfDay(), now()->startOfDay()],
            'semaine' => [now()->startOfWeek(), null],
            'mois' => [now()->startOfMonth(), null],
            'tout' => [null, null],
            default => [now()->startOfDay(), now()->addDay()->startOfDay()],
        };

        return [$debut, $fin, $periode];
    }
}
