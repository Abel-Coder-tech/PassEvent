<?php $__env->startSection('title', 'Tarifs — ' . $evenement->titre); ?>

<?php $__env->startSection('page-title', 'Tarifs — ' . $evenement->titre); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.show', $evenement->id)); ?>"><?php echo e($evenement->titre); ?></a></li>
    <li class="breadcrumb-item active" aria-current="page">Tarifs</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('admin.tarifs.create', $evenement->id)); ?>" class="btn btn-vert btn-sm me-2">
    <i class="bi bi-plus-lg me-1"></i> <span class="btn-text">Ajouter un tarif</span>
</a>
<a href="<?php echo e(route('admin.evenements.show', $evenement->id)); ?>" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> <span class="btn-text">Retour</span>
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="panel-card">
        <div class="card-body p-0">
        <?php if($tarifs->count() > 0): ?>
            <div class="table-responsive">
                <table class="table custom-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Type</th>
                            <th>Prix</th>
                            <th>Quantité disponible</th>
                            <th>Vendus</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $badgeClass = match($tarif->statut) {
                                    'actif' => 'badge-publie',
                                    'épuisé' => 'badge-annule',
                                    'désactivé' => 'badge-brouillon',
                                };
                            ?>
                            <tr>
                                <td>
                                    <?php if($tarif->categorie === 'etudiant'): ?>
                                        <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet);">Étudiant</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(66,140,121,0.1); color: var(--teal);">Externe</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($tarif->type === 'vip'): ?>
                                        <span class="badge" style="background: rgba(135,66,139,0.1); color: var(--violet);">VIP</span>
                                    <?php else: ?>
                                        Normal
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold"><?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</td>
                                <td><?php echo e($tarif->quantite_disponible ?? 'Illimité'); ?></td>
                                <td><?php echo e($tarif->quantite_vendue); ?></td>
                                <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($tarif->statut)); ?></span></td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('admin.tarifs.edit', [$evenement->id, $tarif->id])); ?>" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.tarifs.destroy', [$evenement->id, $tarif->id])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce tarif ?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-tag d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                <h5>Aucun tarif configuré</h5>
                <p>Commencez par ajouter des tarifs pour cet événement.</p>
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\tarifs\index.blade.php ENDPATH**/ ?>