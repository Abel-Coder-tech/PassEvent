@extends('layouts.public')

@section('title', $evenement->titre . ' - PassEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Evenements</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($evenement->titre, 40) }}</li>
@endsection

@section('content')
@php
    $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
    $estComplet = $placesRestantes <= 0;
    $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
    $prixEtudiant = $tarifs->where('categorie', 'etudiant')->min('prix');
    $prixExterne = $tarifs->where('categorie', 'externe')->min('prix');
@endphp

<!-- Banniere evenement -->
<section class="event-banner position-relative">
    @if($evenement->image)
        <div class="banner-img" style="background: url('{{ asset('storage/' . $evenement->image) }}') center/cover no-repeat;"></div>
        <div class="banner-overlay"></div>
    @else
        <div class="banner-img" style="background: linear-gradient(135deg, var(--violet), var(--violet-dark), var(--aubergine));"></div>
    @endif

    <div class="banner-content position-relative">
        <div class="container">
            <div class="row g-4 align-items-end">
                <div class="col-12">
                    @if($evenement->categorie)
                        <span class="badge mb-2 px-3 py-2" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); color: #fff; font-size: 0.75rem; font-weight: 700; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ ucfirst($evenement->categorie) }}
                        </span>
                    @endif
                    <h1 class="display-6 fw-bold text-white mb-2 banner-title">{{ $evenement->titre }}</h1>
                    <div class="d-flex flex-wrap gap-3 text-white" style="font-size: 0.92rem; opacity: 0.9;">
                        <span><i class="bi bi-calendar3 me-1"></i>{{ $evenement->date_event->format('d M Y') }}</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $evenement->date_event->format('H:i') }}</span>
                        <span><i class="bi bi-geo-alt me-1"></i>{{ $evenement->lieu }}</span>
                        <span><i class="bi bi-people me-1"></i>{{ number_format($placesRestantes, 0, ',', ' ') }} places</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .event-banner { position: relative; min-height: 320px; }

    .banner-img {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .banner-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.3) 60%, rgba(0,0,0,0.1) 100%);
    }

    .banner-content {
        position: relative;
        z-index: 2;
        padding: 5rem 0 3rem;
    }

    .event-detail-card {
        border-radius: 12px;
        border: 1px solid #e5e5e5;
        background: var(--blanc);
        overflow: hidden;
    }

    .event-detail-card .card-section {
        padding: 1.5rem;
        border-bottom: 1px solid #f5f5f5;
    }

    .event-detail-card .card-section:last-child { border-bottom: none; }

    .tarif-card {
        border: 2px solid #e5e5e5;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--blanc);
    }

    .tarif-card:hover { border-color: var(--menthe); background: rgba(178,224,214,0.06); }

    .tarif-card.selected { border-color: var(--vert); background: rgba(18,151,110,0.04); }

    .tarif-card input[type="radio"] { display: none; }

    .form-control:focus, .form-select:focus {
        border-color: var(--vert);
        box-shadow: 0 0 0 0.15rem rgba(18, 151, 110, 0.15);
    }

    .qty-control { display: inline-flex; align-items: center; border: 1px solid #e5e5e5; border-radius: 8px; overflow: hidden; }
    .qty-control button {
        width: 36px; height: 36px; border: none; background: var(--blanc-casse); color: var(--sombre); font-size: 1rem; cursor: pointer; transition: background 0.15s;
    }
    .qty-control button:hover { background: #e5e5e5; }
    .qty-control input {
        width: 50px; height: 36px; border: none; text-align: center; font-weight: 700; font-size: 0.95rem;
    }
    .qty-control input:focus { outline: none; }

    .sidebar-sticky { position: sticky; top: 80px; }

    .share-btn {
        width: 38px; height: 38px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #e5e5e5; color: var(--sombre); background: var(--blanc); transition: all 0.15s; font-size: 0.9rem;
    }
    .share-btn:hover { border-color: var(--violet); color: var(--violet); background: rgba(135,66,139,0.04); }

    @media (max-width: 767.98px) {
        .event-banner { min-height: 260px; }
        .banner-content { padding: 4rem 0 2rem; }
        .banner-title { font-size: 1.4rem !important; }
    }
</style>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-7">
                <!-- Description -->
                <div class="event-detail-card mb-4">
                    <div class="card-section">
                        <h4 class="fw-bold mb-3"><i class="bi bi-info-circle me-2" style="color: var(--violet);"></i>Description</h4>
                        @if($evenement->description)
                            <p class="text-muted" style="line-height: 1.75;">{{ $evenement->description }}</p>
                        @else
                            <p class="text-muted">Aucune description disponible pour cet evenement.</p>
                        @endif
                    </div>
                </div>

                <!-- Infos + Map -->
                <div class="event-detail-card mb-4">
                    <div class="card-section">
                        <h5 class="fw-bold mb-3"><i class="bi bi-pin-map me-2" style="color: var(--vert);"></i>Lieu et date</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <div class="p-3 rounded-3" style="background: var(--blanc-casse);">
                                    <i class="bi bi-calendar3" style="color: var(--violet);"></i>
                                    <div class="fw-bold mt-2">{{ $evenement->date_event->format('d M Y') }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 rounded-3" style="background: var(--blanc-casse);">
                                    <i class="bi bi-clock" style="color: var(--vert);"></i>
                                    <div class="fw-bold mt-2">{{ $evenement->date_event->format('H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 rounded-3" style="background: var(--blanc-casse);">
                                    <i class="bi bi-people" style="color: var(--teal);"></i>
                                    <div class="fw-bold mt-2">{{ number_format($evenement->capacite, 0, ',', ' ') }} places</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded-3" style="background: rgba(135,66,139,0.04);">
                            <i class="bi bi-geo-alt-fill" style="color: var(--violet); font-size: 1.2rem;"></i>
                            <div>
                                <div class="fw-bold">{{ $evenement->lieu }}</div>
                            </div>
                        </div>
                        <!-- Google Maps Embed -->
                        <div class="rounded-3 overflow-hidden" style="height: 250px; background: #f5f5f5;">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12707.298550849!2d2.4244!3d6.3654!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10235780f0b0c787%3A0x1b4a3c7c5a6e0f0!2sCotonou%2C+Benin!5e0!3m2!1sfr!2sbj!4v1" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>

                <!-- Organisateur -->
                <div class="event-detail-card mb-4">
                    <div class="card-section">
                        <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2" style="color: var(--teal);"></i>Organisateur</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--violet), var(--vert)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                                BE
                            </div>
                            <div>
                                <div class="fw-bold">Bureau des etudiants UPAO</div>
                                <div class="text-muted" style="font-size: 0.85rem;">Organisateur officiel</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-5">
                <div class="sidebar-sticky">
                    <!-- Purchase Form -->
                    <div class="event-detail-card mb-4">
                        <div class="card-section" style="background: linear-gradient(135deg, rgba(18,151,110,0.03), rgba(135,66,139,0.03));">
                            <h5 class="fw-bold mb-1"><i class="bi bi-ticket-perforated me-2" style="color: var(--vert);"></i>Acheter un billet</h5>
                            @if($estComplet)
                                <span class="badge-complet">Complet</span>
                            @else
                                <small class="text-muted">{{ number_format($placesRestantes, 0, ',', ' ') }} places disponibles</small>
                            @endif
                        </div>

                        <div class="card-section">
                            <!-- Tarifs -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Choisir votre tarif</label>
                                @if($tarifs->isEmpty())
                                    <div class="alert alert-danger py-2" style="font-size: 0.85rem;">Aucun tarif disponible</div>
                                @else
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($tarifs as $tarif)
                                            <label class="tarif-card d-block" onclick="selectTarif(this)">
                                                <input type="radio" name="tarif_id" value="{{ $tarif->id }}" {{ $loop->first ? 'checked' : '' }}>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge me-2" style="background: {{ $tarif->categorie === 'etudiant' ? 'rgba(135,66,139,0.12)' : 'rgba(66,140,121,0.12)' }}; color: {{ $tarif->categorie === 'etudiant' ? 'var(--violet)' : 'var(--teal)' }};">
                                                            {{ $tarif->categorie === 'etudiant' ? 'Etudiant' : 'Externe' }}
                                                        </span>
                                                        <strong>{{ $tarif->type === 'normal' ? 'Standard' : 'VIP' }}</strong>
                                                    </div>
                                                    <strong style="color: var(--vert);">{{ number_format($tarif->prix, 0, ',', ' ') }} F</strong>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Quantite -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Quantite</label>
                                <div class="qty-control">
                                    <button type="button" id="qtyMinus">-</button>
                                    <input type="number" id="quantiteInput" value="1" min="1" max="5" readonly>
                                    <button type="button" id="qtyPlus">+</button>
                                </div>
                            </div>

                            <hr>

                            <!-- Formulaire -->
                            <form action="{{ route('evenements.achat', $evenement->id) }}" method="POST" id="achatForm">
                                @csrf
                                <input type="hidden" name="tarif_id" id="hiddenTarifId">
                                <input type="hidden" name="quantite" id="hiddenQuantite" value="1">

                                <div class="mb-3">
                                    <label for="nom_acheteur" class="form-label fw-semibold">Prenom et Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom_acheteur" name="nom_acheteur" value="{{ old('nom_acheteur') }}" placeholder="Ex: Kofi Mensah" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email_acheteur" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email_acheteur" name="email_acheteur" value="{{ old('email_acheteur') }}" placeholder="votre@email.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="telephone_acheteur" class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="telephone_acheteur" name="telephone_acheteur" value="{{ old('telephone_acheteur') }}" placeholder="+229 43 70 45 13" required>
                                </div>

                                <div class="mb-3">
                                    <label for="code_promo" class="form-label fw-semibold">Code promo <span class="text-muted fw-normal">(optionnel)</span></label>
                                    <input type="text" class="form-control" id="code_promo" name="code_promo" value="{{ old('code_promo') }}" placeholder="PROMO-XXXXXX" style="text-transform: uppercase;">
                                </div>

                                <!-- Recap -->
                                <div class="p-3 rounded-3 mb-3" style="background: var(--blanc-casse);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Total a payer</span>
                                        <span class="fw-bold" style="font-size: 1.4rem; color: var(--vert);" id="totalDisplay">--</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-vert w-100 py-3" id="btnPayer" style="border-radius: 8px; font-size: 1rem;" {{ $tarifs->isEmpty() || $estComplet ? 'disabled' : '' }}>
                                    <i class="bi bi-shield-lock me-2"></i> Payer avec KKiaPay
                                </button>

                                <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.75rem;">
                                    <i class="bi bi-shield-check me-1" style="color: var(--vert);"></i>
                                    Paiement 100% securise
                                </p>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar: Share + Calendar -->
                    <div class="event-detail-card mb-4">
                        <div class="card-section">
                            <h6 class="fw-bold mb-3"><i class="bi bi-share me-2"></i>Partager l'evenement</h6>
                            <div class="d-flex gap-2">
                                <a href="https://wa.me/?text={{ urlencode($evenement->titre . ' - ' . route('evenements.public.show', $evenement->id)) }}" target="_blank" class="share-btn" title="WhatsApp" style="color: #25D366;">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?text={{ urlencode($evenement->titre) }}&url={{ urlencode(route('evenements.public.show', $evenement->id)) }}" target="_blank" class="share-btn" title="Twitter" style="color: #1DA1F2;">
                                    <i class="bi bi-twitter-x"></i>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('evenements.public.show', $evenement->id)) }}" target="_blank" class="share-btn" title="Facebook" style="color: #1877F2;">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <button class="share-btn" onclick="copyLink()" title="Copier le lien">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-section">
                            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-plus me-2"></i>Ajouter au calendrier</h6>
                            <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ urlencode($evenement->titre) }}&dates={{ $evenement->date_event->format('Ymd\THis') }}/{{ $evenement->date_event->addHours(2)->format('Ymd\THis') }}&details={{ urlencode($evenement->description ?? '') }}&location={{ urlencode($evenement->lieu) }}" target="_blank" class="btn btn-outline-secondary w-100 btn-sm" style="border-radius: 8px;">
                                <i class="bi bi-google me-1"></i> Google Calendar
                            </a>
                        </div>
                    </div>

                    <!-- Autres evenements -->
                    @php
                        $autresEvenements = App\Models\Evenement::with('tarifs')
                            ->where('statut', 'publié')
                            ->where('date_event', '>=', now())
                            ->where('id', '!=', $evenement->id)
                            ->orderBy('date_event', 'asc')
                            ->limit(3)
                            ->get();
                    @endphp
                    @if($autresEvenements->isNotEmpty())
                        <div class="event-detail-card">
                            <div class="card-section">
                                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i>Autres evenements a venir</h6>
                                <div class="d-flex flex-column gap-3">
                                    @foreach($autresEvenements as $autre)
                                        <a href="{{ route('evenements.public.show', $autre->id) }}" class="text-decoration-none">
                                            <div class="d-flex gap-3 align-items-center p-2 rounded-3" style="background: var(--blanc-casse); transition: background 0.15s;">
                                                @if($autre->image)
                                                    <img src="{{ asset('storage/' . $autre->image) }}" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; flex-shrink: 0;">
                                                @else
                                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--violet), var(--violet-dark)); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <i class="bi bi-calendar-event text-white" style="font-size: 1.2rem;"></i>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="fw-bold" style="font-size: 0.82rem; color: var(--sombre); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $autre->titre }}</div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">
                                                        <i class="bi bi-calendar3 me-1"></i>{{ $autre->date_event->format('d M Y') }}
                                                    </div>
                                                </div>
                                                <i class="bi bi-chevron-right" style="color: var(--gris); font-size: 0.8rem;"></i>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantiteInput');
    const hiddenQuantite = document.getElementById('hiddenQuantite');
    const hiddenTarifId = document.getElementById('hiddenTarifId');
    const totalDisplay = document.getElementById('totalDisplay');

    const tarifs = @json($tarifs->map(function($t) { return ['id' => $t->id, 'prix' => $t->prix]; }));

    function getSelectedTarif() {
        const selected = document.querySelector('input[name="tarif_id"]:checked');
        if (!selected) return null;
        return tarifs.find(t => t.id == selected.value);
    }

    function updateTotal() {
        const tarif = getSelectedTarif();
        const qty = parseInt(quantiteInput.value) || 1;
        if (tarif) {
            totalDisplay.textContent = new Intl.NumberFormat('fr-FR').format(tarif.prix * qty) + ' F';
            hiddenTarifId.value = tarif.id;
        } else {
            totalDisplay.textContent = '--';
        }
        hiddenQuantite.value = qty;
    }

    document.getElementById('qtyMinus').addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v > 1) { quantiteInput.value = v - 1; updateTotal(); }
    });

    document.getElementById('qtyPlus').addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v < 5) { quantiteInput.value = v + 1; updateTotal(); }
    });

    document.querySelectorAll('input[name="tarif_id"]').forEach(radio => {
        radio.addEventListener('change', updateTotal);
    });

    updateTotal();
});

function selectTarif(el) {
    document.querySelectorAll('.tarif-card').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href);
    alert('Lien copie !');
}

// Init first tarif
document.addEventListener('DOMContentLoaded', function() {
    const firstTarif = document.querySelector('.tarif-card');
    if (firstTarif) selectTarif(firstTarif);
});

const tarifs = @json($tarifs->map(function($t) { return ['id' => $t->id, 'prix' => $t->prix]; }));
</script>
@endsection
