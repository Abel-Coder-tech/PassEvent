<?php $__env->startSection('title', 'Statistiques - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Statistiques'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-bar-chart-fill me-2" style="color: var(--sa-primary);"></i>Taux de remplissage par evenement</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Evenement</th><th>Capacite</th><th>Vendus</th><th>Taux remplissage</th><th>Recettes</th><th>Date</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $taux = $ev->capacite > 0 ? round(($ev->quota_vendu / $ev->capacite) * 100) : 0;
                ?>
                <tr>
                    <td><strong><?php echo e($ev->titre); ?></strong></td>
                    <td><?php echo e($ev->capacite); ?></td>
                    <td><?php echo e($ev->quota_vendu); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="flex:1;height:6px;background:#eee;border-radius:3px;overflow:hidden;">
                                <div style="height:100%;width:<?php echo e(min($taux,100)); ?>%;background:<?php echo e($taux >= 80 ? 'var(--sa-success)' : ($taux >= 50 ? 'var(--sa-warning)' : 'var(--sa-primary)')); ?>;border-radius:3px;"></div>
                            </div>
                            <span style="font-size:0.75rem;font-weight:600;"><?php echo e($taux); ?>%</span>
                        </div>
                    </td>
                    <td><?php echo e(number_format($ev->recettes ?? 0, 0, ',', ' ')); ?> F</td>
                    <td style="font-size:0.75rem;"><?php echo e($ev->date_event->format('d M Y')); ?></td>
                    <td>
                        <?php if($ev->statut === 'publié'): ?> <span class="sa-badge sa-badge-success">Publie</span>
                        <?php elseif($ev->statut === 'brouillon'): ?> <span class="sa-badge sa-badge-secondary">Brouillon</span>
                        <?php else: ?> <span class="sa-badge sa-badge-danger"><?php echo e($ev->statut); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\superadmin\statistiques.blade.php ENDPATH**/ ?>