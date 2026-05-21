<?php $__env->startSection('title', 'Tous les événements - PassEvent'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Événements</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- ===== En-tête de la page ===== -->
<section class="ev-header">
    <!-- Éléments décoratifs en arrière-plan -->
    <div class="ev-header-bg">
        <div class="dot d1"></div>
        <div class="dot d2"></div>
        <div class="dot d3"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <h2 class="ev-header-title">Tous les événements</h2>
        <p class="ev-header-sub">Trouvez l'événement parfait pour vous</p>
    </div>
</section>

<!-- ===== Filtres de recherche ===== -->
<section class="ev-filters">
    <div class="container">
        <div class="filter-card-events">
            <!-- Barre de recherche et tri par date -->
            <form action="<?php echo e(route('evenements.public')); ?>" method="GET" class="filter-row">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" class="search-field" placeholder="Rechercher par nom, catégorie, lieu..." value="<?php echo e($q ?? ''); ?>">
                </div>
                <?php if($selectedCategorie): ?><input type="hidden" name="categorie" value="<?php echo e($selectedCategorie); ?>"><?php endif; ?>
                <?php if($selectedDate): ?><input type="hidden" name="date" value="<?php echo e($selectedDate); ?>"><?php endif; ?>
                <button class="filter-btn-submit" type="submit"><i class="bi bi-search"></i></button>
                <?php if($q || $selectedCategorie || $selectedDate): ?>
                    <a href="<?php echo e(route('evenements.public')); ?>" class="filter-btn-clear"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-funnel"></i></span>
                    <form action="<?php echo e(route('evenements.public')); ?>" method="GET" class="d-inline">
                        <?php if($q): ?><input type="hidden" name="q" value="<?php echo e($q); ?>"><?php endif; ?>
                        <?php if($selectedCategorie): ?><input type="hidden" name="categorie" value="<?php echo e($selectedCategorie); ?>"><?php endif; ?>
                        <select name="date" class="filter-date-select" onchange="this.form.submit()">
                            <option value="">Toutes dates</option>
                            <option value="weekend" <?php echo e(($selectedDate ?? '') == 'weekend' ? 'selected' : ''); ?>>Ce weekend</option>
                            <option value="mois" <?php echo e(($selectedDate ?? '') == 'mois' ? 'selected' : ''); ?>>Ce mois</option>
                        </select>
                    </form>
                </div>
            </form>
            <!-- Piliers de catégories -->
            <div class="filter-chips">
                <a href="<?php echo e(route('evenements.public')); ?><?php echo e($q ? '?q=' . $q : ''); ?><?php echo e($selectedDate ? ($q ? '&date=' . $selectedDate : '?date=' . $selectedDate) : ''); ?>"
                   class="chip <?php echo e(!$selectedCategorie ? 'active' : ''); ?>">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Toutes
                </a>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('evenements.public')); ?>?categorie=<?php echo e($cat); ?><?php echo e($q ? '&q=' . $q : ''); ?><?php echo e($selectedDate ? '&date=' . $selectedDate : ''); ?>"
                       class="chip <?php echo e($selectedCategorie == $cat ? 'active' : ''); ?>"><?php echo e(ucfirst($cat)); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== Grille des événements ===== -->
