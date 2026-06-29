<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\CodePromoController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\EvenementPublicController;
use App\Http\Controllers\SitePublicController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\VenteManuelleController;
use App\Http\Controllers\RappelController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\RemboursementController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminAuthController;

// ============================================================
// SEO
// ============================================================
Route::get('/robots.txt', function () {
    $content = "User-agent: *\n"
        . "Disallow: /admin/\n"
        . "Disallow: /superadmin/\n"
        . "Disallow: /dashboard\n"
        . "Disallow: /parametres/\n"
        . "Disallow: /scan/\n"
        . "Disallow: /login\n"
        . "Disallow: /mot-de-passe-oublie\n"
        . "Disallow: /reinitialiser/\n\n"
        . "Sitemap: " . url('/sitemap.xml') . "\n";
    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');
    if (!file_exists($path)) {
        Artisan::call('sitemap:generate');
    }
    return response()->file($path, ['Content-Type' => 'application/xml']);
})->name('sitemap');

// ============================================================
// Routes publiques (participant)
// ============================================================
Route::get('/', [SitePublicController::class, 'accueil'])->name('accueil');
Route::get('/evenements', [EvenementPublicController::class, 'index'])->name('evenements.public');
Route::get('/evenements/{evenement}', [EvenementPublicController::class, 'show'])->name('evenements.public.show');
Route::post('/evenements/{evenement}/achat', [EvenementPublicController::class, 'achat'])->name('evenements.achat');
Route::post('/evenements/{evenement}/contacter-organisateur', [EvenementPublicController::class, 'contacterOrganisateur'])->name('evenements.contacter-organisateur');

Route::get('/paiement/{ticket}', [PaiementController::class, 'show'])->name('paiement.show');
Route::get('/confirmation/{ticket}', [PaiementController::class, 'confirmation'])->name('confirmation.show');

Route::get('/recuperer', [TicketController::class, 'recuperer'])->name('tickets.recuperer');
Route::post('/recuperer', [TicketController::class, 'rechercher'])->name('tickets.rechercher');
Route::get('/ticket/{ticket}/telecharger', [TicketController::class, 'downloadTicket'])->name('tickets.telecharger');

Route::get('/aide', [SitePublicController::class, 'aide'])->name('aide');
Route::get('/contact', [SitePublicController::class, 'contact'])->name('contact');
Route::post('/contact', [SitePublicController::class, 'contactStore'])->name('contact.store')->middleware('throttle:3,10');
Route::get('/confidentialite', [SitePublicController::class, 'confidentialite'])->name('confidentialite');
Route::get('/cgu', [SitePublicController::class, 'cgu'])->name('cgu');
Route::get('/mentions-legales', [SitePublicController::class, 'mentionsLegales'])->name('mentions-legales');
Route::get('/politique-remboursement', [SitePublicController::class, 'politiqueRemboursement'])->name('politique-remboursement');


// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Callback KKiaPay
Route::get('/paiement/callback', [PaiementController::class, 'callback'])->name('paiement.callback');
Route::post('/paiement/webhook', [PaiementController::class, 'webhook'])->name('paiement.webhook');

