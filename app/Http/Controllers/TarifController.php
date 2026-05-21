<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarifController extends Controller
{
    public function index(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        $tarifs = $evenement->tarifs;
        return view('tarifs.index', compact('evenement', 'tarifs'));
    }

    public function create(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        return view('tarifs.create', compact('evenement'));
    }

    public function store(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
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

    public function edit(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        return view('tarifs.edit', compact('evenement', 'tarif'));
    }

    public function update(Request $request, Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
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

    public function destroy(Evenement $evenement, Tarif $tarif)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        abort_if($tarif->evenement_id !== $evenement->id, 403);
        Tarif::destroy($tarif->id);

        return redirect()->route('admin.tarifs.index', $evenement->id)
            ->with('success', 'Tarif supprimé avec succès.');
    }
}