<section class="ev-grid-section">
    <div class="container">
        <?php if($evenements->count() > 0): ?>
            <div class="ev-grid">
                <?php $__currentLoopData = $evenements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evenement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
                        $prixDernier = $evenement->tarifs->min('prix');
                    ?>
                    <div class="ev-grid-col">
                        <a href="<?php echo e(route('evenements.public.show', $evenement->id)); ?>" class="ev-card">
                            <!-- Image de l'événement -->
                            <div class="ev-card-img">
                                <?php if($evenement->image): ?>
                                    <img src="<?php echo e(asset('storage/' . $evenement->image)); ?>" alt="<?php echo e($evenement->titre); ?>">
                                <?php else: ?>
                                    <div class="ev-card-placeholder"><i class="bi bi-calendar-event"></i></div>
                                <?php endif; ?>
                                <?php if($evenement->categorie): ?>
                                    <span class="ev-card-badge"><?php echo e(ucfirst($evenement->categorie)); ?></span>
                                <?php endif; ?>
                                <?php if($estComplet): ?>
                                    <span class="ev-card-badge ev-card-badge-complet">Complet</span>
                                <?php endif; ?>
                            </div>
                            <!-- Contenu de la carte -->
                            <div class="ev-card-body">
                                <h5 class="ev-card-title"><?php echo e($evenement->titre); ?></h5>
                                <p class="ev-card-meta">
                                    <i class="bi bi-calendar3"></i> <?php echo e($evenement->date_event->format('d M Y')); ?><br>
                                    <i class="bi bi-geo-alt"></i> <?php echo e($evenement->lieu); ?>

                                </p>
                                <?php if($evenement->gratuit): ?>
                                    <div class="ev-card-price">Entrée <strong>Gratuit</strong></div>
                                <?php elseif($prixDernier): ?>
                                    <div class="ev-card-price">À partir de <strong><?php echo e(number_format($prixDernier, 0, ',', ' ')); ?> F</strong></div>
                                <?php endif; ?>
                                <!-- Jauge de places restantes -->
                                <div class="ev-card-gauge">
                                    <div class="d-flex justify-content-between" style="font-size:0.7rem;">
                                        <span><?php echo e($placesRestantes); ?> places</span>
                                        <span><?php echo e($remplissage); ?>%</span>
                                    </div>
                                    <div class="gauge"><div class="gauge-fill" style="width:<?php echo e(min($remplissage,100)); ?>%"></div></div>
                                </div>
                                <?php if($estComplet): ?>
                                    <span class="ev-card-btn disabled"><i class="bi bi-slash-circle me-1"></i> Complet</span>
                                <?php else: ?>
                                    <span class="ev-card-btn">
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

            <!-- Pagination -->
            <?php if($evenements->hasPages()): ?>
                <div class="pagination-wrap"> <?php echo e($evenements->links()); ?> </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- État vide -->
            <div class="ev-empty">
                <i class="bi bi-calendar-x"></i>
                <h5>Aucun événement trouvé</h5>
                <p>
                    <?php if($q || $selectedCategorie || $selectedDate): ?>
                        Essayez une autre catégorie ou un autre terme de recherche.
                    <?php else: ?>
                        Revenez plus tard pour découvrir nos prochains événements.
                    <?php endif; ?>
                </p>
                <?php if($q || $selectedCategorie || $selectedDate): ?>
                    <a href="<?php echo e(route('evenements.public')); ?>" class="btn-outline"><i class="bi bi-arrow-left me-1"></i> Voir tous</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* ===== En-tête de la page ===== */
