<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RetraitController extends Controller
{
    const COMMISSION_PERCENTAGE = 10;

    public function index()
    {
        $user = Auth::user();
        $evenementsIds = $user->evenements()->pluck('id');

        $recettesBrutes = (float) \App\Models\Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->sum('montant');

        $commission = round($recettesBrutes * self::COMMISSION_PERCENTAGE / 100, 2);
        $recettesNettes = $recettesBrutes - $commission;

        $totalRetraits = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->sum('montant');

        $soldeDisponible = $recettesNettes - $totalRetraits;

        $retraits = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.retraits.index', compact(
            'recettesBrutes', 'commission', 'recettesNettes',
            'totalRetraits', 'soldeDisponible', 'retraits'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $evenementsIds = $user->evenements()->pluck('id');

        $recettesBrutes = (float) \App\Models\Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->sum('montant');

        $commission = round($recettesBrutes * self::COMMISSION_PERCENTAGE / 100, 2);
        $recettesNettes = $recettesBrutes - $commission;

        $totalRetraits = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->sum('montant');

        $soldeDisponible = $recettesNettes - $totalRetraits;

        $data = $request->validate([
            'montant' => 'required|numeric|min:100|max:' . $soldeDisponible,
            'nom' => 'required|string|max:255',
            'mobile' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        if (!Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
        }

        Withdrawal::create([
            'user_id' => $user->id,
            'montant' => $data['montant'],
            'commission_percentage' => self::COMMISSION_PERCENTAGE,
            'nom' => $data['nom'],
            'mobile' => $data['mobile'],
            'status' => 'en_attente',
        ]);

        return back()->with('success', 'Demande de retrait envoyée. Le super admin va la traiter.');
    }
}
