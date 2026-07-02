@extends('layouts.public')

@section('title', $evenement->titre . ' — PaxEvent')
@section('description', Str::limit($evenement->description ?? 'Consultez les détails de cet événement sur PaxEvent.', 160))
@section('og_title', $evenement->titre)
@section('og_description', Str::limit($evenement->description ?? 'Réservez vos billets pour ' . $evenement->titre . ' sur PaxEvent.', 160))
@if($evenement->image)
    @section('og_image', asset('storage/' . $evenement->image))
@endif

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($evenement->titre, 40) }}</li>
@endsection

@section('content')
@php
    $placesRestantes = max(0, $evenement->capacite - $evenement->quota_vendu);
    $estComplet = $placesRestantes <= 0;
    $remplissage = $evenement->capacite > 0 ? round(($evenement->quota_vendu / $evenement->capacite) * 100) : 0;
@endphp

<div class="show-event">
    <!-- Hero -->
    <div class="show-hero">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    @if($evenement->categorie)
                        <span class="show-badge">{{ ucfirst($evenement->categorie) }}</span>
                    @endif
                    <h1 class="show-title">{{ $evenement->titre }}</h1>
                    <div class="show-meta">
                        <span><i class="bi bi-calendar3"></i> {{ $evenement->date_event->format('d M Y') }}</span>
                        <span><i class="bi bi-clock"></i> {{ $evenement->date_event->format('H:i') }}</span>
                        <span><i class="bi bi-geo-alt"></i> {{ $evenement->lieu }}</span>
                        @if(!$estComplet)
                            <span><i class="bi bi-people"></i> {{ number_format($placesRestantes, 0, ',', ' ') }} places</span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5 text-lg-end">
                    @if($evenement->image)
                        <div class="show-hero-image">
                            <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-5 py-4">
            <!-- Colonne gauche (rendue après le formulaire sur mobile via order) -->
            <div class="col-lg-7 order-lg-first">
                <!-- Barre infos rapides -->
                <div class="show-infos">
                    <div class="show-info-chip">
                        <i class="bi bi-calendar-check"></i>
                        <span><strong>Date :</strong> {{ $evenement->date_event->format('d M Y') }}</span>
                    </div>
                    <div class="show-info-chip">
                        <i class="bi bi-clock"></i>
                        <span><strong>Heure :</strong> {{ $evenement->date_event->format('H:i') }}</span>
                    </div>
                    <div class="show-info-chip">
                        <i class="bi bi-geo-alt"></i>
                        <span><strong>Lieu :</strong> {{ $evenement->lieu }}</span>
                    </div>
                    @if(!$estComplet)
                        <div class="show-info-chip">
                            <i class="bi bi-people"></i>
                            <span><strong>{{ number_format($placesRestantes, 0, ',', ' ') }}</strong> places</span>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="show-card">
                    <h5 class="show-card-title"><i class="bi bi-info-circle"></i> Description</h5>
                    @if($evenement->description)
                        <p class="show-card-text">{{ $evenement->description }}</p>
                    @else
                        <p class="show-card-text text-muted">Aucune description disponible.</p>
                    @endif
                </div>

                <!-- Partager -->
                <div class="show-card">
                    <h5 class="show-card-title"><i class="bi bi-share"></i> Partager</h5>
                    <div class="d-flex gap-2 flex-nowrap">
                        <button class="show-share-btn share-native" onclick="shareEvent()" title="Partager"><i class="bi bi-box-arrow-up"></i></button>
                        <a href="https://wa.me/?text={{ urlencode($evenement->titre . ' - ' . route('evenements.public.show', $evenement->id)) }}" target="_blank" class="show-share-btn" style="color:#25D366;" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('evenements.public.show', $evenement->id)) }}" target="_blank" class="show-share-btn" style="color:#1877F2;" title="Facebook"><i class="bi bi-facebook"></i></a>
                        <button class="show-share-btn" onclick="copyLink()" title="Copier le lien"><i class="bi bi-link-45deg"></i></button>
                    </div>
                </div>

                <!-- Lieu -->
                <div class="show-card">
                    <h5 class="show-card-title"><i class="bi bi-pin-map"></i> Lieu</h5>
                    <div class="show-map">
                        <iframe src="https://www.google.com/maps?q={{ urlencode($evenement->lieu) }}&output=embed" width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>

                <!-- Contacter -->
                <div class="show-card">
                    <h5 class="show-card-title"><i class="bi bi-envelope"></i> Une question ?</h5>
                    <p class="show-card-text" style="margin-bottom:1rem;">Vous avez une question spécifique sur cet événement ? Contactez directement l'organisateur.</p>
                    <button type="button" class="show-btn" data-bs-toggle="modal" data-bs-target="#contactOrganisateurModal">
                        <i class="bi bi-envelope me-2"></i> Contacter l'organisateur
                    </button>
                </div>
            </div>

            <!-- Colonne droite (rendue avant la gauche sur mobile) -->
            <div class="col-lg-5 order-lg-last">
                <div class="show-sidebar">
                    @if(($venteCloturee ?? false) || ($evenementPasse ?? false))
                        <div class="show-card text-center py-4">
                            <div class="show-lock-icon"><i class="bi bi-lock-fill"></i></div>
                            <h5 style="color:var(--danger); font-weight:700; margin-bottom:0.25rem;">Vente clôturée</h5>
                            <p class="show-card-text mb-0">
                                @if($evenementPasse ?? false)
                                    Cet événement a déjà eu lieu.
                                @else
                                    Les inscriptions ne sont plus possibles pour cet événement.
                                @endif
                            </p>
                        </div>
                    @else
                    <div class="show-card">
                        <div class="show-ticket-header">
                            <div class="show-ticket-icon"><i class="bi bi-ticket-perforated"></i></div>
                            <div>
                                <h5 class="fw-bold mb-0">{{ $evenement->gratuit ? 'Participer gratuitement' : 'Acheter un billet' }}</h5>
                                <small class="text-muted">{{ number_format($placesRestantes, 0, ',', ' ') }} places disponibles</small>
                            </div>
                        </div>
                        <hr class="my-3">

                        @if($evenement->gratuit)
                        <form action="{{ route('evenements.achat', $evenement->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="gratuit" value="1">
                            <div class="mb-3">
                                <label class="show-label">Nombre de places</label>
                                <div class="show-qty">
                                    <button type="button" id="qtyMinusG">-</button>
                                    <input type="number" id="quantiteInputG" value="1" min="1" max="5" readonly>
                                    <button type="button" id="qtyPlusG">+</button>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="show-label">Prénom et Nom <span class="text-danger">*</span></label>
                                <input type="text" class="show-input" name="nom_acheteur" value="{{ old('nom_acheteur') }}" placeholder="Ex: Kofi Mensah" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="show-input" name="email_acheteur" value="{{ old('email_acheteur') }}" placeholder="votre@email.com" required>
                            </div>
                            <div class="show-total">
                                <span class="fw-bold">Billets</span>
                                <span class="show-total-price">Gratuit</span>
                            </div>
                            <button type="submit" class="show-btn show-btn-primary" {{ $estComplet ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle me-2"></i> Participer
                            </button>
                            <p class="show-secure"><i class="bi bi-check-circle me-1" style="color:var(--violet);"></i> Réservation gratuite — Ticket PDF reçu par email</p>
                        </form>
                        @else
                        <div class="mb-3">
                            <label class="show-label">Choisissez votre tarif</label>
                            @if($tarifs->isEmpty())
                                <div class="alert alert-danger py-2" style="font-size:0.85rem;">Aucun tarif disponible</div>
                            @else
                                <div class="d-flex flex-column gap-2">
                                    @foreach($tarifs as $tarif)
                                        <label class="show-tarif" onclick="selectTarif(this)">
                                            <input type="radio" name="tarif_id" value="{{ $tarif->id }}" {{ $loop->first ? 'checked' : '' }}>
                                            @if($estUniversitaire)
                                                <span class="show-tarif-badge" style="background:{{ $tarif->categorie === 'etudiant' ? 'rgba(84,38,128,0.1)' : 'rgba(84,38,128,0.06)' }}; color:{{ $tarif->categorie === 'etudiant' ? '#542680' : '#211C31' }};">
                                                    {{ $tarif->categorie === 'etudiant' ? 'Étudiant' : 'Externe' }}
                                                </span>
                                            @endif
                                            <strong>{{ $tarif->type === 'normal' ? 'Standard' : 'VIP' }}</strong>
                                            <strong class="show-tarif-price">{{ number_format($tarif->prix, 0, ',', ' ') . ' F' }}</strong>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="show-label">Quantité</label>
                            <div class="show-qty">
                                <button type="button" id="qtyMinus">-</button>
                                <input type="number" id="quantiteInput" value="1" min="1" max="5" readonly>
                                <button type="button" id="qtyPlus">+</button>
                            </div>
                        </div>
                        <hr>
                        <form action="{{ route('evenements.achat', $evenement->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="tarif_id" id="hiddenTarifId">
                            <input type="hidden" name="quantite" id="hiddenQuantite" value="1">
                            <div class="mb-3">
                                <label class="show-label">Prénom et Nom <span class="text-danger">*</span></label>
                                <input type="text" class="show-input" name="nom_acheteur" value="{{ old('nom_acheteur') }}" placeholder="Ex: Kofi Mensah" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="show-input" name="email_acheteur" value="{{ old('email_acheteur') }}" placeholder="votre@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel" class="show-input" name="telephone_acheteur" value="{{ old('telephone_acheteur') }}" placeholder="+229 XX XX XX XX" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label">Code promo <span class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="text" class="show-input" name="code_promo" value="{{ old('code_promo') }}" placeholder="PROMO-XXXXXX" style="text-transform:uppercase;">
                            </div>
                            <div class="show-total">
                                <span class="fw-bold">Total à payer</span>
                                <span class="show-total-price" id="totalDisplay">--</span>
                            </div>
                            <button type="submit" class="show-btn show-btn-primary" id="btnPayer" {{ $tarifs->isEmpty() || $estComplet ? 'disabled' : '' }}>
                                <i class="bi bi-shield-lock me-2"></i> Reserver et payer
                            </button>
                            <p class="show-secure"><i class="bi bi-shield-check me-1" style="color:var(--violet);"></i> Paiement 100% sécurisé — Ticket PDF envoyé par email</p>
                        </form>
                        @endif
                    </div>
                    @endif

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
                        <div class="show-card mt-4">
                            <h6 class="fw-bold mb-3" style="color:#211C31;"><i class="bi bi-calendar-event me-2" style="color:var(--violet);"></i>Autres événements</h6>
                            <div class="d-flex flex-column gap-3">
                                @foreach($autresEvenements as $autre)
                                    <a href="{{ route('evenements.public.show', $autre->id) }}" class="show-other-card">
                                        @if($autre->image)
                                            <img src="{{ asset('storage/' . $autre->image) }}" alt="">
                                        @else
                                            <div class="show-other-placeholder"><i class="bi bi-calendar-event"></i></div>
                                        @endif
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="show-other-title">{{ $autre->titre }}</div>
                                            <div class="show-other-date"><i class="bi bi-calendar3 me-1"></i>{{ $autre->date_event->format('d M Y') }}</div>
                                        </div>
                                        <i class="bi bi-chevron-right" style="color:#aaa; font-size:0.8rem;"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contacter -->
<div class="modal fade" id="contactOrganisateurModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" style="color:#211C31;"><i class="bi bi-envelope me-2" style="color:var(--violet);"></i> Contacter l'organisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color:#6c757d; font-size:0.85rem; margin-bottom:1rem;">
                    Votre message sera envoyé à <strong>{{ $evenement->user->nom }}</strong> (organisateur de <strong>{{ $evenement->titre }}</strong>).
                </p>
                <form action="{{ route('evenements.contacter-organisateur', $evenement->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="show-label">Votre nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="show-input" placeholder="Ex: Kofi Mensah" required>
                    </div>
                    <div class="mb-3">
                        <label class="show-label">Votre email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="show-input" placeholder="votre@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="show-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="show-input" rows="4" placeholder="Écrivez votre message ici..." required minlength="10"></textarea>
                    </div>
                    <button type="submit" class="show-btn show-btn-primary" style="background:var(--violet);">
                        <i class="bi bi-send me-2"></i> Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== LAYOUT ===== */
.show-event {
    background: #f7f5f3;
    padding-bottom: 3rem;
}

/* ===== HERO ===== */
.show-hero {
    padding: 4rem 0 2.5rem;
    background: #111;
    position: relative;
}
.show-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(84,38,128,0.08) 0%, transparent 100%);
    pointer-events: none;
}
.show-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.8rem;
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    color: rgba(255,255,255,0.5);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 1rem;
}
.show-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 1rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
}
.show-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem 1.2rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.82rem;
}
.show-meta i { margin-right: 0.35rem; color: rgba(255,255,255,0.25); }
.show-status-badge {
    display: inline-flex;
    padding: 0.35rem 1rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.2px;
}
.show-status-badge.available {
    background: rgba(18,151,110,0.1);
    color: #3ab87a;
    border: 1px solid rgba(18,151,110,0.15);
}
.show-status-badge.closed {
    background: rgba(231,76,60,0.08);
    color: #e74c3c;
    border: 1px solid rgba(231,76,60,0.12);
}

