<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\AgentAssigned;
use App\Models\Agent;
use App\Models\AgentVente;
use App\Models\Evenement;
use App\Models\Log;
use App\Models\User;
use App\Support\UploadError;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Événements internes : créés par le super admin pour des événements qui ne passent pas
 * par la billetterie en ligne. Toujours masqués du public, rattachés au compte interne,
 * ils servent à générer des tickets physiques scannables et à affecter des agents.
 */
class EvenementInterneController extends Controller
{
    private const MAX_TARIFS = 10;

    private const MAX_DATES = 10;

    public function index(): View
    {
        $evenements = Evenement::where('interne', true)
            ->with('dates')
            ->withCount(['tarifs', 'agents'])
            ->withCount(['tickets as nb_tickets' => fn ($q) => $q->whereNotNull('lot_physique_id')->where('annule', false)])
            ->orderByDesc('date_event')
            ->paginate(20);

        $entres = self::billetsEntres($evenements->getCollection()->pluck('id'));
        $evenements->getCollection()->each(fn ($e) => $e->nb_scannes = $entres[$e->id] ?? 0);

        return view('superadmin.evenements-internes.index', compact('evenements'));
    }

    // Nombre de billets distincts ayant au moins un scan validé, par événement
    private static function billetsEntres(\Illuminate\Support\Collection $evenementIds): array
    {
        if ($evenementIds->isEmpty()) {
            return [];
        }

        return Log::query()
            ->join('ticket', 'ticket.id', '=', 'logs.ticket_id')
            ->where('logs.type_operation', 'scan')
            ->where('logs.details->resultat', 'valide')
            ->whereIn('ticket.evenement_id', $evenementIds)
            ->groupBy('ticket.evenement_id')
            ->selectRaw('ticket.evenement_id, COUNT(DISTINCT logs.ticket_id) as total')
            ->pluck('total', 'ticket.evenement_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public function create(): View
    {
        return view('superadmin.evenements-internes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($erreur = UploadError::pour($request, 'image')) {
            return back()->withInput()->withErrors(['image' => $erreur]);
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'auteur_prenom' => 'required|string|max:100',
            'auteur_nom' => 'required|string|max:100',
            'lieu' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'capacite' => 'required|integer|min:1|max:1000000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'dates' => 'required|array|min:1|max:'.self::MAX_DATES,
            'dates.*' => 'required|date',
            'tarifs' => 'required|array|min:1|max:'.self::MAX_TARIFS,
            'tarifs.*.nom' => 'required|string|max:100',
            'tarifs.*.prix' => 'nullable|numeric|min:0|max:100000000',
        ], [
            'titre.required' => 'Le nom de l\'événement est obligatoire.',
            'auteur_prenom.required' => 'Le prénom de l\'auteur est obligatoire.',
            'auteur_nom.required' => 'Le nom de l\'auteur est obligatoire.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.min' => 'La capacité doit être d\'au moins 1.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Formats acceptés : JPG, PNG ou WEBP.',
            'image.max' => 'L\'image ne doit pas dépasser 5 Mo.',
            'dates.required' => 'Indiquez au moins une date.',
            'dates.max' => 'Maximum '.self::MAX_DATES.' dates.',
            'dates.*.required' => 'Chaque date doit être renseignée.',
            'dates.*.date' => 'Une des dates est invalide.',
            'tarifs.required' => 'Ajoutez au moins un tarif.',
            'tarifs.max' => 'Maximum '.self::MAX_TARIFS.' tarifs.',
            'tarifs.*.nom.required' => 'Chaque tarif doit avoir un nom.',
            'tarifs.*.prix.numeric' => 'Le prix d\'un tarif doit être un nombre.',
            'tarifs.*.prix.min' => 'Le prix d\'un tarif ne peut pas être négatif.',
        ]);

        $dates = collect($validated['dates'])
            ->map(fn ($d) => Carbon::parse($d))
            ->unique(fn (Carbon $d) => $d->toDateTimeString())
            ->sort()
            ->values();

        $noms = collect($validated['tarifs'])->map(fn ($t) => mb_strtolower(trim($t['nom'])));
        if ($noms->unique()->count() !== $noms->count()) {
            return back()->withInput()->withErrors(['tarifs' => 'Deux tarifs ne peuvent pas porter le même nom.']);
        }

        $image = $request->hasFile('image') ? $request->file('image')->store('evenements', 'public') : null;
        $proprietaire = User::compteInterne();

        $evenement = DB::transaction(function () use ($validated, $dates, $image, $proprietaire) {
            $evenement = Evenement::create([
                'user_id' => $proprietaire->id,
                'interne' => true,
                'auteur_prenom' => trim($validated['auteur_prenom']),
                'auteur_nom' => trim($validated['auteur_nom']),
                'titre' => trim($validated['titre']),
                'description' => $validated['description'] ?? null,
                'lieu' => trim($validated['lieu']),
                'capacite' => (int) $validated['capacite'],
                'date_event' => $dates->first(),
                'image' => $image,
                // « publié » pour que le scan fonctionne ; « interne » le masque du public
                'statut' => 'publié',
                'type_evenement' => 'spectacle',
                'gratuit' => collect($validated['tarifs'])->every(fn ($t) => (float) ($t['prix'] ?? 0) <= 0),
                'billetterie_ouverte' => false,
                'co_parrainage_actif' => false,
            ]);

            foreach ($dates as $ordre => $date) {
                $evenement->dates()->create(['date_debut' => $date, 'ordre' => $ordre]);
            }

            foreach (array_values($validated['tarifs']) as $tarif) {
                $evenement->tarifs()->create([
                    'nom' => trim($tarif['nom']),
                    'prix' => (float) ($tarif['prix'] ?? 0),
                    'quantite_disponible' => null,
                    'statut' => 'actif',
                ]);
            }

            return $evenement;
        });

        return redirect()->route('superadmin.evenements-internes.voir', $evenement)
            ->with('success', 'Événement interne créé. Il est masqué du public.');
    }

    public function show(Evenement $evenement): View
    {
        abort_unless($evenement->interne, 404);

        $evenement->load('dates', 'tarifs', 'agents');
        $lots = $evenement->lotsPhysiques()->with('tarif')->latest()->get();

        // Scans des agents de cet événement (journal « scan »)
        $agentIds = $evenement->agents->pluck('id');
        $scans = Log::with('agent:id,nom', 'ticket:id,code_unique,nom_tarif')
            ->where('type_operation', 'scan')
            ->whereIn('agent_id', $agentIds)
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'page_scans')
            ->withQueryString();

        // Compteurs par agent : total, entrées validées, refus, dernier scan
        $statsAgents = Log::where('type_operation', 'scan')
            ->whereIn('agent_id', $agentIds)
            ->get(['agent_id', 'details', 'created_at'])
            ->groupBy('agent_id')
            ->map(fn ($logs) => [
                'total' => $logs->count(),
                'valides' => $logs->filter(fn ($l) => ($l->details['resultat'] ?? null) === 'valide')->count(),
                'dernier' => $logs->max('created_at'),
            ]);

        // Les entrées sont tracées dans le journal des scans (par jour pour les événements multi-dates)
        $resume = [
            'billets' => $evenement->tickets()->whereNotNull('lot_physique_id')->where('annule', false)->count(),
            'entres' => self::billetsEntres(collect([$evenement->id]))[$evenement->id] ?? 0,
        ];

        return view('superadmin.evenements-internes.show', compact('evenement', 'lots', 'scans', 'statsAgents', 'resume'));
    }

