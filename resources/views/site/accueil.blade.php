@extends('layouts.public')

@section('title', 'PassEvent - Billetterie intelligente au Benin')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
@endsection

@section('content')
<!-- Hero Section avec slider -->
<section class="hero-slider" id="heroSlider">
    <div class="hero-slides">
        @php
            $slides = [
                asset('images/image1.jpg'),
                asset('images/image2.jpg'),
                asset('images/image3.jpg'),
                asset('images/image4.jpg'),
            ];
        @endphp

        @foreach($slides as $index => $slide)
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" style="background: url('{{ $slide }}') center/cover no-repeat;">
                <div class="hero-overlay"></div>
            </div>
        @endforeach
    </div>

    <div class="hero-content">
        <div class="container position-relative" style="z-index: 2;">
            <div class="text-center">
                <h1 class="display-5 fw-bold mb-3 hero-title">Decouvrez et vivez vos evenements</h1>
                <p class="lead mb-4 hero-subtitle">Achetez vos billets en quelques clics et recevez votre billet PDF par email</p>

                <div class="row justify-content-center mb-3">
                    <div class="col-md-6 col-lg-5">
                        <form action="{{ route('evenements.public') }}" method="GET" class="d-flex gap-2 hero-form">
                            <input type="text" name="q" class="form-control py-3 border-0" placeholder="Rechercher un evenement..." style="border-radius: 8px;">
                            <button type="submit" class="btn btn-vert px-4" style="border-radius: 8px; white-space: nowrap;">
                                <i class="bi bi-search me-1"></i> Chercher
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('evenements.public') }}" class="btn btn-light btn-lg px-4 hero-btn" style="border-radius: 8px; font-weight: 600;">
                    Voir tous les evenements <i class="bi bi-arrow-right ms-1"></i>
                </a>

                <div class="hero-indicators mt-4">
                    @foreach($slides as $index => $slide)
                        <span class="hero-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .hero-slider {
        position: relative;
        min-height: 520px;
        overflow: hidden;
    }

    .hero-slides {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1.2s ease-in-out;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .hero-slide.active { opacity: 1; }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
    }

    .hero-title {
        color: #fff;
        text-shadow: 0 2px 12px rgba(0,0,0,0.6), 0 1px 3px rgba(0,0,0,0.8);
    }

    .hero-subtitle {
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,0.5), 0 1px 2px rgba(0,0,0,0.7);
    }

    .hero-form .form-control {
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    }

    .hero-form .btn-vert {
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    }

    .hero-btn {
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    }

    .hero-content {
        position: relative;
        z-index: 1;
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 0;
    }

    .hero-indicators {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .hero-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255,255,255,0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .hero-dot.active {
        background: #fff;
        width: 28px;
        border-radius: 5px;
    }

    @media (max-width: 767.98px) {
        .hero-slider, .hero-content {
            min-height: 420px;
            padding: 2rem 0;
        }

        .hero-slider h1 { font-size: 1.6rem !important; }
        .hero-slider .lead { font-size: 0.95rem !important; }
    }

    /* Filter tabs */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 1rem;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        border: 1.5px solid #e5e5e5;
        background: var(--blanc);
        color: var(--sombre);
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
    }

    .filter-chip:hover {
        border-color: var(--violet);
        color: var(--violet);
    }

    .filter-chip.active {
        background: var(--violet);
        border-color: var(--violet);
        color: #fff;
    }

    .filter-chip.active:hover {
        background: var(--violet-dark);
        color: #fff;
    }

    .filter-group-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gris);
        margin-bottom: 0.5rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let current = 0;
    const interval = 5000;

    function goToSlide(index) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = index;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }

    function nextSlide() {
        goToSlide((current + 1) % slides.length);
    }

    let timer = setInterval(nextSlide, interval);

    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            clearInterval(timer);
            goToSlide(parseInt(this.dataset.slide));
            timer = setInterval(nextSlide, interval);
        });
    });
});
</script>

