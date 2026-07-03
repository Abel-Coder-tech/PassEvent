<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\CodePromo;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EvenementPublicController extends Controller
{
    public function index(Request $request)
    {
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

    public function show(Evenement $evenement)
    {
        if ($evenement->statut !== 'publié') {
            abort(404);
        }

        $evenement->load('user');
        $tarifs = $evenement->tarifs()->where('statut', 'actif')->get();
        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
        $estComplet = $placesRestantes <= 0;
        $venteCloturee = $evenement->date_fin_vente && $evenement->date_fin_vente->isPast();
        $evenementPasse = $evenement->date_event->isPast();
        $estUniversitaire = $evenement->user->type === 'universitaire';

        return view('evenement-public.show', compact('evenement', 'tarifs', 'placesRestantes', 'estComplet', 'venteCloturee', 'evenementPasse', 'estUniversitaire'));
    }

    public function achat(Evenement $evenement, Request $request)
    {
        if ($evenement->statut !== 'publié') {
            abort(404);
        }

        if (($evenement->date_fin_vente && $evenement->date_fin_vente->isPast()) || $evenement->date_event->isPast()) {
            return back()->with('error', 'La vente est cloturee pour cet evenement.');
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

        $quantite = max(1, min(10, (int) ($validated['quantite'] ?? 1)));

        $placesRestantes = $evenement->capacite - $evenement->quota_vendu;
        if ($placesRestantes < $quantite) {
            $placesRestantes = max(0, $placesRestantes);
            return back()->with('error', "Il ne reste que {$placesRestantes} place(s) disponible(s) pour cet événement.")->withInput();
        }

        $codePromoUtilise = null;
        $montantReduction = 0;
        $montantUnitaire = $tarif->prix;

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

        $groupTransactionId = 'GRP-' . strtoupper(\Illuminate\Support\Str::random(16));
        $tickets = [];

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
                'code_unique' => 'PASS' . $evenement->user_id . '26' . $t->id,
            ]);

            $tickets[] = $t;
        }

        if ($codePromoUtilise) {
            $codePromo->increment('nb_utilisations', 1);
        }

        if ($evenement->gratuit) {
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

    public function contacterOrganisateur(Evenement $evenement, Request $request)
    {
        if ($evenement->statut !== 'publié') {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:2000',
        ]);

        $organisateur = $evenement->user;

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

        return back()->with('success', 'Votre message a été envoyé a l\'organisateur. Vous recevrez une reponse sous peu.');
    }
}
