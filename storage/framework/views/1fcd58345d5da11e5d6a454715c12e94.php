<?php $__env->startSection('title', 'Tableau de bord — Agent PaxEvent'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-house-door me-2" style="color:var(--violet);"></i>Tableau de bord
        </h4>
        <small class="text-muted"><?php echo e(now()->format('d/m/Y H:i')); ?></small>
    </div>

    <?php if(!$agent->actif): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>Votre compte a été désactivé par l'organisateur.
        </div>
    <?php elseif($evenement->date_event < now()): ?>
        <div class="alert alert-warning">
            <i class="bi bi-clock me-2"></i>L'événement <strong><?php echo e($evenement->titre); ?></strong> est terminé (<?php echo e($evenement->date_event->format('d/m/Y')); ?>).
        </div>
    <?php endif; ?>

    <?php if(session('scan_ok')): ?>
        <div class="alert alert-success py-2" style="font-size:0.85rem;border-radius:10px;">
            <i class="bi bi-check-circle me-1"></i>Code d'accès validé. Vous pouvez scanner.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card card-agent p-3">
                <div class="value"><?php echo e($stats['total']); ?></div>
                <div class="label">Total scans</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-agent p-3">
                <div class="value" style="color:#28a745;"><?php echo e($stats['valides']); ?></div>
                <div class="label">Validés</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-agent p-3">
                <div class="value" style="color:#dc3545;"><?php echo e($stats['invalides']); ?></div>
                <div class="label">Invalides</div>
            </div>
        </div>
    </div>

    <div class="card-agent p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h5 class="fw-bold mb-1"><?php echo e($evenement->titre); ?></h5>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-calendar3 me-1"></i><?php echo e($evenement->date_event->isoFormat('D MMMM YYYY')); ?>

                    <i class="bi bi-geo-alt ms-3 me-1"></i><?php echo e($evenement->lieu); ?>

                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if($agent->actif && $evenement->date_event >= now()): ?>
                    <?php if(session('agent_scan_ok') === $agent->id): ?>
                        <a href="<?php echo e(route('agent.scan.index')); ?>" class="btn btn-violet">
                            <i class="bi bi-qr-code-scan me-1"></i>Scanner
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('agent.scan.pin')); ?>" class="btn btn-violet">
                            <i class="bi bi-shield-lock me-1"></i>Accéder au scan
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        <i class="bi bi-lock me-1"></i>Scan indisponible
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($scans->isNotEmpty()): ?>
    <div class="card-agent p-0">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2" style="color:var(--violet);"></i>Derniers scans</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Résultat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $scans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="small"><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></td>
                        <td><code><?php echo e($log->details['code'] ?? ''); ?></code></td>
                        <td>
                            <?php if(($log->details['resultat'] ?? '') === 'valide'): ?>
                                <span class="badge bg-success">Validé</span>
                            <?php elseif(($log->details['resultat'] ?? '') === 'deja_utilise'): ?>
                                <span class="badge bg-warning text-dark">Déjà utilisé</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Invalide</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/agent/dashboard.blade.php ENDPATH**/ ?>