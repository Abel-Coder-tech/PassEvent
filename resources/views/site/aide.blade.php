@extends('layouts.public')

@section('title', 'Comment ça marche — PaxEvent')
@section('description', 'Découvrez comment fonctionne PaxEvent : acheter un billet, créer un événement, et bien plus. Guide pas à pas.')
@section('og_title', 'Comment ça marche — PaxEvent')
@section('og_description', 'Apprenez à utiliser PaxEvent pour acheter et vendre vos billets d\'événements en ligne.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comment ça marche</li>
@endsection

@section('content')
<!-- Hero -->
<section class="aide-hero">
    <div class="aide-hero-bg">
        <div class="aide-circle c1"></div>
        <div class="aide-circle c2"></div>
        <div class="aide-circle c3"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="aide-hero-chip">Guide d'utilisation</span>
                <h1 class="aide-hero-title">Comment ça marche ?</h1>
                <span class="aide-faq-chip" style="margin-bottom:0.5rem;">POUR UTILISATEUR</span>
                <p class="aide-hero-sub" style="color:var(--accent);">4 étapes simples pour obtenir votre billet en un clin d'œil</p>
            </div>
        </div>
    </div>
</section>

<!-- Étapes -->
<section class="aide-steps">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(84,38,128,0.12), rgba(84,38,128,0.04));">
                        <span class="aide-card-num">1</span>
                        <i class="bi bi-calendar-check" style="color: var(--violet);"></i>
                    </div>
                    <h5 class="aide-card-title">Choisir l'événement</h5>
                    <p class="aide-card-text">Parcourez notre sélection d'événements, filtrez par date, catégorie ou lieu pour trouver celui qui vous correspond.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(254,213,20,0.12), rgba(254,213,20,0.04));">
                        <span class="aide-card-num" style="background: var(--violet);">2</span>
                        <i class="bi bi-pencil-square" style="color: var(--violet);"></i>
                    </div>
                    <h5 class="aide-card-title">Saisissez vos informations</h5>
                    <p class="aide-card-text">Sélectionnez le tarif (Gratuit, Standard ou VIP, Interne ou Externe, etc.), la quantité de tickets, renseignez vos coordonnées (nom, email, téléphone/WhatsApp) et appliquez un éventuel code promo.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(52,152,219,0.12), rgba(52,152,219,0.04));">
                        <span class="aide-card-num" style="background: #3498db;">3</span>
                        <i class="bi bi-shield-check" style="color: #3498db;"></i>
                    </div>
                    <h5 class="aide-card-title">Payer en sécurisé</h5>
                    <p class="aide-card-text">Réglez par Mobile Money (MTN, Moov, Celtiis Cash) ou par carte bancaire (Visa, Mastercard) via des passerelles sécurisées (FedaPay).</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(84,38,128,0.12), rgba(84,38,128,0.04));">
                        <span class="aide-card-num" style="background: #542680;">4</span>
                        <i class="bi bi-envelope-check" style="color: #542680;"></i>
                    </div>
                    <h5 class="aide-card-title">Recevez votre billet</h5>
                    <p class="aide-card-text">Recevez votre billet PDF par email et présentez son QR code à l'entrée (imprimé ou sur smartphone) pour authentification.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('evenements.public') }}" class="aide-btn-primary">
                <i class="bi bi-calendar-event me-2"></i>Découvrir les évènements
            </a>
        </div>
    </div>
</section>

