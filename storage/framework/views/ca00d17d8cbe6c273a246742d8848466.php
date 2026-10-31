<?php $__env->startSection('title', 'Demandes de modification de tarifs - Super Admin PaxEvent'); ?>
<?php $__env->startSection('page-title', 'Demandes de modification de tarifs'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(243,156,18,0.1); color: var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['en_attente']); ?></div>
                <div class="kpi-label">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(39,174,96,0.1); color: var(--sa-success);"><i class="bi bi-check-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['approuve']); ?></div>
                <div class="kpi-label">Approuvées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(231,76,60,0.1); color: var(--sa-danger);"><i class="bi bi-x-circle-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['refuse']); ?></div>
                <div class="kpi-label">Refusées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(52,152,219,0.1); color: #3498db;"><i class="bi bi-tags"></i></div>
            <div class="kpi-info">
                <div class="kpi-value"><?php echo e($stats['total']); ?></div>
                <div class="kpi-label">Total</div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-list-check me-2" style="color: var(--sa-primary);"></i>Toutes les demandes</span>
        <span class="sa-topbar-badge"><?php echo e($demandes->total()); ?> demandes</span>
    </div>
    <div class="sa-card-body p-0">
        <?php if($demandes->count() > 0): ?>
            <div class="table-responsive">
                <table class="sa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Organisateur</th>
                            <th>Événement</th>
                            <th>Tarif</th>
                            <th>Ancien prix</th>
                            <th>Nouveau prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $demandes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $demande): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo e($demande->created_at->isoFormat('DD/MM/YYYY HH:mm')); ?></td>
                                <td>
                                    <strong><?php echo e($demande->user->nom); ?></strong>
                                    <br><small style="color:var(--sa-text-muted);"><?php echo e($demande->user->email); ?></small>
                                </td>
                                <td><?php echo e($demande->evenement->titre); ?></td>
                                <td><?php echo e($demande->tarif->nom); ?></td>
                                <td><strong><?php echo e(number_format($demande->ancien_prix, 0, ',', ' ')); ?> F</strong></td>
                                <td><strong style="color:var(--sa-warning);"><?php echo e(number_format($demande->nouveau_prix, 0, ',', ' ')); ?> F</strong></td>
                                <td>
                                    <?php if($demande->statut === 'en_attente'): ?>
                                        <span class="sa-badge sa-badge-warning">En attente</span>
                                    <?php elseif($demande->statut === 'approuve'): ?>
                                        <span class="sa-badge sa-badge-success">Approuvée</span>
                                    <?php else: ?>
                                        <span class="sa-badge sa-badge-danger">Refusée</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($demande->statut === 'en_attente'): ?>
                                        <button type="button" class="sa-btn sa-btn-sm sa-btn-primary" title="Approuver"
                                            onclick="document.getElementById('approuverModal<?php echo e($demande->id); ?>').style.display='flex'">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="sa-btn sa-btn-sm sa-btn-danger" title="Refuser"
                                            onclick="document.getElementById('refuserModal<?php echo e($demande->id); ?>').style.display='flex'">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="sa-btn sa-btn-sm sa-btn-info" title="Voir les détails"
                                        onclick="document.getElementById('detailModal<?php echo e($demande->id); ?>').style.display='flex'">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <div id="detailModal<?php echo e($demande->id); ?>" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h5><i class="bi bi-tags me-2" style="color:var(--sa-primary);"></i>Demande de modification</h5>
                                                <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Organisateur</span>
                                                    <span class="org-detail-value"><strong><?php echo e($demande->user->nom); ?></strong></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Événement</span>
                                                    <span class="org-detail-value"><?php echo e($demande->evenement->titre); ?></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Tarif</span>
                                                    <span class="org-detail-value"><?php echo e($demande->tarif->nom); ?></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Ancien prix</span>
                                                    <span class="org-detail-value"><strong><?php echo e(number_format($demande->ancien_prix, 0, ',', ' ')); ?> FCFA</strong></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Nouveau prix</span>
                                                    <span class="org-detail-value"><strong style="color:var(--sa-warning);"><?php echo e(number_format($demande->nouveau_prix, 0, ',', ' ')); ?> FCFA</strong></span>
                                                </div>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Statut</span>
                                                    <span class="org-detail-value">
                                                        <?php if($demande->statut === 'en_attente'): ?>
                                                            <span class="sa-badge sa-badge-warning">En attente</span>
                                                        <?php elseif($demande->statut === 'approuve'): ?>
                                                            <span class="sa-badge sa-badge-success">Approuvée</span>
                                                        <?php else: ?>
                                                            <span class="sa-badge sa-badge-danger">Refusée</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <?php if($demande->notes): ?>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Notes</span>
                                                    <span class="org-detail-value" style="white-space:pre-line;"><?php echo e($demande->notes); ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <?php if($demande->traitee_le): ?>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Traité le</span>
                                                    <span class="org-detail-value"><?php echo e($demande->traitee_le->format('d M Y à H:i')); ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <div class="org-detail-row">
                                                    <span class="org-detail-label">Demandé le</span>
                                                    <span class="org-detail-value"><?php echo e($demande->created_at->format('d M Y à H:i')); ?></span>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="gap:0.5rem; flex-wrap:wrap;">
                                                <?php if($demande->statut === 'en_attente'): ?>
                                                    <button type="button" class="sa-btn sa-btn-primary" onclick="this.closest('.modal-overlay').style.display='none'; document.getElementById('approuverModal<?php echo e($demande->id); ?>').style.display='flex';">
                                                        <i class="bi bi-check-lg"></i> Approuver
                                                    </button>
                                                    <button type="button" class="sa-btn sa-btn-danger" onclick="this.closest('.modal-overlay').style.display='none'; document.getElementById('refuserModal<?php echo e($demande->id); ?>').style.display='flex';">
                                                        <i class="bi bi-x-lg"></i> Refuser
                                                    </button>
                                                <?php endif; ?>
                                                <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Fermer</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="approuverModal<?php echo e($demande->id); ?>" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h5><i class="bi bi-check-circle me-2" style="color:var(--sa-success);"></i>Approuver la demande</h5>
                                                <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p style="font-size:0.85rem;color:#6c757d;margin-bottom:1rem;">
                                                    Le prix du tarif « <?php echo e($demande->tarif->nom); ?> » passera de
                                                    <strong><?php echo e(number_format($demande->ancien_prix, 0, ',', ' ')); ?> F</strong> à
                                                    <strong style="color:var(--sa-success);"><?php echo e(number_format($demande->nouveau_prix, 0, ',', ' ')); ?> F</strong>.
                                                </p>
                                                <form action="<?php echo e(route('superadmin.demandes-modification-tarifs.approuver', $demande)); ?>" method="POST" id="approuverForm<?php echo e($demande->id); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="mb-0">
                                                        <label style="font-size:0.82rem;font-weight:600;color:#666;">Notes (optionnel)</label>
                                                        <textarea name="notes" class="sa-form-control" rows="2" placeholder="Ajouter une note à l'organisateur..."></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                                                <button type="submit" form="approuverForm<?php echo e($demande->id); ?>" class="sa-btn sa-btn-primary" onclick="return confirm('Confirmer l\'approbation et appliquer le nouveau prix ?')">
                                                    <i class="bi bi-check-lg"></i> Confirmer
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="refuserModal<?php echo e($demande->id); ?>" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Refuser la demande</h5>
                                                <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="<?php echo e(route('superadmin.demandes-modification-tarifs.refuser', $demande)); ?>" method="POST" id="refuserForm<?php echo e($demande->id); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="mb-0">
                                                        <label style="font-size:0.82rem;font-weight:600;color:#666;">Raison du refus</label>
                                                        <textarea name="notes" class="sa-form-control" rows="3" placeholder="Expliquez le motif du refus à l'organisateur..."></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                                                <button type="submit" form="refuserForm<?php echo e($demande->id); ?>" class="sa-btn sa-btn-danger" onclick="return confirm('Confirmer le refus ?')">
                                                    <i class="bi bi-x-lg"></i> Confirmer le refus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem; color: var(--sa-text-muted);"></i>
                <p style="color: var(--sa-text-muted);">Aucune demande de modification de tarif.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if($demandes->hasPages()): ?>
    <div class="mt-3"><?php echo e($demandes->links()); ?></div>