// Google OAuth
Route::get('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('google.callback');

// ============================================================
// Routes de connexion & inscription
// ============================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::prefix('inscription')->name('inscriptions.')->group(function () {
    Route::get('/', [InscriptionController::class, 'step0'])->name('create');
    Route::post('/email', [InscriptionController::class, 'sendOtp'])->name('send-otp')->middleware('throttle:3,1');
    Route::get('/verifier', [InscriptionController::class, 'showVerify'])->name('verify');
    Route::post('/verifier', [InscriptionController::class, 'verifyOtp'])->name('verify-otp')->middleware('throttle:5,1');
    Route::post('/renvoyer', [InscriptionController::class, 'resendOtp'])->name('resend-otp')->middleware('throttle:2,30');
    Route::get('/identite', [InscriptionController::class, 'step1'])->name('identity');
    Route::post('/identite', [InscriptionController::class, 'postStep1'])->name('post-identity');
    Route::get('/organisation', [InscriptionController::class, 'step2'])->name('org');
    Route::post('/organisation', [InscriptionController::class, 'postStep2'])->name('post-org');
    Route::get('/recapitulatif', [InscriptionController::class, 'step3'])->name('recap');
    Route::post('/confirmer', [InscriptionController::class, 'confirm'])->name('confirm');
    Route::get('/confirmation', [InscriptionController::class, 'confirmation'])->name('confirmation');
    Route::get('/precedent/{step}', [InscriptionController::class, 'previous'])->name('previous');
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
    Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.post');

    Route::middleware('superadmin')->group(function () {
        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/utilisateurs', [SuperAdminController::class, 'utilisateurs'])->name('utilisateurs');
        Route::get('/organisateurs', [SuperAdminController::class, 'organisateurs'])->name('organisateurs');
        Route::post('/organisateurs/creer', [SuperAdminController::class, 'creerOrganisateur'])->name('organisateurs.creer');
        Route::post('/organisateurs/{user}/suspendre', [SuperAdminController::class, 'suspendreOrganisateur'])->name('organisateurs.suspendre');
        Route::post('/organisateurs/{user}/approuver', [SuperAdminController::class, 'approuverOrganisateur'])->name('organisateurs.approuver');
        Route::post('/organisateurs/{user}/rejeter', [SuperAdminController::class, 'rejeterOrganisateur'])->name('organisateurs.rejeter');
        Route::post('/organisateurs/{user}/corrections', [SuperAdminController::class, 'demanderCorrectionsOrganisateur'])->name('organisateurs.corrections');
        Route::post('/organisateurs/{user}/supprimer', [SuperAdminController::class, 'supprimerOrganisateur'])->name('organisateurs.supprimer');
        Route::post('/organisateurs/{user}/email', [SuperAdminController::class, 'envoyerEmailOrganisateur'])->name('organisateurs.email');
        Route::get('/evenements', [SuperAdminController::class, 'evenements'])->name('evenements');
        Route::post('/evenements/{evenement}/suspendre', [SuperAdminController::class, 'suspendreEvenement'])->name('evenements.suspendre');
        Route::post('/evenements/{evenement}/masquer', [SuperAdminController::class, 'masquerEvenement'])->name('evenements.masquer');
        Route::delete('/evenements/{evenement}', [SuperAdminController::class, 'supprimerEvenement'])->name('evenements.supprimer');
        Route::post('/evenements/{evenement}/mettre-en-avant', [SuperAdminController::class, 'mettreEnAvant'])->name('evenements.mettre-en-avant');
        Route::get('/transactions', [SuperAdminController::class, 'transactions'])->name('transactions');
        Route::get('/tickets', [SuperAdminController::class, 'tickets'])->name('tickets');
        Route::get('/scans', [SuperAdminController::class, 'scans'])->name('scans');
        Route::get('/statistiques', [SuperAdminController::class, 'statistiques'])->name('statistiques');
        Route::get('/securite', [SuperAdminController::class, 'securite'])->name('securite');
        Route::get('/notifications', [SuperAdminController::class, 'notifications'])->name('notifications');
        Route::get('/parametres', [SuperAdminController::class, 'parametres'])->name('parametres');
        Route::get('/retraits', [SuperAdminController::class, 'retraits'])->name('retraits');
        Route::post('/retraits/{withdrawal}/approuver', [SuperAdminController::class, 'approuverRetrait'])->name('retraits.approuver');
        Route::post('/retraits/{withdrawal}/rejeter', [SuperAdminController::class, 'rejeterRetrait'])->name('retraits.rejeter');
        Route::get('/logs', [SuperAdminController::class, 'logsSysteme'])->name('logs');
        Route::get('/moderation', [SuperAdminController::class, 'moderation'])->name('moderation');
    });
});

