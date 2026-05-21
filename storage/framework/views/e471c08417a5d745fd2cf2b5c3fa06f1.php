

<?php $__env->startSection('title', 'PassEvent - Billetterie intelligente au Benin'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Hero -->
<section class="hero-section">
    <div class="hero-bg">
        <div class="circle c1"></div>
        <div class="circle c2"></div>
        <div class="circle c3"></div>
        <div class="line l1"></div>
        <div class="line l2"></div>
    </div>
    <div class="container text-center position-relative" style="z-index:2;">
        <h1 class="hero-title">Achetez et vendez vos tickets en quelques clics</h1>
        <p class="hero-subtitle">Billetterie simple et rapide pour vos événements.</p>
        <div class="hero-actions">
            <a href="<?php echo e(route('evenements.public')); ?>" class="btn-hero-primary">
                Acheter un ticket <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <a href="<?php echo e(route('login')); ?>" class="btn-hero-outline">
                Devenir organisateur
            </a>
        </div>
    </div>
</section>

<style>
    .hero-section {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        background: #f7f5f3;
    }
    .hero-bg {
        position: absolute; inset: 0;
        pointer-events: none;
        animation: fadeInBg 1s ease forwards;
    }
    @keyframes fadeInBg { 0%{opacity:0} 100%{opacity:1} }
    .circle {
        position: absolute;
        border-radius: 50%;
        filter: blur(60px);
        opacity: 0.25;
    }
    .c1 { width: 320px; height: 320px; background: #7B3FA0; top: -80px; left: -80px; }
    .c2 { width: 240px; height: 240px; background: #2E7D4F; bottom: -60px; right: -60px; }
    .c3 { width: 180px; height: 180px; background: #7B3FA0; bottom: 20%; left: 50%; }
    .line {
        position: absolute;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(123,63,160,0.15), transparent);
    }
    .l1 { width: 60%; top: 25%; left: 20%; transform: rotate(-2deg); }
    .l2 { width: 40%; top: 65%; right: 10%; transform: rotate(3deg); }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        color: #1d1d1f;
        margin: 0 0 0.75rem;
        line-height: 1.15;
        animation: heroFadeUp 0.6s ease forwards;
    }
    .hero-subtitle {
        font-size: 1.2rem;
        font-weight: 400;
        color: #6c757d;
        margin: 0 0 2rem;
        animation: heroFadeUp 0.6s ease 0.2s both;
    }
    .hero-actions {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        animation: heroFadeUp 0.6s ease 0.4s both;
    }
    @keyframes heroFadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .btn-hero-primary,
    .btn-hero-outline {
        display: inline-flex;
        align-items: center;
        padding: 0.8rem 2rem;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s ease;
        animation: heroScaleIn 0.5s ease 0.6s both;
    }
    @keyframes heroScaleIn {
        0% { opacity: 0; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }
    .btn-hero-primary {
        background: #7B3FA0;
        color: #fff;
        box-shadow: 0 4px 16px rgba(123,63,160,0.3);
    }
    .btn-hero-primary:hover {
        background: #6a1b9a;
        color: #fff;
        box-shadow: 0 6px 24px rgba(123,63,160,0.45);
        transform: translateY(-2px);
    }
    .btn-hero-outline {
        border: 2px solid #2E7D4F;
        color: #2E7D4F;
        background: transparent;
    }
    .btn-hero-outline:hover {
        background: rgba(46,125,79,0.08);
        transform: scale(1.02);
    }

    @media (max-width: 767.98px) {
        .hero-section { min-height: 55vh; padding: 1.5rem; }
        .hero-title { font-size: 1.8rem; }
        .hero-subtitle { font-size: 1rem; }
        .hero-actions { flex-direction: column; }
        .btn-hero-primary,
        .btn-hero-outline { width: 100%; justify-content: center; }
    }
</style>

<!-- Evenements a venir -->
<section class="section-events">
    <div class="container">
        <div class="section-header">
            <h2>Événements à venir</h2>
            <p>Trouvez l'événement parfait pour vous</p>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <!-- Search bar -->
            <form action="<?php echo e(route('accueil')); ?>" method="GET" class="filter-search">
                <div class="search-wrap">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Rechercher par nom, catégorie, lieu..." value="<?php echo e($q ?? ''); ?>">
                </div>
                <?php if($selectedCategorie): ?>
                    <input type="hidden" name="categorie" value="<?php echo e($selectedCategorie); ?>">
                <?php endif; ?>
                <?php if($selectedDate): ?>
                    <input type="hidden" name="date" value="<?php echo e($selectedDate); ?>">
                <?php endif; ?>
                <button type="submit" class="btn-search"><i class="bi bi-search"></i></button>
                <?php if($q || $selectedCategorie || $selectedDate): ?>
                    <a href="<?php echo e(route('accueil')); ?>" class="btn-clear"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>

            <div class="filter-bar">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="filter-label">Catégories :</span>
                    <a href="<?php echo e(route('accueil')); ?><?php echo e($q ? '?q=' . $q : ''); ?><?php echo e($selectedDate ? ($q ? '&date=' . $selectedDate : '?date=' . $selectedDate) : ''); ?>"
                       class="chip <?php echo e(!$selectedCategorie ? 'active' : ''); ?>">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Toutes
                    </a>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('accueil')); ?>?categorie=<?php echo e($cat); ?><?php echo e($q ? '&q=' . $q : ''); ?><?php echo e($selectedDate ? '&date=' . $selectedDate : ''); ?>"
                           class="chip <?php echo e($selectedCategorie == $cat ? 'active' : ''); ?>">
                            <?php echo e(ucfirst($cat)); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-label"><i class="bi bi-funnel"></i> Date :</span>
                    <form action="<?php echo e(route('accueil')); ?>" method="GET">
                        <?php if($q): ?><input type="hidden" name="q" value="<?php echo e($q); ?>"><?php endif; ?>
                        <?php if($selectedCategorie): ?><input type="hidden" name="categorie" value="<?php echo e($selectedCategorie); ?>"><?php endif; ?>
                        <select name="date" class="filter-select" onchange="this.form.submit()">
                            <option value="">Toutes dates</option>
                            <option value="weekend" <?php echo e(($selectedDate ?? '') == 'weekend' ? 'selected' : ''); ?>>Ce weekend</option>
                            <option value="mois" <?php echo e(($selectedDate ?? '') == 'mois' ? 'selected' : ''); ?>>Ce mois</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Events grid -->
        <?php if($evenementsVedettes->isNotEmpty()): ?>
            <div class="events-grid">
                <?php $__currentLoopData = $evenementsVedettes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evenement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
                        $prixEtudiant = $evenement->tarifs->where('categorie', 'etudiant')->min('prix');
                        $prixExterne = $evenement->tarifs->where('categorie', 'externe')->min('prix');
                        $prixDernier = $evenement->tarifs->min('prix');
                    ?>
                    <div class="event-col">
                        <a href="<?php echo e(route('evenements.public.show', $evenement->id)); ?>" class="ev-card">
                            <div class="ev-img">
                                <?php if($evenement->image): ?>
                                    <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="<?php echo e($evenement->titre); ?>">
                                <?php else: ?>
                                    <div class="ev-img-placeholder">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if($evenement->categorie): ?>
                                    <span class="ev-badge"><?php echo e(ucfirst($evenement->categorie)); ?></span>
                                <?php endif; ?>
                                <?php if($estComplet): ?>
                                    <span class="ev-badge ev-badge-complet">Complet</span>
                                <?php endif; ?>
                            </div>
                            <div class="ev-body">
                                <h6 class="ev-title"><?php echo e($evenement->titre); ?></h6>
                                <p class="ev-meta">
                                    <i class="bi bi-calendar3"></i> <?php echo e($evenement->date_event->format('d M Y')); ?><br>
                                    <i class="bi bi-geo-alt"></i> <?php echo e(Str::limit($evenement->lieu, 25)); ?>

                                </p>
                                <?php if($evenement->gratuit): ?>
                                    <div class="ev-price">Entrée <strong>Gratuit</strong></div>
                                <?php elseif($prixDernier): ?>
                                    <div class="ev-price">À partir de <strong><?php echo e(number_format($prixDernier, 0, ',', ' ')); ?> F</strong></div>
                                <?php else: ?>
                                    <div class="ev-price"><span class="text-muted">Tarifs non configurés</span></div>
                                <?php endif; ?>
                                <div class="ev-gauge">
                                    <div class="d-flex justify-content-between" style="font-size:0.68rem;">
                                        <span><?php echo e($placesRestantes); ?> places</span>
                                        <span><?php echo e($remplissage); ?>%</span>
                                    </div>
                                    <div class="gauge-track"><div class="gauge-fill" style="width:<?php echo e(min($remplissage,100)); ?>%"></div></div>
                                </div>
                                <?php if($estComplet): ?>
                                    <span class="ev-btn disabled">Complet</span>
                                <?php else: ?>
                                    <span class="ev-btn">
                                        <?php if($evenement->gratuit): ?>
                                            <i class="bi bi-check-circle me-1"></i> Participer
                                        <?php else: ?>
                                            <i class="bi bi-ticket-perforated me-1"></i> Acheter ticket
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>Aucun événement disponible</h5>
                <p>Revenez plus tard pour découvrir nos prochains événements.</p>
                <?php if($q || $selectedCategorie || $selectedDate): ?>
                    <a href="<?php echo e(route('accueil')); ?>" class="btn-outline"><i class="bi bi-arrow-left me-1"></i> Voir tous les événements</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($evenementsVedettes->isNotEmpty()): ?>
            <div class="text-center mt-4">
                <a href="<?php echo e(route('evenements.public')); ?>" class="btn-outline">Voir tous les événements <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Comment ca marche -->
<section class="section-steps">
    <div class="container">
        <div class="section-header">
            <h2>Comment ça marche ?</h2>
            <p>4 étapes simples pour obtenir votre billet</p>
        </div>
        <div class="steps-grid" id="steps-grid">
            <div class="step-item" data-step="1">
                <div class="step-num">1</div>
                <h6>Choisir</h6>
                <p>Trouvez l'événement qui vous intéresse</p>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-num">2</div>
                <h6>Remplir</h6>
                <p>Entrez vos informations et choisissez votre tarif</p>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-num">3</div>
                <h6>Payer</h6>
                <p>Paiement sécurisé via KKiaPay (MTN, Moov, Celtiis)</p>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-num">4</div>
                <h6>Recevoir</h6>
                <p>Recevez votre ticket PDF par email</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Recuperation -->
<section class="section-cta">
    <div class="container">
        <div class="cta-card">
            <h3>Vous avez perdu votre ticket ?</h3>
            <p>Rendez-vous sur la page de récupération avec vos informations</p>
            <a href="<?php echo e(route('tickets.recuperer')); ?>" class="btn-cta">
                <i class="bi bi-ticket-perforated me-1"></i> Récupérer mon ticket
            </a>
        </div>
    </div>
</section>

<style>
/* ===== Shared ===== */
.section-header {
    text-align: center;
    margin-bottom: 2rem;
}
.section-header h2 {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1d1d1f;
    margin: 0 0 0.3rem;
}
.section-header p {
    color: #6c757d;
    font-size: 0.95rem;
    margin: 0;
}

/* ===== Events section ===== */
.section-events {
    padding: 4rem 0;
    background: #fff;
}
.filter-card {
    background: #f7f5f3;
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 2rem;
}
.filter-search {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.search-wrap {
    position: relative;
    flex: 1;
}
.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9a9a9a;
}
.search-input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    font-size: 0.88rem;
    background: #fff;
    outline: none;
    transition: border-color 0.2s;
}
.search-input:focus {
    border-color: #7B3FA0;
    box-shadow: 0 0 0 3px rgba(123,63,160,0.1);
}
.btn-search {
    background: #7B3FA0;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
    transition: background 0.2s;
}
.btn-search:hover { background: #6a1b9a; }
.btn-clear {
    display: inline-flex;
    align-items: center;
    padding: 0.6rem 0.8rem;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
    background: #fff;
}
.btn-clear:hover { border-color: #dc3545; color: #dc3545; }

.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
}
.filter-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1.5px solid #e5e5e5;
    background: #fff;
    color: #3d4345;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
}
.chip:hover { border-color: #7B3FA0; color: #7B3FA0; }
.chip.active { background: #7B3FA0; border-color: #7B3FA0; color: #fff; }
.chip.active:hover { background: #6a1b9a; }
.filter-select {
    padding: 0.4rem 0.8rem;
    border: 1.5px solid #e5e5e5;
    border-radius: 10px;
    font-size: 0.82rem;
    background: #fff;
    outline: none;
}
.filter-select:focus { border-color: #7B3FA0; }

.events-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
@media (max-width: 991px) { .events-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .events-grid { grid-template-columns: 1fr; } }

.event-col { display: flex; }
.ev-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #eee;
    text-decoration: none;
    transition: all 0.25s ease;
}
.ev-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    transform: translateY(-3px);
    border-color: #e0ddd9;
}
.ev-img {
    position: relative;
    height: 150px;
    overflow: hidden;
    background: #f0eeec;
}
.ev-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.ev-img-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #7B3FA0, #6a1b9a);
    color: #fff;
    font-size: 2.2rem;
}
.ev-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(255,255,255,0.92);
    color: #7B3FA0;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    backdrop-filter: blur(4px);
}
.ev-badge-complet {
    left: auto;
    right: 8px;
    background: #dc3545;
    color: #fff;
}
.ev-body {
    padding: 0.9rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.ev-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1d1d1f;
    margin: 0 0 0.4rem;
    line-height: 1.3;
}
.ev-meta {
    font-size: 0.78rem;
    color: #6c757d;
    margin: 0 0 0.5rem;
    line-height: 1.4;
}
.ev-meta i { margin-right: 0.3rem; }
.ev-price {
    font-size: 0.82rem;
    margin-bottom: 0.5rem;
}
.ev-price strong {
    font-size: 1rem;
    color: #2E7D4F;
}
.ev-gauge {
    margin-bottom: 0.6rem;
}
.gauge-track {
    height: 5px;
    background: #e5e5e5;
    border-radius: 3px;
    margin-top: 3px;
}
.gauge-fill {
    height: 100%;
    border-radius: 3px;
    background: #7B3FA0;
    transition: width 0.3s;
}
.ev-btn {
    display: block;
    width: 100%;
    padding: 0.4rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    text-align: center;
    background: #2E7D4F;
    color: #fff;
    transition: background 0.2s;
    margin-top: auto;
}
.ev-btn:hover { background: #256a42; }
.ev-btn.disabled {
    background: #98919b;
    pointer-events: none;
}

.empty-state {
    text-align: center;
    padding: 3rem 0;
}
.empty-state i {
    font-size: 3rem;
    color: #c0c0c0;
    display: block;
    margin-bottom: 1rem;
}
.empty-state h5 { color: #6c757d; margin-bottom: 0.3rem; }
.empty-state p { color: #9a9a9a; font-size: 0.9rem; }
.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.55rem 1.5rem;
    border: 2px solid #7B3FA0;
    border-radius: 10px;
    color: #7B3FA0;
    font-weight: 600;
    font-size: 0.88rem;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-outline:hover { background: rgba(123,63,160,0.06); }

/* ===== Steps section ===== */
.section-steps {
    padding: 4rem 0;
    background: #f7f5f3;
}
.steps-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}
@media (max-width: 767px) { .steps-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 420px) { .steps-grid { grid-template-columns: 1fr; } }

.step-item {
    text-align: center;
    padding: 2rem 1rem;
    background: #fff;
    border-radius: 14px;
    border: 1px solid #eee;
    transition: all 0.25s;
}
.step-item:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,0.05);
    transform: translateY(-2px);
}
.step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #7B3FA0;
    color: #fff;
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 0.8rem;
}
.step-item h6 {
    font-size: 1rem;
    font-weight: 700;
    color: #1d1d1f;
    margin: 0 0 0.3rem;
}
.step-item p {
    font-size: 0.82rem;
    color: #6c757d;
    margin: 0;
}

