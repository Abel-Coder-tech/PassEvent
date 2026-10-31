<?php $__env->startSection('title', 'Agents de vente'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0"><i class="bi bi-people"></i> Agents de vente</h5>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm text-white" style="background: #7B3FA0;" onclick="openDemande('augmentation_agents')">
                <i class="bi bi-plus-circle"></i> Demander plus d'agents
            </button>
            <a href="<?php echo e(route('admin.agents-vente.create')); ?>" class="btn btn-sm text-white" style="background: #7c3aed;">
                <i class="bi bi-plus-lg"></i> Nouvel agent
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($agents->isEmpty()): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-people" style="font-size: 3rem;"></i>
        <p class="mt-2">Aucun agent de vente pour le moment.</p>
        <a href="<?php echo e(route('admin.agents-vente.create')); ?>" class="btn btn-sm text-white" style="background: #7c3aed;">
            Créer un agent
        </a>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Agent</th>
                        <th>Email</th>
                        <th>Événement</th>
                        <th class="text-center">Tickets</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="ps-3 fw-medium"><?php echo e($agent->nom); ?></td>
                        <td><?php echo e($agent->email); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.evenements.show', $agent->evenement)); ?>"
                                class="text-decoration-none"><?php echo e($agent->evenement->titre); ?></a>
                        </td>
                        <td class="text-center"><?php echo e($agent->tickets_count); ?></td>
                        <td class="text-end"><?php echo e(number_format($agent->montant_total, 0, ',', ' ')); ?> F</td>
                        <td class="text-center">
                            <span class="badge bg-<?php echo e($agent->actif ? 'success' : 'secondary'); ?>">
                                <?php echo e($agent->actif ? 'Actif' : 'Inactif'); ?>

                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="<?php echo e(route('admin.agents-vente.show', $agent)); ?>"
                                class="btn btn-sm btn-outline-secondary py-0 px-2" title="Voir détails">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="<?php echo e(route('admin.agents-vente.toggle-actif', $agent)); ?>"
                                method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-outline-<?php echo e($agent->actif ? 'warning' : 'success'); ?> py-0 px-2"
                                    title="<?php echo e($agent->actif ? 'Désactiver' : 'Activer'); ?>">
                                    <i class="bi bi-<?php echo e($agent->actif ? 'pause' : 'play'); ?>"></i>
                                </button>
                            </form>
                            <form action="<?php echo e(route('admin.agents-vente.destroy', $agent)); ?>"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('Supprimer cet agent définitivement ?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?php echo e($agents->links('vendor.pagination.pass', ['label' => 'agent(s) de vente'])); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/agents-vente/index.blade.php ENDPATH**/ ?>