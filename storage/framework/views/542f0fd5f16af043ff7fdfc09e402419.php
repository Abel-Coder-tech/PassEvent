<?php $__env->startSection('title', 'Remboursements - PassEvent'); ?>

<?php $__env->startSection('page-title', 'Remboursements'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Remboursements</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    
    <div class="alert mb-4 d-flex align-items-start" style="background: rgba(243,156,18,0.06); border-left: 4px solid #f39c12; border-radius: 8px; padding: 1rem 1.25rem;">
        <i class="bi bi-info-circle me-2 mt-1" style="color: #f39c12; font-size: 1.25rem;"></i>
        <div>
            <strong style="color: #f39c12; font-size: 0.85rem;">Politique de remboursement</strong>
            <p class="mb-0 text-muted" style="font-size: 0.82rem;">
                Les tickets sont remboursables dans les 30 jours suivant l'achat ou si l'événement est annulé. Le remboursement est traité via KKiaPay et un email est envoyé automatiquement à l'acheteur.
            </p>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                    <i class="bi bi-arrow-return-left"></i>
                </div>
                <div class="metric-label">Remboursables</div>
                <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($stats['remboursables'])); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-label">Remboursés</div>
                <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($stats['rembourses'])); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1); color: var(--teal);">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div class="metric-label">Montant remboursable</div>
                <div class="metric-value" style="font-size: 1.1rem;"><?php echo e(number_format($stats['montant_remboursable'], 0, ',', ' ')); ?> F</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--danger);">
                <div class="metric-icon" style="background: rgba(231,76,60,0.1); color: var(--danger);">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="metric-label">Total remboursé</div>
                <div class="metric-value" style="font-size: 1.1rem;"><?php echo e(number_format($stats['montant_total_rembourse'], 0, ',', ' ')); ?> F</div>
            </div>
        </div>
    </div>

    
    <div class="panel-card mb-4">
        <div class="panel-card-body py-3">
            <form action="<?php echo e(route('admin.remboursements.index')); ?>" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Rechercher</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Nom, email, code, transaction..." value="<?php echo e($q); ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Événement</label>
                    <select name="evenement_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($event->id); ?>" <?php echo e($selectedEvent == $event->id ? 'selected' : ''); ?>>
                                <?php echo e(Str::limit($event->titre, 30)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="remboursable" <?php echo e($statut === 'remboursable' ? 'selected' : ''); ?>>Remboursables</option>
                        <option value="rembourse" <?php echo e($statut === 'rembourse' ? 'selected' : ''); ?>>Remboursés</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-vert btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="<?php echo e(route('admin.remboursements.index')); ?>" class="btn btn-secondary-custom btn-sm w-100" style="border-radius: 6px;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="panel-card">
        <div class="panel-card-header">
            <h5>
                <?php if($statut === 'rembourse'): ?>
                    <i class="bi bi-check-circle me-2" style="color: var(--vert);"></i>Tickets remboursés
                <?php else: ?>
                    <i class="bi bi-arrow-return-left me-2" style="color: var(--violet);"></i>Tickets remboursables
                <?php endif; ?>
            </h5>
            <span class="text-muted" style="font-size: 0.78rem;"><?php echo e($remboursements->total()); ?> ticket<?php echo e($remboursements->total() > 1 ? 's' : ''); ?></span>
        </div>
        <div class="panel-card-body p-0">
            <?php if($remboursements->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Acheteur</th>
                                <th>Événement</th>
                                <th>Montant</th>
                                <th>Date achat</th>
                                <th>Transaction</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $remboursements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <code class="fw-bold" style="font-size: 0.82rem;"><?php echo e($ticket->code_unique); ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="font-size: 0.82rem;"><?php echo e(Str::limit($ticket->nom_acheteur ?? '—', 25)); ?></div>
                                        <small class="text-muted" style="font-size: 0.72rem;"><?php echo e(Str::limit($ticket->email_acheteur, 30)); ?></small>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem;"><?php echo e(Str::limit($ticket->evenement->titre, 35)); ?></div>
                                        <small class="text-muted" style="font-size: 0.72rem;"><?php echo e($ticket->evenement->date_event->format('d/m/Y')); ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold" style="color: var(--vert);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem;"><?php echo e($ticket->date_achat->format('d/m/Y')); ?></div>
                                        <small class="text-muted" style="font-size: 0.72rem;"><?php echo e($ticket->date_achat->format('H:i')); ?></small>
                                    </td>
                                    <td>
                                        <code style="font-size: 0.72rem;"><?php echo e(Str::limit($ticket->transaction_id ?? '—', 20)); ?></code>
                                    </td>
                                    <td>
                                        <?php if($ticket->statut_paiement === 'remboursé'): ?>
                                            <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert); font-size: 0.68rem;">Remboursé</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(243,156,18,0.12); color: #f39c12; font-size: 0.68rem;">Remboursable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($ticket->statut_paiement === 'payé'): ?>
                                            <button type="button" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--violet); color: var(--violet); background: transparent;" onclick="openRembourseModal(<?php echo e($ticket->id); ?>, '<?php echo e($ticket->code_unique); ?>', '<?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?>', '<?php echo e(addslashes($ticket->evenement->titre)); ?>')" title="Rembourser">
                                                <i class="bi bi-arrow-return-left"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--danger); color: var(--danger); background: transparent;" onclick="openAnnulerModal(<?php echo e($ticket->id); ?>, '<?php echo e($ticket->code_unique); ?>')" title="Annuler le remboursement">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    <?php echo e($remboursements->appends(['q' => $q, 'evenement_id' => $selectedEvent, 'statut' => $statut])->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-receipt" style="font-size: 3rem; color: var(--gris); opacity: 0.3;"></i>
                    <p class="text-muted mt-3 mb-0">Aucun ticket trouvé pour ces critères.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="modal fade" id="modalRembourse" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem;">
                <h5 class="modal-title" style="color: var(--violet);">
                    <i class="bi bi-arrow-return-left me-2"></i>Rembourser le ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 rounded mb-3" style="background: var(--blanc-casse);">
                    <div class="row g-2" style="font-size: 0.85rem;">
                        <div class="col-6"><strong>Code:</strong> <code id="remCode"></code></div>
                        <div class="col-6"><strong>Montant:</strong> <span style="color: var(--vert);" id="remMontant"></span></div>
                        <div class="col-12"><strong>Événement:</strong> <span id="remEvenement"></span></div>
                    </div>
                </div>

                <form id="formRembourse" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Motif du remboursement <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="3" placeholder="Expliquez la raison du remboursement..." required minlength="10"></textarea>
                        <?php $__errorArgs = ['motif'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size: 0.78rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="alert alert-warning" style="border-radius: 8px; font-size: 0.82rem; background: rgba(243,156,18,0.06); border: none;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Attention :</strong> Cette action est irréversible. Le montant sera restitué via KKiaPay et un email sera envoyé à l'acheteur.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal" style="border-radius: 8px;">Annuler</button>
                        <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                            <i class="bi bi-check-lg me-1"></i> Confirmer le remboursement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalAnnuler" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem;">
                <h5 class="modal-title" style="color: var(--danger);">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Annuler le remboursement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" style="font-size: 0.88rem;">
                    Êtes-vous sûr de vouloir annuler le remboursement du ticket <strong id="annCode"></strong> ? Le ticket redeviendra valide.
                </p>

                <form id="formAnnuler" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="alert alert-danger" style="border-radius: 8px; font-size: 0.82rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Attention :</strong> Cette action réactivera le ticket. L'acheteur pourra de nouveau l'utiliser.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal" style="border-radius: 8px;">Annuler</button>
                        <button type="submit" class="btn btn-outline-rouge" style="border-radius: 8px;">
                            <i class="bi bi-check-lg me-1"></i> Confirmer l'annulation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function openRembourseModal(id, code, montant, evenement) {
    document.getElementById('remCode').textContent = code;
    document.getElementById('remMontant').textContent = montant + ' FCFA';
    document.getElementById('remEvenement').textContent = evenement;
    document.getElementById('formRembourse').action = '/tickets/' + id + '/rembourser';
    new bootstrap.Modal(document.getElementById('modalRembourse')).show();
}

function openAnnulerModal(id, code) {
    document.getElementById('annCode').textContent = code;
    document.getElementById('formAnnuler').action = '/tickets/' + id + '/annuler-remboursement';
    new bootstrap.Modal(document.getElementById('modalAnnuler')).show();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\admin\remboursements\index.blade.php ENDPATH**/ ?>