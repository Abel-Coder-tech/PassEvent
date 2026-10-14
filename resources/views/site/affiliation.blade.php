@extends('layouts.public')

@section('title', "Programme d'Affiliation PaxEvent — Parrainez & co-parrainez")
@section('description', "Deux façons de gagner : parrainez un organisateur (rémunéré par PaxEvent) ou co-parrainez ses événements avec une commission fixée par l'organisateur. Inscription gratuite, tableau de bord en temps réel.")

@section('styles')
<noscript><style>.aff-gain-card { opacity: 1 !important; transform: none !important; }</style></noscript>
<style>
    .aff-hero {
        background: linear-gradient(135deg, #542680 0%, #3d1a5c 100%);
        color: #fff;
        padding: 4rem 0 3.5rem;
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
    .aff-hero h1 { font-weight: 800; line-height: 1.2; }
    .aff-hero .lead { color: rgba(255,255,255,0.88); }
    .aff-hero .micro { color: rgba(255,255,255,0.65); font-size: 0.85rem; }

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
    .aff-gain-total {
        margin-top: 2.25rem;
        text-align: center;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--sombre);
        background: #f4effb;
        border: 1px solid #e5dcf0;
        border-radius: 16px;
        padding: 1.5rem 1.25rem;
    }
    .aff-gain-total .aff-count { color: var(--violet); font-weight: 800; }
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

    .aff-mech-row { position: relative; }

    .aff-mech {
        position: relative; background: #fff;
        border: 1px solid #ece7f3; border-radius: 16px;
        border-left: 4px solid var(--violet);
        padding: 1.6rem 1.5rem; height: 100%;
        display: flex; flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .aff-mech-b { border-left-color: #d9a400; }
    .aff-mech:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(84,38,128,0.08); }
    .aff-mech-head { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; }
    .aff-mech-letter {
        flex: 0 0 auto; width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; font-weight: 800;
        background: rgba(84,38,128,0.08); color: var(--violet);
    }
    .aff-mech-b .aff-mech-letter { background: rgba(217,164,0,0.12); color: #9a7600; }
    .aff-mech-kicker { display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gris); }
    .aff-mech-title { font-size: 1.08rem; font-weight: 800; margin: 0.1rem 0 0; line-height: 1.25; color: var(--sombre); }
    .aff-mech-body { display: flex; flex-direction: column; flex: 1; }
    .aff-mech-metric { display: flex; align-items: center; gap: 1rem; padding-bottom: 1.15rem; margin-bottom: 1.15rem; border-bottom: 1px solid #f0ecf6; }
    .aff-mech-metric-value { font-size: 2.8rem; font-weight: 800; line-height: 1; color: var(--violet); }
    .aff-mech-metric-value small { font-size: 1.4rem; margin-left: 0.05rem; }
    .aff-mech-b .aff-mech-metric-value { color: #b98900; }
    .aff-mech-metric-label { color: var(--sombre); font-size: 0.9rem; font-weight: 700; line-height: 1.25; }
    .aff-mech-metric-label em { display: block; margin-top: 0.2rem; color: var(--gris); font-size: 0.78rem; font-weight: 500; font-style: normal; }
    .aff-mech-desc { color: var(--gris); font-size: 0.9rem; margin-bottom: 1rem; }
    .aff-mech-list { list-style: none; padding: 0; margin: 0 0 1.35rem; }
    .aff-mech-list li { display: flex; gap: 0.55rem; color: var(--gris); font-size: 0.88rem; margin-bottom: 0.6rem; }
    .aff-mech-list li i { color: var(--violet); flex: 0 0 auto; margin-top: 0.12rem; }
    .aff-mech-b .aff-mech-list li i { color: #b98900; }
    .aff-mech-payer {
        margin-top: auto; align-self: flex-start;
        display: inline-flex; align-items: center; gap: 0.45rem;
        font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
        color: var(--gris);
    }
    .aff-mech-payer i { color: var(--violet); }
    .aff-mech-b .aff-mech-payer i { color: #b98900; }

    .aff-scroll { max-height: 520px; overflow-y: auto; padding-right: 0.5rem; }
    .aff-scroll::-webkit-scrollbar { width: 8px; }
    .aff-scroll::-webkit-scrollbar-thumb { background: #d9cdee; border-radius: 8px; }
</style>
@endsection

@section('content')

{{-- ========== 1. HERO ========== --}}
<section class="aff-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-12 col-lg-9">
                <span class="badge rounded-pill mb-3" style="background: rgba(254,213,20,0.15); color: var(--accent); font-weight: 700;">
                    <i class="bi bi-megaphone me-1"></i> Programme d'Affiliation
                </span>
                <h1 class="display-5 mb-3">Deux façons de gagner avec PaxEvent.</h1>
                <p class="lead mb-4">
                    <strong>Parrainez un organisateur</strong> ou <strong>co-parrainez des événements</strong> sur PaxEvent, et touchez une commission sur chaque vente.
                </p>
                <button type="button" class="btn btn-violet btn-lg px-4 fw-bold" style="background: var(--accent); border-color: var(--accent); color: var(--sombre);">
                    Rejoindre le programme gratuitement <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <p class="micro mt-3 mb-0">Inscription gratuite &bull; Aucun coût d'entrée &bull; Tableau de bord en temps réel</p>
            </div>
        </div>
    </div>
</section>

{{-- ========== 2. LES DEUX MÉCANISMES ========== --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Deux mécanismes, cumulables</h2>
            <p class="aff-sub mb-0 mx-auto" style="max-width: 640px;">
                Vous pouvez activer les deux en même temps : les commissions s'additionnent sur vos ventes tracées.
            </p>
        </div>
        <div class="row g-4 align-items-stretch aff-mech-row">
            <div class="col-12 col-lg-6">
                <article class="aff-mech aff-mech-a">
                    <header class="aff-mech-head">
                        <span class="aff-mech-letter">A</span>
                        <div>
                            <span class="aff-mech-kicker"><i class="bi bi-person-plus me-1"></i>Mécanisme A</span>
                            <h3 class="aff-mech-title">Parrainage direct d'un organisateur</h3>
                        </div>
                    </header>
                    <div class="aff-mech-body">
                        <div class="aff-mech-metric">
                            <span class="aff-mech-metric-value">20<small>%</small></span>
                            <span class="aff-mech-metric-label">de la commission PaxEvent
                                <em>≈ 1 à 2 % du prix du billet</em>
                            </span>
                        </div>
                        <p class="aff-mech-desc">Vous amenez un organisateur sur PaxEvent grâce à votre lien. Vous devenez son <strong>parrain direct</strong>.</p>
                        <ul class="aff-mech-list">
                            <li><i class="bi bi-check2-circle"></i><span>Sur <strong>toutes les ventes payées de son premier événement</strong>.</span></li>
                            <li><i class="bi bi-check2-circle"></i><span>Un organisateur ne peut avoir qu'<strong>un seul parrain direct</strong>.</span></li>
                        </ul>
                        <span class="aff-mech-payer"><i class="bi bi-bank"></i> Rémunéré par PaxEvent</span>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-6">
                <article class="aff-mech aff-mech-b">
                    <header class="aff-mech-head">
                        <span class="aff-mech-letter">B</span>
                        <div>
                            <span class="aff-mech-kicker"><i class="bi bi-share me-1"></i>Mécanisme B</span>
                            <h3 class="aff-mech-title">Co-parrainage d'un événement</h3>
                        </div>
                    </header>
                    <div class="aff-mech-body">
                        <div class="aff-mech-metric">
                            <span class="aff-mech-metric-value">x<small>%</small></span>
                            <span class="aff-mech-metric-label">du prix de chaque billet
                                <em>taux fixé par l'organisateur</em>
                            </span>
                        </div>
                        <p class="aff-mech-desc">Les organisateurs peuvent <strong>ouvrir leurs événements au co-parrainage</strong> et fixer eux-mêmes la commission offerte.</p>
                        <ul class="aff-mech-list">
                            <li><i class="bi bi-check2-circle"></i><span>À la <strong>charge de l'organisateur</strong>, jamais prélevée sur la commission PaxEvent.</span></li>
                            <li><i class="bi bi-check2-circle"></i><span><strong>Plusieurs co-parrains</strong> peuvent promouvoir le même événement.</span></li>
                        </ul>
                        <span class="aff-mech-payer"><i class="bi bi-person-badge"></i> Rémunéré par l'organisateur</span>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

{{-- ========== 3. POURQUOI DEVENIR AFFILIÉ ========== --}}
<section class="py-5 aff-soft">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Pourquoi devenir Affilié PaxEvent ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-cash-coin"></i></div>
                    <h6 class="fw-bold mb-2">Deux sources de revenus</h6>
                    <p class="text-muted small mb-0">Cumulez la rémunération du parrainage direct et les commissions de co-parrainage d'événements. Sans plafond de gains.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-sliders"></i></div>
                    <h6 class="fw-bold mb-2">Des commissions fixées par l'organisateur</h6>
                    <p class="text-muted small mb-0">Sur le co-parrainage, l'organisateur choisit la commission qu'il vous verse. Le taux est affiché avant toute promotion.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-speedometer2"></i></div>
                    <h6 class="fw-bold mb-2">Dashboard en temps réel</h6>
                    <p class="text-muted small mb-0">Suivez vos liens, vos clics, le volume de billets vendus et vos commissions disponibles depuis un tableau de bord clair.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-wallet2"></i></div>
                    <h6 class="fw-bold mb-2">Paiements garantis</h6>
                    <p class="text-muted small mb-0">Vos commissions se libèrent à la fin de l'événement concerné. Demandez le retrait de vos gains en quelques clics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 3. COMBIEN POUVEZ-VOUS GAGNER ========== --}}
<section class="py-5 aff-soft" id="affGains">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="aff-section-title h3">Combien pouvez-vous gagner ?</h2>
            <p class="aff-sub mb-0 mx-auto" style="max-width: 640px;">
                Le parrainage direct suit la commission de PaxEvent ; le co-parrainage suit la commission fixée par l'organisateur.
            </p>
        </div>
        <div class="row justify-content-center g-4">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="aff-gain-card">
                    <span class="aff-gain-tag">Parrainage direct</span>
                    <div class="aff-gain-pct"><span data-count="20">20</span><small>%</small></div>
                    <p class="aff-gain-sub">de la commission PaxEvent (≈ 1 à 2 % du prix de chaque billet du 1er événement)</p>
                    <div class="aff-gain-amount">
                        <span data-count="200000" data-suffix=" FCFA">200 000 FCFA</span>
                        <span class="aff-gain-amount-note">pour 1 000 billets à 10 000 FCFA (commission PaxEvent 10 %)</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-5">
                <div class="aff-gain-card premium">
                    <span class="aff-gain-badge">Fixée par l'organisateur</span>
                    <span class="aff-gain-tag">Co-parrainage d'événement</span>
                    <div class="aff-gain-pct"><span data-count="3">3</span><small>%</small></div>
                    <p class="aff-gain-sub">du prix de chaque billet vendu via votre lien (exemple : commission fixée à 3 % par l'organisateur)</p>
                    <div class="aff-gain-amount">
                        <span data-count="300000" data-suffix=" FCFA">300 000 FCFA</span>
                        <span class="aff-gain-amount-note">pour 1 000 billets à 10 000 FCFA</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="aff-gain-total">
            <i class="bi bi-lightning-charge-fill me-1"></i>
            Cumulez parrainage direct et co-parrainage sur les événements de vos organisateurs et générez jusqu'à
            <span class="aff-count" data-count="1000000" data-suffix=" FCFA">1 000 000 FCFA</span> de revenus complémentaires !
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
            <div class="col-12 col-md-4 step-card">
                <div class="aff-step-num">1</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-person-plus"></i></div>
                <h6 class="fw-bold">Créez votre compte affilié</h6>
                <p class="text-muted small mb-0">L'inscription est gratuite et sans engagement. Votre code de parrainage et votre tableau de bord sont générés automatiquement. Vous êtes organisateur ? Activez votre profil affilié en un clic.</p>
            </div>
            <div class="col-12 col-md-4 step-card">
                <div class="aff-step-num">2</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-person-badge"></i></div>
                <h6 class="fw-bold">Parrainez un organisateur</h6>
                <p class="text-muted small mb-0">Partagez votre lien avec des organisateurs, agences, artistes ou promoteurs. Dès qu'un organisateur s'inscrit via votre lien, vous devenez son parrain direct : vous touchez 20 % de la commission PaxEvent sur toutes les ventes de son premier événement.</p>
            </div>
            <div class="col-12 col-md-4 step-card">
                <div class="aff-step-num">3</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-share"></i></div>
                <h6 class="fw-bold">Co-parrainez les événements ouverts</h6>
                <p class="text-muted small mb-0">Quand un organisateur ouvre un événement au co-parrainage, il fixe la commission offerte. Générez votre lien dédié depuis votre dashboard, partagez-le et touchez la commission sur chaque billet vendu via votre lien.</p>
            </div>
        </div>
    </div>
</section>

{{-- ========== 5. À QUI S'ADRESSE ========== --}}
<section class="py-5 aff-soft">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Un programme pensé pour les acteurs de l'événementiel</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-briefcase"></i></div>
                    <h6 class="fw-bold mb-2">Agences &amp; Event Planners</h6>
                    <p class="text-muted small mb-0">Recommandez la meilleure billetterie à vos clients et créez une nouvelle marge financière sur chacun de vos projets.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-people"></i></div>
                    <h6 class="fw-bold mb-2">Créateurs, artistes &amp; influenceurs</h6>
                    <p class="text-muted small mb-0">Mettez votre audience à profit en co-parrainant les événements que vous recommandez, avec la commission fixée par l'organisateur.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-shop"></i></div>
                    <h6 class="fw-bold mb-2">Organisateurs</h6>
                    <p class="text-muted small mb-0">Vous organisez déjà ? Devenez affilié, parrainez d'autres organisateurs et co-parrainez des événements pour générer des revenus complémentaires.</p>
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2">
                                Comment fonctionne le parrainage direct ?
                            </button>
                        </h2>
                        <div id="affFaq2" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Vous partagez votre lien de parrainage. Dès qu'un organisateur s'inscrit via ce lien, vous devenez son parrain direct et vous percevez 20 % de la commission PaxEvent sur toutes les ventes payées de son premier événement. Au-delà de ce premier événement, il n'y a plus de commission de parrainage direct.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2b">
                                Comment fonctionne le co-parrainage d'événement ?
                            </button>
                        </h2>
                        <div id="affFaq2b" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Les organisateurs peuvent ouvrir un événement au co-parrainage en fixant eux-mêmes la commission offerte (un pourcentage du prix du billet). Depuis votre dashboard, vous générez un lien dédié par événement ouvert et le partagez. Vous touchez la commission sur chaque billet vendu via votre lien. Plusieurs co-parrains peuvent promouvoir le même événement.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2c">
                                Les deux mécanismes peuvent-ils se cumuler ?
                            </button>
                        </h2>
                        <div id="affFaq2c" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Oui. Sur le premier événement de l'organisateur que vous avez parrainé, un billet vendu via votre lien de co-parrainage peut vous rapporter à la fois la commission de parrainage direct (versée par PaxEvent) et la commission de co-parrainage (versée par l'organisateur).</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2d">
                                Un organisateur peut-il être affilié ?
                            </button>
                        </h2>
                        <div id="affFaq2d" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Oui, tout organisateur peut activer un profil affilié. En revanche, un organisateur ne peut pas être le parrain direct d'un autre organisateur : il participe via le co-parrainage des événements.</div>
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
                    <h2 class="h3 fw-bold mb-2">Prêt à monétiser votre réseau événementiel ?</h2>
                    <p class="mb-4" style="color: rgba(255,255,255,0.8);">Rejoignez les créateurs, agences et partenaires qui génèrent déjà des revenus récurrents avec PaxEvent.</p>
                    <button type="button" class="btn btn-lg fw-bold px-4" style="background: var(--accent); color: var(--sombre);">
                        Créer mon compte affilié maintenant <i class="bi bi-arrow-right ms-1"></i>
                    </button>
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
                                    <p>Le Programme permet à des personnes physiques ou morales (ci-après les « Affiliés » ou « Apporteurs d'Affaires ») de percevoir une commission en lien avec les ventes de billets réalisées sur la plateforme, selon <strong>deux mécanismes distincts et cumulables</strong> :
                                    <ol>
                                        <li><strong>Le parrainage direct d'un organisateur</strong> — rémunéré par PaxEvent sur le premier événement de l'organisateur parrainé ;</li>
                                        <li><strong>Le co-parrainage d'un événement</strong> — rémunéré par l'Organisateur, sur les ventes tracées via le lien de l'Affilié.</li>
                                    </ol>
                                    Dans tous les cas, aucune commission n'est due en l'absence de vente tracée et intégralement payée.</p>

                                    <h6 class="fw-bold mt-4 mb-2">1. Définitions</h6>
                                    <p><strong>Affilié :</strong> toute personne inscrite au Programme.<br>
                                    <strong>Organisateur :</strong> utilisateur publiant des événements sur PaxEvent.<br>
                                    <strong>Parrain direct :</strong> l'Affilié dont le lien a permis l'inscription d'un Organisateur.<br>
                                    <strong>Co-parrain :</strong> l'Affilié qui promeut un événement ouvert au co-parrainage.<br>
                                    <strong>Vente tracée :</strong> achat de billet réalisé au moyen du lien ou du code d'un Affilié et intégralement payé.<br>
                                    <strong>Commission PaxEvent :</strong> la commission perçue par PaxEvent sur une vente (de 5 % à 10 % selon l'Organisateur ou l'événement).</p>

                                    <h6 class="fw-bold mt-4 mb-2">2. Inscription et éligibilité</h6>
                                    <p><strong>Adhésion :</strong> L'inscription au Programme est gratuite et s'effectue via un espace dédié de la plateforme. PaxEvent se réserve le droit d'accepter, de refuser ou de suspendre toute candidature sans obligation de justification.</p>
                                    <p><strong>Organisateurs affiliés :</strong> Un Organisateur peut activer un profil affilié. Toutefois, un Organisateur ne peut pas être le parrain direct (mécanisme A) d'un autre Organisateur ; il participe au Programme via le co-parrainage d'événements (mécanisme B).</p>
                                    <p><strong>Capacité légale :</strong> L'Affilié certifie être majeur et disposer de la capacité juridique ou des statuts professionnels nécessaires au Bénin pour percevoir des revenus d'apport d'affaires.</p>

                                    <h6 class="fw-bold mt-4 mb-2">3. Mécanisme A — Parrainage direct d'un organisateur</h6>
                                    <p><strong>Modalité :</strong> L'Affilié (non-Organisateur) parraine un Organisateur au moyen d'un lien de parrainage mis à disposition par PaxEvent.</p>
                                    <p><strong>Exclusivité :</strong> Un Organisateur ne peut avoir qu'<strong>un seul parrain direct</strong>. Le parrainage est attribué au premier lien de parrainage effectivement utilisé lors de l'inscription ; toute tentative concurrente est sans effet.</p>
                                    <p><strong>Événement couvert :</strong> Le parrainage direct ouvre droit à commission sur <strong>le tout premier événement</strong> publié par l'Organisateur parrainé. Les ventes des événements suivants ne sont pas couvertes par ce mécanisme (elles peuvent l'être via le co-parrainage, mécanisme B).</p>
                                    <p><strong>Rémunération :</strong> La commission est versée par <strong>PaxEvent</strong> et s'élève à <strong>20 % de la Commission PaxEvent</strong> de la vente concernée. Elle est automatiquement due sur chaque vente payée du premier événement, sans nécessité de lien ou de code de l'Affilié.</p>
                                    <p><strong>Validation :</strong> Le parrainage direct n'est efficient qu'à compter de la validation du compte de l'Organisateur par PaxEvent.</p>

                                    <h6 class="fw-bold mt-4 mb-2">4. Mécanisme B — Co-parrainage d'un événement</h6>
                                    <p><strong>Ouverture :</strong> Tout Organisateur peut ouvrir un de ses événements au co-parrainage et fixer librement la commission offerte au Co-parrain.</p>
                                    <p><strong>Financement :</strong> La commission de co-parrainage est <strong>à la charge de l'Organisateur</strong>. Elle n'est jamais prélevée sur la Commission PaxEvent.</p>
                                    <p><strong>Plafond :</strong> La commission de co-parrainage ne peut excéder la marge nette perçue par l'Organisateur sur le billet concerné (après déduction de la Commission PaxEvent). En conséquence, elle ne réduit jamais la Commission PaxEvent.</p>
                                    <p><strong>Pluralité :</strong> Plusieurs Co-parrains peuvent promouvoir un même événement. Une même vente ne génère qu'une seule commission de co-parrainage, au profit d'un seul Co-parrain.</p>
                                    <p><strong>Cumul :</strong> Sur le premier événement d'un Organisateur, un même billet peut générer à la fois une commission de parrainage direct (mécanisme A) et une commission de co-parrainage (mécanisme B).</p>

                                    <h6 class="fw-bold mt-4 mb-2">5. Liens, codes et traçabilité des ventes</h6>
                                    <p><strong>Génération :</strong> Après validation de son compte, l'Affilié dispose d'un code de parrainage et peut générer un lien de traçage dédié par événement ouvert au co-parrainage.</p>
                                    <p><strong>Vente tracée :</strong> Une commission de co-parrainage n'est due que si le lien ou le code de l'Affilié a été utilisé lors de l'achat et si le paiement a été intégralement confirmé. Sans utilisation du lien ou du code, aucune commission de co-parrainage n'est attribuée. La commission de parrainage direct, quant à elle, est due automatiquement sur les ventes payées du premier événement.</p>
                                    <p><strong>Règle d'attribution :</strong> Pour le co-parrainage, l'attribution suit la règle du « dernier clic valide » : la commission revient au Co-parrain dont le lien ou le code a été utilisé au moment de l'achat.</p>
                                    <p><strong>Fenêtre de suivi :</strong> Le lien ouvre une session traçable de trente (30) jours à compter de sa première activation. Au-delà, l'attribution expire.</p>
                                    <p><strong>Exclusions :</strong> Sont hors du champ du Programme les événements gratuits (0 FCFA), les billets physiques (lots à codes QR) et toute vente n'ayant pas donné lieu à un paiement intégral et définitif.</p>

                                    <h6 class="fw-bold mt-4 mb-2">6. Calcul et taux de commission</h6>
                                    <p><strong>Base de calcul :</strong> La commission est calculée exclusivement sur la valeur faciale nette du billet vendu (hors frais de service de la plateforme et taxes).</p>
                                    <p><strong>Parrainage direct :</strong> 20 % de la Commission PaxEvent, soit un ordre de grandeur de 1 % à 2 % de la valeur faciale du billet selon le taux perçu par PaxEvent (5 % à 10 %).</p>
                                    <p><strong>Co-parrainage :</strong> Pourcentage fixé librement par l'Organisateur, dans la limite de sa marge nette. Le taux applicable à chaque événement est affiché à l'Affilié sur son tableau de bord <strong>avant le début de toute promotion</strong>.</p>
                                    <p><em>Exemple : pour un billet à 10 000 FCFA vendu avec une Commission PaxEvent de 10 % (1 000 FCFA) et une commission de co-parrainage de 3 % (300 FCFA) : le Co-parrain perçoit 300 FCFA ; s'il s'agit du premier événement parrainé, le Parrain direct perçoit en outre 20 % de 1 000 FCFA, soit 200 FCFA ; PaxEvent conserve 1 000 FCFA ; l'Organisateur reçoit le solde.</em></p>
                                    <p><strong>Plafond global :</strong> Sur une même vente, la Commission PaxEvent ne peut jamais être réduite par une commission d'affiliation.</p>
                                    <p><strong>Événement gratuit :</strong> Un événement gratuit (0 FCFA) ne génère aucune commission ni droit à rémunération.</p>
                                    <p><strong>Changement de taux :</strong> Tout nouveau taux ne s'applique qu'aux ventes tracées postérieures à sa publication. Les ventes déjà tracées conservent le taux affiché au moment de leur attribution.</p>

                                    <h6 class="fw-bold mt-4 mb-2">7. Maturation, paiement et reversement</h6>
                                    <p><strong>Maturation :</strong> Une commission est comptabilisée en attente puis devient disponible <strong>à la fin de l'événement concerné</strong>. Ce délai couvre la fenêtre d'éventuels remboursements. Tant qu'une commission n'est pas disponible, elle ne peut pas être retirée.</p>
                                    <p><strong>Seuil de paiement :</strong> Les commissions disponibles sont payables dès que le solde de l'Affilié atteint un seuil minimum de 5 000 XOF.</p>
                                    <p><strong>Canaux de paiement :</strong> Les reversements s'effectuent directement sur le numéro Mobile Money (MTN, Moov, Wave) ou le compte bancaire renseigné par l'Affilié.</p>
                                    <p><strong>Frais techniques de reversement :</strong> Conformément à notre infrastructure FedaPay, les frais de transfert de masse (PAYOUT) s'appliquent selon le barème officiel en vigueur de la plateforme.</p>
                                    <p><strong>Vérification d'identité :</strong> PaxEvent peut demander un justificatif d'identité avant tout premier reversement.</p>

                                    <h6 class="fw-bold mt-4 mb-2">8. Annulation et remboursement</h6>
                                    <p><strong>Commission non encore disponible :</strong> Si un billet fait l'objet d'un remboursement (annulation de l'événement, transaction frauduleuse, rejet Mobile Money ou chargeback), la commission correspondante est automatiquement annulée.</p>
                                    <p><strong>Commission déjà versée :</strong> Si la commission a déjà été créditée ou versée, elle est reprise (clawback) sur le solde de l'Affilié. Le solde peut devenir négatif et être compensé par les commissions futures jusqu'à apurement. Chaque annulation ou reprise est visible dans l'historique du compte Affilié.</p>
                                    <p><strong>Responsabilité de l'Organisateur :</strong> En cas d'annulation d'un événement, la commission de co-parrainage non encore versée n'est pas due ; l'Organisateur n'est pas débité d'une commission qui n'était pas encore due.</p>

                                    <h6 class="fw-bold mt-4 mb-2">9. Règles de conduite et pratiques interdites</h6>
                                    <p>L'Affilié s'engage à préserver l'image de marque de PaxEvent et de l'Organisateur. Sont strictement interdits :</p>
                                    <ul>
                                        <li><strong>Le Spamming :</strong> l'envoi massif d'e-mails, de SMS ou de messages WhatsApp non sollicités contenant les liens d'affiliation.</li>
                                        <li><strong>Le « Self-Affiliation » :</strong> le fait d'utiliser son propre lien d'affiliation pour acheter ses propres billets dans le seul but de toucher une commission.</li>
                                        <li><strong>Publicité mensongère :</strong> divulguer de fausses informations sur l'événement (tarifs erronés, faux invités, fausses dates) pour forcer la vente.</li>
                                        <li><strong>Achat de mots-clés :</strong> l'achat de mots-clés sur Google Ads ou d'autres régies publicitaires contenant la marque « PaxEvent » ou le nom exact de l'événement sans accord écrit.</li>
                                        <li><strong>Parrainage frauduleux :</strong> le parrainage d'un Organisateur dont l'Affilié est lui-même l'organisateur, un associé, un mandataire ou un proche, dans le but de détourner le Programme à son profit.</li>
                                        <li><strong>Co-parrainage fictif :</strong> toute collusion entre l'Organisateur et un Co-parrain visant à simuler des ventes ou à détourner des commissions.</li>
                                    </ul>

                                    <h6 class="fw-bold mt-4 mb-2">10. Responsabilité et indépendance</h6>
                                    <p>L'Affilié exerce son activité en totale indépendance. Il n'existe aucun lien de subordination, de contrat de travail ou de mandat de représentation exclusif entre PaxEvent et l'Affilié. L'Affilié est seul responsable de la déclaration fiscale de ses gains auprès des autorités compétentes au Bénin.</p>

                                    <h6 class="fw-bold mt-4 mb-2">11. Modification et résiliation</h6>
                                    <p><strong>Résiliation par l'Affilié :</strong> L'Affilié peut cesser sa participation au Programme à tout moment en cessant d'utiliser ses liens.</p>
                                    <p><strong>Bannissement pour fraude :</strong> En cas de non-respect constaté de l'Article 9, PaxEvent annulera immédiatement le compte de l'Affilié sans préavis. Toutes les commissions en attente de paiement seront définitivement confisquées au profit de PaxEvent ou de l'Organisateur pour réparer le préjudice causé.</p>

                                    <h6 class="fw-bold mt-4 mb-2">12. Loi applicable</h6>
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
