@extends('layouts.public')

@section('title', 'Tous les evenements - PassEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Evenements</li>
@endsection

@section('content')
<!-- Hero mini -->
<section class="py-4" style="background: linear-gradient(135deg, var(--violet) 0%, var(--violet-dark) 100%); color: #fff;">
    <div class="container">
        <h2 class="fw-bold mb-1">Tous les evenements</h2>
        <p class="mb-0" style="opacity: 0.8;">Trouvez l'evenement parfait pour vous</p>
    </div>
</section>

<!-- Filters -->
<section class="py-4" style="background: var(--blanc); border-bottom: 1px solid #e5e5e5;">
    <div class="container">
        <!-- Search + Date -->
        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-8">
                <form action="{{ route('evenements.public') }}" method="GET" class="d-flex gap-2">
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
                        <a href="{{ route('evenements.public') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted" style="font-size: 0.82rem; font-weight: 600;"><i class="bi bi-funnel me-1"></i> Date :</span>
                    <form action="{{ route('evenements.public') }}" method="GET" class="d-flex gap-1">
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
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('evenements.public') }}{{ $q ? '?q=' . $q : '' }}{{ $selectedDate ? ($q ? '&date=' . $selectedDate : '?date=' . $selectedDate) : '' }}"
               class="filter-chip {{ !$selectedCategorie ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap-fill"></i> Toutes les categories
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('evenements.public') }}?categorie={{ $cat }}{{ $q ? '&q=' . $q : '' }}{{ $selectedDate ? '&date=' . $selectedDate : '' }}"
                   class="filter-chip {{ $selectedCategorie == $cat ? 'active' : '' }}">
                    {{ ucfirst($cat) }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<style>
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

    .filter-chip:hover { border-color: var(--violet); color: var(--violet); }

    .filter-chip.active {
        background: var(--violet);
        border-color: var(--violet);
        color: #fff;
    }

    .filter-chip.active:hover { background: var(--violet-dark); color: #fff; }

    .gauge-bar {
        height: 4px;
        border-radius: 2px;
        background: #f0f0f0;
        overflow: hidden;
    }

    .gauge-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.4s ease;
    }

    .gauge-low { background: var(--vert); }
    .gauge-mid { background: #f59e0b; }
    .gauge-high { background: var(--danger); }
    .gauge-full { background: var(--danger); }

    .badge-complet {
        background: rgba(231,76,60,0.12);
        color: #e74c3c;
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
        font-weight: 600;
    }
</style>

<!-- Events Grid -->
<section class="py-5">
    <div class="container">
        @if($evenements->count() > 0)
            <div class="row g-3 g-md-4">
                @foreach($evenements as $evenement)
                    @php
                        $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
                        $estComplet = $placesRestantes <= 0;
                        $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
                        $prixDernier = $evenement->tarifs->min('prix');
                        $gaugeClass = $remplissage >= 100 ? 'gauge-full' : ($remplissage >= 75 ? 'gauge-high' : ($remplissage >= 40 ? 'gauge-mid' : 'gauge-low'));
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="{{ route('evenements.public.show', $evenement->id) }}" class="event-card-link h-100 text-decoration-none">
                            <div class="event-card h-100 {{ $estComplet ? 'opacity-75' : '' }}">
                                @if($evenement->image)
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}" style="height: 180px;">
                                        @if($evenement->categorie)
                                            <span class="position-absolute top-0 start-0 m-2 badge" style="background: rgba(255,255,255,0.92); color: var(--violet); font-size: 0.65rem; font-weight: 700; border-radius: 10px;">
                                                {{ ucfirst($evenement->categorie) }}
                                            </span>
                                        @endif
                                        @if($estComplet)
                                            <span class="position-absolute top-0 end-0 m-2 badge-complet">Complet</span>
                                        @endif
                                    </div>
                                @else
                                    <div style="height:180px; background: linear-gradient(135deg, var(--violet), var(--violet-dark)); display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-calendar-event text-white" style="font-size: 3rem;"></i>
                                    </div>
                                    @if($estComplet)
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge-complet">Complet</span>
                                        </div>
                                    @endif
                                @endif
                                <div class="p-3 d-flex flex-column">
                                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem; line-height: 1.3;">{{ $evenement->titre }}</h5>
                                    <p class="text-muted mb-2" style="font-size: 0.85rem; line-height: 1.3;">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $evenement->date_event->format('d M Y') }}<br>
                                        <i class="bi bi-geo-alt me-1"></i>{{ $evenement->lieu }}
                                    </p>

                                    <!-- Price from -->
                                    @if($prixDernier)
                                        <div class="mb-2" style="font-size: 0.8rem;">
                                            <span class="text-muted">A partir de</span>
                                            <strong style="color: var(--vert); font-size: 1rem;">{{ number_format($prixDernier, 0, ',', ' ') }} F</strong>
                                        </div>
                                    @endif

                                    <!-- Gauge -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between" style="font-size: 0.7rem;">
                                            <span class="text-muted">{{ $placesRestantes }} places</span>
                                            <span class="text-muted">{{ $remplissage }}%</span>
                                        </div>
                                        <div class="gauge-bar mt-1" style="background: #e0e0e0;">
                                            <div class="gauge-fill" style="width: {{ min($remplissage, 100) }}%; background: #000;"></div>
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    @if($estComplet)
                                        <span class="btn btn-sm w-100 disabled" style="border-radius: 8px; background: #98919b; border-color: #98919b; color: #fff;">
                                            <i class="bi bi-slash-circle me-1"></i> Complet
                                        </span>
                                    @else
                                        <span class="btn btn-sm w-100" style="border-radius: 8px; background: var(--vert); border-color: var(--vert); color: #fff; pointer-events: none;">
                                            <i class="bi bi-ticket-perforated me-1"></i> Acheter ticket
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($evenements->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $evenements->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x d-block mb-3" style="font-size: 3rem; color: var(--gris);"></i>
                <h5 class="text-muted">Aucun evenement trouve</h5>
                <p class="text-muted">
                    @if($q || $selectedCategorie || $selectedDate)
                        Essayez une autre categorie ou un autre terme de recherche.
                    @else
                        Revenez plus tard pour decouvrir nos prochains evenements.
                    @endif
                </p>
                @if($q || $selectedCategorie || $selectedDate)
                    <a href="{{ route('evenements.public') }}" class="btn btn-violet" style="border-radius: 8px;">
                        Voir tous les evenements
                    </a>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
