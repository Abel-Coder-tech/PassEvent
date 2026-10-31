<?php $__env->startSection('title', 'Tableau de bord - ' . $agent->evenement->titre); ?>

<?php $__env->startSection('content'); ?>
<?php $textes = $agent->evenement->getTextes(); ?>
<div class="container py-4">
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <img src="<?php echo e(asset_v('images/logo_paxevent.png')); ?>" alt="PaxEvent" height="60">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i><?php echo e($agent->nom); ?></span>
            <form method="POST" action="<?php echo e(route('agent-vente.logout')); ?>" class="m-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Déconnecter
                </button>
            </form>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success py-2 small" id="successAlert">
        <i class="bi bi-check-circle"></i> <?php echo e(session('success')); ?>

        <?php if(session('ticket_created')): ?>
        <a href="<?php echo e(route('agent-vente.ticket.pdf', session('ticket_created'))); ?>"
            class="btn btn-sm btn-outline-success ms-3" target="_blank">
            <i class="bi bi-filetype-pdf"></i> Télécharger PDF
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4" id="statsCards">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-purple-50">
                <div class="card-body text-center py-3">
                    <div class="text-purple-700 fw-bold fs-3" id="statTotalTickets"><?php echo e($stats['total_tickets']); ?></div>
                    <small class="text-muted">Tickets vendus</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-green-50">
                <div class="card-body text-center py-3">
                    <div class="text-green-700 fw-bold fs-3" id="statMontantTotal"><?php echo e(number_format($stats['montant_total'], 0, ',', ' ')); ?> F</div>
                    <small class="text-muted">Montant total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-blue-50">
                <div class="card-body text-center py-3">
                    <div class="text-blue-700 fw-bold fs-3" id="statAujourdHui"><?php echo e($stats['aujourd_hui']); ?></div>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-amber-50">
                <div class="card-body text-center py-3">
                    <div class="text-amber-700 fw-bold fs-3" id="statMontantAjd"><?php echo e(number_format($stats['montant_ajd'], 0, ',', ' ')); ?> F</div>
                    <small class="text-muted">Montant aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom-0 pt-3 px-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-cart-plus"></i> Nouvelle vente</h6>
                </div>
                <div class="card-body px-3">
                    <form method="POST" action="<?php echo e(route('agent-vente.vendre')); ?>" id="venteForm">
                        <?php echo csrf_field(); ?>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Nom de l'acheteur</label>
                            <input type="text" name="nom_acheteur" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email_acheteur" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">N° WhatsApp <span class="text-muted">(facultatif)</span></label>
                            <input type="tel" name="whatsapp_acheteur" class="form-control form-control-sm" placeholder="+229 XX XX XX XX">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Tarif</label>
                            <select name="tarif_id" class="form-select form-select-sm" required>
                                <option value="">Sélectionner...</option>
                                <?php $__currentLoopData = $agent->evenement->tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tarif->id); ?>">
                                    <?php echo e($tarif->getLabel()); ?> &mdash; <?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Code promo <span class="text-muted">(facultatif)</span></label>
                            <input type="text" name="code_promo" class="form-control form-control-sm" maxlength="50" placeholder="Ex: BIENVENUE10">
                            <?php $__errorArgs = ['code_promo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Paiement</label>
                            <?php $especesOk = $agent->evenement->ventesEspecesActivees(); ?>
                            <?php if(!$agent->evenement->gratuit && !$especesOk): ?>
                                <div class="alert alert-warning py-1 px-2 mb-1" style="font-size:0.78rem;border-radius:6px;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Espèces bloquées — <?php echo e($agent->evenement->ticketsEnLigneCount()); ?>/<?php echo e((int) ceil($agent->evenement->capacite * 15 / 100)); ?> tickets vendus en ligne.
                                </div>
                            <?php endif; ?>
                            <select name="methode_paiement" class="form-select form-select-sm" id="methodePaiement" required>
                                <option value="">Choisir...</option>
                                <option value="cash" <?php echo e(!$especesOk ? 'disabled' : ''); ?>><?php echo e($especesOk ? 'Espèces (Cash)' : 'Espèces (bloquées)'); ?></option>
                                <option value="mobile_money">Mobile</option>
                            </select>
                        </div>

                        <button type="submit" class="btn w-100 text-white py-2 fw-medium" id="btnVente" style="background: #7c3aed;">
                            <i class="bi bi-check-lg"></i> <span id="btnVenteLabel"><?php echo e($textes['type'] === 'spectacle' ? 'Vendre le billet' : 'Enregistrer l\'inscription'); ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Ventes du jour</h6>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">
                            <i class="bi bi-arrow-repeat"></i> Actualisation automatique
                        </small>
                        <a href="<?php echo e(route('agent-vente.historique')); ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-list-ul"></i> Historique
                        </a>
                    </div>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">Heure</th>
                                    <th>Acheteur</th>
                                    <th>WhatsApp</th>
                                    <th>N° paiement</th>
                                    <th>Tarif</th>
                                    <th>Montant</th>
                                    <th>Paiement</th>
                                    <th class="pe-3">PDF</th>
                                </tr>
                            </thead>
                            <tbody id="historiqueBody">
                                <?php $__empty_1 = true; $__currentLoopData = $ticketsAujourdHui; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-3 small"><?php echo e($ticket->date_achat->format('H:i')); ?></td>
                                    <td class="small"><?php echo e($ticket->nom_acheteur); ?></td>
                                    <td class="small"><?php echo e($ticket->whatsapp_acheteur ?? '—'); ?></td>
                                    <td class="small"><?php echo e($ticket->telephone_paiement ?? $ticket->telephone_acheteur ?? '—'); ?></td>
                                    <td class="small"><?php echo e($ticket->tarif?->getLabel() ?? 'N/A'); ?></td>
                                    <?php if($ticket->montant > 0): ?>
                                    <td class="small fw-medium"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</td>
                                    <td class="small"><?php echo e(\App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement)); ?></td>
                                    <?php else: ?>
                                    <td class="small text-muted">Gratuit</td>
                                    <td class="small">—</td>
                                    <?php endif; ?>
                                    <td class="pe-3">
                                        <a href="<?php echo e(route('agent-vente.ticket.pdf', $ticket)); ?>"
                                            class="btn btn-sm btn-outline-secondary py-0 px-2" target="_blank">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3 small">
                                        <i class="bi bi-inbox"></i> Aucune vente aujourd'hui
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-purple-50 { background: #f5f3ff; }
.text-purple-700 { color: #7c3aed; }
.bg-green-50 { background: #f0fdf4; }
.text-green-700 { color: #16a34a; }
.bg-blue-50 { background: #eff6ff; }
.text-blue-700 { color: #2563eb; }
.bg-amber-50 { background: #fffbeb; }
.text-amber-700 { color: #d97706; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let historiqueInterval;

function chargerHistorique() {
    fetch('<?php echo e(route("agent-vente.historique.json")); ?>')
        .then(r => r.json())
        .then(data => {
            document.getElementById('statTotalTickets').textContent = data.total_tickets;
            document.getElementById('statMontantTotal').textContent = data.montant_total;
            document.getElementById('statAujourdHui').textContent = data.aujourd_hui;
            document.getElementById('statMontantAjd').textContent = data.montant_ajd;

            const tbody = document.getElementById('historiqueBody');
            if (data.tickets.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3 small"><i class="bi bi-inbox"></i> Aucune vente aujourd\'hui</td></tr>';
            } else {
                tbody.innerHTML = data.tickets.map(t => `
                    <tr>
                        <td class="ps-3 small">${escapeHtml(t.date)}</td>
                        <td class="small">${escapeHtml(t.nom)}</td>
                        <td class="small">${escapeHtml(t.tarif)}</td>
                        ${t.montant_val > 0 ? `
                        <td class="small fw-medium">${escapeHtml(t.montant)}</td>
                        <td class="small">${escapeHtml(t.methode_label)}</td>
                        ` : `
                        <td class="small text-muted">Gratuit</td>
                        <td class="small">—</td>
                        `}
                        <td class="pe-3">
                            <a href="/vente/tickets/${escapeHtml(t.id)}/pdf" class="btn btn-sm btn-outline-secondary py-0 px-2" target="_blank">
                                <i class="bi bi-filetype-pdf"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    historiqueInterval = setInterval(chargerHistorique, 10000);

    const methodeSelect = document.getElementById('methodePaiement');
    const btnLabel = document.getElementById('btnVenteLabel');
    if (methodeSelect && btnLabel) {
        const labelVente = <?php echo json_encode($textes['type'] === 'spectacle' ? 'Vendre le billet' : "Enregistrer l'inscription", 15, 512) ?>;
        methodeSelect.addEventListener('change', function() {
            btnLabel.textContent = this.value === 'mobile_money' ? 'Payer via FedaPay' : labelVente;
        });
    }

    setTimeout(() => {
        const alert = document.getElementById('successAlert');
        if (alert) alert.style.display = 'none';
    }, 8000);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.agent-vente', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/agent-vente/dashboard.blade.php ENDPATH**/ ?>