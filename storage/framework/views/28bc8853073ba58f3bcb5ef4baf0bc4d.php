<?php $__env->startSection('title', 'Transactions - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-stack me-2" style="color: var(--sa-primary);"></i>Transactions KKiaPay</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($transactions->total()); ?> total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Transaction ID</th><th>Evenement</th><th>Montant</th><th>Statut</th><th>Methode</th><th>Acheteur</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-family:monospace;font-size:0.75rem;"><?php echo e($t->transaction_id); ?></td>
                    <td><?php echo e($t->evenement->titre ?? '-'); ?></td>
                    <td><strong><?php echo e(number_format($t->montant, 0, ',', ' ')); ?> F</strong></td>
                    <td>
                        <?php if($t->statut_paiement === 'payé'): ?> <span class="sa-badge sa-badge-success">Reussi</span>
                        <?php elseif($t->statut_paiement === 'échoué'): ?> <span class="sa-badge sa-badge-danger">Echoue</span>
                        <?php elseif($t->statut_paiement === 'remboursé'): ?> <span class="sa-badge sa-badge-warning">Rembourse</span>
                        <?php else: ?> <span class="sa-badge sa-badge-secondary"><?php echo e($t->statut_paiement); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($t->methode_paiement ?? '-'); ?></td>
                    <td><?php echo e($t->email_acheteur); ?></td>
                    <td style="font-size:0.75rem;"><?php echo e($t->created_at->format('d M Y H:i')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($transactions->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\transactions.blade.php ENDPATH**/ ?>