/* ===== CTA section ===== */
.section-cta {
    padding: 4rem 0;
    background: #fff;
}
.cta-card {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    background: linear-gradient(135deg, #7B3FA0, #6a1b9a);
    color: #fff;
    padding: 3rem 2rem;
    border-radius: 18px;
}
.cta-card h3 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0 0 0.5rem;
}
.cta-card p {
    font-size: 0.95rem;
    opacity: 0.9;
    margin: 0 0 1.5rem;
}
.btn-cta {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    background: #fff;
    color: #7B3FA0;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-cta:hover { background: #f0eeec; transform: translateY(-2px); }

/* ===== Steps animation ===== */
.step-item { opacity: 0; transform: translateY(30px); transition: opacity 0.6s ease, transform 0.6s ease; }
.step-item.visible { opacity: 1; transform: translateY(0); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('steps-grid');
    if (!grid) return;
    const steps = grid.querySelectorAll('.step-item');
    const observer = new IntersectionObserver(function(entries) {
        if (entries[0].isIntersecting) {
            steps.forEach(function(el, i) {
                setTimeout(function() {
                    el.classList.add('visible');
                }, i * 1000);
            });
            observer.disconnect();
        }
    }, { threshold: 0.3 });
    observer.observe(grid);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/site/accueil.blade.php ENDPATH**/ ?>