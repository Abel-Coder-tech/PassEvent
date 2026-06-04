<?php $__env->startSection('title', 'Codes promos — ' . $evenement->titre); ?>

<?php $__env->startSection('page-title', 'Codes promos étudiants'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.index')); ?>">Événements</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.evenements.show', $evenement->id)); ?>"><?php echo e($evenement->titre); ?></a></li>
    <li class="breadcrumb-item active" aria-current="page">Codes promos</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-subtitle', 'Liste des codes promos — ' . $evenement->titre . ' · ' . $totalGeneres . ' codes générés'); ?>

<?php $__env->startSection('topbar-actions'); ?>
<a href="<?php echo e(route('admin.evenements.show', $evenement->id)); ?>" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Stat Cards -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-label">Total générés</div>
                <div class="metric-value"><?php echo e($totalGeneres); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-label">Utilisés</div>
                <div class="metric-value" style="color: var(--vert);"><?php echo e($utilises); ?></div>
                <div class="metric-subtitle"><?php echo e($pctUtilises); ?>% du total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-label">Disponibles</div>
                <div class="metric-value" style="color: var(--teal);"><?php echo e($disponibles); ?></div>
                <div class="metric-subtitle"><?php echo e($pctDisponibles); ?>% restant</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--menthe);">
                <div class="metric-label">Économie étudiants</div>
                <div class="metric-value" style="font-size: 1.3rem; color: var(--violet);"><?php echo e(number_format($economieEtudiants, 0, ',', ' ')); ?> F</div>
                <div class="metric-subtitle">Réductions appliquées</div>
            </div>
        </div>
    </div>

    <!-- Toolbar: Search -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div class="position-relative w-100" style="max-width: 320px;">
            <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 0.9rem;"></i>
            <input type="text" class="form-control ps-5 py-2 border rounded-3" id="searchInput" placeholder="Rechercher un code…" style="border-color: #e2e0e4; font-size: 0.88rem;">
        </div>
    </div>

    <!-- Codes Table -->
    <div class="panel-card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table custom-table table-hover mb-0" id="codesTable">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>UTILISÉ PAR</th>
                            <th>STATUT</th>
                            <th>DATE</th>
                            <th>TARIF</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $codesPromos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="code-row" data-code="<?php echo e(strtolower($code->code)); ?>">
                                <td>
                                    <code class="fw-semibold" style="color: var(--violet); font-size: 0.95rem;"><?php echo e($code->code); ?></code>
                                </td>
                                <td>
                                    <?php if($code->nb_utilisations > 0): ?>
                                        <?php echo e($code->ticket?->nom_acheteur ?? '—'); ?>

                                    <?php else: ?>
                                        <span class="text-muted">Non utilisé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($code->nb_utilisations > 0): ?>
                                        <span class="status-badge status-en-cours">Utilisé</span>
                                    <?php elseif(!$code->actif): ?>
                                        <span class="status-badge status-termine">Désactivé</span>
                                    <?php else: ?>
                                        <span class="status-badge status-a-venir">Disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($code->nb_utilisations > 0 && $code->ticket): ?>
                                        <?php echo e($code->ticket->date_achat->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold">
                                    <?php echo e($code->tarif ? number_format($code->tarif->prix, 0, ',', ' ') . ' F' : '—'); ?>

                                </td>
                                <td class="text-end">
                                    <?php if($code->nb_utilisations > 0 && $code->ticket): ?>
                                        <a href="<?php echo e(route('tickets.show', $code->ticket->id)); ?>" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px;">
                                            <i class="bi bi-ticket-perforated me-1"></i>Ticket
                                        </a>
                                    <?php else: ?>
                                        <form action="<?php echo e(route('admin.codes-promos.destroy', [$evenement->id, $code->id])); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm" style="border-radius: 6px; border: 1px solid #e74c3c; color: #e74c3c; background: transparent;" onclick="return confirm('Désactiver ce code ?')">
                                                <i class="bi bi-x-circle me-1"></i>Désactiver
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-tag d-block mb-2" style="font-size: 2rem; color: var(--gris);"></i>
                                    <small>Aucun code promo généré</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($codesPromos->hasPages()): ?>
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size: 0.82rem;">
                            Affichage <?php echo e($codesPromos->firstItem()); ?> à <?php echo e($codesPromos->lastItem()); ?> sur <?php echo e($codesPromos->total()); ?> codes
                        </span>
                        <?php echo e($codesPromos->links()); ?>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Generate Codes Form -->
    <div class="panel-card mb-4">
        <div class="panel-card-header">
            <h5>Générer des codes</h5>
        </div>
        <div class="panel-card-body">
            <form action="<?php echo e(route('admin.codes-promos.store', $evenement->id)); ?>" method="POST" id="generateForm">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="evenement_select" class="form-label fw-semibold">Événement</label>
                        <select class="form-select" id="evenement_select" name="evenement_id" disabled>
                            <?php $__currentLoopData = $userEvenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($evt->id); ?>" <?php echo e($evt->id === $evenement->id ? 'selected' : ''); ?>><?php echo e($evt->titre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="tarif_id" class="form-label fw-semibold">Tarif lié <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['tarif_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="tarif_id" name="tarif_id" required>
                            <option value="">Choisir un tarif —</option>
                            <?php $__currentLoopData = $tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tarif->id); ?>" <?php echo e(old('tarif_id') == $tarif->id ? 'selected' : ''); ?>><?php echo e($tarif->getLabel()); ?> — <?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['tarif_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="prefixe" class="form-label fw-semibold">Préfixe personnalisé</label>
                        <input type="text" class="form-control" id="prefixe" name="prefixe" value="<?php echo e(old('prefixe')); ?>" maxlength="10" placeholder="Ex: PROMO, ETU2025">
                        <small class="text-muted">Le suffixe sera généré automatiquement de manière unique</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="count" class="form-label fw-semibold">Nombre de codes</label>
                        <div class="input-group" style="max-width: 160px;">
                            <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyMinus" style="border-radius: 6px 0 0 6px;">−</button>
                            <input type="number" class="form-control text-center" id="count" name="count" value="<?php echo e(old('count', 1)); ?>" min="1" max="100" style="border-left:0;border-right:0;">
                            <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyPlus" style="border-radius: 0 6px 6px 0;">+</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Aperçu du format</label>
                        <div class="p-2 rounded" style="background: #f5f5f5; font-family: 'Courier New', monospace; font-weight: 700; font-size: 1rem; color: var(--violet);" id="previewCode">
                            PRÉFIXE-XXXXXX
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="date_expiration" class="form-label fw-semibold">Date d'expiration</label>
                        <input type="date" class="form-control" id="date_expiration" name="date_expiration" value="<?php echo e(old('date_expiration')); ?>">
                        <small class="text-muted">Laissez vide pour pas d'expiration</small>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-custom" style="border-radius: 8px;">
                        <i class="bi bi-magic me-1"></i> Générer les codes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Block -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5>Distribution</h5>
            <span class="text-muted" style="font-size: 0.82rem;">
                <?php echo e($disponibles); ?> disponibles · <?php echo e($utilises); ?> utilisés
            </span>
        </div>
        <div class="panel-card-body">
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="<?php echo e(route('admin.codes-promos.export', ['evenement' => $evenement->id, 'disponibles' => 1])); ?>" class="btn btn-secondary-custom" style="border-radius: 8px;">
                    <i class="bi bi-download me-1"></i> Exporter codes disponibles (CSV)
                </a>
                <a href="<?php echo e(route('admin.codes-promos.export', $evenement->id)); ?>" class="btn btn-secondary-custom" style="border-radius: 8px;">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exporter tous les codes
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('.code-row');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        rows.forEach(row => {
            row.style.display = row.dataset.code.includes(query) ? '' : 'none';
        });
    });

    const prefixeInput = document.getElementById('prefixe');
    const previewCode = document.getElementById('previewCode');

    function updatePreview() {
        const prefix = prefixeInput.value.trim().toUpperCase();
        if (prefix) {
            previewCode.textContent = prefix + '-' + 'XXXXXX';
        } else {
            previewCode.textContent = 'XXXXXXXXXXXX';
        }
    }

    prefixeInput.addEventListener('input', updatePreview);

    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const countInput = document.getElementById('count');

    qtyMinus.addEventListener('click', function() {
        const v = parseInt(countInput.value);
        if (v > 1) { countInput.value = v - 1; }
    });

    qtyPlus.addEventListener('click', function() {
        const v = parseInt(countInput.value);
        if (v < 100) { countInput.value = v + 1; }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\codes-promos\index.blade.php ENDPATH**/ ?>