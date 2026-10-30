@extends('layouts.public')

@section('title', "Programme d'affiliation PaxEvent — Co-parrainage d'événements")
@section('description', "Promouvez les événements ouverts au co-parrainage sur PaxEvent et percevez une commission sur les ventes éligibles attribuées à votre lien. Inscription gratuite et suivi depuis votre tableau de bord.")

@section('styles')
<noscript><style>.aff-gain-card { opacity: 1 !important; transform: none !important; }</style></noscript>
<style>
    .aff-hero {
        background: linear-gradient(135deg, #542680 0%, #3d1a5c 100%);
        color: #fff;
        padding: 4.5rem 0;
        position: relative;
        overflow: hidden;
    }
    .aff-hero::after {
        content: '';
        position: absolute;
        right: -80px; top: -80px;
        width: 320px; height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(254,213,20,0.18) 0%, rgba(254,213,20,0) 70%);
    }
    .aff-hero .container { position: relative; z-index: 1; }
    .aff-hero-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
        align-items: center;
        gap: clamp(2rem, 4vw, 4rem);
    }
    .aff-hero-copy { max-width: 640px; }
    .aff-hero h1 { max-width: none; font-size: clamp(1.75rem, 3.5vw, 3rem); font-weight: 800; line-height: 1.08; letter-spacing: -0.035em; }
    .aff-hero h1 span { display: block; white-space: nowrap; }
    .aff-hero .lead { color: rgba(255,255,255,0.88); }
    .aff-hero .micro { color: rgba(255,255,255,0.65); font-size: 0.85rem; }
    .aff-hero-visual { position: relative; width: min(100%, 380px); justify-self: center; }
    .aff-hero-image {
        display: block; width: 100%; aspect-ratio: 1 / 1; object-fit: cover;
        border: 8px solid rgba(255,255,255,0.92); border-radius: 30px;
        box-shadow: 0 28px 70px rgba(20,8,35,0.3);
    }
    .aff-hero-float {
        position: absolute; display: flex; align-items: center; gap: 0.6rem;
        padding: 0.8rem 1rem; border: 1px solid rgba(255,255,255,0.8); border-radius: 14px;
        color: var(--sombre); background: rgba(255,255,255,0.96);
        box-shadow: 0 12px 30px rgba(20,8,35,0.2); font-size: 0.82rem; font-weight: 700;
        animation: aff-float 4s ease-in-out infinite;
    }
    .aff-hero-float i { color: var(--violet); font-size: 1.1rem; }
    .aff-hero-float-link { top: 16%; left: -9%; }
    .aff-hero-float-sales { right: -7%; bottom: 17%; animation-delay: -2s; }
    .aff-hero-float small { display: block; color: var(--gris); font-size: 0.68rem; font-weight: 500; }
    @keyframes aff-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-9px); }
    }
    @media (max-width: 767.98px) {
        .aff-hero { padding: 3rem 0 2.5rem; }
        .aff-hero-layout { grid-template-columns: 1fr; gap: 1.5rem; text-align: center; }
        .aff-hero-copy { margin: 0 auto; }
        .aff-hero h1 { margin-left: auto; margin-right: auto; font-size: clamp(2.6rem, 12vw, 4.2rem); line-height: 1.05; }
        .aff-hero h1 span { display: block; white-space: normal; }
        .aff-hero .lead { font-size: 0.9rem; }
        .aff-hero .micro { font-size: 0.72rem; }
        .aff-hero .btn-lg { font-size: 0.95rem; padding: 0.5rem 1rem; }
        .aff-hero-visual { width: min(72%, 300px); }
        .aff-hero-image { border-width: 6px; border-radius: 24px; }
        .aff-hero-float { padding: 0.6rem 0.75rem; font-size: 0.72rem; }
        .aff-hero-float-link { left: -13%; }
        .aff-hero-float-sales { right: -12%; }
    }
    @media (max-width: 575.98px) {
        .aff-hero h1 { font-size: clamp(2.4rem, 14vw, 3.6rem); line-height: 1.04; }
    }
    @media (prefers-reduced-motion: reduce) {
        .aff-hero-float { animation: none; }
    }

    .aff-soft { background: #faf8fd; }
    .aff-section-title { font-weight: 800; color: var(--sombre); }
    .aff-sub { color: var(--gris); }

    .aff-card {
        background: #fff;
        border: 1px solid #ece7f3;
        border-radius: 14px;
        padding: 1.6rem 1.4rem;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .aff-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(84,38,128,0.1); }
    .aff-ico {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(84,38,128,0.08);
        color: var(--violet);
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }

    .aff-gain-card {
        position: relative;
        background: #fff;
        border: 1px solid #ece7f3;
        border-radius: 20px;
        padding: 2rem 1.6rem;
        height: 100%;
        text-align: center;
        transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.5s ease;
        opacity: 0;
        transform: translateY(18px);
    }
    .aff-gain-card.in-view { opacity: 1; transform: translateY(0); }
    .aff-gain-card.in-view:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(84,38,128,0.16); }
    .aff-gain-card.premium {
        background: #fff;
        border: 2px solid var(--violet);
        box-shadow: 0 14px 34px rgba(84,38,128,0.16);
    }
    .aff-gain-tag {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        background: rgba(84,38,128,0.08);
        color: var(--violet);
        margin-bottom: 1.1rem;
    }
    .aff-gain-pct { font-size: 3.4rem; font-weight: 800; line-height: 1; color: var(--violet); }
    .aff-gain-pct small { font-size: 1.6rem; margin-left: 0.1rem; }
    .aff-gain-sub { color: var(--gris); font-size: 0.85rem; margin: 0.5rem 0 1.25rem; }
    .aff-gain-amount {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--violet);
        border-top: 1px dashed #e5dcf0;
        padding-top: 1.25rem;
    }
    .aff-gain-amount-note { display: block; font-size: 0.75rem; font-weight: 500; color: var(--gris); margin-top: 0.3rem; }
    .aff-gain-badge {
        position: absolute;
        top: -14px; left: 50%;
        transform: translateX(-50%);
        background: var(--accent);
        color: var(--sombre);
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(254,213,20,0.4);
    }
    @media (prefers-reduced-motion: reduce) {
        .aff-gain-card { opacity: 1; transform: none; transition: none; }
    }

    .aff-step-num {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: var(--violet);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800;
        margin: 0 auto 0.8rem;
    }
    .aff-step-ico { font-size: 1.5rem; color: var(--violet); }

    .aff-final {
        background: linear-gradient(135deg, #542680 0%, #3d1a5c 100%);
        color: #fff;
        border-radius: 18px;
        padding: 3rem 1.5rem;
    }

    .aff-faq .accordion-button { font-weight: 600; }
    .aff-faq .accordion-body { color: var(--gris); }
    .aff-faq .accordion-button:not(.collapsed) { background: rgba(84,38,128,0.06); color: var(--violet); }

    .aff-partnership-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
        align-items: stretch;
        gap: 1.25rem;
    }
    .aff-partnership {
        display: flex; flex-direction: column; justify-content: center;
        background: #fff;
        border: 1px solid #ece7f3;
        border-radius: 20px;
        padding: clamp(1.5rem, 3vw, 2.5rem);
        box-shadow: 0 14px 36px rgba(84,38,128,0.07);
    }
    .aff-partnership-kicker {
        display: inline-flex; align-items: center; gap: 0.5rem;
        color: var(--violet); background: rgba(84,38,128,0.08);
        border-radius: 999px; padding: 0.45rem 0.8rem;
        font-size: 0.72rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;
    }
    .aff-partnership-kicker i { font-size: 0.95rem; }
    .aff-partnership-title { color: var(--sombre); font-size: clamp(1.4rem, 3vw, 1.8rem); font-weight: 800; line-height: 1.2; margin: 1.1rem 0 0.75rem; }
    .aff-partnership-desc { color: var(--gris); line-height: 1.7; margin: 0; }
    .aff-partnership-points { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; margin-top: 1.75rem; }
    .aff-partnership-point {
        display: flex; align-items: flex-start; gap: 0.65rem;
        background: #faf8fd; border: 1px solid #f0ecf6; border-radius: 12px;
        padding: 0.9rem; color: var(--sombre); font-size: 0.84rem; font-weight: 600; line-height: 1.45;
    }
    .aff-partnership-point i { color: var(--violet); font-size: 1.05rem; flex: 0 0 auto; }
    .aff-gain-card { display: flex; flex-direction: column; justify-content: center; }
    .aff-gain-example-note { color: var(--gris); font-size: 0.8rem; line-height: 1.5; margin: 1.1rem 0 0; }
    @media (max-width: 767.98px) {
        .aff-partnership-layout { grid-template-columns: 1fr; }
        .aff-partnership-points { grid-template-columns: 1fr; }
    }

    .aff-scroll { max-height: 520px; overflow-y: auto; padding-right: 0.5rem; }
    .aff-scroll::-webkit-scrollbar { width: 8px; }
    .aff-scroll::-webkit-scrollbar-thumb { background: #d9cdee; border-radius: 8px; }
</style>
@endsection

@section('content')

{{-- ========== 1. HERO ========== --}}
<section class="aff-hero">
    <div class="container">
        <div class="aff-hero-layout">
            <div class="aff-hero-copy">
                <span class="badge rounded-pill mb-2 mb-md-3" style="background: rgba(254,213,20,0.15); color: var(--accent); font-weight: 700;">
                    <i class="bi bi-cash-stack me-1"></i> Programme d'affiliation
                </span>
                <h1 class="mb-2 mb-md-3"><span>Gagnez de l’argent</span><span>avec PaxEvent</span></h1>
                <p class="lead mb-3 mb-md-4">
                    Co-parrainez des événements sur PaxEvent, et touchez une commission sur chaque vente de tickets.
                </p>
                <a href="{{ route('affiliate.register') }}" class="btn btn-violet btn-lg px-3 px-md-4 fw-bold" style="background: var(--accent); border-color: var(--accent); color: var(--sombre);">
                    Rejoindre le programme <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <p class="micro mt-2 mt-md-3 mb-0">Gratuit • Aucun coût d'entrée • Tableau de bord dédié</p>
            </div>
            <div class="aff-hero-visual" aria-hidden="true">
                <img class="aff-hero-image" src="{{ asset('images/image_heros.jpeg') }}" alt="" fetchpriority="high">
                <div class="aff-hero-float aff-hero-float-link">
                    <i class="bi bi-link-45deg"></i>
                    <span>Votre lien affilié<small>À partager avec votre audience</small></span>
                </div>
                <div class="aff-hero-float aff-hero-float-sales">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Ventes suivies<small>Depuis votre tableau de bord</small></span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 2. LE CO-PARRAINAGE ET SON EXEMPLE DE GAINS ========== --}}
