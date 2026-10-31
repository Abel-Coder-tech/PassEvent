<?php $__env->startSection('title', 'Tarifs — ' . $evenement->titre); ?>

<?php $__env->startSection('page-title', 'Tarifs — ' . $evenement->titre); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Tableau de bord</a></li>
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
    <?php $evenementAVendu = $evenement->tarifs()->sum('quantite_vendue') > 0; ?>
    <?php if($evenementAVendu): ?>
        <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            Des billets ont déjà été vendus pour cet événement : le prix d'un tarif ne peut plus être modifié directement. Cliquez sur « Modifier » puis utilisez le bouton « Demander une modification » pour soumettre un nouveau prix à validation par PaxEvent.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Aucun billet vendu pour l'instant : vous pouvez créer et modifier librement les prix des tarifs.
        </div>
    <?php endif; ?>
    <div class="panel-card">
        <div class="card-body p-0">
        <?php if($tarifs->count() > 0): ?>
            <div class="table-responsive">
                <table class="table custom-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
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
                                <td class="fw-semibold"><?php echo e($tarif->nom); ?></td>
                                <td class="fw-semibold"><?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</td>
                                <td><?php echo e($tarif->quantite_disponible ?? 'Illimité'); ?></td>
                                <td><?php echo e($tarif->quantite_vendue); ?></td>
                                <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($tarif->statut)); ?></span></td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('admin.tarifs.edit', [$evenement->id, $tarif->id])); ?>" class="btn btn-sm btn-outline-secondary me-1" title="Modifier ce tarif (prix, quantité, ou demande de modification)">
                                        <i class="bi bi-pencil me-1"></i> Modifier
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/tarifs/index.blade.php ENDPATH**/ ?>