<!-- FAQ Acheteur -->
<section class="aide-faq">
    <div class="container">
        <div class="text-center mb-4">
            <span class="aide-faq-chip">FAQ</span>
            <h2 class="aide-faq-title">Questions fréquentes</h2>
            <p class="aide-faq-sub">Tout ce que vous devez savoir avant d'acheter</p>
        </div>

        <div class="text-center mb-4">
            <span class="aide-faq-chip" style="background:linear-gradient(135deg,rgba(84,38,128,0.08),rgba(84,38,128,0.03));color:#9972B0;">POUR UTILISATEUR</span>
        </div>

        <div class="row g-5 justify-content-center">
            <div class="col-lg-7">
                <div class="accordion" id="faqAccordion">
                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-cart me-2" style="color: var(--violet);"></i>
                                Comment acheter un billet ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ol style="padding-left:1.2rem;margin:0;">
                                    <li>Choisissez votre évènement et voyez tous les détails.</li>
                                    <li>Sélectionnez le type de billet et indiquez le nombre souhaité.</li>
                                    <li>Cliquez sur "Participer" ou "Acheter".</li>
                                    <li>Payez votre ticket.</li>
                                    <li>Recevez directement votre billet électronique.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-wallet2 me-2" style="color: var(--violet);"></i>
                                Comment payer ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Vous pouvez payer en toute sécurité de deux façons :<br>
                                - Par <strong>Mobile Money</strong> (MTN MoMo, Moov Money, Celtiis Cash).<br>
                                - Par <strong>carte bancaire</strong> (Visa ou Mastercard).<br>
                                Toutes les transactions sont entièrement sécurisées et protégées.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-search me-2" style="color: #3498db;"></i>
                                Où trouver mes billets après achat ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Dès que votre paiement est validé, vous pouvez récupérer vos billets de trois façons :<br>
                                - Par <strong>e-mail</strong> (envoyé instantanément).<br>
                                - En <strong>téléchargement direct</strong> sur la page de confirmation d'achat.<br>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="bi bi-ticket-perforated me-2" style="color: #9972B0;"></i>
                                Comment récupérer votre ticket perdu ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ol style="padding-left:1.2rem;margin:0;">
                                    <li>Allez sur la page <strong>"Récupérer mon ticket"</strong>.</li>
                                    <li>Entrez les 3 informations données lors de l'achat : votre nom complet, votre email et votre numéro de téléphone.</li>
                                    <li>Téléchargez votre billet au format PDF.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="bi bi-arrow-counterclockwise me-2" style="color: #3498db;"></i>
                                Puis-je obtenir un remboursement ?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Non, les billets ne sont pas remboursables, sauf dans le cas exceptionnel où l'événement est annulé par l'organisateur. Pour toute réclamation ou problème rencontré, vous devez vous adresser directement à l'organisateur de l'événement.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                <i class="bi bi-mortarboard me-2" style="color: #9972B0;"></i>
                                Comment fonctionnent les codes promos ?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Saisissez le code fourni par l'organisateur lors de votre achat pour appliquer la réduction. Chaque code est <strong>unique</strong>, valable pour un seul tarif spécifique et utilisable une seule fois.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                <i class="bi bi-envelope me-2" style="color: var(--violet);"></i>
                                Je n'ai pas reçu mon billet par email
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Si vous n'avez pas reçu votre billet par email après confirmation du paiement, vérifiez d'abord vos <strong>spams</strong>. Si le billet n'y est pas, téléchargez-le directement au format PDF depuis la page <strong>"Récupérer mon ticket"</strong>.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                <i class="bi bi-shield-lock me-2" style="color: var(--violet);"></i>
                                Mes données sont-elles protégées ?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Vos données personnelles sont chiffrées et servent uniquement à générer vos tickets. Aucune information bancaire n'est conservée, et la sécurité des transactions est garantie par des partenaires certifiés PCI DSS.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="aide-organiser">
                    <i class="bi bi-cart-check aide-organiser-icon"></i>
                    <h3 class="aide-organiser-title">Achetez vos tickets en ligne</h3>
                    <p class="aide-organiser-text" style="color:var(--accent);">Achetez vos tickets en moins d'une minute</p>
                    <div class="aide-organiser-grid">
                        <div class="aide-org-item">
                            <i class="bi bi-ticket-perforated"></i>
                            <span>E-Ticket</span>
                        </div>
                        <div class="aide-org-item">
                            <i class="bi bi-phone"></i>
                            <span>Paiement mobile</span>
                        </div>
                        <div class="aide-org-item">
                            <i class="bi bi-lightning"></i>
                            <span>Rapidité</span>
                        </div>
                        <div class="aide-org-item">
                            <i class="bi bi-shield-check"></i>
                            <span>Sécurité</span>
                        </div>
                    </div>
                    <div class="aide-organiser-actions">
                        <a href="{{ route('evenements.public') }}" class="aide-btn-primary" style="font-size:0.85rem; padding:0.65rem 1.5rem;">
                            Acheter un ticket <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="{{ route('tickets.recuperer') }}" class="aide-btn-outline" style="font-size:0.82rem; padding:0.6rem 1.2rem;">
                            Récupérer un ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Organizer intro -->
