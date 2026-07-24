<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;
use App\Mail\RegistrationCorrections;
use App\Mail\NewsletterMassEmail;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SuperAdminController extends Controller
{
    // Tableau de bord super admin avec statistiques globales de la plateforme
    public function dashboard()
    {
        $now = now();
        $today = $now->copy()->startOfDay();

        // Compteurs globaux
        $totalUsers = User::count();
        $totalOrganisateurs = User::where('role', 'admin')->count();
        $totalSuperAdmins = User::where('role', 'super_admin')->count();
        $totalEvenements = Evenement::count();
        $evenementsActifs = Evenement::where('statut', 'publié')->where('date_event', '>=', $now)->count();
        $ticketsVendus = Ticket::where('statut_paiement', 'payé')->count();
        $recettesGlobales = Ticket::where('statut_paiement', 'payé')->sum('montant');
        $scansAujourdhui = Ticket::where('utilise', true)->whereDate('updated_at', $today)->count();
        

        // Ventes des 7 derniers jours pour graphique
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

        // Top événements par ventes
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

        // Activité en direct
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

        // Alertes de sécurité
        $scanInvalides = Log::where('type_operation', 'scan')->where('created_at', '>=', $today)->count();
        $paiementsEchoues = Ticket::where('statut_paiement', 'échoué')->count();
        // Tickets suspects (>3 achats par même email/événement)
        $ticketsDupliques = Ticket::select('email_acheteur', 'evenement_id', DB::raw('count(*) as total'))
            ->where('statut_paiement', 'payé')
            ->groupBy('email_acheteur', 'evenement_id')
            ->having('total', '>', 3)
            ->get()->count();

        // Indicateurs financiers
        $transactionsReussies = Ticket::where('statut_paiement', 'payé')->where('transaction_id', 'not like', 'GRATUIT-%')->count();
        $transactionsEchouees = Ticket::where('statut_paiement', 'échoué')->count();
        $montantsJournaliers = Ticket::where('statut_paiement', 'payé')->whereDate('date_achat', $today)->sum('montant');
        $commissionPct = \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE;
        $commissionPlateforme = Ticket::where('statut_paiement', 'payé')->sum(DB::raw("montant * $commissionPct / 100")); // Commission totale

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

    // Liste de tous les utilisateurs
    public function utilisateurs()
    {
        $users = User::withCount('evenements')->orderByDesc('created_at')->paginate(20);
        return view('superadmin.utilisateurs', compact('users'));
    }

    // Liste des organisateurs avec leurs statistiques
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

    // Liste de tous les événements
    public function evenements()
    {
        $evenements = Evenement::with('user')
            ->withCount(['tickets as tickets_vendus' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.evenements', compact('evenements'));
    }

    // Liste des transactions financières
    public function transactions()
    {
        $transactions = Ticket::whereNotNull('transaction_id')
            ->where('transaction_id', 'not like', 'GRATUIT-%')
            ->with('evenement')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.transactions', compact('transactions'));
    }

    // Liste de tous les tickets
    public function tickets()
    {
        $allTickets = Ticket::with('evenement')->orderByDesc('created_at')->paginate(20);
        return view('superadmin.tickets', compact('allTickets'));
    }

    // Historique des scans
    public function scans()
    {
        $logs = Log::with('ticket.evenement')
            ->where('type_operation', 'scan')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('superadmin.scans', compact('logs'));
    }

    // Statistiques par événement
    public function statistiques()
    {
        $evenements = Evenement::select('id', 'titre', 'capacite', 'quota_vendu', 'date_event', 'statut')
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('date_event')
            ->get();
        return view('superadmin.statistiques', compact('evenements'));
    }

    // Logs de sécurité (échecs de paiement, erreurs)
    public function securite()
    {
        $logsSuspects = Log::whereIn('type_operation', ['echec_paiement', 'erreur_paiement'])
            ->with('ticket.evenement')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return view('superadmin.securite', compact('logsSuspects'));
    }

    // Notifications non lues (messages système)
    public function notifications()
    {
        $messages = Message::whereNull('user_id')->orderByDesc('created_at')->paginate(20);
        return view('superadmin.notifications', compact('messages'));
    }

    // Marque une notification comme lue
    public function lireNotification(Message $message)
    {
        if ($message->user_id !== null) {
            abort(403); // Notification système uniquement
        }
        $message->update(['lu' => true]);
        return response()->json(['success' => true]);
    }

    // Supprime une notification
    public function supprimerNotification(Message $message)
    {
        if ($message->user_id !== null) {
            abort(403); // Notification système uniquement
        }
        $message->delete();
        return back()->with('success', 'Notification supprimée.');
    }

    // Page des paramètres super admin
    public function parametres()
    {
        $user = Auth::user();
        return view('superadmin.parametres', compact('user'));
    }

    // Mise à jour du profil super admin (nom, email, téléphone)
    public function updateParametresProfil(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.unique' => 'Cet email est déjà utilisé par un autre compte.',
        ]);

        $user->update($validated);

        return redirect()->route('superadmin.parametres')->with('success', 'Profil mis à jour avec succès.');
    }

    // Mise à jour des réseaux sociaux du super admin
    public function updateParametresReseaux(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'facebook_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'tiktok_url' => 'nullable|url|max:500',
            'twitter_url' => 'nullable|url|max:500',
            'youtube_url' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'website_url' => 'nullable|url|max:500',
        ], [
            'facebook_url.url' => 'L\'URL Facebook est invalide.',
            'instagram_url.url' => 'L\'URL Instagram est invalide.',
            'tiktok_url.url' => 'L\'URL TikTok est invalide.',
            'twitter_url.url' => 'L\'URL Twitter est invalide.',
            'youtube_url.url' => 'L\'URL YouTube est invalide.',
            'linkedin_url.url' => 'L\'URL LinkedIn est invalide.',
            'website_url.url' => 'L\'URL du site web est invalide.',
        ]);

        $user->update($validated);

        return redirect()->route('superadmin.parametres')->with('success', 'Réseaux sociaux mis à jour avec succès.');
    }

    // Logs système complets
    public function logsSysteme()
    {
        $logs = Log::with('ticket.evenement')
            ->orderByDesc('created_at')
            ->paginate(30);
        return view('superadmin.logs', compact('logs'));
    }

    // Page de modération des événements annulés
    public function moderation()
    {
        $evenementsSuspendus = Evenement::where('statut', 'annulé')->with('user')->paginate(20);
        return view('superadmin.moderation', compact('evenementsSuspendus'));
    }

    // Suspend un événement (passe en annulé)
    public function suspendreEvenement(Evenement $evenement)
    {
        $evenement->update(['statut' => 'annulé']); // Annule l'événement
        Log::create([ // Log de modération
            'type_operation' => 'evenement_annule',
            'ticket_id' => null,
            'details' => json_encode(['evenement_id' => $evenement->id, 'titre' => $evenement->titre, 'par' => auth('superadmin')->user()->email]),
            'ip' => request()->ip(),
        ]);
        return back()->with('success', 'Evenement suspendu.');
    }

    // Masque un événement (passe en brouillon)
    public function masquerEvenement(Evenement $evenement)
    {
        $evenement->update(['statut' => 'brouillon']);
        return back()->with('success', 'Evenement masque.');
    }

    // Supprime définitivement un événement
    public function supprimerEvenement(Evenement $evenement)
    {
        $evenement->delete();
        return back()->with('success', 'Evenement supprime.');
    }

    // Remet en avant un événement (passe en publié)
    public function mettreEnAvant(Evenement $evenement)
    {
        $evenement->update(['statut' => 'publié']);
        return back()->with('success', 'Evenement mis en avant.');
    }

    // Suspend un organisateur et annule tous ses événements publiés
    public function suspendreOrganisateur(User $user)
    {
        $user->update(['statut' => 'bloque']); // Bloque le compte
        $evenements = $user->evenements()->where('statut', 'publié')->update(['statut' => 'annulé']); // Annule les événements
        Log::create([ // Log de modération
            'type_operation' => 'organisateur_suspendu',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'evenements_annules' => $evenements]),
            'ip' => request()->ip(),
        ]);
        return back()->with('success', 'Organisateur suspendu et ses evenements annules.');
    }

    // Crée un compte organisateur directement depuis le super admin
    public function creerOrganisateur(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'mot_de_passe' => 'required|min:8',
            'telephone' => 'nullable|string|max:20',
            'organisation' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:universitaire,professionnel',
        ]);
        $data['mot_de_passe'] = bcrypt($data['mot_de_passe']);
        $data['role'] = 'admin';
        $data['statut'] = 'actif';
        User::create($data);
        return back()->with('success', 'Organisateur créé.');
    }

    // Approuve un organisateur et lui envoie un email de confirmation
    public function approuverOrganisateur(User $user)
    {
        if ($user->role !== 'admin' || !in_array($user->statut, ['en_attente', 'incomplet'])) {
            return back()->with('error', 'Action non autorisée.'); // Statut inapproprié
        }

        $user->update(['statut' => 'actif']); // Active le compte

        Mail::to($user->email)->send(new RegistrationApproved($user)); // Notification email

        Log::create([ // Log d'action
            'type_operation' => 'organisateur_approuve',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} approuvé. Un email de confirmation a été envoyé.");
    }

    // Rejette un organisateur avec motif et notification
    public function rejeterOrganisateur(Request $request, User $user)
    {
        if ($user->role !== 'admin' || !in_array($user->statut, ['en_attente', 'incomplet', 'corrections_demandees'])) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate(['motif' => 'required|string|max:2000']);

        $user->update(['statut' => 'rejete']);

        Mail::to($user->email)->send(new RegistrationRejected($user, $request->motif));

        Log::create([
            'type_operation' => 'organisateur_rejete',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'motif' => $request->motif]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} rejeté avec notification.");
    }

    // Demande des corrections sur le profil d'un organisateur
    public function demanderCorrectionsOrganisateur(Request $request, User $user)
    {
        if ($user->role !== 'admin' || !in_array($user->statut, ['en_attente', 'incomplet', 'corrections_demandees'])) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate(['motif' => 'required|string|max:2000']);

        $user->update(['statut' => 'corrections_demandees']); // Change le statut

        Mail::to($user->email)->send(new RegistrationCorrections($user, $request->motif)); // Email de notification

        Message::create([ // Notification système dans l'appli
            'user_id' => $user->id,
            'objet' => 'Corrections demandées sur votre profil',
            'message' => "Bonjour {$user->nom},\n\nVotre demande de compte organisateur nécessite des corrections avant de pouvoir être validée.\n\nMotif : {$request->motif}\n\nConnectez-vous à votre compte pour apporter les modifications nécessaires via \"Compléter mon profil\".",
            'lu' => false,
        ]);

        Log::create([
            'type_operation' => 'organisateur_corrections',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'motif' => $request->motif]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Corrections demandées à {$user->nom} par email.");
    }

    // Supprime définitivement un organisateur
    public function supprimerOrganisateur(User $user)
    {
        if ($user->role !== 'admin') {
            return back()->with('error', 'Action non autorisée.'); // Seuls les admins peuvent être supprimés
        }

        $user->delete(); // Suppression en cascade

        Log::create([ // Log de suppression
            'type_operation' => 'organisateur_supprime',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', "Organisateur {$user->nom} supprimé définitivement.");
    }

    // Envoie un email personnalisé à un organisateur
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

    // Vue détaillée d'un organisateur avec toutes ses statistiques
    public function voirOrganisateur(User $user)
    {
        if ($user->role !== 'admin') {
            abort(404); // Seuls les organisateurs sont accessibles
        }

        $evenements = Evenement::where('user_id', $user->id)
            ->withCount(['tickets as tickets_vendus' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('date_event')
            ->get();

        $totalTickets = $evenements->sum('tickets_vendus');
        $totalRecettes = $evenements->sum('recettes');

        $ticketsQuery = Ticket::whereIn('evenement_id', $evenements->pluck('id'))->where('statut_paiement', 'payé');

        $mobileRecettes = (clone $ticketsQuery)->whereNotIn('methode_paiement', ['cash', 'especes'])->sum('montant');
        $cashRecettes = (clone $ticketsQuery)->whereIn('methode_paiement', ['cash', 'especes'])->sum('montant');

        $commissionPct = \App\Http\Controllers\RetraitController::COMMISSION_PERCENTAGE;
        $commission = round($totalRecettes * $commissionPct / 100, 2);
        $recettesNettes = $totalRecettes - $commission;
        $retirable = max(0, $mobileRecettes - $commission);

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

    // Liste des demandes de retrait
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

    // Approuve une demande de retrait
    public function approuverRetrait(Withdrawal $withdrawal, Request $request)
    {
        if ($withdrawal->status !== 'en_attente') {
            return back()->with('error', 'Ce retrait a déjà été traité.'); // Déjà traité
        }

        $withdrawal->update([
            'status' => 'approuvé',
            'admin_notes' => $request->input('admin_notes'),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Retrait approuvé.');
    }

    // Rejette une demande de retrait
    public function rejeterRetrait(Withdrawal $withdrawal, Request $request)
    {
        if ($withdrawal->status !== 'en_attente') {
            return back()->with('error', 'Ce retrait a déjà été traité.'); // Déjà traité
        }

        $withdrawal->update([
            'status' => 'rejeté',
            'admin_notes' => $request->input('admin_notes'),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Retrait rejeté.');
    }

    // Liste des demandes de remboursement
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

    // Détail d'une demande de remboursement
    public function voirDemandeRemboursement(DemandeRemboursement $demande)
    {
        $demande->load('organisateur', 'evenement', 'tickets.tarif', 'traiteePar');
        $soldeOrganisateur = $demande->organisateur->solde; // Solde actuel de l'organisateur
        return view('superadmin.remboursements.show', compact('demande', 'soldeOrganisateur'));
    }

    // Approuve une demande de remboursement (vérifie le solde)
    public function approuverDemandeRemboursement(DemandeRemboursement $demande, Request $request)
    {
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.'); // Déjà traitée
        }

        // Vérifie que l'organisateur a un solde suffisant
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

    // Confirme le remboursement après traitement sur FedaPay
    public function confirmerRemboursement(DemandeRemboursement $demande)
    {
        if ($demande->statut !== 'en_cours') {
            return back()->with('error', 'Cette demande doit d\'abord être en cours.'); // Statut requis
        }

        $demande->load('tickets', 'organisateur', 'evenement');

        DB::beginTransaction(); // Transaction pour atomicité
        try {
            // Rembourse chaque ticket individuellement
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

    // Refuse une demande de remboursement
    public function refuserDemandeRemboursement(DemandeRemboursement $demande, Request $request)
    {
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.'); // Déjà traitée
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
                "Contactez PaxEvent pour plus d'informations.",
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

    // Liste de tous les abonnés newsletter
    public function newsletter()
    {
        $abonnes = Newsletter::orderByDesc('created_at')->paginate(20);
        $totalActifs = Newsletter::where('actif', true)->count();
        $totalInactifs = Newsletter::where('actif', false)->count();

        return view('superadmin.newsletter', compact('abonnes', 'totalActifs', 'totalInactifs'));
    }

    // Envoyer un message à tous les abonnés actifs
    public function envoyerNewsletter(Request $request)
    {
        $validated = $request->validate([
            'objet' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $abonnesActifs = Newsletter::where('actif', true)->pluck('email');

        if ($abonnesActifs->isEmpty()) {
            return back()->with('error', 'Aucun abonné actif à qui envoyer un message.');
        }

        $superadmin = auth('superadmin')->user();

        foreach ($abonnesActifs as $email) {
            Mail::to($email)->queue(new NewsletterMassEmail(
                $validated['objet'],
                $validated['message'],
                $superadmin->nom,
                $superadmin->email
            ));
        }

        Log::create([
            'type_operation' => 'newsletter',
            'ticket_id' => null,
            'details' => json_encode([
                'action' => 'envoi_newsletter_masse',
                'objet' => $validated['objet'],
                'destinataires' => $abonnesActifs->count(),
                'par' => $superadmin->email,
            ]),
            'ip' => $request->ip(),
        ]);

        return back()->with('success', "Message envoyé à {$abonnesActifs->count()} abonné(s).");
    }
}
