<?php

namespace App\Http\Controllers;

use App\Mail\WithdrawalRequestedMail;
use App\Models\AdminNotification;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RetraitController extends Controller
{
    const COMMISSION_PERCENTAGE = 10;

    private const OPERATEURS = ['mtn' => 'MTN MoMo', 'moov' => 'Moov Money', 'celtiis' => 'Celtiis Cash'];

    public function index()
    {
        $user = Auth::user();
        $evenementsIds = $user->evenements()->pluck('id');

        $ticketsPayes = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé');

        $totalTickets = (clone $ticketsPayes)->sum('montant');
        $cashRecettes = (clone $ticketsPayes)->whereIn('methode_paiement', ['cash', 'especes'])->sum('montant');

        $totalDejaRetire = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->sum('montant');

        $commissionGlobale = round($totalTickets * self::COMMISSION_PERCENTAGE / 100, 2);
        $commissionEspeces = round($cashRecettes * self::COMMISSION_PERCENTAGE / 100, 2);

        $commissionEstimeePayee = $totalDejaRetire > 0
            ? round($totalDejaRetire / ((100 - self::COMMISSION_PERCENTAGE) / self::COMMISSION_PERCENTAGE), 2)
            : 0;
        $commissionEspecesRestante = max(0, $commissionEspeces - $commissionEstimeePayee);
        $commissionEspecesSoldee = $commissionEspeces <= 0 || $commissionEspecesRestante <= 0;

        $reseaux = [];
        $totalMobileNet = 0;
        foreach (self::OPERATEURS as $key => $label) {
            $brut = (clone $ticketsPayes)->where('methode_paiement', $key)->sum('montant');
            $count = (clone $ticketsPayes)->where('methode_paiement', $key)->count();
            $net = round($brut * (100 - self::COMMISSION_PERCENTAGE) / 100, 2);
            $totalMobileNet += $net;
            $reseaux[$key] = [
                'label' => $label,
                'brut' => $brut,
                'net' => $net,
                'count' => $count,
            ];
        }

        $netRetirableGlobal = max(0, $totalMobileNet - $commissionEspeces - $totalDejaRetire);
        $soldeDisponible = $netRetirableGlobal;

        $retraits = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.retraits.index', compact(
            'reseaux',
            'cashRecettes',
            'commissionGlobale',
            'commissionEspeces',
            'commissionEspecesSoldee',
            'totalMobileNet',
            'netRetirableGlobal',
            'soldeDisponible',
            'totalDejaRetire',
            'retraits',
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $evenementsIds = $user->evenements()->pluck('id');

        $totalDejaRetire = (float) Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->sum('montant');

        $ticketsPayes = Ticket::whereIn('evenement_id', $evenementsIds)->where('statut_paiement', 'payé');
        $cashRecettes = (clone $ticketsPayes)->whereIn('methode_paiement', ['cash', 'especes'])->sum('montant');
        $commissionEspeces = round($cashRecettes * self::COMMISSION_PERCENTAGE / 100, 2);

        $totalMobileNet = 0;
        foreach (self::OPERATEURS as $key => $label) {
            $brut = (clone $ticketsPayes)->where('methode_paiement', $key)->sum('montant');
            $totalMobileNet += round($brut * (100 - self::COMMISSION_PERCENTAGE) / 100, 2);
        }

        $soldeDisponible = max(0, $totalMobileNet - $commissionEspeces - $totalDejaRetire);

        $validated = $request->validate([
            'operateur' => 'required|in:' . implode(',', array_keys(self::OPERATEURS)),
            'montant' => 'required|numeric|min:100|max:' . $soldeDisponible,
            'nom' => 'required|string|max:255',
            'mobile' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'montant' => $validated['montant'],
            'commission_percentage' => self::COMMISSION_PERCENTAGE,
            'operateur' => $validated['operateur'],
            'nom' => $validated['nom'],
            'mobile' => $validated['mobile'],
            'status' => 'en_attente',
        ]);

        AdminNotification::create([
            'withdrawal_id' => $withdrawal->id,
            'type' => 'withdrawal_requested',
            'data' => [
                'organisateur' => $user->nom,
                'email' => $user->email,
                'montant' => $validated['montant'],
                'operateur' => $validated['operateur'],
                'beneficiaire' => $validated['nom'],
                'mobile' => $validated['mobile'],
            ],
        ]);

        try {
            foreach (User::where('role', 'super_admin')->cursor() as $sa) {
                Mail::to($sa->email)->send(new WithdrawalRequestedMail($withdrawal));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Erreur envoi email retrait: ' . $e->getMessage());
        }

        return back()->with('success', 'Demande de retrait envoyée. L\'équipe PaxEvent va la traiter.');
    }

    public static function getOperateurs(): array
    {
        return self::OPERATEURS;
    }
}
