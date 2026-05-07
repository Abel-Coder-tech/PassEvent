@extends('layouts.public')

@section('title', 'Comment ca marche - PassEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comment ca marche</li>
@endsection

@section('content')
<!-- Hero -->
<section class="py-5" style="background: linear-gradient(135deg, var(--violet) 0%, var(--violet-dark) 100%); color: #fff;">
    <div class="container text-center">
        <h1 class="fw-bold mb-2">Comment ca marche ?</h1>
        <p class="lead mb-0" style="opacity: 0.9;">4 etapes simples pour obtenir votre billet</p>
    </div>
</section>

<!-- Steps timeline -->
<section class="py-5">
    <div class="container">
        <div class="position-relative">
            <!-- Timeline line -->
            <div class="position-absolute start-50 top-0 bottom-0 d-none d-md-block" style="width: 3px; background: linear-gradient(to bottom, var(--violet), var(--vert), var(--teal), var(--aubergine)); transform: translateX(-50%); border-radius: 2px; z-index: 0;"></div>

            <!-- Step 1 -->
            <div class="row g-4 align-items-center mb-5 position-relative">
                <div class="col-md-5">
                    <div class="step-card text-end">
                        <span class="step-number">1</span>
                        <h4 class="fw-bold mt-2 mb-2">Choisir l'evenement</h4>
                        <p class="text-muted mb-0">
                            Parcourez notre liste d'evenements et selectionnez celui qui vous interesse.
                            Utilisez les filtres pour trouver par date, lieu ou prix.
                        </p>
                    </div>
                </div>
                <div class="col-md-2 text-center d-none d-md-block">
                    <div class="step-icon-lg" style="background: rgba(135,66,139,0.08); color: var(--violet);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="step-preview">
                        <div class="p-3 rounded-3 bg-white shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 50px; height: 50px; border-radius: 8px; background: linear-gradient(135deg, var(--violet), var(--violet-dark)); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-calendar-event text-white"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold" style="font-size: 0.9rem;">Conference IA 2026</div>
                                    <small class="text-muted">28 Avril 2026 - Cotonou</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="row g-4 align-items-center mb-5 position-relative">
                <div class="col-md-5 order-md-2">
                    <div class="step-card text-start">
                        <span class="step-number" style="background: rgba(18,151,110,0.08); color: var(--vert);">2</span>
                        <h4 class="fw-bold mt-2 mb-2">Remplir vos informations</h4>
                        <p class="text-muted mb-0">
                            Choisissez votre tarif (Etudiant ou Externe),
                            entrez votre nom, email et numero de telephone.
                            Les etudiants peuvent utiliser un code promo.
                        </p>
                    </div>
                </div>
                <div class="col-md-2 text-center d-none d-md-block order-md-1">
                    <div class="step-icon-lg" style="background: rgba(18,151,110,0.08); color: var(--vert);">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                </div>
                <div class="col-md-5 order-md-2">
                    <div class="step-preview">
                        <div class="p-3 rounded-3 bg-white shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet); font-size: 0.75rem;">Etudiant</span>
                                <span class="badge" style="background: rgba(66,140,121,0.1); color: var(--teal); font-size: 0.75rem;">Normal</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Tarif</small>
                                <strong style="color: var(--vert);">3 000 FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="row g-4 align-items-center mb-5 position-relative">
                <div class="col-md-5">
                    <div class="step-card text-end">
                        <span class="step-number" style="background: rgba(66,140,121,0.08); color: var(--teal);">3</span>
                        <h4 class="fw-bold mt-2 mb-2">Payer securise</h4>
                        <p class="text-muted mb-0">
                            Choisissez votre moyen de paiement : MTN Mobile Money, Moov Money ou Celtiis Cash.
                            Le paiement est securise par KKiaPay.
                        </p>
                    </div>
                </div>
                <div class="col-md-2 text-center d-none d-md-block">
                    <div class="step-icon-lg" style="background: rgba(66,140,121,0.08); color: var(--teal);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="step-preview">
                        <div class="p-3 rounded-3 bg-white shadow-sm">
                            <div class="d-flex justify-content-center gap-3">
                                <div style="width: 50px; height: 35px; border-radius: 6px; background: #ffcc00; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.6rem;">MTN</div>
                                <div style="width: 50px; height: 35px; border-radius: 6px; background: #0066cc; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.6rem; color: #fff;">Moov</div>
                                <div style="width: 50px; height: 35px; border-radius: 6px; background: #cc0000; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.6rem; color: #fff;">Celtiis</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="row g-4 align-items-center mb-5 position-relative">
                <div class="col-md-5 order-md-2">
                    <div class="step-card text-start">
                        <span class="step-number" style="background: rgba(109,78,114,0.08); color: var(--aubergine);">4</span>
                        <h4 class="fw-bold mt-2 mb-2">Recevoir votre billet PDF</h4>
                        <p class="text-muted mb-0">
                            Une fois le paiement confirme, recevez votre billet PDF par email.
                            Imprimez-le ou presentez-le sur votre telephone a l'entree.
                        </p>
                    </div>
                </div>
                <div class="col-md-2 text-center d-none d-md-block order-md-1">
                    <div class="step-icon-lg" style="background: rgba(109,78,114,0.08); color: var(--aubergine);">
                        <i class="bi bi-qr-code"></i>
                    </div>
                </div>
                <div class="col-md-5 order-md-2">
                    <div class="step-preview">
                        <div class="p-3 rounded-3 bg-white shadow-sm text-center">
                            <div class="d-inline-block p-2 rounded" style="background: #f5f5f5;">
                                <i class="bi bi-qr-code" style="font-size: 2.5rem; color: var(--sombre);"></i>
                            </div>
                            <small class="text-muted mt-1 d-block">Envoye par email en PDF</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5" style="background: linear-gradient(135deg, var(--vert) 0%, var(--teal) 100%); color: #fff;">
    <div class="container text-center">
        <h3 class="fw-bold mb-3">Pret a vivre une experience unique ?</h3>
        <a href="{{ route('evenements.public') }}" class="btn btn-light btn-lg px-5" style="border-radius: 8px; font-weight: 600;">
            <i class="bi bi-calendar-event me-1"></i> Voir les evenements
        </a>
    </div>
</section>

<!-- Questions frequentes -->
<section class="py-5" style="background: var(--blanc-casse);">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-2">Questions frequentes</h3>
            <p class="text-muted">Tout ce que vous devez savoir avant d'acheter</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ 1 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="font-weight: 600; font-size: 0.95rem; border-radius: 12px;">
                                <i class="bi bi-ticket-perforated me-2" style="color: var(--violet);"></i>
                                Comment recuperer mon ticket ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted" style="font-size: 0.9rem; line-height: 1.7;">
                                Allez sur la page <strong>"Recuperer mon ticket"</strong> et entrez le <strong>nom complet</strong>, l'<strong>email</strong> et le <strong>numero de telephone</strong> utilises lors de l'achat. Les trois informations sont necessaires pour retrouver votre billet. Vous pourrez ensuite le telecharger en PDF.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="font-weight: 600; font-size: 0.95rem; border-radius: 12px;">
                                <i class="bi bi-wallet2 me-2" style="color: var(--vert);"></i>
                                Quels moyens de paiement sont acceptes ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted" style="font-size: 0.9rem; line-height: 1.7;">
                                Nous acceptons <strong>MTN Mobile Money</strong>, <strong>Moov Money</strong> et <strong>Celtiis Cash</strong> via la plateforme securisee KKiaPay. Tous les paiements sont chiffres et proteges.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="font-weight: 600; font-size: 0.95rem; border-radius: 12px;">
                                <i class="bi bi-arrow-counterclockwise me-2" style="color: var(--teal);"></i>
                                Puis-je obtenir un remboursement ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted" style="font-size: 0.9rem; line-height: 1.7;">
                                Les billets ne sont pas remboursables sauf en cas d'annulation de l'evenement par l'organisateur. Si vous rencontrez un probleme, contactez-nous via <a href="https://wa.me/22943704513" class="text-decoration-none" style="color: var(--violet); font-weight: 600;">WhatsApp</a> avec votre reference de transaction.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="font-weight: 600; font-size: 0.95rem; border-radius: 12px;">
                                <i class="bi bi-mortarboard me-2" style="color: var(--aubergine);"></i>
                                Comment fonctionne le code promo etudiant ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted" style="font-size: 0.9rem; line-height: 1.7;">
                                Si vous etes etudiant, utilisez le <strong>code promo fourni par votre institution</strong> lors de l'achat pour beneficier d'une reduction sur le tarif etudiant. Le code est unique et lie a un tarif specifique. Il ne peut etre utilise qu'une seule fois.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" style="font-weight: 600; font-size: 0.95rem; border-radius: 12px;">
                                <i class="bi bi-envelope me-2" style="color: var(--violet);"></i>
                                Je n'ai pas recu mon billet par email
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted" style="font-size: 0.9rem; line-height: 1.7;">
                                Verifiez d'abord dans vos spams. Si votre paiement a ete confirme mais que vous n'avez pas recu le billet, rendez-vous sur la page <strong>"Recuperer mon ticket"</strong> pour le telecharger en PDF directement.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6" style="font-weight: 600; font-size: 0.95rem; border-radius: 12px;">
                                <i class="bi bi-shield-lock me-2" style="color: var(--violet);"></i>
                                Mes donnees sont-elles protegees ?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted" style="font-size: 0.9rem; line-height: 1.7;">
                                Oui. Vos informations personnelles sont chiffrees et ne sont utilisees que pour la delivrance de vos billets. Nous ne stockons aucune information de paiement. Notre partenaire de paiement <strong>KKiaPay</strong> est certifie PCI DSS.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact CTA -->
        <div class="text-center mt-5">
            <p class="text-muted mb-3">Vous n'avez pas trouve la reponse a votre question ?</p>
            <a href="{{ route('contact') }}" class="btn btn-violet px-4" style="border-radius: 8px;">
                <i class="bi bi-envelope me-1"></i> Contactez-nous
            </a>
        </div>
    </div>
</section>
@endsection

@section('styles')
<style>
    .step-card {
        padding: 1.5rem;
        background: var(--blanc);
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .step-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(135,66,139,0.08);
        color: var(--violet);
        font-weight: 800;
        font-size: 1.1rem;
    }

    .step-icon-lg {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border: 3px solid var(--blanc);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        z-index: 1;
        position: relative;
    }

    .step-preview {
        transition: transform 0.2s;
    }

    .step-preview:hover {
        transform: translateY(-2px);
    }

    /* Accordion custom */
    .accordion-button:not(.collapsed) {
        color: var(--violet);
        background: rgba(135,66,139,0.04);
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(135,66,139,0.15);
    }

    .accordion-button::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2387428b'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }

    @media (max-width: 767.98px) {
        .step-card {
            text-align: center !important;
            padding: 1.25rem;
        }

        .step-preview {
            margin-top: 1rem;
        }
    }
</style>
@endsection
