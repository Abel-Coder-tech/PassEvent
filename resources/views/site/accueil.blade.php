@extends('layouts.public')

@section('title', 'PaxEvent — Billetterie en ligne 100% Bénin')
@section('description', 'PaxEvent — Billetterie en ligne 100% Bénin. Achetez et vendez vos billets d\'événements en toute simplicité et sécurité.')
@section('og_title', 'PaxEvent — Billetterie en ligne 100% Bénin')
@section('og_description', 'Billetterie en ligne 100% Bénin. Solution rapide et sécurisée pour gérer vos événements et vendre vos billets en ligne.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
@endsection

@section('content')
<!-- Hero -->
<section class="hero-section">
    <div class="hero-bg">
        <div class="circle c1"></div>
        <div class="circle c2"></div>
        <div class="circle c3"></div>
        <div class="circle c4"></div>
        <div class="hero-grid"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 d-flex flex-column align-items-center justify-content-center text-center text-lg-start">
                <span class="hero-chip">Billetterie en ligne 100% Bénin</span>
                <h1 class="hero-title align-items-center justify-content-center text-center text-center">Achetez et vendez vos tickets en quelques clics</h1>
                <p class="hero-subtitle align-items-center justify-content-center text-center">La solution simple et rapide pour gérer vos événements et vendre vos billets en ligne.</p>
                <p class="hero-features align-items-center justify-content-center text-center">Billet électronique — Scan QR code — Paiement sécurisé</p>
                <div class="hero-actions">
                    <a href="{{ route('evenements.public') }}" class="btn-hero-primary">
                        Acheter un ticket <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="{{ route('inscriptions.organisateur') }}" class="btn-hero-outline">
                        Devenir organisateur
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat text-center">
                        <span class="hero-stat-value">500+</span>
                        <span class="hero-stat-label">Événements</span>
                    </div>
                    <div class="hero-stat text-center">
                        <span class="hero-stat-value">10k+</span>
                        <span class="hero-stat-label">Billets vendus</span>
                    </div>
                    <div class="hero-stat text-center">
                        <span class="hero-stat-value">50+</span>
                        <span class="hero-stat-label">Organisateurs</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="hero-mockup">
                    <img src="{{ asset('images/image_heros.jpeg') }}" alt="Illustration PaxEvent" class="hero-illustration">
                    <div class="hero-float-card float-card-1">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Billet électronique</span>
                    </div>
                    <div class="hero-float-card float-card-2">
                        <i class="bi bi-shield-check"></i>
                        <span>Paiement sécurisé</span>
                    </div>
                    <div class="hero-float-card float-card-3">
                        <i class="bi bi-qr-code"></i>
                        <span>Scan QR code</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .hero-section {
        font-family: 'Neomatric Alt', sans-serif;
        min-height: 85vh;
        display: flex;
        align-items: center;
        padding: 0;
        position: relative;
        overflow: hidden;
        background: #f7f5f3;
    }
    .hero-section .container {
        padding-top: 2rem;
        padding-bottom: 0;
    }
    .hero-section .row {
        min-height: 80vh;
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
        filter: blur(80px);
    }
    .c1 { width: 500px; height: 500px; background: #542680; top: -150px; right: -100px; opacity: 0.15; }
    .c2 { width: 350px; height: 350px; background: #FED514; bottom: -100px; left: -80px; opacity: 0.12; }
    .c3 { width: 200px; height: 200px; background: #9972B0; top: 40%; right: 30%; opacity: 0.08; }
    .c4 { width: 150px; height: 150px; background: #542680; bottom: 20%; left: 40%; opacity: 0.1; }
    .hero-grid {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 40px 40px;
    }

    .hero-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 1rem;
        background: rgba(84,38,128,0.08);
        border: 1px solid rgba(84,38,128,0.15);
        border-radius: 20px;
        color: #542680;
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        animation: heroFadeUp 0.6s ease forwards;
    }

    .hero-title {
        font-family: 'Neomatric Alt Black', sans-serif;
        font-weight: 1000;
        align-items: center;
        font-size: 3.2rem;
        color: #211C31;
        margin: 0 0 0.75rem;
        line-height: 1.12;
        animation: heroFadeUp 0.6s ease forwards;
    }
    .hero-subtitle {
        align-items: center;
        font-size: 1.1rem;
        font-weight: 400;
        color: rgba(33,28,49,0.65);
        margin: 0 0 2rem;
        max-width: 480px;
        animation: heroFadeUp 0.6s ease 0.2s both;
    }
    .hero-actions {
        display: flex;
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
        padding: 0.85rem 2.2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: heroScaleIn 0.5s ease 0.6s both;
        position: relative;
        overflow: hidden;
    }
    @keyframes heroScaleIn {
        0% { opacity: 0; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }
    .btn-hero-primary {
        background: linear-gradient(135deg, #542680, #9972B0);
        color: #fff;
        box-shadow: 0 4px 20px rgba(84,38,128,0.4);
    }
    .btn-hero-primary::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.1));
        pointer-events: none;
    }
    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(84,38,128,0.55);
        color: #fff;
    }
    .btn-hero-primary:active {
        transform: translateY(-1px);
    }
    .btn-hero-outline {
        border: 2px solid rgba(84,38,128,0.3);
        color: #542680;
        background: transparent;
    }
    .btn-hero-outline:hover {
        border-color: #542680;
        background: rgba(84,38,128,0.08);
        color: #542680;
        transform: translateY(-2px);
    }

    .hero-features {
        font-size: 0.85rem;
        font-weight: 600;
        color: #542680;
        margin: -0.5rem 0 1.5rem;
        letter-spacing: 0.3px;
        animation: heroFadeUp 0.6s ease 0.3s both;
    }
    .hero-features::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #542680;
        margin-right: 0.5rem;
        vertical-align: middle;
    }
    .hero-stats {
        display: flex;
        gap: 2rem;
        margin-top: 2.5rem;
        animation: heroFadeUp 0.6s ease 0.8s both;
    }
    .hero-stat {
        display: flex;
        flex-direction: column;
    }
    .hero-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #542680;
        line-height: 1.2;
    }
    .hero-stat-label {
        font-size: 0.78rem;
        color: rgba(33,28,49,0.5);
        font-weight: 500;
    }

    .hero-mockup {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-illustration {
        width: 500px;
        height: 400px;
        border-radius: 10px;
        animation: heroFadeUp 0.8s ease 0.3s both;
    }
    .hero-float-card {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        font-size: 0.82rem;
        font-weight: 600;
        color: #211C31;
        animation: float 3s ease-in-out infinite;
    }
    .hero-float-card i {
        font-size: 1.1rem;
        color: #542680;
    }
    .float-card-1 {
        top: 10%;
        center: -10px;
        animation-delay: 0s;
    }
    .float-card-2 {
        bottom: 15%;
        left: -20px;
        animation-delay: 1.5s;
    }
    .float-card-3 {
        bottom: 50%;
        right: -20px;
        animation-delay: 0.75s;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    @media (max-width: 991.98px) {
        .hero-section { min-height: auto; padding: 3rem 1.5rem; }
        .hero-section .row { min-height: auto; }
        .hero-title { font-size: 2.4rem; text-align: center; }
        .hero-subtitle { text-align: center; margin-left: auto; margin-right: auto; }
        .hero-actions { justify-content: center; }
        .hero-stats { justify-content: center; }
        .hero-chip { margin-left: auto; margin-right: auto; }
        .hero-features { text-align: center; margin-left: auto; margin-right: auto; }
        .hero-illustration {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 2rem;
        }
        .float-card-1 { top: 5%; right: -10px; }
        .float-card-2 { bottom: 20%; left: -10px; }
        .float-card-3 { bottom: 50%; right: -10px; }
    }
    @media (max-width: 767.98px) {
        .hero-title { font-size: 1.8rem; }
        .hero-subtitle { font-size: 0.95rem; }
        .hero-features { font-size: 0.78rem; }
        .hero-actions { flex-direction: column; width: 100%; }
        .btn-hero-primary,
        .btn-hero-outline { width: 100%; justify-content: center; }
        .hero-stats { gap: 1.5rem; }
        .hero-stat-value { font-size: 1.2rem; }
        .hero-illustration {
            width: 240px;
            height: 240px;
            border-radius: 50%;
            object-fit: cover;
            max-width: 100%;
        }
        .float-card-1 { top: 0; right: -5px; font-size: 0.72rem; padding: 0.4rem 0.7rem; }
        .float-card-2 { bottom: 18%; left: -5px; font-size: 0.72rem; padding: 0.4rem 0.7rem; }
        .float-card-3 { bottom: 48%; right: -5px; font-size: 0.72rem; padding: 0.4rem 0.7rem; }
    }
</style>

<!-- Evenements a venir -->
<section class="section-events">
    <div class="container">
        <!-- Barre de recherche -->
        <form action="{{ route('accueil') }}" method="GET" class="filter-search mb-4">
            <div class="search-wrap">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="q" class="search-input" placeholder="Rechercher par nom, catégorie, lieu..." value="{{ $q ?? '' }}">
            </div>
            @if($selectedCategorie)
                <input type="hidden" name="categorie" value="{{ $selectedCategorie }}">
            @endif
            @if($selectedDate)
                <input type="hidden" name="date" value="{{ $selectedDate }}">
            @endif
            <button type="submit" class="btn-search"><i class="bi bi-search"></i></button>
            @if($q || $selectedCategorie || $selectedDate)
                <a href="{{ route('accueil') }}" class="btn-clear"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>

        @if($evenementsVedettes->isNotEmpty())
            <div class="events-grid">
                @foreach($evenementsVedettes as $evenement)
                    @php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
                        $prixEtudiant = $evenement->tarifs->where('categorie', 'etudiant')->min('prix');
                        $prixExterne = $evenement->tarifs->where('categorie', 'externe')->min('prix');
                        $prixDernier = $evenement->tarifs->min('prix');
                    @endphp
                    <div class="event-col">
                        <a href="{{ route('evenements.public.show', $evenement->id) }}" class="ev-card">
                            <div class="ev-img">
                                @if($evenement->image)
                                    <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}">
                                @else
                                    <div class="ev-img-placeholder">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                @endif
                                @if($evenement->categorie)
                                    <span class="ev-badge">{{ ucfirst($evenement->categorie) }}</span>
                                @endif
                                @if($estComplet)
                                    <span class="ev-badge ev-badge-complet">Complet</span>
                                @endif
                                <div class="ev-img-overlay">
                                    <span class="ev-img-btn">Voir les détails <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </div>
                            <div class="ev-body">
                                <h6 class="ev-title">{{ $evenement->titre }}</h6>
                                <p class="ev-meta">
                                    <i class="bi bi-calendar3"></i> {{ $evenement->date_event->isoFormat('D MMM YYYY') }}<br>
                                    <i class="bi bi-geo-alt"></i> {{ Str::limit($evenement->lieu, 25) }}
                                </p>
                                @if($evenement->gratuit)
                                    <div class="ev-price">Entrée <strong>Gratuit</strong></div>
                                @elseif($prixDernier)
                                    <div class="ev-price">À partir de <strong>{{ number_format($prixDernier, 0, ',', ' ') }} F</strong></div>
                                @else
                                    <div class="ev-price"><span class="text-muted">Tarifs non configurés</span></div>
                                @endif
                                <div class="ev-gauge">
                                    <div class="d-flex justify-content-between" style="font-size:0.68rem;">
                                        <span>{{ $placesRestantes }} places</span>
                                        <span>{{ $remplissage }}%</span>
                                    </div>
                                    <div class="gauge-track"><div class="gauge-fill" style="width:{{ min($remplissage,100) }}%"></div></div>
                                </div>
                                @if($estComplet)
                                    <span class="ev-btn disabled">Complet</span>
                                @else
                                    <span class="ev-btn">
                                        @if($evenement->gratuit)
                                            <i class="bi bi-check-circle me-1"></i> Participer
                                        @else
                                            <i class="bi bi-ticket-perforated me-1"></i> Acheter ticket
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>Aucun événement disponible</h5>
                <p>Revenez plus tard pour découvrir nos prochains événements.</p>
                @if($q || $selectedCategorie || $selectedDate)
                    <a href="{{ route('accueil') }}" class="btn-outline"><i class="bi bi-arrow-left me-1"></i> Voir tous les événements</a>
                @endif
            </div>
        @endif

        @if($evenementsVedettes->isNotEmpty())
            <div class="text-center mt-5">
                <a href="{{ route('evenements.public') }}" class="btn-outline btn-outline-lg">Voir tous les événements <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        @endif
    </div>
</section>

<style>
.section-events {
    padding: 5rem 0;
    background: #fff;
}
.section-header {
    text-align: center;
    margin-bottom: 2.5rem;
}
.section-tag {
    display: inline-block;
    padding: 0.25rem 0.9rem;
    background: linear-gradient(135deg, rgba(84,38,128,0.1), rgba(84,38,128,0.05));
    border: 1px solid rgba(84,38,128,0.15);
    border-radius: 20px;
    color: #542680;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}
.section-header h2 {
    font-size: 2rem;
    font-weight: 800;
    color: #211C31;
    margin: 0 0 0.3rem;
}
.section-header p {
    color: #6c757d;
    font-size: 0.95rem;
    margin: 0;
}

.filter-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 2.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid #eee;
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
    padding: 0.65rem 1rem 0.65rem 2.6rem;
    border: 1.5px solid #e5e5e5;
    border-radius: 12px;
    font-size: 0.88rem;
    background: #f8f7fa;
    outline: none;
    transition: all 0.2s;
}
.search-input:focus {
    border-color: #542680;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(84,38,128,0.08);
}
.btn-search {
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 0.65rem 1.2rem;
    font-size: 0.9rem;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(84,38,128,0.25);
}
.btn-search:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(84,38,128,0.35); }
.btn-clear {
    display: inline-flex;
    align-items: center;
    padding: 0.65rem 0.9rem;
    border: 1.5px solid #e5e5e5;
    border-radius: 12px;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
    background: #fff;
}
.btn-clear:hover { border-color: #dc3545; color: #dc3545; background: rgba(220,53,69,0.03); }

.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
}
.filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 1.1rem;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1.5px solid #e5e5e5;
    background: #fff;
    color: #3d4345;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.chip:hover { border-color: #542680; color: #542680; transform: translateY(-1px); }