<section class="aide-hero" style="padding:3rem 0;">
    <div class="aide-hero-bg">
        <div class="aide-circle c1"></div>
        <div class="aide-circle c2"></div>
        <div class="aide-circle c3"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="aide-faq-chip" style="background:rgba(178,224,214,0.12);border-color:rgba(178,224,214,0.2);color:#9972B0;">POUR ORGANISATEUR</span>
                <h1 class="aide-hero-title" style="font-size:2rem;">Comment ça marche ?</h1>
                <p class="aide-hero-sub" style="color:var(--accent);">03 étapes pour vendre vos tickets en toute simplicité</p>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="aide-card text-center h-100" style="padding:2rem 1.25rem;">
                    <div class="aide-card-icon" style="width:70px;height:70px;font-size:1.5rem;background:linear-gradient(135deg,rgba(84,38,128,0.12),rgba(84,38,128,0.04));">
                        <span class="aide-card-num" style="width:26px;height:26px;font-size:0.75rem;">1</span>
                        <i class="bi bi-person-plus" style="color:var(--violet);"></i>
                    </div>
                    <h5 class="aide-card-title" style="font-size:1rem;">Créer votre compte</h5>
                    <p class="aide-card-text">Inscrivez-vous à l'aide de votre adresse email ou de votre compte Google, puis complétez vos informations personnelles pour finaliser l'inscription.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="aide-card text-center h-100" style="padding:2rem 1.25rem;">
                    <div class="aide-card-icon" style="width:70px;height:70px;font-size:1.5rem;background:linear-gradient(135deg,rgba(254,213,20,0.12),rgba(254,213,20,0.04));">
                        <span class="aide-card-num" style="width:26px;height:26px;font-size:0.75rem;background:var(--violet);">2</span>
                        <i class="bi bi-file-earmark-text" style="color:var(--violet);"></i>
                    </div>
                    <h5 class="aide-card-title" style="font-size:1rem;">Soumettre votre profil</h5>
                    <p class="aide-card-text">Renseignez les détails liés à votre statut (particulier, université, entreprise), importez les justificatifs demandés (CIP, RCCM / IFU) et patientez un maximum de 24 heures pour l'approbation.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="aide-card text-center h-100" style="padding:2rem 1.25rem;">
                    <div class="aide-card-icon" style="width:70px;height:70px;font-size:1.5rem;background:linear-gradient(135deg,rgba(52,152,219,0.12),rgba(52,152,219,0.04));">
                        <span class="aide-card-num" style="width:26px;height:26px;font-size:0.75rem;background:#3498db;">3</span>
                        <i class="bi bi-megaphone" style="color:#3498db;"></i>
                    </div>
                    <h5 class="aide-card-title" style="font-size:1rem;">Publier vos événements</h5>
                    <p class="aide-card-text">Publiez librement vos différents événements ouverts au public (concerts, conférences, festivals, soirées, galas, etc.).</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Organisateur -->
