<?php $__env->startSection('title', 'Tickets - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Tickets'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i>Tous les tickets</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($allTickets->total()); ?> total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Code unique</th><th>Evenement</th><th>Acheteur</th><th>WhatsApp</th><th>N° paiement</th><th>Montant</th><th>Statut</th><th>Utilise</th><th>Date achat</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $allTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-family:monospace;font-size:0.75rem;"><?php echo e($t->code_unique); ?></td>
                    <td><?php echo e($t->evenement->titre ?? '-'); ?></td>
                    <td><?php echo e($t->email_acheteur); ?><br><small class="text-muted"><?php echo e($t->nom_acheteur ?? '-'); ?></small></td>
                    <td><?php if($t->whatsapp_acheteur): ?><?php echo e($t->whatsapp_acheteur); ?><?php else: ?> <span class="text-muted">—</span> <?php endif; ?></td>
                    <td><?php if($t->telephone_paiement): ?><?php echo e($t->telephone_paiement); ?><?php elseif($t->telephone_acheteur): ?><?php echo e($t->telephone_acheteur); ?><?php else: ?> <span class="text-muted">—</span> <?php endif; ?></td>
                    <td><strong><?php if($t->montant > 0): ?><?php echo e(number_format($t->montant, 0, ',', ' ')); ?> F <?php else: ?> Gratuit <?php endif; ?></strong></td>
                    <td>
                        <?php if($t->statut_paiement === 'payé'): ?> <span class="sa-badge sa-badge-success">Paye</span>
                        <?php elseif($t->statut_paiement === 'échoué'): ?> <span class="sa-badge sa-badge-danger">Echoue</span>
                        <?php elseif($t->statut_paiement === 'remboursé'): ?> <span class="sa-badge sa-badge-warning">Rembourse</span>
                        <?php else: ?> <span class="sa-badge sa-badge-secondary"><?php echo e($t->statut_paiement); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($t->utilise): ?> <span class="sa-badge sa-badge-success">Oui</span>
                        <?php else: ?> <span class="sa-badge sa-badge-secondary">Non</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.75rem;"><?php echo e($t->date_achat ? \Carbon\Carbon::parse($t->date_achat)->isoFormat('D MMM YYYY HH:mm') : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($allTickets->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/tickets.blade.php ENDPATH**/ ?>