<!-- Evenements a venir -->
<section class="py-5" id="section-evenements">
    <div class="container">
        <h3 class="fw-bold mb-1">Evenements a venir</h3>
        <p class="text-muted mb-4" style="font-size: 0.9rem;">Trouvez l'evenement parfait pour vous</p>

        <!-- Filters -->
        <div class="panel-card mb-4" style="border-radius: 12px;">
            <div class="panel-card-body p-3">
                <!-- Search bar -->
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-md-8">
                        <form action="{{ route('accueil') }}" method="GET" class="d-flex gap-2">
                            <div class="position-relative flex-grow-1">
                                <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris);"></i>
                                <input type="text" name="q" class="form-control ps-5" placeholder="Rechercher par nom, categorie, lieu..." value="{{ $q ?? '' }}">
                            </div>
                            @if($selectedCategorie)
                                <input type="hidden" name="categorie" value="{{ $selectedCategorie }}">
                            @endif
                            @if($selectedDate)
                                <input type="hidden" name="date" value="{{ $selectedDate }}">
                            @endif
                            <button type="submit" class="btn btn-vert px-3" style="border-radius: 8px;">
                                <i class="bi bi-search"></i>
                            </button>
                            @if($q || $selectedCategorie || $selectedDate)
                                <a href="{{ route('accueil') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2 align-items-center">
                            <span class="text-muted" style="font-size: 0.82rem; font-weight: 600;"><i class="bi bi-funnel me-1"></i> Date :</span>
                            <form action="{{ route('accueil') }}" method="GET" class="d-flex gap-1">
                                @if($q)
                                    <input type="hidden" name="q" value="{{ $q }}">
                                @endif
                                @if($selectedCategorie)
                                    <input type="hidden" name="categorie" value="{{ $selectedCategorie }}">
                                @endif
                                <select name="date" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.82rem;" onchange="this.form.submit()">
                                    <option value="">Toutes dates</option>
                                    <option value="weekend" {{ ($selectedDate ?? '') == 'weekend' ? 'selected' : '' }}>Ce weekend</option>
                                    <option value="mois" {{ ($selectedDate ?? '') == 'mois' ? 'selected' : '' }}>Ce mois</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Category chips -->
                <div class="filter-group-label">Categories</div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('accueil') }}{{ $q ? '?q=' . $q : '' }}{{ $selectedDate ? ($q ? '&date=' . $selectedDate : '?date=' . $selectedDate) : '' }}"
                       class="filter-chip {{ !$selectedCategorie ? 'active' : '' }}">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Toutes les categories
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('accueil') }}?categorie={{ $cat }}{{ $q ? '&q=' . $q : '' }}{{ $selectedDate ? '&date=' . $selectedDate : '' }}"
                           class="filter-chip {{ $selectedCategorie == $cat ? 'active' : '' }}">
                            {{ ucfirst($cat) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Events grid -->
        @if($evenementsVedettes->isNotEmpty())
            <div class="row g-3 g-md-4">
                @foreach($evenementsVedettes as $evenement)
                    @php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;

                        $prixEtudiant = $evenement->tarifs->where('categorie', 'etudiant')->min('prix');
                        $prixExterne = $evenement->tarifs->where('categorie', 'externe')->min('prix');
                        $prixDernier = $evenement->tarifs->min('prix');

                        $gaugeClass = $remplissage >= 100 ? 'gauge-full' : ($remplissage >= 75 ? 'gauge-high' : ($remplissage >= 40 ? 'gauge-mid' : 'gauge-low'));
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('evenements.public.show', $evenement->id) }}" class="event-card-link h-100 text-decoration-none">
                            <div class="event-card h-100 {{ $estComplet ? 'opacity-75' : '' }}">
                                <!-- Photo -->
                                <div class="position-relative">
                                    @if($evenement->image)
                                        <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}">
                                    @else
                                        <div style="height:140px; background: linear-gradient(135deg, var(--violet), var(--violet-dark)); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-calendar-event text-white" style="font-size: 2.5rem;"></i>
                                        </div>
                                    @endif
                                    @if($evenement->categorie)
                                        <span class="position-absolute top-0 start-0 m-2 badge" style="background: rgba(255,255,255,0.92); color: var(--violet); font-size: 0.65rem; font-weight: 700; border-radius: 10px; backdrop-filter: blur(4px);">
                                            {{ ucfirst($evenement->categorie) }}
                                        </span>
                                    @endif
                                    @if($estComplet)
                                        <span class="position-absolute top-0 end-0 m-2 badge-complet">Complet</span>
                                    @endif
                                </div>

                                <div class="p-3 d-flex flex-column">
                                    <!-- Title -->
                                    <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.88rem; line-height: 1.3;">{{ $evenement->titre }}</h6>

                                    <!-- Date + Lieu -->
                                    <p class="text-muted mb-2" style="font-size: 0.78rem; line-height: 1.3;">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $evenement->date_event->format('d M Y') }}<br>
                                        <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($evenement->lieu, 25) }}
                                    </p>

                                    <!-- Price from -->
                                    @if($prixDernier)
                                        <div class="mb-2" style="font-size: 0.8rem;">
                                            <span class="text-muted">A partir de</span>
                                            <strong style="color: var(--vert); font-size: 1rem;">{{ number_format($prixDernier, 0, ',', ' ') }} F</strong>
                                        </div>
                                    @else
                                        <div class="mb-2" style="font-size: 0.8rem;">
                                            <span class="text-muted">Tarifs non configurés</span>
                                        </div>
                                    @endif

                                    <!-- Gauge -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between" style="font-size: 0.68rem;">
                                            <span class="text-muted">{{ $placesRestantes }} places</span>
                                            <span class="text-muted">{{ $remplissage }}%</span>
                                        </div>
                                        <div class="gauge-bar mt-1" style="background: #e0e0e0;">
                                            <div class="gauge-fill" style="width: {{ min($remplissage, 100) }}%; background: #000;"></div>
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    @if($estComplet)
                                        <span class="btn btn-sm w-100 disabled" style="border-radius: 6px; font-size: 0.78rem; background: #98919b; border-color: #98919b; color: #fff;">
                                            <i class="bi bi-slash-circle me-1"></i> Complet
                                        </span>
                                    @else
                                        <span class="btn btn-sm w-100" style="border-radius: 6px; font-size: 0.78rem; background: var(--vert); border-color: var(--vert); color: #fff; pointer-events: none;">
                                            <i class="bi bi-ticket-perforated me-1"></i> Acheter ticket
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                <h5 class="text-muted">Aucun evenement disponible</h5>
                <p class="text-muted">Revenez plus tard pour decouvrir nos prochains evenements.</p>
                @if($q || $selectedCategorie || $selectedDate)
                    <a href="{{ route('accueil') }}" class="btn btn-violet btn-sm" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left me-1"></i> Voir tous les evenements
                    </a>
                @endif
            </div>
        @endif

        @if($evenementsVedettes->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ route('evenements.public') }}" class="btn btn-outline-violet btn-sm" style="border-radius: 8px;">
                    Voir tous les evenements <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        @endif
    </div>
