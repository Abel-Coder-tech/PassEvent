<?php $__env->startSection('title', 'Notifications - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-bell-fill me-2" style="color: var(--sa-primary);"></i>Messages de contact</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Objet</th><th>Message</th><th>Lu</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($msg->nom_complet); ?></strong></td>
                    <td><?php echo e($msg->email); ?></td>
                    <td><?php echo e($msg->objet); ?></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($msg->message); ?></td>
                    <td>
                        <?php if($msg->lu): ?> <span class="sa-badge sa-badge-success">Lu</span>
                        <?php else: ?> <span class="sa-badge sa-badge-danger">Non lu</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.75rem;"><?php echo e($msg->created_at->format('d M Y H:i')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($messages->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\notifications.blade.php ENDPATH**/ ?>