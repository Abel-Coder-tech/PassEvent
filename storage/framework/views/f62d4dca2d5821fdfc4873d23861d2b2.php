<?php $__env->startSection('title', 'Événements - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Evenements'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-calendar-event-fill me-2" style="color: var(--sa-primary);"></i>Tous les evenements</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($evenements->total()); ?> total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Evenement</th>
                    <th>Organisateur</th>
                    <th>Statut</th>
                    <th>Places vendues</th>
                    <th>Recettes</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><a href="<?php echo e(route('superadmin.evenements.voir', $ev)); ?>" class="text-decoration-none" style="color: var(--sa-primary);"><?php echo e($ev->titre); ?></a></strong></td>
                    <td><?php echo e($ev->user->nom ?? '-'); ?></td>
                    <td>
                        <?php $statut = $ev->statutEffectif(); ?>
                        <?php if($statut === 'passé'): ?> <span class="sa-badge sa-badge-passed">Passé</span>
                        <?php elseif($statut === 'publié'): ?> <span class="sa-badge sa-badge-success">Publie</span>
                        <?php elseif($statut === 'brouillon'): ?> <span class="sa-badge sa-badge-secondary">Brouillon</span>
                        <?php elseif($statut === 'annulé'): ?> <span class="sa-badge sa-badge-danger">Annule</span>
                        <?php else: ?> <span class="sa-badge sa-badge-warning"><?php echo e($statut); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($ev->tickets_vendus ?? 0); ?> / <?php echo e($ev->capacite); ?></td>
                    <td><?php echo e(number_format($ev->recettes ?? 0, 0, ',', ' ')); ?> F</td>
                    <td style="font-size:0.75rem;"><?php echo e($ev->date_event->isoFormat('D MMM YYYY')); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <form action="<?php echo e(route('superadmin.evenements.masquer', $ev)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Masquer"><i class="bi bi-eye-slash"></i></button>
                            </form>
                            <form action="<?php echo e(route('superadmin.evenements.suspendre', $ev)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Suspendre <?php echo e($ev->titre); ?> ?')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Suspendre"><i class="bi bi-pause-fill"></i></button>
                            </form>
                            <form action="<?php echo e(route('superadmin.evenements.supprimer', $ev)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer definitivement <?php echo e($ev->titre); ?> ? Cette action est irreversible.')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash-fill"></i></button>
                            </form>
                            <?php if($ev->statut !== 'publié'): ?>
                                <form action="<?php echo e(route('superadmin.evenements.mettre-en-avant', $ev)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-success);color:#fff;border:none;" title="Publier"><i class="bi bi-check-lg"></i></button>
                                </form>
                            <?php endif; ?>
                            <?php if($ev->statut === 'publié' && $ev->date_event >= now()): ?>
                                <form action="<?php echo e(route('superadmin.evenements.a-la-une', $ev)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="sa-btn sa-btn-sm <?php echo e($ev->a_la_une ? 'sa-btn-danger' : 'sa-btn-outline'); ?>" title="<?php echo e($ev->a_la_une ? 'Retirer de la une' : 'Mettre à la une'); ?>">
                                        <i class="bi bi-<?php echo e($ev->a_la_une ? 'star-fill' : 'star'); ?>"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center"><?php echo e($evenements->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/evenements.blade.php ENDPATH**/ ?>