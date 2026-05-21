<?php $__env->startSection('title', $evenement->titre . ' - PassEvent'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('evenements.public')); ?>">Evenements</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo e(Str::limit($evenement->titre, 40)); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
    $estComplet = $placesRestantes <= 0;
    $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
?>

<style>
    .hero-event {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        margin-top: 1.5rem;
        background: #f8f6f9;
    }
    .hero-event img {
        width: 100%;
        height: 380px;
        object-fit: cover;
        object-position: center 20%;
        display: block;
    }
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(123,63,160,0.6) 0%, rgba(123,63,160,0.15) 50%, transparent 100%);
        pointer-events: none;
    }
    .hero-content {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 3rem 2rem 1.5rem;
        background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
    }
    .hero-content h1 {
        color: #fff;
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 12px rgba(0,0,0,0.2);
    }
    .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.5rem;
        color: rgba(255,255,255,0.92);
        font-size: 0.88rem;
    }
    .hero-meta i { margin-right: 0.3rem; }
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.3rem 0.9rem;
        border-radius: 20px;
        margin-bottom: 0.6rem;
    }
    .event-card {
        border-radius: 14px;
        border: 1px solid #eee;
        background: #fff;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .event-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
    .event-card .card-body { padding: 1.75rem; }
    .event-card .card-body:not(:last-child) { border-bottom: 1px solid #f5f5f5; }
    .tarif-card {
        border: 2px solid #eee;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .tarif-card:hover { border-color: #7B3FA0; background: rgba(123,63,160,0.03); }
    .tarif-card.selected { border-color: #7B3FA0; background: rgba(123,63,160,0.06); }
    .tarif-card input[type="radio"] { display: none; }
    .qty-control {
        display: inline-flex; align-items: center;
        border: 1px solid #e5e5e5; border-radius: 10px; overflow: hidden;
    }
    .qty-control button {
        width: 38px; height: 38px; border: none;
        background: #f7f5f3; color: #333;
        font-size: 1.1rem; cursor: pointer; transition: background 0.15s;
    }
    .qty-control button:hover { background: #e8e4e0; }
    .qty-control input {
        width: 52px; height: 38px; border: none;
        text-align: center; font-weight: 700; font-size: 0.95rem;
    }
    .qty-control input:focus { outline: none; }
    .form-control-custom {
        border: 1.5px solid #e5e5e5;
        border-radius: 10px;
        padding: 0.7rem 1rem;
        font-size: 0.88rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control-custom:focus {
        border-color: #7B3FA0;
        box-shadow: 0 0 0 3px rgba(123,63,160,0.1);
    }
    .sticky-sidebar { position: sticky; top: 90px; }
    .info-chip {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1rem; border-radius: 10px;
        background: #f8f6f9; font-size: 0.85rem;
    }
    @media (max-width: 767.98px) {
        .hero-event img { height: 240px; object-position: center; }
        .hero-content { padding: 2rem 1.25rem 1rem; }
        .hero-content h1 { font-size: 1.4rem; }
        .event-card .card-body { padding: 1.25rem; }
        .sticky-sidebar { position: static; }
    }
</style>

<div class="container">
    
    <div class="hero-event">
        <?php if($evenement->image): ?>
            <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="<?php echo e($evenement->titre); ?>" loading="lazy">
        <?php else: ?>
            <div style="height: 380px; background: linear-gradient(135deg, #7B3FA0, #2E7D4F);"></div>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <?php if($evenement->categorie): ?>
                <span class="hero-badge"><?php echo e(ucfirst($evenement->categorie)); ?></span>
            <?php endif; ?>
            <h1><?php echo e($evenement->titre); ?></h1>
            <div class="hero-meta">
                <span><i class="bi bi-calendar3"></i><?php echo e($evenement->date_event->format('d M Y')); ?></span>
                <span><i class="bi bi-clock"></i><?php echo e($evenement->date_event->format('H:i')); ?></span>
                <span><i class="bi bi-geo-alt"></i><?php echo e($evenement->lieu); ?></span>
                <?php if(!$estComplet): ?>
                    <span><i class="bi bi-people"></i><?php echo e(number_format($placesRestantes, 0, ',', ' ')); ?> places</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="row g-4 py-4">
        
        <div class="col-lg-7">
            
            <div class="d-flex flex-wrap gap-3 mb-4">
                <div class="info-chip">
                    <i class="bi bi-calendar-check" style="color: #7B3FA0;"></i>
                    <span><strong>Date :</strong> <?php echo e($evenement->date_event->format('d M Y')); ?></span>
                </div>
                <div class="info-chip">
                    <i class="bi bi-clock" style="color: #2E7D4F;"></i>
                    <span><strong>Heure :</strong> <?php echo e($evenement->date_event->format('H:i')); ?></span>
                </div>
                <div class="info-chip">
                    <i class="bi bi-geo-alt" style="color: #e67e22;"></i>
                    <span><strong>Lieu :</strong> <?php echo e($evenement->lieu); ?></span>
                </div>
                <?php if(!$estComplet): ?>
                    <div class="info-chip">
                        <i class="bi bi-people" style="color: #2E7D4F;"></i>
                        <span><strong><?php echo e(number_format($placesRestantes, 0, ',', ' ')); ?></strong> places</span>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="event-card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3" style="color: #333;">
                        <i class="bi bi-info-circle me-2" style="color: #7B3FA0;"></i>Description
                    </h5>
                    <?php if($evenement->description): ?>
                        <p class="mb-0" style="line-height: 1.8; color: #555;"><?php echo e($evenement->description); ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-0">Aucune description disponible.</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="event-card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3" style="color: #333;">
                        <i class="bi bi-pin-map me-2" style="color: #2E7D4F;"></i>Lieu
                    </h5>
                    <div class="rounded-3 overflow-hidden" style="height: 220px; background: #f5f5f5;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12707.298550849!2d2.4244!3d6.3654!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10235780f0b0c787%3A0x1b4a3c7c5a6e0f0!2sCotonou%2C+Benin!5e0!3m2!1sfr!2sbj!4v1" width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

            
            <div class="event-card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3" style="color: #333;">
                        <i class="bi bi-share me-2" style="color: #7B3FA0;"></i>Partager
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/?text=<?php echo e(urlencode($evenement->titre . ' - ' . route('evenements.public.show', $evenement->id))); ?>" target="_blank" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; border-color: #ddd;">
                            <i class="bi bi-whatsapp" style="color: #25D366;"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo e(urlencode($evenement->titre)); ?>&url=<?php echo e(urlencode(route('evenements.public.show', $evenement->id))); ?>" target="_blank" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; border-color: #ddd;">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(route('evenements.public.show', $evenement->id))); ?>" target="_blank" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px; border-color: #ddd;">
                            <i class="bi bi-facebook" style="color: #1877F2;"></i>
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyLink()" style="border-radius: 8px; border-color: #ddd;">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                        <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?php echo e(urlencode($evenement->titre)); ?>&dates=<?php echo e($evenement->date_event->format('Ymd\THis')); ?>/<?php echo e($evenement->date_event->copy()->addHours(2)->format('Ymd\THis')); ?>&details=<?php echo e(urlencode($evenement->description ?? '')); ?>&location=<?php echo e(urlencode($evenement->lieu)); ?>" target="_blank" class="btn btn-outline-secondary btn-sm ms-auto" style="border-radius: 8px; border-color: #ddd;">
                            <i class="bi bi-google" style="color: #4285F4;"></i> Calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-5">
            <div class="sticky-sidebar">
                <div class="event-card">
                    
                    <div class="card-body" style="background: linear-gradient(135deg, rgba(123,63,160,0.04), rgba(46,125,79,0.04));">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(123,63,160,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-ticket-perforated" style="color: #7B3FA0; font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0"><?php echo e($evenement->gratuit ? 'Participer gratuitement' : 'Acheter un billet'); ?></h5>
                                <?php if($estComplet): ?>
                                    <span class="badge bg-danger mt-1">Complet</span>
                                <?php else: ?>
                                    <small class="text-muted"><?php echo e(number_format($placesRestantes, 0, ',', ' ')); ?> places disponibles</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Choisissez votre tarif</label>
                            <?php if($tarifs->isEmpty()): ?>
                                <div class="alert alert-danger py-2" style="font-size: 0.85rem;">Aucun tarif disponible</div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php $__currentLoopData = $tarifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="tarif-card d-block" onclick="selectTarif(this)">
                                            <input type="radio" name="tarif_id" value="<?php echo e($tarif->id); ?>" <?php echo e($loop->first ? 'checked' : ''); ?>>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge me-2" style="background: <?php echo e($tarif->categorie === 'etudiant' ? 'rgba(123,63,160,0.1)' : 'rgba(46,125,79,0.1)'); ?>; color: <?php echo e($tarif->categorie === 'etudiant' ? '#7B3FA0' : '#2E7D4F'); ?>; font-weight: 600;">
                                                        <?php echo e($tarif->categorie === 'etudiant' ? 'Etudiant' : 'Externe'); ?>

                                                    </span>
                                                    <strong><?php echo e($tarif->type === 'normal' ? 'Standard' : 'VIP'); ?></strong>
                                                </div>
                                                <strong style="color: #7B3FA0; font-size: 1.05rem;">
                                                    <?php echo e($evenement->gratuit ? 'Gratuit' : number_format($tarif->prix, 0, ',', ' ') . ' F'); ?>

                                                </strong>
                                            </div>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quantite</label>
                            <div class="qty-control">
                                <button type="button" id="qtyMinus">-</button>
                                <input type="number" id="quantiteInput" value="1" min="1" max="5" readonly>
                                <button type="button" id="qtyPlus">+</button>
                            </div>
                        </div>

                        <hr>

                        
                        <form action="<?php echo e(route('evenements.achat', $evenement->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="tarif_id" id="hiddenTarifId">
                            <input type="hidden" name="quantite" id="hiddenQuantite" value="1">

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Prenom et Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" name="nom_acheteur" value="<?php echo e(old('nom_acheteur')); ?>" placeholder="Ex: Kofi Mensah" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-custom" name="email_acheteur" value="<?php echo e(old('email_acheteur')); ?>" placeholder="votre@email.com" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Telephone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control form-control-custom" name="telephone_acheteur" value="<?php echo e(old('telephone_acheteur')); ?>" placeholder="+229 XX XX XX XX" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Code promo <span class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="text" class="form-control form-control-custom" name="code_promo" value="<?php echo e(old('code_promo')); ?>" placeholder="PROMO-XXXXXX" style="text-transform: uppercase;">
                            </div>

                            
                            <div class="p-3 rounded-3 mb-3" style="background: #f8f6f9;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><?php echo e($evenement->gratuit ? 'Billets' : 'Total a payer'); ?></span>
                                    <span class="fw-bold" style="font-size: 1.3rem; color: #7B3FA0;" id="totalDisplay">--</span>
                                </div>
                            </div>

                            <?php if($evenement->gratuit): ?>
                                <button type="submit" class="btn w-100 py-3" id="btnPayer" style="background: #7B3FA0; color: #fff; border-radius: 10px; font-weight: 700; font-size: 1rem; border: none;" <?php echo e($tarifs->isEmpty() || $estComplet ? 'disabled' : ''); ?>>
                                    <i class="bi bi-check-circle me-2"></i> Reserver gratuitement
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn w-100 py-3" id="btnPayer" style="background: #7B3FA0; color: #fff; border-radius: 10px; font-weight: 700; font-size: 1rem; border: none;" <?php echo e($tarifs->isEmpty() || $estComplet ? 'disabled' : ''); ?>>
                                    <i class="bi bi-shield-lock me-2"></i> Payer avec KKiaPay
                                </button>
                            <?php endif; ?>

                            <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.75rem;">
                                <i class="bi bi-shield-check me-1" style="color: #2E7D4F;"></i>
                                Paiement 100% securise — Ticket PDF recu par email
                            </p>
                        </form>
                    </div>
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
                    <div class="event-card mt-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3" style="color: #333;">
                                <i class="bi bi-calendar-event me-2" style="color: #7B3FA0;"></i>Autres evenements
                            </h6>
                            <div class="d-flex flex-column gap-3">
                                <?php $__currentLoopData = $autresEvenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $autre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('evenements.public.show', $autre->id)); ?>" class="text-decoration-none">
                                        <div class="d-flex gap-3 align-items-center p-2 rounded-3" style="background: #f8f6f9;">
                                            <?php if($autre->image): ?>
                                                <img src="<?php echo e(asset('storage/' . $autre->image)); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; flex-shrink: 0;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #7B3FA0, #2E7D4F); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="bi bi-calendar-event text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-bold" style="font-size: 0.82rem; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e($autre->titre); ?></div>
                                                <div class="text-muted" style="font-size: 0.72rem;">
                                                    <i class="bi bi-calendar3 me-1"></i><?php echo e($autre->date_event->format('d M Y')); ?>

                                                </div>
                                            </div>
                                            <i class="bi bi-chevron-right" style="color: #aaa; font-size: 0.8rem;"></i>
                                        </div>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
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
        const isFree = <?php echo json_encode($evenement->gratuit ?? false, 15, 512) ?>;
        if (tarif) {
            totalDisplay.textContent = isFree ? 'Gratuit' : new Intl.NumberFormat('fr-FR').format(tarif.prix * qty) + ' F';
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
        if (v < 5) { quantiteInput.value = v + 1; updateTotal(); }
    });

    document.querySelectorAll('input[name="tarif_id"]').forEach(radio => {
        radio.addEventListener('change', updateTotal);
    });

    updateTotal();
});

function selectTarif(el) {
    document.querySelectorAll('.tarif-card').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href);
    alert('Lien copie !');
}

document.addEventListener('DOMContentLoaded', function() {
    const firstTarif = document.querySelector('.tarif-card');
    if (firstTarif) selectTarif(firstTarif);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/evenement-public/show.blade.php ENDPATH**/ ?>