<?php $__env->startSection('title', $evenement->titre . ' — PaxEvent'); ?>
<?php $__env->startSection('description', Str::limit($evenement->description ?? 'Consultez les détails de cet événement sur PaxEvent.', 160)); ?>
<?php $__env->startSection('og_title', $evenement->titre); ?>
<?php $__env->startSection('og_description', Str::limit($evenement->description ?? (Str::ucfirst($evenement->getTextes()['acheter']) . ' pour ' . $evenement->titre . ' sur PaxEvent.'), 160)); ?>
<?php if($evenement->image): ?>
    <?php $__env->startSection('og_image', asset('storage/' . $evenement->image)); ?>
<?php endif; ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('evenements.public')); ?>">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo e(Str::limit($evenement->titre, 40)); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
    $estComplet = $placesRestantes <= 0;
    $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
    $textes = $evenement->getTextes();
?>

<div class="show-event">
    <!-- Hero -->
    <div class="show-hero">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <?php if($evenement->categorie): ?>
                        <span class="show-badge"><?php echo e(ucfirst($evenement->categorie)); ?></span>
                    <?php endif; ?>
                    <h1 class="show-title"><?php echo e($evenement->titre); ?></h1>
                    <div class="show-meta">
                        <?php if($evenement->dates && $evenement->dates->count() > 1): ?>
                            <span><i class="bi bi-calendar3"></i> <?php echo e($evenement->dates->map(fn($d) => $d->date_debut->isoFormat('D MMM') . ' à ' . $d->date_debut->format('H:i'))->implode(' · ')); ?></span>
                        <?php else: ?>
                            <span><i class="bi bi-calendar3"></i> <?php echo e($evenement->date_event->isoFormat('D MMM YYYY')); ?></span>
                            <span><i class="bi bi-clock"></i> <?php echo e($evenement->date_event->format('H:i')); ?></span>
                        <?php endif; ?>
                        <span><i class="bi bi-geo-alt"></i> <?php echo e($evenement->lieu); ?></span>
                        <?php if(!$estComplet): ?>
                            <span><i class="bi bi-people"></i> <?php echo e(number_format($placesRestantes, 0, ',', ' ')); ?> places</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <?php if($evenement->image): ?>
                        <div class="show-hero-image">
                            <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="<?php echo e($evenement->titre); ?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-5 py-4">
            <!-- Colonne gauche : Description + Achat -->
            <div class="col-lg-8 show-event-content">

                <!-- Description -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-info-circle"></i> Description</h5>
                    <?php if($evenement->description): ?>
                        <p class="show-card-text"><?php echo e($evenement->description); ?></p>
                    <?php else: ?>
                        <p class="show-card-text text-muted">Aucune description disponible.</p>
                    <?php endif; ?>
                </div>

                <!-- Achat -->
                <div class="show-purchase-wrap">
                    <?php if(($venteCloturee ?? false) || ($evenementPasse ?? false)): ?>
                        <div class="show-card text-center py-4">
                            <div class="show-lock-icon"><i class="bi bi-lock-fill"></i></div>
                            <h5 style="color:var(--danger); font-weight:700; margin-bottom:0.25rem;"><?php echo e($textes['cloturee']); ?></h5>
                            <p class="show-card-text mb-0">
                                <?php if($evenementPasse ?? false): ?>
                                    Cet événement a déjà eu lieu.
                                <?php else: ?>
                                    <?php echo e($textes['type'] === 'spectacle' ? 'Les ventes sont clôturées pour cet événement.' : 'Les inscriptions sont clôturées pour cet événement.'); ?>

                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                    <div class="show-card">
                        <div class="show-ticket-header">
                            <div class="show-ticket-icon"><i class="bi bi-ticket-perforated"></i></div>
                            <div>
                                <h5 class="fw-bold mb-0"><?php echo e($evenement->gratuit ? $textes['participer_gratuit'] : $textes['acheter']); ?></h5>
                                <small class="text-muted"><?php echo e(number_format($placesRestantes, 0, ',', ' ')); ?> <?php echo e($textes['places']); ?></small>
                            </div>
                        </div>
                        <hr class="my-3">

                        <?php if($evenement->gratuit): ?>
                        <form action="<?php echo e(route('evenements.achat', $evenement)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="gratuit" value="1">
                            <input type="hidden" name="quantite" id="hiddenQuantiteG" value="1">
                            <div class="mb-3">
                                <label class="show-label">Nombre de places</label>
                                <div class="show-qty">
                                    <button type="button" id="qtyMinusG">-</button>
                                    <input type="number" id="quantiteInputG" value="1" min="1" max="<?php echo e(max(1, $placesRestantes)); ?>" readonly>
                                    <button type="button" id="qtyPlusG">+</button>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="show-label">Prénom et Nom <span class="text-danger">*</span></label>
                                <input type="text" class="show-input" name="nom_acheteur" value="<?php echo e(old('nom_acheteur')); ?>" placeholder="Ex: Kofi Mensah" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="show-input" name="email_acheteur" value="<?php echo e(old('email_acheteur')); ?>" placeholder="votre@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">N° WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="show-input" name="whatsapp_acheteur" value="<?php echo e(old('whatsapp_acheteur')); ?>" placeholder="+229 XX XX XX XX" required>
                            </div>
                            <div class="show-total">
                                <span class="fw-bold"><?php echo e($textes['billet_pluriel']); ?></span>
                                <span class="show-total-price">Gratuit</span>
                            </div>
                            <button type="submit" class="show-btn show-btn-primary" <?php echo e($estComplet ? 'disabled' : ''); ?>>
                                <i class="bi bi-check-circle me-2"></i> <?php echo e($textes['acheter']); ?>

                            </button>
                            <p class="show-secure"><i class="bi bi-check-circle me-1" style="color:var(--violet);"></i> Réservation gratuite — Ticket PDF reçu par email</p>
                        </form>
                        <?php else: ?>
                        <div class="mb-3">
                            <label class="show-label">Choisissez votre tarif</label>
                            <?php if($tarifs->isEmpty()): ?>
                                <div class="alert alert-danger py-2" style="font-size:0.85rem;">Aucun tarif disponible</div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php $__currentLoopData = $tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="show-tarif" onclick="selectTarif(this)">
                                            <input type="radio" name="tarif_id" value="<?php echo e($tarif->id); ?>" <?php echo e($loop->first ? 'checked' : ''); ?>>
                                            <strong><?php echo e($tarif->nom); ?></strong>
                                            <strong class="show-tarif-price"><?php echo e(number_format($tarif->prix, 0, ',', ' ') . ' F'); ?></strong>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="show-label">Quantité</label>
                            <div class="show-qty">
                                <button type="button" id="qtyMinus">-</button>
                                <input type="number" id="quantiteInput" value="1" min="1" max="<?php echo e(max(1, $placesRestantes)); ?>" readonly>
                                <button type="button" id="qtyPlus">+</button>
                            </div>
                        </div>
                        <hr>
                        <form action="<?php echo e(route('evenements.achat', $evenement)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="tarif_id" id="hiddenTarifId">
                            <input type="hidden" name="quantite" id="hiddenQuantite" value="1">
                            <div class="mb-3">
                                <label class="show-label">Prénom et Nom <span class="text-danger">*</span></label>
                                <input type="text" class="show-input" name="nom_acheteur" value="<?php echo e(old('nom_acheteur')); ?>" placeholder="Ex: Kofi Mensah" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="show-input" name="email_acheteur" value="<?php echo e(old('email_acheteur')); ?>" placeholder="votre@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">N° WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="show-input" name="whatsapp_acheteur" value="<?php echo e(old('whatsapp_acheteur')); ?>" placeholder="+229 XX XX XX XX" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Code promo <span class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="text" class="show-input" name="code_promo" value="<?php echo e(old('code_promo')); ?>" placeholder="PROMO-XXXXXX" style="text-transform:uppercase;">
                            </div>
                            <div class="show-total">
                                <span class="fw-bold">Total à payer</span>
                                <span class="show-total-price" id="totalDisplay">--</span>
                            </div>
                            <button type="submit" class="show-btn show-btn-primary" id="btnPayer" <?php echo e($tarifs->isEmpty() || $estComplet ? 'disabled' : ''); ?>>
                                <i class="bi bi-shield-lock me-2"></i> Payer avec FedaPay
                            </button>
                            <p class="show-secure"><i class="bi bi-shield-check me-1" style="color:var(--violet);"></i> Paiement 100% sécurisé — Ticket PDF envoyé par email</p>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne droite : Partager + Lieu + Contacter + Autres -->
            <div class="col-lg-4 show-event-sidebar">

                <?php if($evenement->co_parrainage_actif && !$evenement->gratuit): ?>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3" style="background: linear-gradient(90deg, rgba(254,213,20,0.12), rgba(84,38,128,0.08)); border:1px solid rgba(254,213,20,0.5); border-radius:12px; padding:0.6rem 0.8rem;">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#542680,#9972B0);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.9rem;"><i class="bi bi-share-fill"></i></div>
                            <div>
                                <div style="font-weight:700;font-size:0.82rem;color:#211C31;">Co-parrainage activé</div>
                                <div style="font-size:0.74rem;color:#6c757d;"><?php echo e(rtrim(rtrim(number_format((float) $evenement->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',')); ?> % par billet</div>
                            </div>
                        </div>
                        <a href="<?php echo e(auth('affiliate')->check() ? route('affiliate.liens') : route('affiliate.register')); ?>" style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.42rem 0.7rem;border-radius:8px;background:linear-gradient(135deg,#542680,#9972B0);color:#fff;text-decoration:none;font-weight:700;font-size:0.75rem;white-space:nowrap;">
                            <i class="bi bi-person-plus-fill"></i> Devenir affilié
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Partager -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-share"></i> Partager</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="show-share-btn" onclick="shareEvent()" title="Partager">
                            <i class="bi bi-share-fill"></i>
                        </button>
                        <button type="button" class="show-share-btn" onclick="copyLink()" title="Copier le lien">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(url()->current())); ?>" target="_blank" class="show-share-btn" title="Partager sur Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo e(urlencode($evenement->titre . ' - ' . url()->current())); ?>" target="_blank" class="show-share-btn" title="Partager sur WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
                

                <!-- Lieu -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-pin-map"></i> Lieu</h5>
                    <div class="show-map">
                        <iframe src="https://www.google.com/maps?q=<?php echo e(urlencode($evenement->lieu)); ?>&output=embed" width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>

                <!-- Contacter -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-envelope"></i> Une question ?</h5>
                    <p class="show-card-text" style="margin-bottom:1rem;">Vous avez une question spécifique sur cet événement ? Contactez directement l'organisateur.</p>
                    <button type="button" class="show-btn" data-bs-toggle="modal" data-bs-target="#contactOrganisateurModal">
                        <i class="bi bi-envelope me-2"></i> Contacter l'organisateur
                    </button>
                </div>

                <?php
                    $autresEvenements = App\Models\Evenement::with('tarifs')
                        ->where('statut', 'publié')
                        ->where('date_event', '>=', now())
                        ->where('id', '!=', $evenement->id)
                        ->orderBy('date_event', 'asc')
                        ->limit(3)
                        ->get();
                ?>
                <?php if($autresEvenements->isNotEmpty()): ?>
                    <div class="show-card show-card-left">
                        <h6 class="fw-bold mb-3" style="color:#211C31;"><i class="bi bi-calendar-event me-2" style="color:var(--violet);"></i>Autres événements</h6>
                        <div class="d-flex flex-column gap-3">
                            <?php $__currentLoopData = $autresEvenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $autre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('evenements.public.show', $autre)); ?>" class="show-other-card">
                                    <?php if($autre->image): ?>
                                        <img src="<?php echo e(asset('storage/' . $autre->image)); ?>" alt="">
                                    <?php else: ?>
                                        <div class="show-other-placeholder"><i class="bi bi-calendar-event"></i></div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="show-other-title"><?php echo e($autre->titre); ?></div>
                                        <div class="show-other-date"><i class="bi bi-calendar3 me-1"></i><?php echo e($autre->date_event->isoFormat('D MMM YYYY')); ?></div>
                                    </div>
                                    <i class="bi bi-chevron-right" style="color:#aaa; font-size:0.8rem;"></i>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contacter -->
<div class="modal fade" id="contactOrganisateurModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" style="color:#211C31;"><i class="bi bi-envelope me-2" style="color:var(--violet);"></i> Contacter l'organisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color:#6c757d; font-size:0.85rem; margin-bottom:1rem;">
                    Votre message sera envoyé à <strong><?php echo e($evenement->user->nom); ?></strong> (organisateur de <strong><?php echo e($evenement->titre); ?></strong>).
                </p>
                <form action="<?php echo e(route('evenements.contacter-organisateur', $evenement)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="show-label">Votre nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="show-input" placeholder="Ex: Kofi Mensah" required>
                    </div>
                    <div class="mb-3">
                        <label class="show-label">Votre email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="show-input" placeholder="votre@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="show-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="show-input" rows="4" placeholder="Écrivez votre message ici..." required minlength="10"></textarea>
                    </div>
                    <button type="submit" class="show-btn show-btn-primary" style="background:var(--violet);">
                        <i class="bi bi-send me-2"></i> Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== LAYOUT ===== */
.show-event {
    background: #f7f5f3;
    padding-bottom: 3rem;
}

/* ===== HERO ===== */
.show-hero {
    padding: 4rem 0 2.5rem;
    background: #111;
    position: relative;
}
.show-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(84,38,128,0.08) 0%, transparent 100%);
    pointer-events: none;
}
.show-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.8rem;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    color: rgba(255,255,255,0.5);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 1rem;
}
.show-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 1rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
}
.show-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem 1.2rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.82rem;
}
.show-meta i { margin-right: 0.35rem; color: rgba(255,255,255,0.25); }
.show-status-badge {
    display: inline-flex;
    padding: 0.35rem 1rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.2px;
}
.show-status-badge.available {
    background: rgba(18,151,110,0.1);
    color: #3ab87a;
    border: 1px solid rgba(18,151,110,0.15);
}
.show-status-badge.closed {
    background: rgba(231,76,60,0.08);
    color: #e74c3c;
    border: 1px solid rgba(231,76,60,0.12);
}

