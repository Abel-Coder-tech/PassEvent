<?php $__env->startSection('title', 'Tableau de bord affiliation - Super Admin PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Tableau de bord affiliation'); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div style="color:var(--sa-text-muted); font-size:0.85rem;">
        Vue globale des affiliés, des événements co-parrainés et des commissions de la plateforme.
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?php echo e(route('superadmin.affiliations.index')); ?>" class="sa-btn sa-btn-outline">
            <i class="bi bi-people-fill"></i> Liste des affiliés
        </a>
        <a href="<?php echo e(route('superadmin.affiliations.commissions')); ?>" class="sa-btn sa-btn-outline">
            <i class="bi bi-cash-stack"></i> Commissions
        </a>
        <a href="<?php echo e(route('superadmin.affiliations.reversements')); ?>" class="sa-btn sa-btn-primary">
            <i class="bi bi-cash-coin"></i> Reversements
            <?php if($stats['reversements_demandes'] > 0): ?>
                <span class="sa-badge sa-badge-warning ms-1"><?php echo e($stats['reversements_demandes']); ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(84,38,128,0.1); color: var(--sa-primary);"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['affilies'], 0, ',', ' ')); ?></div>
                <div class="kpi-label">Affiliés (<?php echo e($stats['affilies_actifs']); ?> actifs)</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e($stats['parrainages']); ?> filleul(s) · <?php echo e($stats['parrainages_valides']); ?> validé(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['evenements_coparaines'], 0, ',', ' ')); ?></div>
                <div class="kpi-label">Événements co-parrainés</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e($stats['evenements_ouverts']); ?> ouvert(s) au co-parrainage</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(155,89,182,0.1); color: #9b59b6;"><i class="bi bi-link-45deg"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['liens'], 0, ',', ' ')); ?></div>
                <div class="kpi-label">Liens de co-parrainage</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e($stats['liens_actifs']); ?> actif(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(26,188,156,0.1); color: #1abc9c;"><i class="bi bi-cursor-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['clics'], 0, ',', ' ')); ?></div>
                <div class="kpi-label">Clics générés</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['commissions_total'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Total gagné</div>
                <div class="small" style="color:var(--sa-text-muted);">
                    <?php echo e(number_format($stats['commissions_count'], 0, ',', ' ')); ?> commission(s) :
                    <?php echo e(number_format($stats['commissions_co'], 0, ',', ' ')); ?> F co-parr. ·
                    <?php echo e(number_format($stats['commissions_par'], 0, ',', ' ')); ?> F parr.
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['en_attente'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">En attente</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e(number_format($stats['en_attente_count'], 0, ',', ' ')); ?> commission(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['disponible'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Disponible (à reverser)</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e(number_format($stats['disponible_count'], 0, ',', ' ')); ?> commission(s)</div>
                <?php if($stats['reversements_demandes_montant'] > 0): ?>
                    <div class="small" style="color:var(--sa-text-muted);"><?php echo e(number_format($stats['reversements_demandes_montant'], 0, ',', ' ')); ?> F en demande</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card h-100">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e(number_format($stats['paye'], 0, ',', ' ')); ?> F</div>
                <div class="kpi-label">Commissions payées</div>
                <div class="small" style="color:var(--sa-text-muted);"><?php echo e(number_format($stats['paye_count'], 0, ',', ' ')); ?> commission(s) · <?php echo e(number_format($stats['reversements_payes'], 0, ',', ' ')); ?> F reversés</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-calendar-check me-2" style="color: var(--sa-primary);"></i>Événements co-parrainés</span>
        <span class="sa-topbar-badge"><?php echo e($parEvenement->count()); ?> événement(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($parEvenement->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Événement</th>
                            <th>Date</th>
                            <th class="text-center">Affiliés</th>
                            <th class="text-center">Liens</th>
                            <th class="text-center">Clics</th>
                            <th class="text-end">Commissions</th>
                            <th class="text-end">En attente</th>
                            <th class="text-end">Payé</th>
                            <th>Statut</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $parEvenement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ligne): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($evenement = $ligne['evenement']); ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:42px;height:42px;border-radius:8px;overflow:hidden;background:#f1f3f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <?php if($evenement->image): ?>
                                                <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="<?php echo e($evenement->titre); ?>" style="width:100%;height:100%;object-fit:cover;">
                                            <?php else: ?>
                                                <i class="bi bi-calendar-event" style="color:#adb5bd;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div style="min-width:0;">
                                            <strong class="d-block text-truncate" style="max-width:260px;"><?php echo e($evenement->titre); ?></strong>
                                            <small style="color:var(--sa-text-muted);"><?php echo e($evenement->user?->nom ?? 'Organisateur supprimé'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td style="white-space:nowrap;"><?php echo e($evenement->date_event?->format('d/m/Y') ?? '—'); ?></td>
                                <td class="text-center" title="<?php echo e($evenement->affiliateEventLinks->pluck('affiliate.nom_complet')->filter()->unique()->join(', ')); ?>">
                                    <?php echo e($ligne['affilies']); ?>

                                </td>
                                <td class="text-center">
                                    <?php echo e($ligne['liens']); ?>

                                    <br><small style="color:var(--sa-text-muted);"><?php echo e($ligne['liens_actifs']); ?> actif(s)</small>
                                </td>
                                <td class="text-center"><?php echo e(number_format($ligne['clics'], 0, ',', ' ')); ?></td>
                                <td class="text-end">
                                    <strong><?php echo e(number_format($ligne['commissions'], 0, ',', ' ')); ?> F</strong>
                                    <br><small style="color:var(--sa-text-muted);"><?php echo e($ligne['commissions_count']); ?> commission(s)</small>
                                </td>
                                <td class="text-end"><?php echo e(number_format($ligne['en_attente'], 0, ',', ' ')); ?> F</td>
                                <td class="text-end"><?php echo e(number_format($ligne['paye'], 0, ',', ' ')); ?> F</td>
                                <td>
                                    <?php if($evenement->statut === 'publié'): ?>
                                        <span class="sa-badge sa-badge-success">Publié</span>
                                    <?php elseif($evenement->statut === 'terminé'): ?>
                                        <span class="sa-badge sa-badge-secondary">Terminé</span>
                                    <?php elseif($evenement->statut === 'annulé'): ?>
                                        <span class="sa-badge sa-badge-danger">Annulé</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-warning">Brouillon</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('superadmin.evenements.voir', $evenement)); ?>" class="sa-btn sa-btn-sm sa-btn-outline" title="Voir l'événement">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                <p class="mt-2 mb-1">Aucun événement co-parrainé pour le moment.</p>
                <small>Les événements apparaîtront ici dès qu'un affilié aura généré un lien de co-parrainage.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-trophy me-2" style="color: var(--sa-primary);"></i>Affiliés les plus performants</span>
                <a href="<?php echo e(route('superadmin.affiliations.index')); ?>" class="sa-btn sa-btn-sm sa-btn-outline">Tout voir</a>
            </div>
            <div class="sa-card-body p-0">
                <?php if($topAffilies->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="sa-table mb-0">
                            <thead>
                                <tr>
                                    <th>Affilié</th>
                                    <th>Événements co-parrainés</th>
                                    <th class="text-center">Liens</th>
                                    <th class="text-end">Total gagné</th>
                                    <th class="text-end">Disponible</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topAffilies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $affilie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('superadmin.affiliations.show', $affilie)); ?>" class="text-decoration-none">
                                                <strong style="color:var(--sa-primary);"><?php echo e($affilie->nom_complet); ?></strong>
                                            </a>
                                            <br><small style="color:var(--sa-text-muted);"><?php echo e($affilie->email); ?></small>
                                        </td>
                                        <td>
                                            <?php ($titres = $affilie->eventLinks->pluck('evenement.titre')->filter()); ?>
                                            <?php if($titres->count() > 0): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php $__currentLoopData = $titres->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $titre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="sa-badge sa-badge-info" style="font-size:0.65rem;"><?php echo e(\Illuminate\Support\Str::limit($titre, 24)); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($titres->count() > 2): ?>
                                                        <span class="sa-badge sa-badge-secondary" style="font-size:0.65rem;">+<?php echo e($titres->count() - 2); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color:var(--sa-text-muted);">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo e($affilie->event_links_count); ?></td>
                                        <td class="text-end"><strong><?php echo e(number_format((float) $affilie->total_gagne, 0, ',', ' ')); ?> F</strong></td>
                                        <td class="text-end"><?php echo e(number_format((float) $affilie->solde_disponible, 0, ',', ' ')); ?> F</td>
                                        <td class="text-center">
                                            <?php if($affilie->actif): ?>
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
                    <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                        <i class="bi bi-person-plus" style="font-size:2rem;"></i>
                        <p class="mt-2 mb-0">Aucun affilié inscrit.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-cash-stack me-2" style="color: var(--sa-primary);"></i>Dernières commissions</span>
                <a href="<?php echo e(route('superadmin.affiliations.commissions')); ?>" class="sa-btn sa-btn-sm sa-btn-outline">Tout voir</a>
            </div>
            <div class="sa-card-body p-0">
                <?php if($dernieresCommissions->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="sa-table mb-0">
                            <thead>
                                <tr>
                                    <th>Affilié</th>
                                    <th>Événement</th>
                                    <th class="text-end">Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $dernieresCommissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($commission->affiliate?->nom_complet ?? '—'); ?></strong>
                                            <br><small style="color:var(--sa-text-muted);"><?php echo e($commission->created_at->format('d/m/Y')); ?></small>
                                        </td>
                                        <td class="text-truncate" style="max-width:150px;"><?php echo e($commission->evenement?->titre ?? '—'); ?></td>
                                        <td class="text-end"><strong><?php echo e(number_format((float) $commission->montant, 0, ',', ' ')); ?> F</strong></td>
                                        <td><?php echo $__env->make('superadmin.affiliations.partials.statut-commission', ['commission' => $commission], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center" style="color:var(--sa-text-muted);">
                        <i class="bi bi-cash-coin" style="font-size:2rem;"></i>
                        <p class="mt-2 mb-0">Aucune commission enregistrée.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/affiliations/dashboard.blade.php ENDPATH**/ ?>