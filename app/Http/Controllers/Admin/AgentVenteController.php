<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AgentVenteAssigned;
use App\Models\AgentVente;
use App\Models\Evenement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgentVenteController extends Controller
{
    public function index(): View
    {
        $evenements = Evenement::where('user_id', auth()->id())
            ->withCount(['agentsVentes' => function ($q) { $q->where('actif', true); }])
            ->get();

        $agents = AgentVente::whereHas('evenement', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->with('evenement')
            ->latest()
            ->get();

        return view('admin.agents-vente.index', compact('evenements', 'agents'));
    }

    public function create(): View
    {
        $evenements = Evenement::where('user_id', auth()->id())->latest()->get();

        return view('admin.agents-vente.create', compact('evenements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents_vente,email',
            'evenement_id' => 'required|exists:evenement,id',
        ]);

        $evenement = Evenement::where('user_id', auth()->id())
            ->findOrFail($validated['evenement_id']);

        $nbActifs = $evenement->agentsVentes()->where('actif', true)->count();
        $maxAgents = min(5, max(2, $evenement->agentsVentes()->count() + 1));

        if ($nbActifs >= $maxAgents) {
            return back()->withErrors([
                'evenement_id' => "Cet événement a déjà {$nbActifs} agent(s) de vente actif(s). Maximum autorisé : {$maxAgents}.",
            ])->withInput();
        }

        $motDePasse = Str::random(10);
        $codeVente = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $agent = AgentVente::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($motDePasse),
            'evenement_id' => $evenement->id,
            'code_vente' => $codeVente,
        ]);

        Mail::to($agent->email)->send(new AgentVenteAssigned($agent, $motDePasse));

        return redirect()->route('admin.agents-vente.show', $agent)
            ->with('success', 'Agent de vente créé avec succès. Un email lui a été envoyé.');
    }

    public function show(AgentVente $agentVente): View
    {
        if ($agentVente->evenement->user_id !== auth()->id()) {
            abort(403);
        }

        $tickets = $agentVente->tickets()
            ->with('tarif')
            ->latest('date_achat')
            ->paginate(50);

        $stats = [
            'total_tickets' => $agentVente->tickets_count,
            'montant_total' => $agentVente->montant_total,
            'par_tarif' => $agentVente->tickets()
                ->selectRaw('tarif_id, COUNT(*) as total, SUM(montant) as montant')
                ->groupBy('tarif_id')
                ->with('tarif')
                ->get(),
            'par_methode' => $agentVente->tickets()
                ->selectRaw('methode_paiement, COUNT(*) as total')
                ->groupBy('methode_paiement')
                ->get(),
            'aujourd_hui' => $agentVente->tickets()
                ->whereDate('date_achat', today())
                ->count(),
        ];

        return view('admin.agents-vente.show', compact('agentVente', 'tickets', 'stats'));
    }

    public function toggleActif(AgentVente $agentVente): RedirectResponse
    {
        if ($agentVente->evenement->user_id !== auth()->id()) {
            abort(403);
        }

        $agentVente->update(['actif' => !$agentVente->actif]);

        return back()->with('success', $agentVente->actif
            ? 'Agent de vente réactivé.'
            : 'Agent de vente désactivé.');
    }

    public function destroy(AgentVente $agentVente): RedirectResponse
    {
        if ($agentVente->evenement->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $agentVente->tickets()->update(['agent_vente_id' => null]);
            $agentVente->delete();
        } catch (\Exception $e) {
            return redirect()->route('admin.agents-vente.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return redirect()->route('admin.agents-vente.index')
            ->with('success', 'Agent de vente supprimé.');
    }

    public function statsEvenement(Evenement $evenement): View
    {
        if ($evenement->user_id !== auth()->id()) {
            abort(403);
        }

        $agents = $evenement->agentsVentes()
            ->withCount('tickets')
            ->get()
            ->each(function ($agent) {
                $agent->montant = $agent->montant_total;
                $agent->derniere_vente = $agent->tickets()
                    ->latest('date_achat')
                    ->value('date_achat');
                $agent->par_methode = $agent->tickets()
                    ->selectRaw('methode_paiement, COUNT(*) as total')
                    ->groupBy('methode_paiement')
                    ->pluck('total', 'methode_paiement');
            });

        $totalGeneral = [
            'tickets' => $agents->sum('tickets_count'),
            'montant' => $agents->sum('montant_total'),
        ];

        return view('admin.agents-vente.partials._table', compact('agents', 'evenement', 'totalGeneral'));
    }
}
