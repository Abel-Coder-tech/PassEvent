<?php $__env->startSection('title', 'Commissions - Super Admin PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Commissions d\'affiliation'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['total'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Total généré</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['en_attente'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['disponible'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Disponible</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['paye'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Payées</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body">
        <div class="d-flex flex-nowrap gap-2 align-items-center overflow-auto">
            <form method="GET" action="<?php echo e(route('superadmin.affiliations.commissions')); ?>" id="commissionsFilterForm" class="d-flex flex-nowrap gap-2 align-items-center">
                <input type="text" name="q" id="commissionsSearch" value="<?php echo e($recherche); ?>" class="sa-form-control" placeholder="Rechercher..." style="width:160px; min-width:160px; flex:0 0 160px; height:32px; padding:0.3rem 0.5rem; font-size:0.75rem;" autocomplete="off">
                <select name="statut" id="commissionsStatut" class="sa-form-control" style="width:120px; min-width:120px; flex:0 0 120px; height:32px; padding:0.3rem 0.5rem; font-size:0.75rem;">
                    <option value="">Statut</option>
                    <option value="en_attente" <?php if($statut === 'en_attente'): echo 'selected'; endif; ?>>En attente</option>
                    <option value="disponible" <?php if($statut === 'disponible'): echo 'selected'; endif; ?>>Disponible</option>
                    <option value="paye" <?php if($statut === 'paye'): echo 'selected'; endif; ?>>Payée</option>
                    <option value="annule" <?php if($statut === 'annule'): echo 'selected'; endif; ?>>Annulée</option>
                </select>
                <?php if($recherche !== '' || $statut): ?>
                    <a href="<?php echo e(route('superadmin.affiliations.commissions')); ?>" class="sa-btn sa-btn-outline" style="height:32px; padding:0.25rem 0.45rem; font-size:0.75rem; line-height:1;">×</a>
                <?php endif; ?>
            </form>
            <a href="<?php echo e(route('superadmin.affiliations.index')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-people"></i></a>
            <a href="<?php echo e(route('superadmin.affiliations.dashboard')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-speedometer2"></i></a>
            <a href="<?php echo e(route('superadmin.affiliations.reversements')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-cash-coin"></i></a>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-stack me-2" style="color: var(--sa-primary);"></i>Commissions</span>
        <span class="sa-topbar-badge"><?php echo e($commissions->total()); ?> ligne(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($commissions->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Affilié</th>
                            <th>Type</th>
                            <th>Événement</th>
                            <th class="text-end">Base</th>
                            <th>%</th>
                            <th class="text-end">Montant</th>
                            <th>Statut</th>
                            <th>Disponible le</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo e($commission->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <?php if($commission->affiliate): ?>
                                        <a href="<?php echo e(route('superadmin.affiliations.show', $commission->affiliate)); ?>" style="color:var(--sa-primary);text-decoration:none;">
                                            <strong><?php echo e($commission->affiliate->nom_complet); ?></strong>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($commission->type === \App\Models\AffiliateCommission::TYPE_PARRAINAGE): ?>
                                        <span class="sa-badge" style="background:rgba(84,38,128,0.1);color:var(--sa-primary);">Parrainage direct</span>
                                    <?php else: ?>
                                        <span class="sa-badge" style="background:rgba(52,152,219,0.1);color:#3498db;">Co-parrainage</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($commission->evenement?->titre ?? '—'); ?></td>
                                <td class="text-end"><?php echo e(number_format((float) $commission->base_montant, 0, ',', ' ')); ?> F</td>
                                <td><?php echo e(rtrim(rtrim(number_format((float) $commission->pourcentage, 2, ',', ' '), '0'), ',')); ?> %</td>
                                <td class="text-end"><strong><?php echo e(number_format((float) $commission->montant, 0, ',', ' ')); ?> F</strong></td>
                                <td><?php echo $__env->make('superadmin.affiliations.partials.statut-commission', ['commission' => $commission], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                <td style="white-space:nowrap;"><?php echo e($commission->disponible_le?->format('d/m/Y H:i') ?? '—'); ?></td>
                                <td class="text-end">
                                    <?php if(! in_array($commission->statut, ['paye', 'annule'], true) && ! $commission->affiliate_payout_id): ?>
                                        <form action="<?php echo e(route('superadmin.affiliations.commissions.annuler', $commission)); ?>" method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Annuler la commission"
                                                onclick="return confirm('Annuler cette commission ? Le solde de l\'affilié sera recalculé.')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:var(--sa-text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3"><?php echo e($commissions->links()); ?></div>
        <?php else: ?>
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucune commission trouvée.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        const form = document.getElementById('commissionsFilterForm');
        const search = document.getElementById('commissionsSearch');
        const statut = document.getElementById('commissionsStatut');
        if (!form) return;

        let timeout;
        if (search) {
            search.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    form.submit();
                }, 400);
            });
        }
        if (statut) {
            statut.addEventListener('change', function() {
                form.submit();
            });
        }
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/affiliations/commissions.blade.php ENDPATH**/ ?>