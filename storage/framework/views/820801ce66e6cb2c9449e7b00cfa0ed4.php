<div class="empty-state">
    <i class="bi <?php echo e($icon ?? 'bi-inbox'); ?>"></i>
    <p class="empty-title"><?php echo e($title ?? 'Rien à afficher'); ?></p>
    <p class="text-muted small mb-0"><?php echo e($text ?? ''); ?></p>
    <?php if(isset($action)): ?>
        <a href="<?php echo e($action); ?>" class="btn btn-violet btn-sm mt-3"><?php echo e($actionLabel ?? 'Continuer'); ?></a>
    <?php endif; ?>
</div><?php /**PATH /home/abel/Projects/PassEvent/resources/views/affiliate/partials/empty-state.blade.php ENDPATH**/ ?>