<?php $__env->startSection('title', 'Lot ' . $lot->nom . ' - Super Admin'); ?>
<?php $__env->startSection('page-title', 'Lot : ' . $lot->nom); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
<div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i><?php echo e($lot->nom); ?></span>
                <?php if($lot->estUneDemande()): ?>
                    <span class="sa-badge sa-badge-warning" style="font-size:0.65rem;">DEMANDE ORGANISATEUR</span>
                <?php endif; ?>
                <?php if($lot->estTransmis): ?>
                    <span class="sa-badge sa-badge-success">Transmis le <?php echo e($lot->transmis_at?->format('d/m/Y H:i')); ?></span>
                <?php else: ?>
                    <span class="sa-badge sa-badge-warning" style="background:<?php echo e($lot->statutCouleur()); ?>;color:#fff;"><?php echo e($lot->statutLibelle()); ?></span>
                <?php endif; ?>
            </div>
            <div class="sa-card-body">
                <div class="row text-center">
                    <div class="col-6 col-md-3">
                        <div style="font-size:1.4rem;font-weight:800;color:var(--sa-primary);"><?php echo e($lot->quantite); ?></div>
                        <div style="font-size:0.72rem;color:#888;">Tickets</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div style="font-size:1.4rem;font-weight:800;color:#e74c3c;"><?php echo e($lot->tickets->where('annule', true)->count()); ?></div>
                        <div style="font-size:0.72rem;color:#888;">Annues</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div style="font-size:1.4rem;font-weight:800;color:#27ae60;"><?php echo e($lot->tickets->where('utilise', true)->where('annule', false)->count()); ?></div>
                        <div style="font-size:0.72rem;color:#888;">Scannes</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div style="font-size:1.4rem;font-weight:800;color:#7B3FA0;"><?php echo e($lot->download_count); ?>/<?php echo e(config('app.max_downloads')); ?></div>
                        <div style="font-size:0.72rem;color:#888;">Telechargements</div>
                    </div>
                </div>
                <hr>
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-person"></i></div>
                            <div>
                                <div class="sa-info-label">Organisateur</div>
                                <div class="sa-info-value"><?php echo e($lot->user?->nom ?? '---'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-envelope"></i></div>
                            <div>
                                <div class="sa-info-label">Email</div>
                                <div class="sa-info-value"><?php echo e($lot->user?->email ?? '---'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-calendar-event"></i></div>
                            <div>
                                <div class="sa-info-label">Evenement</div>
                                <div class="sa-info-value"><?php echo e($lot->evenement?->titre ?? '---'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if($lot->evenement?->date_event): ?>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-calendar"></i></div>
                            <div>
                                <div class="sa-info-label">Date</div>
                                <div class="sa-info-value"><?php echo e($lot->evenement->date_event->isoFormat('D MMM YYYY')); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-ticket-perforated"></i></div>
                            <div>
                                <div class="sa-info-label">Tarif</div>
                                <div class="sa-info-value"><?php echo e($lot->tarif?->nom ?? '---'); ?> <?php if($lot->tarif?->prix): ?>(<?php echo e(number_format($lot->tarif->prix, 0, ',', ' ')); ?> FCFA)<?php endif; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-percent"></i></div>
                            <div>
                                <div class="sa-info-label">Commission</div>
                                <div class="sa-info-value"><?php echo e(number_format($lot->commission_pourcentage !== null && $lot->commission_pourcentage !== '' ? $lot->commission_pourcentage : $lot->evenement?->commissionEffective(), 2, ',', ' ')); ?> %</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-cash-stack"></i></div>
                            <div>
                                <div class="sa-info-label">Valeur du lot</div>
                                <div class="sa-info-value"><?php echo e(number_format($lot->quantite * ($lot->tarif?->prix ?? 0), 0, ',', ' ')); ?> FCFA</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sa-card-body" style="border-top:1px solid #f1f2f6;">
                <?php if($lot->estUneDemande() && ! $lot->estTransmis): ?>
                    <div class="alert py-2 px-3 mb-2" style="font-size:0.8rem;background:#fff8e6;border:1px solid #ffe1a8;color:#8a5b00;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <?php if($lot->statut === \App\Models\LotPhysique::STATUT_ATTENTE_PAIEMENT): ?>
                            Demande en attente de paiement : le super admin sera notifié dès la confirmation du règlement.
                        <?php elseif(! $lot->templatePresent()): ?>
                            Aucun visuel fourni : la planche standard (grille de QR codes) sera utilisée. Vous pouvez configurer un template ci-dessous.
                        <?php elseif(! $lot->aUnTemplate()): ?>
                            Template fourni : positionnez le QR code sur l'image de l'organisateur avant de transmettre.
                        <?php else: ?>
                            Template et QR code en place : la planche peut être transmise à l'organisateur.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if (! ($lot->estTransmis)): ?>
                        <button type="button" class="sa-btn sa-btn-success" data-bs-toggle="modal" data-bs-target="#transmettreModal">
                            <i class="bi bi-send-fill"></i> Transmettre a l'organisateur
                        </button>
                        <form action="<?php echo e(route('superadmin.tickets-physiques.supprimer', $lot)); ?>" method="POST" onsubmit="return confirm('Supprimer ce lot et ses <?php echo e($lot->quantite); ?> tickets ? Cette action est irreversible.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-trash"></i> Supprimer le lot</button>
                        </form>
                    <?php endif; ?>
                    <a href="<?php echo e(route('superadmin.tickets-physiques.planche', $lot)); ?>" class="sa-btn" style="background:#3b82f6;border:none;color:#fff;" title="Télécharger la planche PDF de ce lot">
                        <i class="bi bi-file-earmark-pdf"></i> Planche PDF
                    </a>
                    <a href="<?php echo e(route('superadmin.tickets-physiques.template', $lot)); ?>" class="sa-btn" style="background:var(--sa-primary);border:none;color:#fff;" title="<?php echo e($lot->estUneDemande() ? 'Positionner le QR code sur le template de l\'organisateur' : 'Configurer le template du ticket physique'); ?>">
                        <i class="bi bi-image"></i> Template
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Rappels</span>
            </div>
            <div class="sa-card-body" style="font-size:0.82rem;color:#555;line-height:1.6;">
                <ul class="mb-0 ps-3">
                    <li>Annulez un ticket uniquement en cas d'erreur d'impression ou de perte (il ne sera plus scannable).</li>
                    <li>Les tickets annules sont retires de la commission attendue.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-upc-scan me-2" style="color: var(--sa-primary);"></i>Tickets du lot (<?php echo e($lot->tickets->count()); ?>)</span>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="sa-btn sa-btn-sm" style="background:#f1f2f6;border:none;color:#666;" onclick="toggleAllQr()"><i class="bi bi-qr-code"></i> Afficher / masquer les QR</button>
        </div>
    </div>
    <div class="sa-card-body p-0">
        <form id="actionMasseForm" method="POST" action="<?php echo e(route('superadmin.tickets-physiques.action-masse', $lot)); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" id="actionMasseValue" value="">
            <div class="d-flex flex-wrap gap-2 align-items-center px-3 py-2" style="background:#fafafa;border-bottom:1px solid #f1f2f6;">
                <label class="sa-check-inline d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox" id="selectAll" class="form-check-input mt-0" style="width:16px;height:16px;">
                    <span class="small">Tout cocher</span>
                </label>
                <span class="small text-muted" id="selectedCount">0 sélectionné(s)</span>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="sa-btn sa-btn-sm sa-btn-danger" id="btnAnnulerMasse" disabled onclick="submitMasse('annuler')">
                        <i class="bi bi-x-circle"></i> Annuler la sélection
                    </button>
                    <button type="button" class="sa-btn sa-btn-sm sa-btn-danger" id="btnSupprimerMasse" disabled onclick="submitMasse('supprimer')">
                        <i class="bi bi-trash"></i> Supprimer la sélection
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th style="width:32px;"></th>
                            <th>Code</th>
                            <th class="text-center">QR</th>
                            <th class="text-end">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="tickets[]" value="<?php echo e($ticket->id); ?>" class="ticket-check form-check-input mt-0"
                                    <?php if($ticket->utilise): ?> disabled <?php endif; ?>>
                            </td>
                            <td><code style="font-size:0.9rem;"><?php echo e($ticket->code_unique); ?></code></td>
                            <td class="text-center">
                                <img src="<?php echo e($qrs[$ticket->id]); ?>" alt="QR" style="width:44px;height:44px;" class="ticket-qr">
                            </td>
                            <td class="text-end">
                                <?php if($ticket->annule): ?>
                                    <span class="sa-badge sa-badge-danger">Annule</span>
                                <?php elseif($ticket->utilise): ?>
                                    <span class="sa-badge sa-badge-success">Scanne</span>
                                <?php else: ?>
                                    <span class="sa-badge sa-badge-secondary">Valide</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if(!$ticket->annule && !$ticket->utilise): ?>
                                    <form action="<?php echo e(route('superadmin.tickets-physiques.annuler', [$lot, $ticket])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Annuler le ticket <?php echo e($ticket->code_unique); ?> ? Il ne sera plus scannable.')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Annuler le ticket"><i class="bi bi-x-circle"></i> Annuler</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="transmettreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?php echo e(route('superadmin.tickets-physiques.transmettre', $lot)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header" style="background:#542680;color:#fff;border-radius:0;">
                    <h5 class="modal-title"><i class="bi bi-send-fill me-2"></i>Transmettre le lot</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Organisateur</label>
                        <input type="text" class="form-control" value="<?php echo e($lot->user?->nom); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Adresse email de l'organisateur</label>
                        <input type="email" name="email" id="emailOrganisateur" class="form-control" value="<?php echo e($lot->user?->email); ?>" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="hidden" name="notifier" value="0">
                        <input class="form-check-input" type="checkbox" name="notifier" id="notifierOrganisateur" value="1" checked
                            onchange="var e=document.getElementById('emailOrganisateur');if(e){e.required=this.checked;e.disabled=!this.checked;}">
                        <label class="form-check-label small" for="notifierOrganisateur">
                            Notifier l'organisateur par email
                            <span class="text-muted d-block" style="font-size:0.72rem;">La planche PDF du ticket est jointe automatiquement à cet email.</span>
                        </label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Note (facultative)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Un petit message pour l'organisateur..."></textarea>
                    </div>
                    <div class="small text-muted">
                        Le lot sera transmis : l'organisateur recevra une notification dans son espace. Il pourra ensuite télécharger la planche de QR codes (3 téléchargements maximum).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn" style="background:#27ae60;color:#fff;font-weight:600;"><i class="bi bi-send-check"></i> Transmettre</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleAllQr() {
    document.querySelectorAll('.ticket-qr').forEach(function (img) {
        img.style.display = img.style.display === 'none' ? '' : 'none';
    });
}

var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.ticket-check'));
var selectAll = document.getElementById('selectAll');
var countLabel = document.getElementById('selectedCount');
var btnAnnuler = document.getElementById('btnAnnulerMasse');
var btnSupprimer = document.getElementById('btnSupprimerMasse');

function updateSelection() {
    var checked = checkboxes.filter(function (c) { return c.checked; });
    countLabel.textContent = checked.length + ' sélectionné(s)';
    var has = checked.length > 0;
    btnAnnuler.disabled = !has;
    btnSupprimer.disabled = !has;
    if (checkboxes.length > 0) {
        selectAll.checked = checked.length === checkboxes.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
    }
}

selectAll.addEventListener('change', function () {
    checkboxes.forEach(function (c) { c.checked = selectAll.checked; });
    updateSelection();
});

checkboxes.forEach(function (c) { c.addEventListener('change', updateSelection); });
updateSelection();

function submitMasse(action) {
    var checked = checkboxes.filter(function (c) { return c.checked; });
    if (checked.length === 0) return;
    var libelle = action === 'annuler' ? 'annuler' : 'supprimer';
    if (!confirm('Voulez-vous vraiment ' + libelle + ' ' + checked.length + ' ticket(s) sélectionné(s) ?')) return;
    document.getElementById('actionMasseValue').value = action;
    document.getElementById('actionMasseForm').submit();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/lots-physiques/show.blade.php ENDPATH**/ ?>