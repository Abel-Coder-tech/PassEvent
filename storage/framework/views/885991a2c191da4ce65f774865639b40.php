<?php use Illuminate\Support\Str; ?>

<?php $__env->startSection('title', 'Co-parrainage'); ?>

<?php $__env->startSection('page-title', 'Co-parrainage'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Co-parrainage</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-2 mb-4">
        <div>
            <p class="text-muted mb-0" style="font-size:0.9rem;">Suivez vos événements ouverts au co-parrainage, les liens générés et les commissions de vos affiliés.</p>
        </div>
        <a href="<?php echo e(route('admin.evenements.index')); ?>" class="btn btn-secondary-custom">
            <i class="bi bi-calendar-event me-1"></i> Mes événements
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label">Affiliés actifs</div>
                        <div class="metric-value"><?php echo e(number_format($stats['affilies'], 0, ',', ' ')); ?></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(123,63,160,0.12);color:var(--violet);">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label">Liens actifs / total</div>
                        <div class="metric-value"><?php echo e(number_format($stats['liens_actifs'], 0, ',', ' ')); ?> <span class="text-muted" style="font-size:1rem;">/ <?php echo e(number_format($stats['liens'], 0, ',', ' ')); ?></span></div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(32,201,151,0.12);color:var(--teal);">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metric-card" style="border-top-color: var(--orange);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label">Clics / Ventes via liens</div>
                        <div class="metric-value"><?php echo e(number_format($stats['clics'], 0, ',', ' ')); ?> <span class="text-muted" style="font-size:1rem;">/ <?php echo e(number_format($stats['ventes'] ?? 0, 0, ',', ' ')); ?></span></div>
                        <div class="text-muted" style="font-size:0.78rem;">Taux de conversion: <?php echo e(($stats['clics'] ?? 0) > 0 ? round(($stats['ventes'] ?? 0)/$stats['clics']*100, 2) : 0); ?>%</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(255,159,28,0.14);color:#b66a00;">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="metric-label">Commissions dues</div>
                        <div class="metric-value" style="font-size:1.25rem;"><?php echo e(number_format($stats['commissions'], 0, ',', ' ')); ?> F</div>
                        <div class="text-muted" style="font-size:0.78rem;"><?php echo e(number_format($stats['commissions_en_attente'], 0, ',', ' ')); ?> F en attente</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(40,167,69,0.12);color:var(--vert);">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <div class="panel-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2" style="color:var(--violet);"></i> Événements ouverts au co-parrainage</h5>
            <span class="badge bg-light text-muted border"><?php echo e($parEvenement->count()); ?> événement<?php echo e($parEvenement->count() > 1 ? 's' : ''); ?></span>
        </div>
        <div class="panel-card-body p-0">
            <?php if($parEvenement->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 ps-4">Événement</th>
                                <th class="border-0">Date</th>
                                <th class="border-0 text-center">Liens</th>
                                <th class="border-0 text-center">Clics</th>
                                <th class="border-0 text-center">Ventes via liens</th>
                                <th class="border-0 text-end">Commissions totales</th>
                                <th class="border-0 text-end">En attente</th>
                                <th class="border-0">Statut</th>
                                <th class="border-0 text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $parEvenement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ligne): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($ev = $ligne['evenement']); ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(123,63,160,0.12);color:var(--violet);">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo e($ev->titre); ?></div>
                                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?php echo e(Str::limit($ev->lieu, 40)); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e($ev->date_event?->format('d/m/Y') ?? '—'); ?></td>
                                    <td class="text-center"><?php echo e(number_format($ligne['liens'], 0, ',', ' ')); ?></td>
                                    <td class="text-center"><?php echo e(number_format($ligne['clics'] ?? 0, 0, ',', ' ')); ?></td>
                                    <td class="text-center"><?php echo e(number_format($ligne['ventes'] ?? 0, 0, ',', ' ')); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e(number_format($ligne['commissions'], 0, ',', ' ')); ?> F</td>
                                    <td class="text-end"><?php echo e(number_format($ligne['en_attente'], 0, ',', ' ')); ?> F</td>
                                    <td>
                                        <?php if($ev->statut === 'publié'): ?>
                                            <span class="badge" style="background:rgba(40,167,69,0.12);color:var(--vert);">Publié</span>
                                        <?php elseif($ev->statut === 'terminé'): ?>
                                            <span class="badge bg-light text-muted border">Terminé</span>
                                        <?php elseif($ev->statut === 'annulé'): ?>
                                            <span class="badge" style="background:rgba(220,53,69,0.12);color:#dc3545;">Annulé</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Brouillon</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?php echo e(route('admin.evenements.show', $ev)); ?>#coparrainage" class="btn btn-sm" style="border:1px solid #e2e0e4;">
                                            <i class="bi bi-gear me-1"></i>Gérer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-share d-block mb-2" style="font-size:2.5rem;color:var(--gris);"></i>
                    <h6 class="fw-semibold mb-1">Aucun événement ouvert au co-parrainage</h6>
                    <p class="text-muted small mb-3">Activez le co-parrainage depuis la fiche d'un événement payant pour inviter des affiliés à le promouvoir.</p>
                    <a href="<?php echo e(route('admin.evenements.index')); ?>" class="btn btn-primary-custom"><i class="bi bi-arrow-right me-1"></i>Voir mes événements</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="panel-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2" style="color:var(--violet);"></i> Liens des affiliés</h5>
                    <span class="badge bg-light text-muted border"><?php echo e($liens->count()); ?></span>
                </div>
                <div class="panel-card-body p-0">
                    <?php if($liens->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 ps-4">Affilié</th>
                                        <th class="border-0">Événement</th>
                                        <th class="border-0 text-center">Taux</th>
                                        <th class="border-0 text-center">Clics</th>
                                        <th class="border-0 text-center">Ventes</th>
                                        <th class="border-0 pe-4">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $liens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lien): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold"><?php echo e($lien->affiliate?->nom_complet ?? '—'); ?></div>
                                                <div class="text-muted small"><?php echo e(Str::limit($lien->affiliate?->email, 28)); ?></div>
                                            </td>
                                            <td class="text-truncate" style="max-width:180px;"><?php echo e($lien->evenement?->titre ?? '—'); ?></td>
                                            <td class="text-center"><?php echo e(rtrim(rtrim(number_format((float) $lien->pourcentage, 2, ',', ' '), '0'), ',')); ?> %</td>
                                            <td class="text-center"><?php echo e(number_format($lien->clics, 0, ',', ' ')); ?></td>
                                            <td class="text-center"><?php echo e(number_format($ventesParLien[$lien->id] ?? 0, 0, ',', ' ')); ?></td>
                                            <td class="pe-4">
                                                <?php if($lien->actif): ?>
                                                    <span class="badge" style="background:rgba(40,167,69,0.12);color:var(--vert);">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-link-45deg d-block mb-2" style="font-size:2rem;color:var(--gris);"></i>
                            <p class="text-muted small mb-0">Aucun affilié n'a encore généré de lien.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="panel-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2" style="color:var(--violet);"></i> Dernières commissions</h5>
                    <span class="badge bg-light text-muted border"><?php echo e($commissions->count()); ?></span>
                </div>
                <div class="panel-card-body p-0">
                    <?php if($commissions->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 ps-4">Date</th>
                                        <th class="border-0">Affilié</th>
                                        <th class="border-0 text-end">Montant</th>
                                        <th class="border-0 pe-4">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="ps-4"><?php echo e($commission->created_at->format('d/m/Y')); ?></td>
                                            <td>
                                                <div class="fw-semibold"><?php echo e($commission->affiliate?->nom_complet ?? '—'); ?></div>
                                                <div class="text-muted small"><?php echo e(Str::limit($commission->affiliate?->email, 24)); ?></div>
                                            </td>
                                            <td class="text-end fw-semibold"><?php echo e(number_format((float) $commission->montant, 0, ',', ' ')); ?> F</td>
                                            <td class="pe-4">
                                                <?php if($commission->statut === 'en_attente'): ?>
                                                    <span class="badge" style="background:rgba(255,159,28,0.16);color:#b66a00;">En attente</span>
                                                <?php elseif($commission->statut === 'disponible'): ?>
                                                    <span class="badge" style="background:rgba(32,201,151,0.14);color:var(--teal);">Disponible</span>
                                                <?php elseif($commission->statut === 'paye'): ?>
                                                    <span class="badge" style="background:rgba(40,167,69,0.12);color:var(--vert);">Payée</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Annulée</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-cash-coin d-block mb-2" style="font-size:2rem;color:var(--gris);"></i>
                            <p class="text-muted small mb-0">Aucune commission de co-parrainage pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/affiliations/index.blade.php ENDPATH**/ ?>