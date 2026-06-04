<?php $__env->startSection('title', 'Scans - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Scans'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-qr-code me-2" style="color: var(--sa-primary);"></i>Historique des scans</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Evenement</th><th>Action</th><th>Details</th><th>IP</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($log->ticket?->evenement?->titre ?? 'N/A'); ?></td>
                    <td><span class="sa-badge sa-badge-info"><?php echo e($log->type_operation); ?></span></td>
                    <td style="font-size:0.75rem; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php echo e($log->details ? (is_string($log->details) ? $log->details : json_encode($log->details)) : '-'); ?>

                    </td>
                    <td style="font-family:monospace;font-size:0.75rem;"><?php echo e($log->ip ?? '-'); ?></td>
                    <td style="font-size:0.75rem;"><?php echo e($log->created_at->format('d M Y H:i:s')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($logs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\scans.blade.php ENDPATH**/ ?>