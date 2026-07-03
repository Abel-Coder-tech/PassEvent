@extends('layouts.public')

@section('title', 'Tous les événements — PaxEvent')
@section('description', 'Découvrez tous les événements disponibles sur PaxEvent : concerts, conférences, spectacles et plus au Bénin.')
@section('og_title', 'Événements — PaxEvent')
@section('og_description', 'Trouvez et réservez vos billets pour les meilleurs événements au Bénin sur PaxEvent.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Événements</li>
@endsection

@section('content')
<!-- Hero -->
<section class="ev-hero">
    <div class="ev-hero-bg">
        <div class="ev-circle c1"></div>
        <div class="ev-circle c2"></div>
        <div class="ev-circle c3"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="ev-hero-chip">Tous les événements</span>
                <h1 class="ev-hero-title">Découvrez les événements près de chez vous</h1>
                <p class="ev-hero-sub" style="color:var(--accent);">Concerts, soirées, conférences &mdash; trouvez l'événement qui vous correspond</p>
                <form action="{{ route('evenements.public') }}" method="GET" class="ev-hero-search">
                    <div class="ev-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" name="q" class="ev-search-input" placeholder="Rechercher par nom, catégorie, lieu..." value="{{ $q ?? '' }}">
                    </div>
                    @if($selectedCategorie)<input type="hidden" name="categorie" value="{{ $selectedCategorie }}">@endif
                    @if($selectedDate)<input type="hidden" name="date" value="{{ $selectedDate }}">@endif
                    <button type="submit" class="ev-search-btn"><i class="bi bi-search"></i></button>
                    @if($q || $selectedCategorie || $selectedDate)
                        <a href="{{ route('evenements.public') }}" class="ev-search-clear"><i class="bi bi-x-lg"></i></a>
                    @endif
                </form>
                <div class="ev-hero-stats">
                    <span><strong>{{ $evenements->total() }}</strong> événements</span>
                    <span><strong>100%</strong> paiement sécurisé</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== Grille des événements ===== -->
<section class="ev-grid-section">
    <div class="container">
        @if($evenements->count() > 0)
            <div class="ev-grid">
                @foreach($evenements as $evenement)
                    @php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
                        $prixDernier = $evenement->tarifs->min('prix');
                        $venteCloturee = $evenement->date_fin_vente && $evenement->date_fin_vente->isPast();
                    @endphp
                    <div class="ev-grid-col">
                        <a href="{{ route('evenements.public.show', $evenement->id) }}" class="ev-card">
                            <div class="ev-card-img">
                                @if($evenement->image)
                                    <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}">
                                @else
                                    <div class="ev-card-placeholder"><i class="bi bi-calendar-event"></i></div>
                                @endif
                                @if($evenement->categorie)
                                    <span class="ev-card-badge">{{ ucfirst($evenement->categorie) }}</span>
                                @endif
                                @if($venteCloturee)
                                    <span class="ev-card-badge" style="background: rgba(231,76,60,0.9); color: #fff;">Vente cloturée</span>
                                @elseif($estComplet)
                                    <span class="ev-card-badge ev-card-badge-complet">Complet</span>
                                @endif
                                <div class="ev-card-overlay">
                                    <span class="ev-card-overlay-btn">Voir les détails <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </div>
                            <div class="ev-card-body">
                                <h5 class="ev-card-title">{{ $evenement->titre }}</h5>
                                <p class="ev-card-meta">
                                    <i class="bi bi-calendar3"></i> {{ $evenement->date_event->isoFormat('D MMM YYYY') }}<br>
                                    <i class="bi bi-geo-alt"></i> {{ $evenement->lieu }}
                                </p>
                                @if($evenement->gratuit)
                                    <div class="ev-card-price">Entrée <strong>Gratuit</strong></div>
                                @elseif($prixDernier)
                                    <div class="ev-card-price">À partir de <strong>{{ number_format($prixDernier, 0, ',', ' ') }} F</strong></div>
                                @endif
                                <div class="ev-card-gauge">
                                    <div class="d-flex justify-content-between" style="font-size:0.7rem;">
                                        <span>{{ $placesRestantes }} places</span>
                                        <span>{{ $remplissage }}%</span>
                                    </div>
                                    <div class="gauge"><div class="gauge-fill" style="width:{{ min($remplissage,100) }}%"></div></div>
                                </div>
                                @if($venteCloturee)
                                    <span class="ev-card-btn disabled"><i class="bi bi-lock me-1"></i> Vente clôturée</span>
                                @elseif($estComplet)
                                    <span class="ev-card-btn disabled"><i class="bi bi-slash-circle me-1"></i> Complet</span>
                                @else
                                    <span class="ev-card-btn">
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

            @if($evenements->hasPages())
                <div class="pagination-wrap"> {{ $evenements->links() }} </div>
            @endif
        @else
            <div class="ev-empty">
                <i class="bi bi-calendar-x"></i>
                <h5>Aucun événement trouvé</h5>
                <p>
                    @if($q || $selectedCategorie || $selectedDate)
                        Essayez une autre catégorie ou un autre terme de recherche.
                    @else
                        Revenez plus tard pour découvrir nos prochains événements.
                    @endif
                </p>
                @if($q || $selectedCategorie || $selectedDate)
                    <a href="{{ route('evenements.public') }}" class="btn-outline"><i class="bi bi-arrow-left me-1"></i> Voir tous</a>
                @endif
            </div>
        @endif
    </div>
