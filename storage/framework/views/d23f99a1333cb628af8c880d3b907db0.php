<?php $__env->startSection('title', 'Paramètres - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Parametres de la plateforme'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-gear-fill me-2" style="color: var(--sa-primary);"></i>Configuration plateforme</span></div>
            <div class="sa-card-body">
                <p class="text-muted" style="font-size:0.85rem;">Les parametres de configuration sont definis dans le fichier <code>.env</code> et les fichiers de configuration Laravel.</p>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Mode maintenance</span>
                    <span class="sa-badge sa-badge-secondary">Desactive</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>PassEvent Mail</span>
                    <span class="text-muted" style="font-size:0.8rem;">passevent2026@gmail.com</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>KKiaPay</span>
                    <span class="sa-badge sa-badge-success">Configure</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-bar-chart-steps me-2" style="color: var(--sa-primary);"></i>Informations systeme</span></div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Version Laravel</span><span class="text-muted"><?php echo e(app()->version()); ?></span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Environnement</span><span class="text-muted"><?php echo e(app()->environment()); ?></span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Total utilisateurs</span><strong><?php echo e(\App\Models\User::count()); ?></strong></div>
                <div class="d-flex justify-content-between py-2"><span>Total evenements</span><strong><?php echo e(\App\Models\Evenement::count()); ?></strong></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\parametres.blade.php ENDPATH**/ ?>