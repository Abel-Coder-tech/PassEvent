<?php $__env->startSection('title', 'Mes commissions — Espace affilié PaxEvent'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="h4 fw-bold mb-1">Mes commissions</h1>
    <p class="text-muted small mb-4">Détail de vos commissions de co-parrainage et des éventuels parrainages historiques.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Total gagné</div>
                <div class="value"><?php echo e(number_format((float) $totaux['total'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">En attente</div>
                <div class="value"><?php echo e(number_format((float) $totaux['en_attente'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Disponible</div>
                <div class="value"><?php echo e(number_format((float) $totaux['disponible'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card card h-100">
                <div class="label">Déjà payé</div>
                <div class="value"><?php echo e(number_format((float) $totaux['paye'], 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="co_parrainage" <?php if(request('type') === 'co_parrainage'): echo 'selected'; endif; ?>>Co-parrainage</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="en_attente" <?php if(request('statut') === 'en_attente'): echo 'selected'; endif; ?>>En attente</option>
                        <option value="disponible" <?php if(request('statut') === 'disponible'): echo 'selected'; endif; ?>>Disponible</option>
                        <option value="paye" <?php if(request('statut') === 'paye'): echo 'selected'; endif; ?>>Payé</option>
                        <option value="annule" <?php if(request('statut') === 'annule'): echo 'selected'; endif; ?>>Annulé</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-violet btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filtrer</button>
                </div>
            </form>

            <?php if($commissions->isEmpty()): ?>
                <?php echo $__env->make('affiliate.partials.empty-state', [
                    'icon' => 'bi-cash-coin',
                    'title' => 'Aucune commission trouvée',
                    'text' => 'Aucun résultat pour ces filtres. Vos commissions apparaîtront ici dès vos premières ventes tracées.',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Date</th>
                                <th>Type</th>
                                <th>Événement</th>
                                <th class="text-end">Base</th>
                                <th class="text-end">Taux</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="small"><?php echo e($commission->created_at?->format('d/m/Y')); ?></td>
                                    <td class="small">Co-parrainage</td>
                                    <td class="small"><?php echo e($commission->evenement?->titre ?? '—'); ?></td>
                                    <td class="text-end small"><?php echo e(number_format((float) $commission->base_montant, 0, ',', ' ')); ?> F</td>
                                    <td class="text-end small"><?php echo e(rtrim(rtrim(number_format((float) $commission->pourcentage, 2), '0'), '.')); ?> %</td>
                                    <td class="text-end fw-semibold"><?php echo e(number_format((float) $commission->montant, 0, ',', ' ')); ?> F</td>
                                    <td class="text-end">
                                        <?php
                                            $badge = [
                                                'en_attente' => 'bg-warning-subtle text-warning-emphasis',
                                                'disponible' => 'bg-info-subtle text-info-emphasis',
                                                'paye' => 'bg-success-subtle text-success-emphasis',
                                                'annule' => 'bg-secondary-subtle text-secondary-emphasis',
                                            ][$commission->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $label = ['en_attente' => 'En attente', 'disponible' => 'Disponible', 'paye' => 'Payé', 'annule' => 'Annulé'][$commission->statut] ?? $commission->statut;
                                        ?>
                                        <span class="badge <?php echo e($badge); ?>"><?php echo e($label); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-center"><?php echo e($commissions->links()); ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.affiliate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/commissions.blade.php ENDPATH**/ ?>