.chip.active { background: linear-gradient(135deg, #542680, #9972B0); border-color: #542680; color: #fff; box-shadow: 0 2px 8px rgba(84,38,128,0.2); }
.filter-select {
    padding: 0.4rem 0.9rem;
    border: 1.5px solid #e5e5e5;
    border-radius: 12px;
    font-size: 0.82rem;
    background: #fff;
    outline: none;
    transition: border-color 0.2s;
}
.filter-select:focus { border-color: #542680; }

.events-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}
@media (max-width: 1100px) { .events-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 820px) { .events-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 520px) { .events-grid { grid-template-columns: 1fr; } }

.event-col { display: flex; }
.ev-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #eee;
    text-decoration: none;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.ev-card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
    transform: translateY(-6px);
    border-color: transparent;
}
.ev-img {
    position: relative;
    height: 160px;
    overflow: hidden;
    background: #f0eeec;
}
.ev-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}
.ev-card:hover .ev-img img {
    transform: scale(1.08);
}
.ev-img-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #542680, #542680);
    color: #fff;
    font-size: 2.5rem;
}
.ev-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255,255,255,0.95);
    color: #542680;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: 10px;
    backdrop-filter: blur(8px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.ev-badge-complet {
    left: auto;
    right: 10px;
    background: #dc3545;
    color: #fff;
}
.ev-img-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}
.ev-card:hover .ev-img-overlay {
    opacity: 1;
}
.ev-img-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.2rem;
    background: rgba(255,255,255,0.95);
    border-radius: 10px;
    color: #542680;
    font-weight: 700;
    font-size: 0.82rem;
    transform: translateY(10px);
    transition: transform 0.3s;
}
.ev-card:hover .ev-img-btn {
    transform: translateY(0);
}
.ev-body {
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    position: relative;
}
.ev-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.4rem;
    line-height: 1.3;
    transition: color 0.2s;
}
.ev-card:hover .ev-title {
    color: #542680;
}
.ev-meta {
    font-size: 0.78rem;
    color: #6c757d;
    margin: 0 0 0.6rem;
    line-height: 1.5;
}
.ev-meta i { margin-right: 0.3rem; color: #542680; }
.ev-price {
    font-size: 0.82rem;
    margin-bottom: 0.5rem;
}
.ev-price strong {
    font-size: 1.05rem;
    color: #542680;
}
.ev-gauge {
    margin-bottom: 0.7rem;
}
.gauge-track {
    height: 5px;
    background: #f0eeec;
    border-radius: 4px;
    margin-top: 3px;
    overflow: hidden;
}
.gauge-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, #542680, #9972B0);
    transition: width 0.3s;
}
.ev-btn {
    display: block;
    width: 100%;
    padding: 0.45rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 700;
    text-align: center;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    transition: all 0.25s;
    margin-top: auto;
    box-shadow: 0 2px 8px rgba(84,38,128,0.2);
    letter-spacing: 0.2px;
}
.ev-btn:hover { background: linear-gradient(135deg, #3d1a5c, #542680); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(84,38,128,0.3); }
.ev-btn.disabled {
    background: #adb5bd;
    box-shadow: none;
    pointer-events: none;
}

.empty-state {
    text-align: center;
    padding: 4rem 0;
}
.empty-state i {
    font-size: 3.5rem;
    color: #d0d0d0;
    display: block;
    margin-bottom: 1rem;
}
.empty-state h5 { color: #6c757d; margin-bottom: 0.3rem; }
.empty-state p { color: #9a9a9a; font-size: 0.9rem; }

.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.6rem;
    border: 2px solid #542680;
    border-radius: 12px;
    color: #542680;
    font-weight: 700;
    font-size: 0.88rem;
    text-decoration: none;
    transition: all 0.25s;
}
.btn-outline:hover { background: rgba(84,38,128,0.06); transform: translateY(-2px); box-shadow: 0 4px 14px rgba(84,38,128,0.12); }
.btn-outline-lg {
    padding: 0.75rem 2rem;
    font-size: 0.95rem;
}
</style>

<!-- Comment ca marche -->
<section class="section-steps">
    <div class="container">
        <div class="section-header">
            <span class="section-tag section-tag-light">Fonctionnement</span>
            <h2 style="color: #211C31;">Comment ça marche ?</h2>
            <p style="color: rgba(33,28,49,0.6);">4 étapes simples pour obtenir votre billet</p>
        </div>
        <div class="steps-grid" id="steps-grid">
            <div class="step-item" data-step="1">
                <div class="step-icon-wrap">
                    <div class="step-num">1</div>
                    <i class="bi bi-search"></i>
                </div>
                <h6>Choisir</h6>
                <p>Trouvez l'événement qui vous intéresse</p>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-icon-wrap">
                    <div class="step-num">2</div>
                    <i class="bi bi-pencil-square"></i>
                </div>
                <h6>Remplir</h6>
                <p>Entrez vos informations et choisissez votre tarif</p>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-icon-wrap">
                    <div class="step-num">3</div>
                    <i class="bi bi-credit-card"></i>
                </div>
                <h6>Payer</h6>
                <p>Paiement sécurisé via FedaPay (Mobile Money)</p>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-icon-wrap">
                    <div class="step-num">4</div>
                    <i class="bi bi-envelope-check"></i>
                </div>
                <h6>Recevoir</h6>
                <p>Recevez votre ticket PDF par email</p>
            </div>
        </div>
    </div>
</section>

<style>
.section-steps {
    padding: 5rem 0;
    background: #f7f5f3;
    position: relative;
    overflow: hidden;
}
.section-steps::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(84,38,128,0.06) 1px, transparent 1px);
    background-size: 40px 40px;
}
.section-tag-light {
    background: rgba(84,38,128,0.08);
    border-color: rgba(84,38,128,0.15);
    color: #542680;
}
.steps-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}
@media (max-width: 767px) { .steps-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 420px) { .steps-grid { grid-template-columns: 1fr; } }

