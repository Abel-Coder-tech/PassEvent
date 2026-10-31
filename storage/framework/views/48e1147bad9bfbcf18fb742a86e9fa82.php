<?php $__env->startSection('title', $agent->nom . ' — Agent de scan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="<?php echo e(route('admin.agents.index')); ?>" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Tous les agents
        </a>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="alert alert-danger py-2 small"><?php echo e($errors->all()[0]); ?></div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                        style="width: 64px; height: 64px; background: #f5f3ff;">
                        <i class="bi bi-person text-purple-700" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo e($agent->nom); ?></h5>
                    <p class="text-muted small mb-2"><?php echo e($agent->email); ?></p>
                    <span class="badge bg-<?php echo e($agent->actif ? 'success' : 'secondary'); ?> mb-2">
                        <?php echo e($agent->actif ? 'Actif' : 'Inactif'); ?>

                    </span>
                    <p class="small mb-0">
                        <i class="bi bi-calendar-event"></i>
                        <a href="<?php echo e(route('admin.evenements.show', $agent->evenement)); ?>" class="text-decoration-none">
                            <?php echo e($agent->evenement->titre); ?>

                        </a>
                    </p>
                    <div class="text-start small mt-3 pt-2 border-top">
                        <div class="mb-1"><strong>Code d'accès :</strong> <code><?php echo e($agent->code_acces); ?></code></div>
                        <div class="mb-1"><strong>Dernier accès :</strong> <?php echo e($agent->dernier_acces ? $agent->dernier_acces->format('d/m/Y H:i') : 'Jamais'); ?></div>
                        <div class="mb-1"><strong>Créé le :</strong> <?php echo e($agent->created_at->format('d/m/Y H:i')); ?></div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <form action="<?php echo e(route('admin.agents.toggle-actif', $agent)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-outline-<?php echo e($agent->actif ? 'warning' : 'success'); ?>">
                                <i class="bi bi-<?php echo e($agent->actif ? 'pause' : 'play'); ?>"></i>
                                <?php echo e($agent->actif ? 'Désactiver' : 'Réactiver'); ?>

                            </button>
                        </form>
                        <a href="<?php echo e(route('admin.agents.edit', $agent)); ?>"
                            class="btn btn-sm btn-outline-<?php echo e($agent->peutEtreReaffecte() ? 'primary' : 'secondary'); ?>">
                            <i class="bi bi-<?php echo e($agent->peutEtreReaffecte() ? 'arrow-repeat' : 'pencil-square'); ?>"></i>
                            <?php echo e($agent->peutEtreReaffecte() ? 'Réaffecter' : 'Modifier'); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            
            <?php $ongletGlobal = request('onglet') === 'global'; ?>
            <?php
                $s = $ongletGlobal ? $statsGlobales : $stats;
                $liste = $ongletGlobal ? $logsGlobaux : $logs;
            ?>

            <ul class="nav nav-pills mb-2">
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 <?php echo e($ongletGlobal ? '' : 'active'); ?>"
                        href="<?php echo e(request()->fullUrlWithQuery(['onglet' => null])); ?>">
                        <i class="bi bi-calendar-event me-1"></i> Cet événement
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 <?php echo e($ongletGlobal ? 'active' : ''); ?>"
                        href="<?php echo e(request()->fullUrlWithQuery(['onglet' => 'global'])); ?>">
                        <i class="bi bi-globe me-1"></i> Tous les événements
                    </a>
                </li>
            </ul>

            <div class="row g-2">
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-purple-50 text-center py-3">
                        <div class="fw-bold fs-4 text-purple-700"><?php echo e($s['total_scans']); ?></div>
                        <small class="text-muted">Scans effectués</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-green-50 text-center py-3">
                        <div class="fw-bold fs-4 text-green-700"><?php echo e($s['scans_ajd']); ?></div>
                        <small class="text-muted">Aujourd'hui</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-blue-50 text-center py-3">
                        <div class="fw-bold fs-4 text-blue-700"><?php echo e($s['valides']); ?></div>
                        <small class="text-muted">Validés</small>
                    </div>
                </div>
            </div>

            
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-check-circle"></i> Par résultat</h6>
                            <div class="d-flex justify-content-between small">
                                <span class="text-success">Validés</span>
                                <span class="fw-medium"><?php echo e($s['valides']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-warning">Déjà utilisés</span>
                                <span class="fw-medium"><?php echo e($s['deja_utilises']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-danger">Invalides</span>
                                <span class="fw-medium"><?php echo e($s['invalides']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-info-circle"></i> Informations</h6>
                            <div class="d-flex justify-content-between small">
                                <span>Statut</span>
                                <span class="fw-medium"><?php echo e($agent->actif ? 'Actif' : 'Inactif'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Dernier accès</span>
                                <span class="fw-medium"><?php echo e($s['dernier_acces'] ? $s['dernier_acces']->diffForHumans() : 'Jamais'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Périmètre</span>
                                <span class="fw-medium"><?php echo e($ongletGlobal ? 'Tous les événements' : 'Cet événement'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-clock-history"></i> Historique des scans
                <span class="text-muted fw-normal small ms-1">— <?php echo e($ongletGlobal ? 'tous les événements' : 'cet événement'); ?></span>
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Ticket</th>
                        <?php if($ongletGlobal): ?><th>Événement</th><?php endif; ?>
                        <th>Résultat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $liste; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="ps-3"><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></td>
                        <td>
                            <?php if($log->ticket): ?>
                                <code><?php echo e($log->ticket->code_unique); ?></code>
                            <?php else: ?>
                                <code><?php echo e($log->details['code'] ?? 'N/A'); ?></code>
                            <?php endif; ?>
                        </td>
                        <?php if($ongletGlobal): ?>
                        <td><?php echo e($log->ticket?->evenement?->titre ?? '—'); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php $resultat = $log->details['resultat'] ?? ''; ?>
                            <?php if($resultat === 'valide'): ?>
                                <span class="badge bg-success">Validé</span>
                            <?php elseif($resultat === 'deja_utilise'): ?>
                                <span class="badge bg-warning text-dark">Déjà utilisé</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Invalide</span>
                            <?php endif; ?>
                            <?php if(!empty($log->details['raison'])): ?>
                            <small class="d-block text-muted"><?php echo e($log->details['raison']); ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e($ongletGlobal ? 4 : 3); ?>" class="text-center text-muted py-3">Aucun scan effectué</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($liste->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($liste->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bg-purple-50 { background: #f5f3ff; }
.text-purple-700 { color: #7c3aed; }
.bg-green-50 { background: #f0fdf4; }
.text-green-700 { color: #16a34a; }
.bg-blue-50 { background: #eff6ff; }
.text-blue-700 { color: #2563eb; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/agents/show.blade.php ENDPATH**/ ?>