</section>

<!-- CTA Devenir organisateur -->
<section class="ev-cta">
    <div class="container">
        <div class="ev-cta-card">
            <div class="ev-cta-content">
                <span class="ev-cta-chip">Organisateur</span>
                <h2 class="ev-cta-title">Envie de devenir organisateur ?</h2>
                <p class="ev-cta-text">Créez vos événements, vendez vos billets en ligne et gérez votre billetterie en toute simplicité.</p>
                <div class="ev-cta-actions">
                    <a href="{{ route('inscriptions.organisateur') }}" class="ev-cta-btn-primary">
                        Créer un compte gratuit <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="{{ route('login') }}" class="ev-cta-btn-outline">
                        Déjà inscrit ? Se connecter
                    </a>
                </div>
            </div>
            <div class="ev-cta-icon">
                <i class="bi bi-megaphone"></i>
            </div>
        </div>
    </div>
</section>

<style>
/* ===== HERO ===== */
.ev-hero {
    padding: 4rem 0 3rem;
    background: linear-gradient(160deg, #211C31 0%, #211C31 50%, #211C31 100%);
    position: relative;
    overflow: hidden;
}
.ev-hero-bg {
    position: absolute; inset: 0;
    pointer-events: none;
}
.ev-circle {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
}
.ev-circle.c1 { width: 450px; height: 450px; background: #542680; top: -200px; right: -100px; opacity: 0.18; }
.ev-circle.c2 { width: 300px; height: 300px; background: #FED514; bottom: -120px; left: -80px; opacity: 0.12; }
.ev-circle.c3 { width: 180px; height: 180px; background: #9972B0; top: 30%; left: 50%; opacity: 0.06; }

.ev-hero-chip {
    display: inline-flex;
    padding: 0.35rem 1rem;
    background: rgba(178,224,214,0.12);
    border: 1px solid rgba(178,224,214,0.2);
    border-radius: 20px;
    color: #9972B0;
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.ev-hero-title {
    font-size: 2.6rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.75rem;
    line-height: 1.15;
}
.ev-hero-sub {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.6);
    margin: 0 0 1.5rem;
}

.ev-hero-search {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 580px;
    margin: 0 auto 1.5rem;
}
.ev-search-wrap {
    flex: 1;
    position: relative;
}
.ev-search-wrap .bi-search {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.4);
}
.ev-search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.6rem;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    font-size: 0.9rem;
    background: rgba(255,255,255,0.06);
    color: #fff;
    outline: none;
    transition: all 0.25s;
}
.ev-search-input::placeholder { color: rgba(255,255,255,0.35); }
.ev-search-input:focus {
    border-color: rgba(178,224,214,0.3);
    background: rgba(255,255,255,0.08);
    box-shadow: 0 0 0 3px rgba(178,224,214,0.06);
}
.ev-search-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-size: 1rem;
    transition: all 0.25s;
}
.ev-search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(123,63,160,0.4);
}
.ev-search-clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    transition: all 0.2s;
}
.ev-search-clear:hover { border-color: #dc3545; color: #dc3545; }

.ev-hero-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.82rem;
}
.ev-hero-stats strong {
    color: #9972B0;
    font-weight: 700;
}

