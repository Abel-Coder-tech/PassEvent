@extends('layouts.public')

@section('title', 'API PaxEvent — Bientôt disponible')
@section('description', 'L’API PaxEvent arrive bientôt. Découvrez les services PaxEvent et contactez-nous pour vos projets d’intégration.')
@section('og_title', 'API PaxEvent — Bientôt disponible')
@section('og_description', 'Connectez vos outils à PaxEvent grâce à notre API, bientôt disponible.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">API</li>
@endsection

@section('styles')
<style>
    .api-page-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(3.5rem, 8vw, 6.5rem) 0;
        background:
            radial-gradient(circle at 82% 18%, rgba(153,114,176,0.28), transparent 28%),
            linear-gradient(135deg, #241536 0%, #542680 58%, #765098 100%);
        color: #fff;
    }
    .api-page-hero::after {
        position: absolute;
        right: -5rem;
        bottom: -11rem;
        width: 25rem;
        height: 25rem;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 50%;
        content: "";
        box-shadow: 0 0 0 2rem rgba(255,255,255,0.025), 0 0 0 4rem rgba(255,255,255,0.02);
        pointer-events: none;
    }
    .api-page-hero-content { position: relative; z-index: 1; }
    .api-page-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.2rem;
        color: #f3d9ff;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }
    .api-page-kicker i { font-size: 1rem; }
    .api-page-title {
        max-width: 760px;
        margin: 0;
        font-size: clamp(2.2rem, 6vw, 4.25rem);
        font-weight: 750;
        letter-spacing: -0.045em;
        line-height: 1.08;
        text-wrap: balance;
    }
    .api-page-lead {
        max-width: 620px;
        margin: 1.25rem 0 0;
        color: rgba(255,255,255,0.82);
        font-size: clamp(1rem, 2vw, 1.15rem);
        line-height: 1.7;
    }
    .api-page-status {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin-top: 1.75rem;
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 999px;
        background: rgba(255,255,255,0.1);
        padding: 0.6rem 0.9rem;
        color: #fff;
        font-size: 0.88rem;
        font-weight: 700;
    }
    .api-page-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ff9292;
        box-shadow: 0 0 0 4px rgba(255,146,146,0.16);
    }
    .api-page-section { padding: clamp(3rem, 7vw, 5rem) 0; }
    .api-page-section-title {
        margin: 0;
        color: #241a2d;
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 750;
        letter-spacing: -0.03em;
    }
    .api-page-section-lead {
        max-width: 620px;
        margin: 0.65rem 0 0;
        color: #655b6c;
        line-height: 1.65;
    }
    .api-explore-card {
        display: flex;
        min-height: 100%;
        flex-direction: column;
        border: 1px solid #ece6f1;
        border-radius: 18px;
        background: #fff;
        padding: 1.4rem;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 8px 26px rgba(42,31,58,0.045);
        transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
    }
    .api-explore-card:hover {
        transform: translateY(-3px);
        border-color: rgba(84,38,128,0.35);
        color: inherit;
        box-shadow: 0 16px 36px rgba(42,31,58,0.1);
    }
    .api-explore-card:focus-visible {
        outline: 3px solid #9972b0;
        outline-offset: 3px;
    }
    .api-explore-icon {
        display: grid;
        width: 48px;
        height: 48px;
        place-items: center;
        margin-bottom: 1.1rem;
        border-radius: 14px;
        background: #f3edf8;
        color: var(--violet);
        font-size: 1.35rem;
    }
    .api-explore-card h3 {
        margin: 0;
        color: #292131;
        font-size: 1.05rem;
        font-weight: 700;
    }
    .api-explore-card p {
        flex: 1;
        margin: 0.55rem 0 1.1rem;
        color: #655b6c;
        font-size: 0.92rem;
        line-height: 1.6;
    }
    .api-explore-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--violet);
        font-size: 0.88rem;
        font-weight: 700;
    }
    .api-contact-note {
        border: 1px solid #eee8f2;
        border-radius: 16px;
        background: #faf8fc;
        padding: 1.25rem 1.4rem;
        color: #51475a;
        line-height: 1.65;
    }
    .api-contact-note a { color: var(--violet); font-weight: 700; }
    @media (prefers-reduced-motion: reduce) {
        .api-explore-card { transition: none; }
        .api-explore-card:hover { transform: none; }
    }
</style>
@endsection

@section('content')
<section class="api-page-hero">
    <div class="container api-page-hero-content">
        <span class="api-page-kicker"><i class="bi bi-braces" aria-hidden="true"></i> PaxEvent pour les développeurs</span>
        <h1 class="api-page-title">Connectez PaxEvent à vos outils.</h1>
        <p class="api-page-lead">
            Nous préparons une API pour vous permettre d’intégrer les services PaxEvent à vos propres applications.
            La documentation et les accès seront annoncés ici.
        </p>
        <span class="api-page-status"><span class="api-page-status-dot" aria-hidden="true"></span>Bientôt disponible</span>
    </div>
</section>

<section class="api-page-section">
    <div class="container">
        <div class="mb-4">
            <h2 class="api-page-section-title">Explorez PaxEvent en attendant</h2>
            <p class="api-page-section-lead">Ces liens vous permettent de découvrir dès maintenant les services que l’API pourra accompagner.</p>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <a class="api-explore-card" href="{{ route('evenements.public') }}">
                    <span class="api-explore-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
                    <h3>Événements</h3>
                    <p>Parcourez les événements publiés sur PaxEvent et découvrez leur programmation.</p>
                    <span class="api-explore-action">Voir les événements <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a class="api-explore-card" href="{{ route('evenements.public') }}">
                    <span class="api-explore-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                    <h3>Billetterie</h3>
                    <p>Consultez les offres et choisissez votre billet parmi les événements disponibles.</p>
                    <span class="api-explore-action">Trouver un billet <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a class="api-explore-card" href="{{ route('contact') }}">
                    <span class="api-explore-icon"><i class="bi bi-plug" aria-hidden="true"></i></span>
                    <h3>Intégrations</h3>
                    <p>Vous avez un projet de connexion avec PaxEvent ? Parlons de votre besoin.</p>
                    <span class="api-explore-action">Contacter l’équipe <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </div>
        <p class="api-contact-note mt-4 mb-0">
            Une question sur l’API ou un besoin d’intégration urgent ?
            <a href="{{ route('contact') }}">Contactez notre équipe</a>.
        </p>
    </div>
</section>
@endsection
