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

    protected function getSoldeDisponible($user)
    {
        $evenementsIds = $user->evenements()->pluck('id');

        $totalTickets = (float) \App\Models\Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->sum('montant');

        $mobileRecettes = (float) \App\Models\Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNotIn('methode_paiement', ['cash', 'especes'])
            ->sum('montant');

        $commission = round($totalTickets * self::COMMISSION_PERCENTAGE / 100, 2);

        $totalRetraits = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->sum('montant');

        return [
            'recettesBrutes' => $mobileRecettes,
            'commission' => $commission,
            'recettesNettes' => max(0, $mobileRecettes - $commission),
            'totalRetraits' => $totalRetraits,
            'soldeDisponible' => max(0, $mobileRecettes - $commission - $totalRetraits),
            'totalTickets' => $totalTickets,
        ];
    }

    public function index()
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);

        $retraits = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.retraits.index', array_merge(
            $data,
            ['retraits' => $retraits]
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);
        $soldeDisponible = $data['soldeDisponible'];

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
