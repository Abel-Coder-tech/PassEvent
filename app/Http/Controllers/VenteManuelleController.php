<?php

namespace App\Http\Controllers;

use App\Exceptions\PlacesInsuffisantesException;
use App\Mail\TicketEmail;
use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Services\FedapayService;
use App\Services\PaymentMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VenteManuelleController extends Controller
{
    protected FedapayService $fedapay;

    public function __construct(FedapayService $fedapay)
    {
        $this->fedapay = $fedapay;
    }

    // Page de vente manuelle avec historique du jour
    public function create()
    {
        $user = Auth::user();

        // Événements éligibles (publiés ou brouillons, pas encore passés)
        $evenements = Evenement::where('user_id', $user->id)
            ->whereIn('statut', ['publié', 'brouillon'])
            ->where(fn ($q) => $q->whereNull('date_event')->orWhere('date_event', '>=', now()))
            ->orderBy('date_event', 'asc')
            ->get();

        // Ventes manuelles du jour courant
        $debutJour = now()->startOfDay()->utc();
        $finJour = now()->endOfDay()->utc();
        $ventesJour = Ticket::whereHas('evenement', fn ($q) => $q->where('user_id', $user->id))
            ->where(fn ($q) => $q
                ->where('source', 'vente_manuelle') // Ventes manuelles (espèces, gratuites, mobile confirmé)
                ->orWhere('transaction_id', 'like', 'MANUEL-%')) // Compatibilité données historiques
            ->whereBetween('date_achat', [$debutJour, $finJour])
            ->with('evenement')
            ->latest('date_achat')
            ->get();

        $totalVentesJour = $ventesJour->count();
        $montantVentesJour = $ventesJour->sum('montant');

        $publicKey = $this->fedapay->getPublicKey();
        $sandbox = $this->fedapay->isSandbox();

        return view('ventes-manuelles.create', compact(
            'evenements',
            'ventesJour',
            'totalVentesJour',
            'montantVentesJour',
            'publicKey',
            'sandbox',
        ));
    }

    // Enregistre une vente manuelle (gratuite, espèces ou paiement mobile).
    // Le panier peut contenir plusieurs tarifs : lignes[tarif_id] = quantité
    // (l'ancien format tarif_id + quantite reste accepté).
    public function store(Request $request, PaymentMonitoringService $paymentMonitoring)
    {
        $evenement = Evenement::where('id', $request->evenement_id)
            ->where('user_id', Auth::id()) // Vérification de propriété
            ->firstOrFail();

        $derniereDate = $evenement->derniereDate();
        if ($derniereDate && $derniereDate->date_debut->isPast()) {
            return response()->json([
                'errors' => [
                    'evenement_id' => ['Cet événement est terminé. La vente manuelle est fermée.'],
                ],
            ], 422);
        }

        $rules = [
            'evenement_id' => 'required|exists:evenement,id',
            'nom_acheteur' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'required|email|max:255',
            'quantite' => ($evenement->gratuit ? 'required' : 'nullable').'|integer|min:1|max:20',
            'code_promo' => 'nullable|string|max:50',
        ];
        $messages = [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'nom_acheteur.required' => 'Le nom de l\'acheteur est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité doit être d\'au moins 1.',
            'quantite.max' => 'La quantité ne doit pas dépasser 20.',
            'lignes.*.max' => 'Pas plus de 20 billets par tarif.',
        ];

        if (! $evenement->gratuit) {
            $rules['tarif_id'] = 'nullable|integer';
            $rules['lignes'] = 'nullable|array';
            $rules['lignes.*'] = 'nullable|integer|min:0|max:20';
            $rules['methode_paiement'] = 'required|in:especes,mobile';

            if (Auth::user()->type === 'universitaire') {
                $rules['categorie'] = 'nullable|string';
            }

            // Blocage espèces si seuil mobile money non atteint ou si bloqué par le superadmin
            if ($request->methode_paiement === 'especes' && ! $evenement->ventesEspecesActivees()) {
                if ($evenement->ventesEspecesBloqueesSuperadmin()) {
                    return response()->json([
                        'errors' => [
                            'methode_paiement' => [
                                'Les ventes espèces sont actuellement désactivées pour cet événement. Utilisez le mobile money.',
                            ],
                        ],
                    ], 422);
                }
                $seuil = (int) ceil($evenement->capacite * Evenement::SEUIL_ESPECES_PCT / 100);
                $vendus = $evenement->ticketsEnLigneCount();

                return response()->json([
                    'errors' => [
                        'methode_paiement' => [
                            "Ventes espèces bloquées. Vous devez vendre au moins {$seuil} ticket(s) par mobile money avant de pouvoir vendre en espèces. Progression : {$vendus}/{$seuil} tickets vendus en ligne.",
                        ],
                    ],
                ], 422);
            }
        }

        $validated = $request->validate($rules, $messages);

        // --- Cas 1 : Événement gratuit (un billet unique, valable tous les jours) ---
        if ($evenement->gratuit) {
            $tarif = $evenement->tarifs()->where('statut', 'actif')->first() ?? Tarif::create([
                'evenement_id' => $evenement->id,
                'nom' => 'Gratuit',
                'prix' => 0,
                'statut' => 'actif',
                'quantite_disponible' => $evenement->capacite,
                'quantite_vendue' => 0,
            ]);

            $resultat = $this->creerSousVerrou($evenement, [[
                'tarif' => $tarif,
                'quantite' => (int) $validated['quantite'],
                'creer' => fn (Tarif $t) => Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $t->id,
                    'source' => 'vente_manuelle',
                    'code_unique' => Ticket::genererCodeSecurise(),
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $validated['nom_acheteur'],
                    'email_acheteur' => $validated['email'] ?? null,
                    'whatsapp_acheteur' => $validated['whatsapp'] ?? null,
                    'nom_tarif' => $t->nom,
                    'montant' => 0,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => null,
                    'transaction_id' => 'MANUEL-GRATUIT-'.strtoupper(Str::random(6)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]),
            ]], verifierStockTarif: false, compterVente: true);

            if (isset($resultat['erreurs'])) {
                return response()->json(['errors' => $resultat['erreurs']], 422);
            }

            $tickets = $resultat['tickets'];
            $this->envoyerBillets($tickets, 'Vente manuelle gratuite');

            return response()->json([
                'success' => true,
                'message' => count($tickets) > 1
                    ? count($tickets).' inscription(s) gratuite(s) enregistrée(s) avec succès.'
                    : 'Inscription gratuite enregistrée avec succès.',
                'tickets' => $tickets,
                'total' => 0,
            ]);
        }

        // --- Panier : tarif_id => quantité ---
        $demandes = collect($validated['lignes'] ?? [])
            ->map(fn ($quantite) => (int) $quantite)
            ->filter(fn ($quantite) => $quantite > 0);

        if ($demandes->isEmpty() && ! empty($validated['tarif_id'])) {
            $demandes = collect([(int) $validated['tarif_id'] => (int) ($validated['quantite'] ?? 1)]);
        }

        if ($demandes->isEmpty()) {
            return response()->json(['errors' => ['tarif_id' => ['Ajoutez au moins un billet.']]], 422);
        }

        if ($demandes->sum() > 20) {
            return response()->json(['errors' => ['quantite' => ['Pas plus de 20 billets par vente.']]], 422);
        }

        $tarifs = Tarif::where('evenement_id', $evenement->id)
            ->whereIn('id', $demandes->keys())
            ->with('jour')
            ->get()
            ->keyBy('id');

        if ($tarifs->count() !== $demandes->count()) {
            return response()->json(['errors' => ['tarif_id' => ['Un des billets choisis n\'existe plus.']]], 422);
        }

        foreach ($tarifs as $tarif) {
            if (! $tarif->estEncoreVendable()) {
                return response()->json([
                    'errors' => ['tarif_id' => ["« {$tarif->nom} » n'est plus en vente : la date associée est passée."]],
                ], 422);
            }
        }

        // Code promo : valable pour l'un des tarifs du panier
        $codePromo = null;
        if (! empty($validated['code_promo'])) {
            $derniereErreur = null;
            foreach ($tarifs as $tarif) {
                try {
                    $codePromo = CodePromo::validerPour($validated['code_promo'], $evenement, $tarif);
                    break;
                } catch (ValidationException $e) {
                    $derniereErreur = $e;
                }
            }
            if (! $codePromo && $derniereErreur) {
                throw $derniereErreur;
            }
        }

        $lignes = $tarifs->map(function (Tarif $tarif) use ($demandes, $codePromo) {
            $promoSurCeTarif = $codePromo && (int) $codePromo->tarif_id === (int) $tarif->id;
            $reduction = $promoSurCeTarif ? $codePromo->calculerReduction($tarif->prix) : 0;

            return [
                'tarif' => $tarif,
                'quantite' => $demandes[$tarif->id],
                'reduction' => $reduction,
                'unitaire' => max(0, $tarif->prix - $reduction),
                'code' => $promoSurCeTarif ? $codePromo->code : null,
            ];
        })->values();
        $montantTotal = $lignes->sum(fn ($ligne) => $ligne['unitaire'] * $ligne['quantite']);
        $nombreBillets = (int) $lignes->sum('quantite');

        // --- Cas 2 : Paiement en espèces (ou panier entièrement gratuit) ---
        if ($validated['methode_paiement'] === 'especes' || $montantTotal <= 0) {
            $especes = $validated['methode_paiement'] === 'especes';
            $resultat = $this->creerSousVerrou($evenement, $lignes->map(fn ($ligne) => [
                'tarif' => $ligne['tarif'],
                'quantite' => $ligne['quantite'],
                'creer' => fn (Tarif $t) => Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $t->id,
                    'source' => 'vente_manuelle',
                    'code_unique' => Ticket::genererCodeSecurise(),
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $validated['nom_acheteur'],
                    'email_acheteur' => $validated['email'] ?? null,
                    'whatsapp_acheteur' => $validated['whatsapp'] ?? null,
                    'nom_tarif' => $t->nom,
                    'montant' => $ligne['unitaire'],
                    'montant_reduction' => $ligne['reduction'],
                    'code_promo_utilise' => $ligne['code'],
                    'quantite' => 1,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => $especes ? 'especes' : null,
                    'type_paiement' => $especes ? 'especes' : null,
                    'transaction_id' => 'MANUEL-'.strtoupper(Str::random(6)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]),
            ])->all(), verifierStockTarif: true, compterVente: true);

            if (isset($resultat['erreurs'])) {
                return response()->json(['errors' => $resultat['erreurs']], 422);
            }

            $tickets = $resultat['tickets'];

            // Comptabilise le code promo une fois la vente confirmée
            if ($codePromo) {
                $codePromo->increment('nb_utilisations', 1);
            }

            $this->envoyerBillets($tickets, 'Vente manuelle');

            return response()->json([
                'success' => true,
                'message' => "{$nombreBillets} billet(s) enregistré(s) avec succès.",
                'tickets' => $tickets,
                'total' => $montantTotal,
            ]);
        }

        // --- Cas 3 : Paiement mobile (FedaPay) : billets en attente, même groupe ---
        $groupTransactionId = 'GRP-'.strtoupper(Str::random(16));

        // Les billets en attente ne sont comptés dans quota_vendu qu'à la
        // confirmation du paiement, mais ils occupent déjà leur jour en
        // multijours : ils sont donc eux aussi créés sous le verrou.
        $resultat = $this->creerSousVerrou($evenement, $lignes->map(fn ($ligne) => [
            'tarif' => $ligne['tarif'],
            'quantite' => $ligne['quantite'],
            'creer' => fn (Tarif $t) => Ticket::create([
                'evenement_id' => $evenement->id,
                'tarif_id' => $t->id,
                'source' => 'vente_manuelle',
                'code_unique' => Ticket::genererCodeReservation(),
                'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                'nom_acheteur' => $validated['nom_acheteur'],
                'email_acheteur' => $validated['email'],
                'whatsapp_acheteur' => $validated['whatsapp'] ?? null,
                'nom_tarif' => $t->nom,
                'montant' => $ligne['unitaire'],
                'montant_reduction' => $ligne['reduction'],
                'code_promo_utilise' => $ligne['code'],
                'quantite' => 1,
                'statut_paiement' => 'en_attente',
                'methode_paiement' => 'mobile_money',
                'type_paiement' => 'mobile_money',
                'transaction_id' => $groupTransactionId,
                'utilise' => false,
                'date_achat' => now(),
            ]),
        ])->all(), verifierStockTarif: true, compterVente: false);

        if (isset($resultat['erreurs'])) {
            return response()->json(['errors' => $resultat['erreurs']], 422);
        }

        $tickets = $resultat['tickets'];

        $paymentMonitoring->record(
            'paiement_initie',
            [
                'transaction_id' => $groupTransactionId,
                'event_id' => $evenement->id,
                'ticket_count' => count($tickets),
                'amount' => round((float) $montantTotal, 2),
                'currency' => 'XOF',
                'source' => 'vente_manuelle',
                'payment_status' => 'en_attente',
                'actor_id' => Auth::id(),
            ],
            $request,
            $tickets[0]->id,
        );

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $tickets[0]->id,
                'transaction_id' => $groupTransactionId,
                'montant' => (int) $montantTotal,
                'nom_acheteur' => $tickets[0]->nom_acheteur,
                'email_acheteur' => $tickets[0]->email_acheteur,
                'evenement_titre' => $evenement->titre,
            ],
        ]);
    }

    // Envoie le billet de chaque ticket délivrable (vente gratuite ou espèces).
    private function envoyerBillets(array $tickets, string $contexte): void
    {
        foreach ($tickets as $ticket) {
            if ($ticket->email_acheteur && $ticket->estDelivrable()) {
                try {
                    $ticket->load('evenement', 'tarif');
                    Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
                    Log::info($contexte.' - Email envoyé à '.$ticket->email_acheteur);
                } catch (\Exception $e) {
                    Log::error($contexte.' - Erreur email : '.$e->getMessage());
                }
            }
        }
    }

    /**
     * Vérifie les places et crée les billets de tout le panier dans une même
     * transaction, sous verrou de l'événement et de chaque tarif : deux ventes
     * simultanées ne peuvent pas se partager la dernière place. Une ligne sans
     * place annule tout le panier (rien n'est créé).
     *
     * @param  array<int, array{tarif: Tarif, quantite: int, creer: callable(Tarif): Ticket}>  $lignes
     * @return array{tickets: array<int, Ticket>}|array{erreurs: array<string, array<int, string>>}
     */
    private function creerSousVerrou(Evenement $evenement, array $lignes, bool $verifierStockTarif, bool $compterVente): array
    {
        try {
            return DB::transaction(function () use ($evenement, $lignes, $verifierStockTarif, $compterVente) {
                $evenementVerrouille = Evenement::whereKey($evenement->id)->lockForUpdate()->firstOrFail();
                $tickets = [];

                foreach ($lignes as $ligne) {
                    $quantite = (int) $ligne['quantite'];
                    $tarifVerrouille = Tarif::whereKey($ligne['tarif']->id)->lockForUpdate()->firstOrFail();
                    $evenementVerrouille->resetCachesCapacite();

                    $places = $evenementVerrouille->placesRestantesPourTarif($tarifVerrouille);
                    if ($verifierStockTarif && $tarifVerrouille->quantite_disponible !== null) {
                        $places = min($places, max(0, $tarifVerrouille->quantite_disponible - $tarifVerrouille->quantite_vendue));
                    }

                    if ($places < $quantite) {
                        throw new PlacesInsuffisantesException($tarifVerrouille, max(0, $places));
                    }

                    for ($i = 0; $i < $quantite; $i++) {
                        $tickets[] = $ligne['creer']($tarifVerrouille)->load('evenement');
                    }

                    if ($compterVente) {
                        $evenementVerrouille->increment('quota_vendu', $quantite);
                        $tarifVerrouille->increment('quantite_vendue', $quantite);
                    }
                }

                return ['tickets' => $tickets];
            });
        } catch (PlacesInsuffisantesException $e) {
            return ['erreurs' => [
                'quantite' => ["Places restantes insuffisantes pour « {$e->tarif->nom} » : il n'en reste que {$e->placesRestantes}."],
            ]];
        }
    }

    // Récupère les tarifs d'un événement pour le formulaire dynamique
    public function getTarifs(Request $request)
    {
        $rules = [
            'evenement_id' => 'required|exists:evenement,id',
        ];
        $messages = [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'evenement_id.exists' => 'L\'événement sélectionné est invalide.',
        ];

        $request->validate($rules);

        $evenement = Evenement::where('id', $request->evenement_id)
            ->where('user_id', Auth::id()) // Vérification de propriété
            ->firstOrFail();

        $tarifs = collect();

        if (! $evenement->gratuit) {
            $evenement->loadMissing('dates');
            $tarifs = Tarif::where('evenement_id', $evenement->id)
                ->where('statut', 'actif')
                ->with('jour')
                ->get()
                ->filter(fn (Tarif $tarif) => $tarif->estEncoreVendable())
                ->sortBy(fn (Tarif $tarif) => [
                    $tarif->estPack() ? 1 : 0,
                    $tarif->jour?->date_debut?->timestamp ?? PHP_INT_MAX,
                    (float) $tarif->prix,
                ])
                ->map(fn (Tarif $tarif) => [
                    'id' => $tarif->id,
                    'nom' => $tarif->nom,
                    'prix' => (float) $tarif->prix,
                    // Un « pack » n'a de sens qu'en multijours (tarif valable tous les jours).
                    'pack' => $evenement->estMultiJour() && $tarif->estPack(),
                    'jour' => $tarif->jour?->libelle(),
                    'jour_date' => $tarif->jour?->date_debut?->isoFormat('ddd D MMM · HH:mm'),
                    'restantes' => max(0, min(
                        $evenement->placesRestantesPourTarif($tarif),
                        $tarif->quantite_disponible === null ? PHP_INT_MAX : (int) $tarif->quantite_disponible - (int) $tarif->quantite_vendue
                    )),
                    'nb_jours' => max(1, $evenement->dates->count()),
                ])
                ->values();
        }

        return response()->json([
            'tarifs' => $tarifs,
            'gratuit' => $evenement->gratuit,
            'especes_activees' => $evenement->ventesEspecesActivees(),
            'seuil' => $evenement->gratuit ? 0 : (int) ceil($evenement->capacite * Evenement::SEUIL_ESPECES_PCT / 100),
            'vendus_en_ligne' => $evenement->ticketsEnLigneCount(),
        ]);
    }

    // Vérifie un code promo pour afficher la réduction dans le récapitulatif
    public function verifierCodePromo(Request $request)
    {
        $request->validate([
            'evenement_id' => 'required|exists:evenement,id',
            'tarif_id' => 'required|exists:tarifs,id',
            'code' => 'required|string|max:50',
        ]);

        $evenement = Evenement::where('id', $request->evenement_id)
            ->where('user_id', Auth::id()) // Vérification de propriété
            ->firstOrFail();

        $tarif = Tarif::where('evenement_id', $evenement->id)->findOrFail($request->tarif_id);

        try {
            $codePromo = CodePromo::validerPour($request->code, $evenement, $tarif);
        } catch (ValidationException $e) {
            return response()->json([
                'valide' => false,
                'erreur' => $e->validator->errors()->first('code_promo'),
            ]);
        }

        $reduction = $codePromo->calculerReduction($tarif->prix);

        return response()->json([
            'valide' => true,
            'code' => $codePromo->code,
            'reduction' => $reduction,
            'montant_unitaire' => max(0, $tarif->prix - $reduction),
        ]);
    }
}