<section class="py-5 aff-soft" id="affGains">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Le Co-parrainage sur PaxEvent</h2>
            <p class="aff-sub mb-0 mx-auto" style="max-width: 720px;">
                Permet aux prescripteurs (agences, influenceurs et médias) de monétiser leur audience. Promouvez les événements à travers un lien de co-parrainage traçable et percevez une commission sur les ventes.
            </p>
        </div>
        <div class="aff-partnership-layout">
            <article class="aff-partnership">
                <span class="aff-partnership-kicker"><i class="bi bi-share"></i> Programme d'affiliation</span>
                <h3 class="aff-partnership-title">Co-parrainage d'un événement</h3>
                <p class="aff-partnership-desc">
                    <strong>x% sur chaque billet vendu</strong> – taux fixé par l'organisateur.
                </p>
                <p class="aff-partnership-desc mt-2">
                    Les organisateurs ouvrent leurs événements au co-parrainage et fixent eux-mêmes la commission offerte. À la charge de l'organisateur, jamais prélevée sur la commission PaxEvent. Plusieurs co-parrains peuvent promouvoir le même événement.
                </p>
                <div class="aff-partnership-points">
                    <div class="aff-partnership-point">
                        <i class="bi bi-cash-stack" aria-hidden="true"></i>
                        <span>Rémunéré par l'organisateur</span>
                    </div>
                </div>
            </article>
            <aside class="aff-gain-card premium" aria-label="Exemple indicatif de commission">
                <span class="aff-gain-badge">Exemple indicatif</span>
                <h3 class="h5 fw-bold mb-3">Combien pouvez-vous gagner&nbsp;?</h3>
                <span class="aff-gain-tag">Votre commission par billet</span>
                <div class="aff-gain-pct"><span data-count="3">3</span><small>%</small></div>
                <p class="aff-gain-sub">Exemple de taux proposé par un organisateur</p>
                <div class="aff-gain-amount">
                    <span data-count="300000" data-suffix=" FCFA">300 000 FCFA</span>
                    <span class="aff-gain-amount-note">1 000 billets × 10 000 FCFA × 3 %</span>
                </div>
                <p class="aff-gain-example-note">Simulation non garantie : le montant réel dépend des ventes éligibles attribuées à votre lien et du taux applicable à l'événement.</p>
            </aside>
        </div>
    </div>
