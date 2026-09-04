<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Tarif;
use App\Services\ContratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EvenementController extends Controller
{
    use AuthorizesRequests;

    // Liste les événements de l'organisateur avec statistiques résumées
    public function index()
    {
        $user = Auth::user();

        $evenements = Evenement::where('user_id', $user->id)
            ->withCount(['tickets' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->orderBy('date_event', 'asc')
            ->paginate(\App\Support\PerPage::resolve());

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

    // Affiche le formulaire de création (profil vérifié requis)
    public function create()
    {
        if (Auth::user()->statut !== 'actif') { // Seuls les profils vérifiés peuvent créer
            return redirect()->route('dashboard')->with('error', 'Votre profil doit être vérifié avant de pouvoir créer un événement.');
        }
        return view('evenements.create');
    }

    // Crée un événement avec tarifs personnalisés
    public function store(Request $request)
    {
        if (Auth::user()->statut !== 'actif') {
            return redirect()->route('dashboard')->with('error', 'Votre profil doit être vérifié avant de pouvoir créer un événement.');
        }

        $gratuit = $request->boolean('gratuit');

        $rules = [
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_event' => 'required|date|after_or_equal:now',
            'lieu' => 'required|string|max:255',
            'categorie' => 'required',
            'autre_categorie' => 'nullable|string|max:255',
            'capacite' => 'required|integer|min:1',
            'image' => 'nullable|image|max:512',
            'statut' => 'required|in:brouillon,publié',
            'type_evenement' => 'required|in:spectacle,formation,conference',
            'gratuit' => 'nullable|boolean',
            'tarif_nom_1' => 'required_without:gratuit|string|max:100',
            'tarif_prix_1' => 'required_without:gratuit|numeric|min:0',
            'tarif_qte_1' => 'nullable|integer|min:1',
            'tarif_nom_2' => 'nullable|string|max:100',
            'tarif_prix_2' => 'nullable|numeric|min:0',
            'tarif_qte_2' => 'nullable|integer|min:1',
            'tarif_nom_3' => 'nullable|string|max:100',
            'tarif_prix_3' => 'nullable|numeric|min:0',
            'tarif_qte_3' => 'nullable|integer|min:1',
            'tarif_nom_4' => 'nullable|string|max:100',
            'tarif_prix_4' => 'nullable|numeric|min:0',
            'tarif_qte_4' => 'nullable|integer|min:1',
            'generer_vip' => 'nullable|boolean',
        ];

        if ($gratuit) {
            foreach ($rules as $key => $rule) {
                if (str_starts_with($key, 'tarif_')) {
                    unset($rules[$key]);
                }
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
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être d\'au moins 1 place.',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => 'L\'image ne doit pas dépasser 512 Ko.',
            'statut.required' => 'Le statut est obligatoire.',
            'tarif_nom_1.required_without' => 'Le nom du tarif est obligatoire.',
            'tarif_nom_1.string' => 'Le nom du tarif doit être du texte.',
            'tarif_prix_1.required_without' => 'Le prix du tarif est obligatoire.',
            'tarif_prix_1.numeric' => 'Le prix du tarif doit être un nombre.',
            'tarif_prix_1.min' => 'Le prix du tarif ne peut pas être négatif.',
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

        // Extraire les données de dates supplémentaires (multi-jours) avant création
        $datesData = $this->extraireDatesSupplementaires($request, $validated['date_event']);

        // Extraire les données de tarifs avant de créer l'événement
        $tarifsData = [];
        if ($gratuit) {
            $tarifsData[] = ['nom' => 'Gratuit', 'prix' => 0, 'quantite_disponible' => null];
        } else {
            for ($i = 1; $i <= 4; $i++) {
                $nom = trim($validated["tarif_nom_{$i}"] ?? '');
                $prix = $validated["tarif_prix_{$i}"] ?? null;
                $qte = !empty($validated["tarif_qte_{$i}"]) ? (int) $validated["tarif_qte_{$i}"] : null;

                if ($nom !== '' && $prix !== null) {
                    $tarifsData[] = [
                        'nom' => $nom,
                        'prix' => round(floatval($prix)),
                        'quantite_disponible' => $qte,
                    ];
                }
            }

            // Génération automatique VIP si cochée
            if ($request->boolean('generer_vip') && count($tarifsData) >= 1) {
                $premierPrix = $tarifsData[0]['prix'];
                $tarifsData[] = [
                    'nom' => 'VIP',
                    'prix' => $premierPrix * 2,
                    'quantite_disponible' => null,
                ];
            }
        }

        // Nettoyer les champs tarif du validated
        foreach (['tarif_nom_', 'tarif_prix_', 'tarif_qte_'] as $prefix) {
            for ($i = 1; $i <= 4; $i++) {
                unset($validated["{$prefix}{$i}"]);
            }
        }
        unset($validated['generer_vip']);

        $evenement = Evenement::create($validated);

        // Enregistre les sessions (jours) de l'événement, la première étant date_event
        $this->enregistrerDates($evenement, $validated['date_event'], $datesData);

        // Créer les tarifs
        foreach ($tarifsData as $t) {
            $evenement->tarifs()->create([
                'nom' => $t['nom'],
                'prix' => $t['prix'],
                'quantite_disponible' => $t['quantite_disponible'],
                'quantite_vendue' => 0,
                'statut' => 'actif',
            ]);
        }

        return redirect()->route('admin.evenements.index')
            ->with('success', $gratuit ? 'Événement gratuit créé avec succès.' : 'Événement créé avec succès.');
    }

    // Génère un contrat de prestation PDF pour l'organisateur
    public function contratPrestation(ContratService $contratService)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->statut, ['actif', 'approuvé', 'verifie'])) {
            abort(403, 'Accès réservé aux organisateurs approuvés.');
        }

        $user->load('evenements');

        $pdf = $contratService->pdf($user);

        if (!$user->contrat_telecharge_le) {
            $user->update(['contrat_telecharge_le' => now()]);
        }

        return $pdf->download($contratService->filename($user));
    }

    // Affiche les détails d'un événement avec statistiques de ventes
    public function show(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

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

    // Génère un code d'accès unique au format SCAN-XXXXXXXX
    public function genererCodeAcces(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

        do {
            $code = 'SCAN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)); // Code aléatoire unique
        } while (\App\Models\ScanAccessCode::where('code', $code)->exists()); // Évite les doublons

        $evenement->scanAccessCodes()->create(['code' => $code]);

        return redirect()->route('admin.scan-codes.index')
            ->with('success', 'Code d\'accès généré : <strong>' . $code . '</strong><br>Rendez-vous dans le menu <strong>Scan QR</strong> pour commencer à scanner les tickets.');
    }

    // Supprime un code d'accès scan
    public function supprimerCodeAcces(Evenement $evenement, \App\Models\ScanAccessCode $scanAccessCode)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriétaire

        if ($scanAccessCode->evenement_id !== $evenement->id) {
            abort(404); // Code n'appartient pas à cet événement
        }

        $scanAccessCode->delete();

        return back()->with('success', 'Code d\'accès supprimé.');
    }

    // Liste les codes d'accès scan par événement
    public function scanCodesIndex()
    {
        $evenements = auth()->user()->evenements()->orderByDesc('created_at')->get();

        return view('admin.scan-codes.index', compact('evenements'));
    }

    // Affiche le formulaire d'édition d'un événement
    public function edit(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

        $evenement->load('tarifs');

        return view('evenements.edit', compact('evenement'));
    }

    // Met à jour un événement existant
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
            'image' => 'nullable|image|max:512',
            'statut' => 'required|in:brouillon,publié',
            'type_evenement' => 'required|in:spectacle,formation,conference',
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
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être d\'au moins 1 place.',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => 'L\'image ne doit pas dépasser 512 Ko.',
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

        $datesData = $this->extraireDatesSupplementaires($request, $validated['date_event']);

        $evenement->update($validated);

        // Remplace les sessions de l'événement par celles saisies (la première étant date_event)
        $this->enregistrerDates($evenement, $validated['date_event'], $datesData);

        if ($validated['gratuit']) {
            $evenement->tarifs()->update(['prix' => 0]);
        } else {
            $this->synchroniserPrixTarifs($evenement, (array) $request->input('prix_tarifs', []));
        }

        return redirect()->route('admin.evenements.index')
            ->with('success', 'Événement modifié avec succès.');
    }

    // Bascule la fermeture des ventes
    public function fermerVente(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $evenement->update([
            'ventes_fermees' => !$evenement->ventes_fermees,
        ]);

        $message = $evenement->ventes_fermees
            ? 'Les ventes sont désormais fermées pour cet événement.'
            : 'Les ventes sont désormais rouvertes pour cet événement.';

        return redirect()->route('admin.evenements.show', $evenement->id)
            ->with('success', $message);
    }

    // Supprime définitivement un événement
    public function destroy(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

        $evenement->delete();

        return redirect()->route('admin.evenements.index')
            ->with('success', 'Événement supprimé avec succès.');
    }

    // Applique directement le nouveau prix aux tarifs n'ayant aucune vente
    // (les tarifs avec ventes passent obligatoirement par une approbation PaxEvent).
    private function synchroniserPrixTarifs(Evenement $evenement, array $prixTarifs): void
    {
        foreach ($prixTarifs as $tarifId => $prix) {
            $tarif = $evenement->tarifs()->find((int) $tarifId);
            if (!$tarif) {
                continue;
            }

            // Un tarif avec des ventes laisse le prix inchangé :
            // sa modification passe par une demande d'approbation dédiée.
            if ($tarif->quantite_vendue > 0) {
                continue;
            }

            $prix = trim((string) $prix);
            if ($prix === '' || !is_numeric($prix) || (float) $prix < 0) {
                continue;
            }

            if ((float) $prix !== (float) $tarif->prix) {
                $tarif->update(['prix' => (float) $prix]);
            }
        }
    }

    // Collecte les dates supplémentaires saisies dans le formulaire (dates_supplementaires[])
    private function extraireDatesSupplementaires(Request $request, string $premiereDate): array
    {
        $dates = [];
        foreach ((array) $request->input('dates_supplementaires', []) as $dateBrute) {
            $dateBrute = trim((string) $dateBrute);
            if ($dateBrute === '') {
                continue;
            }

            try {
                $date = \Carbon\Carbon::parse($dateBrute);
            } catch (\Exception $e) {
                continue;
            }

            // Évite les doublons et les dates identiques à la première session
            if ($date->equalTo(\Carbon\Carbon::parse($premiereDate))) {
                continue;
            }
            if (in_array($date->toDateTimeString(), $dates, true)) {
                continue;
            }

            $dates[] = $date->toDateTimeString();
        }

        sort($dates);

        return $dates;
    }

    // Synchronise les sessions de l'événement : la première = date_event, puis les dates supplémentaires
    private function enregistrerDates(Evenement $evenement, string $premiereDate, array $datesSupplementaires): void
    {
        $toutes = array_merge([\Carbon\Carbon::parse($premiereDate)->toDateTimeString()], $datesSupplementaires);
        $toutes = array_values(array_unique($toutes));

        // Préserve les evenement_dates existants qui correspondent (id stable) mais reconstruit l'ordre
        $evenement->dates()->delete();

        foreach (array_values($toutes) as $i => $date) {
            $evenement->dates()->create([
                'date_debut' => $date,
                'ordre' => $i,
            ]);
        }
    }
}
