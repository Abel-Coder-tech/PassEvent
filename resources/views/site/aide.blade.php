@extends('layouts.public')

@section('title', 'Comment ca marche - PaxEvent')

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
                    <p class="aide-card-text">Parcourez notre sélection d'événements. Filtrez par date, catégorie ou lieu pour trouver celui qui vous correspond.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(254,213,20,0.12), rgba(254,213,20,0.04));">
                        <span class="aide-card-num" style="background: var(--violet);">2</span>
                        <i class="bi bi-pencil-square" style="color: var(--violet);"></i>
                    </div>
                    <h5 class="aide-card-title">Saisissez vos informations</h5>
                    <p class="aide-card-text">Choisissez votre tarif (Étudiant ou Externe), entrez votre nom, email et téléphone. Ajoutez un code promo si vous en avez un.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(52,152,219,0.12), rgba(52,152,219,0.04));">
                        <span class="aide-card-num" style="background: #3498db;">3</span>
                        <i class="bi bi-shield-check" style="color: #3498db;"></i>
                    </div>
                    <h5 class="aide-card-title">Payer sécurisé</h5>
                    <p class="aide-card-text">Payez via MTN Mobile Money, Moov Money ou Celtiis Cash. Le paiement est 100% sécurisé par KKiaPay.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="aide-card text-center h-100">
                    <div class="aide-card-icon" style="background: linear-gradient(135deg, rgba(84,38,128,0.12), rgba(84,38,128,0.04));">
                        <span class="aide-card-num" style="background: #542680;">4</span>
                        <i class="bi bi-envelope-check" style="color: #542680;"></i>
                    </div>
                    <h5 class="aide-card-title">Recevez votre billet</h5>
                    <p class="aide-card-text">Billet PDF reçu par email. Imprimez-le ou montrez-le sur votre téléphone à l'entrée. Un QR code sera scanné.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('evenements.public') }}" class="aide-btn-primary">
                <i class="bi bi-calendar-event me-2"></i>Découvrir les événements
            </a>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="aide-faq">
    <div class="container">
        <div class="text-center mb-5">
            <span class="aide-faq-chip">FAQ</span>
            <h2 class="aide-faq-title">Questions fréquentes</h2>
            <p class="aide-faq-sub">Tout ce que vous devez savoir avant d'acheter</p>
        </div>

        <div class="row g-5 justify-content-center">
            <div class="col-lg-7">
                <div class="accordion" id="faqAccordion">
                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-ticket-perforated me-2" style="color: var(--violet);"></i>
                                Comment récupérer mon ticket ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Allez sur la page <strong>"Récupérer mon ticket"</strong> et entrez le <strong>nom complet</strong>, l'<strong>email</strong> et le <strong>numéro de téléphone</strong> utilisés lors de l'achat. Les trois informations sont nécessaires pour retrouver votre billet. Vous pourrez ensuite le télécharger en PDF.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-wallet2 me-2" style="color: var(--violet);"></i>
                                Quels moyens de paiement sont acceptés ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous acceptons <strong>MTN Mobile Money</strong>, <strong>Moov Money</strong> et <strong>Celtiis Cash</strong> via la plateforme sécurisée KKiaPay. Tous les paiements sont chiffrés et protégés.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-arrow-counterclockwise me-2" style="color: #3498db;"></i>
                                Puis-je obtenir un remboursement ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Les billets ne sont pas remboursables sauf en cas d'annulation de l'événement par l'organisateur. Si vous rencontrez un problème, contactez-nous via <a href="https://wa.me/22943704513" style="color: var(--violet); font-weight: 600;">WhatsApp</a> avec votre référence de transaction.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="bi bi-mortarboard me-2" style="color: #9972B0;"></i>
                                Comment fonctionne le code promo étudiant ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Si vous êtes étudiant, utilisez le <strong>code promo fourni par votre institution</strong> lors de l'achat pour bénéficier d'une réduction sur le tarif étudiant. Le code est unique et lié à un tarif spécifique. Il ne peut être utilisé qu'une seule fois.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="bi bi-envelope me-2" style="color: var(--violet);"></i>
                                Je n'ai pas reçu mon billet par email
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Vérifiez d'abord dans vos spams. Si votre paiement a été confirmé mais que vous n'avez pas reçu le ticket, rendez-vous sur la page <strong>"Récupérer mon ticket"</strong> pour le télécharger en PDF directement.
                            </div>
                        </div>
                    </div>

                    <div class="aide-accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                <i class="bi bi-shield-lock me-2" style="color: var(--violet);"></i>
                                Mes données sont-elles protégées ?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Oui. Vos informations personnelles sont chiffrées et ne sont utilisées que pour la délivrance de vos tickets. Nous ne stockons aucune information de paiement. Notre partenaire <strong>KKiaPay</strong> est certifié PCI DSS.
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-5">
                <div class="aide-organiser">
                    <i class="bi bi-megaphone aide-organiser-icon"></i>
                    <h3 class="aide-organiser-title">Devenir organisateur</h3>
                    <p class="aide-organiser-text" style="color:var(--accent);">Créez, vendez et gérez vos événements en toute simplicité.</p>
                    <div class="aide-organiser-grid">
                        <div class="aide-org-item">
                            <i class="bi bi-ticket-perforated"></i>
                            <span>Multi-tarifs</span>
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
        </div>

        <div class="text-center mt-5">
            <p style="color: #6c757d; margin-bottom: 0.75rem;">Vous n'avez pas trouvé la réponse à votre question ?</p>
            <a href="{{ route('contact') }}" class="aide-btn-outline">
                <i class="bi bi-envelope me-1"></i> Contactez-nous
            </a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="aide-cta">
    <div class="container">
        <div class="aide-cta-card">
            <div class="row align-items-center g-4">
                <div class="col-md-7">
                    <h3 class="aide-cta-title">Prêt à vivre une expérience unique ?</h3>
                    <p class="aide-cta-text">Rejoignez des milliers de participants et ne manquez plus aucun événement.</p>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="{{ route('evenements.public') }}" class="aide-cta-btn">
                        Voir les événements <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
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

/* ===== CTA ===== */
.aide-cta {
    padding: 4rem 0;
    background: linear-gradient(160deg, #211C31 0%, #211C31 50%, #211C31 100%);
}
.aide-cta-card {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 3rem;
}
.aide-cta-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.3rem;
}
.aide-cta-text {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.6);
    margin: 0;
}
.aide-cta-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2.2rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(123,63,160,0.35);
    transition: all 0.25s;
}
.aide-cta-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(123,63,160,0.45);
    color: #fff;
}

@media (max-width: 767px) {
    .aide-hero-title { font-size: 1.8rem; }
    .aide-cta-card { padding: 2rem 1.5rem; }
    .aide-cta-title { font-size: 1.3rem; }
    .aide-cta .text-md-end { text-align: center !important; }
}
</style>
@endsection
