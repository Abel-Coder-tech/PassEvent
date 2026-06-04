<?php $__env->startSection('title', 'Sécurité - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Securite'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-shield-fill me-2" style="color: var(--sa-danger);"></i>Alertes securite</span></div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Paiements echoues</span><strong style="color:var(--sa-danger);"><?php echo e($logsSuspects->whereIn('type_operation', ['echec_paiement'])->count()); ?></strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Erreurs paiement</span><strong style="color:var(--sa-warning);"><?php echo e($logsSuspects->whereIn('type_operation', ['erreur_paiement'])->count()); ?></strong></div>
                <div class="d-flex justify-content-between py-2"><span>Total evenements annules</span><strong><?php echo e(\App\Models\Evenement::where('statut', 'annulé')->count()); ?></strong></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-exclamation-triangle-fill me-2" style="color: var(--sa-warning);"></i>Activite suspecte (50 dernieres entrees)</span></div>
            <div class="sa-card-body p-0" style="max-height:400px;overflow-y:auto;">
                <table class="sa-table">
                    <thead><tr><th>Type</th><th>Evenement</th><th>IP</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logsSuspects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="sa-badge sa-badge-danger"><?php echo e($log->type_operation); ?></span></td>
                            <td><?php echo e($log->ticket?->evenement?->titre ?? 'N/A'); ?></td>
                            <td style="font-family:monospace;font-size:0.75rem;"><?php echo e($log->ip ?? '-'); ?></td>
                            <td style="font-size:0.75rem;"><?php echo e($log->created_at->format('d M Y H:i')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucune activite suspecte</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\securite.blade.php ENDPATH**/ ?>