/* ===== HERO IMAGE ===== */
.show-hero-image {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
}
.show-hero-image img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    display: block;
}

/* ===== INFOS ===== */
.show-infos {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.show-info-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    background: #fff;
    border: 1px solid #ede5f0;
    font-size: 0.82rem;
}
.show-info-chip i { color: var(--violet); font-size: 0.9rem; }

/* ===== CARDS ===== */
.show-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    border: 1px solid #ede5f0;
    transition: box-shadow 0.2s;
}
.show-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.show-card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.75rem;
}
.show-card-title i {
    margin-right: 0.5rem;
    color: var(--violet);
}
.show-card-text {
    font-size: 0.88rem;
    color: #6c757d;
    line-height: 1.8;
    margin: 0;
}

/* ===== MAP ===== */
.show-map {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #ede5f0;
}

/* ===== SHARE ===== */
.show-share-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid #ede5f0;
    background: #fff;
    color: #6c757d;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.2s;
    cursor: pointer;
}
.show-share-btn:hover {
    border-color: var(--violet);
    background: rgba(135,66,139,0.04);
    color: var(--violet);
}

/* ===== SIDEBAR ===== */
.show-sidebar { position: sticky; top: 90px; }
.show-lock-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(231,76,60,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: var(--danger);
    font-size: 1.4rem;
}
.show-ticket-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.show-ticket-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(84,38,128,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #542680;
    font-size: 1.3rem;
}

