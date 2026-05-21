<?php $__env->startSection('title', 'Contact - PassEvent'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Contact</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- ===== En-tête de la page ===== -->
<section class="page-header">
    <!-- Éléments décoratifs en arrière-plan -->
    <div class="page-header-bg">
        <div class="dot d1"></div>
        <div class="dot d2"></div>
        <div class="dot d3"></div>
    </div>
    <div class="container text-center position-relative" style="z-index:2;">
        <h1 class="page-header-title">Contactez-nous</h1>
        <p class="page-header-sub">Une question ou un problème ? Nous sommes là pour vous aider</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Formulaire de contact -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-envelope me-2" style="color: var(--violet);"></i>
                            Envoyez-nous un message
                        </h4>

                        <form action="<?php echo e(route('contact.store')); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            <!-- Nom complet -->
                            <div class="mb-3">
                                <label for="nom_complet" class="form-label fw-semibold">Nom et prenoms <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['nom_complet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="nom_complet" name="nom_complet" value="<?php echo e(old('nom_complet')); ?>" placeholder="Ex: Kofi Mensah" required>
                                <?php $__errorArgs = ['nom_complet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Adresse email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="votre@email.com" required>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Objet -->
                            <div class="mb-3">
                                <label for="objet" class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php $__errorArgs = ['objet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="objet" name="objet" value="<?php echo e(old('objet')); ?>" placeholder="Ex: Probleme de paiement, Question sur un evenement..." required>
                                <?php $__errorArgs = ['objet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="message" name="message" rows="5" placeholder="Decrivez votre demande en detail..." required><?php echo e(old('message')); ?></textarea>
                                <small class="text-muted">Minimum 10 caracteres, maximum 2000 caracteres</small>
                                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <button type="submit" class="btn btn-violet w-100 py-3" style="border-radius: 8px;">
                                <i class="bi bi-send me-2"></i> Envoyer le message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Coordonnees + Google Maps -->
            <div class="col-12 col-lg-5">
                <!-- Coordonnees -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Nos coordonnees</h5>

                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3 flex-shrink-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(18,151,110,0.1);">
                                    <i class="bi bi-whatsapp" style="font-size: 1.2rem; color: var(--vert);"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">WhatsApp</h6>
                                <a href="https://wa.me/22943704513" class="text-decoration-none" style="color: var(--violet); font-size: 0.9rem;">+229 43 70 45 13</a>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Reponse rapide garantie</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3 flex-shrink-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(135,66,139,0.08);">
                                    <i class="bi bi-envelope" style="font-size: 1.2rem; color: var(--violet);"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <a href="mailto:passevent2026@gmail.com" class="text-decoration-none" style="color: var(--violet); font-size: 0.9rem;">passevent2026@gmail.com</a>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Reponse sous 24h</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <div class="me-3 flex-shrink-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(66,140,121,0.08);">
                                    <i class="bi bi-phone" style="font-size: 1.2rem; color: var(--teal);"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Telephone</h6>
                                <a href="tel:+22943704513" class="text-decoration-none" style="color: var(--violet); font-size: 0.9rem;">+229 43 70 45 13</a>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Lun-Ven, 8h-18h</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Maps -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div style="height: 250px;">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63405.62982906847!2d2.36686!3d6.3703!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x102355ec41a5e8e3%3A0xe545b0b0e4f5d5c0!2sCotonou%2C%20Benin!5e0!3m2!1sfr!2sbj!4v1700000000000!5m2!1sfr!2sbj"
                            width="100%"
                            height="250"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="p-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt-fill" style="color: var(--violet);"></i>
                            <strong style="font-size: 0.9rem;">Cotonou, Benin</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<style>
/* ===== En-tête de page partagé ===== */
.page-header {
    padding: 3rem 0;
    background: linear-gradient(135deg, #7B3FA0, #6a1b9a);
    color: #fff;
    position: relative;
    overflow: hidden;
    text-align: center;
}
.page-header-bg {
    position: absolute; inset: 0;
    pointer-events: none;
}
.dot {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.2;
}
.d1 { width: 200px; height: 200px; background: #fff; top: -60px; right: -60px; }
.d2 { width: 140px; height: 140px; background: #fff; bottom: -40px; left: 20%; }
.d3 { width: 100px; height: 100px; background: #fff; top: 30%; left: -30px; }
.page-header-title {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.3rem;
    animation: fadeUp 0.5s ease forwards;
}
.page-header-sub {
    font-size: 1rem;
    opacity: 0.85;
    margin: 0;
    animation: fadeUp 0.5s ease 0.15s both;
}
@keyframes fadeUp { 0%{opacity:0;transform:translateY(12px)} 100%{opacity:1;transform:translateY(0)} }
</style>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/site/contact.blade.php ENDPATH**/ ?>