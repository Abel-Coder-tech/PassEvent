<?php

namespace App\Http\Controllers;

use App\Mail\RetraitApproved;
use App\Mail\RetraitConfirmed;
use App\Mail\RetraitRejected;
use App\Mail\RetraitRequested;
use App\Models\Message;
use App\Models\Evenement;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\PaiementMapper;
use App\Services\TicketStatisticsService;
use App\Support\PerPage;
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

    // Solde retirable : calcul centralisé dans User::soldeRetrait() (aussi
    // affiché au super admin, pour que les deux montants soient identiques).
    protected function getSoldeDisponible($user)
    {
        return $user->soldeRetrait();
    }

    public function index(TicketStatisticsService $ticketStatistics)
    {
        $user = Auth::user();
        $data = $this->getSoldeDisponible($user);

        $retraits = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(PerPage::resolve());

        // Répartition des paiements, affichée ici pour accompagner les retraits.
        $evenementsIds = Evenement::where('user_id', $user->id)->pluck('id');
        $paiementStats = $ticketStatistics->repartitionPaiements($evenementsIds);
        $operateursRaw = $paiementStats['operateurs'];

        $operateursAffiches = ['mtn', 'moov', 'celtiis'];
        $reseauxConfig = [];
        foreach ($operateursAffiches as $operateur) {
            $reseauxConfig[$operateur] = [
                'label' => PaiementMapper::operateurLabel($operateur),
                'icon' => 'bi-phone',
            ];
        }
        $reseauxConfig['autres'] = ['label' => 'Autres / Indéterminé', 'icon' => 'bi-phone'];

        $reseauxPaiement = collect();
        foreach ($reseauxConfig as $key => $cfg) {
            if ($key === 'autres') {
                $autres = $operateursRaw->reject(fn ($data, $k) => in_array($k, $operateursAffiches));
                $reseauxPaiement->put('autres', (object) [
                    'total' => (int) $autres->sum('total'),
                    'montant' => (int) $autres->sum('montant'),
                ]);
            } else {
                $donnees = $operateursRaw->get($key);
                $reseauxPaiement->put($key, (object) [
                    'total' => $donnees ? (int) $donnees->total : 0,
                    'montant' => $donnees ? (int) $donnees->montant : 0,
                ]);
            }
        }

        $moyensPaiement = $paiementStats['moyens'];

        return view('admin.retraits.index', array_merge($data, [
            'retraits' => $retraits,
            'commissionPct' => $user->commissionPourcentage(),
            'numerosRetrait' => $user->numerosRetrait()->orderByDesc('id')->get(),
            'reseauxPaiement' => $reseauxPaiement,
            'reseauxConfig' => $reseauxConfig,
            'moyensPaiement' => $moyensPaiement,
            'ticketsVendus' => $moyensPaiement->sum('total'),
        ]));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAjax = $request->ajax() || $request->expectsJson();

        // Transaction atomique avec verrouillage de l'utilisateur pour éviter les doubles retraits (race condition)
        $retrait = DB::transaction(function () use ($user, $request) {
            // Verrou pessimiste sur la ligne utilisateur : sérialise les demandes simultanées
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $data = $this->getSoldeDisponible($user);

            $validated = $request->validate([
                'reseau' => 'required|in:mtn,moov,celtiis',
                'numero_id' => 'required|integer',
                'montant' => 'required|numeric|min:1000|max:'.$data['soldeDisponible'],
                'password' => 'required|string',
            ]);

            $numero = $user->numerosRetrait()
                ->where('id', $validated['numero_id'])
                ->where('operateur', $validated['reseau'])
                ->first();

            if (! $numero) {
                return ['error' => true, 'message' => 'Numéro de retrait invalide.'];
            }

            if (! Hash::check($validated['password'], $user->getAuthPassword())) {
                return ['error' => true, 'message' => 'Mot de passe incorrect.'];
            }

            return Withdrawal::create([
                'user_id' => $user->id,
                'montant' => $validated['montant'],
                'commission_percentage' => $user->commissionPourcentage(),
                'nom' => $numero->nom,
                'mobile' => $numero->mobile,
                'reseau' => $validated['reseau'],
                'status' => 'en_attente',
            ]);
        });

        if (is_array($retrait) && isset($retrait['error'])) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $retrait['message']], 422);
            }

            return back()->withErrors(['password' => $retrait['message']])->withInput();
        }

        $label = self::RESEAUX_CONFIG[$retrait->reseau]['label'];
        $this->notifierSuperAdmin($user, [
            'montant' => $retrait->montant,
            'nom' => $retrait->nom,
            'mobile' => $retrait->mobile,
        ], $label);

        $msg = "Demande de retrait de {$label} envoyée. L'équipe PaxEvent va la traiter sous 72h.";
        if ($isAjax) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    protected function notifierSuperAdmin(User $user, array $validated, string $label)
    {
        Message::create([
            'nom_complet' => $user->nom,
            'email' => $user->email,
            'objet' => 'Demande de retrait — '.$user->nom,
            'message' => "L'organisateur {$user->nom} ({$user->email}) a demandé un retrait de ".number_format($validated['montant'], 0, ',', ' ')." FCFA sur {$label}.\nBénéficiaire : {$validated['nom']}\nMobile : {$validated['mobile']}",
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
            Log::error('Email retrait non envoyé : '.$e->getMessage());
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
            'en_cours' => 'Votre retrait de '.number_format($retrait->montant, 0, ',', ' ')." FCFA sur {$label} est en cours de traitement.",
            'paye' => 'Votre retrait de '.number_format($retrait->montant, 0, ',', ' ')." FCFA sur {$label} a été effectué. Merci d'avoir utilisé PaxEvent !",
            'rejete' => 'Votre retrait de '.number_format($retrait->montant, 0, ',', ' ')." FCFA sur {$label} a été rejeté. Raison : ".($retrait->admin_notes ?? 'Non spécifiée'),
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
            $mailClass = match ($type) {
                'en_cours' => new RetraitApproved($user->nom, $label, $retrait->montant),
                'paye' => new RetraitConfirmed($user->nom, $label, $retrait->montant),
                'rejete' => new RetraitRejected($user->nom, $label, $retrait->montant, $retrait->admin_notes ?? 'Non spécifiée'),
                default => null,
            };
            if ($mailClass) {
                Mail::to($user->email)->send($mailClass);
            }
        } catch (\Exception $e) {
            Log::error("Email retrait {$type} non envoyé : ".$e->getMessage());
        }
    }
}
