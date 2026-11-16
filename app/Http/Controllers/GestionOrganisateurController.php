<?php

namespace App\Http\Controllers;

use App\Http\Middleware\GestionOrganisateurMiddleware;
use App\Models\ActionPaxevent;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestionOrganisateurController extends Controller
{
    // Superadmin : ouvre le tableau de bord de l'organisateur en son nom.
    public function demarrer(Request $request, User $user)
    {
        $admin = auth('superadmin')->user();

        if (! $admin || ! $admin->peut('organisateurs.gerer')) {
            abort(403, 'Vous n\'avez pas le droit de gérer un organisateur.');
        }

        if ($user->role !== 'admin' || $user->statut === 'rejete') {
            return back()->with('error', 'Ce compte ne peut pas être géré.');
        }

        if (! $user->mandat_actif) {
            return back()->with('error', 'L\'organisateur n\'a pas autorisé PaxEvent à gérer son compte. Recueillez son autorisation depuis sa fiche.');
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->forget('profil');
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put('gestion', [
            'admin_id' => $admin->id,
            'org_id' => $user->id,
            'debut' => now()->timestamp,
            'activite' => now()->timestamp,
        ]);

        Log::create([
            'type_operation' => 'gestion_organisateur',
            'ticket_id' => null,
            'details' => ['action' => 'prise_en_main', 'user_id' => $user->id, 'email' => $user->email, 'par' => $admin->email],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('dashboard');
    }

    // Termine la prise en main et revient à la fiche de l'organisateur.
    public function quitter(Request $request)
    {
        $orgId = (int) ($request->session()->get('gestion.org_id') ?? 0);

        GestionOrganisateurMiddleware::terminer($request);

        return $orgId
            ? redirect()->route('superadmin.organisateurs.voir', $orgId)->with('success', 'Vous avez quitté la gestion de l\'organisateur.')
            : redirect()->route('login');
    }

    // Journal des actions réalisées par PaxEvent au nom de l'organisateur connecté.
    public function journal()
    {
        $actions = ActionPaxevent::where('user_id', Auth::id())->latest()->paginate(30);

        return view('admin.journal-paxevent', compact('actions'));
    }
}
