<?php $__env->startSection('title', 'Tableau de bord'); ?>
<?php $__env->startSection('page-title', 'Bienvenue'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="panel-card text-center py-5">
                <?php if($statut === 'incomplet'): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-person-plus" style="color: var(--violet);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Finalisez votre inscription
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    <?php echo e($message); ?>

                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?php echo e(route('profil.step2')); ?>" class="btn btn-vert btn-sm">
                        <i class="bi bi-arrow-right-circle me-1"></i> Continuer
                    </a>
                    <a href="<?php echo e(route('logout')); ?>" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                <?php elseif($statut === 'corrections_apportees'): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-check2-circle" style="color: var(--vert);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Corrections apportées
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    <?php echo e($message); ?>

                </p>
                <div style="background: #f8f6f9; border-radius: 12px; padding: 1rem 1.5rem; display: inline-block; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="bi bi-clock-history" style="color: var(--violet); font-size: 1.25rem;"></i>
                        <span style="font-size: 0.9rem; color: var(--sombre); font-weight: 600;">
                            Délai estimé : 0 à 2h maximum
                        </span>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?php echo e(route('parametres.index')); ?>" class="btn btn-vert btn-sm">
                        <i class="bi bi-pencil me-1"></i> Modifier mon profil
                    </a>
                    <a href="<?php echo e(route('logout')); ?>" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                <?php elseif($statut === 'corrections_demandees'): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-pencil-square" style="color: #e0a800;"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Corrections demandées
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    <?php echo e($message); ?>

                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?php echo e(route('parametres.index')); ?>" class="btn btn-vert btn-sm">
                        <i class="bi bi-pencil me-1"></i> Modifier mon profil
                    </a>
                    <form action="<?php echo e(route('inscriptions.resubmit')); ?>" method="POST" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary-custom btn-sm">
                            <i class="bi bi-send me-1"></i> Soumettre à nouveau
                        </button>
                    </form>
                    <a href="<?php echo e(route('logout')); ?>" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                <?php elseif($statut === 'rejete'): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-x-circle" style="color: var(--danger);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Inscription rejetée
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    <?php echo e($message); ?>

                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?php echo e(route('logout')); ?>" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                <?php elseif($statut === 'bloque'): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-shield-exclamation" style="color: var(--danger);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Compte bloqué
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    <?php echo e($message); ?>

                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?php echo e(route('logout')); ?>" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                <?php else: ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-hourglass-split" style="color: var(--violet);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Profil en cours de validation
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    <?php echo e($message); ?>

                </p>
                <div style="background: #f8f6f9; border-radius: 12px; padding: 1rem 1.5rem; display: inline-block; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="bi bi-clock-history" style="color: var(--violet); font-size: 1.25rem;"></i>
                        <span style="font-size: 0.9rem; color: var(--sombre); font-weight: 600;">
                            Délai estimé : 0 à 2h maximum
                        </span>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="<?php echo e(route('parametres.index')); ?>" class="btn btn-vert btn-sm">
                        <i class="bi bi-pencil me-1"></i> Modifier mon profil
                    </a>
                    <a href="<?php echo e(route('logout')); ?>" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                <?php endif; ?>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display:none;"><?php echo csrf_field(); ?></form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/dashboard-pending.blade.php ENDPATH**/ ?>