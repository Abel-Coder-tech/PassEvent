<?php $__env->startSection('title', 'Generer un lot de tickets physiques - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Generer un lot de tickets physiques'); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('error')): ?>
<div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-plus-square-fill me-2" style="color: var(--sa-primary);"></i>Nouveau lot</span>
            </div>
            <div class="sa-card-body">
                <form action="<?php echo e(route('superadmin.tickets-physiques.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Organisateur</label>
                            <select name="user_id" id="sel_organisateur" class="sa-form-control" required>
                                <option value="">-- Choisir un organisateur --</option>
                                <?php $__currentLoopData = $organisateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($org->id); ?>"><?php echo e($org->nom); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Nom du lot</label>
                            <input type="text" name="nom" class="sa-form-control" placeholder="Ex : Lot guichet principal" value="<?php echo e(old('nom')); ?>" required>
                            <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Evenement</label>
                            <select name="evenement_id" id="sel_evenement" class="sa-form-control" required disabled>
                                <option value="">-- Choisir un organisateur d'abord --</option>
                            </select>
                            <?php $__errorArgs = ['evenement_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Tarif</label>
                            <select name="tarif_id" id="sel_tarif" class="sa-form-control" disabled>
                                <option value="">-- Choisir un evenement d'abord --</option>
                            </select>
                            <?php $__errorArgs = ['tarif_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Commission (en %)</label>
                            <input type="number" name="commission_pourcentage" id="inp_commission" class="sa-form-control" min="0" max="100" step="0.01" value="<?php echo e(old('commission_pourcentage')); ?>" placeholder="Defaut : commission evenement">
                            <?php $__errorArgs = ['commission_pourcentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted">Laissez vide pour reprendre le taux de l'evenement. Le prix du ticket est celui du tarif choisi.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.8rem;font-weight:600;">Quantite</label>
                            <input type="number" name="quantite" id="inp_quantite" class="sa-form-control" min="1" max="500" value="<?php echo e(old('quantite')); ?>" placeholder="Nombre de tickets" required>
                            <?php $__errorArgs = ['quantite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-magic"></i> Generer le lot</button>
                            <a href="<?php echo e(route('superadmin.tickets-physiques')); ?>" class="sa-btn" style="background:#f1f2f6;border:none;color:#666;">Annuler</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Informations</span>
            </div>
            <div class="sa-card-body" style="font-size:0.82rem;color:#555;line-height:1.6;">
                <ul class="mb-0 ps-3">
                    <li>Les tickets physiques ne comptent pas dans la capacite de l'evenement.</li>
                    <li>Chaque ticket recoit un code unique <code>PAX-XXXXX</code> scannable a l'entree.</li>
                    <li>La commission est calculee au taux effectif de l'evenement et suivie separement.</li>
                    <li>Le lot doit etre transmis a l'organisateur avant qu'il ne puisse telecharger la planche de QR codes.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const selOrg = document.getElementById('sel_organisateur');
    const selEvt = document.getElementById('sel_evenement');
    const selTar = document.getElementById('sel_tarif');
    const inpCommission = document.getElementById('inp_commission');

    function reset(select, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        select.disabled = true;
    }

    function erreur(select, message) {
        select.innerHTML = '<option value="">' + message + '</option>';
        select.disabled = true;
    }

    function loadTarifs(evenementId) {
        if (!evenementId) {
            reset(selTar, '-- Aucun tarif --');
            return;
        }
        reset(selTar, '-- Aucun tarif --');

        fetch('<?php echo e(route("superadmin.tickets-physiques.tarifs")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ evenement_id: evenementId }),
        })
        .then(r => r.ok ? r.json() : Promise.reject('HTTP ' + r.status))
        .then(data => {
            inpCommission.value = '';
            const tarifs = data.tarifs || [];
            if (!tarifs.length) {
                if (data.gratuit) {
                    selTar.innerHTML = '<option value="">Evenement gratuit (tarif auto)</option>';
                } else {
                    selTar.innerHTML = '<option value="">-- Aucun tarif --</option>';
                }
                if (data.commission) inpCommission.value = data.commission;
                return;
            }
            selTar.innerHTML = '<option value="">-- Choisir un tarif --</option>' + tarifs.map(t => {
                var etat = (t.statut && t.statut !== 'actif') ? ' (' + t.statut + ')' : '';
                return '<option value="' + t.id + '">' + escapeHtml(t.nom) + ' - ' + escapeHtml(t.prix) + ' FCFA' + escapeHtml(etat) + '</option>';
            }).join('');
            selTar.disabled = false;
            if (data.commission) inpCommission.value = data.commission;
        })
        .catch(err => erreur(selTar, 'Erreur chargement tarifs (' + err + ')'));
    }

    selOrg.addEventListener('change', function () {
        reset(selEvt, '-- Aucun evenement --');
        reset(selTar, '-- Choisir un evenement --');
        inpCommission.value = '';
        if (!this.value) return;

        fetch('<?php echo e(route("superadmin.tickets-physiques.evenements")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ user_id: this.value }),
        })
        .then(r => r.ok ? r.json() : Promise.reject('HTTP ' + r.status))
        .then(data => {
            const evenements = data.evenements || [];
            if (!evenements.length) {
                selEvt.innerHTML = '<option value="">-- Aucun evenement --</option>';
                return;
            }
            selEvt.innerHTML = evenements.map(e =>
                '<option value="' + e.id + '">' + escapeHtml(e.titre) + '</option>'
            ).join('');
            selEvt.disabled = false;
            // La première option est présélectionnée par le navigateur (surtout si un seul
            // événement) : on charge donc directment ses tarifs, sinon `change` ne se déclenche pas.
            loadTarifs(selEvt.value);
        })
        .catch(err => erreur(selEvt, 'Erreur chargement evenements (' + err + ')'));
    });

    selEvt.addEventListener('change', function () {
        reset(selTar, '-- Choisir un evenement --');
        if (!this.value) return;
        loadTarifs(this.value);
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/lots-physiques/create.blade.php ENDPATH**/ ?>