/* ===== Grille ===== */
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
    background: #e8e8e8;
    color: #999;
    font-size: 3rem;
}
.ev-card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}
.ev-card:hover .ev-card-overlay { opacity: 1; }
.ev-card-overlay-btn {
    color: #fff;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.5rem 1.2rem;
    border: 2px solid rgba(255,255,255,0.6);
    border-radius: 8px;
    backdrop-filter: blur(4px);
    transition: all 0.2s;
}
.ev-card-overlay-btn:hover { border-color: #fff; }
.ev-card-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255,255,255,0.92);
    color: #542680;
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
    color: #542680;
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
    background: linear-gradient(90deg, #542680, #9972B0);
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
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    transition: all 0.2s;
    margin-top: auto;
}
.ev-card-btn:hover { transform: scale(1.02); }
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
    border: 2px solid #542680;
    border-radius: 10px;
    color: #542680;
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
    color: #211C31;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    background: #fff;
}
.pagination-wrap .page-link:hover { border-color: #542680; color: #542680; }
.pagination-wrap .active .page-link { background: #542680; border-color: #542680; color: #fff; }
.pagination-wrap .disabled .page-link { opacity: 0.4; pointer-events: none; }

/* ===== CTA Organisateur ===== */
.ev-cta {
    padding: 4rem 0;
    background: #f7f5f3;
}
.ev-cta-card {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    background: linear-gradient(135deg, #fff 0%, #faf8fb 100%);
    border: 1px solid #ede5f0;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(123,63,160,0.06);
    position: relative;
    overflow: hidden;
}
.ev-cta-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #542680, #9972B0, #542680);
}
.ev-cta-content {
    flex: 1;
    position: relative;
    z-index: 1;
}
.ev-cta-chip {
    display: inline-block;
    padding: 0.25rem 0.9rem;
    background: linear-gradient(135deg, rgba(123,63,160,0.1), rgba(123,63,160,0.05));
    border: 1px solid rgba(123,63,160,0.15);
    border-radius: 20px;
    color: #542680;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}
.ev-cta-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #211C31;
    margin: 0 0 0.5rem;
}
.ev-cta-text {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 0 0 1.5rem;
    max-width: 480px;
}
.ev-cta-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.ev-cta-btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.8rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(123,63,160,0.3);
    transition: all 0.25s;
}
.ev-cta-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(123,63,160,0.4);
    color: #fff;
}
.ev-cta-btn-outline {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    border: 2px solid rgba(109,53,112,0.3);
    color: #542680;
    font-weight: 600;
    font-size: 0.88rem;
    text-decoration: none;
    transition: all 0.2s;
}
.ev-cta-btn-outline:hover {
    background: rgba(109,53,112,0.06);
    border-color: #542680;
}
.ev-cta-icon {
    font-size: 5rem;
    color: rgba(123,63,160,0.08);
    flex-shrink: 0;
}

@media (max-width: 767px) {
    .ev-hero-title { font-size: 1.8rem; }
    .ev-cta-card { flex-direction: column; text-align: center; padding: 2rem 1.5rem; }
    .ev-cta-text { max-width: 100%; }
    .ev-cta-actions { justify-content: center; }
    .ev-cta-icon { display: none; }
}
</style>
@endsection
