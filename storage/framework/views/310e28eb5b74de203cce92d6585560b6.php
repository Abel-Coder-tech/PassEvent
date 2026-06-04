<?php $__env->startSection('title', 'Logs système - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Logs systeme'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-journal-text me-2" style="color: var(--sa-primary);"></i>Journal des actions</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($logs->total()); ?> entrees</span>
    </div>
    <div class="sa-card-body p-0" style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr><th>Admin</th><th>Action</th><th>Evenement</th><th>Details</th><th>IP</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <?php if($log->ticket_id): ?>
                            <span style="font-size:0.75rem;"><?php echo e($log->ticket?->email_acheteur ?? '-'); ?></span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.75rem;">Systeme</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="sa-badge sa-badge-info"><?php echo e($log->type_operation); ?></span></td>
                    <td><?php echo e($log->ticket?->evenement?->titre ?? 'N/A'); ?></td>
                    <td style="font-size:0.72rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php echo e($log->details ? (is_string($log->details) ? \Illuminate\Support\Str::limit($log->details, 60) : \Illuminate\Support\Str::limit(json_encode($log->details), 60)) : '-'); ?>

                    </td>
                    <td style="font-family:monospace;font-size:0.75rem;"><?php echo e($log->ip ?? '-'); ?></td>
                    <td style="font-size:0.72rem; white-space:nowrap;"><?php echo e($log->created_at->format('d M Y H:i:s')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($logs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\logs.blade.php ENDPATH**/ ?>