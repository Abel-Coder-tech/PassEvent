<?php

namespace App\Http\Controllers;

use App\Exceptions\PlacesInsuffisantesException;
use App\Jobs\SendTicketEmailJob;
use App\Models\AffiliateEventLink;
use App\Models\CodePromo;
use App\Models\Evenement;
use App\Models\EventWaitlist;
use App\Models\Message;
use App\Models\Tarif;
use App\Models\Ticket;
use App\Services\PaymentMonitoringService;
use App\Support\PerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EvenementPublicController extends Controller
{
    // Durée (en minutes) pendant laquelle une réservation en ligne non payée reste réservée.
    public const DUREE_RESERVATION_MINUTES = 15;

    // Liste publique des événements avec filtres par catégorie, date et recherche
    public function index(Request $request)
    {
        // Récupère les catégories disponibles pour les événements à venir
        $categories = Evenement::withUpcomingDate()
            ->where('statut', 'publié')
            ->whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        $selectedCategorie = $request->input('categorie');
        $selectedDate = $request->input('date');
        $q = $request->input('q');

        $query = Evenement::with('tarifs', 'dates')
            ->withUpcomingDate()
            ->where('statut', 'publié')
            ->orderBy('date_event', 'asc');

        if ($selectedCategorie) {
            $query->where('categorie', $selectedCategorie);
        }

        if ($selectedDate === 'weekend') {
            $query->whereHas('dates', fn ($q) => $q->whereBetween('date_debut', [now()->startOfWeek()->addDays(5), now()->endOfWeek()->addDays(5)]));
        } elseif ($selectedDate === 'mois') {
            $query->whereHas('dates', fn ($q) => $q->whereMonth('date_debut', now()->month)->whereYear('date_debut', now()->year));
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('titre', 'like', "%{$q}%")
                    ->orWhere('categorie', 'like', "%{$q}%")
                    ->orWhere('lieu', 'like', "%{$q}%");
            });
        }

        $evenements = $query->paginate(PerPage::resolve());

        // Événements passés (publiés ou terminés) affichés en bas de page
        $evenementsPasses = Evenement::with('tarifs', 'dates')
            ->whereIn('statut', ['publié', 'terminé'])
            ->where(function ($query) {
                $query->where(function ($withDates) {
                    $withDates->whereHas('dates')
                        ->whereDoesntHave('dates', fn ($dates) => $dates->where('date_debut', '>=', now()));
                })->orWhere(function ($withoutDates) {
                    $withoutDates->whereDoesntHave('dates')
                        ->where('date_event', '<', now());
                });
            })
            ->orderBy('date_event', 'desc')
            ->limit(9)
            ->get();

        return view('evenement-public.index', compact('evenements', 'evenementsPasses', 'categories', 'selectedCategorie', 'selectedDate', 'q'));
    }

    // Affiche la page publique d'un événement avec tarifs et disponibilité
    public function show(Evenement $evenement, Request $request)
    {
        // SEO : redirection 301 de l'ancien format /evenements/{id} vers /evenements/{slug}.
        // On lit le segment d'URL (et non request()->route('evenement') qui contient le modèle lié).
        $segment = (string) request()->segment(2);
        if ($segment !== $evenement->slug) {
            return redirect()->route('evenements.public.show', $evenement)->setStatusCode(301);
        }

        if (! in_array($evenement->statut, ['publié', 'terminé'], true)) {
            abort(404); // Seuls les événements publiés ou passés sont visibles
        }

        $this->capturerAffiliation($evenement, $request);

        $evenement->load('user', 'dates');
        $tarifs = $evenement->tarifs()
            ->where('statut', 'actif')
            ->with('jour')
            ->get()
            ->filter(fn (Tarif $tarif) => $tarif->estEncoreVendable())
            ->values();
        $placesRestantes = $evenement->placesRestantesPourTarif(null);
        $placesRestantesTotales = $evenement->placesRestantesTotales();
        // Détail par jour affiché uniquement pour un événement multijours.
        $placesParJour = $evenement->estMultiJour() ? $evenement->placesParJour() : collect();
        // Un événement payant reste achetable tant qu'au moins un tarif en
        // vente a des places. Pour le gratuit (billet valable tous les jours),
        // c'est la limite du jour le plus contraint qui s'applique.
        $estComplet = $evenement->estComplet() || (! $evenement->gratuit && $tarifs->isEmpty());
        $venteCloturee = $evenement->ventes_fermees; // Fermeture manuelle par l'organisateur
        $derniereDate = $evenement->derniereDate();
        $evenementPasse = $derniereDate && $derniereDate->date_debut->lt(now());

        return view('evenement-public.show', compact('evenement', 'tarifs', 'placesRestantes', 'placesRestantesTotales', 'placesParJour', 'estComplet', 'venteCloturee', 'evenementPasse'));
    }

    // Traite l'achat d'un panier (un ou plusieurs tarifs : jours et packs),
    // gratuit ou payant, avec code promo. Accepte aussi l'ancien format
    // (tarif_id + quantite) et l'inscription seule sur liste d'attente.
    public function achat(Evenement $evenement, Request $request, PaymentMonitoringService $paymentMonitoring)
    {
        if ($evenement->statut !== 'publié') {
            abort(404);
        }

        $derniereDate = $evenement->derniereDate();
        if ($evenement->ventes_fermees || ($derniereDate && $derniereDate->date_debut->isPast())) {
            return back()->with('error', 'La vente est clôturée pour cet événement.'); // Vente expirée
        }

        $validated = $request->validate([
            'nom_acheteur' => 'required|string|max:255',
            'email_acheteur' => 'required|email|max:255',
            'whatsapp_acheteur' => 'required|string|min:10|max:20',
            'code_promo' => 'nullable|string|max:50',
            'quantite' => 'nullable|integer|min:1|max:50',
            'tarif_id' => 'nullable|integer',
            'lignes' => 'nullable|array',
            'lignes.*' => 'nullable|integer|min:0|max:50',
        ], [
            'nom_acheteur.required' => 'Le nom est obligatoire.',
            'email_acheteur.required' => 'L\'email est obligatoire.',
            'email_acheteur.email' => 'Le format de l\'email est invalide.',
            'whatsapp_acheteur.required' => 'Le numéro WhatsApp est obligatoire.',
            'whatsapp_acheteur.min' => 'Le numéro WhatsApp doit contenir au moins 10 caractères.',
            'lignes.*.max' => 'Pas plus de 50 billets par tarif et par commande.',
        ]);

        // Panier demandé : tarif_id => quantité
        if ($evenement->gratuit) {
            $tarifGratuit = $evenement->tarifs()->where('statut', 'actif')->first() ?? Tarif::create([
                'evenement_id' => $evenement->id,
                'nom' => 'Gratuit',
                'prix' => 0,
                'statut' => 'actif',
                'quantite_disponible' => $evenement->capacite ?? 9999,
                'quantite_vendue' => 0,
            ]);
            $demandes = [$tarifGratuit->id => max(1, (int) ($validated['quantite'] ?? 1))];
        } else {
            $demandes = collect($validated['lignes'] ?? [])
                ->map(fn ($quantite) => (int) $quantite)
                ->filter(fn ($quantite) => $quantite > 0)
                ->all();

            if ($demandes === [] && ! empty($validated['tarif_id'])) {
                $demandes = [(int) $validated['tarif_id'] => max(1, (int) ($validated['quantite'] ?? 1))];
            }
        }

        if ($demandes === []) {
            return back()->with('error', 'Choisissez au moins un billet.')->withInput();
        }

        $tarifs = Tarif::query()
            ->where('evenement_id', $evenement->id)
            ->where('statut', 'actif')
            ->whereIn('id', array_keys($demandes))
            ->with('jour')
            ->get()
            ->keyBy('id');

        if ($tarifs->count() !== count($demandes)) {
            return back()->with('error', 'Un des billets choisis n\'est plus disponible.')->withInput();
        }

        foreach ($tarifs as $tarif) {
            if (! $tarif->estEncoreVendable()) {
                return back()->with('error', "« {$tarif->nom} » n'est plus en vente : la date associée est passée.")->withInput();
            }
        }

        // Code promo : s'applique aux billets de son tarif
        $codePromo = null;
        if (! empty($validated['code_promo']) && ! $evenement->gratuit) {
            $codePromo = CodePromo::where('code', strtoupper($validated['code_promo']))
                ->whereHas('tarif', fn ($q) => $q->where('evenement_id', $evenement->id))
                ->first();

            $erreurPromo = match (true) {
                ! $codePromo => 'Ce code promo n\'est pas valide pour cet événement.',
                ! $codePromo->actif => 'Ce code promo a été désactivé.',
                $codePromo->date_expiration && $codePromo->date_expiration < now() => 'Ce code promo a expiré.',
                $codePromo->max_utilisations && $codePromo->nb_utilisations >= $codePromo->max_utilisations => 'Ce code promo a atteint son nombre maximum d\'utilisations.',
                ! $tarifs->has($codePromo->tarif_id) => 'Ce code promo ne s\'applique à aucun des billets choisis.',
                default => null,
            };

            if ($erreurPromo) {
                return back()->with('error', $erreurPromo)->withInput();
            }
        }

        $lignes = $tarifs->map(function (Tarif $tarif) use ($demandes, $codePromo) {
            $reduction = $codePromo && (int) $codePromo->tarif_id === (int) $tarif->id
                ? $codePromo->calculerReduction($tarif->prix)
                : 0;

            return [
                'tarif' => $tarif,
                'quantite' => $demandes[$tarif->id],
                'reduction' => $reduction,
                'unitaire' => max(0, $tarif->prix - $reduction),
                'code' => $reduction > 0 || ($codePromo && (int) $codePromo->tarif_id === (int) $tarif->id) ? $codePromo->code : null,
            ];
        })->values();

        $total = $lignes->sum(fn ($ligne) => $ligne['unitaire'] * $ligne['quantite']);
        $nombreBillets = (int) $lignes->sum('quantite');
        $estGratuit = $evenement->gratuit || $total <= 0;

        $groupTransactionId = 'GRP-'.strtoupper(Str::random(16)); // ID de transaction groupée
        // Les tickets payants sont réservés pour une durée limitée ; s'ils ne sont pas payés,
        // la place est libérée et proposée à la file d'attente.
        $reservationExpireLe = $estGratuit ? null : now()->addMinutes(self::DUREE_RESERVATION_MINUTES);

        // Attribution co-parrainage (mécanisme B) : le visiteur est arrivé via un lien affilié
        // ouvert sur la page de cet événement. On rattache le lien actif aux billets créés.
        $affiliateLinkId = null;
        if ($sessionLinkId = session('affiliate_event_link_'.$evenement->id)) {
            $affiliateLinkId = AffiliateEventLink::whereKey($sessionLinkId)
                ->where('evenement_id', $evenement->id)
                ->where('actif', true)
                ->value('id');
        }

        // Réservation ATOMIQUE de tout le panier : le verrou sérialise les demandes
        // simultanées et les billets sont créés SOUS le verrou (en multijours,
        // l'occupation d'un jour est calculée à partir des billets). Une ligne sans
        // place lève une exception qui annule tout le panier.
        try {
            $tickets = DB::transaction(function () use (
                $evenement, $lignes, $validated, $groupTransactionId, $reservationExpireLe, $affiliateLinkId
            ) {
                $verrou = Evenement::whereKey($evenement->id)->lockForUpdate()->firstOrFail();
                $tickets = [];

                foreach ($lignes as $ligne) {
                    $tarif = Tarif::whereKey($ligne['tarif']->id)->lockForUpdate()->firstOrFail();
                    $verrou->resetCachesCapacite();

                    $places = min($verrou->placesRestantesPourTarif($tarif), $this->stockRestantTarif($tarif));
                    if ($places < $ligne['quantite']) {
                        throw new PlacesInsuffisantesException($tarif, max(0, $places));
                    }

                    for ($i = 0; $i < $ligne['quantite']; $i++) {
                        $tickets[] = $evenement->tickets()->create([
                            'tarif_id' => $tarif->id,
                            'code_unique' => Ticket::genererCodeReservation(),
                            'qr_signature' => hash_hmac('sha256', (string) Str::uuid(), config('app.key') ?? 'fallback'),
                            'email_acheteur' => strtolower($validated['email_acheteur']),
                            'whatsapp_acheteur' => $validated['whatsapp_acheteur'] ?? null,
                            'nom_acheteur' => $validated['nom_acheteur'],
                            'nom_tarif' => $tarif->nom,
                            'montant' => $ligne['unitaire'],
                            'montant_reduction' => $ligne['reduction'],
                            'quantite' => 1,
                            'statut_paiement' => 'en_attente',
                            'transaction_id' => $groupTransactionId,
                            'reservation_expire_le' => $reservationExpireLe,
                            'date_achat' => now(),
                            'code_promo_utilise' => $ligne['code'],
                            'affiliate_event_link_id' => $affiliateLinkId,
                        ]);
                    }

                    $verrou->increment('quota_vendu', $ligne['quantite']);
                }

                return $tickets;
            });
        } catch (PlacesInsuffisantesException $e) {
            $reste = $e->placesRestantes;
            $message = $reste > 0
                ? "Il ne reste que {$reste} place(s) pour « {$e->tarif->nom} ». Ajustez votre panier."
                : "« {$e->tarif->nom} » est complet. Retirez-le de votre panier ou inscrivez-vous sur sa liste d'attente.";

            return back()->with('error', $message)->withInput();
        }

        // Sécurité anti-IDOR : mémorise les tickets de la commande courante en session.
        // Les pages paiement.show / confirmation.show ne sont accessibles que pour ces tickets.
        $sessionTickets = session('paiement_tickets', []);
        session(['paiement_tickets' => array_values(array_unique(array_merge($sessionTickets, collect($tickets)->pluck('id')->all())))]);

        if ($estGratuit) {
            // Commande entièrement gratuite (événement gratuit, jours à 0 F ou promo 100 %) :
            // confirmation immédiate. quota_vendu a déjà été compté à la réservation.
            $freeGroupId = 'GRATUIT-'.$tickets[0]->id;
            foreach ($tickets as $t) {
                $t->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $freeGroupId,
                    'reservation_expire_le' => null,
                ]);
            }
            foreach ($lignes as $ligne) {
                $ligne['tarif']->increment('quantite_vendue', $ligne['quantite']);
            }
            if ($codePromo) {
                $codePromo->increment('nb_utilisations', 1);
            }

            SendTicketEmailJob::dispatchAfterResponse(collect($tickets)->pluck('id')->all(), $tickets[0]->email_acheteur);

            return redirect()->route('confirmation.show', $tickets[0]->id)
                ->with('success', $nombreBillets > 1 ? "{$nombreBillets} places réservées." : 'Votre place est réservée.');
        }

        $paymentMonitoring->record(
            'paiement_initie',
            [
                'transaction_id' => $groupTransactionId,
                'event_id' => $evenement->id,
                'ticket_count' => count($tickets),
                'amount' => round((float) $total, 2),
                'currency' => 'XOF',
                'source' => 'public',
                'payment_status' => 'en_attente',
            ],
            $request,
            $tickets[0]->id,
        );

        return redirect()->route('paiement.show', $tickets[0]->id)
            ->with('success', $nombreBillets > 1 ? "{$nombreBillets} places réservées. Finalisez le paiement." : 'Votre place est réservée. Finalisez le paiement.');
    }

    // Places encore vendables sur le stock propre d'un tarif : stock − vendus −
    // réservations en ligne non expirées (non encore comptées dans quantite_vendue).
    private function stockRestantTarif(Tarif $tarif): int
    {
        if ($tarif->quantite_disponible === null) {
            return PHP_INT_MAX;
        }

        $reservees = (int) Ticket::query()
            ->where('tarif_id', $tarif->id)
            ->where('statut_paiement', 'en_attente')
            ->whereNull('expire_at')
            ->where(fn ($q) => $q->whereNull('annule')->orWhere('annule', false))
            ->where('reservation_expire_le', '>', now())
            ->sum('quantite');

        return max(0, (int) $tarif->quantite_disponible - (int) $tarif->quantite_vendue - $reservees);
    }

    // Liste d'attente d'un tarif complet (fenêtre de la page publique) : une
    // demande par email et par tarif, l'organisateur est prévenu à chaque fois.
    public function inscrireListeAttente(Request $request, Evenement $evenement)
    {
        abort_if($evenement->statut !== 'publié', 404);

        $validated = $request->validate([
            'tarif_id' => 'required|integer',
            'nom' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'nullable|string|max:30',
            'quantite' => 'required|integer|min:1|max:100000',
        ], [
            'email.required' => 'Indiquez votre email.',
            'email.email' => 'Le format de l\'email est invalide.',
            'quantite.required' => 'Indiquez le nombre de places souhaitées.',
            'quantite.min' => 'Demandez au moins une place.',
        ]);

        $tarif = Tarif::where('evenement_id', $evenement->id)
            ->where('statut', 'actif')
            ->find($validated['tarif_id']);
        if (! $tarif) {
            throw ValidationException::withMessages(['tarif_id' => 'Ce billet n\'est pas disponible.']);
        }

        $email = strtolower(trim($validated['email']));
        $demande = EventWaitlist::where('evenement_id', $evenement->id)
            ->where('tarif_id', $tarif->id)
            ->where('email_acheteur', $email)
            ->where('statut', 'en_attente')
            ->first();

        $donnees = [
            'nom_acheteur' => $validated['nom'] ?? null,
            'telephone_acheteur' => $validated['whatsapp'] ?? null,
            'quantite' => (int) $validated['quantite'],
            'montant_unitaire' => (int) round($tarif->prix),
        ];

        if ($demande) {
            $demande->update($donnees);
        } else {
            $demande = EventWaitlist::create($donnees + [
                'evenement_id' => $evenement->id,
                'tarif_id' => $tarif->id,
                'email_acheteur' => $email,
                'montant_reduction' => 0,
                'statut' => 'en_attente',
            ]);
        }

        $this->notifierOrganisateurListeAttente($evenement, $tarif, $demande);

        return response()->json([
            'ok' => true,
            'message' => 'Vous êtes inscrit(e). Nous vous préviendrons par email dès que des places seront disponibles.',
        ]);
    }

    // Notification de l'organisateur à chaque demande : tableau de bord + email.
    private function notifierOrganisateurListeAttente(Evenement $evenement, Tarif $tarif, EventWaitlist $demande): void
    {
        $organisateur = $evenement->user;
        if (! $organisateur) {
            return;
        }

        $totaux = EventWaitlist::where('tarif_id', $tarif->id)->where('statut', 'en_attente');
        $personnes = (clone $totaux)->count();
        $places = (int) (clone $totaux)->sum('quantite');
        $qui = trim(($demande->nom_acheteur ? $demande->nom_acheteur.' · ' : '').$demande->email_acheteur
            .($demande->telephone_acheteur ? ' · '.$demande->telephone_acheteur : ''));
        $texte = "Nouvelle demande sur la liste d'attente de « {$tarif->nom} » ({$evenement->titre}) : "
            ."{$demande->quantite} place(s) demandée(s) par {$qui}.\n\n"
            ."Au total : {$places} place(s) demandée(s) par {$personnes} personne(s).\n"
            ."Ajoutez des places à ce tarif puis cliquez sur « Envoyer la notification » dans la gestion des tarifs : "
            .route('admin.tarifs.index', $evenement->id);

        try {
            Message::create([
                'user_id' => $organisateur->id,
                'evenement_id' => $evenement->id,
                'nom_complet' => $demande->nom_acheteur ?: 'Liste d\'attente',
                'email' => $demande->email_acheteur,
                'telephone' => $demande->telephone_acheteur,
                'objet' => "Liste d'attente : {$tarif->nom} — {$evenement->titre}",
                'message' => $texte,
            ]);

            Mail::raw($texte, function ($m) use ($organisateur, $evenement, $tarif) {
                $m->to($organisateur->email, $organisateur->nom)
                    ->subject("[PaxEvent] Liste d'attente : {$tarif->nom} — {$evenement->titre}");
            });
        } catch (\Throwable $e) {
            // L'inscription reste valable même si la notification échoue.
            Log::warning('Notification liste d\'attente impossible', ['evenement_id' => $evenement->id, 'erreur' => $e->getMessage()]);
        }
    }

    // Permet à un utilisateur de contacter l'organisateur d'un événement
    public function contacterOrganisateur(Evenement $evenement, Request $request)
    {
        if ($evenement->statut !== 'publié') {
            abort(404); // Événement non publié
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:2000',
        ]);

        $organisateur = $evenement->user;

        Message::create([
            'user_id' => $organisateur->id,
            'evenement_id' => $evenement->id,
            'nom_complet' => $validated['nom'],
            'email' => $validated['email'],
            'objet' => "Question sur {$evenement->titre}",
            'message' => $validated['message'],
        ]);

        Mail::raw(
            "Message de {$validated['nom']} ({$validated['email']})\n\n".
            "Evenement : {$evenement->titre}\n\n".
            "Message :\n{$validated['message']}",
            function ($m) use ($organisateur, $validated, $evenement) {
                $m->to($organisateur->email, $organisateur->nom)
                    ->replyTo($validated['email'], $validated['nom'])
                    ->subject("[PaxEvent] Question sur {$evenement->titre}");
            }
        );

        return back()->with('success', 'Votre message a été envoyé à l\'organisateur. Vous recevrez une réponse sous peu.');
    }

    // Capture un éventuel lien de co-parrainage (?ref=CODE) sur la page de l'événement.
    // Mémorise le lien en session pour l'attribution à l'achat et compte un clic unique.
    private function capturerAffiliation(Evenement $evenement, Request $request): void
    {
        $code = $request->query('ref');
        if (! $code) {
            return;
        }

        $lien = AffiliateEventLink::where('code', $code)
            ->where('evenement_id', $evenement->id)
            ->where('actif', true)
            ->first();

        if (! $lien) {
            return;
        }

        session(['affiliate_event_link_'.$evenement->id => $lien->id]);

        // Un seul clic compté par session et par lien (évite l'inflation au rechargement).
        $flag = 'affiliate_clic_'.$lien->id;
        if (! session()->has($flag)) {
            $lien->increment('clics');
            session([$flag => true]);
        }
    }

    public function redirectAffilie(string $code)
    {
        $lien = \App\Models\AffiliateEventLink::with('evenement.dates')->where('code', $code)->firstOrFail();
        $evenement = $lien->evenement;
        if (! $evenement || ! in_array($evenement->statut, ['publié', 'terminé'], true)) {
            abort(404);
        }

        $targetUrl = route('evenements.public.show', $evenement) . '?ref=' . $lien->code;
        $title = $evenement->titre . ' — PaxEvent';
        $datesPartage = $evenement->dates->isNotEmpty()
            ? $evenement->dates->map(fn ($date) => $date->date_debut->isoFormat('D MMM YYYY'))->unique()->implode(', ')
            : $evenement->date_event?->isoFormat('D MMM YYYY');
        $description = \Illuminate\Support\Str::limit(implode(' · ', array_filter([
            $evenement->categorie ? ucfirst($evenement->categorie) : null,
            $datesPartage,
            $evenement->lieu,
            $evenement->description ? \Illuminate\Support\Str::squish(strip_tags($evenement->description)) : null,
        ])), 160) ?: ('Découvrir ' . $evenement->titre . ' sur PaxEvent.');
        $image = $evenement->image ? asset('storage/' . $evenement->image) : asset('images/og-image.png');
        $canonical = route('evenements.public.show', $evenement);

        $e = fn ($v) => e((string) $v);
        $html = '<!doctype html><html lang="fr"><head>'
            . '<meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . $e($title) . '</title>'
            . '<meta name="description" content="' . $e($description) . '">'
            . '<link rel="canonical" href="' . $e($canonical) . '">'
            . '<meta property="og:type" content="website">'
            . '<meta property="og:site_name" content="PaxEvent">'
            . '<meta property="og:title" content="' . $e($evenement->titre) . '">'
            . '<meta property="og:description" content="' . $e($description) . '">'
            . '<meta property="og:url" content="' . $e($canonical) . '">'
            . '<meta property="og:image" content="' . $e($image) . '">'
            . '<meta property="og:image:secure_url" content="' . $e($image) . '">'
            . '<meta property="og:image:alt" content="' . $e($evenement->titre) . '">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '<meta name="twitter:title" content="' . $e($evenement->titre) . '">'
            . '<meta name="twitter:description" content="' . $e($description) . '">'
            . '<meta name="twitter:image" content="' . $e($image) . '">'
            . '<meta http-equiv="refresh" content="0;url=' . $e($targetUrl) . '">'
            . '</head><body>'
            . '<p>Redirection vers <a href="' . $e($targetUrl) . '">' . $e($evenement->titre) . '</a>…</p>'
            . '<script>location.replace(' . json_encode($targetUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . ');</script>'
            . '</body></html>';

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
