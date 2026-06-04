<?php $__env->startSection('title', 'Comment ca marche - PassEvent'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Comment ça marche</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- ===== En-tête de la page ===== -->
<section class="page-header">
    <div class="container text-center">
        <span class="page-header-badge">Guide d'utilisation</span>
        <h1 class="page-header-title">Comment ça marche ?</h1>
        <p class="page-header-sub">4 étapes simples pour obtenir votre billet en un clin d'œil</p>
    </div>
</section>

<!-- Etapes : 4 cartes modernes -->
<section class="py-5" style="background: #f8f9fc;">
    <div class="container">
        <div class="row g-4">
            <!-- Carte 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="step-card-modern text-center h-100">
                    <div class="step-icon-wrap" style="background: linear-gradient(135deg, rgba(135,66,139,0.1), rgba(109,53,112,0.06));">
                        <span class="step-badge">1</span>
                        <i class="bi bi-calendar-check" style="font-size: 2.2rem; color: var(--violet);"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-2">Choisir l'evenement</h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem; line-height: 1.6;">
                        Parcourez notre selection d'evenements. Filtrez par date, categorie ou lieu pour trouver celui qui vous correspond.
                    </p>
                </div>
            </div>
            <!-- Carte 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="step-card-modern text-center h-100">
                    <div class="step-icon-wrap" style="background: linear-gradient(135deg, rgba(18,151,110,0.1), rgba(18,151,110,0.06));">
                        <span class="step-badge" style="background: var(--vert);">2</span>
                        <i class="bi bi-pencil-square" style="font-size: 2.2rem; color: var(--vert);"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-2">Saisissez vos informations</h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem; line-height: 1.6;">
                        Choisissez votre tarif (Etudiant ou Externe), entrez votre nom, email et télephone. Un code promo etudiant ? Ajoutez-le.
                    </p>
                </div>
            </div>
            <!-- Carte 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="step-card-modern text-center h-100">
                    <div class="step-icon-wrap" style="background: linear-gradient(135deg, rgba(52,152,219,0.1), rgba(52,152,219,0.06));">
                        <span class="step-badge" style="background: #3498db;">3</span>
                        <i class="bi bi-shield-check" style="font-size: 2.2rem; color: #3498db;"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-2">Payer securise</h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem; line-height: 1.6;">
                        Payez via MTN Mobile Money, Moov Money ou Celtiis Cash. Le paiement est 100% securisé par KKiaPay.
                    </p>
                </div>
            </div>
            <!-- Carte 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="step-card-modern text-center h-100">
                    <div class="step-icon-wrap" style="background: linear-gradient(135deg, rgba(109,53,112,0.1), rgba(109,53,112,0.06));">
                        <span class="step-badge" style="background: #6d3570;">4</span>
                        <i class="bi bi-envelope-check" style="font-size: 2.2rem; color: #6d3570;"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-2">Recevez votre billet</h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem; line-height: 1.6;">
                        Billet PDF recu par email. Imprimez-le ou montrez-le sur votre telephone a l'entree. Un QR code sera scanné.
                    </p>
                </div>
            </div>
        </div>

        <!-- Lien vers les evenements -->
        <div class="text-center mt-5">
            <a href="<?php echo e(route('evenements.public')); ?>" class="btn btn-violet btn-lg px-5" style="border-radius: 10px; font-weight: 600;">
                <i class="bi bi-calendar-event me-2"></i>Découvrir les evenements
            </a>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-2">Questions frequentes</h3>
            <p class="text-muted">Tout ce que vous devez savoir avant d'acheter</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="font-weight: 600; font-size: 0.93rem;">
                                <i class="bi bi-ticket-perforated me-2" style="color: var(--violet);"></i>
                                Comment récuperer mon ticket ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0" style="font-size: 0.9rem; line-height: 1.7;">
                                Allez sur la page <strong>"Récuperer mon ticket"</strong> et entrez le <strong>nom complet</strong>, l'<strong>email</strong> et le <strong>numero de telephone</strong> utilises lors de l'achat. Les trois informations sont necessaires pour retrouver votre billet. Vous pourrez ensuite le telecharger en PDF.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="font-weight: 600; font-size: 0.93rem;">
                                <i class="bi bi-wallet2 me-2" style="color: var(--vert);"></i>
                                Quels moyens de paiement sont acceptes ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0" style="font-size: 0.9rem; line-height: 1.7;">
                                Nous acceptons <strong>MTN Mobile Money</strong>, <strong>Moov Money</strong> et <strong>Celtiis Cash</strong> via la plateforme securisee KKiaPay. Tous les paiements sont chiffres et proteges.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="font-weight: 600; font-size: 0.93rem;">
                                <i class="bi bi-arrow-counterclockwise me-2" style="color: #3498db;"></i>
                                Puis-je obtenir un remboursement ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0" style="font-size: 0.9rem; line-height: 1.7;">
                                Les billets ne sont pas remboursables sauf en cas d'annulation de l'événement par l'organisateur. Si vous rencontrez un probleme, contactez-nous via <a href="https://wa.me/22943704513" class="text-decoration-none" style="color: var(--violet); font-weight: 600;">WhatsApp</a> avec votre reference de transaction.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="font-weight: 600; font-size: 0.93rem;">
                                <i class="bi bi-mortarboard me-2" style="color: var(--aubergine);"></i>
                                Comment fonctionne le code promo etudiant ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0" style="font-size: 0.9rem; line-height: 1.7;">
                                Si vous êtes étudiant, utilisez le <strong>code promo fourni par votre institution</strong> lors de l'achat pour beneficier d'une reduction sur le tarif étudiant. Le code est unique et lié a un tarif specifique. Il ne peut être utiliser qu'une seule fois.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" style="font-weight: 600; font-size: 0.93rem;">
                                <i class="bi bi-envelope me-2" style="color: var(--violet);"></i>
                                Je n'ai pas recu mon billet par email
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0" style="font-size: 0.9rem; line-height: 1.7;">
                                Verifiez d'abord dans vos spams. Si votre paiement a été confirmer mais que vous n'avez pas reçu le ticket, rendez-vous sur la page <strong>"Récuperer mon ticket"</strong> pour le télécharger en PDF directement.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq6" style="font-weight: 600; font-size: 0.93rem;">
                                <i class="bi bi-shield-lock me-2" style="color: var(--violet);"></i>
                                Mes donnees sont-elles protegées ?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0" style="font-size: 0.9rem; line-height: 1.7;">
                                Oui. Vos informations personnelles sont chiffrées et ne sont utilisées que pour la delivrance de vos tickets. Nous ne stockons aucune information de paiement. Notre partenaire <strong>KKiaPay</strong> est certifié PCI DSS.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">Vous n'avez pas trouve la reponse a votre question ?</p>
                    <a href="<?php echo e(route('contact')); ?>" class="btn btn-outline-violet px-4" style="border-radius: 8px;">
                        <i class="bi bi-envelope me-1"></i> Contactez-nous
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA avant footer -->
<section style="background: linear-gradient(135deg, var(--vert) 0%, #0d7a54 100%); color: #fff; padding: 3.5rem 0;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <h3 class="fw-bold mb-2" style="font-size: 1.6rem;">Prêt à vivre une expérience unique ?</h3>
                <p class="mb-0" style="opacity: 0.9; font-size: 1rem;">Rejoignez des milliers de participants et ne manquez plus aucun evenement.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="<?php echo e(route('evenements.public')); ?>" class="btn btn-light btn-lg px-5" style="border-radius: 10px; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                    <i class="bi bi-arrow-right me-1"></i> Voir les evenements
                </a>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* Cartes etapes modernes */
    .step-card-modern {
        background: #fff;
        border-radius: 16px;
        padding: 2rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        cursor: default;
    }
    .step-card-modern:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.08);
    }

    .step-icon-wrap {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }

    .step-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--violet);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

/* ===== En-tête de page partagé ===== */
.page-header {
    padding: 2rem 0 1rem;
    text-align: center;
}
.page-header-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #333;
    margin: 0 0 0.3rem;
}
.page-header-sub {
    font-size: 1rem;
    color: #666;
    margin: 0;
}
.page-header-badge {
    display: inline-block;
    padding: 0.25rem 0.9rem;
    border-radius: 20px;
    background: #f0f0f0;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
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
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\site\aide.blade.php ENDPATH**/ ?>