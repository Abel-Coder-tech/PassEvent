<?php $__env->startSection('title', 'Acheteurs - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Acheteurs'); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
<div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <form method="GET" action="<?php echo e(route('superadmin.acheteurs')); ?>" class="d-inline-flex align-items-center gap-2 flex-wrap">
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:#888; font-size:0.8rem; pointer-events:none;"></i>
            <input type="text" name="q" class="form-control form-control-sm ps-4" style="border-radius:10px; min-width:280px;" placeholder="Nom, email, WhatsApp, tel, référence" value="<?php echo e(request('q')); ?>" autocomplete="off">
        </div>
        <select name="evenement_id" class="form-select form-select-sm" style="border-radius:10px; width:auto; max-width:220px;">
            <option value="">Tous les événements</option>
            <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($ev->id); ?>" <?php echo e((string) request('evenement_id') === (string) $ev->id ? 'selected' : ''); ?>><?php echo e($ev->titre); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="statut" class="form-select form-select-sm" style="border-radius:10px; width:auto;">
            <option value="">Tous les statuts</option>
            <option value="payé" <?php echo e(request('statut') === 'payé' ? 'selected' : ''); ?>>Payé</option>
            <option value="en_attente" <?php echo e(request('statut') === 'en_attente' ? 'selected' : ''); ?>>En attente</option>
            <option value="échoué" <?php echo e(request('statut') === 'échoué' ? 'selected' : ''); ?>>Échoué</option>
            <option value="remboursé" <?php echo e(request('statut') === 'remboursé' ? 'selected' : ''); ?>>Remboursé</option>
        </select>
        <input type="date" name="debut" class="form-control form-control-sm" style="border-radius:10px; width:auto;" value="<?php echo e(request('debut')); ?>">
        <span class="text-muted">→</span>
        <input type="date" name="fin" class="form-control form-control-sm" style="border-radius:10px; width:auto;" value="<?php echo e(request('fin')); ?>">
        <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary">Filtrer</button>
        <?php if(request('q') || request('evenement_id') || request('debut') || request('fin') || request('statut')): ?>
        <a href="<?php echo e(route('superadmin.acheteurs')); ?>" class="sa-btn sa-btn-sm" style="background:#e74c3c;border:none;color:#fff;border-radius:6px;text-decoration:none;">Réinitialiser</a>
        <?php endif; ?>
    </form>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted" style="font-size:0.8rem;"><?php echo e($acheteurs->total()); ?> achat(s)</span>
        <a href="<?php echo e(route('superadmin.acheteurs.export', request()->query())); ?>" class="sa-btn sa-btn-sm" style="background:#27ae60;border:none;color:#fff;border-radius:6px;text-decoration:none;">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Export CSV
        </a>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-person-lines-fill me-2" style="color: var(--sa-primary);"></i>Contacts des acheteurs</span>
        <span class="text-muted ms-auto" style="font-size:0.8rem;">
            <i class="bi bi-info-circle me-1"></i>Nom, email et WhatsApp capturés à chaque achat (table ticket).
        </span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($acheteurs->isEmpty()): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-lines-fill" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Aucun acheteur pour le moment.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Acheteur</th>
                        <th>WhatsApp</th>
                        <th>Téléphone</th>
                        <th>Événement</th>
                        <th>Date achat</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Référence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $acheteurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $wa = preg_replace('/[^0-9]/', '', (string) ($t->whatsapp_acheteur ?? '')); ?>
                    <tr>
                        <td>
                            <strong><?php echo e($t->nom_acheteur ?? '—'); ?></strong>
                            <?php if($t->email_acheteur): ?><div class="text-muted" style="font-size:0.75rem;"><?php echo e($t->email_acheteur); ?></div><?php endif; ?>
                        </td>
                        <td>
                            <?php if($wa): ?>
                            <a href="https://wa.me/<?php echo e($wa); ?>" target="_blank" rel="noopener" style="text-decoration:none; color:#25D366;">
                                <i class="bi bi-whatsapp me-1"></i><?php echo e($t->whatsapp_acheteur); ?>

                            </a>
                            <?php else: ?> <span class="text-muted">—</span> <?php endif; ?>
                        </td>
                        <td style="font-size:0.8rem;"><?php echo e($t->telephone_paiement ?? $t->telephone_acheteur ?? '—'); ?></td>
                        <td><?php echo e($t->evenement->titre ?? '-'); ?></td>
                        <td class="text-nowrap" style="font-size:0.8rem;"><?php echo e($t->date_achat ? \Carbon\Carbon::parse($t->date_achat)->isoFormat('D MMM YYYY HH:mm') : '-'); ?></td>
                        <td><strong><?php if($t->montant > 0): ?><?php echo e(number_format($t->montant, 0, ',', ' ')); ?> F <?php else: ?> Gratuit <?php endif; ?></strong></td>
                        <td>
                            <?php if($t->statut_paiement === 'payé'): ?> <span class="sa-badge sa-badge-success">Payé</span>
                            <?php elseif($t->statut_paiement === 'échoué'): ?> <span class="sa-badge sa-badge-danger">Échoué</span>
                            <?php elseif($t->statut_paiement === 'remboursé'): ?> <span class="sa-badge sa-badge-warning">Remboursé</span>
                            <?php else: ?> <span class="sa-badge sa-badge-secondary"><?php echo e($t->statut_paiement); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-family:monospace;font-size:0.75rem;"><?php echo e($t->code_unique); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center"><?php echo e($acheteurs->links()); ?></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/acheteurs.blade.php ENDPATH**/ ?>