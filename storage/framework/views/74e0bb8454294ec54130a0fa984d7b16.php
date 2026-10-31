<?php $__env->startSection('title', 'Vente physique - Billetterie'); ?>
<?php $__env->startSection('page-title', 'Vente physique'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active" aria-current="page">Vente physique</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content">
    <style>
    .steps-bar { display: flex; align-items: center; margin-bottom: .9rem; }
    .step-item { display: flex; align-items: center; flex: 1; min-width: 0; }
    .step-dot { width: 28px; height: 28px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: .78rem; font-weight: 700; flex-shrink: 0; transition: all .3s; }
    .step-label { font-size: .7rem; color: #6c757d; margin-left: .4rem; white-space: nowrap; }
    .step-line { flex: 1; height: 2px; background: #e9ecef; margin: 0 .5rem; min-width: 12px; transition: background .3s; border-radius: 2px; }
    .step-item.done .step-dot { background: #198754; color: #fff; }
    .step-item.done .step-label { color: #198754; }
    .step-item.done .step-line { background: #198754; }
    .step-item.active .step-dot { background: #542680; color: #fff; box-shadow: 0 0 0 4px rgba(84,38,128,.15); }
    .step-item.active .step-label { color: #542680; font-weight: 700; }

    #modalGenerer .modal-dialog { flex-direction: column; align-items: stretch; width: min(500px, 97vw); max-width: min(500px, 97vw); }
    #modalGenerer .modal-dialog > * { width: 100%; }
    #modalGenerer .modal-content { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
    #modalGenerer .modal-body { max-height: calc(100vh - 210px); overflow-y: auto; }

    .event-card { cursor: pointer; border: 1.5px solid #e9ecef; border-radius: 12px; padding: .8rem .9rem; transition: all .18s; height: 100%; position: relative; }
    .event-card:hover { border-color: #c4a6dd; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(84,38,128,.08); }
    .event-card.selected { border-color: #542680; background: rgba(84,38,128,.04); }
    .event-check { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid #dee2e6; display: flex; align-items: center; justify-content: center; color: transparent; font-size: .85rem; transition: all .18s; flex-shrink: 0; }
    .event-card.selected .event-check { background: #542680; border-color: #542680; color: #fff; }

    .tarif-row { padding: .8rem 0; border-bottom: 1px solid #f1f0f3; }
    .tarif-row:last-child { border-bottom: none; }
    .stepper { display: inline-flex; align-items: center; background: #f4f2f7; border-radius: 10px; padding: 3px; }
    .st-btn { width: 28px; height: 28px; border: none; background: #fff; border-radius: 8px; color: #542680; font-weight: 700; box-shadow: 0 1px 3px rgba(0,0,0,.08); display: flex; align-items: center; justify-content: center; transition: all .15s; }
    .st-btn:hover { background: #542680; color: #fff; }
    .stepper input { width: 52px; border: none; background: transparent; text-align: center; font-weight: 700; color: #1d1d1f; outline: none; -moz-appearance: textfield; }
    .stepper input::-webkit-outer-spin-button, .stepper input::-webkit-inner-spin-button { -webkit-appearance: none; }

    .recap-ligne { display: flex; justify-content: space-between; align-items: center; padding: .55rem 0; border-bottom: 1px dashed #e9ecef; font-size: .88rem; }
    .recap-ligne:last-child { border-bottom: none; }
    .total-block { background: linear-gradient(135deg, rgba(84,38,128,.06), rgba(124,58,237,.06)); border-radius: 12px; padding: .8rem 1rem; }
    </style>

    <?php if(session('success')): ?>
    <div class="alert alert-success py-2 small"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="alert alert-danger py-2 small"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <!-- Mini-dashboard -->
    <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
        <div class="col">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);"><i class="bi bi-ticket-perforated" style="color: var(--violet);"></i></div>
                <div class="metric-label">Tickets generes</div>
                <div class="metric-value" style="font-size:1.3rem;"><?php echo e($nbTickets); ?></div>
                <div class="metric-subtitle">Dont <?php echo e($nbAnnules); ?> annule(s)</div>
            </div>
        </div>
        <div class="col">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-upc-scan" style="color: var(--vert);"></i></div>
                <div class="metric-label">Scannes a l'entree</div>
                <div class="metric-value" style="font-size:1.3rem; color: var(--vert);"><?php echo e($nbScannes); ?></div>
                <div class="metric-subtitle"><?php echo e(max(0, $nbTickets - $nbAnnules - $nbScannes)); ?> restant(s)</div>
            </div>
        </div>
        <div class="col">
            <div class="metric-card" style="border-top-color: var(--orange);">
                <div class="metric-icon" style="background: rgba(241,159,29,0.1);"><i class="bi bi-cash-coin" style="color: var(--orange);"></i></div>
                <div class="metric-label">Valeur totale</div>
                <div class="metric-value" style="font-size:1.3rem;"><?php echo e(number_format($recettesPhysiques, 0, ',', ' ')); ?> F</div>
                <div class="metric-subtitle">Valeur faciale generee</div>
            </div>
        </div>
        <div class="col">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-icon" style="background: rgba(152,145,155,0.1);"><i class="bi bi-percent" style="color: var(--gris);"></i></div>
                <div class="metric-label">Commission</div>
                <div class="metric-value" style="font-size:1.3rem;"><?php echo e(number_format($commissionTotale, 0, ',', ' ')); ?> F</div>
                <div class="metric-subtitle">Reglee avant generation / demande</div>
            </div>
        </div>
    </div>

    <?php if($lots->isEmpty()): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-ticket-perforated" style="font-size:3rem;"></i>
        <p class="mt-2">Aucun lot de tickets physiques pour le moment.</p>
        <p style="font-size:0.85rem;">Generez vos QR codes vous-meme en quelques clics, ou faites une demande a l'equipe PaxEvent.</p>
        <button type="button" class="btn btn-sm text-white" style="background:#7c3aed;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="ouvrirModal()">
            <i class="bi bi-qr-code me-1"></i> Générer mes QR codes
        </button>
        <button type="button" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="openDemande('ticket_physique')">
            <i class="bi bi-envelope me-1"></i> Demander des QR codes
        </button>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong><i class="bi bi-stack me-1"></i> Mes lots de tickets physiques</strong>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm text-white" style="background:#7c3aed;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="ouvrirModal()">
                    <i class="bi bi-qr-code me-1"></i> Générer mes QR codes
                </button>
                <button type="button" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="openDemande('ticket_physique')">
                    <i class="bi bi-envelope me-1"></i> Demander des QR codes
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Lot</th>
                        <th>Evenement</th>
                        <th>Tarif</th>
                        <th class="text-center">Tickets</th>
                        <th class="text-center">Annues</th>
                        <th class="text-center">Scannes</th>
                        <th>Statut</th>
                        <th class="text-center">Télechargements</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $lots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="ps-3 fw-medium">
                            <?php echo e($lot->nom); ?>

                            <?php if($lot->auto_genere): ?><span class="badge" style="background:#7c3aed;font-size:.62rem;">AUTO</span>
                            <?php else: ?><span class="badge" style="background:#7B3FA0;font-size:.62rem;">DEMANDE</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if($lot->evenement): ?>
                            <a href="<?php echo e(route('admin.evenements.show', $lot->evenement)); ?>" class="text-decoration-none"><?php echo e($lot->evenement->titre); ?></a>
                            <?php else: ?>
                            ---
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($lot->tarif?->nom ?? '---'); ?></td>
                        <td class="text-center"><?php echo e($lot->nb_tickets); ?></td>
                        <td class="text-center">
                            <?php if($lot->nb_annules > 0): ?><span class="badge bg-danger"><?php echo e($lot->nb_annules); ?></span><?php else: ?> 0 <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($lot->nb_scannes > 0): ?><span class="badge bg-success"><?php echo e($lot->nb_scannes); ?></span><?php else: ?> 0 <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge text-white" style="background:<?php echo e($lot->statutCouleur()); ?>;"><?php echo e($lot->statutLibelle()); ?></span>
                        </td>
                        <td class="text-center"><?php echo e($lot->download_count); ?>/<?php echo e(config('app.max_downloads')); ?></td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1 align-items-center">
                            <?php if($lot->statut === \App\Models\LotPhysique::STATUT_ATTENTE_PAIEMENT && $lot->reference_paiement): ?>
                                <a href="<?php echo e(route('admin.lots-physiques.checkout', $lot->reference_paiement)); ?>" class="btn btn-sm text-white" style="background:#f59e0b;">
                                    <i class="bi bi-credit-card"></i> Payer
                                </a>

                            <?php elseif($lot->estTransmis && $lot->nb_tickets - $lot->nb_annules > 0): ?>
                                <a href="<?php echo e(route('admin.lots-physiques.download', $lot)); ?>" class="btn btn-sm text-white" style="background:#7c3aed;">
                                    <i class="bi bi-download"></i> Planche PDF
                                </a>
                                
                                <?php if (! ($lot->estUneDemande())): ?>
                                <a href="<?php echo e(route('admin.lots-physiques.template', $lot)); ?>" class="btn btn-sm btn-outline-primary" title="Configurer le template du ticket">
                                    <i class="bi bi-image"></i>
                                </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.78rem;">
                                    <?php if($lot->estTransmis): ?> Aucun ticket valide <?php else: ?> En attente de transmission <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <?php if($lot->nb_scannes == 0 && !($lot->estUneDemande() && $lot->statut !== \App\Models\LotPhysique::STATUT_ATTENTE_PAIEMENT)): ?>
                            <form action="<?php echo e(route('admin.lots-physiques.destroy', $lot)); ?>"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce lot et ses tickets définitivement ?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            <?php echo e($lots->links()); ?>

        </div>
    </div>
    <?php endif; ?>

    <div class="alert alert-light border mt-3 py-2 small text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Les tickets physiques ne comptent pas dans la capacité de vos événements. Ils sont scannables a l'entrée comme les tickets en ligne. La commission y afferente est suivie separement (rubrique ci-dessus).
    </div>
</div>

<!-- Modal d'auto-génération : barre de progression hors carte, même largeur que le modal -->
<div class="modal fade" id="modalGenerer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="steps-bar px-1" id="stepsBarGen"></div>

        <form method="POST" action="<?php echo e(route('admin.lots-physiques.commander')); ?>" id="formCommande">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="evenement_id" id="evenement_id" value="">
            <div id="quantitesContainer"></div>

            <div class="modal-content">
                <div class="modal-header py-3 px-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-qr-code me-1" style="color:#542680;"></i> Générer mes QR codes</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body px-3">
                    <?php if($errors->any() && !old('objet')): ?>
                    <div class="alert alert-danger py-2 small"><?php echo e($errors->all()[0]); ?></div>
                    <?php endif; ?>

                    <!-- Étape Événement : cartes -->
                    <div id="panelChoix">
                        <p class="text-muted small mb-2">Choisissez l'événement concerné :</p>
                        <?php if($evenementsAuto->isEmpty()): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-x" style="font-size:2.2rem;"></i>
                            <p class="mt-2 mb-0 small">Aucun événement à venir avec des tarifs actifs.</p>
                        </div>
                        <?php else: ?>
                        <div class="row g-2">
                            <?php $__currentLoopData = $evenementsAuto; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-12 col-md-6">
                                <div class="event-card" data-id="<?php echo e($ev['id']); ?>" onclick="selectionnerEvenement(<?php echo e($ev['id']); ?>, this)">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div style="min-width:0;">
                                            <div class="fw-semibold text-truncate"><?php echo e($ev['titre']); ?></div>
                                            <div class="text-muted" style="font-size:.78rem;">
                                                <?php if($ev['date_event']): ?><i class="bi bi-clock me-1"></i><?php echo e($ev['date_event']); ?><?php else: ?><i class="bi bi-infinity me-1"></i>Date libre <?php endif; ?>
                                                <?php if($ev['gratuit']): ?><span class="badge bg-success ms-1" style="font-size:.62rem;">Gratuit</span><?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="event-check"><i class="bi bi-check-lg"></i></span>
                                    </div>
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        <?php $__currentLoopData = $ev['tarifs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-light text-dark border" style="font-size:.66rem;"><?php echo e($t['nom']); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Étape Événement : quantités (révélées après sélection) -->
                    <div id="blocQtes" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-1">
                            <p class="text-muted small mb-0">Quantités pour <strong id="qtesEventTitre"></strong> :</p>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size:.75rem;" onclick="reinitialiserChoix()"><i class="bi bi-pencil-square me-1"></i>Changer</button>
                        </div>
                        <div class="d-flex justify-content-end mb-1">
                            <small class="text-muted">Commission PaxEvent : <strong><?php echo e(number_format($tauxCommission, 1, ',', '')); ?> %</strong> du prix du billet</small>
                        </div>
                        <div id="tarifsListe"></div>
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top">
                            <span class="small text-muted"><strong id="totalBillets" style="color:#1d1d1f;">0</strong> billet(s)</span>
                            <span class="small" id="ligneTotalPayer">Total à payer : <strong id="totalPayer" style="font-size:1.15rem;color:#542680;">0 F</strong></span>
                        </div>
                        <div class="row g-2 align-items-center mt-2">
                            <div class="col-auto" style="width:38px;"><i class="bi bi-envelope text-muted"></i></div>
                            <div class="col">
                                <input type="email" name="email_reception" class="form-control form-control-sm <?php $__errorArgs = ['email_reception'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('email_reception', $emailDefaut)); ?>" placeholder="Email de réception des planches (optionnel)">
                                <?php $__errorArgs = ['email_reception'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Récapitulatif (uniquement flux gratuit : rien à payer) -->
                    <div id="panelRecapGratuit" style="display:none;">
                        <div id="recapLignesGratuit" class="mb-2"></div>
                        <div class="total-block d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold small">Total à payer</span>
                            <strong style="font-size:1.25rem;color:#198754;">0 F</strong>
                        </div>
                        <div class="alert alert-success border py-2 mb-0" style="font-size:.78rem;">
                            <i class="bi bi-check-circle me-1"></i>
                            Événement gratuit : aucun paiement requis. Vos planches PDF seront générées immédiatement.
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-sm btn-secondary-custom" id="btnRetourGen" style="visibility:hidden;" onclick="retourEtape()">Retour</button>
                    <button type="button" class="btn btn-sm btn-secondary-custom" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-sm text-white" id="btnPrincipal" style="background:#542680;border-radius:8px;font-weight:600;min-width:190px;" disabled onclick="actionPrincipale()">Continuer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de résultat : succès (téléchargement) / échec / attente -->
<div class="modal fade" id="modalResultat" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center" style="border:none;border-radius:16px;">
            <div class="modal-body p-4">
                <div id="iconeResultat" class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;border-radius:50%;font-size:1.8rem;background:rgba(25,135,84,.12);color:#198754;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h5 class="fw-bold mb-2" id="titreResultat"></h5>
                <p class="text-muted small mb-3" id="texteResultat"></p>
                <div id="listeTelecharges" class="text-start mb-3"></div>
                <button type="button" class="btn btn-sm text-white px-4" style="background:#542680;border-radius:8px;font-weight:600;" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const EVENEMENTS = <?php echo json_encode($evenementsAuto, 15, 512) ?>;
const TAUX = <?php echo e($tauxCommission); ?>;
let evenementCourant = null;
let etape = 1;
let chemin = 'payant';
let barLabels = [];

function ouvrirModal() {
    new bootstrap.Modal(document.getElementById('modalGenerer')).show();
}

function construireBarre(labels) {
    barLabels = labels;
    let html = '';
    labels.forEach((lbl, i) => {
        html += '<div class="step-item" id="gstep' + i + '">' +
            '<span class="step-dot">' + (i + 1) + '</span>' +
            '<span class="step-label">' + lbl + '</span>' +
            (i < labels.length - 1 ? '<span class="step-line"></span>' : '') +
            '</div>';
    });
    document.getElementById('stepsBarGen').innerHTML = html;
    marquerEtapes(etape - 1);
}

function marquerEtapes(actifIdx) {
    barLabels.forEach((_, i) => {
        const el = document.getElementById('gstep' + i);
        if (!el) return;
        el.classList.toggle('done', i < actifIdx);
        el.classList.toggle('active', i === actifIdx);
        el.querySelector('.step-dot').innerHTML = i < actifIdx ? '<i class="bi bi-check"></i>' : (i + 1);
    });
}

function selectionnerEvenement(id, el) {
    document.querySelectorAll('.event-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    evenementCourant = EVENEMENTS.find(e => e.id === id);
    document.getElementById('evenement_id').value = id;
    document.getElementById('blocQtes').style.display = '';
    document.getElementById('qtesEventTitre').textContent = evenementCourant.titre;

    const liste = document.getElementById('tarifsListe');
    liste.innerHTML = '';
    evenementCourant.tarifs.forEach(t => {
        const div = document.createElement('div');
        div.className = 'tarif-row';
        div.innerHTML =
            '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">' +
              '<div><div class="fw-semibold">' + escapeHtml(t.nom) + '</div>' +
              '<small class="text-muted">' + fmt(t.prix) + ' F / billet</small></div>' +
              '<div class="stepper">' +
                '<button type="button" class="st-btn" onclick="ajuster(this, -1)">&minus;</button>' +
                '<input type="number" min="0" max="2000" value="0" data-tarif="' + t.id + '" data-prix="' + t.prix + '" oninput="recalc()">' +
                '<button type="button" class="st-btn" onclick="ajuster(this, 1)">+</button>' +
              '</div>' +
            '</div>' +
            '<div class="ligne-total small text-muted mt-1">Valeur billets : <span class="lv">0 F</span> &middot; Commission : <span class="lc fw-semibold">0 F</span></div>';
        liste.appendChild(div);
    });

    recalc();
}

function reinitialiserChoix() {
    evenementCourant = null;
    document.querySelectorAll('.event-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('blocQtes').style.display = 'none';
    document.getElementById('evenement_id').value = '';
    recalc();
}

function ajuster(btn, delta) {
    const input = btn.parentElement.querySelector('input');
    input.value = Math.max(0, Math.min(2000, (parseInt(input.value) || 0) + delta));
    recalc();
}

function recalc() {
    let totalBillets = 0, totalCommission = 0;
    document.querySelectorAll('#tarifsListe .tarif-row').forEach(row => {
        const input = row.querySelector('input');
        const qte = Math.max(0, Math.min(2000, parseInt(input.value) || 0));
        input.value = qte;
        const prix = parseFloat(input.dataset.prix);
        const valeur = qte * prix;
        const commission = Math.round(valeur * TAUX) / 100;
        row.querySelector('.lv').textContent = fmt(valeur) + ' F';
        row.querySelector('.lc').textContent = fmt(commission) + ' F';
        totalBillets += qte;
        totalCommission += commission;
    });
    totalCommission = Math.round(totalCommission * 100) / 100;
    document.getElementById('totalBillets').textContent = totalBillets;
    document.getElementById('totalPayer').textContent = fmt(totalCommission) + ' F';

    // Barre adaptée au parcours : gratuit (rien à payer) ou payant (FedaPay)
    let nouveauChemin = chemin;
    if (totalBillets > 0) {
        nouveauChemin = totalCommission <= 0 ? 'gratuit' : 'payant';
    }
    if (! barLabels.length || nouveauChemin !== chemin) {
        chemin = nouveauChemin;
        construireBarre(chemin === 'gratuit'
            ? ['Événement', 'Récapitulatif', 'Téléchargement']
            : ['Événement', 'Paiement', 'Téléchargement']);
    }
    majBouton();
    return { totalBillets, totalCommission };
}

function majBouton() {
    const btn = document.getElementById('btnPrincipal');
    if (etape === 2) {
        btn.setAttribute('type', 'submit');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-1"></i> Générer mes QR codes';
        return;
    }
    let totalBillets = 0;
    document.querySelectorAll('#tarifsListe input').forEach(i => { totalBillets += parseInt(i.value) || 0; });
    btn.disabled = !(evenementCourant && totalBillets > 0);
    if (chemin === 'gratuit') {
        btn.setAttribute('type', 'button');
        btn.innerHTML = 'Continuer';
    } else {
        // Payant : soumet le formulaire -> creation de la commande puis ouverture de FedaPay
        btn.setAttribute('type', 'submit');
        btn.innerHTML = '<i class="bi bi-shield-lock me-1"></i> Payer avec FedaPay';
    }
}

function actionPrincipale() {
    if (etape === 1 && chemin === 'gratuit') {
        const t = recalc();
        if (t.totalBillets === 0) return;
        remplirQuantites();
        document.getElementById('recapLignesGratuit').innerHTML = lignesRecapHtml();
        etape = 2;
        document.getElementById('panelRecapGratuit').style.display = '';
        document.getElementById('blocQtes').style.display = 'none';
        document.getElementById('btnRetourGen').style.visibility = 'visible';
        marquerEtapes(1);
        majBouton();
    }
}

function retourEtape() {
    if (etape !== 2) return;
    etape = 1;
    document.getElementById('panelRecapGratuit').style.display = 'none';
    document.getElementById('blocQtes').style.display = '';
    document.getElementById('btnRetourGen').style.visibility = 'hidden';
    marquerEtapes(0);
    majBouton();
}

function lignesRecapHtml() {
    let html = '';
    document.querySelectorAll('#tarifsListe .tarif-row').forEach(row => {
        const input = row.querySelector('input');
        const qte = parseInt(input.value) || 0;
        if (qte <= 0) return;
        const nom = row.querySelector('.fw-semibold').textContent.trim();
        const prix = parseFloat(input.dataset.prix);
        html += '<div class="recap-ligne"><span><strong>' + qte + '</strong> &times; ' + escapeHtml(nom) +
            ' <small class="text-muted">(' + fmt(prix) + ' F/u)</small></span>' +
            '<span class="fw-semibold">' + fmt(qte * prix) + ' F</span></div>';
    });
    return html;
}

function remplirQuantites() {
    const container = document.getElementById('quantitesContainer');
    container.innerHTML = '';
    document.querySelectorAll('#tarifsListe input').forEach(input => {
        const qte = parseInt(input.value) || 0;
        if (qte <= 0) return;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'quantites[' + input.dataset.tarif + ']';
        hidden.value = qte;
        container.appendChild(hidden);
    });
}

document.getElementById('formCommande').addEventListener('submit', remplirQuantites);

function afficherResultat(cfg) {
    const icone = document.getElementById('iconeResultat');
    icone.innerHTML = cfg.icone;
    icone.style.background = cfg.fond;
    icone.style.color = cfg.couleur;
    document.getElementById('titreResultat').textContent = cfg.titre;
    document.getElementById('texteResultat').textContent = cfg.texte;

    const liste = document.getElementById('listeTelecharges');
    liste.innerHTML = '';
    (cfg.lots || []).forEach(lot => {
        liste.insertAdjacentHTML('beforeend',
            '<div class="d-flex justify-content-between align-items-center py-1 border-bottom">' +
            '<span class="small"><strong>' + lot.quantite + '</strong>&nbsp;&times;&nbsp;' + escapeHtml(lot.nom) + '</span>' +
            (lot.telecharger
                ? '<a href="' + lot.telecharger + '" class="btn btn-sm text-white" style="background:#542680;border-radius:8px;"><i class="bi bi-download"></i> Planche PDF</a>'
                : '') +
            '</div>');
    });
    new bootstrap.Modal(document.getElementById('modalResultat')).show();
}

const resultatSucces = <?php echo json_encode(session('qr_succes'), 15, 512) ?>;
const resultatEchec = <?php echo json_encode(session('qr_echec'), 15, 512) ?>;
const resultatAttente = <?php echo json_encode(session('qr_attente'), 15, 512) ?>;

if (resultatSucces) afficherResultat({
    icone: '<i class="bi bi-check-lg"></i>', fond: 'rgba(25,135,84,.12)', couleur: '#198754',
    titre: 'Vos QR codes sont prêts !',
    texte: 'Téléchargez vos planches ci-dessous. Un email de confirmation vous a également été envoyé.',
    lots: resultatSucces.lots,
});
if (resultatEchec) afficherResultat({
    icone: '<i class="bi bi-x-lg"></i>', fond: 'rgba(220,53,69,.12)', couleur: '#dc3545',
    titre: 'Paiement non abouti',
    texte: resultatEchec,
});
if (resultatAttente) afficherResultat({
    icone: '<i class="bi bi-hourglass-split"></i>', fond: 'rgba(255,193,7,.18)', couleur: '#b7791f',
    titre: 'Vérification en cours',
    texte: resultatAttente,
});

function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n * 100) / 100);
}

function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

construireBarre(['Événement', 'Paiement', 'Téléchargement']);
majBouton();

// Réouverture automatique avec restauration si erreurs de validation
<?php if($errors->any() && old('evenement_id') && !old('objet')): ?>
document.addEventListener('DOMContentLoaded', function () {
    ouvrirModal();
    const evId = <?php echo e((int) old('evenement_id')); ?>;
    const carte = document.querySelector('.event-card[data-id="' + evId + '"]');
    if (carte) {
        selectionnerEvenement(evId, carte);
        <?php $__currentLoopData = old('quantites', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tid => $qte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        {
            const inp = document.querySelector('input[data-tarif="<?php echo e($tid); ?>"]');
            if (inp) { inp.value = <?php echo e((int) $qte); ?>; }
        }
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        recalc();
    }
});
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/admin/lots-physiques/index.blade.php ENDPATH**/ ?>