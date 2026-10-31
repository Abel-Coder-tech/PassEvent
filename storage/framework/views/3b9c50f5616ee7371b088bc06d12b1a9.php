<?php $__env->startSection('title', $evenement->titre . ' — Événement'); ?>
<?php $__env->startSection('page-title', Str::limit($evenement->titre, 40)); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="<?php echo e(route('superadmin.evenements')); ?>" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Tous les événements
    </a>
    <a href="<?php echo e(route('superadmin.organisateurs.voir', $evenement->user)); ?>" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-person"></i> <?php echo e($evenement->user->nom ?? 'Organisateur'); ?>

    </a>
</div>

<?php
    $statutEffectif = $evenement->statutEffectif();
    $badgeMap = ['publié' => 'success', 'brouillon' => 'secondary', 'annulé' => 'danger', 'passé' => 'passed'];
    $labelMap = ['publié' => 'Publié', 'brouillon' => 'Brouillon', 'annulé' => 'Annulé', 'passé' => 'Passé'];
    $statutEspeces = $evenement->ventes_especes ?? $evenement->user?->ventes_especes;
    $labelEspecesEffectif = $statutEspeces === 'toujours' ? 'Toujours autorisées' : ($statutEspeces === 'jamais' ? 'Jamais (bloquées)' : 'Auto (règle 15 %)');
    $labelCommissionEffectif = $commissionPct;
    $limiteAgents = $evenement->limiteAgentsVente();
    $labelAgentsEffectif = $limiteAgents === null ? 'illimité' : ($limiteAgents . ' agents');
?>

<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: var(--sa-primary);"><?php echo e($evenement->quota_vendu); ?> / <?php echo e($evenement->capacite); ?></div>
            <small class="text-muted">Places vendues</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: var(--sa-success);"><?php echo e($evenement->tickets_vendus ?? 0); ?></div>
            <small class="text-muted">Tickets payés</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: #3498db;"><?php echo e(number_format($evenement->recettes ?? 0, 0, ',', ' ')); ?> F</div>
            <small class="text-muted">Recettes</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-3">
            <div class="fw-bold fs-4" style="color: var(--sa-danger);"><?php echo e(number_format($commission, 0, ',', ' ')); ?> F</div>
            <small class="text-muted">Commission (<?php echo e($commissionPct); ?>%)</small>
        </div>
    </div>
</div>

