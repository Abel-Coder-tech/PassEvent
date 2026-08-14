<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SitePublicController extends Controller
{
    // Page d'accueil publique avec événements à la une et filtres
    public function accueil(Request $request)
    {
        // Récupère les catégories disponibles
        $categories = Evenement::where('statut', '=', 'publié')
            ->where('date_event', '>=', now())
            ->whereNotNull('categorie')
            ->distinct()
            ->orderBy('categorie')
            ->pluck('categorie');

        $selectedCategorie = $request->input('categorie');
        $selectedDate = $request->input('date');
        $q = $request->input('q');

        $query = Evenement::with('tarifs')
            ->where('statut', '=', 'publié')
            ->where('date_event', '>=', now())
            ->orderBy('date_event', 'asc');

        if ($selectedCategorie) {
            $query->where('categorie', '=', $selectedCategorie);
        }

        if ($selectedDate === 'weekend') {
            $query->whereBetween('date_event', [now()->startOfWeek()->addDays(5), now()->endOfWeek()->addDays(5)]);
        } elseif ($selectedDate === 'mois') {
            $query->whereMonth('date_event', now()->month)
                ->whereYear('date_event', now()->year);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('titre', 'like', '%'.$q.'%')
                    ->orWhere('categorie', 'like', '%'.$q.'%')
                    ->orWhere('lieu', 'like', '%'.$q.'%');
            });
        }

        $evenementsVedettes = $query->limit(12)->get();

        // Événements "à la une" : sélection superadmin, défilement dans le carrousel
        $evenementsUne = Evenement::ALaUne()
            ->where('statut', 'publié')
            ->where('date_event', '>=', now())
            ->with('tarifs')
            ->limit(6)
            ->get();

        return view('site.accueil', compact('evenementsVedettes', 'evenementsUne', 'categories', 'selectedCategorie', 'selectedDate', 'q'));
    }

    // Page d'aide / FAQ
    public function aide()
    {
        return view('site.aide');
    }

    // Formulaire de contact public
    public function contact()
    {
        return view('site.contact');
    }

    // Traite le formulaire de contact et notifie les super admins
    public function contactStore(Request $request)
    {
        $motifs = [
            'ticket_non_recu' => 'Incident paiement : ticket non reçu',
            'debit_sans_confirmation' => 'Incident paiement : débité sans confirmation',
            'erreur_montant' => 'Incident paiement : erreur de montant',
            'autre' => 'Autre question',
        ];

        $motif = $request->input('motif');
        $isIncident = in_array($motif, ['ticket_non_recu', 'debit_sans_confirmation', 'erreur_montant'], true);

        $rules = [
            'nom_complet' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'motif' => 'nullable|string|in:'.implode(',', array_keys($motifs)),
            'telephone' => 'nullable|string|max:30',
            'email_achat' => $isIncident ? 'required|email|max:255' : 'nullable|email|max:255',
            'transaction_id' => 'nullable|string|max:100',
            'message' => 'required|string|min:10|max:2000',
        ];

        if (! $isIncident) {
            $rules['objet'] = 'required|string|min:5|max:255';
        }

        $messages = [
            'nom_complet.required' => 'Le nom et prenom est obligatoire.',
            'nom_complet.min' => 'Le nom doit contenir au moins 3 caracteres.',
            'nom_complet.max' => 'Le nom ne doit pas depasser 255 caracteres.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.max' => 'L\'email ne doit pas depasser 255 caracteres.',
            'motif.in' => 'Le motif sélectionné est invalide.',
            'telephone.max' => 'Le téléphone ne doit pas dépasser 30 caracteres.',
            'email_achat.required' => 'Indiquez l\'email utilisé lors de l\'achat : il nous permet de retrouver votre commande.',
            'email_achat.email' => 'Le format de l\'email d\'achat est invalide.',
            'email_achat.max' => 'L\'email d\'achat ne doit pas dépasser 255 caracteres.',
            'transaction_id.max' => 'L\'ID de transaction ne doit pas dépasser 100 caracteres.',
            'objet.required' => 'L\'objet est obligatoire.',
            'objet.min' => 'L\'objet doit contenir au moins 5 caracteres.',
            'objet.max' => 'L\'objet ne doit pas dépasser 255 caracteres.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 10 caracteres.',
            'message.max' => 'Le message ne doit pas dépasser 2000 caracteres.',
        ];

        $validated = $request->validate($rules, $messages);

        // L'objet est recomposé côté serveur : jamais de confiance aux valeurs client pour les incidents
        $objet = $isIncident ? $motifs[$motif] : $validated['objet'];

        $message = Message::create([
            'nom_complet' => $validated['nom_complet'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'email_achat' => $validated['email_achat'] ?? null,
            'objet' => $objet,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'message' => $validated['message'],
        ]);

        // Envoyer un email à tous les super admins
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $sa) {
            Mail::raw(
                "Nouveau message depuis le formulaire de contact :\n\n".
                "De : {$validated['nom_complet']} ({$validated['email']})\n".
                ($validated['telephone'] ?? null ? "Téléphone : {$validated['telephone']}\n" : '').
                "Objet : {$objet}\n".
                ($validated['email_achat'] ?? null ? "Email d'achat : {$validated['email_achat']}\n" : '').
                ($validated['transaction_id'] ?? null ? "ID transaction FedaPay : {$validated['transaction_id']}\n" : '').
                "Message :\n{$validated['message']}\n\n".
                ($isIncident
                    ? "INCIDENT PAIEMENT : ouvrez le Support technique du super dashboard pour vérifier et réconcilier la transaction.\n\n"
                    : '').
                'Connectez-vous au super dashboard pour y repondre.',
                function ($m) use ($sa, $validated, $objet) {
                    $m->to($sa->email)
                        ->replyTo($validated['email'], $validated['nom_complet'])
                        ->subject("[PaxEvent] Contact : {$objet}");
                }
            );
        }

        return redirect()->route('contact')
            ->with('success', 'Votre message a ete envoye avec succes. Nous vous repondrons dans les plus brefs delais.');
    }

    // Page de politique de confidentialité
    public function confidentialite()
    {
        $file = resource_path('views/site/confidentialite.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file)) // Date de dernière modification du fichier
            : now()->isoFormat('MMMM YYYY');

        return view('site.confidentialite', compact('derniereMiseAJour'));
    }

    // Page des conditions générales d'utilisation
    public function cgu()
    {
        $file = resource_path('views/site/cgu.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->isoFormat('MMMM YYYY');

        return view('site.cgu', compact('derniereMiseAJour'));
    }

    // Page des mentions légales
    public function mentionsLegales()
    {
        $file = resource_path('views/site/mentions-legales.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->isoFormat('MMMM YYYY');

        return view('site.mentions-legales', compact('derniereMiseAJour'));
    }

    // Page de politique de remboursement
    public function politiqueRemboursement()
    {
        $file = resource_path('views/site/politique-remboursement.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->isoFormat('MMMM YYYY');

        return view('site.politique-remboursement', compact('derniereMiseAJour'));
    }

    // Page des conditions générales de vente
    public function cgv()
    {
        $file = resource_path('views/site/cgv.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->isoFormat('MMMM YYYY');

        return view('site.cgv', compact('derniereMiseAJour'));
    }

    // Page du programme d'affiliation
    public function affiliation()
    {
        $file = resource_path('views/site/affiliation.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->isoFormat('MMMM YYYY');

        return view('site.affiliation', compact('derniereMiseAJour'));
    }

    // Page du contrat de prestation
    public function contratPrestation()
    {
        $file = resource_path('views/site/contrat-prestation.blade.php');
        $derniereMiseAJour = file_exists($file)
            ? date('F Y', filemtime($file))
            : now()->isoFormat('MMMM YYYY');

        return view('site.contrat-prestation', compact('derniereMiseAJour'));
    }
}
