<?php $__env->startSection('title', 'Paiements — Espace affilié PaxEvent'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="h4 fw-bold mb-1">Paiements</h1>
    <p class="text-muted small mb-4">Demandez le reversement de vos commissions disponibles.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Solde disponible</div>
                <div class="value"><?php echo e(number_format((float) $affiliate->solde_disponible, 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">En attente</div>
                <div class="value"><?php echo e(number_format((float) $affiliate->solde_en_attente, 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Total gagné</div>
                <div class="value"><?php echo e(number_format((float) $affiliate->total_gagne, 0, ',', ' ')); ?> <small class="fs-6">FCFA</small></div>
            </div>
        </div>
    </div>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3"><i class="bi bi-cash-stack me-1"></i> Nouvelle demande de retrait</h2>

            <?php if(!$affiliate->operateur_paiement || !$affiliate->numero_paiement): ?>
                <?php
                    $manquant = array_filter([
                        'opérateur de paiement' => !$affiliate->operateur_paiement,
                        'numéro Mobile Money' => !$affiliate->numero_paiement,
                    ]);
                ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Pour demander un retrait, complétez d'abord vos coordonnées de paiement
                    (<?php echo e(implode(', ', array_keys($manquant))); ?>) dans votre
                    <a href="<?php echo e(route('affiliate.profil')); ?>" class="alert-link">profil</a>.
                </div>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('affiliate.paiements.demander')); ?>" class="row g-3 align-items-end">
                    <?php echo csrf_field(); ?>
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-muted mb-1">Montant à retirer (FCFA)</label>
                        <input type="number" name="montant" class="form-control <?php $__errorArgs = ['montant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               min="5000" step="100" max="<?php echo e((int) $affiliate->solde_disponible); ?>"
                               value="<?php echo e(old('montant')); ?>" placeholder="5000">
                        <?php $__errorArgs = ['montant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-text">Minimum 5 000 FCFA · Disponible : <?php echo e(number_format((float) $affiliate->solde_disponible, 0, ',', ' ')); ?> FCFA</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Versement</label>
                        <div class="form-control-plaintext small">
                            <?php echo e($affiliate->operateur_paiement); ?> · <?php echo e($affiliate->numero_paiement); ?>

                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-violet w-100"><i class="bi bi-send me-1"></i> Demander</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Historique des demandes</h2>
            <?php if($payouts->isEmpty()): ?>
                <?php echo $__env->make('affiliate.partials.empty-state', [
                    'icon' => 'bi-wallet2',
                    'title' => 'Aucune demande de retrait',
                    'text' => 'Vos demandes de reversement apparaîtront ici.',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Date</th>
                                <th class="text-end">Montant</th>
                                <th>Versement</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="small"><?php echo e($payout->created_at?->format('d/m/Y')); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e(number_format((float) $payout->montant, 0, ',', ' ')); ?> FCFA</td>
                                    <td class="small"><?php echo e($payout->operateur); ?> · <?php echo e($payout->numero_paiement); ?></td>
                                    <td class="text-end">
                                        <?php
                                            $badge = [
                                                'demande' => 'bg-warning-subtle text-warning-emphasis',
                                                'valide' => 'bg-info-subtle text-info-emphasis',
                                                'paye' => 'bg-success-subtle text-success-emphasis',
                                                'refuse' => 'bg-danger-subtle text-danger-emphasis',
                                            ][$payout->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $label = ['demande' => 'En cours', 'valide' => 'Validé', 'paye' => 'Payé', 'refuse' => 'Refusé'][$payout->statut] ?? $payout->statut;
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

<?php echo $__env->make('layouts.affiliate', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/paiements.blade.php ENDPATH**/ ?>