.ev-header {
    padding: 3rem 0;
    background: linear-gradient(135deg, #7B3FA0, #6a1b9a);
    color: #fff;
    position: relative;
    overflow: hidden;
    text-align: center;
}
.ev-header-bg {
    position: absolute; inset: 0;
    pointer-events: none;
}
.dot {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.2;
}
.d1 { width: 200px; height: 200px; background: #fff; top: -60px; right: -60px; }
.d2 { width: 140px; height: 140px; background: #fff; bottom: -40px; left: 20%; }
.d3 { width: 100px; height: 100px; background: #fff; top: 30%; left: -30px; }
.ev-header-title {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.3rem;
    animation: fadeUp 0.5s ease forwards;
}
.ev-header-sub {
    font-size: 1rem;
    opacity: 0.85;
    margin: 0;
    animation: fadeUp 0.5s ease 0.15s both;
}
@keyframes fadeUp { 0%{opacity:0;transform:translateY(12px)} 100%{opacity:1;transform:translateY(0)} }

/* ===== Barre de filtres ===== */
.ev-filters {
    padding: 1.5rem 0;
    background: #f7f5f3;
    border-bottom: 1px solid #eee;
}
.filter-card-events {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    border: 1px solid #eee;
}
.filter-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.search-box {
    position: relative;
    flex: 1;
    min-width: 200px;
}
.search-box .bi-search {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #9a9a9a;
}
.search-field {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    font-size: 0.88rem;
    outline: none;
    transition: border-color 0.2s;
}
.search-field:focus { border-color: #7B3FA0; box-shadow: 0 0 0 3px rgba(123,63,160,0.1); }
.filter-btn-submit {
    background: #7B3FA0;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.6rem 1rem;
    transition: background 0.2s;
}
.filter-btn-submit:hover { background: #6a1b9a; }
.filter-btn-clear {
    display: inline-flex;
    align-items: center;
    padding: 0.6rem 0.8rem;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    color: #6c757d;
    text-decoration: none;
    background: #fff;
    transition: all 0.2s;
}
.filter-btn-clear:hover { border-color: #dc3545; color: #dc3545; }
.filter-date-group {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-left: auto;
}
.filter-date-label { color: #6c757d; font-size: 0.9rem; }
.filter-date-select {
    padding: 0.4rem 0.8rem;
    border: 1.5px solid #e5e5e5;
    border-radius: 10px;
    font-size: 0.82rem;
    background: #fff;
    outline: none;
}
.filter-date-select:focus { border-color: #7B3FA0; }

/* Piliers de catégories */
.filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
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
    text-decoration: none;
    transition: all 0.15s;
}
.chip:hover { border-color: #7B3FA0; color: #7B3FA0; }
.chip.active { background: #7B3FA0; border-color: #7B3FA0; color: #fff; }
.chip.active:hover { background: #6a1b9a; }

/* ===== Grille des événements ===== */
.ev-grid-section {
    padding: 3rem 0;
    background: #fff;
    min-height: 300px;
}
.ev-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
@media (max-width: 991px) { .ev-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .ev-grid { grid-template-columns: 1fr; } }

.ev-grid-col {
    display: flex;
    animation: cardIn 0.5s ease both;
}
.ev-grid-col:nth-child(1) { animation-delay: 0s; }
.ev-grid-col:nth-child(2) { animation-delay: 0.08s; }
.ev-grid-col:nth-child(3) { animation-delay: 0.16s; }
.ev-grid-col:nth-child(4) { animation-delay: 0.24s; }
.ev-grid-col:nth-child(5) { animation-delay: 0.32s; }
.ev-grid-col:nth-child(6) { animation-delay: 0.40s; }
@keyframes cardIn {
    0% { opacity: 0; transform: translateY(20px) scale(0.97); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

.ev-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #eee;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.25,0.46,0.45,0.94);
}
.ev-card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.08);
    transform: translateY(-4px) scale(1.01);
    border-color: #e0ddd9;
}

.ev-card-img {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: #f0eeec;
}
.ev-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}
.ev-card:hover .ev-card-img img { transform: scale(1.05); }
.ev-card-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #7B3FA0, #6a1b9a);
    color: #fff;
    font-size: 3rem;
}
.ev-card-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255,255,255,0.92);
    color: #7B3FA0;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: 10px;
    backdrop-filter: blur(4px);
}
.ev-card-badge-complet {
    left: auto;
    right: 10px;
    background: #dc3545;
    color: #fff;
}

.ev-card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.ev-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1d1d1f;
    margin: 0 0 0.4rem;
    line-height: 1.3;
}
.ev-card-meta {
    font-size: 0.82rem;
    color: #6c757d;
    margin: 0 0 0.6rem;
    line-height: 1.4;
}
.ev-card-meta i { margin-right: 0.3rem; }
.ev-card-price {
    font-size: 0.82rem;
    margin-bottom: 0.5rem;
}
.ev-card-price strong {
    font-size: 1rem;
    color: #2E7D4F;
}
.ev-card-gauge { margin-bottom: 0.6rem; }
.gauge {
    height: 4px;
    background: #e5e5e5;
    border-radius: 2px;
    margin-top: 3px;
    overflow: hidden;
}
.gauge-fill {
    height: 100%;
    border-radius: 2px;
    background: #7B3FA0;
    transition: width 0.4s;
}
.ev-card-btn {
    display: block;
    width: 100%;
    padding: 0.45rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    background: #2E7D4F;
    color: #fff;
    transition: background 0.2s, transform 0.2s;
    margin-top: auto;
}
.ev-card-btn:hover { background: #256a42; transform: scale(1.02); }
.ev-card-btn.disabled {
    background: #98919b;
    pointer-events: none;
}

/* ===== État vide ===== */
.ev-empty {
    text-align: center;
    padding: 4rem 0;
}
.ev-empty i {
    font-size: 3.5rem;
    color: #c0c0c0;
    display: block;
    margin-bottom: 1rem;
}
.ev-empty h5 { color: #6c757d; margin-bottom: 0.3rem; }
.ev-empty p { color: #9a9a9a; font-size: 0.9rem; }
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

/* ===== Pagination ===== */
.pagination-wrap { text-align: center; margin-top: 2rem; }
.pagination-wrap nav { display: inline-block; }
.pagination-wrap .pagination { display: flex; gap: 0.3rem; list-style: none; padding: 0; margin: 0; }
.pagination-wrap .page-item { display: inline; }
.pagination-wrap .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid #e5e5e5;
    color: #3d4345;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    background: #fff;
}
.pagination-wrap .page-link:hover { border-color: #7B3FA0; color: #7B3FA0; }
.pagination-wrap .active .page-link { background: #7B3FA0; border-color: #7B3FA0; color: #fff; }
.pagination-wrap .disabled .page-link { opacity: 0.4; pointer-events: none; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/evenement-public/index.blade.php ENDPATH**/ ?>