<div class="row g-2 mb-4">
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color:#3498db;"><?php echo e(number_format($mobileRecettes, 0, ',', ' ')); ?> F</div>
            <small class="text-muted">Mobile (FedaPay)</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color:#f39c12;"><?php echo e(number_format($cashRecettes, 0, ',', ' ')); ?> F</div>
            <small class="text-muted">Espèces</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color:var(--sa-success);"><?php echo e(number_format($recettesNettes, 0, ',', ' ')); ?> F</div>
            <small class="text-muted">Net (après commission)</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color: var(--sa-primary);"><?php echo e($ticketsScannes); ?></div>
            <small class="text-muted">Tickets scannés</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color: var(--sa-success);"><?php echo e($agentsScan); ?></div>
            <small class="text-muted">Agents scan</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="color: #3498db;"><?php echo e($agentsVente); ?></div>
            <small class="text-muted">Agents vente</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-sliders me-2" style="color: var(--sa-primary);"></i>Contrôles de l'événement</span>
                <span class="text-muted" style="font-size:0.8rem;">Spécifique &gt; organisateur &gt; défaut</span>
            </div>
            <div class="sa-card-body">
                <form action="<?php echo e(route('superadmin.evenements.controles', $evenement)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Ventes espèces</label>
                            <select name="ventes_especes" class="sa-form-control">
                                <option value="">Héritage (<?php echo e($labelEspecesEffectif); ?>)</option>
                                <option value="toujours" <?php if($evenement->ventes_especes === 'toujours'): echo 'selected'; endif; ?>>Toujours autorisées</option>
                                <option value="jamais" <?php if($evenement->ventes_especes === 'jamais'): echo 'selected'; endif; ?>>Jamais (bloquées)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Commission (%)</label>
                            <input type="number" name="commission_pourcentage" class="sa-form-control" min="0" max="10" step="0.5" value="<?php echo e($evenement->commission_pourcentage); ?>" placeholder="Héritage (<?php echo e($labelCommissionEffectif); ?> %)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Agents de vente (max)</label>
                            <select name="max_agents_vente" class="sa-form-control">
                                <option value="">Défaut (2 agents)</option>
                                <option value="5" <?php if($evenement->max_agents_vente === 5): echo 'selected'; endif; ?>>5 agents</option>
                                <option value="10" <?php if($evenement->max_agents_vente === 10): echo 'selected'; endif; ?>>10 agents</option>
                                <option value="0" <?php if($evenement->max_agents_vente === 0): echo 'selected'; endif; ?>>Illimité</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="sa-btn sa-btn-primary w-100"><i class="bi bi-check-lg"></i> Enregistrer</button>
                        </div>
                    </div>
                </form>
                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>Effectif actuel : espèces « <?php echo e($labelEspecesEffectif); ?> », commission <?php echo e($labelCommissionEffectif); ?> %, agents de vente : <?php echo e($labelAgentsEffectif); ?>.
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Actions</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex flex-wrap gap-2">
                    <form action="<?php echo e(route('superadmin.evenements.suspendre', $evenement)); ?>" method="POST" onsubmit="return confirm('Suspendre <?php echo e($evenement->titre); ?> ?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-pause-fill"></i> Suspendre</button>
                    </form>
                    <form action="<?php echo e(route('superadmin.evenements.masquer', $evenement)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="sa-btn sa-btn-outline"><i class="bi bi-eye-slash"></i> Masquer</button>
                    </form>
                    <?php if($evenement->statut !== 'publié'): ?>
                        <form action="<?php echo e(route('superadmin.evenements.mettre-en-avant', $evenement)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="sa-btn" style="background:var(--sa-success);color:#fff;border:none;"><i class="bi bi-check-lg"></i> Publier</button>
                        </form>
                    <?php else: ?>
                        <form action="<?php echo e(route('superadmin.evenements.a-la-une', $evenement)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="sa-btn <?php echo e($evenement->a_la_une ? 'sa-btn-danger' : 'sa-btn-outline'); ?>">
                                <i class="bi bi-<?php echo e($evenement->a_la_une ? 'star-fill' : 'star'); ?>"></i>
                                <?php echo e($evenement->a_la_une ? 'Retirer de la une' : 'Mettre à la une'); ?>

                            </button>
                        </form>
                        <?php if($evenement->a_la_une): ?>
                            <form action="<?php echo e(route('superadmin.evenements.a-la-une.ordre', [$evenement, 'haut'])); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Monter dans la une"><i class="bi bi-arrow-up"></i></button>
                            </form>
                            <form action="<?php echo e(route('superadmin.evenements.a-la-une.ordre', [$evenement, 'bas'])); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Descendre dans la une"><i class="bi bi-arrow-down"></i></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                        $nlObjet = '🎉 '.$evenement->titre.' — réservez vos billets !';
                        $nlMessage = '🎉 '.$evenement->titre." arrive bientôt !\n\n"
                            .($evenement->date_event ? '📅 '.$evenement->date_event->isoFormat('D MMM YYYY HH:mm')."\n" : '')
                            .($evenement->lieu ? '📍 '.$evenement->lieu."\n" : '')
                            ."\nRéservez vos billets dès maintenant sur PaxEvent :\n"
                            .route('evenements.public.show', $evenement)."\n\nÉquipe PaxEvent";
                    ?>
                    <button type="button" class="sa-btn" style="background:var(--sa-primary);color:#fff;border:none;"
                        data-prefill-id="single" onclick="ouvrirNewsletter(this)" title="Envoyer une newsletter">
                        <i class="bi bi-send-fill"></i> Newsletter
                    </button>
                    <a href="<?php echo e(route('superadmin.campagnes-marketing')); ?>" class="sa-btn sa-btn-outline" style="text-decoration:none;" title="Voir les demandes de campagne">
                        <i class="bi bi-megaphone-fill"></i> Campagnes
                    </a>
                    <form action="<?php echo e(route('superadmin.evenements.supprimer', $evenement)); ?>" method="POST" onsubmit="return confirm('Supprimer définitivement <?php echo e($evenement->titre); ?> ? Cette action est irréversible.')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-trash-fill"></i> Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-ticket-perforated me-2" style="color: var(--sa-success);"></i>Tarifs</span>
            </div>
            <div class="sa-card-body p-0">
                <table class="sa-table">
                    <thead><tr><th>Nom</th><th>Prix</th><th>Vendus</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $evenement->tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($tarif->nom); ?></td>
                            <td><?php echo e(number_format($tarif->prix, 0, ',', ' ')); ?> F</td>
                            <td><?php echo e($tarif->quantite_vendue ?? 0); ?></td>
                            <td><?php echo e($tarif->statut === 'actif' ? 'Actif' : ucfirst($tarif->statut)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun tarif</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card h-100">
            <div class="sa-card-header">
                <span><i class="bi bi-calendar-event me-2" style="color: var(--sa-primary);"></i>Informations</span>
            </div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Statut</span>
                    <span class="sa-badge sa-badge-<?php echo e($badgeMap[$statutEffectif] ?? 'warning'); ?>"><?php echo e($labelMap[$statutEffectif] ?? $statutEffectif); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Date</span>
                    <span class="fw-semibold small"><?php echo e($evenement->date_event->isoFormat('D MMM YYYY HH:mm')); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Lieu</span>
                    <span class="fw-semibold small"><?php echo e($evenement->lieu); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Catégorie</span>
                    <span class="fw-semibold small"><?php echo e(ucfirst($evenement->categorie ?? '-')); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f0f0f0;">
                    <span class="text-muted small">Gratuit</span>
                    <span class="fw-semibold small"><?php echo e($evenement->gratuit ? 'Oui' : 'Non'); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted small">Organisateur</span>
                    <a href="<?php echo e(route('superadmin.organisateurs.voir', $evenement->user)); ?>" class="fw-semibold small text-decoration-none" style="color: var(--sa-primary);"><?php echo e($evenement->user->nom ?? '-'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-ticket me-2" style="color: var(--sa-success);"></i>Derniers tickets</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Acheteur</th><th>Email</th><th>WhatsApp</th><th>N° paiement</th><th>Tarif</th><th>Montant</th><th>Méthode</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-size:0.78rem;"><?php echo e($ticket->date_achat->isoFormat('D MMM YYYY HH:mm')); ?></td>
                    <td><?php echo e($ticket->nom_acheteur); ?></td>
                    <td><?php echo e($ticket->email_acheteur); ?></td>
                    <td><?php if($ticket->whatsapp_acheteur): ?><?php echo e($ticket->whatsapp_acheteur); ?><?php else: ?> <span class="text-muted">—</span> <?php endif; ?></td>
                    <td><?php if($ticket->telephone_paiement): ?><?php echo e($ticket->telephone_paiement); ?><?php elseif($ticket->telephone_acheteur): ?><?php echo e($ticket->telephone_acheteur); ?><?php else: ?> <span class="text-muted">—</span> <?php endif; ?></td>
                    <td><?php echo e($ticket->nom_tarif); ?></td>
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
</div>

<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-tools me-2" style="color:#e74c3c;"></i>Support technique</span>
        <a href="<?php echo e(route('superadmin.support', ['evenement_id' => $evenement->id])); ?>" class="sa-btn sa-btn-sm" style="background:var(--sa-primary);border:none;color:#fff;text-decoration:none;">
            <i class="bi bi-search"></i> Réconcilier
        </a>
    </div>
    <div class="sa-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <span class="sa-badge sa-badge-warning"><?php echo e($ticketsEnAttente); ?></span>
                    <span>Tickets en attente de paiement</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <?php if($incidentsSupport > 0): ?>
                        <span class="sa-badge sa-badge-danger"><?php echo e($incidentsSupport); ?></span>
                        <span>Incidents paiement FedaPay à traiter</span>
                    <?php else: ?>
                        <span class="sa-badge sa-badge-success">0</span>
                        <span>Aucun incident FedaPay en attente</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if($incidentsSupport > 0): ?>
            <div class="mt-3" style="font-size:0.78rem;color:#e74c3c;background:rgba(231,76,60,0.06);padding:0.6rem 0.8rem;border-radius:8px;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Des clients ont probablement été débités sans recevoir leur billet. Ouvrez le support technique pour vérifier et confirmer les tickets via FedaPay.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-arrow-repeat me-2" style="color: var(--sa-primary);"></i>Historique des modifications</span>
    </div>
    <div class="sa-card-body p-0">
        <?php
            $formatControle = function ($champ, $valeur) {
                if ($champ === 'ventes_especes') {
                    return $valeur === 'toujours' ? 'Toujours' : ($valeur === 'jamais' ? 'Jamais' : 'Héritage');
                }
                if ($champ === 'commission_pourcentage') {
                    return $valeur === null || $valeur === '' ? 'Défaut (10 %)' : number_format((float) $valeur, 2, ',', '') . ' %';
                }
                if ($champ === 'max_agents_vente') {
                    if ($valeur === null || $valeur === '') return 'Défaut (2 agents)';
                    if ((int) $valeur === 0) return 'Illimité';
                    return (int) $valeur . ' agents';
                }
                return '-';
            };
            $champsControles = ['ventes_especes' => 'Ventes espèces', 'commission_pourcentage' => 'Commission', 'max_agents_vente' => 'Agents de vente'];
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
                                <td><?php echo e($formatControle($champ, $ancienne)); ?></td>
                                <td><?php echo e($formatControle($champ, $nouvelle)); ?></td>
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

<?php echo $__env->make('superadmin.partials.newsletter-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const NEWSLETTER_PREFILL = { single: <?php echo json_encode(['objet' => $nlObjet, 'message' => $nlMessage], 512) ?> };
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/superadmin/evenement-show.blade.php ENDPATH**/ ?>