<section class="aide-faq">
    <div class="container">
        <div class="text-center mb-4">
            <span class="aide-faq-chip">FAQ</span>
            <h2 class="aide-faq-title">Questions fréquentes</h2>
            <p class="aide-faq-sub">Tout ce que vous devez savoir pour devenir organisateur</p>
        </div>

        <div class="text-center mb-4">
            <span class="aide-faq-chip" style="background:linear-gradient(135deg,rgba(84,38,128,0.08),rgba(84,38,128,0.03));color:#9972B0;">POUR ORGANISATEUR</span>
        </div>

        <div class="row g-5 justify-content-center">
            <div class="col-lg-5">
                <div class="aide-organiser">
                    <i class="bi bi-megaphone aide-organiser-icon"></i>
                    <h3 class="aide-organiser-title">Devenir Organisateur</h3>
                    <p class="aide-organiser-text" style="color:var(--accent);">Créez, vendez et gérez vos événements en toute simplicité.</p>
                    <div class="aide-organiser-grid">
                        <div class="aide-org-item">
                            <i class="bi bi-percent"></i>
                            <span>Commission 10%</span>
                        </div>
                        <div class="aide-org-item">
                            <i class="bi bi-phone"></i>
                            <span>Paiement mobile</span>
                        </div>
                        <div class="aide-org-item">
                            <i class="bi bi-qr-code"></i>
                            <span>Scan QR</span>
                        </div>
                        <div class="aide-org-item">
                            <i class="bi bi-graph-up"></i>
                            <span>Statistiques</span>
                        </div>
                    </div>
                    <div class="aide-organiser-actions">
                        <a href="{{ route('inscriptions.organisateur') }}" class="aide-btn-primary" style="font-size:0.85rem; padding:0.65rem 1.5rem;">
                            Créer un compte <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="{{ route('login') }}" class="aide-btn-outline" style="font-size:0.82rem; padding:0.6rem 1.2rem;">
                            Se connecter
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="accordion" id="orgFaqAccordion">
                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orgFaq1">
                                <i class="bi bi-person-check me-2" style="color: var(--violet);"></i>
                                Comment devenir organisateur ?
                            </button>
                        </h2>
                        <div id="orgFaq1" class="accordion-collapse collapse" data-bs-parent="#orgFaqAccordion">
                            <div class="accordion-body">
                                <ol style="padding-left:1.2rem;margin:0;">
                                    <li>Cliquez sur le bouton <strong>"Devenir organisateur"</strong> ou sur le bouton <strong>"Se connecter"</strong> pour vous inscrire ou vous connecter.</li>
                                    <li>Remplissez et envoyez votre profil d'organisateur depuis votre tableau de bord.</li>
                                    <li>Un administrateur examinera votre demande sous 24h et vous recevrez un email de confirmation une fois votre demande approuvée.</li>
                                    <li>Connectez-vous à nouveau pour commencer à publier vos événements.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orgFaq2">
                                <i class="bi bi-pencil-square me-2" style="color: var(--violet);"></i>
                                Comment créer un événement ?
                            </button>
                        </h2>
                        <div id="orgFaq2" class="accordion-collapse collapse" data-bs-parent="#orgFaqAccordion">
                            <div class="accordion-body">
                                <ol style="padding-left:1.2rem;margin:0;">
                                    <li>Accédez à votre compte à l'aide de vos identifiants.</li>
                                    <li>Depuis votre tableau de bord organisateur, cliquez sur <strong>"Créer un événement"</strong> ou sur le bouton <strong>"+"</strong>.</li>
                                    <li>Renseignez le titre, la description, la date et le lieu, puis ajoutez un visuel de couverture attrayant.</li>
                                    <li>Définissez vos tarifs (gratuits/payants, standards/VIP, internes/externes).</li>
                                    <li>Publiez votre événement pour le rendre accessible.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orgFaq3">
                                <i class="bi bi-people me-2" style="color: var(--violet);"></i>
                                Puis-je attribuer des rôles à des membres de mon équipe ?
                            </button>
                        </h2>
                        <div id="orgFaq3" class="accordion-collapse collapse" data-bs-parent="#orgFaqAccordion">
                            <div class="accordion-body">
                                Oui, vous pouvez attribuer des rôles à votre équipe. Chaque organisateur dispose gratuitement de <strong>deux comptes agents</strong> pour l'assister le jour de l'événement, avec des fonctions distinctes :<br><br>
                                - <strong>Le validateur (Agent de scan)</strong> : Responsable du contrôle et de l'authentification des tickets à l'entrée.<br>
                                - <strong>Le vendeur (Agent de vente manuelle)</strong> : Chargé de la billetterie physique et de la vente des tickets sur place.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orgFaq4">
                                <i class="bi bi-person-plus me-2" style="color: #9972B0;"></i>
                                Comment créer un agent de scan ou de vente ?
                            </button>
                        </h2>
                        <div id="orgFaq4" class="accordion-collapse collapse" data-bs-parent="#orgFaqAccordion">
                            <div class="accordion-body">
                                <ol style="padding-left:1.2rem;margin:0 0 0.75rem;">
                                    <li>Rendez-vous dans la section <strong>"créer agent"</strong> de votre tableau de bord. Remplissez le formulaire (nom, email, mot de passe) puis assignez à l'agent un seul événement et un rôle unique (scan ou vente).</li>
                                    <li>L'agent reçoit aussitôt un e-mail contenant les informations de l'événement (date, heure, lieu), son mot de passe, son code d'accès et le lien vers son espace.</li>
                                    <li>Pour commencer sa mission, l'agent clique sur le lien reçu et s'authentifie avec son code d'accès.</li>
                                </ol>
                                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:0.6rem 0.75rem;font-size:0.82rem;color:#856404;">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Le cumul n'est pas autorisé. Chaque agent est strictement affecté à un seul événement pour un seul rôle.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orgFaq5">
                                <i class="bi bi-qr-code me-2" style="color: var(--violet);"></i>
                                Comment scanner les billets le jour de l'événement ?
                            </button>
                        </h2>
                        <div id="orgFaq5" class="accordion-collapse collapse" data-bs-parent="#orgFaqAccordion">
                            <div class="accordion-body">
                                <strong>En tant qu'organisateur :</strong><br>
                                <ol style="padding-left:1.2rem;margin:0 0 0.75rem;">
                                    <li>Connectez-vous sur votre téléphone et allez dans la section <strong>"Scan"</strong>.</li>
                                    <li>Méthodes : saisissez manuellement le code du billet ou scannez le QR code en autorisant l'accès à votre caméra.</li>
                                    <li>Le système vérifie et valide l'entrée automatiquement.</li>
                                </ol>
                                <div style="background:#e8f4f8;border:1px solid #b8d4e8;border-radius:10px;padding:0.6rem 0.75rem;font-size:0.82rem;color:#2c5282;margin-bottom:0.75rem;">
                                    <i class="bi bi-info-circle me-1"></i> Vous êtes uniquement autorisé à scanner les billets liés à vos propres événements.
                                </div>
                                <strong>En tant qu'agent de scan ou de vente :</strong><br>
                                <ol style="padding-left:1.2rem;margin:0;">
                                    <li>Cliquez sur le lien transmis par l'organisateur depuis votre téléphone et connectez-vous avec votre mot de passe.</li>
                                    <li>Saisissez ensuite votre <strong>code d'accès</strong> dédié pour débuter immédiatement le scan ou la vente des tickets.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item" style="border-left:3px solid var(--violet);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#orgFaq6">
                                <i class="bi bi-cash-coin me-2" style="color: #542680;"></i>
                                <strong>Paiements et commissions</strong>
                            </button>
                        </h2>
                        <div id="orgFaq6" class="accordion-collapse collapse" data-bs-parent="#orgFaqAccordion">
                            <div class="accordion-body">
                                <h6 style="color:var(--violet);font-weight:700;margin-bottom:0.5rem;">Quels sont les tarifs de PaxEvent ?</h6>
                                <ul style="padding-left:1.2rem;margin:0 0 1rem;">
                                    <li>Publier un événement sur PaxEvent ne coûte <strong>absolument rien</strong>.</li>
                                    <li>Une commission unique de <strong>10%</strong> est prélevée uniquement sur les billets vendus. Vous ne payez que si vous vendez (ex: sur un billet de 10 000 FCFA, PaxEvent retient 1 000 FCFA et vous touchez 9 000 FCFA net).</li>
                                    <li>Des frais spécifiques s'appliquent si vous choisissez d'utiliser des outils complémentaires : campagnes marketing (SMS et e-mails promotionnels), gestion d'équipe (plus de 2 collaborateurs), achat de terminaux pour la vente physique de tickets.</li>
                                </ul>
                                <p style="font-size:0.85rem;color:#6c757d;margin-bottom:0.5rem;">Les organisateurs peuvent voir leurs revenus et commissions en temps réel depuis le <strong>tableau de bord</strong>.</p>

                                <hr style="margin:0.75rem 0;">

                                <h6 style="color:var(--violet);font-weight:700;margin-bottom:0.5rem;">Quand et comment retirer mes revenus ?</h6>
                                <p style="font-size:0.85rem;color:#495057;margin:0 0 0.5rem;">Les revenus sont habituellement versés après la tenue de l'événement. Les organisateurs peuvent planifier leurs retraits directement depuis la section <strong>« Retrait »</strong> de leur tableau de bord :</p>
                                <ul style="padding-left:1.2rem;margin:0 0 1rem;">
                                    <li><strong>Par période</strong> : Des virements automatiques planifiés tous les 4, 8 ou 31 jours.</li>
                                    <li><strong>Par palier</strong> : Un déclenchement automatique dès que le solde disponible atteint un minimum de 50 000 FCFA.</li>
                                </ul>

                                <hr style="margin:0.75rem 0;">

                                <h6 style="color:var(--violet);font-weight:700;margin-bottom:0.5rem;">Les événements gratuits sont-ils vraiment gratuits ?</h6>
                                <p style="font-size:0.85rem;color:#495057;margin:0;">L'organisation d'événements gratuits est entièrement gratuite. Aucun frais ni aucune commission ne sont appliqués sur les billets à 0 FCFA.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <p style="color: #6c757d; margin-bottom: 0.75rem;">Vous n'avez pas trouvé la réponse à votre question ?</p>
            <a href="{{ route('contact') }}" class="aide-btn-outline">
                <i class="bi bi-envelope me-1"></i> Contactez-nous
            </a>
        </div>
    </div>