/* ===== HERO IMAGE ===== */
.show-hero-image {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
}
.show-hero-image img {
    width: 100%;
    display: block;
}

/* ===== INFOS ===== */
.show-infos {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.show-info-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    background: #fff;
    border: 1px solid #ede5f0;
    font-size: 0.82rem;
}
.show-info-chip i { color: var(--violet); font-size: 0.9rem; }

/* ===== CARDS ===== */
.show-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    border: 1px solid #ede5f0;
    transition: box-shadow 0.2s;
}
.show-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.show-card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.75rem;
}
.show-card-title i {
    margin-right: 0.5rem;
    color: var(--violet);
}
.show-card-text {
    font-size: 0.88rem;
    color: #6c757d;
    line-height: 1.8;
    margin: 0;
}

/* ===== MAP ===== */
.show-map {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #ede5f0;
}

/* ===== SHARE ===== */
.show-share-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid #ede5f0;
    background: #fff;
    color: #6c757d;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.2s;
    cursor: pointer;
}
.show-share-btn:hover {
    border-color: var(--violet);
    background: rgba(135,66,139,0.04);
    color: var(--violet);
}

/* ===== SIDEBAR ===== */
.show-sidebar { position: sticky; top: 90px; }
.show-lock-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(231,76,60,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: var(--danger);
    font-size: 1.4rem;
}
.show-ticket-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.show-ticket-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(84,38,128,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #542680;
    font-size: 1.3rem;
}

