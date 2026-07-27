<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RetraitController extends Controller
{
    const COMMISSION_PERCENTAGE = 10;

    const RESEAUX_CONFIG = [
        'mtn' => ['label' => 'MTN MoMo', 'icon' => 'bi-phone'],
        'moov' => ['label' => 'Moov Money', 'icon' => 'bi-phone'],
        'celtiis' => ['label' => 'Celtiis Cash', 'icon' => 'bi-phone'],
    ];

    // Calcule le solde disponible pour retrait
    protected function getSoldeDisponible($user)
    {
        $evenementsIds = $user->evenements()->pluck('id');

        $totalTickets = (float) Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->sum('montant');

        $mobileRecettes = (float) Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNotIn('methode_paiement', ['cash', 'especes'])
            ->sum('montant');

        $commissionTotale = round($totalTickets * self::COMMISSION_PERCENTAGE / 100, 2);

        $totalRetraits = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->sum('montant');

        $soldeDisponible = max(0, $mobileRecettes - $commissionTotale - $totalRetraits);

        return [
            'totalTickets' => $totalTickets,
            'mobileRecettes' => $mobileRecettes,
            'commissionTotale' => $commissionTotale,
            'totalRetraits' => $totalRetraits,
            'soldeDisponible' => $soldeDisponible,
        ];
    }

    // Page de retraits
    public function index()
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);

        $retraits = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.retraits.index', array_merge($data, ['retraits' => $retraits]));
    }

    // Demande de retrait
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);

        $validated = $request->validate([
            'reseau' => 'required|in:mtn,moov,celtiis',
            'montant' => 'required|numeric|min:1000|max:' . $data['soldeDisponible'],
            'nom' => 'required|string|max:255',
            'mobile' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
        }

        Withdrawal::create([
            'user_id' => $user->id,
            'montant' => $validated['montant'],
            'commission_percentage' => self::COMMISSION_PERCENTAGE,
            'nom' => $validated['nom'],
            'mobile' => $validated['mobile'],
            'reseau' => $validated['reseau'],
            'status' => 'en_attente',
        ]);

        $label = self::RESEAUX_CONFIG[$validated['reseau']]['label'];

        return back()->with('success', "Demande de retrait de {$label} envoyée. L'équipe PaxEvent va la traiter.");
    }
}