</section>

<style>
/* ===== HERO ===== */
.aide-hero {
    padding: 4rem 0 3rem;
    background: linear-gradient(160deg, #211C31 0%, #211C31 50%, #211C31 100%);
    position: relative;
    overflow: hidden;
}
.aide-hero-bg {
    position: absolute; inset: 0;
    pointer-events: none;
}
.aide-circle {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
}
.aide-circle.c1 { width: 400px; height: 400px; background: #542680; top: -180px; left: -80px; opacity: 0.18; }
.aide-circle.c2 { width: 300px; height: 300px; background: #FED514; bottom: -100px; right: -60px; opacity: 0.12; }
.aide-circle.c3 { width: 150px; height: 150px; background: #9972B0; top: 20%; right: 30%; opacity: 0.06; }

.aide-hero-chip {
    display: inline-flex;
    padding: 0.35rem 1rem;
    background: rgba(178,224,214,0.12);
    border: 1px solid rgba(178,224,214,0.2);
    border-radius: 20px;
    color: #9972B0;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.aide-hero-title {
    font-size: 2.6rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.75rem;
    line-height: 1.15;
}
.aide-hero-sub {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.6);
    margin: 0;
}

/* ===== ÉTAPES ===== */
.aide-steps {
    padding: 4rem 0;
    background: #fff;
}
.aide-card {
    background: #fff;
    border-radius: 16px;
    padding: 2.5rem 1.5rem;
    border: 1px solid #f0edf2;
    transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
    position: relative;
    overflow: hidden;
}
.aide-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #542680, #9972B0);
    opacity: 0;
    transition: opacity 0.3s;
}
.aide-card:hover::before { opacity: 1; }
.aide-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.06);
    border-color: transparent;
}
.aide-card-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
    font-size: 2rem;
    transition: transform 0.3s;
}
.aide-card:hover .aide-card-icon { transform: scale(1.05); }
.aide-card-num {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--violet);
    color: #fff;
    font-size: 0.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.aide-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #211C31;
    margin: 1rem 0 0.5rem;
}
.aide-card-text {
    font-size: 0.85rem;
    color: #6c757d;
    line-height: 1.6;
    margin: 0;
}
.aide-btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2.5rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(123,63,160,0.35);
    transition: all 0.25s;
}
.aide-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(123,63,160,0.45);
    color: #fff;
}

