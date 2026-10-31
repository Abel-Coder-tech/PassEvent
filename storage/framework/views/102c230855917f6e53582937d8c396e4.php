<?php $__env->startSection('title', 'Historique des parrainages — Espace affilié PaxEvent'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="h4 fw-bold mb-1">Historique des parrainages</h1>
    <p class="text-muted small mb-4">Consultation des inscriptions attribuées au programme de parrainage direct avant son arrêt. Les nouveaux gains proviennent du co-parrainage d'événements.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Filleuls</div>
                <div class="value"><?php echo e($stats['total']); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Filleuls validés</div>
                <div class="value"><?php echo e($stats['valides']); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Clics sur votre lien</div>
                <div class="value"><?php echo e(number_format((int) $stats['clics'], 0, ',', ' ')); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Commissions générées</div>
                <div class="value"><?php echo e(number_format((float) $stats['commissions'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Liste de vos filleuls</h2>
            <?php if($referrals->isEmpty()): ?>
                <?php echo $__env->make('affiliate.partials.empty-state', [
                    'icon' => 'bi-people',
                    'title' => 'Aucun filleul',
                    'text' => 'Aucun parrainage historique à afficher.',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Organisateur</th>
                                <th>Inscrit le</th>
                                <th>Premier événement</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ref): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold small"><?php echo e($ref->organisateur?->nom ?? '—'); ?></div>
                                        <div class="text-muted" style="font-size:.78rem;"><?php echo e($ref->organisateur?->email); ?></div>
                                    </td>
                                    <td class="small"><?php echo e($ref->created_at?->format('d/m/Y')); ?></td>
                                    <td class="small"><?php echo e($ref->premierEvenement?->titre ?? '—'); ?></td>
                                    <td class="text-end">
                                        <?php
                                            $badge = [
                                                'en_attente' => 'bg-warning-subtle text-warning-emphasis',
                                                'valide' => 'bg-success-subtle text-success-emphasis',
                                                'annule' => 'bg-secondary-subtle text-secondary-emphasis',
                                            ][$ref->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $label = ['en_attente' => 'En attente', 'valide' => 'Validé', 'annule' => 'Annulé'][$ref->statut] ?? $ref->statut;
                                        ?>
                                        <span class="badge <?php echo e($badge); ?>"><?php echo e($label); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.affiliate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/filleuls.blade.php ENDPATH**/ ?>