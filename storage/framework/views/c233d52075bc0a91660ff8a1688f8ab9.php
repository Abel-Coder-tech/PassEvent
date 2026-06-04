<?php $__env->startSection('title', 'Organisateurs - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Organisateurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-person-plus-fill me-2" style="color: var(--sa-success);"></i>Creer un organisateur</span>
            </div>
            <div class="sa-card-body">
                <form action="<?php echo e(route('superadmin.organisateurs.creer')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-2">
                        <div class="col-6"><input type="text" name="nom" class="sa-form-control" placeholder="Nom complet" required></div>
                        <div class="col-6"><input type="email" name="email" class="sa-form-control" placeholder="Email" required></div>
                        <div class="col-6"><input type="password" name="mot_de_passe" class="sa-form-control" placeholder="Mot de passe" required></div>
                        <div class="col-3"><input type="text" name="telephone" class="sa-form-control" placeholder="Telephone"></div>
                        <div class="col-3"><input type="text" name="organisation" class="sa-form-control" placeholder="Organisation"></div>
                        <div class="col-12 mt-2"><button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-plus-lg"></i> Creer</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-shield-fill me-2" style="color: var(--sa-warning);"></i>Actions rapides</span>
            </div>
            <div class="sa-card-body">
                <p class="text-muted mb-2" style="font-size:0.8rem;">Cliquez sur les boutons dans le tableau pour suspendre ou reinitialiser le mot de passe d'un organisateur.</p>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-person-badge-fill me-2" style="color: var(--sa-primary);"></i>Organisateurs</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($organisateurs->total()); ?> total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Organisation</th><th>Evenements</th><th>Telephone</th><th>Inscrit</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $organisateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($org->nom); ?></strong></td>
                    <td><?php echo e($org->email); ?></td>
                    <td><?php echo e($org->organisation ?? '-'); ?></td>
                    <td><?php echo e($org->evenements_count); ?></td>
                    <td><?php echo e($org->telephone ?? '-'); ?></td>
                    <td style="font-size:0.75rem;"><?php echo e($org->created_at->format('d M Y')); ?></td>
                    <td>
                        <form action="<?php echo e(route('superadmin.organisateurs.suspendre', $org)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Suspendre <?php echo e($org->nom); ?> ? Ses evenements seront annules.')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger"><i class="bi bi-pause-fill"></i></button>
                        </form>
                        <form action="<?php echo e(route('superadmin.organisateurs.reset-password', $org)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Reinitialiser mot de passe"><i class="bi bi-key"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($organisateurs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\organisateurs.blade.php ENDPATH**/ ?>