<?php $__env->startSection('title', $user->nom . ' — Organisateur'); ?>
<?php $__env->startSection('page-title', $user->nom); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-3">
    <a href="<?php echo e(route('superadmin.organisateurs')); ?>" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Tous les organisateurs
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="sa-card h-100">
            <div class="sa-card-body text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                    style="width: 64px; height: 64px; background: rgba(107,63,160,0.1);">
                    <i class="bi bi-person" style="font-size: 2rem; color: var(--sa-primary);"></i>
                </div>
                <h5 class="fw-bold"><?php echo e($user->nom); ?></h5>
                <p class="text-muted small mb-2"><?php echo e($user->email); ?></p>
                <?php
                    $badgeMap = ['actif' => 'success', 'bloque' => 'danger', 'rejete' => 'danger', 'en_attente' => 'warning', 'corrections_demandees' => 'warning', 'corrections_apportees' => 'info', 'incomplet' => 'secondary'];
                    $labelMap = ['actif' => 'Actif', 'bloque' => 'Bloqué', 'rejete' => 'Rejeté', 'en_attente' => 'En attente', 'corrections_demandees' => 'Corrections demandées', 'corrections_apportees' => 'Corrections apportées', 'incomplet' => 'Incomplet'];
                ?>
                <span class="sa-badge sa-badge-<?php echo e($badgeMap[$user->statut] ?? 'secondary'); ?> mb-2">
                    <?php echo e($labelMap[$user->statut] ?? ucfirst($user->statut)); ?>

                </span>
                <?php if($user->organisation): ?>
                    <p class="small mb-0 mt-2"><i class="bi bi-building"></i> <?php echo e($user->organisation); ?></p>
                <?php endif; ?>
                <?php if($user->fonction): ?>
                    <p class="small mb-0"><i class="bi bi-person-badge"></i> Fonction : <?php echo e($user->fonction); ?></p>
                <?php endif; ?>
                <?php if($user->telephone): ?>
                    <p class="small mb-0"><i class="bi bi-telephone"></i> <?php echo e($user->telephone); ?></p>
                <?php endif; ?>
                <?php if($user->type): ?>
                    <p class="small mb-0 mt-1"><i class="bi bi-tag"></i> <?php echo e(ucfirst($user->type)); ?></p>
                <?php endif; ?>
                <?php if($user->document_justificatif): ?>
                    <div class="mt-3 pt-2 border-top">
                        <a href="<?php echo e(asset('storage/' . $user->document_justificatif)); ?>" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                            <i class="bi bi-eye me-1"></i> Justificatif
                        </a>
                        <a href="<?php echo e(asset('storage/' . $user->document_justificatif)); ?>" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                            <i class="bi bi-download"></i>
                        </a>
                        <?php if($user->numero_cip): ?>
                            <div class="mt-2 small text-muted"><i class="bi bi-person-vcard me-1"></i> N° CIP : <strong><?php echo e($user->numero_cip); ?></strong></div>
                        <?php endif; ?>
                        <?php if($user->numero_rc): ?>
                            <div class="mt-1 small text-muted"><i class="bi bi-briefcase me-1"></i> N° RC / Récépissé : <strong><?php echo e($user->numero_rc); ?></strong></div>
                        <?php endif; ?>
                        <?php if($user->document_cip): ?>
                            <div class="mt-2">
                                <a href="<?php echo e(asset('storage/' . $user->document_cip)); ?>" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                                    <i class="bi bi-person-vcard me-1"></i> CIP représentant
                                </a>
                                <a href="<?php echo e(asset('storage/' . $user->document_cip)); ?>" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <a href="<?php echo e(route('superadmin.organisateurs.contrat', $user)); ?>" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:#2e7d4f;border-radius:6px;text-decoration:none;">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Voir le contrat
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($user->signature): ?>
                    <div class="mt-2">
                        <a href="<?php echo e(asset('storage/' . $user->signature)); ?>" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                            <i class="bi bi-pen me-1"></i> Signature
                        </a>
                        <a href="<?php echo e(asset('storage/' . $user->signature)); ?>" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: var(--sa-primary);"><?php echo e($evenements->count()); ?></div>
                    <small class="text-muted">Événements</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: var(--sa-success);"><?php echo e(number_format($totalTickets, 0, ',', ' ')); ?></div>
                    <small class="text-muted">Tickets vendus</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: #3498db;"><?php echo e(number_format($totalRecettes, 0, ',', ' ')); ?> F</div>
                    <small class="text-muted">Revenus totaux</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: #f39c12;"><?php echo e($aujourdhui); ?></div>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-6 col-md-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: var(--sa-primary);"><?php echo e($agentsScan); ?></div>
                    <small class="text-muted">Agents scan</small>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: var(--sa-success);"><?php echo e($agentsVente); ?></div>
                    <small class="text-muted">Agents vente</small>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: #3498db;"><?php echo e($scansAujourdhui); ?></div>
                    <small class="text-muted">Scans aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:#3498db;"><?php echo e(number_format($mobileRecettes, 0, ',', ' ')); ?> F</div>
                <small class="text-muted">Mobile (FedaPay)</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:#f39c12;"><?php echo e(number_format($cashRecettes, 0, ',', ' ')); ?> F</div>
                <small class="text-muted">Espèces</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:var(--sa-danger);"><?php echo e(number_format($commission, 0, ',', ' ')); ?> F</div>
                <small class="text-muted">Commission (<?php echo e($commissionPct); ?>%)</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:var(--sa-success);"><?php echo e(number_format($retirable, 0, ',', ' ')); ?> F</div>
                <small class="text-muted">Retirable</small>
            </div>
        </div>
    </div>

    <?php if($physiqueRecettes > 0 || $commissionPhysiqueTotale > 0 || $physiqueQuantite > 0): ?>
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-4">
            <div class="sa-card text-center py-2" style="border:1px dashed #c9a8e0;">
                <div class="fw-bold" style="color:#7B3FA0;"><?php echo e(number_format($physiqueQuantite, 0, ',', ' ')); ?></div>
                <small class="text-muted">Tickets physiques générés</small>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="sa-card text-center py-2" style="border:1px dashed #c9a8e0;">
                <div class="fw-bold" style="color:#7B3FA0;"><?php echo e(number_format($physiqueRecettes, 0, ',', ' ')); ?> F</div>
                <small class="text-muted">Valeur totale (guichet)</small>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="sa-card text-center py-2" style="border:1px dashed #c9a8e0;">
                <div class="fw-bold" style="color:#e67e22;"><?php echo e(number_format($commissionPhysiqueTotale, 0, ',', ' ')); ?> F</div>
                <small class="text-muted">Commission</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="sa-card mb-4">
        <div class="sa-card-header">
            <span><i class="bi bi-cash-coin me-2" style="color: var(--sa-primary);"></i>Ventes espèces</span>
            <span class="text-muted" style="font-size:0.8rem;">Spécifique événement &gt; dashboard &gt; règle auto (15 %)</span>
        </div>
        <div class="sa-card-body">
            <form action="<?php echo e(route('superadmin.organisateurs.vente-especes', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Portée</label>
                        <select name="portee" class="sa-form-control" data-portee="especesEvenementField">
                            <option value="dashboard">Tout le dashboard</option>
                            <option value="evenement">Un événement précis</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-none" id="especesEvenementField">
                        <label class="form-label small fw-semibold mb-1">Événement <span class="text-danger">*</span></label>
                        <select name="evenement_id" class="sa-form-control">
                            <option value="">-- Choisir un événement --</option>
                            <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ev->id); ?>"><?php echo e($ev->titre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Politique</label>
                        <select name="ventes_especes" class="sa-form-control">
                            <option value="auto">Auto (règle 15 %)</option>
                            <option value="toujours">Toujours autorisées</option>
                            <option value="jamais">Jamais (bloquées)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="sa-btn sa-btn-primary w-100"><i class="bi bi-check-lg"></i>Mettre à jour</button>
                    </div>
                </div>
            </form>

            <?php
                $labelsEspeces = [null => 'Auto (règle 15 %)', '' => 'Auto (règle 15 %)', 'toujours' => 'Toujours', 'jamais' => 'Jamais'];
                $especesParEvenement = $evenements->filter(fn($ev) => $ev->ventes_especes !== null);
            ?>
            <?php if($user->ventes_especes !== null || $especesParEvenement->isNotEmpty()): ?>
            <div class="table-responsive mt-3">
                <table class="sa-table">
                    <thead>
                        <tr><th>Portée</th><th>Politique</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if($user->ventes_especes !== null): ?>
                        <tr>
                            <td><strong>Tout le dashboard</strong> <span class="sa-badge sa-badge-secondary ms-1">global</span></td>
                            <td><?php echo e($labelsEspeces[$user->ventes_especes] ?? $user->ventes_especes); ?></td>
                            <td class="text-end">
                                <form action="<?php echo e(route('superadmin.organisateurs.controle-reset', $user)); ?>" method="POST" onsubmit="return confirm('Réinitialiser la politique vente espèces du dashboard ?')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="champ" value="ventes_especes">
                                    <input type="hidden" name="niveau" value="dashboard">
                                    <button type="submit" class="sa-btn sa-btn-secondary" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php $__currentLoopData = $especesParEvenement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e(Str::limit($ev->titre, 30)); ?></strong></td>
                            <td><?php echo e($labelsEspeces[$ev->ventes_especes] ?? $ev->ventes_especes); ?></td>
                            <td class="text-end">
                                <form action="<?php echo e(route('superadmin.organisateurs.controle-reset', $user)); ?>" method="POST" onsubmit="return confirm('Réinitialiser la politique vente espèces de cet événement ?')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="champ" value="ventes_especes">
                                    <input type="hidden" name="niveau" value="evenement">
                                    <input type="hidden" name="evenement_id" value="<?php echo e($ev->id); ?>">
                                    <button type="submit" class="sa-btn sa-btn-secondary" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-0 mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Aucun réglage : la règle auto (15 % de la capacité vendue en ligne) s'applique sur tous les événements.
            </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="sa-card mb-4">
        <div class="sa-card-header">
            <span><i class="bi bi-percent me-2" style="color: var(--sa-primary);"></i>Commission</span>
            <span class="text-muted" style="font-size:0.8rem;">Spécifique événement &gt; dashboard &gt; défaut 10 %</span>
        </div>
        <div class="sa-card-body">
            <form action="<?php echo e(route('superadmin.organisateurs.commission', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Portée</label>
                        <select name="portee" class="sa-form-control" data-portee="commissionEvenementField">
                            <option value="dashboard">Tout le dashboard</option>
                            <option value="evenement">Un événement précis</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-none" id="commissionEvenementField">
                        <label class="form-label small fw-semibold mb-1">Événement <span class="text-danger">*</span></label>
                        <select name="evenement_id" class="sa-form-control">
                            <option value="">-- Choisir un événement --</option>
                            <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ev->id); ?>"><?php echo e($ev->titre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Commission (%)</label>
                        <input type="number" name="commission_pourcentage" class="sa-form-control" min="0" max="10" step="0.5" placeholder="Défaut : 10 %">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="sa-btn sa-btn-primary w-100"><i class="bi bi-check-lg"></i>Mettre à jour</button>
                    </div>
                </div>
            </form>

            <?php
                $commissionsParEvenement = $evenements->filter(fn($ev) => $ev->commission_pourcentage !== null);
                $labelCommission = fn($v) => $v === null || $v === '' ? 'Défaut (10 %)' : number_format((float) $v, 1, ',', ' ') . ' %';
            ?>
            <?php if($user->commission_pourcentage !== null || $commissionsParEvenement->isNotEmpty()): ?>
            <div class="table-responsive mt-3">
                <table class="sa-table">
                    <thead>
                        <tr><th>Portée</th><th>Commission</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if($user->commission_pourcentage !== null): ?>
                        <tr>
                            <td><strong>Tout le dashboard</strong> <span class="sa-badge sa-badge-secondary ms-1">global</span></td>
                            <td><?php echo e($labelCommission($user->commission_pourcentage)); ?></td>
                            <td class="text-end">
                                <form action="<?php echo e(route('superadmin.organisateurs.controle-reset', $user)); ?>" method="POST" onsubmit="return confirm('Réinitialiser la commission du dashboard ?')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="champ" value="commission_pourcentage">
                                    <input type="hidden" name="niveau" value="dashboard">
                                    <button type="submit" class="sa-btn sa-btn-secondary" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php $__currentLoopData = $commissionsParEvenement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e(Str::limit($ev->titre, 30)); ?></strong></td>
                            <td><?php echo e($labelCommission($ev->commission_pourcentage)); ?></td>
                            <td class="text-end">
                                <form action="<?php echo e(route('superadmin.organisateurs.controle-reset', $user)); ?>" method="POST" onsubmit="return confirm('Réinitialiser la commission de cet événement ?')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="champ" value="commission_pourcentage">
                                    <input type="hidden" name="niveau" value="evenement">
                                    <input type="hidden" name="evenement_id" value="<?php echo e($ev->id); ?>">
                                    <button type="submit" class="sa-btn sa-btn-secondary" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-0 mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Aucun réglage : la commission par défaut (10 %) s'applique sur tous les événements.
            </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="sa-card mb-4">
        <div class="sa-card-header">
            <span><i class="bi bi-people me-2" style="color: var(--sa-primary);"></i>Attributions d'agents (scan / vente)</span>
            <span class="text-muted" style="font-size:0.8rem;">0 = illimité &mdash; spécifique événement &gt; dashboard &gt; défaut</span>
        </div>
        <div class="sa-card-body">
            <form action="<?php echo e(route('superadmin.organisateurs.attribuer-agents', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Portée</label>
                        <select name="portee" class="sa-form-control" data-portee="evenementField">
                            <option value="dashboard">Tout le dashboard</option>
                            <option value="evenement">Un événement précis</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-none" id="evenementField">
                        <label class="form-label small fw-semibold mb-1">Événement <span class="text-danger">*</span></label>
                        <select name="evenement_id" class="sa-form-control">
                            <option value="">-- Choisir un événement --</option>
                            <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ev->id); ?>"><?php echo e($ev->titre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Agents scan</label>
                        <input type="number" name="nb_agents_scan" class="sa-form-control" min="0" value="2">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Agents vente</label>
                        <input type="number" name="nb_agents_vente" class="sa-form-control" min="0" value="2">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="sa-btn sa-btn-primary w-100"><i class="bi bi-check-lg"></i>Mettre à jour</button>
                    </div>
                </div>
            </form>

            <?php if($attributions->isNotEmpty()): ?>
            <div class="table-responsive mt-3">
                <table class="sa-table">
                    <thead>
                        <tr><th>Portée</th><th>Agents scan</th><th>Agents vente</th><th>Utilisation</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $attributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong><?php echo e($attr->evenement_id ? Str::limit($attr->evenement?->titre ?? 'Événement', 28) : 'Tout le dashboard'); ?></strong>
                                <?php if(!$attr->evenement_id): ?>
                                    <span class="sa-badge sa-badge-secondary ms-1">global</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($attr->nbAgentsScan() ?? 'Illimité'); ?></td>
                            <td><?php echo e($attr->nbAgentsVente() ?? 'Illimité'); ?></td>
                            <td>
                                <?php
                                    $okScan = $attr->nbAgentsScan() === null || $attr->usage_scan <= $attr->nbAgentsScan();
                                    $okVente = $attr->nbAgentsVente() === null || $attr->usage_vente <= $attr->nbAgentsVente();
                                ?>
                                <span class="sa-badge <?php echo e($okScan ? 'sa-badge-success' : 'sa-badge-danger'); ?>">scan <?php echo e($attr->usage_scan); ?>/<?php echo e($attr->nbAgentsScan() ?? '∞'); ?></span>
                                <span class="sa-badge <?php echo e($okVente ? 'sa-badge-success' : 'sa-badge-danger'); ?>">vente <?php echo e($attr->usage_vente); ?>/<?php echo e($attr->nbAgentsVente() ?? '∞'); ?></span>
                            </td>
                            <td class="text-end">
                                <form action="<?php echo e(route('superadmin.organisateurs.attribution-supprimer', $attr)); ?>" method="POST"
                                    onsubmit="return confirm('Supprimer cette attribution ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="sa-btn sa-btn-danger" title="Supprimer l'attribution"><i class="bi bi-trash"></i>Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-0 mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Aucune attribution. Défauts appliqués : 2 agents de scan et 2 agents de vente par événement (ou la valeur du contrôle événement).
            </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="sa-card mb-4">
        <div class="sa-card-header">
            <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Actions</span>
            <span class="text-muted" style="font-size:0.8rem;">Modération et communication</span>
        </div>
        <div class="sa-card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php if(in_array($user->statut, ['en_attente', 'incomplet', 'corrections_demandees', 'corrections_apportees'])): ?>
                    <form action="<?php echo e(route('superadmin.organisateurs.approuver', $user)); ?>" method="POST" onsubmit="return confirm('Approuver <?php echo e($user->nom); ?> ?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-check-lg"></i> Approuver</button>
                    </form>
                    <button class="sa-btn sa-btn-warning" onclick="document.getElementById('correctionsModal').style.display='flex'">
                        <i class="bi bi-pencil-square"></i> Demander des corrections
                    </button>
                    <button class="sa-btn sa-btn-danger" onclick="document.getElementById('rejetModal').style.display='flex'">
                        <i class="bi bi-x-lg"></i> Rejeter
                    </button>
                <?php endif; ?>
                <?php if($user->statut === 'actif'): ?>
                    <form action="<?php echo e(route('superadmin.organisateurs.suspendre', $user)); ?>" method="POST" onsubmit="return confirm('Suspendre <?php echo e($user->nom); ?> ? Ses événements seront annulés.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-pause-fill"></i> Suspendre</button>
                    </form>
                <?php elseif(in_array($user->statut, ['bloque', 'rejete'])): ?>
                    <form action="<?php echo e(route('superadmin.organisateurs.reactiver', $user)); ?>" method="POST" onsubmit="return confirm('Réactiver <?php echo e($user->nom); ?> ?<?php echo e($user->statut === 'bloque' ? ' Ses événements annulés seront republiés.' : ''); ?>')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="sa-btn sa-btn-success"><i class="bi bi-play-fill"></i> Réactiver</button>
                    </form>
                <?php endif; ?>
                <form action="<?php echo e(route('superadmin.organisateurs.supprimer', $user)); ?>" method="POST" onsubmit="return confirm('Supprimer définitivement <?php echo e($user->nom); ?> ? Cette action est irréversible.')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                </form>
            </div>
            <hr style="margin:0.75rem 0;border-color:#eee;">
            <h6 style="font-size:0.85rem;font-weight:700;margin-bottom:0.5rem;color:var(--sa-primary);">
                <i class="bi bi-envelope me-1"></i> Envoyer un email
            </h6>
            <form action="<?php echo e(route('superadmin.organisateurs.email', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-2">
                    <input type="text" name="sujet" class="sa-form-control" placeholder="Sujet" required>
                </div>
                <div class="mb-2">
                    <textarea name="message" class="sa-form-control" rows="3" placeholder="Votre message..." required style="resize:vertical;"></textarea>
                </div>
                <button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-send"></i> Envoyer</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-calendar-event me-2" style="color: var(--sa-primary);"></i>Événements</span>
            </div>
            <div class="sa-card-body p-0">
                <table class="sa-table">
                    <thead>
                        <tr><th>Titre</th><th>Date</th><th>Tickets</th><th>Revenus</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><a href="<?php echo e(route('superadmin.evenements.voir', $ev)); ?>" class="text-decoration-none" style="color: var(--sa-primary);"><?php echo e($ev->titre); ?></a></strong></td>
                            <td style="font-size:0.78rem;"><?php echo e($ev->date_event->isoFormat('D MMM YYYY')); ?></td>
                            <td><?php echo e($ev->tickets_vendus); ?> / <?php echo e($ev->capacite); ?></td>
                            <td><?php echo e(number_format($ev->recettes, 0, ',', ' ')); ?> F</td>
                            <td>
                                <?php $st = $ev->statutEffectif(); ?>
                                <?php if($st === 'passé'): ?>
                                    <span class="sa-badge sa-badge-passed">Passé</span>
                                <?php elseif($st === 'publié'): ?>
                                    <span class="sa-badge sa-badge-success">Publié</span>
                                <?php elseif($st === 'brouillon'): ?>
                                    <span class="sa-badge sa-badge-secondary">Brouillon</span>
                                <?php else: ?>
                                    <span class="sa-badge sa-badge-danger"><?php echo e(ucfirst($st)); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun événement</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-pie-chart me-2" style="color: var(--sa-success);"></i>Répartition par événement</span>
            </div>
            <div class="sa-card-body p-3">
                <?php $__empty_1 = true; $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong style="font-size:0.82rem;"><?php echo e(Str::limit($ev->titre, 30)); ?></strong>
                        <div class="text-muted" style="font-size:0.72rem;"><?php echo e($ev->tickets_vendus); ?> tickets</div>
                    </div>
                    <div class="fw-bold" style="color: var(--sa-success);"><?php echo e(number_format($ev->recettes, 0, ',', ' ')); ?> F</div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted text-center py-3 mb-0">Aucune donnée</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-clock-history me-2" style="color: var(--sa-primary);"></i>Derniers tickets vendus</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Événement</th><th>Acheteur</th><th>Email</th><th>WhatsApp</th><th>N° paiement</th><th>Montant</th><th>Méthode</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-size:0.78rem;"><?php echo e($ticket->date_achat->isoFormat('D MMM YYYY HH:mm')); ?></td>
                    <td><?php echo e(Str::limit($ticket->evenement->titre, 25)); ?></td>
                    <td><?php echo e($ticket->nom_acheteur); ?></td>
                    <td><?php echo e($ticket->email_acheteur); ?></td>
                    <td><?php if($ticket->whatsapp_acheteur): ?><?php echo e($ticket->whatsapp_acheteur); ?><?php else: ?> <span class="text-muted">—</span> <?php endif; ?></td>
                    <td><?php if($ticket->telephone_paiement): ?><?php echo e($ticket->telephone_paiement); ?><?php elseif($ticket->telephone_acheteur): ?><?php echo e($ticket->telephone_acheteur); ?><?php else: ?> <span class="text-muted">—</span> <?php endif; ?></td>
                    <td>
                        <?php if($ticket->montant > 0): ?>
                        <span class="fw-bold" style="color: var(--sa-success);"><?php echo e(number_format($ticket->montant, 0, ',', ' ')); ?> F</span>
                        <?php else: ?>
                        <span class="text-muted">Gratuit</span>
                        <?php endif; ?>
                    </td>
                    <td><?php if($ticket->montant > 0): ?><?php echo e(\App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement)); ?><?php else: ?> — <?php endif; ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">Aucun ticket vendu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($tickets->hasPages()): ?>
    <div class="p-3 d-flex justify-content-center"><?php echo e($tickets->links()); ?></div>
    <?php endif; ?>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-arrow-repeat me-2" style="color: var(--sa-primary);"></i>Historique des modifications</span>
        <span class="text-muted" style="font-size:0.8rem;">Taux et statuts</span>
    </div>
    <div class="sa-card-body p-0">
        <?php
            $labelsEspeces = [null => 'Auto (règle 15 %)', 'toujours' => 'Toujours', 'jamais' => 'Jamais'];
            $labelsCommission = fn($v) => $v === null || $v === '' ? 'Défaut (10 %)' : number_format((float) $v, 2, ',', '') . ' %';
            $champsControles = ['ventes_especes' => 'Ventes espèces', 'commission_pourcentage' => 'Commission'];
        ?>
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Champ</th><th>Ancienne valeur</th><th>Nouvelle valeur</th><th>Superadmin</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $historique; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $anciens = $log->details['ancien'] ?? [];
                        $nouveaux = $log->details['nouveau'] ?? [];
                        $aDesChangements = false;
                    ?>
                    <?php $__currentLoopData = $champsControles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $champ => $libelle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $ancienne = $anciens[$champ] ?? null;
                            $nouvelle = $nouveaux[$champ] ?? null;
                        ?>
                        <?php if($ancienne !== $nouvelle): ?>
                            <?php $aDesChangements = true; ?>
                            <tr>
                                <td style="font-size:0.78rem;"><?php echo e(\Carbon\Carbon::parse($log->created_at)->isoFormat('D MMM YYYY HH:mm')); ?></td>
                                <td><?php echo e($libelle); ?></td>
                                <td><?php echo e($champ === 'ventes_especes' ? ($labelsEspeces[$ancienne] ?? '-') : $labelsCommission($ancienne)); ?></td>
                                <td><?php echo e($champ === 'ventes_especes' ? ($labelsEspeces[$nouvelle] ?? '-') : $labelsCommission($nouvelle)); ?></td>
                                <td><?php echo e($log->details['par'] ?? '-'); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!$aDesChangements): ?>
                        <tr>
                            <td style="font-size:0.78rem;"><?php echo e(\Carbon\Carbon::parse($log->created_at)->isoFormat('D MMM YYYY HH:mm')); ?></td>
                            <td><?php echo e($log->type_operation === 'evenement_annule' ? 'Annulation' : 'Contrôles'); ?></td>
                            <td colspan="2" class="text-muted"><?php echo e($log->type_operation === 'evenement_annule' ? 'Événement annulé' : 'Modification enregistrée'); ?></td>
                            <td><?php echo e($log->details['par'] ?? '-'); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Aucune modification enregistrée</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if($historique->hasPages()): ?>
        <div class="p-3 d-flex justify-content-center"><?php echo e($historique->links()); ?></div>
        <?php endif; ?>
    </div>
