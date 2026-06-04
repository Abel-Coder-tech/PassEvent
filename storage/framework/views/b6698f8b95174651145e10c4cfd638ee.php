<?php $__env->startSection('title', 'Utilisateurs - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Utilisateurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-people-fill me-2" style="color: var(--sa-primary);"></i>Tous les utilisateurs</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($users->total()); ?> total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Role</th><th>Telephone</th><th>Evenements</th><th>Inscrit le</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($user->nom); ?></strong></td>
                    <td><?php echo e($user->email); ?></td>
                    <td>
                        <?php if($user->role === 'super_admin'): ?>
                            <span class="sa-badge sa-badge-info">Super Admin</span>
                        <?php else: ?>
                            <span class="sa-badge sa-badge-secondary">Admin</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($user->telephone ?? '-'); ?></td>
                    <td><?php echo e($user->evenements_count); ?></td>
                    <td style="font-size:0.75rem; color:var(--sa-text-muted);"><?php echo e($user->created_at->format('d M Y')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($users->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\utilisateurs.blade.php ENDPATH**/ ?>