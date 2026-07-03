<?php

namespace App\Http\Controllers;

use App\Mail\TicketEmail;
use App\Models\Evenement;
use App\Models\Tarif;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VenteManuelleController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        $evenements = Evenement::where('user_id', $user->id)
            ->whereIn('statut', ['publié', 'brouillon'])
            ->orderBy('date_event', 'asc')
            ->get();

        $debutJour = now()->startOfDay()->utc();
        $finJour = now()->endOfDay()->utc();
        $ventesJour = Ticket::whereHas('evenement', fn($q) => $q->where('user_id', $user->id))
            ->where('transaction_id', 'like', 'MANUEL-%')
            ->whereBetween('date_achat', [$debutJour, $finJour])
            ->with('evenement')
            ->latest('date_achat')
            ->get();

        $totalVentesJour = $ventesJour->count();
        $montantVentesJour = $ventesJour->sum('montant');

        return view('ventes-manuelles.create', compact(
            'evenements',
            'ventesJour',
            'totalVentesJour',
            'montantVentesJour',
        ));
    }

    public function store(Request $request)
    {
        $evenement = Evenement::findOrFail($request->evenement_id);

        $rules = [
            'evenement_id' => 'required|exists:evenement,id',
            'nom_acheteur' => 'required|string|max:255',
            'telephone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'quantite' => 'required|integer|min:1|max:20',
        ];
        $messages = [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'nom_acheteur.required' => 'Le nom de l\'acheteur est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité doit être d\'au moins 1.',
            'quantite.max' => 'La quantité ne doit pas dépasser 20.',
        ];

        if (!$evenement->gratuit) {
            $rules['tarif_id'] = 'required|exists:tarifs,id';
            $rules['methode_paiement'] = 'required|in:especes,mobile';

            $messages['tarif_id.required'] = 'Veuillez sélectionner un tarif.';

            if (Auth::user()->type === 'universitaire') {
                $rules['categorie'] = 'required|in:etudiant,externe';
            }

            if ($request->methode_paiement !== 'especes') {
                $rules['email'] = 'required|email|max:255';
                $messages['email.required'] = 'L\'email est obligatoire pour le paiement mobile.';
            }
        }

        $validated = $request->validate($rules, $messages);

        if ($evenement->gratuit) {
            $tarif = $evenement->tarifs()->where('statut', 'actif')->first();
            if (!$tarif) {
                $tarif = Tarif::create([
                    'evenement_id' => $evenement->id,
                    'categorie' => 'externe',
                    'type' => 'normal',
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
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $validated['nom_acheteur'],
                    'telephone_acheteur' => $validated['telephone'],
                    'email_acheteur' => $validated['email'] ?? null,
                    'categorie' => $tarif->categorie,
                    'type' => $tarif->type,
                    'montant' => 0,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => null,
                    'transaction_id' => 'MANUEL-GRATUIT-' . strtoupper(Str::random(6)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]);
                $ticket->update([
                    'code_unique' => 'PASS' . $evenement->user_id . '26' . $ticket->id,
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
                        Log::info('Vente manuelle gratuite - Email envoyé à ' . $ticket->email_acheteur);
                    } catch (\Exception $e) {
                        Log::error('Vente manuelle gratuite - Erreur email : ' . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $validated['quantite'] > 1
                    ? "{$validated['quantite']} inscription(s) gratuite(s) enregistrée(s) avec succès."
                    : "Inscription gratuite enregistrée avec succès.",
                'tickets' => $tickets,
                'total' => 0,
            ]);
        }

        $tarif = Tarif::where('evenement_id', $evenement->id)->findOrFail($validated['tarif_id']);

        if ($validated['methode_paiement'] === 'especes') {
            $tickets = [];
            for ($i = 0; $i < $validated['quantite']; $i++) {
                $ticket = Ticket::create([
                    'evenement_id' => $evenement->id,
                    'tarif_id' => $tarif->id,
                    'code_unique' => 'TMP',
                    'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
                    'nom_acheteur' => $validated['nom_acheteur'],
                    'telephone_acheteur' => $validated['telephone'],
                    'email_acheteur' => $validated['email'] ?? null,
                    'categorie' => $validated['categorie'] ?? $tarif->categorie,
                    'type' => $tarif->type,
                    'montant' => $tarif->prix,
                    'statut_paiement' => 'payé',
                    'methode_paiement' => 'especes',
                    'transaction_id' => 'MANUEL-' . strtoupper(Str::random(6)),
                    'utilise' => false,
                    'date_achat' => now(),
                ]);
                $ticket->update([
                    'code_unique' => 'PASS' . $evenement->user_id . '26' . $ticket->id,
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
                        Log::info('Vente manuelle - Email envoyé à ' . $ticket->email_acheteur);
                    } catch (\Exception $e) {
                        Log::error('Vente manuelle - Erreur email : ' . $e->getMessage());
                    }
                }
            }

            $total = $tarif->prix * $validated['quantite'];

            return response()->json([
                'success' => true,
                'message' => "{$validated['quantite']} billet(s) enregistré(s) avec succès.",
                'tickets' => $tickets,
                'total' => $total,
            ]);
        }

        // Digital payment: create ticket as 'en_attente' and redirect to FedaPay
        $montantTotal = $tarif->prix * $validated['quantite'];

        $ticket = Ticket::create([
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'code_unique' => 'TMP',
            'qr_signature' => hash_hmac('sha256', Str::random(32), config('app.key') ?? 'fallback'),
            'nom_acheteur' => $validated['nom_acheteur'],
            'telephone_acheteur' => $validated['telephone'],
            'email_acheteur' => $validated['email'],
            'categorie' => $validated['categorie'] ?? $tarif->categorie,
            'type' => $tarif->type,
            'montant' => $montantTotal,
            'statut_paiement' => 'en_attente',
            'methode_paiement' => 'mobile_money',
            'transaction_id' => 'PENDING-FEDAPAY-' . strtoupper(Str::random(8)),
            'utilise' => false,
            'date_achat' => now(),
        ]);
        $ticket->update([
            'code_unique' => 'PASS' . $evenement->user_id . '26' . $ticket->id,
        ]);

        $evenement->increment('quota_vendu', $validated['quantite']);
        $tarif->increment('quantite_vendue', $validated['quantite']);

        return response()->json([
            'success' => true,
            'redirect' => route('paiement.show', $ticket->id),
        ]);
    }

    public function getTarifs(Request $request)
    {
        $rules = [
            'evenement_id' => 'required|exists:evenement,id',
        ];

        if (Auth::user()->type === 'universitaire') {
            $rules['categorie'] = 'required|in:etudiant,externe';
        }

        $request->validate($rules);

        $evenement = Evenement::where('id', $request->evenement_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tarifs = collect();

        if (!$evenement->gratuit) {
            $query = Tarif::where('evenement_id', $evenement->id)->where('statut', 'actif');

            if (Auth::user()->type === 'universitaire') {
                $query->where('categorie', $request->categorie);
            }

            $tarifs = $query->get();
        }

        return response()->json(['tarifs' => $tarifs, 'gratuit' => $evenement->gratuit]);
    }
}