</section>

{{-- ========== 3. POURQUOI DEVENIR AFFILIÉ ========== --}}
<section class="py-5 aff-soft">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Pourquoi devenir Co-parrain ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-cash-coin"></i></div>
                    <h6 class="fw-bold mb-2">Commissions Attractives</h6>
                    <p class="text-muted small mb-0">Touchez un pourcentage compétitif sur les événements.</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-infinity"></i></div>
                    <h6 class="fw-bold mb-2">Sans plafond de gains</h6>
                    <p class="text-muted small mb-0">Plus vous partagez, plus vous gagnez. Il n'y a pas de limite au nombre d'événements que vous pouvez promouvoir.</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-speedometer2"></i></div>
                    <h6 class="fw-bold mb-2">Dashboard en temps réel</h6>
                    <p class="text-muted small mb-0">Suivez vos liens, vos clics, le volume de billets vendus et vos commissions disponibles depuis un tableau de bord clair.</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-wallet2"></i></div>
                    <h6 class="fw-bold mb-2">Paiements garantis</h6>
                    <p class="text-muted small mb-0">Vos commissions se libèrent à la fin de l'événement concerné. Demandez le retrait de vos gains en quelques clics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 4. COMMENT ÇA MARCHE ========== --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Comment fonctionne le programme ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-6 step-card">
                <div class="aff-step-num">1</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-person-plus"></i></div>
                <h6 class="fw-bold">Inscrivez-vous gratuitement</h6>
                <p class="text-muted small mb-0">Créez votre compte Affilié en quelques clics et accédez à votre tableau de bord après validation.</p>
            </div>
            <div class="col-12 col-md-6 step-card">
                <div class="aff-step-num">2</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-share"></i></div>
                <h6 class="fw-bold">Co-parrainez les événements</h6>
                <p class="text-muted small mb-0">Depuis votre tableau de bord, générez votre lien dédié dès qu'un organisateur active le co-parrainage, partagez-le, puis touchez une commission sur chaque billet vendu via votre lien.</p>
            </div>
        </div>
    </div>
