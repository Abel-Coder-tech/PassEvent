<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Services\FedapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    // Enregistre une vente manuelle (gratuite, espèces ou paiement mobile)
    public function store(Request $request)
    {
        $evenement = Evenement::where('id', $request->evenement_id)
            ->where('user_id', Auth::id()) // Vérification de propriété
            ->firstOrFail();

        if ($evenement->date_event && $evenement->date_event->isPast()) {
            return response()->json([
                'errors' => [
                    'evenement_id' => ['Cet événement est terminé. La vente manuelle est fermée.'],
                ],
            ], 422);
        }

        $rules = [
            'evenement_id' => 'required|exists:evenement,id',
            'nom_acheteur' => 'required|string|max:255',
            'telephone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'quantite' => 'required|integer|min:1|max:20',
            'code_promo' => 'nullable|string|max:50',
        ];
        $messages = [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'nom_acheteur.required' => 'Le nom de l\'acheteur est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité doit être d\'au moins 1.',
            'quantite.max' => 'La quantité ne doit pas dépasser 20.',
        ];

        if (! $evenement->gratuit) {
            $rules['tarif_id'] = 'required|exists:tarifs,id'; // Tarif obligatoire pour événements payants
            $rules['methode_paiement'] = 'required|in:especes,mobile';

            $messages['tarif_id.required'] = 'Veuillez sélectionner un tarif.';

            if (Auth::user()->type === 'universitaire') {
                $rules['categorie'] = 'nullable|string';
            }

            if ($request->methode_paiement !== 'especes') {
                $rules['email'] = 'required|email|max:255'; // Email obligatoire pour paiement mobile
                $messages['email.required'] = 'L\'email est obligatoire pour le paiement mobile.';
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

        // Vérification des places restantes (capacité de l'événement)
        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
        if ($placesRestantes < $validated['quantite']) {
            return response()->json([
                'errors' => [
                    'quantite' => ["Places restantes insuffisantes. Il ne reste que {$placesRestantes} place(s) disponible(s) pour cet événement."],
                ],
            ], 422);
        }

        // --- Cas 1 : Événement gratuit ---
        if ($evenement->gratuit) {
            // Création de tickets gratuits
            $tarif = $evenement->tarifs()->where('statut', 'actif')->first();
            if (! $tarif) {
                // Crée un tarif par défaut si aucun n'existe
                $tarif = Tarif::create([
                    'evenement_id' => $evenement->id,
                    'nom' => 'Gratuit',
                    'prix' => 0,
                    'statut' => 'actif',
                    'quantite_disponible' => $evenement->capacite,
                    'quantite_vendue' => 0,
                ]);
            }

            $tickets = [];
            for ($i = 0; $i < $validated['quantite']; $i++) {
                $ticket = Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $tarif->id,
                    'source' => 'vente_manuelle',
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $validated['nom_acheteur'],
                    'telephone_acheteur' => $validated['telephone'],
                    'email_acheteur' => $validated['email'] ?? null,
                    'nom_tarif' => $tarif->nom,
                    'montant' => 0,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => null,
                    'transaction_id' => 'MANUEL-GRATUIT-'.strtoupper(Str::random(6)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]);
                $ticket->update([
                    'code_unique' => Ticket::genererCodeSecurise(),
                ]);
                $ticket->load('evenement');
                $tickets[] = $ticket;
            }

            $evenement->increment('quota_vendu', $validated['quantite']);
            $tarif->increment('quantite_vendue', $validated['quantite']);

            foreach ($tickets as $ticket) {
                if ($ticket->email_acheteur) {
                    try {
                        $ticket->load('evenement', 'tarif');
                        Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
                        Log::info('Vente manuelle gratuite - Email envoyé à '.$ticket->email_acheteur);
                    } catch (\Exception $e) {
                        Log::error('Vente manuelle gratuite - Erreur email : '.$e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $validated['quantite'] > 1
                    ? "{$validated['quantite']} inscription(s) gratuite(s) enregistrée(s) avec succès."
                    : 'Inscription gratuite enregistrée avec succès.',
                'tickets' => $tickets,
                'total' => 0,
            ]);
        }

        $tarif = Tarif::where('evenement_id', $evenement->id)->findOrFail($validated['tarif_id']);

        // Application du code promo (si fourni)
        $codePromo = null;
        $montantReduction = 0;
        $prixUnitaire = $tarif->prix;

        if (! empty($validated['code_promo'])) {
            $codePromo = CodePromo::validerPour($validated['code_promo'], $evenement, $tarif);
            $montantReduction = $codePromo->calculerReduction($tarif->prix);
            $prixUnitaire = max(0, $tarif->prix - $montantReduction);
            // nb_utilisations incrémenté uniquement à la confirmation (espèces ici, mobile via callback/webhook)
        }

        // Vérification du stock restant du tarif
        if ($tarif->quantite_disponible !== null && $tarif->quantite_disponible - $tarif->quantite_vendue < $validated['quantite']) {
            $restantes = max(0, $tarif->quantite_disponible - $tarif->quantite_vendue);

            return response()->json([
                'errors' => [
                    'quantite' => ["Quantité restante insuffisante pour ce tarif. Il n'en reste que {$restantes}."],
                ],
            ], 422);
        }

        // --- Cas 2 : Paiement en espèces ---
        if ($validated['methode_paiement'] === 'especes') {
            $tickets = [];
            for ($i = 0; $i < $validated['quantite']; $i++) {
                $ticket = Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $tarif->id,
                    'source' => 'vente_manuelle',
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $validated['nom_acheteur'],
                    'telephone_acheteur' => $validated['telephone'],
                    'email_acheteur' => $validated['email'] ?? null,
                    'nom_tarif' => $tarif->nom,
                    'montant' => $prixUnitaire,
                    'montant_reduction' => $montantReduction,
                    'code_promo_utilise' => $codePromo ? $codePromo->code : null,
                    'quantite' => 1,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => 'especes',
                    'type_paiement' => 'especes',
                    'transaction_id' => 'MANUEL-'.strtoupper(Str::random(6)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]);
                $ticket->update([
                    'code_unique' => Ticket::genererCodeSecurise(),
                ]);
                $ticket->load('evenement');
                $tickets[] = $ticket;
            }

            $evenement->increment('quota_vendu', $validated['quantite']);
            $tarif->increment('quantite_vendue', $validated['quantite']);

            // Comptabilise le code promo une fois la vente espèces confirmée
            if ($codePromo) {
                $codePromo->increment('nb_utilisations', 1);
            }

            foreach ($tickets as $ticket) {
                if ($ticket->email_acheteur) {
                    try {
                        $ticket->load('evenement', 'tarif');
                        Mail::to($ticket->email_acheteur)->send(new TicketEmail($ticket));
                        Log::info('Vente manuelle - Email envoyé à '.$ticket->email_acheteur);
                    } catch (\Exception $e) {
                        Log::error('Vente manuelle - Erreur email : '.$e->getMessage());
                    }
                }
            }

            $total = $prixUnitaire * $validated['quantite'];

            return response()->json([
                'success' => true,
                'message' => "{$validated['quantite']} billet(s) enregistré(s) avec succès.",
                'tickets' => $tickets,
                'total' => $total,
            ]);
        }

        // Digital payment: create N tickets as 'en_attente' with shared group ID
        // --- Cas 3 : Paiement mobile (FedaPay) ---
        $montantTotal = $prixUnitaire * $validated['quantite'];
        $groupTransactionId = 'GRP-'.strtoupper(Str::random(16));
        $tickets = [];

        for ($i = 0; $i < $validated['quantite']; $i++) {
            $t = Ticket::create([
                'evenement_id' => $evenement->id,
                'tarif_id' => $tarif->id,
                'source' => 'vente_manuelle',
                'code_unique' => 'TMP',
                'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                'nom_acheteur' => $validated['nom_acheteur'],
                'telephone_acheteur' => $validated['telephone'],
                'email_acheteur' => $validated['email'],
                'nom_tarif' => $tarif->nom,
                'montant' => $prixUnitaire,
                'montant_reduction' => $montantReduction,
                'code_promo_utilise' => $codePromo ? $codePromo->code : null,
                'quantite' => 1,
                'statut_paiement' => 'en_attente',
                'methode_paiement' => 'mobile_money',
                'type_paiement' => 'mobile_money',
                'transaction_id' => $groupTransactionId,
                'utilise' => false,
                'date_achat' => now(),
            ]);
            $t->update([
                'code_unique' => Ticket::genererCodeSecurise(),
            ]);
            $tickets[] = $t;
        }

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
            $tarifs = Tarif::where('evenement_id', $evenement->id)->where('statut', 'actif')->get();
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