/* ===== TARIFS ===== */
.show-tarif {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border: 2px solid #ede5f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
}
.show-tarif:hover { border-color: #9972B0; }
.show-tarif.selected { border-color: #542680; background: rgba(84,38,128,0.04); }
.show-tarif input[type="radio"] { display: none; }
.show-tarif-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
}
.show-tarif-price {
    margin-left: auto;
    color: #542680;
    font-size: 1rem;
}

/* ===== FORM ===== */
.show-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #211C31;
    margin-bottom: 0.3rem;
    display: block;
}
.show-input {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 1.5px solid #ede5f0;
    border-radius: 10px;
    font-size: 0.85rem;
    outline: none;
    transition: all 0.2s;
    background: #fafafa;
}
.show-input:focus {
    border-color: #542680;
    box-shadow: 0 0 0 3px rgba(84,38,128,0.08);
    background: #fff;
}
.show-qty {
    display: inline-flex;
    align-items: center;
    border: 1.5px solid #ede5f0;
    border-radius: 10px;
    overflow: hidden;
}
.show-qty button {
    width: 38px; height: 38px; border: none;
    background: #f7f5f3; color: #333;
    font-size: 1.1rem; cursor: pointer; transition: background 0.15s;
}
.show-qty button:hover { background: #e8e4e0; }
.show-qty input {
    width: 52px; height: 38px; border: none;
    text-align: center; font-weight: 700; font-size: 0.95rem;
    outline: none;
}
.show-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    background: #f8f6f9;
    margin-bottom: 1rem;
}
.show-total-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: #542680;
}
.show-btn {
    display: block;
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s;
}
.show-btn-primary {
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
}
.show-btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(84,38,128,0.35);
    color: #fff;
}
.show-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.show-secure {
    text-align: center;
    font-size: 0.72rem;
    color: #6c757d;
    margin: 0.5rem 0 0;
}

