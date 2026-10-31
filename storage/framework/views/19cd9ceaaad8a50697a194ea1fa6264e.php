<?php $__env->startSection('title', 'Rappels'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-bell"></i> Rappels</h5>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($evenements->isEmpty()): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-bell" style="font-size: 3rem;"></i>
        <p class="mt-2">Aucun événement à venir pour l'instant.</p>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Événement</th>
                        <th>Date</th>
                        <th class="text-center">Billets payés</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evenement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="ps-3 fw-medium"><?php echo e($evenement->titre); ?></td>
                        <td><?php echo e(\Carbon\Carbon::parse($evenement->date_event)->format('d/m/Y à H:i')); ?></td>
                        <td class="text-center"><?php echo e($evenement->tickets_count); ?></td>
                        <td class="text-end pe-3">
                            <form action="<?php echo e(route('rappels.envoyer')); ?>" method="POST" class="d-inline"
                                onsubmit="return confirm('Envoyer un rappel à tous les acheteurs de « <?php echo e($evenement->titre); ?> » ?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="evenement_id" value="<?php echo e($evenement->id); ?>">
                                <button type="submit" class="btn btn-sm text-white" style="background: #7c3aed;"
                                    <?php echo e($evenement->tickets_count === 0 ? 'disabled' : ''); ?>>
                                    <i class="bi bi-envelope"></i> Envoyer le rappel
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/rappels/index.blade.php ENDPATH**/ ?>