    // Crée un agent de scan ; ses identifiants lui sont envoyés par email.
    // Pas d'agent de vente : ces événements ne vendent rien, PaxEvent imprime seulement les QR codes.
    public function ajouterAgent(Request $request, Evenement $evenement): RedirectResponse
    {
        abort_unless($evenement->interne, 404);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ], [
            'nom.required' => 'Le nom de l\'agent est obligatoire.',
            'email.required' => 'L\'email de l\'agent est obligatoire.',
            'email.email' => 'L\'email de l\'agent est invalide.',
        ]);

        // Un email ne sert qu'à un seul agent, scan ou vente
        if (Agent::where('email', $validated['email'])->exists() || AgentVente::where('email', $validated['email'])->exists()) {
            return back()->withInput()->with('error', 'Cet email est déjà utilisé par un autre agent.');
        }

        $motDePasse = Str::random(10);
        $codeAcces = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $agent = Agent::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($motDePasse),
            'evenement_id' => $evenement->id,
            'code_acces' => Hash::make($codeAcces),
            'actif' => true,
        ]);
        $mail = new AgentAssigned($agent, $motDePasse, $codeAcces);
        $libelle = 'Agent de scan';

        try {
            Mail::to($agent->email)->send($mail);
        } catch (\Throwable $e) {
            FacadesLog::warning('Email agent (événement interne) non envoyé : '.$e->getMessage());

            return back()->with('error', "{$libelle} créé, mais l'email n'a pas pu être envoyé. Mot de passe provisoire : {$motDePasse}");
        }

        return back()->with('success', "{$libelle} créé. Ses identifiants lui ont été envoyés par email.");
    }

    // Active ou désactive un agent de scan de l'événement
    public function basculerAgent(Evenement $evenement, int $id): RedirectResponse
    {
        abort_unless($evenement->interne, 404);

        $agent = Agent::where('evenement_id', $evenement->id)
            ->findOrFail($id);
        $agent->update(['actif' => ! $agent->actif]);

        return back()->with('success', $agent->actif ? 'Agent réactivé.' : 'Agent désactivé.');
    }
}
