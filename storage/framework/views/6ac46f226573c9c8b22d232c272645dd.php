<?php $__env->startSection('title', 'Affilié - Super Admin PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Fiche affilié'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h5 class="mb-1"><?php echo e($affiliate->nom_complet); ?></h5>
        <div style="color:var(--sa-text-muted); font-size:0.85rem;">
            <?php echo e($affiliate->email); ?>

            <?php if($affiliate->telephone): ?> · <?php echo e($affiliate->telephone); ?> <?php endif; ?>
            · Code : <code><?php echo e($affiliate->code_parrainage); ?></code>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('superadmin.affiliations.index')); ?>" class="sa-btn sa-btn-outline"><i class="bi bi-arrow-left"></i> Retour</a>
        <form action="<?php echo e(route('superadmin.affiliations.statut', $affiliate)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="sa-btn <?php echo e($affiliate->actif ? 'sa-btn-danger' : 'sa-btn-primary'); ?>"
                onclick="return confirm('<?php echo e($affiliate->actif ? 'Désactiver' : 'Réactiver'); ?> ce compte ?')">
                <i class="bi <?php echo e($affiliate->actif ? 'bi-pause-fill' : 'bi-play-fill'); ?>"></i>
                <?php echo e($affiliate->actif ? 'Désactiver' : 'Réactiver'); ?>

            </button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format((float) $affiliate->solde_en_attente, 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format((float) $affiliate->solde_disponible, 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Disponible</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format((float) $affiliate->total_gagne, 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Total gagné</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($evenementsCoparaines); ?></div>
                <div class="kpi-label">Événements co-parrainés</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e($affiliate->event_links_count); ?> lien(s) · <?php echo e($affiliate->referrals_count); ?> filleul(s)</div>
            </div>
        </div>
    </div>
</div>


<div class="sa-card mb-4">
    <div class="sa-card-header"><span><i class="bi bi-bank me-2" style="color:var(--sa-primary);"></i>Coordonnées de reversement</span></div>
    <div class="sa-card-body">
        <div class="row g-3" style="font-size:0.85rem;">
            <div class="col-md-4"><strong>Opérateur :</strong> <?php echo e($affiliate->operateur_paiement ?: '—'); ?></div>
            <div class="col-md-4"><strong>Numéro :</strong> <?php echo e($affiliate->numero_paiement ?: '—'); ?></div>
            <div class="col-md-4"><strong>Titulaire :</strong> <?php echo e($affiliate->titulaire_paiement ?: '—'); ?></div>
            <div class="col-md-4"><strong>Pays :</strong> <?php echo e($affiliate->pays); ?></div>
            <div class="col-md-4"><strong>Inscrit le :</strong> <?php echo e($affiliate->created_at->format('d/m/Y')); ?></div>
            <div class="col-md-4"><strong>Dernier accès :</strong> <?php echo e($affiliate->dernier_acces?->format('d/m/Y H:i') ?? '—'); ?></div>
        </div>
    </div>
</div>


<div class="sa-card mb-4">
    <div class="sa-card-header"><span><i class="bi bi-person-lines-fill me-2" style="color:var(--sa-primary);"></i>Filleuls</span></div>
    <div class="sa-card-body p-0">
        <?php if($referrals->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead><tr><th>Organisateur</th><th>1er événement</th><th>Statut</th><th>Validé le</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($referral->organisateur?->nom ?? 'Organisateur supprimé'); ?></strong>
                                    <br><small style="color:var(--sa-text-muted);"><?php echo e($referral->organisateur?->email); ?></small>
                                </td>
                                <td><?php echo e($referral->premierEvenement?->titre ?? '—'); ?></td>
                                <td>
                                    <?php if($referral->statut === 'valide'): ?>
                                        <span class="sa-badge sa-badge-success">Validé</span>
                                    <?php elseif($referral->statut === 'annule'): ?>
                                        <span class="sa-badge sa-badge-danger">Annulé</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($referral->valide_le?->format('d/m/Y') ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-3" style="color:var(--sa-text-muted);">Aucun filleul.</div>
        <?php endif; ?>
    </div>
</div>


<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-calendar-check me-2" style="color:var(--sa-primary);"></i>Événements co-parrainés</span>
        <span class="sa-topbar-badge"><?php echo e($evenementsCoparaines); ?> événement(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($liens->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Événement</th>
                            <th>% commission</th>
                            <th class="text-center">Clics</th>
                            <th class="text-end">Commissions</th>
                            <th class="text-end">En attente</th>
                            <th class="text-end">Payé</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $liens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lien): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($statsEvenement = $commissionsParEvenement->get($lien->evenement_id)); ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:40px;height:40px;border-radius:8px;overflow:hidden;background:#f1f3f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <?php if($lien->evenement?->image): ?>
                                                <img src="<?php echo e(asset('storage/' . $lien->evenement->image)); ?>" alt="<?php echo e($lien->evenement->titre); ?>" style="width:100%;height:100%;object-fit:cover;">
                                            <?php else: ?>
                                                <i class="bi bi-calendar-event" style="color:#adb5bd;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div style="min-width:0;">
                                            <?php if($lien->evenement): ?>
                                                <a href="<?php echo e(route('superadmin.evenements.voir', $lien->evenement)); ?>" class="text-decoration-none">
                                                    <strong style="color:var(--sa-primary);"><?php echo e($lien->evenement->titre); ?></strong>
                                                </a>
                                                <br><small style="color:var(--sa-text-muted);"><?php echo e($lien->evenement->date_event?->format('d/m/Y') ?? '—'); ?></small>
                                            <?php else: ?>
                                                <span style="color:var(--sa-text-muted);">Événement supprimé</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e(rtrim(rtrim(number_format((float) $lien->pourcentage, 2, ',', ' '), '0'), ',')); ?> %</td>
                                <td class="text-center"><?php echo e($lien->clics); ?></td>
                                <td class="text-end">
                                    <strong><?php echo e(number_format((float) ($statsEvenement->commissions_total ?? 0), 0, ',', ' ')); ?> F</strong>
                                    <br><small style="color:var(--sa-text-muted);"><?php echo e((int) ($statsEvenement->commissions_count ?? 0)); ?> commission(s)</small>
                                </td>
                                <td class="text-end"><?php echo e(number_format((float) ($statsEvenement->commissions_en_attente ?? 0), 0, ',', ' ')); ?> F</td>
                                <td class="text-end"><?php echo e(number_format((float) ($statsEvenement->commissions_payees ?? 0), 0, ',', ' ')); ?> F</td>
                                <td>
                                    <?php if($lien->actif): ?>
                                        <span class="sa-badge sa-badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-3" style="color:var(--sa-text-muted);">Aucun événement co-parrainé.</div>
        <?php endif; ?>
    </div>
</div>


<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-stack me-2" style="color:var(--sa-primary);"></i>Commissions</span>
        <a href="<?php echo e(route('superadmin.affiliations.commissions', ['q' => $affiliate->email])); ?>" class="sa-btn sa-btn-sm sa-btn-outline">Tout voir</a>
    </div>
    <div class="sa-card-body p-0">
        <?php if($commissions->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Événement</th><th>Base</th><th>%</th><th class="text-end">Montant</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo e($commission->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <?php if($commission->type === \App\Models\AffiliateCommission::TYPE_PARRAINAGE): ?>
                                        <span class="sa-badge" style="background:rgba(84,38,128,0.1);color:var(--sa-primary);">Parrainage</span>
                                    <?php else: ?>
                                        <span class="sa-badge" style="background:rgba(52,152,219,0.1);color:#3498db;">Co-parrainage</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($commission->evenement?->titre ?? '—'); ?></td>
                                <td><?php echo e(number_format((float) $commission->base_montant, 0, ',', ' ')); ?> F</td>
                                <td><?php echo e(rtrim(rtrim(number_format((float) $commission->pourcentage, 2, ',', ' '), '0'), ',')); ?> %</td>
                                <td class="text-end"><strong><?php echo e(number_format((float) $commission->montant, 0, ',', ' ')); ?> F</strong></td>
                                <td>
                                    <?php echo $__env->make('superadmin.affiliations.partials.statut-commission', ['commission' => $commission], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3"><?php echo e($commissions->links()); ?></div>
        <?php else: ?>
            <div class="p-3" style="color:var(--sa-text-muted);">Aucune commission.</div>
        <?php endif; ?>
    </div>
</div>


<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-cash-coin me-2" style="color:var(--sa-primary);"></i>Reversements</span>
        <a href="<?php echo e(route('superadmin.affiliations.reversements', ['q' => $affiliate->email])); ?>" class="sa-btn sa-btn-sm sa-btn-outline">Tout voir</a>
    </div>
    <div class="sa-card-body p-0">
        <?php if($payouts->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead><tr><th>Date</th><th class="text-end">Montant</th><th>Opérateur</th><th>Commissions</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo e($payout->created_at->format('d/m/Y')); ?></td>
                                <td class="text-end"><strong><?php echo e(number_format((float) $payout->montant, 0, ',', ' ')); ?> F</strong></td>
                                <td><?php echo e($payout->operateur ?: '—'); ?></td>
                                <td><?php echo e($payout->commissions_count); ?></td>
                                <td>
                                    <?php if($payout->statut === 'demande'): ?>
                                        <span class="sa-badge sa-badge-warning">Demandé</span>
                                    <?php elseif($payout->statut === 'en_cours'): ?>
                                        <span class="sa-badge sa-badge-info">En cours</span>
                                    <?php elseif($payout->statut === 'paye'): ?>
                                        <span class="sa-badge sa-badge-success">Payé</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-danger">Refusé</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-3" style="color:var(--sa-text-muted);">Aucune demande de reversement.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/affiliations/show.blade.php ENDPATH**/ ?>