</section>

<!-- Comment ca marche -->
<section class="py-5" style="background: var(--blanc);">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-1">Comment ca marche ?</h3>
            <p class="text-muted" style="font-size: 0.95rem;">4 etapes simples pour obtenir votre billet</p>
        </div>

        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="step-card">
                    <div class="step-icon"><i class="bi bi-calendar-check"></i></div>
                    <h6 class="fw-bold mb-1">Choisir</h6>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">Trouvez l'evenement qui vous interesse</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="step-card">
                    <div class="step-icon"><i class="bi bi-pencil-square"></i></div>
                    <h6 class="fw-bold mb-1">Remplir</h6>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">Entrez vos informations et choisissez votre tarif</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="step-card">
                    <div class="step-icon"><i class="bi bi-credit-card"></i></div>
                    <h6 class="fw-bold mb-1">Payer</h6>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">Payez securise via KKiaPay (MTN, Moov, Celtiis)</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="step-card">
                    <div class="step-icon"><i class="bi bi-whatsapp"></i></div>
                    <h6 class="fw-bold mb-1">Recevoir</h6>
                    <p class="text-muted mb-0" style="font-size: 0.82rem;">Recevez votre billet PDF par email</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="py-5" style="background: linear-gradient(135deg, var(--violet) 0%, var(--violet-dark) 100%); color: #fff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h3 class="fw-bold mb-2">Vous avez perdu votre ticket ?</h3>
                <p class="mb-4" style="opacity: 0.9;">Rendez-vous sur la page de recuperation avec vos informations</p>
                <a href="{{ route('tickets.recuperer') }}" class="btn btn-light btn-lg px-5" style="border-radius: 8px; font-weight: 600;">
                    <i class="bi bi-ticket-perforated me-1"></i> Recuperer mon ticket
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
