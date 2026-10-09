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
        $agents = Agent::with('evenement')->whereIn('evenement_id', auth()->user()->evenements->pluck('id'))->orderBy('created_at', 'desc')->paginate(\App\Support\PerPage::resolve()); // Agents liés aux événements de l'utilisateur
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
            'password' => 'required|string|min:8|max:255',
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
        $limite = $evenement->limiteAgentsScan();
        if ($limite !== null && $nbActifs >= $limite) {
            return back()->with('error', "Maximum de {$limite} agents de scan atteint pour cet événement. Désactivez d'abord un agent existant avant d'en créer un nouveau.");
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

        $agent->load('evenement');

        // Stats et historique limités à l'événement actuellement assigné
        $stats = $agent->statsParEvenement($agent->evenement_id);

        $logs = $agent->logsEvenement($agent->evenement_id)
            ->latest('created_at')
            ->paginate(\App\Support\PerPage::resolve())
            ->appends(request()->query());

        $statsGlobales = $agent->statsGlobales();
        $logsGlobaux = $agent->logsGlobaux()
            ->paginate(\App\Support\PerPage::resolve(), ['*'], 'page_logs')
            ->appends(request()->query());

        return view('admin.agents.show', compact('agent', 'stats', 'logs', 'statsGlobales', 'logsGlobaux'));
    }

    // Affiche le formulaire de modification / réaffectation d'un agent de scan
    public function edit(Agent $agent)
    {
        if ($agent->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }

        $reaffectable = $agent->peutEtreReaffecte();

        // Cibles de réaffectation : événements à venir de l'organisateur, hors annulation
        $evenements = Evenement::where('user_id', auth()->id())
            ->where(fn ($q) => $q->whereNull('date_event')->orWhere('date_event', '>=', now()))
            ->where('statut', '!=', 'annulé')
            ->orderBy('date_event')
            ->get();

        return view('admin.agents.edit', compact('agent', 'evenements', 'reaffectable'));
    }

    // Met à jour l'agent (identité, éventuellement réaffectation à un autre événement)
    public function update(Request $request, Agent $agent)
    {
        if ($agent->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }

        $reaffectable = $agent->peutEtreReaffecte();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('agents', 'email')->ignore($agent->id)],
            'evenement_id' => ['required', Rule::exists('evenement', 'id')->where(function ($q) {
                $q->where('user_id', auth()->id());
            })],
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé par un autre agent.',
            'evenement_id.required' => 'Veuillez sélectionner un événement.',
            'evenement_id.exists' => 'L\'événement sélectionné est invalide.',
        ]);

        $changementEvenement = (int) $validated['evenement_id'] !== (int) $agent->evenement_id;

        if ($changementEvenement) {
            if (! $reaffectable) {
                return back()->withErrors(['evenement_id' => 'Cet agent est actif sur un événement à venir : désactivez-le avant de le réaffecter.']);
            }

            $cible = Evenement::findOrFail($validated['evenement_id']);

            if ($cible->date_event && $cible->date_event->isPast()) {
                return back()->withErrors(['evenement_id' => 'Cet événement est déjà passé : impossible d\'y affecter un agent.']);
            }

            if (in_array($cible->statut, ['annulé', 'terminé'], true)) {
                return back()->withErrors(['evenement_id' => 'Cet événement n\'accepte plus d\'agents de scan.']);
            }

            if ($agent->actif) {
                $nbActifs = $cible->agents()->where('actif', true)->count();
                $limite = $cible->limiteAgentsScan();
                if ($limite !== null && $nbActifs >= $limite) {
                    return back()->withErrors(['evenement_id' => "Maximum de {$limite} agents de scan atteint pour cet événement. Désactivez d'abord un agent existant."]);
                }
            }
        }

        $emailVente = \App\Models\AgentVente::where('email', $validated['email'])->exists();
        if ($emailVente) { // Un email ne peut pas être utilisé pour un scan ET une vente
            return back()->withErrors(['email' => 'Cet email est déjà utilisé par un agent de vente. Un agent ne peut pas être à la fois scan et vente.']);
        }

        $agent->update([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'evenement_id' => $validated['evenement_id'],
        ]);

        return redirect()->route('admin.agents.show', $agent)
            ->with('success', $changementEvenement
                ? 'Agent réaffecté avec succès.'
                : 'Agent mis à jour avec succès.');
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
