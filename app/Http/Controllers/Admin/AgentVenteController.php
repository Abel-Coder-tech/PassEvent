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
    // Liste tous les agents de vente de l'organisateur avec le nombre d'actifs par événement
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

    // Affiche le formulaire de création d'un agent de vente
    public function create(): View
    {
        $evenements = Evenement::where('user_id', auth()->id())->latest()->get();

        return view('admin.agents-vente.create', compact('evenements'));
    }

    // Crée un agent de vente avec mot de passe aléatoire et envoi d'email
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
        if ($nbActifs >= 2) { // Maximum 2 agents de vente par événement
            return back()->with('error', "Maximum de 2 agents de vente atteint pour cet événement. Désactivez d'abord un agent existant avant d'en créer un nouveau.");
        }

        $emailExiste = \App\Models\Agent::where('email', $validated['email'])->exists();
        if ($emailExiste) { // Un email ne peut pas servir pour scan et vente à la fois
            return back()->with('error', 'Cet email est déjà utilisé par un agent de scan. Un agent ne peut pas être à la fois scan et vente.');
        }

        $motDePasse = Str::random(10); // Mot de passe aléatoire envoyé par email

        $agent = AgentVente::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($motDePasse),
            'evenement_id' => $evenement->id,
        ]);

        Mail::to($agent->email)->send(new AgentVenteAssigned($agent, $motDePasse));

        return redirect()->route('admin.agents-vente.show', $agent)
            ->with('success', 'Agent de vente créé avec succès. Un email lui a été envoyé.');
    }

    // Affiche les détails, ventes et statistiques d'un agent de vente
    public function show(AgentVente $agentVente): View
    {
        if ($agentVente->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
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

    // Active ou désactive un agent de vente
    public function toggleActif(AgentVente $agentVente): RedirectResponse
    {
        if ($agentVente->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }

        $agentVente->update(['actif' => !$agentVente->actif]);

        return back()->with('success', $agentVente->actif
            ? 'Agent de vente réactivé.'
            : 'Agent de vente désactivé.');
    }

    // Supprime un agent de vente en détachant d'abord ses tickets
    public function destroy(AgentVente $agentVente): RedirectResponse
    {
        if ($agentVente->evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
        }

        try {
            $agentVente->tickets()->update(['agent_vente_id' => null]); // Détache les tickets avant suppression
            $agentVente->delete();
        } catch (\Exception $e) {
            return redirect()->route('admin.agents-vente.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return redirect()->route('admin.agents-vente.index')
            ->with('success', 'Agent de vente supprimé.');
    }

    // Affiche les statistiques agrégées de tous les agents de vente d'un événement
    public function statsEvenement(Evenement $evenement): View
    {
        if ($evenement->user_id !== auth()->id()) {
            abort(403); // Vérification de propriété
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
