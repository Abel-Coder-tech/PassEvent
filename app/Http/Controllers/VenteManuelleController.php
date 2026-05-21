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

        $ventesJour = Ticket::whereHas('evenement', fn($q) => $q->where('user_id', $user->id))
            ->where('methode_paiement', 'manuel')
            ->whereDate('date_achat', now()->toDateString())
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
        $validated = $request->validate([
            'evenement_id' => 'required|exists:evenement,id',
            'tarif_id' => 'required|exists:tarifs,id',
            'nom_acheteur' => 'required|string|max:255',
            'telephone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'categorie' => 'required|in:etudiant,externe',
            'quantite' => 'required|integer|min:1|max:20',
            'methode_paiement' => 'required|in:especes,mtn,moov,movimoney,celtiis',
        ], [
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'tarif_id.required' => 'Veuillez sélectionner un tarif.',
            'nom_acheteur.required' => 'Le nom de l\'acheteur est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité doit être d\'au moins 1.',
            'quantite.max' => 'La quantité ne doit pas dépasser 20.',
        ]);

        $evenement = Evenement::findOrFail($validated['evenement_id']);
        $tarif = Tarif::where('evenement_id', $evenement->id)->findOrFail($validated['tarif_id']);

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
                'categorie' => $validated['categorie'],
                'type' => $tarif->type,
                'montant' => $tarif->prix,
                'statut_paiement' => 'payé',
                'methode_paiement' => $validated['methode_paiement'],
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

    public function getTarifs(Request $request)
    {
        $request->validate([
            'evenement_id' => 'required|exists:evenement,id',
            'categorie' => 'required|in:etudiant,externe',
        ]);

        $evenement = Evenement::where('id', $request->evenement_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tarifs = Tarif::where('evenement_id', $evenement->id)
            ->where('categorie', $request->categorie)
            ->where('statut', 'actif')
            ->get();

        return response()->json(['tarifs' => $tarifs]);
    }
}