/* ===== AUTRES ÉVÉNEMENTS ===== */
.show-other-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem;
    border-radius: 10px;
    background: #f8f6f9;
    text-decoration: none;
    transition: background 0.2s;
}
.show-other-card:hover { background: #f0edf2; }
.show-other-card img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}
.show-other-placeholder {
    width: 50px;
    height: 50px;
    background: #e8e8e8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #999;
}
.show-other-title {
    font-weight: 700;
    font-size: 0.82rem;
    color: #211C31;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.show-other-date {
    font-size: 0.72rem;
    color: #9a9a9a;
}

@media (min-width: 992px) {
    .show-event-sidebar {
        position: sticky;
        top: 90px;
        align-self: start;
    }
}
@media (max-width: 991px) {
    .show-event-sidebar { position: static; }
}
.share-native {
    background: linear-gradient(135deg, #542680, #7b3fa0) !important;
    color: #fff !important;
    border: none !important;
}
.share-native:hover {
    opacity: 0.9;
    transform: scale(1.1);
}

/* ===== BANDEAU CO-PARRAINAGE ===== */
.co-parrain-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(254,213,20,0.14), rgba(84,38,128,0.06));
    border: 1px solid rgba(254,213,20,0.55) !important;
}
.co-parrain-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-size: 1.2rem;
}
.co-parrain-body { flex: 1; min-width: 200px; }
.co-parrain-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.2rem;
}
.co-parrain-text {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.6;
}
.co-parrain-text strong { color: #542680; }
.co-parrain-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 1.25rem;
    border-radius: 10px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.25s;
}
.co-parrain-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(84,38,128,0.35);
    color: #fff;
}
@media (max-width: 575px) {
    .co-parrain-banner { flex-direction: column; align-items: flex-start; }
    .co-parrain-btn { width: 100%; justify-content: center; }
}
@media (max-width: 767px) {
    .show-hero { padding: 2.5rem 0 2rem; }
    .show-title { font-size: 1.5rem; }
    .show-infos { gap: 0.5rem; }
}
</style>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="shareToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Lien copié dans le presse-papier !', 'success');
    }).catch(() => {
        showToast('Impossible de copier le lien', 'danger');
    });
}