/* ===== FAQ ===== */
.aide-faq {
    padding: 4rem 0;
    background: #f7f5f3;
}
.aide-faq-chip {
    display: inline-block;
    padding: 0.25rem 0.9rem;
    background: linear-gradient(135deg, rgba(123,63,160,0.1), rgba(123,63,160,0.05));
    border: 1px solid rgba(123,63,160,0.15);
    border-radius: 20px;
    color: #542680;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}
.aide-faq-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: #211C31;
    margin: 0 0 0.3rem;
}
.aide-faq-sub {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 0;
}

.aide-accordion-item {
    border: none;
    margin-bottom: 0.75rem;
    border-radius: 12px !important;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s;
}
.aide-accordion-item:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}
.aide-accordion-item .accordion-button {
    padding: 1rem 1.25rem;
    font-weight: 600;
    font-size: 0.93rem;
    color: #211C31;
    background: #fff;
    box-shadow: none;
    border: none;
}
.aide-accordion-item .accordion-button:not(.collapsed) {
    color: var(--violet);
    background: rgba(135,66,139,0.04);
}
.aide-accordion-item .accordion-button:focus {
    box-shadow: none;
}
.aide-accordion-item .accordion-button::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2387428b'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}
.aide-accordion-item .accordion-body {
    padding: 0 1.25rem 1rem;
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.7;
}
.aide-btn-outline {
    display: inline-flex;
    align-items: center;
    padding: 0.65rem 1.8rem;
    border-radius: 10px;
    border: 2px solid rgba(109,53,112,0.3);
    color: #542680;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.2s;
}
.aide-btn-outline:hover {
    background: rgba(109,53,112,0.06);
    border-color: #542680;
}

/* ===== Devenir organisateur ===== */
.aide-organiser {
    background: linear-gradient(160deg, #211C31 0%, #211C31 100%);
    border-radius: 18px;
    padding: 2rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.aide-organiser::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #542680, #9972B0);
}
.aide-organiser-icon {
    font-size: 2.5rem;
    color: rgba(178,224,214,0.15);
    display: block;
    margin-bottom: 0.5rem;
}
.aide-organiser-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.3rem;
}
.aide-organiser-text {
    color: rgba(255,255,255,0.55);
    font-size: 0.82rem;
    margin: 0 0 1.25rem;
}
.aide-organiser-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.aide-org-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 0.75rem;
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.06);
    font-size: 0.78rem;
    color: rgba(255,255,255,0.8);
    font-weight: 500;
    transition: all 0.2s;
}
.aide-org-item:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(178,224,214,0.15);
}
.aide-org-item i {
    font-size: 1rem;
    color: #9972B0;
    flex-shrink: 0;
}
.aide-organiser-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

@media (max-width: 767px) {
    .aide-hero-title { font-size: 1.8rem; }
}
</style>
@endsection