<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\EvenementDate;
use App\Models\Tarif;
use App\Jobs\SendTicketEmailJob;
use App\Services\ContratService;
use App\Services\EvenementStatisticsService;
use App\Services\TicketStatisticsService;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;

class EvenementController extends Controller
{
    use AuthorizesRequests;

    // Liste les événements de l'organisateur avec statistiques résumées
    public function index(EvenementStatisticsService $statistics)
    {
        $user = Auth::user();

        $evenements = Evenement::where('user_id', $user->id)
            ->with('dates')
            ->withExists(['ventesPayeesHorsBilletsPhysiques as has_successful_non_physical_sales'])
            ->withCount(['tickets' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->orderBy('date_event', 'asc')
            ->paginate(\App\Support\PerPage::resolve());

        $stats = $statistics->pourOrganisateur($user->id);
        $totalEvenements = $stats['total_evenements'];
        $enCours = $stats['en_cours'];
        $aVenir = $stats['a_venir'];
        $termines = $stats['termines'];
        $totalBilletsVendus = $stats['total_billets_vendus'];

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
        $maxCoParrainage = max(0, 100 - Auth::user()->commissionPourcentage());
        return view('evenements.create', compact('maxCoParrainage'));
    }

    // Limites de l'assistant de création (alignées sur la gestion des tarifs)
    public const MAX_JOURS = 7;

    // 7 jours (un tarif chacun) + Pack Simple + Pack VIP
    public const MAX_TARIFS = 9;

    // Packs proposés pour un événement sur plusieurs jours
    public const PACKS = ['Pack Simple', 'Pack VIP'];

    // Crée un événement depuis l'assistant en étapes : jours, jauges par jour,
    // billets à la journée et packs.
    public function store(Request $request)
    {
        if (Auth::user()->statut !== 'actif') {
            ActivityLogger::failure('evenement.create', 'Profil non vérifié', null, ['titre' => $request->input('titre')], $request);
            return redirect()->route('dashboard')->with('error', 'Votre profil doit être vérifié avant de pouvoir créer un événement.');
        }

        $gratuit = $request->boolean('gratuit');
        $multi = $request->input('duree') === 'plusieurs';

        // Co-parrainage : commission offerte aux affiliés, plafonnée à (100 % − commission PaxEvent)
        $commissionPax = Auth::user()->commissionPourcentage();
        $maxCoParrainage = max(0, 100 - $commissionPax);

        $rules = [
            'type_evenement' => 'required|in:spectacle,formation,conference',
            'categorie' => 'required|string|max:255',
            'autre_categorie' => 'nullable|string|max:255|required_if:categorie,Autre',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:2048', // 2 Mo
            'gratuit' => 'required|boolean',
            'duree' => 'required|in:un,plusieurs',
            'lieu' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'statut' => 'required|in:brouillon,publié',
            'billetterie_plus_tard' => 'nullable|boolean',
            'billetterie_ouvre_le' => 'nullable|date|after:now',
        ];

        if ($multi) {
            $rules['jours'] = 'required|array|min:2|max:'.self::MAX_JOURS;
            $rules['jours.*'] = 'required|date|after_or_equal:now|distinct';
            $rules['meme_capacite'] = 'nullable|boolean';
            $rules['capacites_jours'] = 'nullable|array';
            $rules['capacites_jours.*'] = 'nullable|integer|min:1';
        } else {
            $rules['date_event'] = 'required|date|after_or_equal:now';
        }

        if (! $gratuit) {
            $rules['tarifs'] = 'required|array|min:1|max:'.self::MAX_TARIFS;
            $rules['tarifs.*.nom'] = 'required|string|max:100';
            $rules['tarifs.*.nom_perso'] = 'nullable|string|max:100';
            // Multijours : le prix et la quantité d'un jour peuvent rester vides (jour gratuit),
            // contrôlés plus bas ; les packs et les événements d'un jour les exigent.
            $rules['tarifs.*.prix'] = ($multi ? 'nullable' : 'required').'|numeric|min:0';
            $rules['tarifs.*.qte'] = ($multi ? 'nullable' : 'required').'|integer|min:1';
            $rules['co_parrainage_actif'] = 'nullable|boolean';
            $rules['co_parrainage_pourcentage'] = "nullable|numeric|min:1|max:{$maxCoParrainage}|required_if:co_parrainage_actif,1";

            if ($multi) {
                $rules['tarifs.*.jour'] = 'required|string';
            } else {
                $rules['generer_vip'] = 'nullable|boolean';
            }
        }

        try {
            $validated = $request->validate($rules, [
                'type_evenement.required' => 'Choisissez le type d\'événement.',
                'categorie.required' => 'Choisissez une catégorie.',
                'autre_categorie.required_if' => 'Précisez la catégorie.',
                'titre.required' => 'Le titre de l\'événement est obligatoire.',
                'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',
                'description.max' => 'La description ne doit pas dépasser 5000 caractères.',
                'image.image' => 'Le fichier doit être une image.',
                'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
                'gratuit.required' => 'Indiquez si l\'événement est gratuit ou payant.',
                'duree.required' => 'Indiquez si l\'événement dure un ou plusieurs jours.',
                'date_event.required' => 'La date de l\'événement est obligatoire.',
                'date_event.date' => 'Le format de la date est invalide.',
                'date_event.after_or_equal' => 'La date de l\'événement doit être dans le futur.',
                'jours.min' => 'Un événement sur plusieurs jours doit compter au moins 2 jours.',
                'jours.max' => 'Un événement ne peut pas dépasser '.self::MAX_JOURS.' jours.',
                'jours.*.required' => 'Renseignez la date et l\'heure de chaque jour.',
                'jours.*.date' => 'Le format d\'une date est invalide.',
                'jours.*.after_or_equal' => 'Chaque jour doit être dans le futur.',
                'jours.*.distinct' => 'Deux jours ont la même date et la même heure.',
                'lieu.required' => 'Le lieu est obligatoire.',
                'lieu.max' => 'Le lieu ne doit pas dépasser 255 caractères.',
                'capacite.required' => 'La capacité est obligatoire.',
                'capacite.integer' => 'La capacité doit être un nombre entier.',
                'capacite.min' => 'La capacité doit être d\'au moins 1 place.',
                'capacites_jours.*.integer' => 'La capacité d\'un jour doit être un nombre entier.',
                'capacites_jours.*.min' => 'La capacité d\'un jour doit être d\'au moins 1 place.',
                'statut.required' => 'Le statut est obligatoire.',
                'tarifs.required' => 'Ajoutez au moins un billet.',
                'tarifs.max' => 'Un événement ne peut pas avoir plus de '.self::MAX_TARIFS.' tarifs.',
                'tarifs.*.nom.required' => 'Choisissez le nom de chaque billet.',
                'tarifs.*.prix.required' => 'Indiquez le prix de chaque billet (0 pour gratuit).',
                'tarifs.*.prix.numeric' => 'Le prix d\'un billet doit être un nombre.',
                'tarifs.*.prix.min' => 'Le prix d\'un billet ne peut pas être négatif.',
                'tarifs.*.qte.required' => 'Indiquez le nombre de places de chaque billet.',
                'tarifs.*.qte.integer' => 'Le nombre de places doit être un nombre entier.',
                'tarifs.*.qte.min' => 'Le nombre de places doit être d\'au moins 1.',
                'tarifs.*.jour.required' => 'Indiquez le jour de chaque billet.',
                'co_parrainage_pourcentage.required_if' => 'Indiquez la commission à reverser aux affiliés.',
                'co_parrainage_pourcentage.min' => 'La commission de co-parrainage doit être d\'au moins 1 %.',
                'co_parrainage_pourcentage.max' => "La commission de co-parrainage ne peut pas dépasser {$maxCoParrainage} % (100 % moins votre commission PaxEvent de {$commissionPax} %).",
            ]);
        } catch (ValidationException $e) {
            ActivityLogger::failure('evenement.create', 'Validation échouée lors de la création', null, ['errors' => array_keys($e->errors()), 'titre' => $request->input('titre')], $request);
            throw $e;
        }

        // Jours : triés par date. On garde la correspondance « index du
        // formulaire → position chronologique » pour rattacher les billets.
        if ($multi) {
            $ordre = collect($validated['jours'])
                ->map(fn ($date, $index) => ['index' => (string) $index, 'date' => Carbon::parse($date)->toDateTimeString()])
                ->sortBy('date')
                ->values();
            $dateEvent = $ordre->first()['date'];
            $datesSupplementaires = $ordre->slice(1)->pluck('date')->values()->all();
            $positionParIndex = $ordre->mapWithKeys(fn ($ligne, $position) => [$ligne['index'] => $position]);
        } else {
            $dateEvent = Carbon::parse($validated['date_event'])->toDateTimeString();
            $datesSupplementaires = [];
            $positionParIndex = collect(['0' => 0]);
        }

        // Jauge propre à chaque jour (null = capacité générale)
        $capacitesParPosition = [];
        if ($multi && ! $request->boolean('meme_capacite')) {
            foreach ($positionParIndex as $index => $position) {
                $valeur = $validated['capacites_jours'][$index] ?? null;
                $capacitesParPosition[$position] = ($valeur === null || $valeur === '') ? null : (int) $valeur;
            }
        }
        $capaciteDuJour = fn (int $position) => $capacitesParPosition[$position] ?? (int) $validated['capacite'];

        // Billets : position du jour (null = pack, valable tous les jours)
        $tarifsData = [];
        if ($gratuit) {
            $tarifsData[] = ['nom' => 'Gratuit', 'prix' => 0, 'quantite_disponible' => null, 'position' => null];
        } else {
            foreach (array_values($validated['tarifs']) as $i => $tarif) {
                $nom = $tarif['nom'] === 'Personnalisé' ? trim((string) ($tarif['nom_perso'] ?? '')) : trim($tarif['nom']);
                if ($nom === '') {
                    throw ValidationException::withMessages(["tarifs.{$i}.nom_perso" => 'Indiquez le nom du billet personnalisé.']);
                }

                $position = null;
                if ($multi && (string) $tarif['jour'] !== 'pack') {
                    if (! $positionParIndex->has((string) $tarif['jour'])) {
                        throw ValidationException::withMessages(["tarifs.{$i}.jour" => 'Le jour choisi pour un billet est invalide.']);
                    }
                    $position = $positionParIndex[(string) $tarif['jour']];
                }

                $prixVide = ! isset($tarif['prix']) || $tarif['prix'] === '';
                $qteVide = ! isset($tarif['qte']) || $tarif['qte'] === '';
                $jourLibre = $multi && $position !== null;
                if ($prixVide && ! $jourLibre) {
                    throw ValidationException::withMessages(["tarifs.{$i}.prix" => 'Indiquez le prix de chaque billet (0 pour gratuit).']);
                }
                $prix = $prixVide ? 0.0 : round((float) $tarif['prix']);
                // Un jour gratuit (vide ou 0 F) peut ne pas limiter ses places : la jauge du jour s'applique.
                if ($qteVide && ! ($jourLibre && $prix === 0.0)) {
                    throw ValidationException::withMessages(["tarifs.{$i}.qte" => 'Indiquez le nombre de places de chaque billet.']);
                }

                $tarifsData[] = [
                    'nom' => $nom,
                    'prix' => $prix,
                    'quantite_disponible' => $qteVide ? null : (int) $tarif['qte'],
                    'position' => $position,
                ];
            }

            // Génération automatique VIP (un seul jour) : stock illimité, borné par la capacité
            if (! $multi && $request->boolean('generer_vip')) {
                $tarifsData[] = ['nom' => 'VIP', 'prix' => $tarifsData[0]['prix'] * 2, 'quantite_disponible' => null, 'position' => null];
            }

            if (count($tarifsData) > self::MAX_TARIFS) {
                throw ValidationException::withMessages(['tarifs' => 'Un événement ne peut pas avoir plus de '.self::MAX_TARIFS.' tarifs.']);
            }

            if ($multi) {
                $tarifsData = $this->normaliserTarifsMultiJours($tarifsData, $ordre->count());
            }

            $this->validerStocksCreation($tarifsData, $multi ? $ordre->count() : 1, $capaciteDuJour, $multi, $request, $validated);
        }

        $attributs = collect($validated)->only([
            'titre', 'description', 'lieu', 'capacite', 'statut', 'type_evenement',
        ])->all();
        $attributs['categorie'] = $validated['categorie'] === 'Autre'
            ? trim((string) $validated['autre_categorie'])
            : $validated['categorie'];
        $attributs['date_event'] = $dateEvent;
        $attributs['user_id'] = Auth::id();
        $attributs['gratuit'] = $gratuit;

        // Billetterie ouverte dès la publication, ou ouverture plus tard (date facultative)
        $plusTard = $request->boolean('billetterie_plus_tard');
        $attributs['billetterie_ouverte'] = ! $plusTard;
        $attributs['billetterie_ouvre_le'] = $plusTard && ! empty($validated['billetterie_ouvre_le'])
            ? Carbon::parse($validated['billetterie_ouvre_le'])
            : null;

        // Co-parrainage : impossible sur un événement gratuit, null si désactivé
        $attributs['co_parrainage_actif'] = ! $gratuit && $request->boolean('co_parrainage_actif');
        $attributs['co_parrainage_pourcentage'] = $attributs['co_parrainage_actif']
            ? (float) $validated['co_parrainage_pourcentage']
            : null;

        if ($request->hasFile('image')) {
            $attributs['image'] = $request->file('image')->store('evenements', 'public');
        }

        $evenement = DB::transaction(function () use ($attributs, $dateEvent, $datesSupplementaires, $capacitesParPosition, $tarifsData) {
            $evenement = Evenement::create($attributs);

            // Sessions de l'événement, la première étant date_event (ordre chronologique)
            $this->enregistrerDates($evenement, $dateEvent, $datesSupplementaires);
            $jours = $evenement->dates()->get()->values();

            foreach ($capacitesParPosition as $position => $capacite) {
                if ($capacite !== null && isset($jours[$position])) {
                    $jours[$position]->update(['capacite' => $capacite]);
                }
            }

            foreach ($tarifsData as $t) {
                $evenement->tarifs()->create([
                    'evenement_date_id' => $t['position'] !== null && $jours->count() > 1 ? $jours[$t['position']]->id : null,
                    'nom' => $t['nom'],
                    'prix' => $t['prix'],
                    'quantite_disponible' => $t['quantite_disponible'],
                    'quantite_vendue' => 0,
                    'statut' => 'actif',
                ]);
            }

            return $evenement;
        });

        ActivityLogger::success('evenement.create', 'Événement créé : ' . $evenement->titre, $evenement, [
            'evenement_id' => $evenement->id,
            'statut' => $evenement->statut,
            'gratuit' => (bool) $gratuit,
            'jours' => count($datesSupplementaires) + 1,
        ], $request);

        $message = $evenement->statut === 'brouillon'
            ? 'Brouillon enregistré. Vous pourrez le publier depuis vos événements.'
            : ($gratuit ? 'Événement gratuit créé avec succès.' : 'Événement créé avec succès.');

        return redirect()->route('admin.evenements.index')->with('success', $message);
    }

    // Multijours : exactement un tarif par jour (nommé « Jour N » dans l'ordre
    // chronologique) et au plus les deux packs proposés.
    private function normaliserTarifsMultiJours(array $tarifsData, int $nbJours): array
    {
        $tarifs = collect($tarifsData);
        $parJour = $tarifs->whereNotNull('position')->groupBy('position');

        if ($parJour->contains(fn ($groupe) => $groupe->count() > 1)) {
            throw ValidationException::withMessages(['tarifs' => 'Un seul tarif par jour : utilisez les packs pour proposer une autre formule.']);
        }

        if ($parJour->count() !== $nbJours) {
            throw ValidationException::withMessages(['tarifs' => 'Chaque jour doit avoir son tarif (0 F pour un jour gratuit).']);
        }

        $nomsPacks = $tarifs->whereNull('position')->pluck('nom');
        if ($nomsPacks->diff(self::PACKS)->isNotEmpty() || $nomsPacks->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['tarifs' => 'Les packs proposés sont « Pack Simple » et « Pack VIP », une fois chacun.']);
        }

        return $tarifs->map(function (array $tarif) {
            if ($tarif['position'] !== null) {
                $tarif['nom'] = 'Jour '.($tarif['position'] + 1);
            }

            return $tarif;
        })->all();
    }

    // Stocks à la création. Un jour : la somme des tarifs ne dépasse pas la
    // capacité. Plusieurs jours : pour chaque jour, billets du jour + packs ne
    // dépassent pas la jauge du jour.
    private function validerStocksCreation(array $tarifsData, int $nbJours, \Closure $capaciteDuJour, bool $multi, Request $request, array $validated): void
    {
        $tarifs = collect($tarifsData);

        if (! $multi) {
            $total = (int) $tarifs->sum(fn ($t) => (int) ($t['quantite_disponible'] ?? 0));
            if ($total > (int) $validated['capacite']) {
                ActivityLogger::failure('evenement.create', 'Capacité tarifs dépassée', null, [
                    'titre' => $validated['titre'], 'total_places_tarifs' => $total, 'capacite' => $validated['capacite'],
                ], $request);

                throw ValidationException::withMessages([
                    'tarifs' => "La somme des places des billets ({$total}) dépasse la capacité de l'événement ({$validated['capacite']} places).",
                ]);
            }

            return;
        }

        $packs = (int) $tarifs->whereNull('position')->sum('quantite_disponible');
        for ($position = 0; $position < $nbJours; $position++) {
            // whereStrict : en comparaison souple, null (pack) == 0 (Jour 1)
            $duJour = (int) $tarifs->whereStrict('position', $position)->sum('quantite_disponible');
            $total = $duJour + $packs;
            $capacite = $capaciteDuJour($position);

            if ($total > $capacite) {
                ActivityLogger::failure('evenement.create', 'Jauge d\'un jour dépassée', null, [
                    'titre' => $validated['titre'], 'jour' => $position + 1, 'total' => $total, 'capacite' => $capacite,
                ], $request);

                $numero = $position + 1;
                throw ValidationException::withMessages([
                    'tarifs' => "Jour {$numero} : les billets du jour ({$duJour}) et les packs ({$packs}) font {$total} places, au-delà de la capacité du jour ({$capacite} places).",
                ]);
            }
        }
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
    public function show(Evenement $evenement, TicketStatisticsService $ticketStatistics)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

        $statsVentes = $ticketStatistics->ventesEvenement($evenement->id);
        $ventes = $statsVentes['ventes'];
        $revenus = $statsVentes['revenus'];
        $placesRestantes = $evenement->placesRestantesTotales();
        $tauxRemplissage = $evenement->tauxRemplissage();
        $tarifs = $evenement->tarifs()->with('jour')->get();
        $jours = $evenement->dates()->get();
        $scanAccessCodes = $evenement->scanAccessCodes()->orderByDesc('created_at')->get();

        return view('evenements.show', compact(
            'evenement', 'ventes', 'revenus', 'placesRestantes', 'tauxRemplissage', 'tarifs', 'scanAccessCodes', 'jours'
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
            ->with('success', 'Code d\'accès généré : ' . $code . ' — Rendez-vous dans le menu "Scan QR" pour commencer à scanner les tickets.');
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
        $evenements = Auth::user()->evenements()->with('scanAccessCodes')->orderByDesc('created_at')->get();

        return view('admin.scan-codes.index', compact('evenements'));
    }

    // Affiche le formulaire d'édition d'un événement
    public function edit(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

        if ($evenement->modificationGlobaleVerrouillee()) {
            return redirect()->route('admin.evenements.show', $evenement)
                ->with('error', 'Cet événement est verrouillé : des scans ont été effectués et le délai de 12 heures après sa dernière date est écoulé.');
        }

        $evenement->load('tarifs', 'dates');
        $joursAvecScansReussis = $evenement->joursAvecScansReussis();

        $maxCoParrainage = max(0, 100 - $evenement->commissionEffective());

        // La modification reprend l'assistant de création, prérempli avec
        // l'événement (sauf après une erreur : la saisie est alors reprise).
        if (! session()->hasOldInput()) {
            session()->now('_old_input', $this->valeursAssistant($evenement));
        }

        // Jours verrouillés : seulement ceux ayant un scan réussi. Un jour avec
        // tarifs ou billets peut être déplacé (les billets suivent le jour) mais
        // pas supprimé.
        $joursVerrouilles = $joursAvecScansReussis->unique()->values();
        $joursProteges = $evenement->joursAvecTarifsOuBillets();

        $edition = [
            'joursVerrouilles' => $joursVerrouilles->all(),
            'joursProteges' => $joursProteges->all(),
            'aDesBillets' => $evenement->tickets()->where('statut_paiement', 'payé')->exists(),
            'dateInitiale' => $evenement->date_event->format('Y-m-d\\TH:i'),
            'image' => $evenement->image ? asset('storage/'.$evenement->image) : null,
            'tarifs' => $evenement->tarifs->mapWithKeys(fn ($tarif) => [$tarif->id => [
                'vendu' => (int) $tarif->quantite_vendue,
                'illimite' => $tarif->quantite_disponible === null,
            ]])->all(),
            // Ancien événement multijours sans tarif à la journée : on ne lui
            // impose pas de tarif « Jour N ».
            'sansTarifJour' => $evenement->dates->count() > 1
                && $evenement->tarifs->whereNotNull('evenement_date_id')->isEmpty(),
            'urlTarifs' => route('admin.tarifs.index', $evenement->id),
        ];

        return view('evenements.create', [
            'maxCoParrainage' => $maxCoParrainage,
            'evenement' => $evenement,
            'edition' => $edition,
            'joursAvecScansReussis' => $joursAvecScansReussis,
        ]);
    }

    // Valeurs de l'événement au format des champs de l'assistant.
    private function valeursAssistant(Evenement $evenement): array
    {
        $categories = ['Festival', 'Gala', 'Match', 'Webinaire'];
        $jours = $evenement->dates->sortBy('date_debut')->values();
        $multi = $jours->count() > 1;
        $indexParJour = $jours->mapWithKeys(fn ($jour, $i) => [$jour->id => $i]);
        $nomsBillets = ['Standard', 'VIP', 'VVIP', 'Étudiant', 'Prévente'];

        $tarifs = $evenement->tarifs->values()->map(function ($tarif) use ($multi, $indexParJour, $nomsBillets) {
            $ligne = [
                'id' => $tarif->id,
                'prix' => (string) (int) round((float) $tarif->prix),
                'qte' => $tarif->quantite_disponible === null ? '' : (string) $tarif->quantite_disponible,
            ];
            if ($multi) {
                $ligne['nom'] = $tarif->nom;
                $ligne['jour'] = $tarif->evenement_date_id !== null && $indexParJour->has($tarif->evenement_date_id)
                    ? (string) $indexParJour[$tarif->evenement_date_id]
                    : 'pack';
            } elseif (in_array($tarif->nom, $nomsBillets, true)) {
                $ligne['nom'] = $tarif->nom;
            } else {
                $ligne['nom'] = 'Personnalisé';
                $ligne['nom_perso'] = $tarif->nom;
            }

            return $ligne;
        })->all();

        $categorieConnue = in_array($evenement->categorie, $categories, true);

        return [
            'type_evenement' => $evenement->type_evenement ?: 'spectacle',
            'categorie' => $categorieConnue ? $evenement->categorie : 'Autre',
            'autre_categorie' => $categorieConnue ? '' : (string) $evenement->categorie,
            'titre' => $evenement->titre,
            'description' => (string) $evenement->description,
            'gratuit' => $evenement->gratuit ? '1' : '0',
            'billetterie_plus_tard' => $evenement->billetteriePasOuverte() ? '1' : null,
            'billetterie_ouvre_le' => $evenement->billetteriePasOuverte() ? $evenement->billetterie_ouvre_le?->format('Y-m-d\TH:i') : null,
            'duree' => $multi ? 'plusieurs' : 'un',
            'date_event' => $evenement->date_event->format('Y-m-d\TH:i'),
            'jours' => $multi ? $jours->map(fn ($jour) => $jour->date_debut->format('Y-m-d\TH:i'))->all() : [],
            'meme_capacite' => $jours->every(fn ($jour) => $jour->capacite === null) ? '1' : null,
            'capacites_jours' => $multi ? $jours->map(fn ($jour) => $jour->capacite === null ? '' : (string) $jour->capacite)->all() : [],
            'lieu' => $evenement->lieu,
            'capacite' => (string) $evenement->capacite,
            'tarifs' => $evenement->gratuit ? [] : $tarifs,
            'co_parrainage_actif' => $evenement->co_parrainage_actif ? '1' : null,
            'co_parrainage_pourcentage' => $evenement->co_parrainage_pourcentage,
            'statut' => $evenement->statut,
        ];
    }

    // Met à jour un événement existant
    public function update(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        if ($evenement->modificationGlobaleVerrouillee()) {
            return redirect()->route('admin.evenements.show', $evenement)
                ->with('error', 'Cet événement est verrouillé : des scans ont été effectués et le délai de 12 heures après sa dernière date est écoulé.');
        }

        // Formulaire en étapes (identique à la création) : on traduit les jours
        // saisis en date_event + dates_supplementaires.
        $assistant = $request->has('duree');
        $donneesAssistant = $assistant ? $this->validerAssistantEdition($request) : null;

        // Co-parrainage : commission offerte aux affiliés, plafonnée à (100 % − commission PaxEvent)
        $commissionPax = $evenement->commissionEffective();
        $maxCoParrainage = max(0, 100 - $commissionPax);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'date_event' => 'required|date',
            'lieu' => 'required|string|max:255',
            'categorie' => 'required',
            'autre_categorie' => 'nullable|string|max:255',
            'capacite' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'statut' => 'required|in:brouillon,publié',
            'type_evenement' => 'required|in:spectacle,formation,conference',
            'gratuit' => 'nullable|boolean',
            'co_parrainage_actif' => 'nullable|boolean',
            'co_parrainage_pourcentage' => "nullable|numeric|min:1|max:{$maxCoParrainage}|required_if:co_parrainage_actif,1",
            'billetterie_plus_tard' => 'nullable|boolean',
            'billetterie_ouvre_le' => 'nullable|date',
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
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
            'statut.required' => 'Le statut est obligatoire.',
            'co_parrainage_pourcentage.required_if' => 'Indiquez la commission à reverser aux affiliés.',
            'co_parrainage_pourcentage.min' => 'La commission de co-parrainage doit être d\'au moins 1 %.',
            'co_parrainage_pourcentage.max' => "La commission de co-parrainage ne peut pas dépasser {$maxCoParrainage} % (100 % moins votre commission PaxEvent de {$commissionPax} %).",
        ]);

        $totalPlacesTarifs = (int) $evenement->tarifs()->sum('quantite_disponible');
        $multiJourDemande = count($this->extraireDatesSupplementaires($request, $validated['date_event'])) > 0;
        if ($assistant) {
            // Contrôlé après coup, une fois jours, jauges et tarifs appliqués.
        } elseif ($evenement->estMultiJour() || $multiJourDemande) {
            // Multijours : la jauge de référence est celle de chaque jour, et
            // quota_vendu cumule toutes les ventes de l'événement. On ne peut
            // donc pas le comparer à la capacité globale.
            if ($totalPlacesTarifs > 0 && (int) $validated['capacite'] <= 0) {
                throw ValidationException::withMessages([
                    'capacite' => 'Indiquez une capacité globale de repli pour les jours sans jauge propre.',
                ]);
            }

            // Les jours sans jauge propre utilisent cette capacité : elle doit
            // couvrir leurs places occupées et les stocks de leurs tarifs.
            foreach ($evenement->dates()->whereNull('capacite')->get() as $jour) {
                $minimum = max($evenement->occupationJour($jour), $evenement->stocksTarifsPourJour($jour));

                if ((int) $validated['capacite'] < $minimum) {
                    throw ValidationException::withMessages([
                        'capacite' => "Le {$jour->libelle()} utilise la capacité de l'événement : elle ne peut pas descendre sous {$minimum} places (places occupées ou prévues par les tarifs de ce jour et les packs).",
                    ]);
                }
            }
        } elseif (
            (int) $validated['capacite'] < (int) $evenement->quota_vendu
            || $totalPlacesTarifs > (int) $validated['capacite']
        ) {
            throw ValidationException::withMessages([
                'capacite' => "La capacité ne peut pas être inférieure aux places déjà vendues ({$evenement->quota_vendu}) ou à la somme des places des tarifs ({$totalPlacesTarifs}).",
            ]);
        }

        if ($validated['categorie'] === 'Autre' && !empty($validated['autre_categorie'])) {
            $validated['categorie'] = $validated['autre_categorie'];
        }
        unset($validated['autre_categorie']);

        $validated['gratuit'] = $request->boolean('gratuit');

        // Normalise le co-parrainage : impossible sur un événement gratuit, null si désactivé
        $validated['co_parrainage_actif'] = !$validated['gratuit'] && $request->boolean('co_parrainage_actif');
        $validated['co_parrainage_pourcentage'] = $validated['co_parrainage_actif']
            ? (float) $validated['co_parrainage_pourcentage']
            : null;

        $datesData = $this->extraireDatesSupplementaires($request, $validated['date_event']);
        // Seuls les jours déjà scannés sont figés : tarifs et billets suivent le jour déplacé.
        $joursAvecScansReussis = $evenement->joursAvecScansReussis()
            ->unique()
            ->values();

        if ($this->datesScanneesModifiees($evenement, $validated['date_event'], $datesData, $joursAvecScansReussis)) {
            $dateEventDemandeeEstVerrouillee = $joursAvecScansReussis->contains(
                Carbon::parse($validated['date_event'])->toDateString()
            );
            $dateEventActuelleEstVerrouillee = $joursAvecScansReussis->contains(
                $evenement->date_event->toDateString()
            );
            $attribut = $dateEventDemandeeEstVerrouillee || $dateEventActuelleEstVerrouillee
                ? 'date_event'
                : 'dates_supplementaires';

            throw ValidationException::withMessages([
                $attribut => 'Une date ayant déjà enregistré un scan ne peut être modifiée, déplacée ou supprimée.',
            ]);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('evenements', 'public');
        }

        $anciennesDates = $this->datesNormalisees($evenement);

        // L'assistant porte l'option de billetterie ; l'ancien formulaire ne la touche pas.
        if ($assistant) {
            $plusTard = $request->boolean('billetterie_plus_tard');
            $validated['billetterie_ouverte'] = ! $plusTard;
            $validated['billetterie_ouvre_le'] = $plusTard && ! empty($validated['billetterie_ouvre_le'])
                ? Carbon::parse($validated['billetterie_ouvre_le'])
                : null;
        } else {
            unset($validated['billetterie_plus_tard'], $validated['billetterie_ouvre_le']);
        }
        unset($validated['billetterie_plus_tard']);

        DB::transaction(function () use ($evenement, $validated, $datesData, $assistant, $donneesAssistant, $request) {
            $evenement->update($validated);

            // Remplace les sessions de l'événement par celles saisies (la première étant date_event)
            $this->enregistrerDates($evenement, $validated['date_event'], $datesData);

            if ($assistant) {
                $this->appliquerCapacitesJours($evenement, $donneesAssistant);
            }

            if ($validated['gratuit']) {
                $evenement->tarifs()->update(['prix' => 0]);
            } elseif ($assistant) {
                $this->synchroniserTarifsAssistant($evenement, $donneesAssistant);
            } else {
                $this->synchroniserPrixTarifs($evenement, (array) $request->input('prix_tarifs', []));
            }

            if ($assistant) {
                $this->verifierCapacitesApresEdition($evenement->refresh());
            }
        });

        // Si les dates ont changé, les billets déjà émis doivent refléter les
        // nouvelles sessions : on renvoie le PDF + l'email à chaque acheteur.
        $acheteursRenotifies = $this->datesNormalisees($evenement) !== $anciennesDates
            ? $this->renvoyerBilletsDatesModifiees($evenement)
            : 0;

        $message = 'Événement modifié avec succès.';
        if ($acheteursRenotifies > 0) {
            $message .= " Les billets ont été renvoyés à {$acheteursRenotifies} acheteur(s) avec les nouvelles dates.";
        }

        return redirect()->route('admin.evenements.index')
            ->with('success', $message);
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

    // Ferme la billetterie (événement publié à l'avance, ventes pas encore
    // ouvertes) ou l'ouvre. Une date d'ouverture facultative l'ouvre toute seule.
    public function billetterie(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'action' => 'required|in:fermer,ouvrir',
            'ouvre_le' => 'nullable|date|after:now',
        ], [
            'ouvre_le.date' => 'La date d\'ouverture est invalide.',
            'ouvre_le.after' => 'La date d\'ouverture doit être dans le futur.',
        ]);

        if ($validated['action'] === 'fermer') {
            $evenement->update([
                'billetterie_ouverte' => false,
                'billetterie_ouvre_le' => $validated['ouvre_le'] ?? null,
            ]);
            $message = isset($validated['ouvre_le'])
                ? 'La billetterie est fermée, elle s\'ouvrira automatiquement à la date choisie.'
                : 'La billetterie est fermée. Rouvrez-la quand vous êtes prêt.';
        } else {
            $evenement->update(['billetterie_ouverte' => true, 'billetterie_ouvre_le' => null]);
            $message = 'La billetterie est ouverte.';
        }

        return redirect()->route('admin.evenements.show', $evenement->id)->with('success', $message);
    }