.step-item {
    text-align: center;
    padding: 2.5rem 1.5rem;
    background: #fff;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.06);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.step-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #542680, #9972B0);
    opacity: 0;
    transition: opacity 0.3s;
}
.step-item:hover::before {
    opacity: 1;
}
.step-item:hover {
    background: #fff;
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    border-color: rgba(84,38,128,0.15);
}
.step-icon-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    margin-bottom: 1rem;
}
.step-icon-wrap i {
    font-size: 1.5rem;
    color: #542680;
    z-index: 1;
}
.step-num {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(84,38,128,0.3);
}
.step-item h6 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.4rem;
}
.step-item p {
    font-size: 0.82rem;
    color: rgba(33,28,49,0.55);
    margin: 0;
    line-height: 1.5;
}
</style>

<!-- CTA Recuperation -->
<section class="section-cta">
    <div class="container">
        <div class="cta-card">
            <div class="cta-content">
                <span class="cta-tag">Vous avez perdu votre ticket ?</span>
                <h3>Pas de panique !</h3>
                <p>Rendez-vous sur la page de récupération avec vos informations pour retrouver votre billet en un clin d'œil.</p>
                <a href="{{ route('tickets.recuperer') }}" class="btn-cta">
                    <i class="bi bi-ticket-perforated me-1"></i> Récupérer mon ticket
                </a>
            </div>
            <div class="cta-graphic">
                <i class="bi bi-ticket-detailed"></i>
            </div>
        </div>
    </div>