<?php endif; ?>

<style>
.sa-btn-info {
    background: #3b82f6; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-info:hover { opacity: 0.85; }
.sa-btn-secondary {
    background: #6c757d; border: none; color: #fff; padding: 0.4rem 1rem;
    border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-secondary:hover { opacity: 0.85; }
.sa-btn-danger {
    background: var(--sa-danger); border: none; color: #fff; padding: 0.4rem 1rem;
    border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s; display: inline-flex; align-items: center; gap: 0.3rem;
}
.sa-btn-danger:hover { opacity: 0.85; }

.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-box {
    background: #fff;
    border-radius: 14px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: modalIn 0.2s ease;
}
@keyframes modalIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #eee;
}
.modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.modal-close {
    background: none; border: none;
    font-size: 1.5rem; cursor: pointer;
    color: #999; line-height: 1;
}
.modal-close:hover { color: #333; }
.modal-body { padding: 1.25rem; }
.modal-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}
.org-detail-row {
    display: flex;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.85rem;
}
.org-detail-row:last-child { border-bottom: none; }
.org-detail-label {
    font-weight: 600;
    color: #666;
    min-width: 120px;
    flex-shrink: 0;
}
.org-detail-value { color: #1a1a1a; }

.modal-overlay .sa-form-control {
    border: 1px solid var(--sa-border);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.82rem;
    width: 100%;
    transition: border-color 0.15s;
}
.modal-overlay .sa-form-control:focus {
    border-color: var(--sa-primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(107,63,160,0.1);
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/demandes-modification-tarifs.blade.php ENDPATH**/ ?>