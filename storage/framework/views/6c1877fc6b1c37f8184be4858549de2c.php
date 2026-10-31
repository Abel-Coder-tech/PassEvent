<?php $__env->startSection('title', 'Support technique - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Support technique'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="font-size:0.85rem;"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="alert alert-danger" style="font-size:0.85rem;"><?php echo e(session('error')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger" style="font-size:0.85rem;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($err); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(243,156,18,0.1);color:var(--sa-warning);"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-info"><div class="kpi-value"><?php echo e($stats['en_attente']); ?></div><div class="kpi-label">Tickets en attente</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(231,76,60,0.1);color:#e74c3c;"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-info"><div class="kpi-value"><?php echo e($stats['incidents']); ?></div><div class="kpi-label">Incidents paiement</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:rgba(107,63,160,0.1);color:var(--sa-primary);"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-info"><div class="kpi-value"><?php echo e(number_format($stats['rembourses_support'], 0, ',', ' ')); ?> F</div><div class="kpi-label">Remboursé (support)</div></div>
        </div>
    </div>
</div>

<?php if(!empty($verification) && $verification['ok']): ?>
    <div class="alert <?php echo e($verification['approuve'] ? 'alert-success' : 'alert-warning'); ?>" style="font-size:0.85rem;">
        <strong>Vérification API FedaPay :</strong>
        statut <span class="sa-badge <?php echo e($verification['approuve'] ? 'sa-badge-success' : 'sa-badge-warning'); ?>"><?php echo e(strtoupper($verification['statut'])); ?></span>
        · Montant <?php echo e($verification['montant']); ?> <?php echo e($verification['devise']); ?>

        · Téléphone <?php echo e($verification['telephone'] ?? '—'); ?>

        · Date <?php echo e(isset($verification['date']) ? \Illuminate\Support\Carbon::parse($verification['date'])->isoFormat('D MMM YYYY HH:mm') : '—'); ?>

        <?php if($verification['approuve']): ?>
            <div class="mt-1 text-success"><i class="bi bi-check-circle"></i> Paiement approuvé : vous pouvez confirmer les tickets sans forçage.</div>
        <?php else: ?>
            <div class="mt-1 text-warning"><i class="bi bi-exclamation-triangle"></i> Paiement non approuvé. La confirmation nécessitera un forçage explicite.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($verification && !$verification['ok']): ?>
    <div class="alert alert-warning" style="font-size:0.85rem;"><i class="bi bi-exclamation-triangle"></i> <?php echo e($verification['message']); ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-search me-2" style="color:var(--sa-primary);"></i>Rechercher un incident</span>
            </div>
            <div class="sa-card-body">
                <form method="GET" action="<?php echo e(route('superadmin.support')); ?>" class="mb-2">
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">ID transaction FedaPay</label>
                        <input type="text" name="transaction_id" value="<?php echo e($transactionId); ?>" class="form-control form-control-sm" placeholder="ex : 2847331">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Email de l'acheteur</label>
                        <input type="text" name="email" value="<?php echo e($email); ?>" class="form-control form-control-sm" placeholder="acheteur@exemple.com">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Téléphone</label>
                        <input type="text" name="telephone" value="<?php echo e($telephone); ?>" class="form-control form-control-sm" placeholder="+228 ...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Code du billet</label>
                        <input type="text" name="code" value="<?php echo e($code); ?>" class="form-control form-control-sm" placeholder="ex : PX-XXXXXX">
                    </div>
                    <input type="hidden" name="evenement_id" value="<?php echo e($evenementId ?? ''); ?>">
                    <button type="submit" class="sa-btn" style="background:var(--sa-primary);border:none;color:#fff;">Rechercher</button>
                </form>

                <hr style="opacity:0.15;">

                <form method="POST" action="<?php echo e(route('superadmin.support.verifier')); ?>">
                    <?php echo csrf_field(); ?>
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Vérifier une transaction via l'API FedaPay</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="ID transaction" value="<?php echo e($transactionId); ?>" required>
                        <button type="submit" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;">Vérifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-plus-circle me-2" style="color:var(--sa-primary);"></i>Recréer un ticket</span>
                <span class="text-muted" style="font-size:0.75rem;">Ticket purgé ou jamais généré</span>
            </div>
            <div class="sa-card-body">
                <form method="POST" action="<?php echo e(route('superadmin.support.recreer')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Événement</label>
                        <select name="evenement_id" id="evenement_id" class="form-select form-select-sm" required>
                            <option value="">— Sélectionner —</option>
                            <?php $__currentLoopData = \App\Models\Evenement::orderByDesc('created_at')->limit(200)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ev->id); ?>" <?php echo e(old('evenement_id') == $ev->id ? 'selected' : ''); ?>><?php echo e($ev->titre); ?> (#<?php echo e($ev->id); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Tarif</label>
                        <select name="tarif_id" id="tarif_id" class="form-select form-select-sm">
                            <option value="">— Tarif par défaut —</option>
                        </select>
                        <div class="text-muted" style="font-size:0.72rem;" id="tarif_hint">Sélectionnez un événement pour afficher ses tarifs.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Nom de l'acheteur</label>
                        <input type="text" name="nom_acheteur" value="<?php echo e(old('nom_acheteur')); ?>" class="form-control form-control-sm" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Téléphone</label>
                            <input type="text" name="telephone" value="<?php echo e(old('telephone')); ?>" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Montant (F)</label>
                            <input type="number" name="montant" value="<?php echo e(old('montant')); ?>" class="form-control form-control-sm" min="0" step="1">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Quantité</label>
                            <input type="number" name="quantite" value="<?php echo e(old('quantite', 1)); ?>" class="form-control form-control-sm" min="1" max="20" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Méthode</label>
                            <input type="text" name="methode_paiement" value="<?php echo e(old('methode_paiement', 'mobile_money')); ?>" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">ID transaction FedaPay</label>
                        <input type="text" name="fedapay_transaction_id" value="<?php echo e(old('fedapay_transaction_id')); ?>" class="form-control form-control-sm" placeholder="ID FedaPay (pour la preuve API)">
                    </div>
                    <button type="submit" class="sa-btn" style="background:var(--sa-success);border:none;color:#fff;"><i class="bi bi-plus-circle"></i> Recréer et envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-list-check me-2" style="color:var(--sa-primary);"></i>Résultats de la recherche</span>
                <span class="text-muted" style="font-size:0.8rem;"><?php echo e($tickets->count()); ?> ticket(s)</span>
            </div>
            <div class="sa-card-body p-0">
                <?php if($tickets->isEmpty()): ?>
                    <div class="text-center text-muted py-4" style="font-size:0.85rem;">Utilisez le formulaire de recherche ou le récapitulatif des incidents ci-dessous.</div>
                <?php else: ?>
                    <?php echo $__env->make('superadmin.support.tickets-table', ['tickets' => $tickets], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-exclamation-triangle me-2" style="color:#e74c3c;"></i>Incidents récents (en attente avec transaction FedaPay)</span>
                <span class="text-muted" style="font-size:0.8rem;"><?php echo e($incidents->count()); ?> incident(s)</span>
            </div>
            <div class="sa-card-body p-0">
                <?php if($incidents->isEmpty()): ?>
                    <div class="text-center text-muted py-4" style="font-size:0.85rem;">Aucun incident en attente. Le paiement est traité normalement.</div>
                <?php else: ?>
                    <?php echo $__env->make('superadmin.support.tickets-table', ['tickets' => $incidents], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.kpi-card {
    background: #fff; border-radius: 10px; padding: 1rem;
    display: flex; align-items: center; gap: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.kpi-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    flex-shrink: 0;
}
.kpi-info { min-width: 0; }
.kpi-value { font-size: 1.2rem; font-weight: 800; line-height: 1.2; }
.kpi-label { font-size: 0.72rem; color: #888; font-weight: 500; }
</style>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventSelect = document.getElementById('evenement_id');
    const tarifSelect = document.getElementById('tarif_id');
    const tarifHint = document.getElementById('tarif_hint');
    const csrf = document.querySelector('form input[name="_token"]')?.value || '';

    const formatPrix = (prix) => Number(prix).toLocaleString('fr-FR').replace(/[\u202f\u00a0]/g, ' ');

    function chargerTarifs(evenementId, tarifSelectionne) {
        tarifSelect.innerHTML = '<option value="">— Tarif par défaut —</option>';
        if (!evenementId) {
            tarifHint.textContent = 'Sélectionnez un événement pour afficher ses tarifs.';
            return;
        }
        tarifHint.textContent = 'Chargement des tarifs...';
        const fd = new FormData();
        fd.append('evenement_id', evenementId);

        fetch('<?php echo e(route('superadmin.support.tarifs')); ?>', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            tarifSelect.innerHTML = '<option value="">— Tarif par défaut —</option>';
            data.tarifs.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nom + ' (' + formatPrix(t.prix) + ' F)';
                if (tarifSelectionne && String(t.id) === String(tarifSelectionne)) {
                    opt.selected = true;
                }
                tarifSelect.appendChild(opt);
            });
            tarifHint.textContent = data.tarifs.length + ' tarif(s) pour cet événement.';
        })
        .catch(() => {
            tarifSelect.innerHTML = '<option value="">— Tarif par défaut —</option>';
            tarifHint.textContent = 'Erreur de chargement des tarifs.';
        });
    }

    eventSelect.addEventListener('change', function () {
        chargerTarifs(this.value, null);
    });

    const evenementSelectionne = eventSelect.value || '';
    if (evenementSelectionne) {
        chargerTarifs(evenementSelectionne, '<?php echo e(old('tarif_id')); ?>');
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/support/index.blade.php ENDPATH**/ ?>