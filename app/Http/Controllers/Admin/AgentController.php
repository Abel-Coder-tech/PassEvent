<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evenement;
use App\Mail\AgentAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    // Liste tous les agents de scan appartenant à l'organisateur connecté
    public function index()
    {
        $agents = Agent::with('evenement')->whereIn('evenement_id', auth()->user()->evenements->pluck('id'))->orderBy('created_at', 'desc')->get(); // Agents liés aux événements de l'utilisateur
        return view('admin.agents.index', compact('agents'));
    }

    // Affiche le formulaire de création d'un agent de scan
    public function create()
    {
        $evenements = auth()->user()->evenements;
        return view('admin.agents.create', compact('evenements'));
    }

    // Crée un nouvel agent de scan après validation et vérification des limites
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email',
            'password' => 'required|string|min:8',
            'evenement_id' => ['required', Rule::exists('evenement', 'id')->where(function ($q) {
                $q->where('user_id', auth()->id());
            })],
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé par un autre agent.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'evenement_id.exists' => 'L\'événement sélectionné est invalide.',
        ]);

        $evenement = Evenement::findOrFail($request->evenement_id);

        $nbActifs = $evenement->agents()->where('actif', true)->count();
        if ($nbActifs >= 2) { // Limite de 2 agents actifs par événement
            return back()->with('error', "Maximum de 2 agents de scan atteint pour cet événement. Désactivez d'abord un agent existant avant d'en créer un nouveau.");
        }

        $emailExiste = \App\Models\AgentVente::where('email', $request->email)->exists();
        if ($emailExiste) { // Un email ne peut pas être utilisé pour un scan ET une vente
            return back()->with('error', 'Cet email est déjà utilisé par un agent de vente. Un agent ne peut pas être à la fois scan et vente.');
        }

        $codeAcces = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // Génère un code PIN à 6 chiffres

        $agent = Agent::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'evenement_id' => $evenement->id,
            'code_acces' => $codeAcces,
            'actif' => true,
        ]);

        try {
            Mail::to($agent->email)->send(new AgentAssigned($agent, $request->password));
        } catch (\Exception $e) {
            // L'agent est créé même si l'email échoue
        }

        return redirect()->route('admin.agents.index')->with('success', 'Agent créé avec succès. Un email lui a été envoyé.');
    }

    // Affiche les détails et statistiques d'un agent de scan
    public function show(Agent $agent)
    {
        if ($agent->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }

        $agent->load('logs.ticket', 'evenement');

        // Regroupe les scans par résultat (valide, déjà utilisé, invalide)
        $scansGroupes = $agent->logs()
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(details, "$.resultat")) as resultat, COUNT(*) as total')
            ->groupBy('resultat')
            ->get()
            ->keyBy('resultat');

        $stats = [
            'total_scans' => $agent->logs()->count(),
            'scans_ajd' => $agent->logs()->whereDate('created_at', today())->count(),
            'valides' => (int) ($scansGroupes['valide']->total ?? 0),
            'deja_utilises' => (int) ($scansGroupes['deja_utilise']->total ?? 0),
            'invalides' => (int) ($scansGroupes['invalide']->total ?? 0),
            'dernier_acces' => $agent->dernier_acces,
        ];

        $logs = $agent->logs()->latest()->paginate(50);

        return view('admin.agents.show', compact('agent', 'stats', 'logs'));
    }

    // Active ou désactive un agent de scan
    public function toggleActif(Agent $agent)
    {
        if ($agent->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }
        $agent->update(['actif' => !$agent->actif]); // Bascule le statut
        $statut = $agent->actif ? 'activé' : 'désactivé';
        return redirect()->route('admin.agents.index')->with('success', "Agent {$statut} avec succès.");
    }

    // Supprime définitivement un agent de scan
    public function destroy(Agent $agent)
    {
        if ($agent->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Agent supprimé avec succès.');
    }
}
