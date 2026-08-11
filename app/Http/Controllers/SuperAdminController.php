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
use App\Models\AttributionAgent;
use App\Models\DemandeRemboursement;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SuperAdminController extends Controller
{
    protected ReconciliationService $reconciliation;

    public function __construct(ReconciliationService $reconciliation)
    {
        $this->reconciliation = $reconciliation;
    }
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
        $scansAujourdhui = Log::where('type_operation', 'scan')->whereDate('created_at', $today)->count();


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
            'etudiants' => Ticket::where('nom_tarif', 'like', '%tudiant%')->where('statut_paiement', 'payé')->distinct('email_acheteur')->count('email_acheteur'),
            'externes' => Ticket::where('nom_tarif', 'not like', '%tudiant%')->where('statut_paiement', 'payé')->distinct('email_acheteur')->count('email_acheteur'),
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
        $scanInvalides = Log::where('type_operation', 'scan')
            ->where('details->resultat', '!=', 'valide')
            ->where('created_at', '>=', $today)
            ->count();
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
        $commissionParEvenement = Ticket::where('statut_paiement', 'payé')
            ->select('evenement_id', DB::raw('SUM(montant) as total'))
            ->groupBy('evenement_id')
            ->get();
        $evenementsCommission = Evenement::with('user')
            ->whereIn('id', $commissionParEvenement->pluck('evenement_id'))
            ->get()
            ->keyBy('id');
        $commissionPlateforme = round($commissionParEvenement->sum(function ($groupe) use ($evenementsCommission) {
            return $groupe->total * ($evenementsCommission->get($groupe->evenement_id)?->commissionEffective() ?? 10) / 100;
        }), 2); // Commission effective par événement
        $commissionPct = $recettesGlobales > 0 ? round($commissionPlateforme / $recettesGlobales * 100, 1) : 10;

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
        $users = User::withCount('evenements')->orderByDesc('created_at')->paginate(\App\Support\PerPage::resolve());
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
            ->paginate(\App\Support\PerPage::resolve());
        return view('superadmin.organisateurs', compact('organisateurs'));
    }

    // Liste de tous les événements
    public function evenements()
    {
        $evenements = Evenement::with('user')
            ->withCount(['tickets as tickets_vendus' => fn($q) => $q->where('statut_paiement', 'payé')])
            ->withSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant')
            ->orderByDesc('created_at')
            ->paginate(\App\Support\PerPage::resolve());
        return view('superadmin.evenements', compact('evenements'));
    }

    // Liste des transactions financières
    public function transactions()
    {
        $transactions = Ticket::whereNotNull('transaction_id')
            ->where('transaction_id', 'not like', 'GRATUIT-%')
            ->with('evenement')
            ->orderByDesc('created_at')
            ->paginate(\App\Support\PerPage::resolve());
        return view('superadmin.transactions', compact('transactions'));
    }

    // Liste de tous les tickets
    public function tickets()
    {
        $allTickets = Ticket::with('evenement')->orderByDesc('created_at')->paginate(\App\Support\PerPage::resolve());
        return view('superadmin.tickets', compact('allTickets'));
    }

    // Historique des scans
    public function scans()
    {
        $logs = Log::with('ticket.evenement')
            ->where('type_operation', 'scan')
            ->orderByDesc('created_at')
            ->paginate(\App\Support\PerPage::resolve());
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
        $messages = Message::whereNull('user_id')->orderByDesc('created_at')->paginate(\App\Support\PerPage::resolve());
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
            ->paginate(\App\Support\PerPage::resolve());
        return view('superadmin.logs', compact('logs'));
    }

    // Page de modération des événements annulés
    public function moderation()
    {
        $evenementsSuspendus = Evenement::where('statut', 'annulé')->with('user')->paginate(\App\Support\PerPage::resolve());
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

    // Page événement : regroupe les contrôles, les infos et les actions
    public function voirEvenement(Evenement $evenement)
    {
        $evenement->load('user', 'tarifs');
        $evenement->loadCount(['tickets as tickets_vendus' => fn($q) => $q->where('statut_paiement', 'payé')]);
        $evenement->loadSum(['tickets as recettes' => fn($q) => $q->where('statut_paiement', 'payé')], 'montant');

        $ticketsQuery = Ticket::where('evenement_id', $evenement->id)->where('statut_paiement', 'payé');
        $mobileRecettes = (clone $ticketsQuery)->whereNotIn('methode_paiement', ['cash', 'especes'])->sum('montant');
        $cashRecettes = (clone $ticketsQuery)->whereIn('methode_paiement', ['cash', 'especes'])->sum('montant');

        $commissionPct = $evenement->commissionEffective();
        $commission = round($evenement->recettes * $commissionPct / 100, 2);
        $recettesNettes = $evenement->recettes - $commission;

        $tickets = Ticket::where('evenement_id', $evenement->id)
            ->with('tarif')
            ->latest('date_achat')
            ->limit(20)
            ->get();
        $ticketsScannes = Ticket::where('evenement_id', $evenement->id)->where('utilise', true)->count();
        $agentsScan = Agent::where('evenement_id', $evenement->id)->count();
        $agentsVente = AgentVente::where('evenement_id', $evenement->id)->count();

        $ticketsEnAttente = Ticket::where('evenement_id', $evenement->id)->where('statut_paiement', 'en_attente')->count();
        $incidentsSupport = Ticket::where('evenement_id', $evenement->id)
            ->where('statut_paiement', 'en_attente')
            ->whereNotNull('fedapay_transaction_id')
            ->count();

        $historique = $this->historiqueAjustements('evenement', $evenement->id);

        return view('superadmin.evenement-show', compact(
            'evenement', 'mobileRecettes', 'cashRecettes',
            'commissionPct', 'commission', 'recettesNettes',
            'tickets', 'ticketsScannes', 'agentsScan', 'agentsVente',
            'ticketsEnAttente', 'incidentsSupport',
            'historique'
        ));
    }

    // Met à jour les contrôles spécifiques d'un événement (ventes espèces + commission + agents de vente)
    public function updateControlesEvenement(Request $request, Evenement $evenement)
    {
        $validated = $request->validate([
            'ventes_especes' => 'nullable|in:toujours,jamais',
            'commission_pourcentage' => 'nullable|numeric|min:0|max:10',
            'max_agents_vente' => 'nullable|integer|in:0,5,10',
        ]);

        $nouveau = $this->normaliserControles($validated);
        $nouveau['max_agents_vente'] = (isset($validated['max_agents_vente']) && $validated['max_agents_vente'] !== '')
            ? (int) $validated['max_agents_vente']
            : null;
        $ancien = [
            'ventes_especes' => $evenement->ventes_especes,
            'commission_pourcentage' => $evenement->commission_pourcentage,
            'max_agents_vente' => $evenement->max_agents_vente,
        ];

        $evenement->update($nouveau);
        $this->logAjustement('evenement', [
            'evenement_id' => $evenement->id,
            'evenement_titre' => $evenement->titre,
            'ancien' => $ancien,
            'nouveau' => $nouveau,
        ]);
        if ($nouveau['commission_pourcentage'] !== $ancien['commission_pourcentage']) {
            $this->notifierCommission($evenement->user, $evenement, $ancien['commission_pourcentage'], $nouveau['commission_pourcentage']);
        }

        return back()->with('success', "Contrôles de l'événement mis à jour.");
    }

    // Met à jour les contrôles spécifiques d'un organisateur (ventes espèces + commission)
    public function updateControlesOrganisateur(Request $request, User $user)
    {
        $validated = $request->validate([
            'ventes_especes' => 'nullable|in:toujours,jamais',
            'commission_pourcentage' => 'nullable|numeric|min:0|max:10',
        ]);

        $nouveau = $this->normaliserControles($validated);
        $ancien = [
            'ventes_especes' => $user->ventes_especes,
            'commission_pourcentage' => $user->commission_pourcentage,
        ];

        $user->update($nouveau);
        $this->logAjustement('organisateur', [
            'organisateur_id' => $user->id,
            'organisateur_nom' => $user->nom,
            'ancien' => $ancien,
            'nouveau' => $nouveau,
        ]);
        if ($nouveau['commission_pourcentage'] !== $ancien['commission_pourcentage']) {
            $this->notifierCommission($user, null, $ancien['commission_pourcentage'], $nouveau['commission_pourcentage']);
        }

        return back()->with('success', "Contrôles de l'organisateur mis à jour.");
    }

    // Attribue à un organisateur un nombre d'agents scan + vente (dashboard complet ou événement précis)
    public function attribuerAgents(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'portee' => 'required|in:dashboard,evenement',
            'evenement_id' => 'nullable|integer',
            'nb_agents_scan' => 'required|integer|min:0',
            'nb_agents_vente' => 'required|integer|min:0',
        ]);

        $evenementId = null;
        if ($validated['portee'] === 'evenement') {
            $evenement = Evenement::where('user_id', $user->id)->findOrFail($validated['evenement_id']);
            $evenementId = $evenement->id;
        }

        AttributionAgent::updateOrCreate(
            ['user_id' => $user->id, 'evenement_id' => $evenementId],
            [
                'nb_agents_scan' => (int) $validated['nb_agents_scan'],
                'nb_agents_vente' => (int) $validated['nb_agents_vente'],
            ]
        );

        $portee = $evenementId ? "sur l'événement « {$evenement->titre} »" : 'sur tout le dashboard';

        return back()->with('success', "Attribution d'agents mise à jour ({$portee}).");
    }

    // Supprime une attribution d'agents
    public function supprimerAttribution(AttributionAgent $attribution)
    {
        $attribution->delete();

        return back()->with('success', 'Attribution supprimée.');
    }

    // Politique de vente espèces avec portée (dashboard = tous les événements, evenement = événement précis)
    public function definirVenteEspeces(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'portee' => 'required|in:dashboard,evenement',
            'evenement_id' => 'nullable|integer',
            'ventes_especes' => 'required|in:auto,toujours,jamais',
        ]);

        $valeur = $validated['ventes_especes'] === 'auto' ? null : $validated['ventes_especes'];

        if ($validated['portee'] === 'evenement') {
            $evenement = Evenement::where('user_id', $user->id)->findOrFail($validated['evenement_id']);
            $ancien = $evenement->ventes_especes;
            $evenement->update(['ventes_especes' => $valeur]);
            $this->logAjustement('evenement', [
                'evenement_id' => $evenement->id,
                'evenement_titre' => $evenement->titre,
                'ancien' => ['ventes_especes' => $ancien],
                'nouveau' => ['ventes_especes' => $valeur],
            ]);

            return back()->with('success', "Ventes espèces mises à jour sur « {$evenement->titre} ».");
        }

        $ancien = $user->ventes_especes;
        $user->update(['ventes_especes' => $valeur]);
        $this->logAjustement('organisateur', [
            'organisateur_id' => $user->id,
            'organisateur_nom' => $user->nom,
            'ancien' => ['ventes_especes' => $ancien],
            'nouveau' => ['ventes_especes' => $valeur],
        ]);

        return back()->with('success', 'Ventes espèces mises à jour pour tout le dashboard.');
    }

    // Pourcentage de commission avec portée (dashboard = tous les événements, evenement = événement précis)
    public function definirCommission(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'portee' => 'required|in:dashboard,evenement',
            'evenement_id' => 'nullable|integer',
            'commission_pourcentage' => 'nullable|numeric|min:0|max:10',
        ]);

        $valeur = (isset($validated['commission_pourcentage']) && $validated['commission_pourcentage'] !== '')
            ? (float) $validated['commission_pourcentage']
            : null;

        if ($validated['portee'] === 'evenement') {
            $evenement = Evenement::where('user_id', $user->id)->findOrFail($validated['evenement_id']);
            $ancien = $evenement->commission_pourcentage;
            $evenement->update(['commission_pourcentage' => $valeur]);
            $this->logAjustement('evenement', [
                'evenement_id' => $evenement->id,
                'evenement_titre' => $evenement->titre,
                'ancien' => ['commission_pourcentage' => $ancien],
                'nouveau' => ['commission_pourcentage' => $valeur],
            ]);
            $this->notifierCommission($user, $evenement, $ancien, $valeur);

            return back()->with('success', "Commission mise à jour sur « {$evenement->titre} ».");
        }

        $ancien = $user->commission_pourcentage;
        $user->update(['commission_pourcentage' => $valeur]);
        $this->logAjustement('organisateur', [
            'organisateur_id' => $user->id,
            'organisateur_nom' => $user->nom,
            'ancien' => ['commission_pourcentage' => $ancien],
            'nouveau' => ['commission_pourcentage' => $valeur],
        ]);
        $this->notifierCommission($user, null, $ancien, $valeur);

        return back()->with('success', 'Commission mise à jour pour tout le dashboard.');
    }

    // Notifie l'organisateur d'un changement de commission (ou réduction)
    protected function notifierCommission(User $user, ?Evenement $evenement, $ancien, $nouveau): void
    {
        $cible = $evenement ? " pour l'événement « {$evenement->titre} »" : ' pour tous vos événements';
        $ancienVal = is_null($ancien) ? 'par défaut' : round((float) $ancien, 1) . ' %';
        $nouveauVal = is_null($nouveau) ? 'valeur par défaut' : round((float) $nouveau, 1) . ' %';

        $verbe = (is_null($nouveau) || is_null($ancien)) ? 'mise à jour' : ((float) $nouveau < (float) $ancien ? 'réduite' : 'mise à jour');
        $objet = 'Votre commission a été ' . $verbe;
        $message = "Bonjour {$user->nom},\n\n"
            . "Votre commission a été modifiée{$cible} : elle passe de {$ancienVal} à {$nouveauVal}.\n\n"
            . "Pour toute question, contactez le support PaxEvent.";

        $this->notifierOrganisateur($user, $objet, $message, $evenement?->id);
    }

    // Réinitialise un contrôle (ventes espèces ou commission) pour le dashboard ou un événement
    public function reinitialiserControle(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'champ' => 'required|in:ventes_especes,commission_pourcentage',
            'niveau' => 'required|in:dashboard,evenement',
            'evenement_id' => 'nullable|integer',
        ]);

        if ($validated['niveau'] === 'evenement') {
            $evenement = Evenement::where('user_id', $user->id)->findOrFail($validated['evenement_id']);
            $ancien = $evenement->{$validated['champ']};
            $evenement->update([$validated['champ'] => null]);
            $this->logAjustement('evenement', [
                'evenement_id' => $evenement->id,
                'evenement_titre' => $evenement->titre,
                'ancien' => [$validated['champ'] => $ancien],
                'nouveau' => [$validated['champ'] => null],
            ]);
            if ($validated['champ'] === 'commission_pourcentage') {
                $this->notifierCommission($user, $evenement, $ancien, null);
            }

            return back()->with('success', "Réinitialisé sur « {$evenement->titre} » (valeur par défaut).");
        }

        $ancien = $user->{$validated['champ']};
        $user->update([$validated['champ'] => null]);
        $this->logAjustement('organisateur', [
            'organisateur_id' => $user->id,
            'organisateur_nom' => $user->nom,
            'ancien' => [$validated['champ'] => $ancien],
            'nouveau' => [$validated['champ'] => null],
        ]);
        if ($validated['champ'] === 'commission_pourcentage') {
            $this->notifierCommission($user, null, $ancien, null);
        }

        return back()->with('success', 'Réinitialisé pour tout le dashboard (valeur par défaut).');
    }

    // Convertit les valeurs du formulaire (champ vide = héritage / défaut)
    protected function normaliserControles(array $validated): array
    {
        return [
            'ventes_especes' => $validated['ventes_especes'] ?? null,
            'commission_pourcentage' => (isset($validated['commission_pourcentage']) && $validated['commission_pourcentage'] !== '')
                ? (float) $validated['commission_pourcentage']
                : null,
        ];
    }

    // Enregistre un changement de taux/statut dans l'historique
    protected function logAjustement(string $niveau, array $details): void
    {
        Log::create([
            'type_operation' => 'ajustement',
            'ticket_id' => null,
            'details' => array_merge([
                'niveau' => $niveau,
                'par' => auth('superadmin')->user()?->email ?? 'superadmin',
            ], $details),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Notifie l'organisateur dans son espace (message affiché sur le dashboard)
    protected function notifierOrganisateur(User $user, string $objet, string $message, ?int $evenementId = null): void
    {
        Message::create([
            'user_id' => $user->id,
            'evenement_id' => $evenementId,
            'nom_complet' => $user->nom,
            'email' => $user->email,
            'objet' => $objet,
            'message' => $message,
            'lu' => false,
        ]);
    }

    // Historique paginé des ajustements (et annulations) pour un événement ou un organisateur
    protected function historiqueAjustements(string $niveau, int $id, ?int $perPage = null)
    {
        $perPage = $perPage ?? \App\Support\PerPage::resolve();
        $logs = Log::where('ticket_id', null)
            ->whereIn('type_operation', ['ajustement', 'evenement_annule'])
            ->orderByDesc('created_at')
            ->limit(500)
            ->get()
            ->filter(function ($log) use ($niveau, $id) {
                $details = $log->details ?? [];
                if (($details['niveau'] ?? null) === $niveau) {
                    return (int) ($details[$niveau . '_id'] ?? 0) === $id;
                }
                if ($niveau === 'evenement' && $log->type_operation === 'evenement_annule') {
                    return (int) ($details['evenement_id'] ?? 0) === $id;
                }
                return false;
            })
            ->values();

        $pageName = 'historique';
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $items = $logs->forPage($page, $perPage)->values();

        return (new LengthAwarePaginator($items, $logs->count(), $perPage, $page, [
            'path' => request()->url(),
        ]))->withQueryString()->setPageName($pageName);
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

    // Réactive un organisateur bloqué ou rejeté (restaure les événements annulés par la suspension)
    public function reactiverOrganisateur(User $user)
    {
        if ($user->role !== 'admin' || !in_array($user->statut, ['bloque', 'rejete'])) {
            return back()->with('error', 'Action non autorisée.');
        }

        $etaitBloque = $user->statut === 'bloque';
        $user->update(['statut' => 'actif']);

        $evenements = 0;
        if ($etaitBloque) {
            $evenements = $user->evenements()->where('statut', 'annulé')->update(['statut' => 'publié']);
        }

        Log::create([
            'type_operation' => 'organisateur_reactive',
            'ticket_id' => null,
            'details' => json_encode(['user_id' => $user->id, 'email' => $user->email, 'evenements_restaures' => $evenements]),
            'ip' => request()->ip(),
        ]);

        return back()->with('success', $etaitBloque
            ? 'Organisateur réactivé et ses événements annulés republiés.'
            : "Organisateur {$user->nom} réactivé.");
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

        $commissionPct = $user->commissionPourcentage();
        $statsFinancieres = $user->statsFinancieres();
        $commission = $statsFinancieres['commissionTotale'];
        $recettesNettes = $totalRecettes - $commission;
        $physiqueRecettes = $statsFinancieres['physiqueRecettes'];
        $commissionPhysique = $statsFinancieres['commissionPhysique'];
        $totalRetraits = (float) Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['en_attente', 'en_cours', 'payé'])
            ->sum('montant');
        $retirable = max(0, $mobileRecettes - $commission - $totalRetraits);

        $historique = $this->historiqueAjustements('organisateur', $user->id);

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

        $attributions = AttributionAgent::with('evenement')
            ->where('user_id', $user->id)
            ->orderByRaw('evenement_id IS NULL')
            ->orderBy('id')
            ->get();

        foreach ($attributions as $attr) {
            if ($attr->evenement_id) {
                $attr->usage_scan = Agent::where('evenement_id', $attr->evenement_id)->where('actif', true)->count();
                $attr->usage_vente = AgentVente::where('evenement_id', $attr->evenement_id)->where('actif', true)->count();
            } else {
                $attr->usage_scan = Agent::whereIn('evenement_id', $evenements->pluck('id'))->where('actif', true)->count();
                $attr->usage_vente = AgentVente::whereIn('evenement_id', $evenements->pluck('id'))->where('actif', true)->count();
            }
        }

        $tickets = Ticket::whereIn('evenement_id', $evenements->pluck('id'))
            ->with('evenement', 'tarif')
            ->where('statut_paiement', 'payé')
            ->latest('date_achat')
            ->paginate(\App\Support\PerPage::resolve());

        return view('superadmin.organisateur-show', compact(
            'user', 'evenements', 'totalTickets', 'totalRecettes',
            'aujourdhui', 'scansAujourdhui',
            'agentsScan', 'agentsVente', 'attributions', 'tickets',
            'mobileRecettes', 'cashRecettes', 'commissionPct',
            'commission', 'recettesNettes', 'retirable',
            'physiqueRecettes', 'commissionPhysique',
            'historique'
        ));
    }

    // Liste des demandes de retrait
    public function retraits()
    {
        $retraits = Withdrawal::with('user')
            ->orderByRaw("FIELD(status, 'en_attente', 'en_cours', 'approuvé', 'payé', 'rejeté')")
            ->orderByDesc('created_at')
            ->paginate(\App\Support\PerPage::resolve());

        $stats = [
            'en_attente' => Withdrawal::where('status', 'en_attente')->count(),
            'en_cours' => Withdrawal::where('status', 'en_cours')->count(),
            'approuve' => Withdrawal::where('status', 'payé')->sum('montant'),
            'total' => Withdrawal::where('status', 'payé')->count(),
        ];

        return view('superadmin.retraits', compact('retraits', 'stats'));
    }

    // Approuve une demande → en cours de traitement
    public function approuverRetrait(Withdrawal $withdrawal, Request $request)
    {
        $updated = Withdrawal::where('id', $withdrawal->id)->where('status', 'en_attente')->update([
            'status' => 'en_cours',
            'admin_notes' => $request->input('admin_notes'),
            'processed_at' => now(),
        ]);

        if (!$updated) {
            return back()->with('error', 'Ce retrait a déjà été traité.');
        }

        $withdrawal->refresh();
        RetraitController::notifierOrganisateur($withdrawal, 'en_cours');

        return back()->with('success', 'Retrait approuvé. En attente de transfert.');
    }

    // Confirme le paiement effectué → payé
    public function confirmerRetrait(Withdrawal $withdrawal, Request $request)
    {
        $updated = Withdrawal::where('id', $withdrawal->id)->where('status', 'en_cours')->update([
            'status' => 'payé',
            'admin_notes' => $request->input('admin_notes', $withdrawal->admin_notes),
            'processed_at' => now(),
        ]);

        if (!$updated) {
            return back()->with('error', 'Ce retrait n\'est pas en cours de traitement.');
        }

        $withdrawal->refresh();
        RetraitController::notifierOrganisateur($withdrawal, 'paye');

        return back()->with('success', 'Paiement confirmé. L\'organisateur a été notifié.');
    }

    // Rejette une demande de retrait
    public function rejeterRetrait(Withdrawal $withdrawal, Request $request)
    {
        if (!in_array($withdrawal->status, ['en_attente', 'en_cours'])) {
            return back()->with('error', 'Ce retrait ne peut plus être rejeté.');
        }

        $motifs = $request->input('motifs', []);
        $autreRaison = trim($request->input('autre_raison', ''));

        $raisons = [];
        $labels = [
            'numero_invalide' => 'Numéro invalide',
            'doublon' => 'Doublon de demande',
            'numero_reseau' => 'Numéro ne correspond pas au réseau sélectionné',
        ];
        foreach ($motifs as $m) {
            if (isset($labels[$m])) $raisons[] = $labels[$m];
        }
        if ($autreRaison !== '') $raisons[] = $autreRaison;

        $notes = $raisons ? implode("\n", $raisons) : ($request->input('admin_notes') ?: 'Non spécifiée');

        $withdrawal->update([
            'status' => 'rejeté',
            'admin_notes' => $notes,
            'processed_at' => now(),
        ]);

        RetraitController::notifierOrganisateur($withdrawal, 'rejete');

        return back()->with('success', 'Retrait rejeté. L\'organisateur a été notifié.');
    }

    // Liste des demandes de remboursement
    public function demandesRemboursement()
    {
        $demandes = DemandeRemboursement::with('organisateur', 'evenement', 'tickets')
            ->orderByRaw("FIELD(statut, 'en_attente', 'en_cours', 'rembourse', 'refuse')")
            ->orderByDesc('created_at')
            ->paginate(\App\Support\PerPage::resolve());

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
        $abonnes = Newsletter::orderByDesc('created_at')->paginate(\App\Support\PerPage::resolve());
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

    // ============================================================
    // Support technique : réconciliation des paiements FedaPay
    // ============================================================

    // Recherche un incident paiement par transaction, email, téléphone ou code
    public function support(Request $request)
    {
        $transactionId = trim($request->input('transaction_id', ''));
        $email = strtolower(trim($request->input('email', '')));
        $telephone = trim($request->input('telephone', ''));
        $code = trim($request->input('code', ''));
        $evenementId = $request->integer('evenement_id') ?: null;

        $tickets = collect();
        $verification = $request->session()->get('verification');

        if ($transactionId || $email || $telephone || $code || $evenementId) {
            $tickets = $this->reconciliation->trouverTickets(
                $transactionId ?: null,
                $email ?: null,
                $telephone ?: null,
                $code ?: null,
                $evenementId
            );
        }

        $incidents = Ticket::where('statut_paiement', 'en_attente')
            ->whereNotNull('fedapay_transaction_id')
            ->with('evenement')
            ->orderByDesc('date_achat')
            ->limit(30)
            ->get();

        $stats = [
            'en_attente' => Ticket::where('statut_paiement', 'en_attente')->count(),
            'incidents' => Ticket::where('statut_paiement', 'en_attente')->whereNotNull('fedapay_transaction_id')->count(),
            'rembourses_support' => DemandeRemboursement::where('origine', 'support_superadmin')->where('statut', 'rembourse')->sum('montant_total'),
        ];

        return view('superadmin.support.index', compact(
            'tickets',
            'verification',
            'incidents',
            'stats',
            'transactionId',
            'email',
            'telephone',
            'code',
            'evenementId',
        ));
    }

    // Vérifie une transaction FedaPay via l'API et affiche le résultat
    public function supportVerifier(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string|max:100',
        ]);

        $verification = $this->reconciliation->verifier($validated['transaction_id']);

        $tickets = $this->reconciliation->trouverTickets($validated['transaction_id']);

        return back()
            ->with('verification', $verification)
            ->with('verification_tickets', $tickets)
            ->withInput();
    }

    // Confirme des tickets en attente (paiement vérifié, sinon force = override tracé)
    public function supportConfirmer(Request $request)
    {
        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:ticket,id',
            'transaction_id' => 'nullable|string|max:100',
            'force' => 'nullable',
            'notes' => 'nullable|string|max:1000',
        ]);

        $tickets = Ticket::whereIn('id', $validated['ticket_ids'])->get();
        $transactionId = trim($validated['transaction_id'] ?? '');
        if ($transactionId === '') {
            $transactionId = $tickets->first()?->fedapay_transaction_id ?? '';
        }
        $force = $request->boolean('force') || $transactionId === '';

        // Sauf force explicite, on vérifie toujours le paiement via l'API
        if (!$force) {
            $verification = $this->reconciliation->verifier($transactionId);
            if (!$verification['ok'] || !$verification['approuve']) {
                return back()
                    ->withErrors(['transaction_id' => 'Paiement non vérifié via FedaPay (' . ($verification['statut'] ?? 'API injoignable') . '). Utilisez le forçage seulement après contrôle manuel.'])
                    ->withInput();
            }
        }

        $resultat = $this->reconciliation->confirmerTickets(
            $tickets,
            $transactionId !== '' ? $transactionId : null,
            null,
            null,
            $force
        );

        if (!$resultat['success']) {
            return back()->with('error', $resultat['message']);
        }

        Log::create([
            'type_operation' => 'reconciliation',
            'ticket_id' => null,
            'details' => [
                'action' => 'confirmation_support',
                'tickets' => $tickets->pluck('id'),
                'transaction_id' => $transactionId,
                'force' => $force,
                'notes' => $validated['notes'] ?? null,
                'par' => auth('superadmin')->user()->email,
            ],
            'ip' => $request->ip(),
        ]);

        foreach ($tickets as $ticket) {
            $this->marquerNotificationsLues($ticket);
        }

        return back()->with('success', $resultat['message']);
    }

    // Récupère les tarifs d'un événement pour le formulaire « Recréer un ticket »
    public function supportTarifs(Request $request)
    {
        $request->validate([
            'evenement_id' => 'required|exists:evenement,id',
        ]);

        $tarifs = \App\Models\Tarif::where('evenement_id', $request->evenement_id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'prix']);

        return response()->json([
            'tarifs' => $tarifs,
            'gratuit' => Evenement::where('id', $request->evenement_id)->value('gratuit'),
        ]);
    }

    // Recrée un ticket purgé ou manquant
    public function supportRecreer(Request $request)
    {
        $validated = $request->validate([
            'evenement_id' => 'required|exists:evenement,id',
            'tarif_id' => 'nullable|exists:tarifs,id',
            'nom_acheteur' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'montant' => 'nullable|numeric|min:0',
            'quantite' => 'required|integer|min:1|max:20',
            'transaction_id' => 'nullable|string|max:100',
            'fedapay_transaction_id' => 'nullable|string|max:100',
            'methode_paiement' => 'nullable|string|max:50',
        ]);

        $resultat = $this->reconciliation->recreerTicket($validated);

        if (!$resultat['success']) {
            return back()->with('error', $resultat['message']);
        }

        Log::create([
            'type_operation' => 'reconciliation',
            'ticket_id' => null,
            'details' => [
                'action' => 'recreation_support',
                'tickets' => $resultat['tickets']->pluck('id'),
                'par' => auth('superadmin')->user()->email,
            ],
            'ip' => $request->ip(),
        ]);

        return back()->with('success', $resultat['message']);
    }

    // Supprime des tickets en attente abandonnés
    public function supportSupprimer(Request $request)
    {
        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:ticket,id',
            'motif' => 'nullable|string|max:1000',
        ]);

        $tickets = Ticket::whereIn('id', $validated['ticket_ids'])->get();
        $resultat = $this->reconciliation->supprimerGroupe($tickets, $validated['motif'] ?? null);

        Log::create([
            'type_operation' => 'reconciliation',
            'ticket_id' => null,
            'details' => [
                'action' => 'suppression_support',
                'tickets' => $tickets->pluck('id'),
                'par' => auth('superadmin')->user()->email,
            ],
            'ip' => $request->ip(),
        ]);

        return back()->with('success', $resultat['message']);
    }

    // Renvoie l'email d'un ticket
    public function supportRenvoyerEmail(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|integer|exists:ticket,id',
        ]);

        $ticket = Ticket::findOrFail($validated['ticket_id']);
        $resultat = $this->reconciliation->renvoyerEmail($ticket);

        if ($resultat['success']) {
            $this->marquerNotificationsLues($ticket);
        }

        return back()->with(
            $resultat['success'] ? 'success' : 'error',
            $resultat['message']
        );
    }

    /**
     * Marque comme lues les notifications (incidents) liées à un ticket :
     * par ID de transaction FedaPay ou par email d'achat sur un incident paiement.
     */
    protected function marquerNotificationsLues(Ticket $ticket): void
    {
        Message::whereNull('user_id')
            ->where('lu', false)
            ->where(function ($q) use ($ticket) {
                $q->where(function ($t) use ($ticket) {
                    $t->whereNotNull('transaction_id')
                        ->whereIn('transaction_id', array_filter([
                            $ticket->fedapay_transaction_id,
                            $ticket->transaction_id,
                        ]));
                })->orWhere(function ($e) use ($ticket) {
                    $e->whereNotNull('email_achat')
                        ->where('email_achat', $ticket->email_acheteur)
                        ->where('objet', 'like', 'Incident paiement%');
                });
            })
            ->update(['lu' => true]);
    }

    // Affiche et marque comme lues les notifications (incidents) liées à un ticket
    public function supportVoirIncident(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|integer|exists:ticket,id',
        ]);

        $ticket = Ticket::findOrFail($validated['ticket_id']);

        $messages = Message::whereNull('user_id')
            ->where(function ($q) use ($ticket) {
                $q->where(function ($t) use ($ticket) {
                    $t->whereNotNull('transaction_id')
                        ->whereIn('transaction_id', array_filter([
                            $ticket->fedapay_transaction_id,
                            $ticket->transaction_id,
                        ]));
                })->orWhere(function ($e) use ($ticket) {
                    $e->whereNotNull('email_achat')
                        ->where('email_achat', $ticket->email_acheteur)
                        ->where('objet', 'like', 'Incident paiement%');
                });
            })
            ->orderByDesc('created_at')
            ->get();

        if ($messages->isNotEmpty()) {
            Message::whereIn('id', $messages->pluck('id'))->update(['lu' => true]);
        }

        return response()->json([
            'ticket_id' => $ticket->id,
            'code_unique' => $ticket->code_unique,
            'messages' => $messages->map(fn ($m) => [
                'nom_complet' => $m->nom_complet,
                'email' => $m->email,
                'telephone' => $m->telephone,
                'email_achat' => $m->email_achat,
                'objet' => $m->objet,
                'transaction_id' => $m->transaction_id,
                'message' => $m->message,
                'date' => $m->created_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    // Remboursement direct superadmin (tickets payés, sans l'organisateur)
    public function supportRembourser(Request $request)
    {
        $validated = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:ticket,id',
            'motif' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $tickets = Ticket::whereIn('id', $validated['ticket_ids'])->get();

        if ($tickets->where('statut_paiement', '!=', 'payé')->isNotEmpty()) {
            return back()->with('error', 'Seuls les tickets payés peuvent être remboursés.');
        }

        $resultat = $this->reconciliation->rembourser($tickets, $validated['motif'], $validated['notes'] ?? null);

        if (!$resultat['success']) {
            return back()->with('error', $resultat['message']);
        }

        Log::create([
            'type_operation' => 'reconciliation',
            'ticket_id' => null,
            'details' => [
                'action' => 'remboursement_support',
                'tickets' => $tickets->pluck('id'),
                'montant' => $tickets->sum('montant'),
                'par' => auth('superadmin')->user()->email,
            ],
            'ip' => $request->ip(),
        ]);

        return back()->with('success', $resultat['message']);
    }
}