/* ===== TARIFS ===== */
.show-tarif {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border: 2px solid #ede5f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
}
.show-tarif:hover { border-color: #9972B0; }
.show-tarif.selected { border-color: #542680; background: rgba(84,38,128,0.04); }
.show-tarif input[type="radio"] { display: none; }
.show-tarif-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
}
.show-tarif-price {
    margin-left: auto;
    color: #542680;
    font-size: 1rem;
}

/* ===== FORM ===== */
.show-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #211C31;
    margin-bottom: 0.3rem;
    display: block;
}
.show-input {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 1.5px solid #ede5f0;
    border-radius: 10px;
    font-size: 0.85rem;
    outline: none;
    transition: all 0.2s;
    background: #fafafa;
}
.show-input:focus {
    border-color: #542680;
    box-shadow: 0 0 0 3px rgba(84,38,128,0.08);
    background: #fff;
}
.show-qty {
    display: inline-flex;
    align-items: center;
    border: 1.5px solid #ede5f0;
    border-radius: 10px;
    overflow: hidden;
}
.show-qty button {
    width: 38px; height: 38px; border: none;
    background: #f7f5f3; color: #333;
    font-size: 1.1rem; cursor: pointer; transition: background 0.15s;
}
.show-qty button:hover { background: #e8e4e0; }
.show-qty input {
    width: 52px; height: 38px; border: none;
    text-align: center; font-weight: 700; font-size: 0.95rem;
    outline: none;
}
.show-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    background: #f8f6f9;
    margin-bottom: 1rem;
}
.show-total-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: #542680;
}
.show-btn {
    display: block;
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s;
}
.show-btn-primary {
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
}
.show-btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(84,38,128,0.35);
    color: #fff;
}
.show-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.show-secure {
    text-align: center;
    font-size: 0.72rem;
    color: #6c757d;
    margin: 0.5rem 0 0;
}

