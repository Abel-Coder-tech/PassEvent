<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Message;
use App\Models\Tarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SitePublicController extends Controller
{
    public function accueil(Request $request)
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

        $query = Evenement::with('tarifs')
            ->where('statut', 'publié')
            ->where('date_event', '>=', now())
            ->orderBy('date_event', 'asc');

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
                $sub->where('titre', 'like', '%' . $q . '%')
                    ->orWhere('categorie', 'like', '%' . $q . '%')
                    ->orWhere('lieu', 'like', '%' . $q . '%');
            });
        }

        $evenementsVedettes = $query->limit(12)->get();

        return view('site.accueil', compact('evenementsVedettes', 'categories', 'selectedCategorie', 'selectedDate', 'q'));
    }

    public function aide()
    {
        return view('site.aide');
    }

    public function contact()
    {
        return view('site.contact');
    }

    public function contactStore(Request $request)
    {
        $validated = $request->validate([
            'nom_complet' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'objet' => 'required|string|min:5|max:255',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'nom_complet.required' => 'Le nom et prenom est obligatoire.',
            'nom_complet.min' => 'Le nom doit contenir au moins 3 caracteres.',
            'nom_complet.max' => 'Le nom ne doit pas depasser 255 caracteres.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.max' => 'L\'email ne doit pas depasser 255 caracteres.',
            'objet.required' => 'L\'objet est obligatoire.',
            'objet.min' => 'L\'objet doit contenir au moins 5 caracteres.',
            'objet.max' => 'L\'objet ne doit pas depasser 255 caracteres.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 10 caracteres.',
            'message.max' => 'Le message ne doit pas depasser 2000 caracteres.',
        ]);

        Message::create($validated);

        return redirect()->route('contact')
            ->with('success', 'Votre message a ete envoye avec succes. Nous vous repondrons dans les plus brefs delais.');
    }

    public function confidentialite()
    {
        return view('site.confidentialite');
    }

    public function cgu()
    {
        return view('site.cgu');
    }
}