// ============================================================
// Routes protégées (admin)
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('evenements', EvenementController::class);

        Route::get('/evenements/{evenement}/tarifs', [TarifController::class, 'index'])->name('tarifs.index');
        Route::get('/evenements/{evenement}/tarifs/create', [TarifController::class, 'create'])->name('tarifs.create');
        Route::post('/evenements/{evenement}/tarifs', [TarifController::class, 'store'])->name('tarifs.store');
        Route::get('/evenements/{evenement}/tarifs/{tarif}/edit', [TarifController::class, 'edit'])->name('tarifs.edit');
        Route::put('/evenements/{evenement}/tarifs/{tarif}', [TarifController::class, 'update'])->name('tarifs.update');
        Route::delete('/evenements/{evenement}/tarifs/{tarif}', [TarifController::class, 'destroy'])->name('tarifs.destroy');

        Route::post('/evenements/{evenement}/scan-codes', [EvenementController::class, 'genererCodeAcces'])->name('evenements.scan-codes.generate');
        Route::delete('/evenements/{evenement}/scan-codes/{scanAccessCode}', [EvenementController::class, 'supprimerCodeAcces'])->name('evenements.scan-codes.destroy');
    });

    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/renvoyer', [TicketController::class, 'renvoyer'])->name('tickets.renvoyer');
    Route::post('/tickets/{ticket}/annuler', [TicketController::class, 'annuler'])->name('tickets.annuler');
    Route::get('/tickets/{ticket}/pdf', [TicketController::class, 'downloadPdf'])->name('tickets.pdf');

    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan/verifier', [ScanController::class, 'verifier'])->name('scan.verifier');
    Route::post('/scan/access-code', [ScanController::class, 'verifierAccessCode'])->name('scan.access-code');
    Route::get('/scan/clear', [ScanController::class, 'clearAccess'])->name('scan.clear');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');
    Route::get('/ventes-manuelles', [VenteManuelleController::class, 'create'])->name('ventes-manuelles.create');
    Route::post('/ventes-manuelles', [VenteManuelleController::class, 'store'])->name('ventes-manuelles.store');
    Route::post('/ventes-manuelles/tarifs', [VenteManuelleController::class, 'getTarifs'])->name('ventes-manuelles.tarifs');
    Route::get('/rappels', [RappelController::class, 'index'])->name('rappels.index');
    Route::post('/rappels/envoyer', [RappelController::class, 'envoyer'])->name('rappels.envoyer');

    Route::prefix('admin/messages')->name('admin.messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/repondre', [MessageController::class, 'repondre'])->name('repondre');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
    });

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/admin/logs/{id}/detail', [LogController::class, 'detail'])->name('logs.detail');
    Route::post('/admin/logs/recuperer', [LogController::class, 'recuperer'])->name('logs.recuperer');

    Route::prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametresController::class, 'index'])->name('index');
        Route::put('/profil', [ParametresController::class, 'profil'])->name('profil.update');
        Route::delete('/avatar', [ParametresController::class, 'supprimerAvatar'])->name('avatar.delete');
        Route::put('/securite', [ParametresController::class, 'securite'])->name('securite.update');
        Route::put('/notifications', [ParametresController::class, 'notifications'])->name('notifications.update');
        Route::put('/paiement', [ParametresController::class, 'paiement'])->name('paiement.update');
        Route::put('/scan', [ParametresController::class, 'scan'])->name('scan.update');
        Route::post('/supprimer-compte', [ParametresController::class, 'supprimerCompte'])->name('compte.delete');
    });

    Route::get('/admin/retraits', [\App\Http\Controllers\RetraitController::class, 'index'])->name('admin.retraits.index');
    Route::post('/admin/retraits', [\App\Http\Controllers\RetraitController::class, 'store'])->name('admin.retraits.store');

    Route::prefix('admin/remboursements')->name('admin.remboursements.')->group(function () {
        Route::get('/', [RemboursementController::class, 'index'])->name('index');
    });
    Route::post('/tickets/{ticket}/rembourser', [RemboursementController::class, 'rembourser'])->name('tickets.rembourser');
    Route::post('/tickets/{ticket}/annuler-remboursement', [RemboursementController::class, 'annulerRemboursement'])->name('tickets.annuler-remboursement');

    // Routes globales pour les codes promo
    Route::get('/admin/codes-promos', [CodePromoController::class, 'globalIndex'])->name('admin.codes-promos.index');
    Route::post('/admin/codes-promos', [CodePromoController::class, 'store'])->name('admin.codes-promos.store');
    Route::delete('/admin/codes-promos/{codePromo}', [CodePromoController::class, 'destroy'])->name('admin.codes-promos.destroy');
    Route::get('/admin/codes-promos/export', [CodePromoController::class, 'export'])->name('admin.codes-promos.export');
});