    // Active / désactive le co-parrainage directement depuis la fiche de l'événement
    public function toggleCoParrainage(Request $request, Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);

        if ($evenement->gratuit) {
            return back()->with('error', 'Le co-parrainage n\'est pas disponible pour un événement gratuit.');
        }

        if ($evenement->co_parrainage_actif) {
            $evenement->update(['co_parrainage_actif' => false]);

            return back()->with('success', 'Co-parrainage désactivé pour cet événement.');
        }

        $maxCoParrainage = max(0, 100 - $evenement->commissionEffective());

        $validated = $request->validate([
            'co_parrainage_pourcentage' => "required|numeric|min:1|max:{$maxCoParrainage}",
        ], [
            'co_parrainage_pourcentage.required' => 'Indiquez la commission à reverser aux affiliés.',
            'co_parrainage_pourcentage.min' => 'La commission de co-parrainage doit être d\'au moins 1 %.',
            'co_parrainage_pourcentage.max' => "La commission de co-parrainage ne peut pas dépasser {$maxCoParrainage} % (100 % moins votre commission PaxEvent).",
        ]);

        $evenement->update([
            'co_parrainage_actif' => true,
            'co_parrainage_pourcentage' => (float) $validated['co_parrainage_pourcentage'],
        ]);

        return back()->with('success', 'Co-parrainage activé pour cet événement.');
    }

    // Met à jour la jauge d'un jour précis (null = repli sur la capacité de l'événement)
    public function updateJourCapacite(Request $request, Evenement $evenement, EvenementDate $jour)
    {
        abort_if($evenement->user_id !== Auth::id(), 403);
        abort_if((int) $jour->evenement_id !== (int) $evenement->id, 404);

        $validated = $request->validate([
            'capacite' => 'nullable|integer|min:1',
        ], [
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être d\'au moins 1 place.',
        ]);

        $capacite = $validated['capacite'] ?? null;
        $occupees = $evenement->occupationJour($jour);

        if ($capacite !== null && (int) $capacite < $occupees) {
            throw ValidationException::withMessages([
                'capacite' => "La capacité ne peut pas être inférieure aux {$occupees} place(s) déjà occupée(s) sur ce jour.",
            ]);
        }

        // La jauge (propre ou de repli) doit couvrir les stocks des tarifs du
        // jour et des packs.
        $stocks = $evenement->stocksTarifsPourJour($jour);
        $capaciteEffective = (int) ($capacite ?? $evenement->capacite);

        if ($capaciteEffective < $stocks) {
            throw ValidationException::withMessages([
                'capacite' => "La jauge du {$jour->libelle()} ne peut pas être inférieure aux {$stocks} place(s) prévues par les tarifs de ce jour et les packs. Réduisez d'abord ces tarifs.",
            ]);
        }

        $jour->update(['capacite' => $capacite]);
        $evenement->resetCachesCapacite();

        return back()->with('success', 'Capacité du '.$jour->libelle().' mise à jour.');
    }

    // Supprime définitivement un événement
    public function destroy(Evenement $evenement)
    {
        abort_if($evenement->user_id !== Auth::id(), 403); // Vérification de propriété

        if ($evenement->suppressionOrganisateurBloquee()) {
            return redirect()->route('admin.evenements.index')
                ->with('error', 'Cet événement ne peut pas être annulé car des billets payants ont déjà été vendus. Contactez l’équipe PaxEvent pour demander une assistance.')
                ->with('evenement_suppression_bloquee', true);
        }

        $evenement->delete();

        return redirect()->route('admin.evenements.index')
            ->with('success', 'Événement supprimé avec succès.');
    }

    // Applique directement le nouveau prix aux tarifs n'ayant aucune vente
    // (les tarifs avec ventes passent obligatoirement par une approbation PaxEvent).
    // Assistant en modification : valide les champs propres aux étapes et
    // renseigne date_event / dates_supplementaires pour la suite de update().
    private function validerAssistantEdition(Request $request): array
    {
        $multi = $request->input('duree') === 'plusieurs';
        $gratuit = $request->boolean('gratuit');

        $rules = [
            'duree' => 'required|in:un,plusieurs',
            'gratuit' => 'required|boolean',
        ];
        if ($multi) {
            $rules['jours'] = 'required|array|min:2|max:'.self::MAX_JOURS;
            $rules['jours.*'] = 'required|date|distinct';
            $rules['meme_capacite'] = 'nullable|boolean';
            $rules['capacites_jours'] = 'nullable|array';
            $rules['capacites_jours.*'] = 'nullable|integer|min:1';
        } else {
            $rules['date_event'] = 'required|date';
        }
        if (! $gratuit) {
            $rules['tarifs'] = 'required|array|min:1|max:'.self::MAX_TARIFS;
            $rules['tarifs.*.id'] = 'nullable|integer';
            $rules['tarifs.*.nom'] = 'required|string|max:100';
            $rules['tarifs.*.nom_perso'] = 'nullable|string|max:100';
            // Un jour laissé vide (sans prix) est gratuit : contrôlé plus bas.
            $rules['tarifs.*.prix'] = ($multi ? 'nullable' : 'required').'|numeric|min:0';
            $rules['tarifs.*.qte'] = 'nullable|integer|min:1';
            $rules[$multi ? 'tarifs.*.jour' : 'generer_vip'] = $multi ? 'required|string' : 'nullable|boolean';
        }

        $donnees = $request->validate($rules, [
            'jours.min' => 'Un événement sur plusieurs jours doit compter au moins 2 jours.',
            'jours.max' => 'Un événement ne peut pas dépasser '.self::MAX_JOURS.' jours.',
            'jours.*.required' => 'Renseignez la date et l\'heure de chaque jour.',
            'jours.*.distinct' => 'Deux jours ont la même date et la même heure.',
            'date_event.required' => 'La date de l\'événement est obligatoire.',
            'tarifs.required' => 'Ajoutez au moins un billet.',
            'tarifs.max' => 'Un événement ne peut pas avoir plus de '.self::MAX_TARIFS.' tarifs.',
            'tarifs.*.prix.required' => 'Indiquez le prix de chaque billet (0 pour gratuit).',
            'tarifs.*.prix.min' => 'Le prix d\'un billet ne peut pas être négatif.',
            'tarifs.*.qte.min' => 'Le nombre de places doit être d\'au moins 1.',
            'capacites_jours.*.min' => 'La capacité d\'un jour doit être d\'au moins 1 place.',
        ]);

        if ($multi) {
            $ordre = collect($donnees['jours'])
                ->map(fn ($date, $index) => ['index' => (string) $index, 'date' => Carbon::parse($date)->toDateTimeString()])
                ->sortBy('date')
                ->values();
            $positionParIndex = $ordre->mapWithKeys(fn ($ligne, $position) => [$ligne['index'] => $position]);
            $request->merge([
                'date_event' => $ordre->first()['date'],
                'dates_supplementaires' => $ordre->slice(1)->pluck('date')->values()->all(),
            ]);
        } else {
            $positionParIndex = collect(['0' => 0]);
            $request->merge(['dates_supplementaires' => []]);
        }

        $capacites = [];
        if ($multi && ! $request->boolean('meme_capacite')) {
            foreach ($positionParIndex as $index => $position) {
                $valeur = $donnees['capacites_jours'][$index] ?? null;
                $capacites[$position] = ($valeur === null || $valeur === '') ? null : (int) $valeur;
            }
        }

        $tarifs = [];
        foreach (array_values($donnees['tarifs'] ?? []) as $i => $tarif) {
            $nom = $tarif['nom'] === 'Personnalisé' ? trim((string) ($tarif['nom_perso'] ?? '')) : trim($tarif['nom']);
            if ($nom === '') {
                throw ValidationException::withMessages(["tarifs.{$i}.nom_perso" => 'Indiquez le nom du billet personnalisé.']);
            }
            $position = null;
            if ($multi && (string) $tarif['jour'] !== 'pack') {
                if (! $positionParIndex->has((string) $tarif['jour'])) {
                    throw ValidationException::withMessages(["tarifs.{$i}.jour" => 'Le jour choisi pour un billet est invalide.']);
                }
                $position = $positionParIndex[(string) $tarif['jour']];
            }
            $nouveau = ! isset($tarif['id']) || $tarif['id'] === '';
            $prixVide = ! isset($tarif['prix']) || $tarif['prix'] === '';
            if ($prixVide && ! ($multi && $position !== null)) {
                throw ValidationException::withMessages(["tarifs.{$i}.prix" => 'Indiquez le prix de chaque billet (0 pour gratuit).']);
            }
            $tarifs[] = [
                'id' => ! $nouveau ? (int) $tarif['id'] : null,
                'nom' => $nom,
                'prix' => $prixVide ? 0.0 : round((float) $tarif['prix']),
                'qte' => isset($tarif['qte']) && $tarif['qte'] !== '' ? (int) $tarif['qte'] : null,
                'position' => $position,
            ];
        }

        return [
            'multi' => $multi,
            'meme_capacite' => ! $multi || $request->boolean('meme_capacite'),
            'capacites' => $capacites,
            'tarifs' => $tarifs,
            'generer_vip' => ! $multi && $request->boolean('generer_vip'),
        ];
    }

    // Jours de l'événement dans l'ordre chronologique (positions de l'assistant).
    private function joursChronologiques(Evenement $evenement): Collection
    {
        return $evenement->dates()->reorder('date_debut')->get()->values();
    }

    private function appliquerCapacitesJours(Evenement $evenement, array $donnees): void
    {
        if (! $donnees['multi']) {
            return;
        }

        foreach ($this->joursChronologiques($evenement) as $position => $jour) {
            $capacite = $donnees['meme_capacite'] ? null : ($donnees['capacites'][$position] ?? null);
            if ($jour->capacite !== $capacite) {
                $jour->update(['capacite' => $capacite]);
            }
        }
    }

    // Tarifs saisis dans l'assistant : mise à jour des existants, création des
    // nouveaux, suppression des retirés. Le prix d'un tarif vendu reste
    // modifiable (les billets émis gardent leur montant) ; il ne peut en
    // revanche ni être supprimé ni descendre sous ses ventes.
    private function synchroniserTarifsAssistant(Evenement $evenement, array $donnees): void
    {
        $multi = $donnees['multi'];
        $jours = $this->joursChronologiques($evenement);
        $existants = $evenement->tarifs()->get()->keyBy('id');
        $conserves = collect();

        foreach ($donnees['tarifs'] as $i => $t) {
            if ($t['id'] !== null) {
                $tarif = $existants->get($t['id']);
                if (! $tarif) {
                    throw ValidationException::withMessages(['tarifs' => 'Un des tarifs envoyés n\'appartient pas à cet événement.']);
                }
                $conserves->push($tarif->id);

                // Le prix reste modifiable après des ventes : les billets déjà émis
                // gardent le montant payé, seuls les achats futurs changent.
                $maj = ['prix' => $t['prix']];
                if ((int) $tarif->quantite_vendue === 0 && ! $multi) {
                    $maj['nom'] = $t['nom'];
                }
                $jourGratuitExistant = $multi && $t['position'] !== null && (float) $t['prix'] === 0.0;
                if ($t['qte'] === null && $jourGratuitExistant && (int) $tarif->quantite_vendue === 0) {
                    $maj['quantite_disponible'] = null; // jour gratuit sans limite propre
                } elseif ($t['qte'] === null) {
                    if ($tarif->quantite_disponible !== null) {
                        throw ValidationException::withMessages(["tarifs.{$i}.qte" => "Indiquez le nombre de places du tarif « {$tarif->nom} »."]);
                    }
                } else {
                    if ($t['qte'] < (int) $tarif->quantite_vendue) {
                        throw ValidationException::withMessages(["tarifs.{$i}.qte" => "Le tarif « {$tarif->nom} » a déjà {$tarif->quantite_vendue} place(s) vendue(s) : sa quantité ne peut pas descendre en dessous."]);
                    }
                    $maj['quantite_disponible'] = $t['qte'];
                }
                if ($maj) {
                    $tarif->update($maj);
                }

                continue;
            }

            // Jour gratuit laissé vide : billet gratuit sans limite propre (la jauge du jour s'applique).
            $jourGratuit = $multi && $t['position'] !== null && (float) $t['prix'] === 0.0;
            if ($t['qte'] === null && ! $jourGratuit) {
                throw ValidationException::withMessages(["tarifs.{$i}.qte" => 'Indiquez le nombre de places de chaque nouveau billet.']);
            }

            $dateId = null;
            $nom = $t['nom'];
            if ($multi && $t['position'] !== null) {
                $jour = $jours[$t['position']] ?? null;
                if (! $jour) {
                    throw ValidationException::withMessages(["tarifs.{$i}.jour" => 'Le jour choisi pour un billet est invalide.']);
                }
                if ($evenement->tarifs()->where('evenement_date_id', $jour->id)->exists()) {
                    throw ValidationException::withMessages(['tarifs' => 'Un seul tarif par jour : utilisez les packs pour proposer une autre formule.']);
                }
                $dateId = $jour->id;
                $nom = 'Jour '.($t['position'] + 1);
            } elseif ($multi) {
                if (! in_array($nom, self::PACKS, true) || $evenement->tarifs()->whereNull('evenement_date_id')->where('nom', $nom)->exists()) {
                    throw ValidationException::withMessages(['tarifs' => 'Les packs proposés sont « Pack Simple » et « Pack VIP », une fois chacun.']);
                }
            }

            $conserves->push($evenement->tarifs()->create([
                'evenement_date_id' => $dateId,
                'nom' => $nom,
                'prix' => $t['prix'],
                'quantite_disponible' => $t['qte'],
                'quantite_vendue' => 0,
                'statut' => 'actif',
            ])->id);
        }

        foreach ($existants as $tarif) {
            if ($conserves->contains($tarif->id)) {
                continue;
            }
            if ((int) $tarif->quantite_vendue > 0 || $tarif->tickets()->exists()) {
                throw ValidationException::withMessages(['tarifs' => "Le tarif « {$tarif->nom} » a déjà des billets : il ne peut pas être supprimé."]);
            }
            $tarif->delete();
        }

        if ($donnees['generer_vip'] && ! $evenement->tarifs()->where('nom', 'VIP')->exists()) {
            $premier = $evenement->tarifs()->orderBy('id')->first();
            $evenement->tarifs()->create([
                'nom' => 'VIP',
                'prix' => ($premier?->prix ?? 0) * 2,
                'quantite_disponible' => null,
                'quantite_vendue' => 0,
                'statut' => 'actif',
            ]);
        }

        if ($evenement->tarifs()->count() > self::MAX_TARIFS) {
            throw ValidationException::withMessages(['tarifs' => 'Un événement ne peut pas avoir plus de '.self::MAX_TARIFS.' tarifs.']);
        }
    }

    // Après modification : chaque jauge couvre ses places occupées et les
    // stocks des tarifs qui la concernent.
    private function verifierCapacitesApresEdition(Evenement $evenement): void
    {
        $evenement->resetCachesCapacite();
        $jours = $this->joursChronologiques($evenement);

        if ($jours->count() <= 1) {
            $stocks = (int) $evenement->tarifs()->sum('quantite_disponible');
            if ((int) $evenement->capacite < (int) $evenement->quota_vendu || $stocks > (int) $evenement->capacite) {
                throw ValidationException::withMessages([
                    'capacite' => "La capacité ne peut pas être inférieure aux places déjà vendues ({$evenement->quota_vendu}) ou à la somme des places des tarifs ({$stocks}).",
                ]);
            }

            return;
        }

        foreach ($jours as $position => $jour) {
            $capacite = (int) ($jour->capacite ?? $evenement->capacite);
            $numero = $position + 1;
            $occupees = $evenement->occupationJour($jour);
            if ($capacite < $occupees) {
                throw ValidationException::withMessages(['capacite' => "Jour {$numero} : la capacité ({$capacite}) est inférieure aux {$occupees} place(s) déjà occupée(s)."]);
            }
            $stocks = $evenement->stocksTarifsPourJour($jour);
            if ($stocks > $capacite) {
                throw ValidationException::withMessages(['tarifs' => "Jour {$numero} : les billets du jour et les packs font {$stocks} places, au-delà de la capacité du jour ({$capacite} places)."]);
            }
        }
    }

    private function synchroniserPrixTarifs(Evenement $evenement, array $prixTarifs): void
    {
        foreach ($prixTarifs as $tarifId => $prix) {
            $tarif = $evenement->tarifs()->find((int) $tarifId);
            if (!$tarif) {
                continue;
            }

            // Le prix reste modifiable après des ventes : les billets déjà
            // émis gardent le montant payé, seuls les achats futurs changent.
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

    private function datesScanneesModifiees(
        Evenement $evenement,
        string $premiereDate,
        array $datesSupplementaires,
        Collection $joursVerrouilles
    ): bool
    {
        if ($joursVerrouilles->isEmpty()) {
            return false;
        }

        $normaliser = fn ($date) => Carbon::parse($date)->format('Y-m-d H:i');
        if (
            $joursVerrouilles->contains($evenement->date_event->toDateString())
            && $normaliser($premiereDate) !== $normaliser($evenement->date_event)
        ) {
            return true;
        }

        $datesDemandees = collect(array_merge([$premiereDate], $datesSupplementaires))
            ->map($normaliser);
        $datesExistantes = $evenement->dates()
            ->get()
            ->map(fn ($date) => $normaliser($date->date_debut));
        $dateEventExistante = $normaliser($evenement->date_event);
        if (! $datesExistantes->contains($dateEventExistante)) {
            $datesExistantes->push($dateEventExistante);
        }

        foreach ($joursVerrouilles as $jourVerrouille) {
            $datesExistantesDuJour = $datesExistantes
                ->filter(fn ($date) => Carbon::parse($date)->toDateString() === $jourVerrouille);

            $datesDemandeesDuJour = $datesDemandees
                ->filter(fn ($date) => Carbon::parse($date)->toDateString() === $jourVerrouille);

            if ($datesExistantesDuJour->sort()->values()->all() !== $datesDemandeesDuJour->sort()->values()->all()) {
                return true;
            }
        }

        return false;
    }

    // Date/heure de chaque session, normalisée pour comparer deux états.
    private function datesNormalisees(Evenement $evenement): array
    {
        $dates = $evenement->dates()
            ->pluck('date_debut')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d H:i'))
            ->all();

        if ($dates === [] && $evenement->date_event) {
            $dates[] = $evenement->date_event->format('Y-m-d H:i');
        }

        sort($dates);

        return $dates;
    }

    // Renvoie les billets (email + PDF) aux acheteurs déjà servis lorsque les
    // dates de l'événement changent. Retourne le nombre d'acheteurs concernés.
    private function renvoyerBilletsDatesModifiees(Evenement $evenement): int
    {
        $tickets = $evenement->tickets()
            ->where('statut_paiement', 'payé')
            ->where(fn ($query) => $query->whereNull('annule')->orWhere('annule', false))
            ->whereNotNull('email_acheteur')
            ->get(['id', 'email_acheteur']);

        $tickets->groupBy('email_acheteur')->each(function ($groupe, $email) {
            SendTicketEmailJob::dispatch($groupe->pluck('id')->all(), (string) $email);
        });

        return $tickets->pluck('email_acheteur')->unique()->count();
    }

    // Synchronise les sessions de l'événement : la première = date_event, puis les dates supplémentaires.
    // Les lignes existantes sont conservées (upsert) pour que les tarifs et billets
    // rattachés à un jour gardent leur evenement_date_id. Seules les dates
    // réellement retirées et sans dépendance sont supprimées.
    private function enregistrerDates(Evenement $evenement, string $premiereDate, array $datesSupplementaires): void
    {
        $toutes = array_merge([\Carbon\Carbon::parse($premiereDate)->toDateTimeString()], $datesSupplementaires);
        $toutes = array_values(array_unique($toutes));

        DB::transaction(function () use ($evenement, $toutes) {
            $existantes = $evenement->dates()->get();

            // 1) Correspondance exacte date/heure : l'identifiant du jour est conservé.
            $restantes = collect($toutes)->map(fn ($date) => [
                'date_debut' => $date,
                'existante' => $existantes->first(
                    fn ($jour) => $jour->date_debut->toDateTimeString() === $date
                ),
            ]);

            // 2) Repli sur le même jour calendaire : un simple décalage d'heure ne
            //    doit pas casser la capacité déjà réglée ni les rattachements.
            $utilisees = $restantes->pluck('existante')->filter()->pluck('id');
            $restantes = $restantes->map(function (array $ligne) use ($existantes, $utilisees) {
                if ($ligne['existante'] !== null) {
                    return $ligne;
                }

                $ligne['existante'] = $existantes->first(
                    fn ($jour) => ! $utilisees->contains($jour->id)
                        && $jour->date_debut->toDateString() === Carbon::parse($ligne['date_debut'])->toDateString()
                );

                if ($ligne['existante'] !== null) {
                    $utilisees->push($ligne['existante']->id);
                }

                return $ligne;
            });

            // 3) Repli par position : un jour déplacé vers une autre date (autre jour
            //    calendaire) garde son identifiant, donc ses tarifs et ses billets.
            //    Seuls les jours portant des tarifs ou des billets sont concernés, attribués
            //    dans l'ordre chronologique aux dates demandées sans correspondance ; un jour
            //    vide remplacé par une autre date reste un simple retrait + ajout.
            $libres = $existantes
                ->reject(fn ($jour) => $utilisees->contains($jour->id))
                ->filter(fn ($jour) => $jour->tarifs()->exists() || $jour->tickets()->exists())
                ->sortBy('date_debut')
                ->values();
            $restantes = $restantes->map(function (array $ligne) use (&$libres, $utilisees) {
                if ($ligne['existante'] !== null || $libres->isEmpty()) {
                    return $ligne;
                }

                $ligne['existante'] = $libres->shift();
                $utilisees->push($ligne['existante']->id);

                return $ligne;
            });

            // Identifiants à garder : jours existants réutilisés ET jours créés
            // ci-dessous (sinon l'étape 3 supprimerait les nouvelles dates).
            $conservees = collect();

            foreach ($restantes->values() as $i => $ligne) {
                if ($ligne['existante'] !== null) {
                    $ligne['existante']->update([
                        'date_debut' => $ligne['date_debut'],
                        'ordre' => $i,
                    ]);
                    $conservees->push($ligne['existante']->id);

                    continue;
                }

                $conservees->push($evenement->dates()->create([
                    'date_debut' => $ligne['date_debut'],
                    'ordre' => $i,
                ])->id);
            }

            // 4) Suppression des jours retirés, en refusant de casser un jour qui
            //    porte encore des tarifs ou des billets.
            $evenement->dates()
                ->whereNotIn('id', $conservees->all())
                ->get()
                ->each(function ($jour) {
                    if ($jour->tarifs()->exists() || $jour->tickets()->exists()) {
                        throw ValidationException::withMessages([
                            'dates_supplementaires' => 'Un jour rattaché à des tarifs ou des billets ne peut pas être supprimé.',
                        ]);
                    }

                    $jour->delete();
                });
        });

        $evenement->unsetRelation('dates');
        $evenement->resetCachesCapacite();
    }
}
