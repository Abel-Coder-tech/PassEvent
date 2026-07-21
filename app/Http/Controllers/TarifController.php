<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use App\Models\Evenement;
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
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        $validated = $request->validate([
            'categorie' => 'required|in:etudiant,externe',
            'type' => 'required|in:normal,vip',
            'prix' => 'required|numeric|min:0',
            'quantite_disponible' => 'nullable|integer|min:1',
        ], [
            'categorie.required' => 'La catégorie est obligatoire.',
            'categorie.in' => 'La catégorie doit être Étudiant ou Externe.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être Normal ou VIP.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'quantite_disponible.integer' => 'La quantité doit être un nombre entier.',
            'quantite_disponible.min' => 'La quantité doit être d\'au moins 1.',
        ]);

        $validated['evenement_id'] = $evenement->id;
        Tarif::create($validated);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif ajouté avec succès.');
    }

    // Affiche le formulaire d'édition d'un tarif
    public function edit(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        return view('tarifs.edit', compact('evenement', 'tarif'));
    }

    // Met à jour un tarif existant
    public function update(Request $request, Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété
        $validated = $request->validate([
            'categorie' => 'required|in:etudiant,externe',
            'type' => 'required|in:normal,vip',
            'prix' => 'required|numeric|min:0',
            'quantite_disponible' => 'nullable|integer|min:1',
        ], [
            'categorie.required' => 'La catégorie est obligatoire.',
            'categorie.in' => 'La catégorie doit être Étudiant ou Externe.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être Normal ou VIP.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix ne peut pas être négatif.',
            'quantite_disponible.integer' => 'La quantité doit être un nombre entier.',
            'quantite_disponible.min' => 'La quantité doit être d\'au moins 1.',
        ]);

        $tarif->update($validated);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif modifié avec succès.');
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
