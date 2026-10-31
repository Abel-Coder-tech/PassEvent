<?php $__env->startSection('title', 'Campagnes marketing - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Campagnes marketing'); ?>

<?php $__env->startSection('content'); ?>
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-megaphone-fill me-2" style="color: var(--sa-primary);"></i>Campagnes marketing</span>
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($messages->total()); ?> demande(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Type</th><th>Expéditeur</th><th>Objet</th><th>Message</th><th>Lu</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr id="row-<?php echo e($msg->id); ?>" class="sa-row-click" data-href="<?php echo e(route('superadmin.campagnes-marketing.show', $msg)); ?>"
                    style="cursor:pointer; <?php echo e(!$msg->lu ? 'background:rgba(107,63,160,0.03);' : ''); ?>">
                    <td>
                        <span class="sa-badge" style="background:#7B3FA0;color:#fff;">Campagne</span>
                    </td>
                    <td>
                        <strong><?php echo e($msg->nom_complet); ?></strong><br><small style="font-size:0.7rem;"><?php echo e($msg->email); ?></small>
                        <?php if($msg->telephone): ?>
                            <br><small style="font-size:0.7rem;"><?php echo e($msg->telephone); ?></small>
                        <?php endif; ?>
                        <?php if($msg->evenement): ?>
                            <br><small style="font-size:0.7rem;color:var(--sa-primary);"><i class="bi bi-calendar-event me-1"></i><?php echo e($msg->evenement->titre); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($msg->objet); ?></td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($msg->message); ?></td>
                    <td id="lu-<?php echo e($msg->id); ?>">
                        <?php if($msg->lu): ?>
                            <span class="sa-badge sa-badge-success">Lu</span>
                        <?php else: ?>
                            <span class="sa-badge sa-badge-warning">Non lu</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.75rem;"><?php echo e($msg->created_at->isoFormat('D MMM YYYY HH:mm')); ?></td>
                    <td style="white-space:nowrap;">
                        <a href="<?php echo e(route('superadmin.campagnes-marketing.show', $msg)); ?>" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;" title="Voir">
                            <i class="bi bi-eye"></i>
                        </a>
                        <form action="<?php echo e(route('superadmin.notifications.supprimer', $msg)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette demande ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="sa-btn sa-btn-sm" style="background:transparent;border:1px solid #e74c3c;color:#e74c3c;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;" title="Supprimer">
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
<div class="mt-3 d-flex justify-content-center"><?php echo e($messages->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Toute la ligne est cliquable et ouvre la page détail (les liens/formulaires restent fonctionnels)
document.querySelectorAll('tr.sa-row-click').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form') || e.target.closest('input')) {
            return;
        }
        window.location.href = row.dataset.href;
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/campagnes.blade.php ENDPATH**/ ?>