<?php $__env->startSection('title', 'Vente manuelle'); ?>

<?php $__env->startSection('page-title', 'Vente manuelle'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vente manuelle</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-subtitle', 'Vendre un billet sur place sans paiement en ligne'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">

    <div class="row g-3">
        <!-- Left Column: Form + Summary -->
        <div class="col-12 col-lg-7">
            <form id="venteForm" novalidate>
                <?php echo csrf_field(); ?>

                <!-- Section 1: Event Selection -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5><span style="display:inline-flex;align-items-center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--violet);color:#fff;font-size:0.75rem;font-weight:700;margin-right:0.5rem;">1</span> Sélectionner l'événement</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="mb-0">
                            <label for="evenement_id" class="form-label fw-semibold">Événement <span class="text-danger">*</span></label>
                            <select class="form-select" id="evenement_id" name="evenement_id" required>
                                <option value="">Choisir un événement —</option>
                                <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($evt->id); ?>" data-tarif-min="<?php echo e($evt->tarifs->min('prix')); ?>"><?php echo e($evt->titre); ?> — <?php echo e($evt->date_event->format('d M Y')); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['evenement_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger mt-1" style="font-size:0.85rem;"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Buyer Info -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5><span style="display:inline-flex;align-items-center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--teal);color:#fff;font-size:0.75rem;font-weight:700;margin-right:0.5rem;">2</span> Informations de l'acheteur</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nom_acheteur" class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom_acheteur" name="nom_acheteur" placeholder="Ex: Adja Koné" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telephone" class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="+229 43 70 45 13" required>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="adja@email.com">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Status, Tariff, Payment -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5><span style="display:inline-flex;align-items-center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--menthe);color:var(--sombre);font-size:0.75rem;font-weight:700;margin-right:0.5rem;">3</span> Statut et tarif</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut de l'acheteur <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="categorie" id="cat_externe" value="externe" checked>
                                        <label class="form-check-label" for="cat_externe">Externe</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="categorie" id="cat_etudiant" value="etudiant">
                                        <label class="form-check-label" for="cat_etudiant">Étudiant</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tarif_id" class="form-label fw-semibold">Tarif <span class="text-danger">*</span></label>
                                <select class="form-select" id="tarif_id" name="tarif_id" required>
                                    <option value="">— Sélectionnez d'abord un événement —</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quantite" class="form-label fw-semibold">Quantité</label>
                                <div class="input-group" style="max-width: 160px;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyMinus" style="border-radius: 6px 0 0 6px;">−</button>
                                    <input type="number" class="form-control text-center" id="quantite" name="quantite" value="1" min="1" max="20" style="border-left:0;border-right:0;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyPlus" style="border-radius: 0 6px 6px 0;">+</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="methode_paiement" class="form-label fw-semibold">Moyen de paiement <span class="text-danger">*</span></label>
                                <select class="form-select" id="methode_paiement" name="methode_paiement" required>
                                    <option value="especes">Espèces</option>
                                    <option value="mtn">MTN Mobile Money</option>
                                    <option value="moov">Moov Money</option>
                                    <option value="movimoney">MoviMoney</option>
                                    <option value="celtiis">Celtiis Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Block -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5>Récapitulatif</h5>
                    </div>
                    <div class="panel-card-body">
                        <div id="recapContent" style="font-size:0.9rem;">
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Événement</span>
                                <span class="fw-semibold" id="recapEvent">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Statut</span>
                                <span class="fw-semibold" id="recapStatut">Externe</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Tarif unitaire</span>
                                <span class="fw-semibold" id="recapTarif">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Quantité</span>
                                <span class="fw-semibold" id="recapQuantite">1</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Moyen de paiement</span>
                                <span class="fw-semibold" id="recapPaiement">Espèces</span>
                            </div>
                            <div class="d-flex justify-content-between py-3 mt-1" style="border-top:2px solid #f0f0f0;">
                                <span class="fw-bold" style="font-size:1rem;">Total à encaisser</span>
                                <span class="fw-bold" style="font-size:1.2rem; color: var(--vert);" id="recapTotal">0 FCFA</span>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold text-white mt-3" id="btnSubmit" style="background: var(--vert); border: none; border-radius: 8px; transition: background 0.2s ease;" disabled>
                            <i class="bi bi-check-circle me-2"></i>Enregistrer la vente
                        </button>
                        <p class="text-center text-muted mt-2 mb-0" style="font-size:0.82rem;">
                            <i class="bi bi-whatsapp me-1" style="color: #25D366;"></i>Le billet QR sera envoyé par mail
                        </p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Today's Sales History -->
        <div class="col-12 col-lg-5">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Ventes manuelles du jour</h5>
                    <span style="font-size:0.82rem; color: var(--gris);">
                        <?php echo e($totalVentesJour); ?> vente<?php echo e($totalVentesJour > 1 ? 's' : ''); ?> — <?php echo e(number_format($montantVentesJour, 0, ',', ' ')); ?> FCFA
                    </span>
                </div>
                <div class="panel-card-body" style="padding: 0; max-height: 600px; overflow-y: auto;">
                    <?php $__empty_1 = true; $__currentLoopData = $ventesJour; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="px-3 py-3" style="border-bottom: 1px solid #f5f5f5;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-semibold" style="font-size: 0.92rem;"><?php echo e($vente->nom_acheteur); ?></div>
                                <div class="fw-bold" style="font-size: 0.9rem; color: var(--vert);"><?php echo e(number_format($vente->montant, 0, ',', ' ')); ?> FCFA</div>
                            </div>
                            <div class="text-muted" style="font-size: 0.82rem;">
                                <?php echo e($vente->evenement?->titre ?? '—'); ?> — <?php echo e(ucfirst($vente->categorie)); ?> · <?php echo e(ucfirst($vente->methode_paiement ?? 'Espèces')); ?>

                            </div>
                            <div style="font-size: 0.75rem; color: var(--gris); margin-top: 0.25rem;">
                                <?php echo e($vente->date_achat->diffForHumans()); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-5" style="color: var(--gris);">
                            <i class="bi bi-bag d-block mb-2" style="font-size: 2rem;"></i>
                            <small>Aucune vente manuelle aujourd'hui</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-body text-center p-4">
                <div class="mb-3" style="font-size: 3rem;">🎫</div>
                <h5 class="fw-bold mb-2">Vente enregistrée !</h5>
                <p class="text-muted mb-3" id="successMessage"></p>
                <div id="successTickets" class="text-start mb-3" style="font-size: 0.85rem;"></div>
                <button type="button" class="btn w-100 py-2 fw-bold text-white" style="background: var(--vert); border: none; border-radius: 8px;" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('venteForm');
    const eventSelect = document.getElementById('evenement_id');
    const tarifSelect = document.getElementById('tarif_id');
    const quantiteInput = document.getElementById('quantite');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const btnSubmit = document.getElementById('btnSubmit');
    const methodLabels = {
        'especes': 'Espèces',
        'mtn': 'MTN Mobile Money',
        'moov': 'Moov Money',
        'movimoney': 'MoviMoney',
        'celtiis': 'Celtiis Cash',
    };

    let tarifUnitaire = 0;

    function updateRecap() {
        const eventOption = eventSelect.options[eventSelect.selectedIndex];
        const tarifOption = tarifSelect.options[tarifSelect.selectedIndex];
        const catRadio = document.querySelector('input[name="categorie"]:checked');
        const methodePaiement = document.getElementById('methode_paiement');

        document.getElementById('recapEvent').textContent = eventOption.text || '—';
        document.getElementById('recapStatut').textContent = catRadio ? catRadio.nextElementSibling.textContent : '—';
        document.getElementById('recapTarif').textContent = tarifUnitaire > 0 ? numberFormat(tarifUnitaire) + ' FCFA' : '—';
        document.getElementById('recapQuantite').textContent = quantiteInput.value;
        document.getElementById('recapPaiement').textContent = methodLabels[methodePaiement.value] || '—';

        const total = tarifUnitaire * parseInt(quantiteInput.value || 0);
        document.getElementById('recapTotal').textContent = numberFormat(total) + ' FCFA';

        btnSubmit.disabled = !(eventSelect.value && tarifSelect.value && document.getElementById('nom_acheteur').value && document.getElementById('telephone').value);
    }

    function numberFormat(n) {
        return new Intl.NumberFormat('fr-FR').format(n);
    }

    function loadTarifs() {
        const eventId = eventSelect.value;
        const catRadio = document.querySelector('input[name="categorie"]:checked');
        const cat = catRadio ? catRadio.value : 'externe';

        tarifSelect.innerHTML = '<option value="">Chargement…</option>';

        if (!eventId) {
            tarifSelect.innerHTML = '<option value="">— Sélectionnez d\'abord un événement —</option>';
            tarifUnitaire = 0;
            updateRecap();
            return;
        }

        fetch('<?php echo e(route('ventes-manuelles.tarifs')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ evenement_id: eventId, categorie: cat }),
        })
        .then(r => r.json())
        .then(data => {
            tarifSelect.innerHTML = '<option value="">Choisir un tarif —</option>';
            data.tarifs.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.type.toUpperCase() + ' — ' + numberFormat(t.prix) + ' FCFA';
                opt.dataset.prix = t.prix;
                tarifSelect.appendChild(opt);
            });
        })
        .catch(() => {
            tarifSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
    }

    eventSelect.addEventListener('change', loadTarifs);

    document.querySelectorAll('input[name="categorie"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (eventSelect.value) loadTarifs();
            updateRecap();
        });
    });

    tarifSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        tarifUnitaire = parseFloat(opt.dataset.prix || 0);
        updateRecap();
    });

    document.getElementById('nom_acheteur').addEventListener('input', updateRecap);
    document.getElementById('telephone').addEventListener('input', updateRecap);
    document.getElementById('methode_paiement').addEventListener('change', updateRecap);

    qtyMinus.addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v > 1) { quantiteInput.value = v - 1; updateRecap(); }
    });

    qtyPlus.addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v < 20) { quantiteInput.value = v + 1; updateRecap(); }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {};
        formData.forEach((val, key) => data[key] = val);

        const submitBtn = document.getElementById('btnSubmit');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement…';

        fetch('<?php echo e(route('ventes-manuelles.store')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                document.getElementById('successMessage').textContent = resp.message;
                let ticketsHtml = '<ul class="list-unstyled mb-0">';
                resp.tickets.forEach(t => {
                    ticketsHtml += '<li><strong>Code :</strong> ' + t.code_unique + '</li>';
                });
                ticketsHtml += '</li><li class="fw-bold mt-1">Total : ' + numberFormat(resp.total) + ' FCFA</li></ul>';
                document.getElementById('successTickets').innerHTML = ticketsHtml;
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();

                form.reset();
                document.getElementById('quantite').value = 1;
                document.getElementById('cat_externe').checked = true;
                tarifUnitaire = 0;
                tarifSelect.innerHTML = '<option value="">— Sélectionnez d\'abord un événement —</option>';
                updateRecap();

                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(err => {
            alert('Erreur lors de l\'enregistrement de la vente.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Enregistrer la vente';
        });
    });

    updateRecap();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\ventes-manuelles\create.blade.php ENDPATH**/ ?>