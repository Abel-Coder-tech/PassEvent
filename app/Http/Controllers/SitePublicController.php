<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Message;
use App\Models\Tarif;
use App\Models\User;
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

        $message = Message::create($validated);

        // Envoyer un email à tous les super admins
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::raw(
                "Nouveau message depuis le formulaire de contact :\n\n" .
                "De : {$validated['nom_complet']} ({$validated['email']})\n" .
                "Objet : {$validated['objet']}\n" .
                "Message :\n{$validated['message']}\n\n" .
                "Connectez-vous au super dashboard pour y repondre.",
                function ($m) use ($sa, $validated) {
                    $m->to($sa->email)
                      ->replyTo($validated['email'], $validated['nom_complet'])
                      ->subject("[PaxEvent] Contact : {$validated['objet']}");
                }
            );
        }

        return redirect()->route('contact')
            ->with('success', 'Votre message a ete envoye avec succes. Nous vous repondrons dans les plus brefs delais.');
    }

    public function confidentialite()
    {
        $file = resource_path('views/site/confidentialite.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->format('F Y');

        return view('site.confidentialite', compact('derniereMiseAJour'));
    }

    public function cgu()
    {
        $file = resource_path('views/site/cgu.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->format('F Y');

        return view('site.cgu', compact('derniereMiseAJour'));
    }

    public function mentionsLegales()
    {
        $file = resource_path('views/site/mentions-legales.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->format('F Y');

        return view('site.mentions-legales', compact('derniereMiseAJour'));
    }

    public function politiqueRemboursement()
    {
        $file = resource_path('views/site/politique-remboursement.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->format('F Y');

        return view('site.politique-remboursement', compact('derniereMiseAJour'));
    }

}
