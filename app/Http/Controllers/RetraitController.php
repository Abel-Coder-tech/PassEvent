<?php

namespace App\Http\Controllers;

use App\Mail\RetraitApproved;
use App\Mail\RetraitConfirmed;
use App\Mail\RetraitRejected;
use App\Mail\RetraitRequested;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetraitController extends Controller
{
    const COMMISSION_PERCENTAGE = 10;

    const RESEAUX_CONFIG = [
        'mtn' => ['label' => 'MTN MoMo', 'icon' => 'bi-phone'],
        'moov' => ['label' => 'Moov Money', 'icon' => 'bi-phone'],
        'celtiis' => ['label' => 'Celtiis Cash', 'icon' => 'bi-phone'],
    ];

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
            ->where('status', 'payé')
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

    public function index()
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);

        $retraits = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.retraits.index', array_merge($data, ['retraits' => $retraits]));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);
        $isAjax = $request->ajax() || $request->expectsJson();

        $validated = $request->validate([
            'reseau' => 'required|in:mtn,moov,celtiis',
            'montant' => 'required|numeric|min:1000|max:' . $data['soldeDisponible'],
            'nom' => 'required|string|max:255',
            'mobile' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        if (!Hash::check($validated['password'], $user->getAuthPassword())) {
            $msg = 'Mot de passe incorrect.';
            if ($isAjax) return response()->json(['success' => false, 'message' => $msg], 422);
            return back()->withErrors(['password' => $msg])->withInput();
        }

        $retrait = Withdrawal::create([
            'user_id' => $user->id,
            'montant' => $validated['montant'],
            'commission_percentage' => self::COMMISSION_PERCENTAGE,
            'nom' => $validated['nom'],
            'mobile' => $validated['mobile'],
            'reseau' => $validated['reseau'],
            'status' => 'en_attente',
        ]);

        $label = self::RESEAUX_CONFIG[$validated['reseau']]['label'];
        $this->notifierSuperAdmin($user, $validated, $label);

        $msg = "Demande de retrait de {$label} envoyée. L'équipe PaxEvent va la traiter sous 72h.";
        if ($isAjax) return response()->json(['success' => true, 'message' => $msg]);
        return back()->with('success', $msg);
    }

    protected function notifierSuperAdmin(User $user, array $validated, string $label)
    {
        Message::create([
            'nom_complet' => $user->nom,
            'email' => $user->email,
            'objet' => 'Demande de retrait — ' . $user->nom,
            'message' => "L'organisateur {$user->nom} ({$user->email}) a demandé un retrait de " . number_format($validated['montant'], 0, ',', ' ') . " FCFA sur {$label}.\nBénéficiaire : {$validated['nom']}\nMobile : {$validated['mobile']}",
            'lu' => false,
        ]);

        try {
            $superadmin = User::where('role', 'super_admin')->first();
            if ($superadmin) {
                Mail::to($superadmin->email)->send(
                    new RetraitRequested(
                        $user->nom,
                        $user->email,
                        $label,
                        $validated['montant'],
                        $validated['nom'],
                        $validated['mobile']
                    )
                );
            }
        } catch (\Exception $e) {
            Log::error('Email retrait non envoyé : ' . $e->getMessage());
        }
    }

    public static function getLabelReseau(?string $reseau): string
    {
        return self::RESEAUX_CONFIG[$reseau]['label'] ?? ucfirst($reseau ?? 'inconnu');
    }

    public static function notifierOrganisateur(Withdrawal $retrait, string $type)
    {
        $user = $retrait->user;
        $label = self::getLabelReseau($retrait->reseau);

        $messages = [
            'en_cours' => "Votre retrait de " . number_format($retrait->montant, 0, ',', ' ') . " FCFA sur {$label} est en cours de traitement.",
            'paye' => "Votre retrait de " . number_format($retrait->montant, 0, ',', ' ') . " FCFA sur {$label} a été effectué. Merci d'avoir utilisé PaxEvent !",
            'rejete' => "Votre retrait de " . number_format($retrait->montant, 0, ',', ' ') . " FCFA sur {$label} a été rejeté. Raison : " . ($retrait->admin_notes ?? 'Non spécifiée'),
        ];

        $objets = [
            'en_cours' => 'Retrait en cours de traitement — PaxEvent',
            'paye' => 'Retrait effectué — Merci ! PaxEvent',
            'rejete' => 'Retrait rejeté — PaxEvent',
        ];

        Message::create([
            'user_id' => $user->id,
            'nom_complet' => 'PaxEvent',
            'email' => 'contact@paxevent.com',
            'objet' => $objets[$type] ?? 'Retrait — PaxEvent',
            'message' => $messages[$type] ?? '',
            'lu' => false,
        ]);

        try {
            $mailClass = match($type) {
                'en_cours' => new RetraitApproved($user->nom, $label, $retrait->montant),
                'paye' => new RetraitConfirmed($user->nom, $label, $retrait->montant),
                'rejete' => new RetraitRejected($user->nom, $label, $retrait->montant, $retrait->admin_notes ?? 'Non spécifiée'),
                default => null,
            };
            if ($mailClass) {
                Mail::to($user->email)->send($mailClass);
            }
        } catch (\Exception $e) {
            Log::error("Email retrait {$type} non envoyé : " . $e->getMessage());
        }
    }
}
