<?php $__env->startSection('title', 'Reversements affiliation - Super Admin PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Reversements d\'affiliation'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['demande']); ?></div>
                <div class="kpi-label">Demandes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-arrow-repeat"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['en_cours']); ?></div>
                <div class="kpi-label">En cours</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['paye'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Payé</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-receipt"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['total']); ?></div>
                <div class="kpi-label">Reversements payés</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <form method="GET" action="<?php echo e(route('superadmin.affiliations.reversements')); ?>" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="text" name="q" value="<?php echo e($recherche); ?>" class="sa-form-control" placeholder="Affilié ou numéro..." style="min-width:220px;">
                <select name="statut" class="sa-form-control" style="min-width:160px;">
                    <option value="">Tous les statuts</option>
                    <option value="demande" <?php if($statut === 'demande'): echo 'selected'; endif; ?>>Demandé</option>
                    <option value="en_cours" <?php if($statut === 'en_cours'): echo 'selected'; endif; ?>>En cours</option>
                    <option value="paye" <?php if($statut === 'paye'): echo 'selected'; endif; ?>>Payé</option>
                    <option value="refuse" <?php if($statut === 'refuse'): echo 'selected'; endif; ?>>Refusé</option>
                </select>
                <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-search"></i> Filtrer</button>
                <?php if($recherche !== '' || $statut): ?>
                    <a href="<?php echo e(route('superadmin.affiliations.reversements')); ?>" class="sa-btn sa-btn-outline">Réinitialiser</a>
                <?php endif; ?>
            </form>
            <a href="<?php echo e(route('superadmin.affiliations.index')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-arrow-left"></i> Affiliés</a>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-primary);"></i>Demandes de reversement</span>
        <span class="sa-topbar-badge"><?php echo e($reversements->total()); ?> demande(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($reversements->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Affilié</th>
                            <th class="text-end">Montant</th>
                            <th>Opérateur / numéro</th>
                            <th class="text-center">Commissions</th>
                            <th>Statut</th>
                            <th>Note</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $reversements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reversement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo e($reversement->created_at->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <?php if($reversement->affiliate): ?>
                                        <a href="<?php echo e(route('superadmin.affiliations.show', $reversement->affiliate)); ?>" style="color:var(--sa-primary);text-decoration:none;">
                                            <strong><?php echo e($reversement->affiliate->nom_complet); ?></strong>
                                        </a>
                                        <br><small style="color:var(--sa-text-muted);"><?php echo e($reversement->affiliate->email); ?></small>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><strong><?php echo e(number_format((float) $reversement->montant, 0, ',', ' ')); ?> F</strong></td>
                                <td>
                                    <?php echo e($reversement->operateur ?: '—'); ?>

                                    <br><small style="color:var(--sa-text-muted);"><?php echo e($reversement->numero_paiement ?: '—'); ?></small>
                                    <?php if($reversement->titulaire): ?>
                                        <br><small style="color:var(--sa-text-muted);"><?php echo e($reversement->titulaire); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo e($reversement->commissions_count); ?></td>
                                <td>
                                    <?php if($reversement->statut === 'demande'): ?>
                                        <span class="sa-badge sa-badge-warning">Demandé</span>
                                    <?php elseif($reversement->statut === 'en_cours'): ?>
                                        <span class="sa-badge sa-badge-info">En cours</span>
                                    <?php elseif($reversement->statut === 'paye'): ?>
                                        <span class="sa-badge sa-badge-success">Payé</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-danger">Refusé</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:180px;font-size:0.78rem;"><?php echo e($reversement->note ?: '—'); ?></td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <?php if($reversement->statut === 'demande'): ?>
                                        <form action="<?php echo e(route('superadmin.affiliations.reversements.approuver', $reversement)); ?>" method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary" title="Approuver">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if(in_array($reversement->statut, ['demande', 'en_cours'], true)): ?>
                                        <?php if($reversement->statut === 'en_cours'): ?>
                                            <form action="<?php echo e(route('superadmin.affiliations.reversements.confirmer', $reversement)); ?>" method="POST" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary" title="Confirmer le paiement"
                                                    onclick="return confirm('Confirmer que le transfert a bien été effectué ?')">
                                                    <i class="bi bi-check-all"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?php echo e(route('superadmin.affiliations.reversements.rejeter', $reversement)); ?>" method="POST" style="display:inline;"
                                            onsubmit="this.note.value = prompt('Motif du rejet (facultatif)') || '';">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="note" value="">
                                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Rejeter">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3"><?php echo e($reversements->links()); ?></div>
        <?php else: ?>
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-cash-coin" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">Aucune demande de reversement.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/affiliations/reversements.blade.php ENDPATH**/ ?>