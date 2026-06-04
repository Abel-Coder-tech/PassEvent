<?php $__env->startSection('title', 'Messages - PassEvent'); ?>

<?php $__env->startSection('page-title', 'Messages de contact'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Messages</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
    <?php if($nonLus > 0): ?>
        <span class="badge bg-danger"><?php echo e($nonLus); ?> non lu<?php echo e($nonLus > 1 ? 's' : ''); ?></span>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-envelope me-2" style="color: var(--violet);"></i>Messages recus</h5>
            <div class="d-flex gap-2 align-items-center">
                <?php if($nonLus > 0): ?>
                    <span class="badge bg-danger"><?php echo e($nonLus); ?> non lu<?php echo e($nonLus > 1 ? 's' : ''); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel-card-body p-0">
            <?php if($messages->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th>Expediteur</th>
                                <th>Objet</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php if(!$msg->lu): ?>
                                            <span class="badge bg-primary">Nouveau</span>
                                        <?php elseif($msg->reponse_admin): ?>
                                            <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Repondu</span>
                                        <?php else: ?>
                                            <span class="status-badge status-termine">Lu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="font-size: 0.85rem;"><?php echo e($msg->nom_complet); ?></div>
                                        <small class="text-muted"><?php echo e($msg->email); ?></small>
                                    </td>
                                    <td><?php echo e(Str::limit($msg->objet, 50)); ?></td>
                                    <td>
                                        <div style="font-size: 0.82rem;"><?php echo e($msg->created_at->format('d/m/Y')); ?></div>
                                        <small class="text-muted"><?php echo e($msg->created_at->format('H:i')); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?php echo e(route('admin.messages.show', $msg->id)); ?>" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px; padding: 0.25rem 0.5rem;" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.messages.destroy', $msg->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce message ?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--danger); color: var(--danger); background: transparent;" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    <?php echo e($messages->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-envelope-open" style="font-size: 3rem; color: var(--gris);"></i>
                    <p class="text-muted mt-3 mb-0">Aucun message recu pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\admin\messages\index.blade.php ENDPATH**/ ?>