</div>


<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-people me-2" style="color: var(--sa-primary);"></i>Interventions sur ce compte</span>
        <small class="text-muted">Qui a validé, rejeté ou suspendu ce compte</small>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table mb-0">
            <thead>
                <tr><th>Date</th><th>Action</th><th>Par</th><th>Détail</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $interventions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $detailsLog = $log->details ?? [];
                        $acteur = $detailsLog['par_membre_nom'] ?? 'Propriétaire';
                        $libellesActions = [
                            'organisateur_approuve' => ['Validé', 'success'],
                            'organisateur_rejete' => ['Rejeté', 'danger'],
                            'organisateur_suspendu' => ['Suspendu', 'danger'],
                            'organisateur_reactive' => ['Réactivé', 'info'],
                            'organisateur_corrections' => ['Corrections demandées', 'warning'],
                            'organisateur_supprime' => ['Supprimé', 'danger'],
                        ];
                        $action = $libellesActions[$log->type_operation] ?? [ucfirst(str_replace('_', ' ', $log->type_operation)), 'secondary'];
                    ?>
                    <tr>
                        <td style="font-size:0.78rem;"><?php echo e(\Carbon\Carbon::parse($log->created_at)->isoFormat('D MMM YYYY HH:mm')); ?></td>
                        <td><span class="sa-badge sa-badge-<?php echo e($action[1]); ?>"><?php echo e($action[0]); ?></span></td>
                        <td style="font-size:0.78rem;">
                            <?php echo e($acteur); ?>

                            <?php if(isset($detailsLog['par_membre_id'])): ?>
                                <span class="sa-badge sa-badge-secondary" style="font-size:0.62rem;">Équipe</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:0.75rem;">
                            <?php echo e($detailsLog['motif'] ?? ($detailsLog['email'] ?? '-')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Aucune intervention enregistrée</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="rejetModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Rejeter <?php echo e($user->nom); ?></h5>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form action="<?php echo e(route('superadmin.organisateurs.rejeter', $user)); ?>" method="POST">
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


<div id="correctionsModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-pencil-square me-2" style="color:var(--sa-warning);"></i>Demander des corrections</h5>
            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
        </div>
        <form action="<?php echo e(route('superadmin.organisateurs.corrections', $user)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Indiquez les modifications nécessaires. L'organisateur recevra un email et pourra corriger son profil.</p>
                <textarea name="motif" class="sa-form-control" rows="4" placeholder="Détail des corrections à apporter..." required style="resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                <button type="submit" class="sa-btn sa-btn-warning"><i class="bi bi-send"></i> Envoyer</button>
            </div>
        </form>
    </div>
</div>

<style>
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
    gap: 0.5rem;
}
.sa-btn-warning {
    background: #e0a800; border: none; color: #fff; padding: 0.4rem 0.9rem;
    border-radius: 8px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-warning:hover { opacity: 0.85; }
.sa-btn-success {
    background: #2e7d4f; border: none; color: #fff; padding: 0.4rem 0.9rem;
    border-radius: 8px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-success:hover { opacity: 0.85; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[data-portee]').forEach(function (porteeSelect) {
        var evenementField = document.getElementById(porteeSelect.dataset.portee);
        if (!evenementField) return;
        var evenementSelect = evenementField.querySelector('select[name="evenement_id"]');
        if (!evenementSelect) return;

        function toggleEvenementField() {
            var isEvent = porteeSelect.value === 'evenement';
            evenementField.classList.toggle('d-none', !isEvent);
            evenementSelect.required = isEvent;
        }

        porteeSelect.addEventListener('change', toggleEvenementField);
        toggleEvenementField();
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/organisateur-show.blade.php ENDPATH**/ ?>