</section>

{{-- ========== 5. À QUI S'ADRESSE ========== --}}
<section class="py-5 aff-soft">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">À qui s'adresse le programme ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-briefcase"></i></div>
                    <h6 class="fw-bold mb-2">Agences &amp; Event Planners</h6>
                    <p class="text-muted small mb-0">Promouvoir les événements de vos clients ouverts au co-parrainage et gagnez la commission fixée par l'organisateur.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-people"></i></div>
                    <h6 class="fw-bold mb-2">Créateurs, influenceurs &amp; média</h6>
                    <p class="text-muted small mb-0">Mettez votre audience à profit en co-parrainant les événements que vous recommandez</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-shop"></i></div>
                    <h6 class="fw-bold mb-2">Revendeurs &amp; billetteries locales</h6>
                    <p class="text-muted small mb-0">Repérez les événements ouverts au co-parrainage et touchez une commission sur chaque billet vendu via votre lien.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 6. FAQ + CTA FINAL ========== --}}
<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-6">
                <h2 class="aff-section-title h4 mb-4">Questions fréquentes</h2>
                <div class="accordion aff-faq" id="affFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq1">
                                L'inscription au programme d'affiliation est-elle payante ?
                            </button>
                        </h2>
                        <div id="affFaq1" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Non, l'inscription est 100 % gratuite et sans aucun engagement.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2b">
                                Comment fonctionne le co-parrainage d'événement ?
                            </button>
                        </h2>
                        <div id="affFaq2b" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">
                                Une fois votre lien généré et partagé, les ventes éligibles qui lui sont attribuées donnent droit à la commission prévue. Plusieurs affiliés peuvent promouvoir le même événement.
                                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:0.6rem 0.75rem;font-size:0.82rem;color:#856404;margin-bottom:0.75rem;">
                                    <i class="bi bi-exclamation-triangle me-1"></i> <strong>À noter :</strong> le taux de commission est défini par l'organisateur et peut varier selon l'événement. Consultez-le avant de partager votre lien.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2d">
                                Qui peut rejoindre le programme d'affiliation ?
                            </button>
                        </h2>
                        <div id="affFaq2d" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Le programme est ouvert aux personnes qui ne détiennent pas de compte organisateur sur PaxEvent. Les organisateurs peuvent, de leur côté, ouvrir leurs événements au co-parrainage et proposer une commission aux affiliés.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq3">
                                Quels billets sont exclus du programme ?
                            </button>
                        </h2>
                        <div id="affFaq3" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Les événements gratuits (0 FCFA), les billets physiques (lots à codes QR) et toute vente non intégralement payée sont exclus : aucune commission d'affiliation ne s'y rattache.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq4">
                                Quand et comment suis-je payé ?
                            </button>
                        </h2>
                        <div id="affFaq4" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Vos commissions sont comptabilisées en temps réel et se libèrent à la fin de l'événement concerné (la fenêtre couvre d'éventuels remboursements). Vous pouvez alors demander le retrait de vos gains directement depuis votre tableau de bord.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="aff-final h-100">
                    <h2 class="h3 fw-bold mb-2">Prêt à monétiser votre réseau ?</h2>
                    <p class="mb-4" style="color: rgba(255,255,255,0.8);">Rejoignez nos partenaires qui génèrent déjà des revenus récurrents avec PaxEvent.</p>
                    <a href="{{ route('affiliate.register') }}" class="btn btn-lg fw-bold px-4" style="background: var(--accent); color: var(--sombre);">
                        Devenez co-parrain <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 8. CONDITIONS GÉNÉRALES (repliables) ========== --}}
