<?php $__env->startSection('title', 'Affiliations - Super Admin PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Programme d\'affiliation'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['total']); ?></div>
                <div class="kpi-label">Affiliés (<?php echo e($stats['actifs']); ?> actifs)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['en_attente'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Commissions en attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['disponible'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Disponible (à reverser)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['paye'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Commissions payées</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body">
        <div class="d-flex flex-nowrap gap-2 align-items-center overflow-auto justify-content-between">
            <div class="d-flex flex-nowrap gap-2 align-items-center">
                <form method="GET" action="<?php echo e(route('superadmin.affiliations.index')); ?>" id="affiliesFilterForm" class="d-flex flex-nowrap gap-2 align-items-center">
                    <input type="text" name="q" id="affiliesSearch" value="<?php echo e($recherche); ?>" class="sa-form-control" placeholder="Nom, email, code..." style="min-width:200px;" autocomplete="off">
                    <select name="statut" id="affiliesStatut" class="sa-form-control" style="min-width:140px;">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?php if($statut === 'actif'): echo 'selected'; endif; ?>>Actifs</option>
                        <option value="inactif" <?php if($statut === 'inactif'): echo 'selected'; endif; ?>>Inactifs</option>
                    </select>
                    <?php if($recherche !== '' || $statut): ?>
                        <a href="<?php echo e(route('superadmin.affiliations.index')); ?>" class="sa-btn sa-btn-outline">Réinitialiser</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="d-flex flex-nowrap gap-2 align-items-center ms-auto">
                <a href="<?php echo e(route('superadmin.affiliations.dashboard')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
                <a href="<?php echo e(route('superadmin.affiliations.commissions')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-list-columns"></i> Commissions</a>
                <a href="<?php echo e(route('superadmin.affiliations.reversements')); ?>" class="sa-btn sa-btn-outline">
                    <i class="bi bi-cash-coin"></i> Reversements
                    <?php if($stats['reversements_demandes'] > 0): ?>
                        <span class="sa-badge sa-badge-warning ms-1"><?php echo e($stats['reversements_demandes']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-people-fill me-2" style="color: var(--sa-primary);"></i>Affiliés</span>
        <span class="sa-topbar-badge"><?php echo e($affilies->total()); ?> compte(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($affilies->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Affilié</th>
                            <th>Code</th>
                            <th class="text-center">Liens</th>
                            <th class="text-center">Filleuls</th>
                            <th class="text-end">En attente</th>
                            <th class="text-end">Disponible</th>
                            <th class="text-end">Total gagné</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $affilies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $affilie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($affilie->nom_complet); ?></strong>
                                    <br><small style="color:var(--sa-text-muted);"><?php echo e($affilie->email); ?></small>
                                </td>
                                <td><code style="font-size:0.72rem;"><?php echo e($affilie->code_parrainage); ?></code></td>
                                <td class="text-center"><?php echo e($affilie->event_links_count); ?></td>
                                <td class="text-center"><?php echo e($affilie->referrals_count); ?></td>
                                <td class="text-end"><?php echo e(number_format((float) $affilie->solde_en_attente, 0, ',', ' ')); ?> F</td>
                                <td class="text-end"><strong><?php echo e(number_format((float) $affilie->solde_disponible, 0, ',', ' ')); ?> F</strong></td>
                                <td class="text-end"><?php echo e(number_format((float) $affilie->total_gagne, 0, ',', ' ')); ?> F</td>
                                <td class="text-center">
                                    <?php if($affilie->actif): ?>
                                        <span class="sa-badge sa-badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('superadmin.affiliations.show', $affilie)); ?>" class="sa-btn sa-btn-sm sa-btn-primary" title="Voir la fiche">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="<?php echo e(route('superadmin.affiliations.statut', $affilie)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="sa-btn sa-btn-sm <?php echo e($affilie->actif ? 'sa-btn-danger' : 'sa-btn-primary'); ?>"
                                            title="<?php echo e($affilie->actif ? 'Désactiver' : 'Réactiver'); ?>"
                                            onclick="return confirm('<?php echo e($affilie->actif ? 'Désactiver' : 'Réactiver'); ?> ce compte affilié ?')">
                                            <i class="bi <?php echo e($affilie->actif ? 'bi-pause-fill' : 'bi-play-fill'); ?>"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3"><?php echo e($affilies->links()); ?></div>
        <?php else: ?>
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-person-plus" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucun affilié trouvé.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        const form = document.getElementById('affiliesFilterForm');
        const search = document.getElementById('affiliesSearch');
        const statut = document.getElementById('affiliesStatut');
        if (!form) return;
        let timeout;
        if (search) {
            search.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() { form.submit(); }, 400);
            });
        }
        if (statut) {
            statut.addEventListener('change', function() { form.submit(); });
        }
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/affiliations/index.blade.php ENDPATH**/ ?>