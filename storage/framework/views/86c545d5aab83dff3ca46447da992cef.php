<div class="sa-card-body p-0">
    <table class="sa-table">
        <thead>
            <tr><th>Nom</th><th>Email</th><th>Type</th><th>Statut</th><th>Evenements</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $organisateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><strong><?php echo e($org->nom); ?></strong></td>
                <td><?php echo e($org->email); ?></td>
                <td><?php if($org->type): ?><span class="sa-badge sa-badge-info"><?php echo e(ucfirst($org->type)); ?></span><?php else: ?>-<?php endif; ?></td>
                <td>
                    <?php $st = $org->statut; ?>
                    <?php if($st === 'en_attente'): ?>
                        <span class="sa-badge sa-badge-warning">En attente</span>
                    <?php elseif($st === 'actif'): ?>
                        <span class="sa-badge sa-badge-success">Actif</span>
                    <?php elseif($st === 'incomplet'): ?>
                        <span class="sa-badge sa-badge-secondary">Incomplet</span>
                    <?php elseif($st === 'corrections_demandees'): ?>
                        <span class="sa-badge sa-badge-warning" style="background:rgba(237,173,8,0.12);color:#8b6914;">Corrections demandées</span>
                    <?php elseif($st === 'corrections_apportees'): ?>
                        <span class="sa-badge sa-badge-info" style="background:rgba(13,110,253,0.12);color:#0a58ca;">Corrections apportées</span>
                    <?php elseif($st === 'rejete'): ?>
                        <span class="sa-badge sa-badge-danger">Rejeté</span>
                    <?php elseif($st === 'bloque'): ?>
                        <span class="sa-badge sa-badge-danger">Bloqué</span>
                    <?php else: ?>
                        <span class="sa-badge sa-badge-secondary"><?php echo e($st); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($org->evenements_count); ?></td>
                <td style="white-space:nowrap;">
                    <div class="d-flex flex-nowrap gap-1">
                        <a href="<?php echo e(route('superadmin.organisateurs.voir', $org)); ?>" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.45rem;border-radius:6px;font-size:0.7rem;line-height:1;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;" title="Voir le détail">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if(in_array($org->statut, ['en_attente', 'incomplet', 'corrections_demandees', 'corrections_apportees'])): ?>
                            <form action="<?php echo e(route('superadmin.organisateurs.approuver', $org)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <button class="sa-btn sa-btn-sm sa-btn-danger" title="Rejeter"
                                onclick="document.getElementById('rejetModal<?php echo e($org->id); ?>').style.display='flex'">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        <?php elseif($org->statut === 'actif'): ?>
                            <form action="<?php echo e(route('superadmin.organisateurs.suspendre', $org)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Suspendre <?php echo e($org->nom); ?> ? Ses événements seront annulés.')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-warning" title="Suspendre"><i class="bi bi-pause-fill"></i></button>
                            </form>
                            <form action="<?php echo e(route('superadmin.organisateurs.supprimer', $org)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement <?php echo e($org->nom); ?> ? Cette action est irréversible.')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php elseif(in_array($org->statut, ['bloque', 'rejete'])): ?>
                            <form action="<?php echo e(route('superadmin.organisateurs.reactiver', $org)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Réactiver <?php echo e($org->nom); ?> ?<?php echo e($org->statut === 'bloque' ? ' Ses événements annulés seront republiés.' : ''); ?>')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Réactiver"><i class="bi bi-play-fill"></i></button>
                            </form>
                            <form action="<?php echo e(route('superadmin.organisateurs.supprimer', $org)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement <?php echo e($org->nom); ?> ? Cette action est irréversible.')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo e(route('superadmin.organisateurs.supprimer', $org)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement <?php echo e($org->nom); ?> ? Cette action est irréversible.')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="text-center text-muted">Aucun organisateur trouvé</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $__currentLoopData = $organisateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="rejetModal<?php echo e($org->id); ?>" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Rejeter <?php echo e($org->nom); ?></h5>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form action="<?php echo e(route('superadmin.organisateurs.rejeter', $org)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Expliquez le motif du rejet. L'organisateur recevra un email avec cette explication.</p>
                <textarea name="motif" class="sa-form-control" rows="4" placeholder="Motif du rejet..." required style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-x-lg"></i> Rejeter</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="p-3" id="organisateurs-pagination"><?php echo e($organisateurs->appends(request()->query())->links('vendor.pagination.pass', ['label' => 'organisateur(s)'])); ?></div>
<?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/partials/organisateurs-table.blade.php ENDPATH**/ ?>