<section class="pb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="accordion aff-cg" id="affCgAccordion">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affCg" aria-expanded="false">
                                <i class="bi bi-file-earmark-text me-2"></i>Conditions Générales du programme d'affiliation
                            </button>
                        </h2>
                        <div id="affCg" class="accordion-collapse collapse" data-bs-parent="#affCgAccordion">
                            <div class="accordion-body">
                                <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : {{ $derniereMiseAJour ?? 'Octobre 2026' }}</p>

                                <div class="aff-scroll" style="font-size: 0.9rem; line-height: 1.7;">
                                    <p>Les présentes Conditions Générales régissent le programme d'affiliation (ci-après le « Programme ») mis en place par Noctam Communication (ci-après « PaxEvent ») sur sa plateforme https://paxevent.com.</p>
                                    <p>Le Programme permet à des personnes physiques ou morales (ci-après les « Affiliés » ou « Apporteurs d'Affaires ») de percevoir une commission sur les ventes de billets réalisées via leurs liens de co-parrainage, selon le pourcentage fixé par l'Organisateur. Aucune commission n'est due en l'absence de vente tracée et intégralement payée.</p>

                                    <h6 class="fw-bold mt-4 mb-2">1. Définitions</h6>
                                    <p>                                    <strong>Affilié :</strong> toute personne non-organisateur inscrite au Programme au moyen d'un compte affilié dédié.<br>
                                    <strong>Organisateur :</strong> utilisateur publiant des événements sur PaxEvent.<br>
                                    <strong>Co-parrain :</strong> l'Affilié qui promeut un événement ouvert au co-parrainage.<br>
                                    <strong>Vente tracée :</strong> achat de billet réalisé au moyen du lien ou du code d'un Affilié et intégralement payé.<br>
                                    <strong>Commission PaxEvent :</strong> la commission perçue par PaxEvent sur une vente (de 5 % à 10 % selon l'Organisateur ou l'événement).</p>

                                    <h6 class="fw-bold mt-4 mb-2">2. Inscription et éligibilité</h6>
                                    <p><strong>Adhésion :</strong> L'inscription au Programme est gratuite et s'effectue via un espace dédié de la plateforme. PaxEvent se réserve le droit d'accepter, de refuser ou de suspendre toute candidature sans obligation de justification.</p>
                                    <p><strong>Exclusion des Organisateurs :</strong> Le Programme est réservé aux personnes qui ne détiennent pas de compte Organisateur sur PaxEvent. Un Organisateur ne peut ni ouvrir ni détenir un compte Affilié. Il peut en revanche ouvrir ses événements au co-parrainage afin de rémunérer les Affiliés.</p>
                                    <p><strong>Capacité légale :</strong> L'Affilié certifie être majeur et disposer de la capacité juridique ou des statuts professionnels nécessaires au Bénin pour percevoir des revenus d'apport d'affaires.</p>

                                    <h6 class="fw-bold mt-4 mb-2">3. Co-parrainage d'un événement</h6>
                                    <p><strong>Ouverture :</strong> Tout Organisateur peut ouvrir un de ses événements au co-parrainage et fixer librement la commission offerte au Co-parrain.</p>
                                    <p><strong>Financement :</strong> La commission de co-parrainage est <strong>à la charge de l'Organisateur</strong>. Elle n'est jamais prélevée sur la Commission PaxEvent.</p>
                                    <p><strong>Plafond :</strong> La commission de co-parrainage ne peut excéder la marge nette perçue par l'Organisateur sur le billet concerné (après déduction de la Commission PaxEvent). En conséquence, elle ne réduit jamais la Commission PaxEvent.</p>
                                    <p><strong>Pluralité :</strong> Plusieurs Co-parrains peuvent promouvoir un même événement. Une même vente ne génère qu'une seule commission de co-parrainage, au profit d'un seul Co-parrain.</p>
                                    <p><strong>Commission :</strong> La commission de co-parrainage est gérée par l'Organisateur, à sa charge, selon le pourcentage qu'il fixe pour l'événement.</p>

                                    <h6 class="fw-bold mt-4 mb-2">4. Liens, codes et traçabilité des ventes</h6>
                                    <p><strong>Génération :</strong> Après validation de son compte, l'Affilié peut générer un lien de traçage dédié par événement ouvert au co-parrainage.</p>
                                    <p><strong>Vente tracée :</strong> Une commission de co-parrainage n'est due que si le lien ou le code de l'Affilié a été utilisé lors de l'achat et si le paiement a été intégralement confirmé. Sans utilisation du lien ou du code, aucune commission de co-parrainage n'est attribuée.</p>
                                    <p><strong>Règle d'attribution :</strong> Pour le co-parrainage, l'attribution suit la règle du « dernier clic valide » : la commission revient au Co-parrain dont le lien ou le code a été utilisé au moment de l'achat.</p>
                                    <p><strong>Fenêtre de suivi :</strong> Le lien ouvre une session traçable de trente (30) jours à compter de sa première activation. Au-delà, l'attribution expire.</p>
                                    <p><strong>Exclusions :</strong> Sont hors du champ du Programme les événements gratuits (0 FCFA), les billets physiques (lots à codes QR) et toute vente n'ayant pas donné lieu à un paiement intégral et définitif.</p>

                                    <h6 class="fw-bold mt-4 mb-2">5. Calcul et taux de commission</h6>
                                    <p><strong>Base de calcul :</strong> La commission est calculée exclusivement sur la valeur faciale nette du billet vendu (hors frais de service de la plateforme et taxes).</p>
                                    <p><strong>Co-parrainage :</strong> Pourcentage fixé librement par l'Organisateur, dans la limite de sa marge nette. Le taux applicable à chaque événement est affiché à l'Affilié sur son tableau de bord <strong>avant le début de toute promotion</strong>.</p>
                                    <p><em>Exemple : pour un billet à 10 000 FCFA vendu avec une Commission PaxEvent de 10 % (1 000 FCFA) et une commission de co-parrainage de 3 % (300 FCFA) : le Co-parrain perçoit 300 FCFA ; PaxEvent conserve 1 000 FCFA ; l'Organisateur reçoit le solde.</em></p>
                                    <p><strong>Plafond global :</strong> Sur une même vente, la Commission PaxEvent ne peut jamais être réduite par une commission d'affiliation.</p>
                                    <p><strong>Événement gratuit :</strong> Un événement gratuit (0 FCFA) ne génère aucune commission ni droit à rémunération.</p>
                                    <p><strong>Changement de taux :</strong> Tout nouveau taux ne s'applique qu'aux ventes tracées postérieures à sa publication. Les ventes déjà tracées conservent le taux affiché au moment de leur attribution.</p>

                                    <h6 class="fw-bold mt-4 mb-2">6. Maturation, paiement et reversement</h6>
                                    <p><strong>Maturation :</strong> Une commission est comptabilisée en attente puis devient disponible <strong>à la fin de l'événement concerné</strong>. Ce délai couvre la fenêtre d'éventuels remboursements. Tant qu'une commission n'est pas disponible, elle ne peut pas être retirée.</p>
                                    <p><strong>Seuil de paiement :</strong> Les commissions disponibles sont payables dès que le solde de l'Affilié atteint un seuil minimum de 5 000 XOF.</p>
                                    <p><strong>Canaux de paiement :</strong> Les reversements s'effectuent directement sur le numéro Mobile Money (MTN, Moov, Wave) ou le compte bancaire renseigné par l'Affilié.</p>
                                    <p><strong>Frais techniques de reversement :</strong> Conformément à notre infrastructure FedaPay, les frais de transfert de masse (PAYOUT) s'appliquent selon le barème officiel en vigueur de la plateforme.</p>
                                    <p><strong>Vérification d'identité :</strong> PaxEvent peut demander un justificatif d'identité avant tout premier reversement.</p>

                                    <h6 class="fw-bold mt-4 mb-2">7. Annulation et remboursement</h6>
                                    <p><strong>Commission non encore disponible :</strong> Si un billet fait l'objet d'un remboursement (annulation de l'événement, transaction frauduleuse, rejet Mobile Money ou chargeback), la commission correspondante est automatiquement annulée.</p>
                                    <p><strong>Commission déjà versée :</strong> Si la commission a déjà été créditée ou versée, elle est reprise (clawback) sur le solde de l'Affilié. Le solde peut devenir négatif et être compensé par les commissions futures jusqu'à apurement. Chaque annulation ou reprise est visible dans l'historique du compte Affilié.</p>
                                    <p><strong>Responsabilité de l'Organisateur :</strong> En cas d'annulation d'un événement, la commission de co-parrainage non encore versée n'est pas due ; l'Organisateur n'est pas débité d'une commission qui n'était pas encore due.</p>

                                    <h6 class="fw-bold mt-4 mb-2">8. Règles de conduite et pratiques interdites</h6>
                                    <p>L'Affilié s'engage à préserver l'image de marque de PaxEvent et de l'Organisateur. Sont strictement interdits :</p>
                                    <ul>
                                        <li><strong>Le Spamming :</strong> l'envoi massif d'e-mails, de SMS ou de messages WhatsApp non sollicités contenant les liens d'affiliation.</li>
                                        <li><strong>Le « Self-Affiliation » :</strong> le fait d'utiliser son propre lien d'affiliation pour acheter ses propres billets dans le seul but de toucher une commission.</li>
                                        <li><strong>Publicité mensongère :</strong> divulguer de fausses informations sur l'événement (tarifs erronés, faux invités, fausses dates) pour forcer la vente.</li>
                                        <li><strong>Achat de mots-clés :</strong> l'achat de mots-clés sur Google Ads ou d'autres régies publicitaires contenant la marque « PaxEvent » ou le nom exact de l'événement sans accord écrit.</li>
                                        <li><strong>Co-parrainage fictif :</strong> toute collusion entre l'Organisateur et un Co-parrain visant à simuler des ventes ou à détourner des commissions.</li>
                                    </ul>

                                    <h6 class="fw-bold mt-4 mb-2">9. Responsabilité et indépendance</h6>
                                    <p>L'Affilié exerce son activité en totale indépendance. Il n'existe aucun lien de subordination, de contrat de travail ou de mandat de représentation exclusif entre PaxEvent et l'Affilié. L'Affilié est seul responsable de la déclaration fiscale de ses gains auprès des autorités compétentes au Bénin.</p>

                                    <h6 class="fw-bold mt-4 mb-2">10. Modification et résiliation</h6>
                                    <p><strong>Résiliation par l'Affilié :</strong> L'Affilié peut cesser sa participation au Programme à tout moment en cessant d'utiliser ses liens.</p>
                                    <p><strong>Bannissement pour fraude :</strong> En cas de non-respect constaté de l'Article 9, PaxEvent annulera immédiatement le compte de l'Affilié sans préavis. Toutes les commissions en attente de paiement seront définitivement confisquées au profit de PaxEvent ou de l'Organisateur pour réparer le préjudice causé.</p>

                                    <h6 class="fw-bold mt-4 mb-2">11. Loi applicable</h6>
                                    <p>Les présentes conditions d'affiliation sont régies par le droit béninois (Code du numérique). Tout litige relatif à leur exécution ou leur interprétation relève de la compétence des tribunaux de Cotonou.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('accueil') }}" class="btn btn-outline-violet" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left me-1"></i> Retour à accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
(function () {
    var section = document.getElementById('affGains');
    if (!section) { return; }

    var cards = section.querySelectorAll('.aff-gain-card');
    var counters = section.querySelectorAll('[data-count]');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var done = false;

    function format(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function countUp(el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        if (reduce) { el.textContent = format(target) + suffix; return; }

        var duration = 1400, start = null;
        el.textContent = '0' + suffix;
        function step(ts) {
            if (start === null) { start = ts; }
            var p = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = format(target * eased) + suffix;
            if (p < 1) { requestAnimationFrame(step); }
            else { el.textContent = format(target) + suffix; }
        }
        requestAnimationFrame(step);
    }

    function run() {
        if (done) { return; }
        done = true;
        cards.forEach(function (card, i) {
            setTimeout(function () { card.classList.add('in-view'); }, i * 130);
        });
        counters.forEach(function (el) { countUp(el); });
    }

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { run(); io.disconnect(); }
            });
        }, { threshold: 0.3 });
        io.observe(section);
    } else {
        run();
    }
})();
</script>
@endsection