</section>

<style>
.section-cta {
    padding: 5rem 0;
    background: #fff;
}
.cta-card {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    background: linear-gradient(135deg, #fff 0%, #faf8fb 100%);
    border: 1px solid #ede5f0;
    padding: 3rem 3rem;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(84,38,128,0.06);
    position: relative;
    overflow: hidden;
}
.cta-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #542680, #9972B0);
}
.cta-content { flex: 1; }
.cta-graphic {
    flex-shrink: 0;
    font-size: 5rem;
    color: rgba(84,38,128,0.08);
    line-height: 1;
}
.cta-tag {
    display: inline-block;
    padding: 0.25rem 0.8rem;
    background: rgba(84,38,128,0.08);
    border-radius: 20px;
    color: #542680;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}
.cta-card h3 {
    font-size: 1.6rem;
    font-weight: 800;
    color: #211C31;
    margin: 0 0 0.5rem;
}
.cta-card p {
    font-size: 0.92rem;
    color: #6c757d;
    margin: 0 0 1.5rem;
    max-width: 400px;
}
.btn-cta {
    display: inline-flex;
    align-items: center;
    padding: 0.8rem 2rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 0.92rem;
    text-decoration: none;
    transition: all 0.3s;
    box-shadow: 0 4px 16px rgba(84,38,128,0.3);
}
.btn-cta:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(84,38,128,0.45); color: #fff; }
@media (max-width: 640px) {
    .cta-card { flex-direction: column; text-align: center; padding: 2rem 1.5rem; }
    .cta-card p { max-width: 100%; }
    .cta-graphic { display: none; }
}
</style>

<!-- Steps animation -->
<style>
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
                }, i * 200);
            });
            observer.disconnect();
        }
    }, { threshold: 0.2 });
    observer.observe(grid);
});
</script>
@endsection