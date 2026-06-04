<?php $__env->startSection('title', 'Codes Promo - PassEvent'); ?>

<?php $__env->startSection('page-title', 'Codes Promo'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Codes Promo</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('topbar-actions'); ?>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.codes-promos.export', ['evenement_id' => $selectedEvent])); ?>" class="btn btn-secondary-custom btn-sm" style="border-radius: 6px;">
            <i class="bi bi-download me-1"></i> Exporter
        </a>
        <button type="button" class="btn btn-vert btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate" onclick="updateTarifs()">
            <i class="bi bi-plus-lg me-1"></i> <span class="btn-text">Générer</span>
        </button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                    <i class="bi bi-tag"></i>
                </div>
                <div class="metric-label">Total codes</div>
                <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($stats['total'])); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-label">Disponibles</div>
                <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($stats['actifs'])); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1); color: var(--teal);">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="metric-label">Utilisés</div>
                <div class="metric-value" style="font-size: 1.5rem;"><?php echo e(number_format($stats['utilises'])); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--danger);">
                <div class="metric-icon" style="background: rgba(231,76,60,0.1); color: var(--danger);">
                    <i class="bi bi-percent"></i>
                </div>
                <div class="metric-label">Réduction totale</div>
                <div class="metric-value" style="font-size: 1.1rem;"><?php echo e(number_format($stats['reduction_totale'], 0, ',', ' ')); ?> F</div>
            </div>
        </div>
    </div>

    
    <div class="panel-card mb-4">
        <div class="panel-card-body py-3">
            <form action="<?php echo e(route('admin.codes-promos.index')); ?>" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Rechercher un code</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Ex: PROMO-..." value="<?php echo e($q); ?>">
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
                        <option value="">Tous</option>
                        <option value="disponible" <?php echo e($statut === 'disponible' ? 'selected' : ''); ?>>Disponibles</option>
                        <option value="utilise" <?php echo e($statut === 'utilise' ? 'selected' : ''); ?>>Utilisés</option>
                        <option value="expire" <?php echo e($statut === 'expire' ? 'selected' : ''); ?>>Expirés</option>
                        <option value="inactif" <?php echo e($statut === 'inactif' ? 'selected' : ''); ?>>Inactifs</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-vert btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="<?php echo e(route('admin.codes-promos.index')); ?>" class="btn btn-secondary-custom btn-sm w-100" style="border-radius: 6px;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-tags me-2" style="color: var(--violet);"></i>Liste des codes promo</h5>
            <span class="text-muted" style="font-size: 0.78rem;"><?php echo e($codesPromos->total()); ?> code<?php echo e($codesPromos->total() > 1 ? 's' : ''); ?></span>
        </div>
        <div class="panel-card-body p-0">
            <?php if($codesPromos->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Événement</th>
                                <th>Tarif lié</th>
                                <th>Réduction</th>
                                <th>Utilisations</th>
                                <th>Expiration</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $codesPromos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <code class="fw-bold" style="font-size: 0.85rem;"><?php echo e($code->code); ?></code>
                                            <button type="button" class="btn btn-sm p-0 border-0 bg-transparent" onclick="copyToClipboard('<?php echo e($code->code); ?>')" title="Copier">
                                                <i class="bi bi-clipboard" style="font-size: 0.85rem; color: var(--gris);"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem;"><?php echo e(Str::limit($code->evenement->titre, 30)); ?></div>
                                    </td>
                                    <td>
                                        <?php if($code->tarif): ?>
                                            <span class="badge" style="background: var(--blanc-casse); color: var(--sombre); font-weight: 500;"><?php echo e($code->tarif->getLabel()); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($code->type_reduction === 'pourcentage'): ?>
                                            <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-weight: 600;">-<?php echo e($code->valeur_reduction); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-weight: 600;">-<?php echo e(number_format($code->valeur_reduction, 0, ',', ' ')); ?> F</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold" style="font-size: 0.85rem;"><?php echo e($code->nb_utilisations); ?></span>
                                            <div class="progress-bar-custom" style="width: 50px;">
                                                <?php
                                                    $pct = $code->max_utilisations > 0 
                                                        ? min(100, ($code->nb_utilisations / $code->max_utilisations) * 100) 
                                                        : 0;
                                                ?>
                                                <div class="progress-bar-fill" style="width: <?php echo e($pct); ?>%; background: <?php echo e($pct >= 100 ? 'var(--danger)' : 'var(--vert)'); ?>;"></div>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">/ <?php echo e($code->max_utilisations ?? '∞'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($code->date_expiration): ?>
                                            <span style="font-size: 0.82rem;"><?php echo e($code->date_expiration->format('d/m/Y')); ?></span>
                                            <?php if($code->date_expiration->isPast() && $code->nb_utilisations == 0): ?>
                                                <br><span class="text-danger" style="font-size: 0.7rem;">Expiré</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.82rem;">Illimité</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($code->nb_utilisations > 0): ?>
                                            <span class="badge" style="background: rgba(66,140,121,0.12); color: var(--teal); font-size: 0.68rem;">Utilisé</span>
                                        <?php elseif(!$code->actif): ?>
                                            <span class="badge" style="background: rgba(152,145,155,0.15); color: var(--gris); font-size: 0.68rem;">Inactif</span>
                                        <?php elseif($code->date_expiration && $code->date_expiration->isPast()): ?>
                                            <span class="badge" style="background: rgba(231,76,60,0.12); color: var(--danger); font-size: 0.68rem;">Expiré</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert); font-size: 0.68rem;">Disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem;"><?php echo e($code->created_at->format('d/m/Y')); ?></div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--danger); color: var(--danger); background: transparent;" onclick="confirmDelete(<?php echo e($code->id); ?>, '<?php echo e($code->code); ?>')" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    <?php echo e($codesPromos->appends(['q' => $q, 'evenement_id' => $selectedEvent, 'statut' => $statut])->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-tags" style="font-size: 3rem; color: var(--gris); opacity: 0.3;"></i>
                    <p class="text-muted mt-3 mb-0">Aucun code promo trouvé.</p>
                    <button type="button" class="btn btn-vert btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCreate">
                        <i class="bi bi-plus-lg me-1"></i> Générer votre premier code
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem;">
                <h5 class="modal-title" style="color: var(--violet);">
                    <i class="bi bi-magic me-2"></i>Générer des codes promo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.codes-promos.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Événement <span class="text-danger">*</span></label>
                        <select name="evenement_id" id="createEventSelect" class="form-select" required onchange="loadTarifs(this.value)">
                            <option value="">Sélectionner un événement</option>
                            <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($event->id); ?>" <?php echo e($selectedEvent == $event->id ? 'selected' : ''); ?>>
                                    <?php echo e($event->titre); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Tarif lié <span class="text-danger">*</span></label>
                        <select name="tarif_id" id="createTarifSelect" class="form-select" required>
                            <option value="">Sélectionner d'abord un événement</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">Type de réduction <span class="text-danger">*</span></label>
                            <select name="type_reduction" class="form-select" required>
                                <option value="pourcentage">Pourcentage (%)</option>
                                <option value="fixe">Montant fixe (F)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">Valeur <span class="text-danger">*</span></label>
                            <input type="number" name="valeur_reduction" class="form-control" placeholder="Ex: 10 pour 10% ou 5000F" min="0" step="0.01" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">Préfixe (optionnel)</label>
                            <input type="text" name="prefixe" class="form-control" placeholder="Ex: PROMO" maxlength="10">
                            <small class="text-muted">Le code sera généré sous la forme PREFIXE-XXXXXX</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">Quantité</label>
                            <input type="number" name="count" class="form-control" value="1" min="1" max="100">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">Max utilisations</label>
                            <input type="number" name="max_utilisations" class="form-control" placeholder="Laisser vide pour illimité" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 0.82rem;">Date d'expiration</label>
                            <input type="datetime-local" name="date_expiration" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 1.25rem;">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal" style="border-radius: 8px;">Annuler</button>
                    <button type="submit" class="btn btn-vert" style="border-radius: 8px;">
                        <i class="bi bi-check-lg me-1"></i> Générer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-trash" style="font-size: 2.5rem; color: var(--danger);"></i>
                <h5 class="mt-3 mb-1">Supprimer ce code ?</h5>
                <p class="text-muted mb-4" style="font-size: 0.88rem;">Le code <strong id="deleteCodeName"></strong> sera supprimé définitivement.</p>
                <form id="deleteForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal" style="border-radius: 8px;">Annuler</button>
                        <button type="submit" class="btn btn-outline-rouge" style="border-radius: 8px;">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const tarifsData = <?php echo json_encode($evenements->mapWithKeys(function($event) {
    return [$event->id => $event->tarifs->where('statut', 'actif')->map(fn($t) => ['id' => $t->id, 'label' => $t->getLabel()])];
})) ?>;

function loadTarifs(eventId) {
    const select = document.getElementById('createTarifSelect');
    select.innerHTML = '<option value="">Sélectionner un tarif</option>';
    
    if (tarifsData[eventId]) {
        tarifsData[eventId].forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.label;
            select.appendChild(opt);
        });
    }
}

function updateTarifs() {
    const selectedEvent = '<?php echo e($selectedEvent); ?>';
    if (selectedEvent) {
        document.getElementById('createEventSelect').value = selectedEvent;
        loadTarifs(selectedEvent);
    }
}

function copyToClipboard(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('Code copié : ' + code);
    });
}

function confirmDelete(id, code) {
    document.getElementById('deleteCodeName').textContent = code;
    document.getElementById('deleteForm').action = '/admin/codes-promos/' + id;
    new bootstrap.Modal(document.getElementById('modalDelete')).show();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\admin\codes-promos\index.blade.php ENDPATH**/ ?>