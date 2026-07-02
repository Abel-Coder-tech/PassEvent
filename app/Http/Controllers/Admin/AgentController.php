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
    public function index()
    {
        $agents = Agent::with('evenement')->whereIn('evenement_id', auth()->user()->evenements->pluck('id'))->orderBy('created_at', 'desc')->get();
        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        $evenements = auth()->user()->evenements;
        return view('admin.agents.create', compact('evenements'));
    }

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

        $codeAcces = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

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

    public function show(Agent $agent)
    {
        $this->authorize('view', $agent);
        $agent->load('logs.ticket', 'evenement');
        return view('admin.agents.show', compact('agent'));
    }

    public function toggleActif(Agent $agent)
    {
        $this->authorize('update', $agent);
        $agent->update(['actif' => !$agent->actif]);
        $statut = $agent->actif ? 'activé' : 'désactivé';
        return redirect()->route('admin.agents.index')->with('success', "Agent {$statut} avec succès.");
    }

    public function destroy(Agent $agent)
    {
        $this->authorize('delete', $agent);
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Agent supprimé avec succès.');
    }
}
