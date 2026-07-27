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

    // Calcule le solde disponible par réseau avec commission proportionnelle
    protected function getSoldeDisponible($user)
    {
        $evenementsIds = $user->evenements()->pluck('id');

        $totalTickets = (float) Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->sum('montant');

        $cashRecettes = (float) Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereIn('methode_paiement', ['cash', 'especes'])
            ->sum('montant');

        $mobileRecettes = (float) Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNotIn('methode_paiement', ['cash', 'especes'])
            ->sum('montant');

        // Montants par réseau
        $parReseau = Ticket::whereIn('evenement_id', $evenementsIds)
            ->where('statut_paiement', 'payé')
            ->whereNotIn('methode_paiement', ['cash', 'especes'])
            ->select('methode_paiement', DB::raw('SUM(montant) as montant'))
            ->groupBy('methode_paiement')
            ->pluck('montant', 'methode_paiement');

        // Retraits approuvés par réseau
        $retraitsParReseau = Withdrawal::where('user_id', $user->id)
            ->where('status', 'approuvé')
            ->select('reseau', DB::raw('SUM(montant) as total'))
            ->groupBy('reseau')
            ->pluck('total', 'reseau');

        // Commission globale (10% sur tous les tickets)
        $commissionTotale = round($totalTickets * self::COMMISSION_PERCENTAGE / 100, 2);

        // Commission espèces (10% sur les espèces) à répartir sur les réseaux
        $commissionCash = round($cashRecettes * self::COMMISSION_PERCENTAGE / 100, 2);

        // Calcul du solde par réseau
        $soldes = [];
        $commissionParReseau = [];
        $soldeTotalDisponible = 0;

        foreach (self::RESEAUX_CONFIG as $key => $cfg) {
            $recettes = (float) ($parReseau->get($key) ?? 0);
            $retraits = (float) ($retraitsParReseau->get($key) ?? 0);

            if ($recettes <= 0) {
                $soldes[$key] = [
                    'label' => $cfg['label'],
                    'icon' => $cfg['icon'],
                    'recettes' => 0,
                    'commission' => 0,
                    'retraits' => 0,
                    'solde' => 0,
                ];
                $commissionParReseau[$key] = 0;
                continue;
            }

            // Commission propre au réseau : 10% des recettes du réseau
            $commissionReseau = round($recettes * self::COMMISSION_PERCENTAGE / 100, 2);

            // Part de la commission espèces proportionnelle au poids du réseau
            $partCommissionCash = 0;
            if ($mobileRecettes > 0 && $commissionCash > 0) {
                $partCommissionCash = round($commissionCash * ($recettes / $mobileRecettes), 2);
            }

            $commissionTotaleReseau = round($commissionReseau + $partCommissionCash, 2);
            $solde = max(0, $recettes - $commissionTotaleReseau - $retraits);

            $soldes[$key] = [
                'label' => $cfg['label'],
                'icon' => $cfg['icon'],
                'recettes' => $recettes,
                'commission' => $commissionTotaleReseau,
                'retraits' => $retraits,
                'solde' => $solde,
            ];

            $commissionParReseau[$key] = $commissionTotaleReseau;
            $soldeTotalDisponible += $solde;
        }

        return [
            'totalTickets' => $totalTickets,
            'cashRecettes' => $cashRecettes,
            'mobileRecettes' => $mobileRecettes,
            'commissionTotale' => $commissionTotale,
            'commissionCash' => $commissionCash,
            'soldes' => $soldes,
            'soldeTotalDisponible' => $soldeTotalDisponible,
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
            'montant' => 'required|numeric|min:1000',
            'nom' => 'required|string|max:255',
            'mobile' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
        }

        $reseau = $validated['reseau'];
        $soldeReseau = $data['soldes'][$reseau]['solde'] ?? 0;

        if ($validated['montant'] > $soldeReseau) {
            return back()->withErrors([
                'montant' => "Montant maximum pour {$data['soldes'][$reseau]['label']} : " . number_format($soldeReseau, 0, ',', ' ') . " FCFA."
            ])->withInput();
        }

        Withdrawal::create([
            'user_id' => $user->id,
            'montant' => $validated['montant'],
            'commission_percentage' => self::COMMISSION_PERCENTAGE,
            'nom' => $validated['nom'],
            'mobile' => $validated['mobile'],
            'reseau' => $reseau,
            'status' => 'en_attente',
        ]);

        return back()->with('success', "Demande de retrait de {$data['soldes'][$reseau]['label']} envoyée. L'équipe PaxEvent va la traiter.");
    }
}
