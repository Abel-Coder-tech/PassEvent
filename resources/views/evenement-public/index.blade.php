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
                <h1 class="ev-hero-title">Retrouvez vos évènements préférés</h1>
                <form action="{{ route('evenements.public') }}" method="GET" class="ev-hero-search" id="evSearchForm">
                    <div class="ev-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" id="evSearchInput" class="ev-search-input" placeholder="Rechercher un événement…" aria-label="Rechercher un événement par nom, catégorie ou lieu" value="{{ $q ?? '' }}" autocomplete="off" @if($q) autofocus @endif>
                        <button type="submit" class="ev-search-go">Rechercher</button>
                    </div>
                    @if($selectedCategorie)<input type="hidden" name="categorie" value="{{ $selectedCategorie }}">@endif
                </form>
                <script>
                    // Recherche automatique : envoie le formulaire après une courte pause de saisie.
                    (function () {
                        const input = document.getElementById('evSearchInput');
                        if (!input) return;
                        const initiale = input.value;
                        let minuterie;
                        input.addEventListener('input', function () {
                            clearTimeout(minuterie);
                            minuterie = setTimeout(function () {
                                const valeur = input.value.trim();
                                if (valeur === initiale.trim() || (valeur.length > 0 && valeur.length < 2)) return;
                                input.form.submit();
                            }, 500);
                        });
                        // Garde le curseur en fin de texte après rechargement.
                        if (document.activeElement === input) {
                            const n = input.value.length;
                            input.setSelectionRange(n, n);
                        }
                    })();
                </script>
                {{-- Catégories qui défilent horizontalement --}}
                @if($categories->isNotEmpty())
                    <nav class="ev-cats-defile" aria-label="Catégories">
                        <a href="{{ route('evenements.public', array_filter(['q' => $q])) }}" class="ev-cat-pill {{ $selectedCategorie ? '' : 'active' }}">Toutes</a>
                        @foreach($categories as $cat)
                            <a href="{{ route('evenements.public', array_filter(['categorie' => $cat, 'q' => $q])) }}" class="ev-cat-pill {{ $selectedCategorie === $cat ? 'active' : '' }}">{{ $cat }}</a>
                        @endforeach
                    </nav>
                @endif
                <div class="ev-hero-stats">
                    <span><strong>{{ $evenements->total() }}</strong> événement{{ $evenements->total() > 1 ? 's' : '' }}</span>
                    <span><strong>100%</strong> paiement sécurisé</span>
                    @if($q || $selectedCategorie || $selectedDate)
                        <a href="{{ route('evenements.public') }}" class="ev-effacer"><i class="bi bi-x-lg me-1"></i>Effacer les filtres</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== Grille des événements ===== -->
