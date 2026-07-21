<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Tarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EvenementController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $user = Auth::user();

        $evenements = Evenement::where('user_id', $user->id)
            ->withCount(['tickets' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->orderBy('date_event', 'asc')
            ->paginate(10);

        $totalEvenements = Evenement::where('user_id', $user->id)->count();
        $enCours = Evenement::where('user_id', $user->id)->where('statut', 'publié')->count();
        $aVenir = Evenement::where('user_id', $user->id)->where('statut', 'brouillon')->count();
        $termines = Evenement::where('user_id', $user->id)->where('statut', 'terminé')->count();
        $totalBilletsVendus = Evenement::where('user_id', $user->id)->sum('quota_vendu');

        return view('evenements.index', compact(
            'evenements',
            'totalEvenements',
            'enCours',
            'aVenir',
            'termines',
            'totalBilletsVendus',
        ));
    }

    public function create()
    {
        if (Auth::user()->statut !== 'actif') {
            return redirect()->route('dashboard')->with('error', 'Votre profil doit être vérifié avant de pouvoir créer un événement.');
        }
        return view('evenements.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->statut !== 'actif') {
            return redirect()->route('dashboard')->with('error', 'Votre profil doit être vérifié avant de pouvoir créer un événement.');
        }

        $gratuit = $request->boolean('gratuit');
        $estUniversitaire = Auth::user()->type === 'universitaire';

        $rules = [
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_event' => 'required|date|after_or_equal:now',
            'lieu' => 'required|string|max:255',
            'categorie' => 'required',
            'autre_categorie' => 'nullable|string|max:255',
            'capacite' => 'required|integer|min:1',
            'date_fin_vente' => 'nullable|date|after:now|before_or_equal:date_event',
            'image' => 'nullable|image|max:2048',
            'statut' => 'required|in:brouillon,publié',
            'gratuit' => 'nullable|boolean',
        ];

        if (!$gratuit) {
            $rules['prix_base'] = 'required|numeric|min:0';
            $rules['multiplicateur_vip'] = 'required|in:1.5,2';
            if ($estUniversitaire) {
                $rules['reduction_etudiant'] = 'nullable|numeric|min:0|max:100';
            }
        }

        $validated = $request->validate($rules, [
            'titre.required' => 'Le titre de l\'événement est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.max' => 'La description ne doit pas dépasser 5000 caractères.',
            'date_event.required' => 'La date de l\'événement est obligatoire.',
            'date_event.date' => 'Le format de la date est invalide.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'lieu.max' => 'Le lieu ne doit pas dépasser 255 caractères.',
            'categorie.required' => 'La categorie est obligatoire.',
            'categorie.in' => 'La categorie doit etre : Sport, Soiree gala, Ceremonie officielle ou Webinaire.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être d\'au moins 1 place.',
            'date_fin_vente.after' => 'La date de fin de vente doit être dans le futur.',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            'statut.required' => 'Le statut est obligatoire.',
            'prix_base.required' => 'Le prix de base est obligatoire.',
            'prix_base.numeric' => 'Le prix doit être un nombre.',
        ]);

        if ($validated['categorie'] === 'Autre' && !empty($validated['autre_categorie'])) {
            $validated['categorie'] = $validated['autre_categorie'];
        }
        unset($validated['autre_categorie']);

        $validated['user_id'] = Auth::id();
        $validated['gratuit'] = $gratuit;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('evenements', 'public');
        }

        $evenement = Evenement::create($validated);

        if ($gratuit) {
            $prix = 0;
            if ($estUniversitaire) {
                $tarifs = [
                    ['categorie' => 'etudiant', 'type' => 'normal', 'prix' => $prix],
                    ['categorie' => 'etudiant', 'type' => 'vip', 'prix' => $prix],
                    ['categorie' => 'externe', 'type' => 'normal', 'prix' => $prix],
                    ['categorie' => 'externe', 'type' => 'vip', 'prix' => $prix],
                ];
            } else {
                $tarifs = [
                    ['categorie' => 'externe', 'type' => 'normal', 'prix' => $prix],
                    ['categorie' => 'externe', 'type' => 'vip', 'prix' => $prix],
                ];
            }
        } else {
            $prixBase = floatval($validated['prix_base']);
            $multVip = floatval($validated['multiplicateur_vip']);

            if ($estUniversitaire) {
                $reductionEtu = floatval($validated['reduction_etudiant'] ?? 30) / 100;
                $tarifs = [
                    ['categorie' => 'etudiant', 'type' => 'normal', 'prix' => round($prixBase * (1 - $reductionEtu))],
                    ['categorie' => 'etudiant', 'type' => 'vip', 'prix' => round($prixBase * $multVip * (1 - $reductionEtu))],
                    ['categorie' => 'externe', 'type' => 'normal', 'prix' => round($prixBase)],
                    ['categorie' => 'externe', 'type' => 'vip', 'prix' => round($prixBase * $multVip)],
                ];
            } else {
                $tarifs = [
                    ['categorie' => 'externe', 'type' => 'normal', 'prix' => round($prixBase)],
                    ['categorie' => 'externe', 'type' => 'vip', 'prix' => round($prixBase * $multVip)],
                ];
            }
        }

        foreach ($tarifs as $t) {
            $evenement->tarifs()->create([
                'categorie' => $t['categorie'],
                'type' => $t['type'],
                'prix' => $t['prix'],
                'quantite_disponible' => $evenement->capacite,
                'quantite_vendue' => 0,
                'statut' => 'actif',
            ]);
        }

        return redirect()->route('admin.evenements.index')
            ->with('success', $gratuit ? 'Événement gratuit créé avec succès.' : 'Événement créé avec succès.');
    }

    public function contratPrestation()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->statut, ['approuvé', 'verifie'])) {
            abort(403, 'Accès réservé aux organisateurs approuvés.');
        }

        $user->load('evenements');

        $html = view('site.contrat-prestation', compact('user'))->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Contrat-Prestation-PaxEvent-' . $user->id . '.pdf';

        return $pdf->download($filename);
    }

    public function show(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $ventes = $evenement->tickets()->where('statut_paiement', 'payé')->count();
        $revenus = $evenement->tickets()->where('statut_paiement', 'payé')->sum('montant');
        $placesRestantes = $evenement->capacite - $evenement->quota_vendu;
        $tauxRemplissage = $evenement->capacite > 0
            ? ($evenement->quota_vendu / $evenement->capacite) * 100
            : 0;
        $tarifs = $evenement->tarifs;
        $scanAccessCodes = $evenement->scanAccessCodes()->orderByDesc('created_at')->get();

        return view('evenements.show', compact(
            'evenement', 'ventes', 'revenus', 'placesRestantes', 'tauxRemplissage', 'tarifs', 'scanAccessCodes'
        ));
    }

    public function genererCodeAcces(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        do {
            $code = 'SCAN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (\App\Models\ScanAccessCode::where('code', $code)->exists());

        $evenement->scanAccessCodes()->create(['code' => $code]);

        return redirect()->route('admin.scan-codes.index')
            ->with('success', 'Code d\'accès généré : <strong>' . $code . '</strong><br>Rendez-vous dans le menu <strong>Scan QR</strong> pour commencer à scanner les tickets.');
    }

    public function supprimerCodeAcces(Evenement $evenement, \App\Models\ScanAccessCode $scanAccessCode)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        if ($scanAccessCode->evenement_id !== $evenement->id) {
            abort(404);
        }

        $scanAccessCode->delete();

        return back()->with('success', 'Code d\'accès supprimé.');
    }

    public function scanCodesIndex()
    {
        $evenements = auth()->user()->evenements()->orderByDesc('created_at')->get();

        return view('admin.scan-codes.index', compact('evenements'));
    }

    public function edit(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $evenement->load('tarifs');

        return view('evenements.edit', compact('evenement'));
    }

    public function update(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_event' => 'required|date',
            'lieu' => 'required|string|max:255',
            'categorie' => 'required',
            'autre_categorie' => 'nullable|string|max:255',
            'capacite' => 'required|integer|min:1',
            'date_fin_vente' => 'nullable|date|before_or_equal:date_event',
            'image' => 'nullable|image|max:2048',
            'statut' => 'required|in:brouillon,publié',
            'gratuit' => 'nullable|boolean',
        ], [
            'titre.required' => 'Le titre de l\'événement est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.max' => 'La description ne doit pas dépasser 5000 caractères.',
            'date_event.required' => 'La date de l\'événement est obligatoire.',
            'date_event.date' => 'Le format de la date est invalide.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'lieu.max' => 'Le lieu ne doit pas dépasser 255 caractères.',
            'categorie.required' => 'La categorie est obligatoire.',
            'autre_categorie.max' => 'La categorie personnalisee ne doit pas depasser 255 caracteres.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être d\'au moins 1 place.',
            'date_fin_vente.before_or_equal' => 'La date de fin de vente doit être antérieure ou égale à la date de l\'événement.',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            'statut.required' => 'Le statut est obligatoire.',
        ]);

        if ($validated['categorie'] === 'Autre' && !empty($validated['autre_categorie'])) {
            $validated['categorie'] = $validated['autre_categorie'];
        }
        unset($validated['autre_categorie']);

        $validated['gratuit'] = $request->boolean('gratuit');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('evenements', 'public');
        }

        $evenement->update($validated);

        if ($validated['gratuit']) {
            $evenement->tarifs()->update(['prix' => 0]);
        }
        // Si on décoche gratuit, les tarifs gardent leur prix actuel;

        return redirect()->route('admin.evenements.index')
            ->with('success', 'Événement modifié avec succès.');
    }

    public function destroy(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $evenement->delete();

        return redirect()->route('admin.evenements.index')
            ->with('success', 'Événement supprimé avec succès.');
    }
}
