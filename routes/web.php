<?php

use App\Http\Controllers\Admin\AgentController as AdminAgentController;
use App\Http\Controllers\Admin\AgentVenteController as AdminAgentVenteController;
use App\Http\Controllers\Admin\DemandeSuperAdminController;
use App\Http\Controllers\Admin\LotPhysiqueController as AdminLotPhysiqueController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Agent\AuthController as AgentAuthController;
use App\Http\Controllers\Agent\ScanController as AgentScanController;
use App\Http\Controllers\AgentVente\AuthController as AgentVenteAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CodePromoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\EvenementPublicController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RappelController;
use App\Http\Controllers\RemboursementController;
use App\Http\Controllers\RetraitController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SitePublicController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\SuperAdmin\LotPhysiqueController as SuperAdminLotPhysiqueController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VenteManuelleController;
use App\Models\Evenement;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

// ============================================================
// Routes publiques (participant)
// ============================================================
Route::get('/', [SitePublicController::class, 'accueil'])->name('accueil');
Route::get('/evenements', [EvenementPublicController::class, 'index'])->name('evenements.public');
Route::get('/evenements/{evenement}', [EvenementPublicController::class, 'show'])->name('evenements.public.show');
Route::post('/evenements/{evenement}/achat', [EvenementPublicController::class, 'achat'])->name('evenements.achat')->middleware('throttle:5,1');
Route::post('/evenements/{evenement}/contacter-organisateur', [EvenementPublicController::class, 'contacterOrganisateur'])->name('evenements.contacter-organisateur')->middleware('throttle:5,10');

Route::get('/paiement/callback', [PaiementController::class, 'callback'])->name('paiement.callback');
Route::post('/paiement/webhook', [PaiementController::class, 'webhook'])->name('paiement.webhook');

Route::get('/paiement/{ticket}', [PaiementController::class, 'show'])->name('paiement.show');
Route::get('/confirmation/{ticket}', [PaiementController::class, 'confirmation'])->name('confirmation.show');

Route::get('/recuperer', [TicketController::class, 'recuperer'])->name('tickets.recuperer');
Route::post('/recuperer', [TicketController::class, 'rechercher'])->name('tickets.rechercher');
Route::get('/ticket/{ticket}/telecharger', [TicketController::class, 'downloadTicket'])->name('tickets.telecharger')->middleware('signed');

Route::get('/aide', [SitePublicController::class, 'aide'])->name('aide');
Route::get('/contact', [SitePublicController::class, 'contact'])->name('contact');
Route::post('/contact', [SitePublicController::class, 'contactStore'])->name('contact.store')->middleware('throttle:3,10');
Route::get('/confidentialite', [SitePublicController::class, 'confidentialite'])->name('confidentialite');
Route::get('/cgu', [SitePublicController::class, 'cgu'])->name('cgu');
Route::get('/mentions-legales', [SitePublicController::class, 'mentionsLegales'])->name('mentions-legales');
Route::get('/politique-remboursement', [SitePublicController::class, 'politiqueRemboursement'])->name('politique-remboursement');
Route::get('/conditions-generales-de-vente', [SitePublicController::class, 'cgv'])->name('cgv');
Route::get('/affiliation', [SitePublicController::class, 'affiliation'])->name('affiliation');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe')->middleware('throttle:3,10');
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe')->middleware('throttle:5,10');

// Google OAuth
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// ============================================================
// Routes de connexion & inscription
// ============================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::prefix('inscription')->name('inscriptions.')->group(function () {
    Route::get('/', [InscriptionController::class, 'step0'])->name('organisateur');
    Route::post('/email', [InscriptionController::class, 'sendOtp'])->name('send-otp')->middleware('throttle:3,1');
    Route::get('/verifier', [InscriptionController::class, 'showVerify'])->name('verify');
    Route::post('/verifier', [InscriptionController::class, 'verifyOtp'])->name('verify-otp')->middleware('throttle:5,1');
    Route::post('/renvoyer', [InscriptionController::class, 'resendOtp'])->name('resend-otp')->middleware('throttle:2,30');
    Route::get('/identite', [InscriptionController::class, 'step1'])->name('identity');
    Route::post('/identite', [InscriptionController::class, 'postStep1'])->name('post-identity');
    Route::post('/resoumettre', [InscriptionController::class, 'resubmit'])->name('resubmit')->middleware('auth');
});