/* ===== AUTRES ÉVÉNEMENTS ===== */
.show-other-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem;
    border-radius: 10px;
    background: #f8f6f9;
    text-decoration: none;
    transition: background 0.2s;
}
.show-other-card:hover { background: #f0edf2; }
.show-other-card img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}
.show-other-placeholder {
    width: 50px;
    height: 50px;
    background: #e8e8e8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #999;
}
.show-other-title {
    font-weight: 700;
    font-size: 0.82rem;
    color: #211C31;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.show-other-date {
    font-size: 0.72rem;
    color: #9a9a9a;
}

@media (max-width: 991px) {
    .show-sidebar { position: static; }
}
.share-native {
    background: linear-gradient(135deg, #542680, #7b3fa0) !important;
    color: #fff !important;
    border: none !important;
}
.share-native:hover {
    opacity: 0.9;
    transform: scale(1.1);
}
@media (max-width: 767px) {
    .show-hero { padding: 2.5rem 0 2rem; }
    .show-title { font-size: 1.5rem; }
    .show-hero-image img { height: 200px; }
    .show-infos { gap: 0.5rem; }
}
</style>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="shareToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Lien copié dans le presse-papier !', 'success');
    }).catch(() => {
        showToast('Impossible de copier le lien', 'danger');
    });
}

