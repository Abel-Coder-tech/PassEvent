<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\Log;
use App\Models\Message;
use App\Models\Newsletter;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $now = now();
        $today = $now->copy()->startOfDay();

        $totalUsers = User::count();
        $totalOrganisateurs = User::where('role', 'admin')->count();
        $totalSuperAdmins = User::where('role', 'super_admin')->count();
        $totalEvenements = Evenement::count();
        $evenementsActifs = Evenement::where('statut', 'publié')->where('date_event', '>=', $now)->count();
        $ticketsVendus = Ticket::where('statut_paiement', 'payé')->count();
        $recettesGlobales = Ticket::where('statut_paiement', 'payé')->sum('montant');
        $scansAujourdhui = Ticket::where('utilise', true)->whereDate('updated_at', $today)->count();

        $ventes7Jours = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $ventes7Jours->push([
                'date' => $day->format('d M'),
                'tickets' => Ticket::where('statut_paiement', 'payé')->whereDate('date_achat', $day)->count(),
                'revenus' => Ticket::where('statut_paiement', 'payé')->whereDate('date_achat', $day)->sum('montant'),
            ]);
        }

        $usersParRole = [
            'etudiants' => Ticket::where('categorie', 'etudiant')->where('statut_paiement', 'payé')->distinct('email_acheteur')->count('email_acheteur'),
            'externes' => Ticket::where('categorie', 'externe')->where('statut_paiement', 'payé')->distinct('email_acheteur')->count('email_acheteur'),
            'admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
        ];

        $topEvenements = Evenement::withCount(['tickets as tickets_vendus' => function ($q) {
            $q->where('statut_paiement', 'payé');
        }])
            ->where('statut', 'publié')
            ->orderByDesc('tickets_vendus')
            ->limit(10)
            ->get()
            ->map(function ($e) {
                $remplissage = $e->capacite > 0 ? round(($e->tickets_vendus / $e->capacite) * 100) : 0;
                return [
                    'titre' => $e->titre,
                    'tickets' => $e->tickets_vendus,
                    'remplissage' => $remplissage,
                ];
            });

        $activiteEnDirect = Log::with(['ticket.evenement'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $evenement = $log->ticket?->evenement?->titre ?? 'N/A';
                return [
                    'action' => $log->type_operation,
                    'evenement' => $evenement,
                    'date' => $log->created_at->diffForHumans(),
                    'ip' => $log->ip ?? '-',
                ];
            });

        $scanInvalides = Log::where('type_operation', 'scan')->where('created_at', '>=', $today)->count();
        $paiementsEchoues = Ticket::where('statut_paiement', 'échoué')->count();
        $ticketsDupliques = Ticket::select('email_acheteur', 'evenement_id', DB::raw('count(*) as total'))
            ->where('statut_paiement', 'payé')
            ->groupBy('email_acheteur', 'evenement_id')
            ->having('total', '>', 3)
            ->get()->count();

        $transactionsReussies = Ticket::where('statut_paiement', 'payé')->where('transaction_id', 'not like', 'GRATUIT-%')->count();
        $transactionsEchouees = Ticket::where('statut_paiement', 'échoué')->count();
        $montantsJournaliers = Ticket::where('statut_paiement', 'payé')->whereDate('date_achat', $today)->sum('montant');
        $commissionPct = \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE;
        $commissionPlateforme = Ticket::where('statut_paiement', 'payé')->sum(DB::raw("montant * $commissionPct / 100"));

        $messagesNonLus = Message::where('lu', false)->count();
        $newsletterCount = Newsletter::where('actif', true)->count();

        return view('superadmin.dashboard', compact(
            'totalUsers', 'totalOrganisateurs', 'totalSuperAdmins',
            'totalEvenements', 'evenementsActifs', 'ticketsVendus',
            'recettesGlobales', 'scansAujourdhui', 'ventes7Jours',
            'usersParRole', 'topEvenements', 'activiteEnDirect',
            'scanInvalides', 'paiementsEchoues', 'ticketsDupliques',
            'transactionsReussies', 'transactionsEchouees',
            'montantsJournaliers', 'commissionPlateforme',
            'commissionPct',
            'messagesNonLus', 'newsletterCount'
        ));
    }

    public function utilisateurs()
    {
        $users = User::withCount('evenements')->orderByDesc('created_at')->paginate(20);
        return view('superadmin.utilisateurs', compact('users'));
    }

    public function organisateurs()
    {
        $organisateurs = User::where('role', 'admin')
            ->withCount('evenements')
            ->withSum(['evenements as tickets_vendus' => function ($q) {
                $q->where('statut', 'publié');
            }], 'quota_vendu')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.organisateurs', compact('organisateurs'));
    }

    public function evenements()
    {
        $evenements = Evenement::with('user')
            ->withCount(['tickets as tickets_vendus' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.evenements', compact('evenements'));
    }

    public function transactions()
    {
        $transactions = Ticket::whereNotNull('transaction_id')
            ->where('transaction_id', 'not like', 'GRATUIT-%')
            ->with('evenement')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.transactions', compact('transactions'));
    }

    public function tickets()
    {
        $allTickets = Ticket::with('evenement')->orderByDesc('created_at')->paginate(20);
        return view('superadmin.tickets', compact('allTickets'));
    }

    public function scans()
    {
        $logs = Log::with('ticket.evenement')
            ->where('type_operation', 'scan')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.scans', compact('logs'));
    }

    public function statistiques()
    {
        $evenements = Evenement::select('id', 'titre', 'capacite', 'quota_vendu', 'date_event', 'statut')
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('date_event')
            ->get();
        return view('superadmin.statistiques', compact('evenements'));
    }

    public function securite()
    {
        $logsSuspects = Log::whereIn('type_operation', ['echec_paiement', 'erreur_paiement'])
            ->with('ticket.evenement')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return view('superadmin.securite', compact('logsSuspects'));
    }

    public function notifications()
    {
        $messages = Message::orderByDesc('created_at')->paginate(20);
        return view('superadmin.notifications', compact('messages'));
    }

    public function parametres()
    {
        return view('superadmin.parametres');
    }

    public function logsSysteme()
    {
        $logs = Log::with('ticket.evenement')
            ->orderByDesc('created_at')
            ->paginate(30);
        return view('superadmin.logs', compact('logs'));
    }

    public function moderation()
    {
        $evenementsSuspendus = Evenement::where('statut', 'annulé')->with('user')->paginate(20);
        return view('superadmin.moderation', compact('evenementsSuspendus'));
    }

    public function suspendreEvenement(Evenement $evenement)
    {
        $evenement->update(['statut' => 'annulé']);
        Log::create([
            'type_operation' => 'evenement_annule',
            'ticket_id' => null,
            'details' => json_encode(['evenement_id' => $evenement->id, 'titre' => $evenement->titre, 'par' => auth('superadmin')->user()->email]),
            'ip' => request()->ip(),
        ]);
        return back()->with('success', 'Evenement suspendu.');
    }

    public function masquerEvenement(Evenement $evenement)
    {
        $evenement->update(['statut' => 'brouillon']);
        return back()->with('success', 'Evenement masque.');
    }

    public function supprimerEvenement(Evenement $evenement)
    {
        $evenement->delete();
        return back()->with('success', 'Evenement supprime.');
    }

    public function mettreEnAvant(Evenement $evenement)
    {
        $evenement->update(['statut' => 'publié']);
        return back()->with('success', 'Evenement mis en avant.');
    }

    public function suspendreOrganisateur(User $user)
    {
        $user->update(['statut' => 'bloque']);
        $evenements = $user->evenements()->where('statut', 'publié')->update(['statut' => 'annulé']);
        Log::create([
            'type_operation' => 'organisateur_suspendu',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'evenements_annules' => $evenements]),
            'ip' => request()->ip(),
        ]);
        return back()->with('success', 'Organisateur suspendu et ses evenements annules.');
    }

    public function creerOrganisateur(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'mot_de_passe' => 'required|min:8',
            'telephone' => 'nullable|string|max:20',
            'organisation' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:universitaire,professionnel',
            'description' => 'nullable|string|max:1000',
        ]);
        $data['mot_de_passe'] = bcrypt($data['mot_de_passe']);
        $data['role'] = 'admin';
        $data['statut'] = 'actif';
        User::create($data);
        return back()->with('success', 'Organisateur créé.');
    }

    public function approuverOrganisateur(User $user)
    {
        if ($user->role !== 'admin' || $user->statut !== 'en_attente') {
            return back()->with('error', 'Action non autorisée.');
        }

        $user->update(['statut' => 'actif']);

        Mail::raw(
            "Bonjour {$user->nom},\n\n" .
            "Votre compte organisateur PaxEvent a été approuvé !\n" .
            "Vous pouvez dès maintenant vous connecter et créer vos événements en quelques clics.\n\n" .
            "Connectez-vous : " . route('login') . "\n\n" .
            "Cordialement,\nL'équipe PaxEvent",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('[PaxEvent] Compte approuvé');
            }
        );

        Log::create([
            'type_operation' => 'organisateur_approuve',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} approuve. Un email de confirmation a ete envoye.");
    }

    public function rejeterOrganisateur(User $user)
    {
        if ($user->role !== 'admin' || $user->statut !== 'en_attente') {
            return back()->with('error', 'Action non autorisée.');
        }

        $user->update(['statut' => 'bloque']);

        Log::create([
            'type_operation' => 'organisateur_rejete',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} rejete.");
    }

    public function retraits()
    {
        $retraits = Withdrawal::with('user')
            ->orderByRaw("FIELD(status, 'en_attente', 'approuvé', 'rejeté')")
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'en_attente' => Withdrawal::where('status', 'en_attente')->count(),
            'approuve' => Withdrawal::where('status', 'approuvé')->sum('montant'),
            'total' => Withdrawal::where('status', 'approuvé')->count(),
        ];

        return view('superadmin.retraits', compact('retraits', 'stats'));
    }

    public function approuverRetrait(Withdrawal $withdrawal, Request $request)
    {
        if ($withdrawal->status !== 'en_attente') {
            return back()->with('error', 'Ce retrait a déjà été traité.');
        }

        $withdrawal->update([
            'status' => 'approuvé',
            'admin_notes' => $request->input('admin_notes'),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Retrait approuvé.');
    }

    public function rejeterRetrait(Withdrawal $withdrawal, Request $request)
    {
        if ($withdrawal->status !== 'en_attente') {
            return back()->with('error', 'Ce retrait a déjà été traité.');
        }

        $withdrawal->update([
            'status' => 'rejeté',
            'admin_notes' => $request->input('admin_notes'),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Retrait rejeté.');
    }
}