// Mot de passe oublié
Route::get('/mot-de-passe-oublie', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/mot-de-passe-oublie', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
Route::get('/reinitialiser/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reinitialiser', [ForgotPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:5,1');

// ============================================================
// Routes super admin
// ============================================================
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');

    Route::middleware(['superadmin', 'no_cache'])->group(function () {
        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');

        // Premiere connexion d'un membre de l'equipe : changement de mot de passe force
        Route::get('/premiere-connexion', [SuperAdminController::class, 'premiereConnexion'])->name('premiere-connexion');
        Route::post('/premiere-connexion', [SuperAdminController::class, 'enregistrerPremiereConnexion'])->name('premiere-connexion.post');

        Route::middleware(['no_cache', 'equipe_permission'])->group(function () {
            Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/utilisateurs', [SuperAdminController::class, 'utilisateurs'])->name('utilisateurs');
        Route::get('/organisateurs', [SuperAdminController::class, 'organisateurs'])->name('organisateurs');
        Route::post('/organisateurs/creer', [SuperAdminController::class, 'creerOrganisateur'])->name('organisateurs.creer');
        Route::post('/organisateurs/{user}/suspendre', [SuperAdminController::class, 'suspendreOrganisateur'])->name('organisateurs.suspendre');
        Route::post('/organisateurs/{user}/reactiver', [SuperAdminController::class, 'reactiverOrganisateur'])->name('organisateurs.reactiver');
        Route::post('/organisateurs/{user}/approuver', [SuperAdminController::class, 'approuverOrganisateur'])->name('organisateurs.approuver');
        Route::post('/organisateurs/{user}/rejeter', [SuperAdminController::class, 'rejeterOrganisateur'])->name('organisateurs.rejeter');
        Route::post('/organisateurs/{user}/corrections', [SuperAdminController::class, 'demanderCorrectionsOrganisateur'])->name('organisateurs.corrections');
        Route::post('/organisateurs/{user}/supprimer', [SuperAdminController::class, 'supprimerOrganisateur'])->name('organisateurs.supprimer');
        Route::post('/organisateurs/{user}/email', [SuperAdminController::class, 'envoyerEmailOrganisateur'])->name('organisateurs.email');
        Route::get('/organisateurs/{user}', [SuperAdminController::class, 'voirOrganisateur'])->name('organisateurs.voir');
        Route::get('/evenements', [SuperAdminController::class, 'evenements'])->name('evenements');
        Route::post('/evenements/{evenement}/suspendre', [SuperAdminController::class, 'suspendreEvenement'])->name('evenements.suspendre');
        Route::post('/evenements/{evenement}/masquer', [SuperAdminController::class, 'masquerEvenement'])->name('evenements.masquer');
        Route::delete('/evenements/{evenement}', [SuperAdminController::class, 'supprimerEvenement'])->name('evenements.supprimer');
        Route::post('/evenements/{evenement}/mettre-en-avant', [SuperAdminController::class, 'mettreEnAvant'])->name('evenements.mettre-en-avant');
        Route::post('/evenements/{evenement}/a-la-une', [SuperAdminController::class, 'toggleALaUne'])->name('evenements.a-la-une');
        Route::post('/evenements/{evenement}/a-la-une/ordre/{direction}', [SuperAdminController::class, 'ordreALaUne'])->name('evenements.a-la-une.ordre');
        Route::get('/evenements/{evenement}', [SuperAdminController::class, 'voirEvenement'])->name('evenements.voir');
        Route::post('/evenements/{evenement}/controles', [SuperAdminController::class, 'updateControlesEvenement'])->name('evenements.controles');
        Route::post('/organisateurs/{user}/controles', [SuperAdminController::class, 'updateControlesOrganisateur'])->name('organisateurs.controles');
        Route::post('/organisateurs/{user}/attribuer-agents', [SuperAdminController::class, 'attribuerAgents'])->name('organisateurs.attribuer-agents');
        Route::delete('/organisateurs/attributions/{attribution}', [SuperAdminController::class, 'supprimerAttribution'])->name('organisateurs.attribution-supprimer');
        Route::post('/organisateurs/{user}/vente-especes', [SuperAdminController::class, 'definirVenteEspeces'])->name('organisateurs.vente-especes');
        Route::post('/organisateurs/{user}/commission', [SuperAdminController::class, 'definirCommission'])->name('organisateurs.commission');
        Route::post('/organisateurs/{user}/controle-reset', [SuperAdminController::class, 'reinitialiserControle'])->name('organisateurs.controle-reset');
        Route::get('/transactions', [SuperAdminController::class, 'transactions'])->name('transactions');
        Route::get('/tickets', [SuperAdminController::class, 'tickets'])->name('tickets');
        Route::get('/scans', [SuperAdminController::class, 'scans'])->name('scans');
        Route::get('/tickets-physiques', [SuperAdminLotPhysiqueController::class, 'index'])->name('tickets-physiques');
        Route::get('/tickets-physiques/creer', [SuperAdminLotPhysiqueController::class, 'create'])->name('tickets-physiques.creer');
        Route::get('/tickets-physiques/planches', [SuperAdminLotPhysiqueController::class, 'telechargerPlanches'])->name('tickets-physiques.planches');
        Route::post('/tickets-physiques/evenements', [SuperAdminLotPhysiqueController::class, 'getEvenements'])->name('tickets-physiques.evenements');
        Route::post('/tickets-physiques/tarifs', [SuperAdminLotPhysiqueController::class, 'getTarifs'])->name('tickets-physiques.tarifs');
        Route::post('/tickets-physiques', [SuperAdminLotPhysiqueController::class, 'store'])->name('tickets-physiques.store');
        Route::get('/tickets-physiques/{lot}', [SuperAdminLotPhysiqueController::class, 'show'])->name('tickets-physiques.voir');
        Route::get('/tickets-physiques/{lot}/planche', [SuperAdminLotPhysiqueController::class, 'telechargerPlanche'])->name('tickets-physiques.planche');
        Route::get('/tickets-physiques/{lot}/template', [SuperAdminLotPhysiqueController::class, 'showTemplate'])->name('tickets-physiques.template');
        Route::post('/tickets-physiques/{lot}/template', [SuperAdminLotPhysiqueController::class, 'saveTemplate'])->name('tickets-physiques.template.save');
        Route::get('/tickets-physiques/{lot}/apercu', [SuperAdminLotPhysiqueController::class, 'previewTemplate'])->name('tickets-physiques.template.preview');
        Route::post('/tickets-physiques/{lot}/transmettre', [SuperAdminLotPhysiqueController::class, 'transmettre'])->name('tickets-physiques.transmettre');
        Route::post('/tickets-physiques/{lot}/tickets/{ticket}/annuler', [SuperAdminLotPhysiqueController::class, 'annulerTicket'])->name('tickets-physiques.annuler');
        Route::post('/tickets-physiques/{lot}/action-masse', [SuperAdminLotPhysiqueController::class, 'actionMasse'])->name('tickets-physiques.action-masse');
        Route::delete('/tickets-physiques/{lot}', [SuperAdminLotPhysiqueController::class, 'destroy'])->name('tickets-physiques.supprimer');
        Route::get('/statistiques', [SuperAdminController::class, 'statistiques'])->name('statistiques');
        Route::get('/securite', [SuperAdminController::class, 'securite'])->name('securite');
        Route::get('/notifications', [SuperAdminController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/{message}/lire', [SuperAdminController::class, 'lireNotification'])->name('notifications.lire');
        Route::post('/notifications/{message}/repondre', [SuperAdminController::class, 'repondreNotification'])->name('notifications.repondre');
        Route::delete('/notifications/{message}', [SuperAdminController::class, 'supprimerNotification'])->name('notifications.supprimer');
        Route::get('/parametres', [SuperAdminController::class, 'parametres'])->name('parametres');
        Route::put('/parametres/profil', [SuperAdminController::class, 'updateParametresProfil'])->name('parametres.profil.update');
        Route::put('/parametres/reseaux', [SuperAdminController::class, 'updateParametresReseaux'])->name('parametres.reseaux.update');
        Route::get('/retraits', [SuperAdminController::class, 'retraits'])->name('retraits');
        Route::post('/retraits/{withdrawal}/approuver', [SuperAdminController::class, 'approuverRetrait'])->name('retraits.approuver');
        Route::post('/retraits/{withdrawal}/confirmer', [SuperAdminController::class, 'confirmerRetrait'])->name('retraits.confirmer');
        Route::post('/retraits/{withdrawal}/rejeter', [SuperAdminController::class, 'rejeterRetrait'])->name('retraits.rejeter');
        Route::get('/logs', [SuperAdminController::class, 'logsSysteme'])->name('logs');
        Route::get('/moderation', [SuperAdminController::class, 'moderation'])->name('moderation');
        Route::get('/remboursements', [SuperAdminController::class, 'demandesRemboursement'])->name('remboursements.demandes');
        Route::get('/newsletter', [SuperAdminController::class, 'newsletter'])->name('newsletter');
        Route::post('/newsletter/envoyer', [SuperAdminController::class, 'envoyerNewsletter'])->name('newsletter.envoyer');
        Route::get('/remboursements/{demande}', [SuperAdminController::class, 'voirDemandeRemboursement'])->name('remboursements.voir');
        Route::post('/remboursements/{demande}/approuver', [SuperAdminController::class, 'approuverDemandeRemboursement'])->name('remboursements.approuver');
        Route::post('/remboursements/{demande}/confirmer', [SuperAdminController::class, 'confirmerRemboursement'])->name('remboursements.confirmer');
        Route::post('/remboursements/{demande}/refuser', [SuperAdminController::class, 'refuserDemandeRemboursement'])->name('remboursements.refuser');
        Route::get('/support', [SuperAdminController::class, 'support'])->name('support');
        Route::post('/support/verifier', [SuperAdminController::class, 'supportVerifier'])->name('support.verifier');
        Route::post('/support/confirmer', [SuperAdminController::class, 'supportConfirmer'])->name('support.confirmer');
        Route::post('/support/recreer', [SuperAdminController::class, 'supportRecreer'])->name('support.recreer');
        Route::post('/support/supprimer', [SuperAdminController::class, 'supportSupprimer'])->name('support.supprimer');
        Route::post('/support/renvoyer-email', [SuperAdminController::class, 'supportRenvoyerEmail'])->name('support.renvoyer-email');
        Route::post('/support/rembourser', [SuperAdminController::class, 'supportRembourser'])->name('support.rembourser');
        Route::post('/support/tarifs', [SuperAdminController::class, 'supportTarifs'])->name('support.tarifs');
        Route::post('/support/incident-message', [SuperAdminController::class, 'supportVoirIncident'])->name('support.incident-message');

            // ---- Gestion de l'equipe (super_admin uniquement, verifie dans le controleur) ----
            Route::get('/parametres/equipe/{membre}', [SuperAdminController::class, 'voirMembreEquipe'])->name('parametres.equipe.voir');
            Route::post('/parametres/equipe', [SuperAdminController::class, 'ajouterMembreEquipe'])->name('parametres.equipe.ajouter');
            Route::post('/parametres/equipe/{membre}/roles', [SuperAdminController::class, 'majRolesMembreEquipe'])->name('parametres.equipe.roles');
            Route::post('/parametres/equipe/{membre}/statut', [SuperAdminController::class, 'basculerStatutMembreEquipe'])->name('parametres.equipe.statut');
            Route::post('/parametres/equipe/{membre}/reinit-mdp', [SuperAdminController::class, 'reinitialiserMdpMembreEquipe'])->name('parametres.equipe.reinit');
            Route::post('/parametres/equipe/{membre}/renvoyer-email', [SuperAdminController::class, 'renvoyerEmailEquipe'])->name('parametres.equipe.renvoyer-email');
            Route::delete('/parametres/equipe/{membre}', [SuperAdminController::class, 'supprimerMembreEquipe'])->name('parametres.equipe.supprimer');
        });
    });
});

// ---- Portail dedie aux membres de l'equipe (URLs /equipe distinctes de l'admin et du superadmin) ----
Route::prefix('equipe')->name('equipe.')->middleware('no_cache')->group(function () {
    Route::get('/', [SuperAdminAuthController::class, 'portail'])->name('portail');
    Route::get('/login', [SuperAdminAuthController::class, 'showLoginFormEquipe'])->name('login');
    Route::post('/login', [SuperAdminAuthController::class, 'loginEquipe'])->name('login.post')->middleware('throttle:5,1');

    Route::get('/premiere-connexion', [SuperAdminController::class, 'premiereConnexion'])->name('premiere-connexion');
    Route::post('/premiere-connexion', [SuperAdminController::class, 'enregistrerPremiereConnexion'])->name('premiere-connexion.post');

    Route::middleware(['equipe_permission'])->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/organisateurs', [SuperAdminController::class, 'organisateurs'])->name('organisateurs');
        Route::get('/organisateurs/{user}', [SuperAdminController::class, 'voirOrganisateur'])->name('organisateurs.voir');
        Route::get('/retraits', [SuperAdminController::class, 'retraits'])->name('retraits');
        Route::get('/remboursements', [SuperAdminController::class, 'demandesRemboursement'])->name('remboursements.demandes');
        Route::get('/remboursements/{demande}', [SuperAdminController::class, 'voirDemandeRemboursement'])->name('remboursements.voir');
        Route::get('/notifications', [SuperAdminController::class, 'notifications'])->name('notifications');
        Route::get('/support', [SuperAdminController::class, 'support'])->name('support');
    });
});
// Routes globales Spatie Sitemap
Route::middleware('auth')->get('/generate-sitemap', function () {
    $sitemap = Sitemap::create()
        ->add(Url::create(url('/')))
        ->add(Url::create(url('/evenements')))
        ->add(Url::create(url('/inscription')))
        ->add(Url::create(url('/contact')))
        ->add(Url::create(url('/aide')))
        ->add(Url::create(url('/cgu')))
        ->add(Url::create(url('/confidentialite')))
        ->add(Url::create(url('/mentions-legales')))
        ->add(Url::create(url('/politique-remboursement')))
        ->add(Url::create(url('/conditions-generales-de-vente')))
        ->add(Url::create(url('/affiliation')))
        ->add(Url::create(url('/contrat-prestation')));

    Evenement::where('statut', '=', 'publié', 'and')->get()->each(function ($evenement) use ($sitemap) {
        $sitemap->add(Url::create(url('/evenements/'.$evenement->id)));
    });

    $sitemap->writeToFile(public_path('sitemap.xml'));

    return 'Sitemap généré avec succès';
});

// ============================================================
// Routes agent de scan
// ============================================================
Route::prefix('agent')->name('agent.')->group(function () {
    Route::get('/connexion', [AgentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [AgentAuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');

    Route::middleware(['agent', 'no_cache'])->group(function () {
        Route::post('/deconnexion', [AgentAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AgentAuthController::class, 'dashboard'])->name('dashboard');

        Route::get('/scan/pin', [AgentScanController::class, 'verifyPin'])->name('scan.pin');
        Route::post('/scan/pin', [AgentScanController::class, 'checkPin'])->name('scan.check-pin');
        Route::get('/scan', [AgentScanController::class, 'index'])->name('scan.index');
        Route::post('/scan/verifier', [AgentScanController::class, 'verifier'])->name('scan.verifier');
        Route::get('/scan/historique', [AgentScanController::class, 'historiqueJson'])->name('scan.historique');
        Route::get('/scan/quitter', [AgentScanController::class, 'exitScan'])->name('scan.exit');
    });
});

// ============================================================
// Routes agent de vente
// ============================================================
Route::prefix('vente')->name('agent-vente.')->group(function () {
    Route::get('/connexion', [AgentVenteAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [AgentVenteAuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');

    Route::middleware(['agent_vente', 'no_cache'])->group(function () {
        Route::post('/deconnexion', [AgentVenteAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AgentVenteAuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/vendre', [AgentVenteAuthController::class, 'vendre'])->name('vendre');
        Route::get('/paiement/{ticket}', [AgentVenteAuthController::class, 'payer'])->name('paiement');
        Route::get('/historique', [AgentVenteAuthController::class, 'historiqueJson'])->name('historique.json');
        Route::get('/historique/ventes', [AgentVenteAuthController::class, 'historique'])->name('historique');
        Route::get('/tickets/{ticket}/pdf', [AgentVenteAuthController::class, 'downloadPdf'])->name('ticket.pdf');
    });
});

// ============================================================
// Routes protégées (admin)
// ============================================================
Route::middleware(['auth', 'compte_actif', 'no_cache'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/type', [ProfilController::class, 'step2'])->name('step2');
        Route::post('/type', [ProfilController::class, 'postStep2'])->name('post-step2');
        Route::get('/recapitulatif', [ProfilController::class, 'recap'])->name('recap');
        Route::post('/soumettre', [ProfilController::class, 'submit'])->name('submit');
    });

    Route::prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametresController::class, 'index'])->name('index');
        Route::put('/profil', [ParametresController::class, 'profil'])->name('profil.update');
        Route::delete('/avatar', [ParametresController::class, 'supprimerAvatar'])->name('avatar.delete');
        Route::put('/securite', [ParametresController::class, 'securite'])->name('securite.update');
        Route::put('/notifications', [ParametresController::class, 'notifications'])->name('notifications.update');
        Route::put('/scan', [ParametresController::class, 'scan'])->name('scan.update');
        Route::post('/supprimer-compte', [ParametresController::class, 'supprimerCompte'])->name('compte.delete');
    });

    Route::get('/contrat-prestation', [EvenementController::class, 'contratPrestation'])->name('contrat-prestation');
});

Route::middleware(['auth', 'compte_actif', 'no_cache'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('evenements', EvenementController::class);

        Route::get('/evenements/{evenement}/tarifs', [TarifController::class, 'index'])->name('tarifs.index');
        Route::get('/evenements/{evenement}/tarifs/create', [TarifController::class, 'create'])->name('tarifs.create');
        Route::post('/evenements/{evenement}/tarifs', [TarifController::class, 'store'])->name('tarifs.store');
        Route::get('/evenements/{evenement}/tarifs/{tarif}/edit', [TarifController::class, 'edit'])->name('tarifs.edit');
        Route::put('/evenements/{evenement}/tarifs/{tarif}', [TarifController::class, 'update'])->name('tarifs.update');
        Route::delete('/evenements/{evenement}/tarifs/{tarif}', [TarifController::class, 'destroy'])->name('tarifs.destroy');

        Route::get('/scan-codes', [EvenementController::class, 'scanCodesIndex'])->name('scan-codes.index');
        Route::post('/evenements/{evenement}/scan-codes', [EvenementController::class, 'genererCodeAcces'])->name('evenements.scan-codes.generate');
        Route::delete('/evenements/{evenement}/scan-codes/{scanAccessCode}', [EvenementController::class, 'supprimerCodeAcces'])->name('evenements.scan-codes.destroy');
        Route::post('/evenements/{evenement}/fermer-vente', [EvenementController::class, 'fermerVente'])->name('evenements.fermer-vente');

        Route::prefix('agents')->name('agents.')->group(function () {
            Route::get('/', [AdminAgentController::class, 'index'])->name('index');
            Route::get('/creer', [AdminAgentController::class, 'create'])->name('create')->middleware('profil_verifie');
            Route::post('/', [AdminAgentController::class, 'store'])->name('store')->middleware('profil_verifie');
            Route::get('/{agent}', [AdminAgentController::class, 'show'])->name('show');
            Route::post('/{agent}/toggle-actif', [AdminAgentController::class, 'toggleActif'])->name('toggle-actif');
            Route::delete('/{agent}', [AdminAgentController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('agents-vente')->name('agents-vente.')->group(function () {
            Route::get('/', [AdminAgentVenteController::class, 'index'])->name('index');
            Route::get('/creer', [AdminAgentVenteController::class, 'create'])->name('create')->middleware('profil_verifie');
            Route::post('/', [AdminAgentVenteController::class, 'store'])->name('store')->middleware('profil_verifie');
            Route::get('/{agentVente}', [AdminAgentVenteController::class, 'show'])->name('show');
            Route::post('/{agentVente}/toggle-actif', [AdminAgentVenteController::class, 'toggleActif'])->name('toggle-actif');
            Route::delete('/{agentVente}', [AdminAgentVenteController::class, 'destroy'])->name('destroy');
            Route::get('/stats/{evenement}', [AdminAgentVenteController::class, 'statsEvenement'])->name('stats-evenement');
        });

        Route::prefix('lots-physiques')->name('lots-physiques.')->group(function () {
            Route::get('/', [AdminLotPhysiqueController::class, 'index'])->name('index');
            Route::post('/commander', [AdminLotPhysiqueController::class, 'commander'])->name('commander');
            Route::get('/paiement/{reference}', [AdminLotPhysiqueController::class, 'checkout'])->name('checkout');
            Route::get('/{lot}/telecharger', [AdminLotPhysiqueController::class, 'download'])->name('download');
            Route::get('/{lot}/template', [AdminLotPhysiqueController::class, 'showTemplate'])->name('template');
            Route::post('/{lot}/template', [AdminLotPhysiqueController::class, 'saveTemplate'])->name('template.save');
            Route::get('/{lot}/apercu', [AdminLotPhysiqueController::class, 'previewTemplate'])->name('template.preview');
            Route::delete('/{lot}', [AdminLotPhysiqueController::class, 'destroy'])->name('destroy');
        });
    });

    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/renvoyer', [TicketController::class, 'renvoyer'])->name('tickets.renvoyer');
    Route::post('/tickets/{ticket}/annuler', [TicketController::class, 'annuler'])->name('tickets.annuler');
    Route::get('/tickets/{ticket}/pdf', [TicketController::class, 'downloadPdf'])->name('tickets.pdf');
    Route::get('/tickets/export/csv', [TicketController::class, 'exportCsv'])->name('tickets.export-csv');

    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan/verifier', [ScanController::class, 'verifier'])->name('scan.verifier');
    Route::post('/scan/access-code', [ScanController::class, 'verifierAccessCode'])->name('scan.access-code');
    Route::get('/scan/clear', [ScanController::class, 'clearAccess'])->name('scan.clear');
    Route::get('/scan/historique', [ScanController::class, 'historiqueJson'])->name('scan.historique');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');
    Route::get('/ventes-manuelles', [VenteManuelleController::class, 'create'])->name('ventes-manuelles.create');
    Route::post('/ventes-manuelles', [VenteManuelleController::class, 'store'])->name('ventes-manuelles.store');
    Route::post('/ventes-manuelles/tarifs', [VenteManuelleController::class, 'getTarifs'])->name('ventes-manuelles.tarifs');
    Route::post('/ventes-manuelles/verifier-code-promo', [VenteManuelleController::class, 'verifierCodePromo'])->name('ventes-manuelles.verifier-code-promo');
    Route::get('/rappels', [RappelController::class, 'index'])->name('rappels.index');
    Route::post('/rappels/envoyer', [RappelController::class, 'envoyer'])->name('rappels.envoyer');

    Route::prefix('admin/messages')->name('admin.messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/repondre', [MessageController::class, 'repondre'])->name('repondre');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
    });

    Route::post('/admin/demande-superadmin', [DemandeSuperAdminController::class, 'store'])
        ->name('demande-superadmin.store')
        ->middleware('throttle:5,10');

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/admin/logs/{id}/detail', [LogController::class, 'detail'])->name('logs.detail');
    Route::post('/admin/logs/recuperer', [LogController::class, 'recuperer'])->name('logs.recuperer');

    Route::get('/admin/retraits', [RetraitController::class, 'index'])->name('admin.retraits.index');
    Route::post('/admin/retraits', [RetraitController::class, 'store'])->middleware('throttle:5,1')->name('admin.retraits.store');

    Route::prefix('admin/remboursements')->name('admin.remboursements.')->group(function () {
        Route::get('/', [RemboursementController::class, 'index'])->name('index');
        Route::post('/demander', [RemboursementController::class, 'demander'])->name('demander');
    });
    Route::post('/tickets/{ticket}/annuler-remboursement', [RemboursementController::class, 'annulerRemboursement'])->name('tickets.annuler-remboursement');

    Route::get('/admin/codes-promos', [CodePromoController::class, 'globalIndex'])->name('admin.codes-promos.index');
    Route::post('/admin/codes-promos', [CodePromoController::class, 'store'])->name('admin.codes-promos.store');
    Route::delete('/admin/codes-promos/{codePromo}', [CodePromoController::class, 'destroy'])->name('admin.codes-promos.destroy');
    Route::get('/admin/codes-promos/export', [CodePromoController::class, 'export'])->name('admin.codes-promos.export');
});