function shareEvent() {
    if (navigator.share) {
        navigator.share({
            title: <?php echo json_encode($evenement->titre, 15, 512) ?>,
            text: <?php echo json_encode($evenement->titre . ' - ' . $evenement->date_event->isoFormat('D MMM YYYY') . ' à ' . $evenement->lieu . ($evenement->description ? ' - ' . strip_tags(Str::limit($evenement->description, 120)) : ''), 512) ?>,
            url: window.location.href,
        }).catch(() => {});
    } else {
        copyLink();
    }
}

function selectTarif(el) {
    document.querySelectorAll('.show-tarif').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event('change'));
    }
}

function showToast(message, type) {
    const el = document.getElementById('shareToast');
    el.querySelector('.toast-body').textContent = message;
    el.className = 'toast align-items-center text-bg-' + type + ' border-0';
    const toast = bootstrap.Toast.getOrCreateInstance(el, { autohide: true, delay: 3000 });
    toast.show();
}

<?php if($evenement->gratuit): ?>
document.addEventListener('DOMContentLoaded', function() {
    const qty = document.getElementById('quantiteInputG');
    const hiddenQuantiteG = document.getElementById('hiddenQuantiteG');
    document.getElementById('qtyMinusG').addEventListener('click', function() {
        const v = parseInt(qty.value);
        if (v > 1) { qty.value = v - 1; hiddenQuantiteG.value = v - 1; }
    });
    document.getElementById('qtyPlusG').addEventListener('click', function() {
        const v = parseInt(qty.value);
        if (v < <?php echo e(max(1, $placesRestantes)); ?>) { qty.value = v + 1; hiddenQuantiteG.value = v + 1; }
    });
});
<?php else: ?>
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantiteInput');
    const hiddenQuantite = document.getElementById('hiddenQuantite');
    const hiddenTarifId = document.getElementById('hiddenTarifId');
    const totalDisplay = document.getElementById('totalDisplay');

    const tarifs = <?php echo json_encode($tarifs->map(function($t) { return ['id' => $t->id, 'prix' => $t->prix]; }), 512) ?>;

    function getSelectedTarif() {
        const selected = document.querySelector('input[name="tarif_id"]:checked');
        if (!selected) return null;
        return tarifs.find(t => t.id == selected.value);
    }

    function updateTotal() {
        const tarif = getSelectedTarif();
        const qty = parseInt(quantiteInput.value) || 1;
        if (tarif) {
            totalDisplay.textContent = new Intl.NumberFormat('fr-FR').format(tarif.prix * qty) + ' F';
            hiddenTarifId.value = tarif.id;
        } else {
            totalDisplay.textContent = '--';
        }
        hiddenQuantite.value = qty;
    }

    document.getElementById('qtyMinus').addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v > 1) { quantiteInput.value = v - 1; updateTotal(); }
    });

    document.getElementById('qtyPlus').addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v < <?php echo e(max(1, $placesRestantes)); ?>) { quantiteInput.value = v + 1; updateTotal(); }
    });

    document.querySelectorAll('input[name="tarif_id"]').forEach(radio => {
        radio.addEventListener('change', updateTotal);
    });

    const first = document.querySelector('.show-tarif');
    if (first) selectTarif(first);
    else updateTotal();
});
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/abel/Projects/PassEvent/resources/views/evenement-public/show.blade.php ENDPATH**/ ?>