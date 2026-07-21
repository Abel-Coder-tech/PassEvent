<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Message;
use App\Models\Tarif;
use App\Models\CodePromo;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EvenementPublicController extends Controller
{
    // Liste publique des événements avec filtres par catégorie, date et recherche
    public function index(Request $request)
    {
        // Récupère les catégories disponibles pour les événements à venir
        $categories = Evenement::where('statut', 'publié')
            ->where('date_event', '>=', now())
            ->whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        $selectedCategorie = $request->input('categorie');
        $selectedDate = $request->input('date');
        $q = $request->input('q');

        $query = Evenement::where('statut', 'publié')
            ->where('date_event', '>=', now());

        if ($selectedCategorie) {
            $query->where('categorie', $selectedCategorie);
        }

        if ($selectedDate === 'weekend') {
            $query->whereBetween('date_event', [now()->startOfWeek()->addDays(5), now()->endOfWeek()->addDays(5)]);
        } elseif ($selectedDate === 'mois') {
            $query->whereMonth('date_event', now()->month)
                  ->whereYear('date_event', now()->year);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('titre', 'like', "%{$q}%")
                    ->orWhere('categorie', 'like', "%{$q}%")
                    ->orWhere('lieu', 'like', "%{$q}%");
            });
        }

        $evenements = $query->with('tarifs')->orderBy('date_event', 'asc')->paginate(12);

        return view('evenement-public.index', compact('evenements', 'categories', 'selectedCategorie', 'selectedDate', 'q'));
    }

    // Affiche la page publique d'un événement avec tarifs et disponibilité
    public function show(Evenement $evenement)
    {
        if ($evenement->statut !== 'publié') {
            abort(404); // Seuls les événements publiés sont visibles
        }

        $evenement->load('user');
        $tarifs = $evenement->tarifs()->where('statut', 'actif')->get();
        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
        $estComplet = $placesRestantes <= 0; // Vérifie la disponibilité
        $venteCloturee = $evenement->date_fin_vente && $evenement->date_fin_vente->isPast(); // Date limite dépassée
        $evenementPasse = $evenement->date_event->isPast();
        $estUniversitaire = $evenement->user->type === 'universitaire'; // Active les tarifs étudiants

        return view('evenement-public.show', compact('evenement', 'tarifs', 'placesRestantes', 'estComplet', 'venteCloturee', 'evenementPasse', 'estUniversitaire'));
    }

    // Traite l'achat de tickets (gratuits ou payants) avec codes promo
    public function achat(Evenement $evenement, Request $request)
    {
        if ($evenement->statut !== 'publié') {
            abort(404);
        }

        if (($evenement->date_fin_vente && $evenement->date_fin_vente->isPast()) || $evenement->date_event->isPast()) {
            return back()->with('error', 'La vente est cloturee pour cet evenement.'); // Vente expirée
        }

        $estGratuit = $evenement->gratuit || $request->boolean('gratuit');

        $rules = [
            'nom_acheteur' => 'required|string|max:255',
            'email_acheteur' => 'required|email|max:255',
            'code_promo' => 'nullable|string|max:50',
            'quantite' => 'nullable|integer|min:1|max:10',
        ];

        $messages = [
            'nom_acheteur.required' => 'Le nom est obligatoire.',
            'email_acheteur.required' => 'L\'email est obligatoire.',
            'email_acheteur.email' => 'Le format de l\'email est invalide.',
        ];

        if ($estGratuit) {
            $rules['telephone_acheteur'] = 'nullable|string|max:20';
        } else {
            $rules['telephone_acheteur'] = 'required|string|min:10|max:20';
            $rules['tarif_id'] = 'required|exists:tarifs,id';
            $messages['telephone_acheteur.required'] = 'Le numéro de téléphone est obligatoire.';
            $messages['telephone_acheteur.min'] = 'Le numéro doit contenir au moins 10 caractères.';
            $messages['tarif_id.required'] = 'Le type de billet est obligatoire.';
        }

        $validated = $request->validate($rules, $messages);

        if ($estGratuit) {
            $tarif = $evenement->tarifs()->where('statut', 'actif')->first();
            if (!$tarif) {
                $tarif = Tarif::create([
                    'evenement_id' => $evenement->id,
                    'categorie' => 'externe',
                    'type' => 'normal',
                    'prix' => 0,
                    'statut' => 'actif',
                    'quantite_disponible' => $evenement->capacite ?? 9999,
                    'quantite_vendue' => 0,
                ]);
            }
        } else {
            $tarif = Tarif::query()
                ->where('id', $validated['tarif_id'])
                ->where('evenement_id', $evenement->id)
                ->where('statut', 'actif')
                ->firstOrFail();
        }

        // Vérification de la disponibilité des places
        $quantite = max(1, min(10, (int) ($validated['quantite'] ?? 1))); // Max 10 tickets par achat

        $placesRestantes = $evenement->capacite - $evenement->quota_vendu;
        if ($placesRestantes < $quantite) {
            $placesRestantes = max(0, $placesRestantes);
            return back()->with('error', "Il ne reste que {$placesRestantes} place(s) disponible(s) pour cet événement.")->withInput();
        }

        $codePromoUtilise = null;
        $montantReduction = 0;
        $montantUnitaire = $tarif->prix;

        // Validation et application du code promo
        if (!empty($validated['code_promo'])) {
            $codePromo = CodePromo::where('code', strtoupper($validated['code_promo']))
                ->whereHas('tarif', fn($q) => $q->where('evenement_id', $evenement->id))
                ->first();

            if (!$codePromo) {
                return back()->with('error', 'Ce code promo n\'est pas valide pour cet événement.')->withInput();
            }

            if (!$codePromo->actif) {
                return back()->with('error', 'Ce code promo a été désactivé.')->withInput();
            }

            if ($codePromo->date_expiration && $codePromo->date_expiration < now()) {
                return back()->with('error', 'Ce code promo a expiré.')->withInput();
            }

            if ($codePromo->max_utilisations && $codePromo->nb_utilisations >= $codePromo->max_utilisations) {
                return back()->with('error', 'Ce code promo a atteint son nombre maximum d\'utilisations.')->withInput();
            }

            if ($codePromo->tarif_id !== $tarif->id) {
                return back()->with('error', 'Ce code promo n\'est pas compatible avec le tarif sélectionné.')->withInput();
            }

            $codePromoUtilise = $codePromo->code;
            $montantReduction = $codePromo->calculerReduction($tarif->prix);
            $montantUnitaire = $tarif->prix - $montantReduction;
        }

        $groupTransactionId = 'GRP-' . strtoupper(\Illuminate\Support\Str::random(16)); // ID de transaction groupée
        $tickets = [];

        // Création de N tickets avec un ID de transaction partagé
        for ($i = 0; $i < $quantite; $i++) {
            $t = $evenement->tickets()->create([
                'tarif_id' => $tarif->id,
                'code_unique' => 'TMP',
                'qr_signature' => hash_hmac('sha256', (string) \Illuminate\Support\Str::uuid(), config('app.key') ?? 'fallback'),
                'email_acheteur' => strtolower($validated['email_acheteur']),
                'telephone_acheteur' => $validated['telephone_acheteur'] ?? null,
                'nom_acheteur' => $validated['nom_acheteur'],
                'categorie' => $tarif->categorie,
                'type' => $tarif->type,
                'montant' => $montantUnitaire,
                'montant_reduction' => $montantReduction,
                'quantite' => 1,
                'statut_paiement' => 'en_attente',
                'transaction_id' => $groupTransactionId,
                'date_achat' => now(),
                'code_promo_utilise' => $codePromoUtilise,
            ]);

            $t->update([
                'code_unique' => 'PASS' . $evenement->user_id . '26' . $t->id, // Code unique basé sur l'ID du ticket
            ]);

            $tickets[] = $t;
        }

        if ($codePromoUtilise) {
            $codePromo->increment('nb_utilisations', 1);
        }

        if ($evenement->gratuit) { // Traitement spécial pour les événements gratuits
            $freeGroupId = 'GRATUIT-' . $tickets[0]->id;
            foreach ($tickets as $t) {
                $t->update([
                    'statut_paiement' => 'payé',
                    'transaction_id' => $freeGroupId,
                ]);
            }
            $evenement->increment('quota_vendu', $quantite);
            $tarif->increment('quantite_vendue', $quantite);
            try {
                Mail::to($tickets[0]->email_acheteur)->send(new \App\Mail\TicketEmail($tickets));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Email gratuit non envoye : ' . $e->getMessage());
            }
            return redirect()->route('confirmation.show', $tickets[0]->id)
                ->with('success', $quantite > 1 ? "{$quantite} places reservées." : 'Votre place est reservée.');
        }

        return redirect()->route('paiement.show', $tickets[0]->id)
            ->with('success', $quantite > 1 ? "{$quantite} places reservées. Finalisez le paiement." : 'Votre place est reservée. Finalisez le paiement.');
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
            "Message de {$validated['nom']} ({$validated['email']})\n\n" .
            "Evenement : {$evenement->titre}\n\n" .
            "Message :\n{$validated['message']}",
            function ($m) use ($organisateur, $validated, $evenement) {
                $m->to($organisateur->email, $organisateur->nom)
                  ->replyTo($validated['email'], $validated['nom'])
                  ->subject("[PaxEvent] Question sur {$evenement->titre}");
            }
        );

        return back()->with('success', 'Votre message a été envoyé à l\'organisateur. Vous recevrez une réponse sous peu.');
    }
}
