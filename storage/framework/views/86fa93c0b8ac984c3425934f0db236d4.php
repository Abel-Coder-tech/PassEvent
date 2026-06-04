<?php $__env->startSection('title', $evenement->titre); ?>

<?php $__env->startSection('page-title', $evenement->titre); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo e($evenement->titre); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('admin.tarifs.index', $evenement->id)); ?>" class="btn btn-secondary-custom me-2">
    <i class="bi bi-tag me-1"></i> Gérer les tarifs
</a>
<a href="<?php echo e(route('admin.evenements.edit', $evenement->id)); ?>" class="btn btn-secondary-custom me-2">
    <i class="bi bi-pencil me-1"></i> Modifier
</a>
<a href="<?php echo e(route('admin.evenements.index')); ?>" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Stats -->
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--vert);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Tickets vendus</div>
                    <div class="metric-value"><?php echo e($ventes); ?></div>
                </div>
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);">🎫</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--teal);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Revenus</div>
                    <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($revenus, 0, ',', ' ')); ?> F</div>
                </div>
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);">💰</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--menthe);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Places restantes</div>
                    <div class="metric-value"><?php echo e($placesRestantes); ?></div>
                </div>
                <div class="metric-icon" style="background: rgba(178,224,214,0.2);">👥</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--violet);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Remplissage</div>
                    <div class="metric-value"><?php echo e(number_format($tauxRemplissage, 0)); ?>%</div>
                </div>
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">📊</div>
            </div>
        </div>
    </div>
</div>

<!-- Détails -->
<div class="row">
    <div class="col-md-8">
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5>Détails de l'événement</h5>
            </div>
            <div class="panel-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Date :</strong> <?php echo e($evenement->date_event->format('d/m/Y à H:i')); ?>

                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Lieu :</strong> <?php echo e($evenement->lieu); ?>

                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Capacité :</strong> <?php echo e($evenement->capacite); ?>

                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Statut :</strong>
                        <?php
                            $badgeClass = match($evenement->statut) {
                                'publié' => 'badge-publie',
                                'brouillon' => 'status-brouillon',
                                'terminé' => 'status-termine',
                                'annulé' => 'badge bg-danger',
                                default => 'status-brouillon',
                            };
                        ?>
                        <span class="status-badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($evenement->statut)); ?></span>
                    </div>
                    <?php if($evenement->date_fin_vente): ?>
                        <div class="col-md-6 mb-3">
                            <strong>Fin de vente :</strong> <?php echo e($evenement->date_fin_vente->format('d/m/Y à H:i')); ?>

                        </div>
                    <?php endif; ?>
                </div>
                <?php if($evenement->description): ?>
                    <div class="mt-3">
                        <strong>Description :</strong>
                        <p class="text-muted mt-1"><?php echo e($evenement->description); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-tag me-2" style="color: var(--violet);"></i>Tarifs</h5>
            </div>
            <div class="panel-card-body p-0">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="font-size: 0.75rem;">Catégorie</th>
                            <th style="font-size: 0.75rem;">Prix</th>
                            <th style="font-size: 0.75rem;">Dispo.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="font-size: 0.82rem;">
                                    <?php echo e(ucfirst($tarif->categorie)); ?> / <?php echo e($tarif->type === 'normal' ? 'Standard' : 'VIP'); ?>

                                    <?php if($tarif->categorie === 'etudiant'): ?>
                                        <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-size: 0.6rem; vertical-align: middle;">Étudiant</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.82rem;"><?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</td>
                                <td style="font-size: 0.82rem;"><?php echo e($tarif->quantite_disponible - $tarif->quantite_vendue); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-shield-lock me-2" style="color: var(--vert);"></i>Codes d'accès scan</h5>
            </div>
            <div class="panel-card-body">
                <?php if($scanAccessCodes->count() > 0): ?>
                    <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                        <?php $__currentLoopData = $scanAccessCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sac): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <i class="bi bi-key" style="color: <?php echo e($sac->actif ? 'var(--vert)' : '#ccc'); ?>; font-size: 0.9rem;"></i>
                                    <code style="font-size: 0.82rem; letter-spacing: 1px;"><?php echo e($sac->code); ?></code>
                                    <?php if(!$sac->actif): ?>
                                        <span class="badge bg-secondary" style="font-size: 0.6rem;">Inactif</span>
                                    <?php endif; ?>
                                </div>
                                <form action="<?php echo e(route('admin.evenements.scan-codes.destroy', [$evenement->id, $sac->id])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce code ? Les agents utilisant ce code perdront l\'accès au scan.')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm p-1" style="color: #ccc;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3" style="font-size: 0.82rem;">Aucun code d'accès généré. Générez un code pour permettre à vos agents de scanner les tickets.</p>
                <?php endif; ?>

                <form action="<?php echo e(route('admin.evenements.scan-codes.generate', $evenement->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-vert btn-sm w-100" style="border-radius: 8px;">
                        <i class="bi bi-plus-lg me-1"></i> Générer un code d'accès
                    </button>
                </form>
            </div>
        </div>

        <?php if($evenement->image): ?>
            <div class="panel-card">
                <div class="panel-card-body p-0">
                    <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" class="img-fluid rounded-bottom" alt="<?php echo e($evenement->titre); ?>">
                </div>
            </div>
        <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\evenements\show.blade.php ENDPATH**/ ?>