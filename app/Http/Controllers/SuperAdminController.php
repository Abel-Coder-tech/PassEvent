<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;
use App\Mail\RegistrationCorrections;
use App\Models\User;
use App\Models\Evenement;
use App\Models\Ticket;
use App\Models\Log;
use App\Models\Message;
use App\Models\Newsletter;
use App\Models\Withdrawal;
use App\Models\Agent;
use App\Models\AgentVente;
use App\Models\DemandeRemboursement;
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
                'date' => $day->isoFormat('D MMM'),
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

        $messagesNonLus = Message::where('lu', false)->whereNull('user_id')->count();
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
        $messages = Message::whereNull('user_id')->orderByDesc('created_at')->paginate(20);
        return view('superadmin.notifications', compact('messages'));
    }

    public function lireNotification(Message $message)
    {
        if ($message->user_id !== null) {
            abort(403);
        }
        $message->update(['lu' => true]);
        return response()->json(['success' => true]);
    }

    public function supprimerNotification(Message $message)
    {
        if ($message->user_id !== null) {
            abort(403);
        }
        $message->delete();
        return back()->with('success', 'Notification supprimée.');
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

        Mail::to($user->email)->send(new RegistrationApproved($user));

        Log::create([
            'type_operation' => 'organisateur_approuve',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} approuvé. Un email de confirmation a été envoyé.");
    }

    public function rejeterOrganisateur(Request $request, User $user)
    {
        if ($user->role !== 'admin' || !in_array($user->statut, ['en_attente', 'corrections_demandees'])) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate(['motif' => 'required|string|max:2000']);

        $user->update(['statut' => 'bloque']);

        Mail::to($user->email)->send(new RegistrationRejected($user, $request->motif));

        Log::create([
            'type_operation' => 'organisateur_rejete',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'motif' => $request->motif]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} rejeté avec notification.");
    }

    public function demanderCorrectionsOrganisateur(Request $request, User $user)
    {
        if ($user->role !== 'admin' || $user->statut !== 'en_attente') {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate(['motif' => 'required|string|max:2000']);

        $user->update(['statut' => 'corrections_demandees']);

        Mail::to($user->email)->send(new RegistrationCorrections($user, $request->motif));

        Log::create([
            'type_operation' => 'organisateur_corrections',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'motif' => $request->motif]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Corrections demandées à {$user->nom} par email.");
    }

    public function supprimerOrganisateur(User $user)
    {
        if ($user->role !== 'admin') {
            return back()->with('error', 'Action non autorisée.');
        }

        $user->delete();

        Log::create([
            'type_operation' => 'organisateur_supprime',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} supprimé définitivement.");
    }

    public function envoyerEmailOrganisateur(Request $request, User $user)
    {
        $request->validate([
            'sujet' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Mail::raw($request->message, function ($mail) use ($request, $user) {
            $mail->to($user->email)
                ->subject($request->sujet)
                ->replyTo(auth('superadmin')->user()->email);
        });

        Log::create([
            'type_operation' => 'email_organisateur',
            'ticket_id' => null,
            'details' => json_encode([
                'user_id' => $user->id,
                'email' => $user->email,
                'sujet' => $request->sujet,
                'envoye_par' => auth('superadmin')->user()->email,
            ]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Email envoyé à {$user->nom}.");
    }

    public function voirOrganisateur(User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $evenements = Evenement::where('user_id', $user->id)
            ->withCount(['tickets as tickets_vendus' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('date_event')
            ->get();

        $totalTickets = $evenements->sum('tickets_vendus');
        $totalRecettes = $evenements->sum('recettes');

        $ticketsQuery = Ticket::whereIn('evenement_id', $evenements->pluck('id'))->where('statut_paiement', 'payé');

        $mobileRecettes = (clone $ticketsQuery)->where('methode_paiement', '!=', 'cash')->sum('montant');
        $cashRecettes = (clone $ticketsQuery)->where('methode_paiement', 'cash')->sum('montant');

        $commissionPct = \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE;
        $commission = round($totalRecettes * $commissionPct / 100, 2);
        $recettesNettes = $totalRecettes - $commission;
        if ($totalRecettes > 0) {
            $retirable = $mobileRecettes - round($commission * ($mobileRecettes / $totalRecettes), 2);
        } else {
            $retirable = 0;
        }

        $aujourdhui = Ticket::whereIn('evenement_id', $evenements->pluck('id'))
            ->where('statut_paiement', 'payé')
            ->whereDate('date_achat', today())
            ->count();

        $scansAujourdhui = Log::where('type_operation', 'scan')
            ->whereDate('created_at', today())
            ->whereHas('ticket', fn($q) => $q->whereIn('evenement_id', $evenements->pluck('id')))
            ->count();

        $agentsScan = Agent::whereIn('evenement_id', $evenements->pluck('id'))->count();
        $agentsVente = AgentVente::whereIn('evenement_id', $evenements->pluck('id'))->count();

        $tickets = Ticket::whereIn('evenement_id', $evenements->pluck('id'))
            ->with('evenement', 'tarif')
            ->where('statut_paiement', 'payé')
            ->latest('date_achat')
            ->paginate(50);

        return view('superadmin.organisateur-show', compact(
            'user', 'evenements', 'totalTickets', 'totalRecettes',
            'aujourdhui', 'scansAujourdhui',
            'agentsScan', 'agentsVente', 'tickets',
            'mobileRecettes', 'cashRecettes', 'commissionPct',
            'commission', 'recettesNettes', 'retirable'
        ));
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

    public function demandesRemboursement()
    {
        $demandes = DemandeRemboursement::with('organisateur', 'evenement', 'tickets')
            ->orderByRaw("FIELD(statut, 'en_attente', 'en_cours', 'rembourse', 'refuse')")
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'en_attente' => DemandeRemboursement::where('statut', 'en_attente')->count(),
            'en_cours' => DemandeRemboursement::where('statut', 'en_cours')->count(),
            'total_montant' => DemandeRemboursement::whereIn('statut', ['en_attente', 'en_cours'])->sum('montant_total'),
            'rembourse_mois' => DemandeRemboursement::where('statut', 'rembourse')
                ->whereMonth('traitee_le', now()->month)->sum('montant_total'),
        ];

        return view('superadmin.remboursements.index', compact('demandes', 'stats'));
    }

    public function voirDemandeRemboursement(DemandeRemboursement $demande)
    {
        $demande->load('organisateur', 'evenement', 'tickets.tarif', 'traiteePar');
        $soldeOrganisateur = $demande->organisateur->solde;
        return view('superadmin.remboursements.show', compact('demande', 'soldeOrganisateur'));
    }

    public function approuverDemandeRemboursement(DemandeRemboursement $demande, Request $request)
    {
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $solde = $demande->organisateur->solde;
        if ($solde < $demande->montant_total) {
            return back()->with('error', 'Solde insuffisant de l\'organisateur ('
                . number_format($solde, 0, ',', ' ') . ' F) pour couvrir ce remboursement de '
                . number_format($demande->montant_total, 0, ',', ' ') . ' F.');
        }

        $validated = $request->validate([
            'notes_admin' => 'nullable|string|max:1000',
        ]);

        $demande->update([
            'statut' => 'en_cours',
            'notes_admin' => $validated['notes_admin'] ?? null,
            'traitee_par' => auth('superadmin')->id(),
        ]);

        Log::create([
            'type_operation' => 'remboursement',
            'ticket_id' => null,
            'details' => json_encode([
                'action' => 'approbation_remboursement',
                'demande_id' => $demande->id,
                'montant' => $demande->montant_total,
                'par' => auth('superadmin')->user()->email,
            ]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', 'Demande approuvée. Le superadmin peut maintenant procéder au remboursement sur FedaPay puis confirmer.');
    }

    public function confirmerRemboursement(DemandeRemboursement $demande)
    {
        if ($demande->statut !== 'en_cours') {
            return back()->with('error', 'Cette demande doit d\'abord être en cours.');
        }

        $demande->load('tickets', 'organisateur', 'evenement');

        DB::beginTransaction();
        try {
            foreach ($demande->tickets as $ticket) {
                $ticket->update(['statut_paiement' => 'remboursé']);

                Log::create([
                    'ticket_id' => $ticket->id,
                    'type_operation' => 'remboursement',
                    'details' => json_encode([
                        'action' => 'remboursement_effectue',
                        'demande_id' => $demande->id,
                        'montant' => $ticket->montant,
                        'transaction_id' => $ticket->transaction_id,
                        'par' => auth('superadmin')->user()->email,
                    ]),
                    'ip' => request()->ip(),
                ]);
            }

            $demande->update([
                'statut' => 'rembourse',
                'traitee_le' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la confirmation du remboursement.');
        }

        $nomsTickets = $demande->tickets->pluck('code_unique')->implode(', ');
        $nb = $demande->tickets->count();
        $montant = number_format($demande->montant_total, 0, ',', ' ');

        foreach ($demande->tickets as $ticket) {
            try {
                Mail::raw(
                    "Votre ticket pour \"{$ticket->evenement->titre}\" a été remboursé.\n\n" .
                    "Code ticket : {$ticket->code_unique}\n" .
                    "Montant remboursé : " . number_format($ticket->montant, 0, ',', ' ') . " F\n" .
                    "Motif : {$demande->motif}\n\n" .
                    "Si vous avez des questions, contactez l'organisateur.",
                    function ($m) use ($ticket) {
                        $m->to($ticket->email_acheteur)
                          ->subject("[PaxEvent] Remboursement effectué - {$ticket->evenement->titre}");
                    }
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Email remboursement non envoyé à ' . $ticket->email_acheteur);
            }
        }

        try {
            Mail::raw(
                "Bonjour {$demande->organisateur->nom},\n\n" .
                "La demande de remboursement pour {$demande->evenement?->titre} a été confirmée.\n" .
                "Tickets concernés : {$nomsTickets}\n" .
                "Montant total : {$montant} F\n\n" .
                "Le remboursement a été traité via FedaPay.",
                function ($m) use ($demande) {
                    $m->to($demande->organisateur->email)
                      ->subject("[PaxEvent] Remboursement confirmé - {$demande->evenement?->titre}");
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email organisateur remboursement non envoyé');
        }

        Message::create([
            'user_id' => null,
            'evenement_id' => $demande->evenement_id,
            'nom_complet' => auth('superadmin')->user()->nom,
            'email' => auth('superadmin')->user()->email,
            'objet' => 'Remboursement traité - ' . ($demande->evenement?->titre ?? ''),
            'message' => "Remboursement de {$montant} F confirmé pour {$nb} ticket(s).\nTraitée par " . auth('superadmin')->user()->email,
        ]);

        return redirect()->route('superadmin.remboursements.demandes')
            ->with('success', "Remboursement confirmé. {$nb} ticket(s) remboursé(s) pour {$montant} F. Les acheteurs ont été notifiés par email.");
    }

    public function refuserDemandeRemboursement(DemandeRemboursement $demande, Request $request)
    {
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $validated = $request->validate([
            'motif_refus' => 'required|string|min:5|max:2000',
        ]);

        $demande->update([
            'statut' => 'refuse',
            'notes_admin' => $validated['motif_refus'],
            'traitee_par' => auth('superadmin')->id(),
            'traitee_le' => now(),
        ]);

        try {
            Mail::raw(
                "Bonjour {$demande->organisateur->nom},\n\n" .
                "Votre demande de remboursement pour {$demande->evenement?->titre} a été refusée.\n\n" .
                "Motif : {$validated['motif_refus']}\n\n" .
                "Contactez le superadmin pour plus d'informations.",
                function ($m) use ($demande) {
                    $m->to($demande->organisateur->email)
                      ->subject("[PaxEvent] Demande de remboursement refusée");
                }
            );
        } catch (\Exception $e) {}

        Log::create([
            'type_operation' => 'remboursement',
            'ticket_id' => null,
            'details' => json_encode([
                'action' => 'refus_remboursement',
                'demande_id' => $demande->id,
                'motif' => $validated['motif_refus'],
                'par' => auth('superadmin')->user()->email,
            ]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', 'Demande de remboursement refusée.');
    }
}