function shareEvent() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $evenement->titre }}',
            text: '{{ $evenement->titre }} - {{ $evenement->date_event->format('d M Y') }} à {{ $evenement->lieu }}@if($evenement->description) - {{ strip_tags(Str::limit($evenement->description, 120)) }}@endif',
            url: window.location.href,
        }).catch(() => {});
    } else {
        copyLink();
    }
}

function showToast(message, type) {
    const el = document.getElementById('shareToast');
    el.querySelector('.toast-body').textContent = message;
    el.className = 'toast align-items-center text-bg-' + type + ' border-0';
    const toast = bootstrap.Toast.getOrCreateInstance(el, { autohide: true, delay: 3000 });
    toast.show();
}

@if($evenement->gratuit)
document.addEventListener('DOMContentLoaded', function() {
    const qty = document.getElementById('quantiteInputG');
    document.getElementById('qtyMinusG').addEventListener('click', function() {
        const v = parseInt(qty.value);
        if (v > 1) qty.value = v - 1;
    });
    document.getElementById('qtyPlusG').addEventListener('click', function() {
        const v = parseInt(qty.value);
        if (v < 5) qty.value = v + 1;
    });
});
@else
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
    document.querySelectorAll('.show-tarif').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

document.addEventListener('DOMContentLoaded', function() {
    const first = document.querySelector('.show-tarif');
    if (first) selectTarif(first);
});
@endif
</script>
@endsection