<section class="ev-grid-section">
    <div class="container">
        @if($evenements->count() > 0)
            {{-- Affichage en cartes ou en liste (choix mémorisé dans le navigateur) --}}
            <div class="ev-vue-barre">
                <span class="ev-vue-compte">{{ $evenements->total() }} événement{{ $evenements->total() > 1 ? 's' : '' }}</span>
                <div class="ev-vue" role="group" aria-label="Affichage">
                    <button type="button" class="ev-vue-btn active" data-vue="cartes" aria-pressed="true" title="Afficher en cartes"><i class="bi bi-grid-3x2-gap"></i></button>
                    <button type="button" class="ev-vue-btn" data-vue="liste" aria-pressed="false" title="Afficher en liste"><i class="bi bi-list-ul"></i></button>
                </div>
            </div>
            <div class="ev-grid" id="evGrille">
                @foreach($evenements as $evenement)
                    @php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = (int) round($evenement->tauxRemplissage());
                        $prixDernier = $evenement->prixAPartirDe();
                        $venteCloturee = $evenement->etatVitrine() === 'cloturee';
                        $estPasse = $evenement->evenementPasse(); // 12 h après le début de la dernière session
                        $textes = $evenement->getTextes();
                    @endphp
                    <div class="ev-grid-col">
                        <a href="{{ route('evenements.public.show', $evenement) }}" class="ev-card">
                            <div class="ev-card-img">
                                @if($evenement->image)
                                    <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}">
                                @else
                                    <div class="ev-card-placeholder"><i class="bi bi-calendar-event"></i></div>
                                @endif
                                @if($evenement->categorie)
                                    <span class="ev-card-badge">{{ ucfirst($evenement->categorie) }}</span>
                                @endif
                                @if($estPasse)
                                    <span class="ev-card-badge" style="right:10px;left:auto;background:#6c757d;color:#fff;">Terminé</span>
                                @elseif($evenement->etatVitrine() === 'bientot')
                                    <span class="ev-card-badge" style="background: rgba(107,100,116,0.92); color: #fff;">Billetterie bientôt ouverte</span>
                                @elseif($venteCloturee)
                                    <span class="ev-card-badge" style="background: rgba(231,76,60,0.9); color: #fff;">{{ $textes['cloturee'] }}</span>
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
                                @if($evenement->etatVitrine() === 'bientot')
                                    {{-- Billetterie pas encore ouverte : pas de prix affiché --}}
                                @elseif($evenement->gratuit)
                                    <div class="ev-card-price">Entrée <strong>Gratuit</strong></div>
                                @elseif($prixDernier)
                                    <div class="ev-card-price">À partir de <strong>{{ number_format($prixDernier, 0, ',', ' ') }} F</strong></div>
                                @elseif($prixDernier !== null)
                                    <div class="ev-card-price">Entrée <strong>Gratuit</strong></div>
                                @endif
                                @if($estPasse)
                                    <div class="ev-card-past"><i class="bi bi-clock-history me-1"></i> Événement passé</div>
                                @else
                                <div class="ev-card-gauge">
                                    <div class="d-flex justify-content-between" style="font-size:0.7rem;">
                                        <span>{{ $placesRestantes }} {{ $textes['places'] }}</span>
                                        <span>{{ $remplissage }}%</span>
                                    </div>
                                    <div class="gauge"><div class="gauge-fill" style="width:{{ min($remplissage,100) }}%"></div></div>
                                </div>
                                @endif
                                @if($estPasse)
                                    <span class="ev-card-btn disabled"><i class="bi bi-clock-history me-1"></i> Événement passé</span>
                                @elseif($evenement->etatVitrine() === 'bientot')
                                    <span class="ev-card-btn disabled"><i class="bi bi-clock me-1"></i> Billetterie bientôt ouverte</span>
                                @elseif($venteCloturee)
                                    <span class="ev-card-btn disabled"><i class="bi bi-lock me-1"></i> {{ $textes['cloturee'] }}</span>
                                @elseif($estComplet)
                                    <span class="ev-card-btn disabled"><i class="bi bi-slash-circle me-1"></i> Complet</span>
                                @else
                                    <span class="ev-card-btn">
                                        @if($evenement->gratuit)
                                            <i class="bi bi-check-circle me-1"></i> {{ $textes['participer_gratuit'] }}
                                        @else
                                            <i class="bi bi-ticket-perforated me-1"></i> {{ $textes['acheter'] }}
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
                <div class="ev-empty-icone"><i class="bi bi-calendar2-x"></i></div>
                <h2 class="ev-empty-titre">Aucun événement trouvé</h2>
                @if($q || $selectedCategorie || $selectedDate)
                    <p class="ev-empty-texte">Rien ne correspond à ces filtres pour le moment.</p>
                    <div class="ev-empty-filtres">
                        @if($q)
                            <a href="{{ route('evenements.public', array_filter(['categorie' => $selectedCategorie, 'date' => $selectedDate])) }}" class="ev-empty-filtre">« {{ $q }} » <i class="bi bi-x"></i></a>
                        @endif
                        @if($selectedCategorie)
                            <a href="{{ route('evenements.public', array_filter(['q' => $q, 'date' => $selectedDate])) }}" class="ev-empty-filtre">{{ ucfirst($selectedCategorie) }} <i class="bi bi-x"></i></a>
                        @endif
                        @if($selectedDate)
                            <a href="{{ route('evenements.public', array_filter(['q' => $q, 'categorie' => $selectedCategorie])) }}" class="ev-empty-filtre">{{ ['aujourdhui' => "Aujourd'hui", 'weekend' => 'Week-end', 'mois' => 'Ce mois'][$selectedDate] ?? $selectedDate }} <i class="bi bi-x"></i></a>
                        @endif
                    </div>
                    <a href="{{ route('evenements.public') }}" class="ev-empty-action">Voir tous les événements</a>
                @else
                    <p class="ev-empty-texte">Aucun événement n'est publié pour l'instant. Revenez bientôt pour découvrir les prochains.</p>
                @endif
            </div>
        @endif
    </div>
