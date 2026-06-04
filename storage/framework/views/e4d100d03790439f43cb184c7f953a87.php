<?php $__env->startSection('title', 'Politique de confidentialite - PassEvent'); ?>

<?php $__env->startSection('content'); ?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4" style="color: var(--violet);">
                            <i class="bi bi-shield-lock me-2"></i>Politique de confidentialite
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Derniere mise a jour : Mai 2026</p>

                        <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                            <h6 class="fw-bold mt-4 mb-2">1. Collecte des donnees</h6>
                            <p>PassEvent collecte les informations suivantes lors de l'achat d'un billet :</p>
                            <ul>
                                <li>Nom et prenom de l'acheteur</li>
                                <li>Adresse email</li>
                                <li>Numero de telephone</li>
                                <li>Informations de paiement (traitees par KKiaPay)</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">2. Utilisation des donnees</h6>
                            <p>Vos donnees sont utilisees pour :</p>
                            <ul>
                                <li>Generer et envoyer vos billets par email</li>
                                <li>Traiter les paiements via KKiaPay</li>
                                <li>Gerer les acces aux evenements (scan QR code)</li>
                                <li>Vous contacter en cas d'annulation ou modification d'evenement</li>
                                <li>Ameliorer nos services</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">3. Partage des donnees</h6>
                            <p>Nous ne vendons jamais vos donnees personnelles. Elles peuvent etre partagees avec :</p>
                            <ul>
                                <li><strong>KKiaPay</strong> : pour le traitement des paiements</li>
                                <li><strong>Les organisateurs d'evenements</strong> : nom, email et telephone des participants a leurs evenements</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">4. Securite</h6>
                            <p>Nous mettons en place des mesures de securite pour proteger vos donnees :</p>
                            <ul>
                                <li>Chiffrement des communications (HTTPS)</li>
                                <li>Signature QR code anti-falsification</li>
                                <li>Acces restreint aux donnees personnelles</li>
                            </ul>

                            <h6 class="fw-bold mt-4 mb-2">5. Duree de conservation</h6>
                            <p>Vos donnees sont conservees pendant 2 ans apres le dernier evenement ou la derniere interaction avec notre plateforme.</p>

                            <h6 class="fw-bold mt-4 mb-2">6. Vos droits</h6>
                            <p>Conformement a la legislation en vigueur, vous disposez des droits suivants :</p>
                            <ul>
                                <li>Droit d'acces a vos donnees</li>
                                <li>Droit de rectification</li>
                                <li>Droit a l'effacement</li>
                                <li>Droit a la portabilite</li>
                            </ul>
                            <p>Pour exercer ces droits, contactez-nous a <a href="mailto:passevent2026@gmail.com">passevent2026@gmail.com</a>.</p>

                            <h6 class="fw-bold mt-4 mb-2">7. Cookies</h6>
                            <p>PassEvent utilise des cookies essentiels pour le fonctionnement du site (session, securite). Aucun cookie de traçage publicitaire n'est utilise.</p>

                            <h6 class="fw-bold mt-4 mb-2">8. Contact</h6>
                            <p>Pour toute question relative a cette politique de confidentialite :</p>
                            <ul>
                                <li>Email : <a href="mailto:passevent2026@gmail.com">passevent2026@gmail.com</a></li>
                                <li>WhatsApp : +229 43 70 45 13</li>
                                <li>Adresse : Benin</li>
                            </ul>
                        </div>

                        <a href="<?php echo e(route('accueil')); ?>" class="btn btn-violet" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Retour a l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\site\confidentialite.blade.php ENDPATH**/ ?>