<?php $__env->startSection('title', 'Modération - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Moderation de contenu'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Evenements suspendus / annules</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Evenement</th><th>Organisateur</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $evenementsSuspendus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($ev->titre); ?></strong></td>
                    <td><?php echo e($ev->user->nom ?? '-'); ?></td>
                    <td><span class="sa-badge sa-badge-danger"><?php echo e($ev->statut); ?></span></td>
                    <td style="font-size:0.75rem;"><?php echo e($ev->date_event->format('d M Y')); ?></td>
                    <td>
                        <form action="<?php echo e(route('superadmin.evenements.mettre-en-avant', $ev)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-success);color:#fff;border:none;">Reactiver</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aucun evenement suspendu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($evenementsSuspendus->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\moderation.blade.php ENDPATH**/ ?>