</section>

<!-- ===== Événements passés ===== -->
@if($evenementsPasses->isNotEmpty())
<section class="ev-past-section">
    <div class="container">
        <div class="ev-section-head">
            <span class="ev-section-chip"><i class="bi bi-clock-history me-1"></i> Ils ont déjà eu lieu</span>
            <h2 class="ev-section-title">Événements passés</h2>
            <p class="ev-section-sub">Revivez les derniers événements organisés sur PaxEvent.</p>
        </div>
        <div class="ev-grid">
            @foreach($evenementsPasses as $evenement)
                @php
                    $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                    $estComplet = $placesRestantes <= 0;
                    $remplissage = (int) round($evenement->tauxRemplissage());
                    $prixDernier = $evenement->prixAPartirDe();
                    $venteCloturee = $evenement->etatVitrine() === 'cloturee';
                    $estPasse = $evenement->evenementPasse();
                    $textes = $evenement->getTextes();
                @endphp
                <div class="ev-grid-col">
                    <a href="{{ route('evenements.public.show', $evenement) }}" class="ev-card">
                        <div class="ev-card-img">
                            @if($evenement->image)
                                <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}">
                            @else
                                <div class="ev-card-placeholder"><i class="bi bi-calendar-event"></i></div>
                            @endif
                            @if($evenement->categorie)
                                <span class="ev-card-badge">{{ ucfirst($evenement->categorie) }}</span>
                            @endif
                            <span class="ev-card-badge" style="right:10px;left:auto;background:#6c757d;color:#fff;">Terminé</span>
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
                            @if($evenement->etatVitrine() === 'bientot')
                                {{-- Billetterie pas encore ouverte : pas de prix affiché --}}
                            @elseif($evenement->gratuit)
                                <div class="ev-card-price">Entrée <strong>Gratuit</strong></div>
                            @elseif($prixDernier)
                                <div class="ev-card-price">À partir de <strong>{{ number_format($prixDernier, 0, ',', ' ') }} F</strong></div>
                            @elseif($prixDernier !== null)
                                <div class="ev-card-price">Entrée <strong>Gratuit</strong></div>
                            @endif
                            <div class="ev-card-past"><i class="bi bi-clock-history me-1"></i> Événement passé</div>
                            <span class="ev-card-btn disabled"><i class="bi bi-clock-history me-1"></i> Événement passé</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

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
    flex-wrap: wrap;
    justify-content: center;
    max-width: 680px;
    margin: 0 auto 1.5rem;
}
.ev-search-wrap {
    flex: 1 1 220px;
    max-width: 300px;
    position: relative;
}
.ev-search-input::-webkit-search-cancel-button { display: none; }
.ev-date-chips {
    display: flex;
    gap: 0.4rem;
}
.ev-date-chip {
    display: inline-flex;
    align-items: center;
    height: 44px;
    padding: 0 0.9rem;
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 12px;
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.7);
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.2s;
}
.ev-date-chip:hover { background: rgba(255,255,255,0.12); color: #fff; }
.ev-date-chip.active {
    background: linear-gradient(135deg, #542680, #9972B0);
    border-color: transparent;
    color: #fff;
}
@media (max-width: 575.98px) {
    .ev-search-wrap { max-width: none; flex-basis: 100%; }
    .ev-date-chip { height: 36px; padding: 0 0.75rem; }
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

.ev-cat-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin: 0 auto 1.5rem;
    max-width: 720px;
}
.ev-cat-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 1.1rem;
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 20px;
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.65);
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.ev-cat-chip:hover {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border-color: rgba(255,255,255,0.3);
    transform: translateY(-1px);
}
.ev-cat-chip.active {
    background: linear-gradient(135deg, #542680, #9972B0);
    border-color: #9972B0;
    color: #fff;
    box-shadow: 0 2px 10px rgba(123,63,160,0.4);
}

.ev-hero-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.82rem;
}
.ev-hero-stats strong {
    color: var(--accent, #FED514);
    font-weight: 700;
}

/* Piste 2 : recherche, onglets de dates, liste de catégories */
/* Le menu des catégories doit pouvoir déborder du hero, par-dessus la liste :
   on coupe seulement les halos décoratifs, et le hero passe au-dessus. */
.ev-hero { overflow: visible; z-index: 5; }
.ev-hero-bg { overflow: hidden; }
.ev-hero .ev-search-wrap { max-width: 560px; flex-basis: 100%; }
.ev-hero .ev-hero-search { margin: 1.75rem 0 1.75rem; }
/* Barre de recherche à bordure jaune, avec bouton Rechercher jaune intégré à droite */
.ev-hero .ev-search-input {
    border-color: var(--accent, #FED514);
    padding-right: 7.2rem;
}
/* Écran étroit : « Rechercher un év… » plutôt qu'un texte coupé net */
.ev-search-input { text-overflow: ellipsis; }
.ev-search-input::placeholder { text-overflow: ellipsis; white-space: nowrap; overflow: hidden; }
.ev-hero .ev-search-input:focus {
    border-color: var(--accent, #FED514);
    box-shadow: 0 0 0 3px rgba(254,213,20,0.18);
}
.ev-hero .ev-search-wrap > .bi-search { color: var(--accent, #FED514); }
.ev-search-go {
    position: absolute;
    top: 50%;
    right: 5px;
    transform: translateY(-50%);
    padding: 0 .9rem;
    height: 34px;
    font-size: .8rem;
    font-weight: 700;
    border: 0;
    border-radius: 9px;
    background: var(--accent, #FED514);
    color: #211C31;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: filter .2s;
}
.ev-search-go:hover { filter: brightness(0.95); }
@media (max-width: 575.98px) {
    .ev-hero .ev-search-wrap { max-width: 82%; margin: 0 auto; }
    .ev-hero .ev-search-input { padding: .6rem 5rem .6rem 2rem; font-size: .78rem; border-radius: 9px; }
    .ev-hero .ev-search-wrap > .bi-search { left: 10px; font-size: .78rem; }
    .ev-search-go { height: 30px; padding: 0 .55rem; font-size: .68rem; border-radius: 6px; right: 3px; }
}
.ev-search-wrap .ev-search-go .bi-search { position: static; transform: none; color: inherit; }
.ev-search-go:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
.ev-cats-defile {
    display: flex; gap: .5rem; overflow-x: auto; 
    -webkit-overflow-scrolling: touch; scrollbar-width: none; padding: .25rem 0 .5rem; margin-bottom: 1rem;
}
.ev-cats-defile > :first-child { margin-left: auto; }
.ev-cats-defile > :last-child { margin-right: auto; }
.ev-cats-defile::-webkit-scrollbar { display: none; }
@media (min-width: 768px) {
    .ev-cats-defile { flex-wrap: nowrap; justify-content: center; overflow: visible; gap: .35rem; }
    .ev-cat-pill { padding: .35rem .75rem; font-size: .8rem; }
    .ev-cats-defile > :first-child, .ev-cats-defile > :last-child { margin: 0; }
}
.ev-cat-pill {
    flex: 0 0 auto; scroll-snap-align: start; padding: .4rem 1rem; border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.3); color: rgba(255,255,255,0.85);
    font-size: .85rem; text-decoration: none; white-space: nowrap; transition: background .2s, color .2s;
}
.ev-cat-pill:hover { color: #fff; border-color: #fff; }
.ev-cat-pill.active { background: #fff; color: #542680; font-weight: 600; border-color: #fff; }
.ev-segments {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    max-width: 560px;
    margin: 0 auto .6rem;
    padding: 4px;
    border-radius: 12px;
    background: rgba(255,255,255,0.07);
}
.ev-segment {
    padding: .55rem .25rem;
    border-radius: 9px;
    color: rgba(255,255,255,0.72);
    font-size: .8rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    white-space: nowrap;
    transition: background .2s, color .2s;
}
.ev-segment:hover { color: #fff; }
.ev-segment.active { background: #fff; color: #542680; }
.ev-categorie { position: relative; max-width: 560px; margin: 0 auto 1.1rem; text-align: left; }
.ev-categorie-btn {
    width: 100%;
    height: 44px;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 12px;
    background: rgba(255,255,255,0.04);
    color: rgba(255,255,255,0.88);
    font-size: .85rem;
}
.ev-categorie-btn:focus-visible, .ev-categorie.ouvert .ev-categorie-btn { outline: none; border-color: #9972B0; }
.ev-categorie-btn .bi-chevron-down { color: rgba(255,255,255,0.6); transition: transform .2s; }
.ev-categorie.ouvert .bi-chevron-down { transform: rotate(180deg); }
.ev-categorie-liste {
    position: absolute;
    left: 0; right: 0; top: calc(100% + 6px);
    z-index: 20;
    margin: 0;
    padding: 6px;
    list-style: none;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 14px 34px rgba(33,28,49,.28);
    max-height: 280px;
    overflow-y: auto;
}
.ev-categorie-liste a {
    display: block;
    padding: .6rem .8rem;
    border-radius: 8px;
    color: #211C31;
    font-size: .88rem;
    text-decoration: none;
}
.ev-categorie-liste a:hover, .ev-categorie-liste a:focus-visible { background: #f1ebf6; color: #542680; outline: none; }
.ev-categorie-liste a.active { background: #542680; color: #fff; font-weight: 600; }
.ev-hero-stats { flex-wrap: wrap; gap: .5rem 2rem; }
.ev-effacer { color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 600; }
.ev-effacer:hover { color: #fff; }
@media (max-width: 575.98px) {
    .ev-segment { font-size: .74rem; }
}

/* ===== Section Événements passés ===== */
.ev-past-section {
    padding: 3rem 0;
    background: #f7f5f3;
}
.ev-section-head {
    text-align: center;
    margin-bottom: 2rem;
}
.ev-section-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 1rem;
    background: rgba(123,63,160,0.08);
    border: 1px solid rgba(123,63,160,0.15);
    border-radius: 20px;
    color: #542680;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    margin-bottom: 0.75rem;
}
.ev-section-title {
    font-size: 1.9rem;
    font-weight: 800;
    color: #211C31;
    margin: 0 0 0.4rem;
}
.ev-section-sub {
    color: #6c757d;
    font-size: 0.95rem;
    margin: 0;
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
.ev-card-past {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.5rem;
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
    max-width: 440px;
    margin: 1rem auto;
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: #faf8fc;
    border: 1px dashed #ddd2e8;
    border-radius: 18px;
}
.ev-empty-icone {
    width: 64px; height: 64px;
    margin: 0 auto 1rem;
    display: grid; place-items: center;
    border-radius: 50%;
    background: #f1ebf6;
    color: #542680;
    font-size: 1.6rem;
}
.ev-empty-titre { font-size: 1.15rem; font-weight: 700; color: #211C31; margin: 0 0 .35rem; }
.ev-empty-texte { color: #6b6474; font-size: .9rem; margin: 0 0 1rem; }
.ev-empty-filtres { display: flex; flex-wrap: wrap; justify-content: center; gap: .4rem; margin-bottom: 1.25rem; }
.ev-empty-filtre {
    display: inline-flex; align-items: center; gap: .2rem;
    padding: .3rem .5rem .3rem .75rem;
    border-radius: 20px;
    background: #fff;
    border: 1px solid #e4dcec;
    color: #4a4453;
    font-size: .8rem; font-weight: 600;
    text-decoration: none;
}
.ev-empty-filtre:hover { border-color: #542680; color: #542680; }
.ev-empty-action {
    display: inline-block;
    padding: .65rem 1.4rem;
    border-radius: 10px;
    background: #542680;
    color: #fff;
    font-weight: 600; font-size: .88rem;
    text-decoration: none;
}
.ev-empty-action:hover { background: #45206a; color: #fff; }

/* ===== Choix de l'affichage ===== */
.ev-vue-barre { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.ev-vue-compte { font-size: .85rem; color: #6b6474; font-weight: 600; }
.ev-vue { display: inline-flex; padding: 3px; border-radius: 10px; background: #f1ebf6; }
.ev-vue-btn {
    width: 36px; height: 32px;
    border: 0; border-radius: 8px;
    background: transparent;
    color: #8a8493;
    font-size: 1rem;
}
.ev-vue-btn.active { background: #fff; color: #542680; box-shadow: 0 1px 4px rgba(33,28,49,.12); }
.ev-vue-btn:focus-visible { outline: 2px solid #542680; outline-offset: 1px; }

/* Affichage en liste : image à gauche, infos au centre, bouton à droite */
.ev-grid.ev-liste { grid-template-columns: 1fr; gap: .75rem; }
.ev-liste .ev-card { flex-direction: row; }
.ev-liste .ev-card:hover { transform: none; }
.ev-liste .ev-card-img { width: 180px; height: auto; min-height: 120px; flex-shrink: 0; }
.ev-liste .ev-card-overlay { display: none; }
.ev-liste .ev-card-gauge { display: none; }
.ev-liste .ev-card-body {
    flex: 1;
    min-width: 0;
    padding: .85rem 1.1rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    column-gap: 1.25rem;
    align-content: center;
}
.ev-liste .ev-card-body > * { grid-column: 1; }
.ev-liste .ev-card-body > .ev-card-btn {
    grid-column: 2;
    grid-row: 1 / span 4;
    align-self: center;
    width: auto;
    min-width: 170px;
    padding: .6rem 1.1rem;
    text-align: center;
    white-space: nowrap;
}
@media (max-width: 575px) {
    .ev-liste .ev-card-img { width: 96px; min-height: 96px; }
    .ev-liste .ev-card-badge { display: none; }
    .ev-liste .ev-card-title { font-size: .92rem; }
    .ev-liste .ev-card-body { grid-template-columns: 1fr; padding: .7rem .8rem; }
    /* Mobile : bouton compact sous les infos */
    .ev-liste .ev-card-body > .ev-card-btn {
        grid-column: 1;
        grid-row: auto;
        justify-self: start;
        min-width: 0;
        margin-top: .4rem;
        padding: .35rem .8rem;
        font-size: .75rem;
    }
}

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
<script>
// Cartes / liste : bascule l'affichage et mémorise le choix.
document.addEventListener('DOMContentLoaded', function () {
    const grille = document.getElementById('evGrille');
    const boutons = document.querySelectorAll('.ev-vue-btn');
    if (!grille || !boutons.length) return;
    function appliquer(vue) {
        grille.classList.toggle('ev-liste', vue === 'liste');
        boutons.forEach(b => {
            const actif = b.dataset.vue === vue;
            b.classList.toggle('active', actif);
            b.setAttribute('aria-pressed', actif ? 'true' : 'false');
        });
    }
    let vue = 'cartes';
    try { vue = localStorage.getItem('pax-vue-evenements') || 'cartes'; } catch (e) {}
    appliquer(vue);
    boutons.forEach(b => b.addEventListener('click', function () {
        appliquer(b.dataset.vue);
        try { localStorage.setItem('pax-vue-evenements', b.dataset.vue); } catch (e) {}
    }));
});
</script>
@endsection
