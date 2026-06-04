<?php $__env->startSection('title', 'Message - ' . $message->objet); ?>

<?php $__env->startSection('page-title', 'Details du message'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.messages.index')); ?>">Messages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Détail</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Message details -->
            <div class="panel-card mb-4">
                <div class="panel-card-header">
                    <h5><i class="bi bi-envelope me-2" style="color: var(--violet);"></i><?php echo e($message->objet); ?></h5>
                    <?php if(!$message->lu): ?>
                        <span class="badge bg-primary">Nouveau</span>
                    <?php elseif($message->reponse_admin): ?>
                        <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Repondu</span>
                    <?php else: ?>
                        <span class="status-badge status-termine">Lu</span>
                    <?php endif; ?>
                </div>
                <div class="panel-card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">EXPEDITEUR</label>
                                <div class="fw-bold"><?php echo e($message->nom_complet); ?></div>
                                <a href="mailto:<?php echo e($message->email); ?>" class="text-decoration-none" style="color: var(--violet); font-size: 0.85rem;"><?php echo e($message->email); ?></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">DATE D'ENVOI</label>
                                <div class="fw-bold"><?php echo e($message->created_at->format('d/m/Y a H:i')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">MESSAGE</label>
                        <div class="p-3 rounded" style="background: var(--blanc-casse); font-size: 0.9rem; line-height: 1.7; white-space: pre-wrap;"><?php echo e($message->message); ?></div>
                    </div>

                    <?php if($message->reponse_admin): ?>
                        <div class="mb-4">
                            <label class="text-muted" style="font-size: 0.78rem; font-weight: 600;">REPONSE (<?php echo e($message->date_reponse?->format('d/m/Y H:i')); ?>)</label>
                            <div class="p-3 rounded" style="background: rgba(18,151,110,0.08); font-size: 0.9rem; line-height: 1.7; white-space: pre-wrap;"><?php echo e($message->reponse_admin); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 justify-content-between">
                        <a href="<?php echo e(route('admin.messages.index')); ?>" class="btn btn-secondary-custom" style="border-radius: 8px;">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <form action="<?php echo e(route('admin.messages.destroy', $message->id)); ?>" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-outline-rouge" style="border-radius: 8px;">
                                <i class="bi bi-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reply form -->
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-reply me-2" style="color: var(--vert);"></i>Repondre</h5>
                </div>
                <div class="panel-card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success" style="background: rgba(18,151,110,0.08); color: var(--vert); border-radius: 8px;">
                            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('admin.messages.repondre', $message->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="reponse_admin" class="form-label fw-semibold">Votre reponse</label>
                            <textarea class="form-control" id="reponse_admin" name="reponse_admin" rows="6" placeholder="Redigez votre reponse ici..."><?php echo e(old('reponse_admin')); ?></textarea>
                            <?php $__errorArgs = ['reponse_admin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger mt-1" style="font-size: 0.82rem;"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">La reponse sera enregistree dans le systeme et visible dans le dashboard.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="mailto:<?php echo e($message->email); ?>" class="btn btn-vert" style="border-radius: 8px;">
                                <i class="bi bi-envelope me-1"></i> Envoyer par email
                            </a>
                            <button type="submit" class="btn btn-outline-vert" style="border-radius: 8px;">
                                <i class="bi bi-save me-1"></i> Enregistrer la reponse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\admin\messages\show.blade.php ENDPATH**/ ?>