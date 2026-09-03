<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use App\Models\Evenement;
use App\Models\DemandeModificationTarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarifController extends Controller
{
    // Liste les tarifs d'un événement
    public function index(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        $tarifs = $evenement->tarifs;
        return view('tarifs.index', compact('evenement', 'tarifs'));
    }

    // Affiche le formulaire de création d'un tarif
    public function create(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        return view('tarifs.create', compact('evenement'));
    }

    // Crée un nouveau tarif pour un événement
    public function store(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $nbTarifs = $evenement->tarifs()->count();
        if ($nbTarifs >= 8) {
            return back()->withErrors(['error' => 'Maximum 8 tarifs par événement.']);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prix' => 'required|numeric|min:0',
            'quantite_disponible' => 'nullable|integer|min:1',
        ], [
            'nom.required' => 'Le nom du tarif est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'quantite_disponible.integer' => 'La quantité doit être un nombre entier.',
            'quantite_disponible.min' => 'La quantité doit être d\'au moins 1.',
        ]);

        $validated['evenement_id'] = $evenement->id;
        $validated['quantite_vendue'] = 0;
        $validated['statut'] = 'actif';
        Tarif::create($validated);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif ajouté avec succès.');
    }

    // Affiche le formulaire d'édition d'un tarif
    public function edit(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        return view('tarifs.edit', compact('evenement', 'tarif'));
    }

    // Met à jour un tarif existant. Le prix est libre s'il n'y a aucune vente,
    // sinon il faut passer par une demande d'approbation (PaxEvent).
    public function update(Request $request, Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prix' => 'nullable|numeric|min:0',
            'quantite_disponible' => 'nullable|integer|min:1',
        ], [
            'nom.required' => 'Le nom du tarif est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'quantite_disponible.integer' => 'La quantité doit être un nombre entier.',
            'quantite_disponible.min' => 'La quantité doit être d\'au moins 1.',
        ]);

        $aVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;

        // Si des billets ont déjà été vendus, le prix ne peut plus être modifié
        // directement : passage par une demande d'approbation.
        if ($aVendu && isset($validated['prix']) && (float) $validated['prix'] != (float) $tarif->prix) {
            return back()->withErrors([
                'prix' => 'Des billets ont déjà été vendus : le prix ne peut pas être modifié directement. Utilisez le bouton « Demander une modification » qui sera validé par PaxEvent.',
            ])->withInput();
        }

        if (!$aVendu && isset($validated['prix'])) {
            $validated['prix'] = $validated['prix'];
        } else {
            $validated['prix'] = $tarif->prix;
        }

        $tarif->update($validated);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif modifié avec succès.');
    }

    // Soumet une demande de modification de prix à PaxEvent (utilisé quand des
    // billets ont déjà été vendus).
    public function demanderModificationPrix(Request $request, Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        abort_if($tarif->evenement_id !== $evenement->id, 403);

        $aVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;
        abort_if(!$aVendu, 403, 'Aucune vente : modifiez directement le prix.');

        $validated = $request->validate([
            'nouveau_prix' => 'required|numeric|min:0',
        ], [
            'nouveau_prix.required' => 'Le nouveau prix est obligatoire.',
            'nouveau_prix.numeric' => 'Le prix doit être un nombre valide.',
            'nouveau_prix.min' => 'Le prix ne peut pas être négatif.',
        ]);

        // Évite les doublons : une demande en attente pour ce tarif existe déjà.
        $existe = $tarif->demandesModification()
            ->where('statut', 'en_attente')
            ->exists();

        if ($existe) {
            return back()->with('error', 'Une demande de modification est déjà en attente pour ce tarif.');
        }

        DemandeModificationTarif::create([
            'evenement_id' => $evenement->id,
            'tarif_id' => $tarif->id,
            'user_id' => Auth::id(),
            'ancien_prix' => $tarif->prix,
            'nouveau_prix' => $validated['nouveau_prix'],
            'statut' => 'en_attente',
        ]);

        return back()->with('success', 'Votre demande de modification de prix a été envoyée à PaxEvent.');
    }

    // Supprime un tarif
    public function destroy(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriétaire
        abort_if($tarif->evenement_id !== $evenement->id, 403); // Vérification d'appartenance
        Tarif::destroy($tarif->id);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif supprimé avec succès.');
    }
}
