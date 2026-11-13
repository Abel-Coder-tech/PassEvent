@extends('layouts.public')

@section('title', $evenement->titre . ' — PaxEvent')
@section('description', Str::limit($evenement->description ?? 'Consultez les détails de cet événement sur PaxEvent.', 160))
@section('og_title', $evenement->titre)
@php
    $datesPartage = $evenement->dates->isNotEmpty()
        ? $evenement->dates->map(fn ($date) => $date->date_debut->isoFormat('D MMM YYYY'))->unique()->implode(', ')
        : $evenement->date_event?->isoFormat('D MMM YYYY');
    $descriptionPartage = Str::limit(implode(' · ', array_filter([
        $evenement->categorie ? ucfirst($evenement->categorie) : null,
        $datesPartage,
        $evenement->lieu,
        $evenement->description ? Str::squish(strip_tags($evenement->description)) : null,
    ])), 160);
@endphp
@section('og_description', $descriptionPartage ?: (Str::ucfirst($evenement->getTextes()['acheter']) . ' pour ' . $evenement->titre . ' sur PaxEvent.'))
@section('og_url', route('evenements.public.show', $evenement))
@section('og_image', $evenement->image ? asset('storage/' . $evenement->image) : asset('images/og-image.png'))
@section('og_image_alt', $evenement->titre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('evenements.public') }}">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($evenement->titre, 40) }}</li>
@endsection

@section('content')
@php
    $placesRestantes = $placesRestantes ?? max(0, $evenement->capacite - $evenement->quota_vendu);
    $placesRestantesTotales = $placesRestantesTotales ?? $placesRestantes;
    $estComplet = $estComplet ?? $placesRestantes <= 0;
    $remplissage = (int) round($evenement->tauxRemplissage());
    $textes = $evenement->getTextes();
    $multiJour = $evenement->estMultiJour();
    $nbJours = max(1, $evenement->dates->count());
    $fmtPrix = fn ($prix) => (float) $prix <= 0 ? 'Gratuit' : number_format((float) $prix, 0, ',', ' ').' F';

    // Lignes du formulaire d'achat : un tarif par jour (multijours) ou les
    // tarifs de l'événement (un jour), puis les packs valables tous les jours.
    $lignesAchat = $tarifs->map(function ($tarif) use ($evenement, $multiJour, $nbJours, $fmtPrix) {
        $stock = $tarif->quantite_disponible === null
            ? PHP_INT_MAX
            : max(0, (int) $tarif->quantite_disponible - (int) $tarif->quantite_vendue);
        $pack = $multiJour && $tarif->evenement_date_id === null;

        return [
            'id' => $tarif->id,
            'nom' => $tarif->nom,
            'sous' => $tarif->jour?->date_debut?->isoFormat('D MMM'),
            'heure' => $tarif->jour?->date_debut?->format('H:i'),
            'prix' => (float) $tarif->prix,
            'prixLabel' => $fmtPrix($tarif->prix),
            'duree' => $pack ? $nbJours.' jours' : null,
            'restantes' => max(0, min($evenement->placesRestantesPourTarif($tarif), $stock)),
            'pack' => $pack,
            'vip' => $pack && \App\Models\Evenement::estPackVip($tarif),
            'economie' => $pack ? $evenement->economiePack($tarif) : null,
            'ordre' => $tarif->jour?->date_debut?->timestamp ?? 0,
        ];
    });
    $lignesJours = $lignesAchat->reject(fn ($ligne) => $ligne['pack'])->sortBy('ordre')->values();
    $lignesPacks = $lignesAchat->filter(fn ($ligne) => $ligne['pack'])->values();
    // Tarifs complets regroupés en bas, avec leur bouton « Liste d'attente »
    $lignesComplets = $lignesJours->merge($lignesPacks)->filter(fn ($ligne) => $ligne['restantes'] <= 0)->values();
    $lignesJours = $lignesJours->filter(fn ($ligne) => $ligne['restantes'] > 0)->values();
    $lignesPacks = $lignesPacks->filter(fn ($ligne) => $ligne['restantes'] > 0)->values();
    $quantitesInitiales = collect(old('lignes', []))->map(fn ($q) => (int) $q)->filter()->all();

    $autresEvenements = App\Models\Evenement::query()
        ->where('statut', 'publié')
        ->where('date_event', '>=', now())
        ->where('id', '!=', $evenement->id)
        ->orderBy('date_event', 'asc')
        ->limit(3)
        ->get();
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
                        @if($evenement->dates && $evenement->dates->count() > 1)
                            <span><i class="bi bi-calendar3"></i> {{ $evenement->dates->map(fn($d) => $d->date_debut->isoFormat('D MMM') . ' à ' . $d->date_debut->format('H:i'))->implode(' · ') }}</span>
                        @else
                            <span><i class="bi bi-calendar3"></i> {{ $evenement->date_event->isoFormat('D MMM YYYY') }}</span>
                            <span><i class="bi bi-clock"></i> {{ $evenement->date_event->format('H:i') }}</span>
                        @endif
                        <span><i class="bi bi-geo-alt"></i> {{ $evenement->lieu }}</span>
                        @if(!$estComplet)
                            <span><i class="bi bi-people"></i> {{ number_format($placesRestantesTotales, 0, ',', ' ') }} places</span>
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
            <!-- Colonne gauche : Description + Achat -->
            <div class="col-lg-8 show-event-content">

                @if($evenement->co_parrainage_actif && !$evenement->gratuit)
                    <div class="d-lg-none mb-3" style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;background:linear-gradient(90deg,rgba(254,213,20,0.12),rgba(84,38,128,0.08));border:1px solid rgba(254,213,20,0.5);border-radius:12px;padding:0.55rem 0.7rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
                            <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#542680,#9972B0);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.9rem;"><i class="bi bi-share-fill"></i></div>
                            <div style="min-width:0;">
                                <div style="font-weight:700;font-size:0.8rem;color:#211C31;line-height:1.1;">Co-parrainage activé</div>
                                <div style="font-size:0.72rem;color:#6c757d;line-height:1.1;">{{ rtrim(rtrim(number_format((float) $evenement->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} % / billet</div>
                            </div>
                        </div>
                        <a href="{{ auth('affiliate')->check() ? route('affiliate.liens') : route('affiliate.register') }}" style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.38rem 0.6rem;border-radius:8px;background:linear-gradient(135deg,#542680,#9972B0);color:#fff;text-decoration:none;font-weight:700;font-size:0.72rem;white-space:nowrap;flex-shrink:0;">
                            <i class="bi bi-person-plus-fill"></i> Devenir affilié
                        </a>
                    </div>
                @endif

                <!-- Description -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-info-circle"></i> Description</h5>
                    @if($evenement->description)
                        <p class="show-card-text">{{ $evenement->description }}</p>
                    @else
                        <p class="show-card-text text-muted">Aucune description disponible.</p>
                    @endif
                </div>

                <!-- Achat -->
                <div class="show-purchase-wrap" id="reserver">
                    @if(($venteCloturee ?? false) || ($evenementPasse ?? false))
                        <div class="show-card text-center py-4">
                            <div class="show-lock-icon"><i class="bi bi-lock-fill"></i></div>
                            <h5 style="color:var(--danger); font-weight:700; margin-bottom:0.25rem;">{{ $textes['cloturee'] }}</h5>
                            <p class="show-card-text mb-0">
                                @if($evenementPasse ?? false)
                                    Cet événement a déjà eu lieu.
                                @else
                                    {{ $textes['type'] === 'spectacle' ? 'Les ventes sont clôturées pour cet événement.' : 'Les inscriptions sont clôturées pour cet événement.' }}
                                @endif
                            </p>
                        </div>
                    @else
                    <div class="show-card">
                        @if($evenement->gratuit)
                        <div class="show-ticket-header">
                            <div class="show-ticket-icon"><i class="bi bi-ticket-perforated"></i></div>
                            <div>
                                <h5 class="fw-bold mb-0">{{ $textes['participer_gratuit'] }}</h5>
                                <small class="text-muted">{{ number_format($placesRestantesTotales, 0, ',', ' ') }} {{ $textes['places'] }}@if($multiJour) · valable tous les jours @endif</small>
                            </div>
                        </div>
                        <hr class="my-3">
                        <form action="{{ route('evenements.achat', $evenement) }}" method="POST">
                            @csrf
                            <input type="hidden" name="gratuit" value="1">
                            <input type="hidden" name="quantite" id="hiddenQuantiteG" value="1">
                            <div class="mb-3">
                                <label class="show-label">Nombre de places</label>
                                <div class="show-qty">
                                    <button type="button" id="qtyMinusG">-</button>
                                    <input type="number" id="quantiteInputG" value="1" min="1" max="{{ max(1, $placesRestantes) }}" readonly>
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
                            <div class="mb-3">
                                <label class="show-label">N° WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="show-input" name="whatsapp_acheteur" value="{{ old('whatsapp_acheteur') }}" placeholder="+229 XX XX XX XX" required>
                            </div>
                            <div class="show-total">
                                <span class="fw-bold">{{ $textes['billet_pluriel'] }}</span>
                                <span class="show-total-price">Gratuit</span>
                            </div>
                            <button type="submit" class="show-btn show-btn-primary" {{ $estComplet ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle me-2"></i> {{ $textes['acheter'] }}
                            </button>
                            <p class="show-secure"><i class="bi bi-check-circle me-1" style="color:var(--violet);"></i> Réservation gratuite — Ticket PDF reçu par email</p>
                        </form>
                        @else
                        <form action="{{ route('evenements.achat', $evenement) }}" method="POST" id="formAchat" novalidate>
                            @csrf

                            <div class="show-ticket-header">
                                <div class="show-ticket-icon"><i class="bi bi-ticket-perforated"></i></div>
                                <div>
                                    <h5 class="fw-bold mb-0">{{ $textes['acheter'] }}</h5>
                                    <small class="text-muted">{{ number_format($placesRestantesTotales, 0, ',', ' ') }} {{ $textes['places'] }}</small>
                                </div>
                            </div>
                            <hr class="my-3">

                            <label class="show-label">Choisissez vos billets</label>
                            @if($lignesAchat->isEmpty())
                                <div class="alert alert-danger py-2" style="font-size:0.85rem;">Aucun tarif disponible</div>
                            @endif

                            @foreach(['' => $lignesJours, 'Packs' => $lignesPacks, 'Complets' => $lignesComplets] as $titreGroupe => $groupe)
                                @if($groupe->isNotEmpty() && $titreGroupe !== '')
                                    <div class="achat-section" @if($lignesJours->isEmpty() && ($titreGroupe === 'Packs' || $lignesPacks->isEmpty())) style="margin-top:0;" @endif>{{ $titreGroupe }}</div>
                                @endif
                                @foreach($groupe as $ligne)
                                    <div class="achat-ligne {{ $ligne['pack'] ? 'achat-pack' : '' }} {{ $ligne['vip'] ? 'achat-vip' : '' }}"
                                         data-tarif="{{ $ligne['id'] }}" data-prix="{{ $ligne['prix'] }}" data-max="{{ $ligne['restantes'] }}" data-nom="{{ $ligne['nom'] }}">
                                        <div class="achat-info">
                                            <div class="achat-l1"><span class="achat-nom">{{ $ligne['nom'] }}</span> <span class="achat-sep">·</span> <span class="achat-montant">{{ $ligne['prixLabel'] }}@if($ligne['duree'])<span class="d-none d-sm-inline"> / {{ $ligne['duree'] }}</span>@endif</span></div>
                                            @if($ligne['economie'] || $ligne['restantes'] <= 0)
                                                <div class="achat-l2">
                                                    @if($ligne['economie'])<span class="achat-eco" style="margin:0 .2rem 0 0;">Économisez {{ number_format($ligne['economie'], 0, ',', ' ') }} F</span>@endif
                                                    @if($ligne['restantes'] <= 0)<span class="achat-complet">Épuisé</span>@endif
                                                </div>
                                            @endif
                                        </div>
                                        @if($ligne['restantes'] > 0)
                                            <div class="achat-step">
                                                <button type="button" data-moins aria-label="Retirer un billet {{ $ligne['nom'] }}">−</button>
                                                <span data-qte aria-live="polite">0</span>
                                                <button type="button" data-plus aria-label="Ajouter un billet {{ $ligne['nom'] }}">+</button>
                                            </div>
                                        @else
                                            <button type="button" class="achat-attente" data-attente data-bs-toggle="modal" data-bs-target="#modalListeAttente">Liste d'attente</button>
                                        @endif
                                        <input type="hidden" name="lignes[{{ $ligne['id'] }}]" value="0" data-input>
                                    </div>
                                @endforeach
                            @endforeach

                            <hr>
                            <div class="mb-3">
                                <label class="show-label" for="nom_acheteur">Prénom et Nom <span class="text-danger">*</span></label>
                                <input type="text" class="show-input" id="nom_acheteur" name="nom_acheteur" value="{{ old('nom_acheteur') }}" placeholder="Ex: Kofi Mensah" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label" for="email_acheteur">Email <span class="text-danger">*</span></label>
                                <input type="email" class="show-input" id="email_acheteur" name="email_acheteur" value="{{ old('email_acheteur') }}" placeholder="votre@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="show-label" for="whatsapp_acheteur">N° WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="show-input" id="whatsapp_acheteur" name="whatsapp_acheteur" value="{{ old('whatsapp_acheteur') }}" placeholder="+229 XX XX XX XX" required>
                            </div>
                            <div class="mb-3" id="blocPromo">
                                <label class="show-label" for="code_promo">Code promo <span class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="text" class="show-input" id="code_promo" name="code_promo" value="{{ old('code_promo') }}" placeholder="PROMO-XXXXXX" style="text-transform:uppercase;">
                            </div>
                            <div class="show-total" id="blocTotal">
                                <span class="fw-bold" data-nombre>Aucun billet choisi</span>
                                <span class="show-total-price" data-total>0 FCFA</span>
                            </div>
                            <button type="submit" class="show-btn show-btn-primary" id="btnPayer" {{ $lignesAchat->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-shield-lock me-2"></i> <span data-libelle>Payer avec FedaPay</span>
                            </button>
                            <p class="show-secure"><i class="bi bi-shield-check me-1" style="color:var(--violet);"></i> Paiement 100% sécurisé — Ticket PDF envoyé par email</p>
                        </form>

                        {{-- Barre du panier, fixée en bas de l'écran sur téléphone --}}
                        <div class="achat-barre" id="barrePanier">
                            <div>
                                <div class="achat-barre-n" data-nombre>Aucun billet choisi</div>
                                <div class="achat-barre-t" data-total>0 FCFA</div>
                            </div>
                            <button type="button" class="show-btn show-btn-primary" id="btnContinuerBarre" style="width:auto;">Continuer <i class="bi bi-arrow-down ms-1"></i></button>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Colonne droite : Co-parrainage + Programme + Partager + Question + Lieu + Autres événements -->
            <div class="col-lg-4 show-event-sidebar">

                @if($evenement->co_parrainage_actif && !$evenement->gratuit)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;margin-bottom:0.9rem;background:linear-gradient(90deg,rgba(254,213,20,0.12),rgba(84,38,128,0.08));border:1px solid rgba(254,213,20,0.5);border-radius:12px;padding:0.55rem 0.7rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
                            <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#542680,#9972B0);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.9rem;"><i class="bi bi-share-fill"></i></div>
                            <div style="min-width:0;">
                                <div style="font-weight:700;font-size:0.8rem;color:#211C31;line-height:1.1;">Co-parrainage activé</div>
                                <div style="font-size:0.72rem;color:#6c757d;line-height:1.1;">{{ rtrim(rtrim(number_format((float) $evenement->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} % / billet</div>
                            </div>
                        </div>
                        <a href="{{ auth('affiliate')->check() ? route('affiliate.liens') : route('affiliate.register') }}" style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.38rem 0.6rem;border-radius:8px;background:linear-gradient(135deg,#542680,#9972B0);color:#fff;text-decoration:none;font-weight:700;font-size:0.72rem;white-space:nowrap;flex-shrink:0;">
                            <i class="bi bi-person-plus-fill"></i> Devenir affilié
                        </a>
                    </div>
                @endif

                @if($evenement->dates->count() > 1)
                    <!-- Programme -->
                    <div class="show-card show-card-left">
                        <h5 class="show-card-title"><i class="bi bi-calendar3"></i> Programme</h5>
                        <ul class="list-unstyled mb-0">
                            @foreach($evenement->dates as $jour)
                                @php $tarifDuJour = $tarifs->firstWhere('evenement_date_id', $jour->id); @endphp
                                <li class="d-flex justify-content-between align-items-center gap-2 py-2 {{ $loop->last ? '' : 'border-bottom' }}" style="font-size:0.9rem;">
                                    <span><strong style="color:var(--violet);">{{ $jour->libelle() }}</strong> {{ $jour->date_debut->isoFormat('ddd D MMM · HH:mm') }}</span>
                                    @if($tarifDuJour && (float) $tarifDuJour->prix <= 0)
                                        <span class="badge" style="background:#b2e0d6;color:#1d4a3f;">Gratuit</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Partager -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-share"></i> Partager</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="show-share-btn" onclick="shareEvent()" title="Partager">
                            <i class="bi bi-share-fill"></i>
                        </button>
                        <button type="button" class="show-share-btn" onclick="copyLink()" title="Copier le lien">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="show-share-btn" title="Partager sur Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($evenement->titre . ' - ' . url()->current()) }}" target="_blank" class="show-share-btn" title="Partager sur WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Contacter -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-envelope"></i> Une question ?</h5>
                    <p class="show-card-text" style="margin-bottom:1rem;">Vous avez une question spécifique sur cet événement ? Contactez directement l'organisateur.</p>
                    <button type="button" class="show-btn show-btn-primary" data-bs-toggle="modal" data-bs-target="#contactOrganisateurModal">
                        <i class="bi bi-envelope me-2"></i> Contacter l'organisateur
                    </button>
                </div>

                <!-- Lieu -->
                <div class="show-card show-card-left">
                    <h5 class="show-card-title"><i class="bi bi-pin-map"></i> Lieu</h5>
                    <div class="show-map">
                        <iframe id="carteLieu" src="https://www.google.com/maps?q={{ urlencode($evenement->lieu) }}&output=embed" width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy" title="Carte : {{ $evenement->lieu }}"></iframe>
                    </div>
                    <p class="show-card-text mt-2 mb-2" style="font-size:0.85rem;" id="lieuTexte">{{ $evenement->lieu }}</p>
                    <button type="button" class="show-btn show-btn-primary" id="btnItineraire" data-lieu="{{ $evenement->lieu }}">
                        <i class="bi bi-sign-turn-right me-2"></i> Itinéraire
                    </button>
                    <div id="itineraireActions" class="gap-2" style="display:none;">
                        <a id="lienNavigation" class="show-btn show-btn-primary" href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($evenement->lieu) }}" target="_blank" rel="noopener">
                            <i class="bi bi-cursor me-2"></i> Lancer la navigation
                        </a>
                        <button type="button" class="show-btn" id="btnMasquerItineraire" style="width:auto;">Masquer</button>
                    </div>
                </div>

                <!-- Autres événements -->
                @if($autresEvenements->isNotEmpty())
                    <div class="show-card show-card-left">
                        <h6 class="fw-bold mb-3" style="color:#211C31;"><i class="bi bi-calendar-event me-2" style="color:var(--violet);"></i>Autres événements</h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach($autresEvenements as $autre)
                                <a href="{{ route('evenements.public.show', $autre) }}" class="show-other-card">
                                    @if($autre->image)
                                        <img src="{{ asset('storage/' . $autre->image) }}" alt="">
                                    @else
                                        <div class="show-other-placeholder"><i class="bi bi-calendar-event"></i></div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="show-other-title">{{ $autre->titre }}</div>
                                        <div class="show-other-date"><i class="bi bi-calendar3 me-1"></i>{{ $autre->date_event->isoFormat('D MMM YYYY') }}</div>
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


    {{-- Liste d'attente d'un tarif complet --}}
    <div class="modal fade" id="modalListeAttente" tabindex="-1" aria-labelledby="titreListeAttente" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="formListeAttente" style="border-radius:16px;" novalidate>
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="titreListeAttente">Liste d'attente</h5>
                        <small class="text-muted" data-attente-tarif></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Ce billet est complet. Laissez votre email : vous serez prévenu(e) dès que des places seront ajoutées.</p>
                    <input type="hidden" name="tarif_id">
                    <div class="mb-3">
                        <label class="show-label" for="attente_email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="show-input" id="attente_email" name="email" placeholder="votre@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="show-label" for="attente_quantite">Nombre de places souhaitées <span class="text-danger">*</span></label>
                        <input type="number" class="show-input" id="attente_quantite" name="quantite" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="show-label" for="attente_nom">Prénom et Nom <span class="text-muted fw-normal">(facultatif)</span></label>
                        <input type="text" class="show-input" id="attente_nom" name="nom" placeholder="Ex: Kofi Mensah">
                    </div>
                    <div class="mb-1">
                        <label class="show-label" for="attente_whatsapp">N° WhatsApp <span class="text-muted fw-normal">(facultatif)</span></label>
                        <input type="tel" class="show-input" id="attente_whatsapp" name="whatsapp" placeholder="+229 XX XX XX XX">
                    </div>
                    <div class="text-danger small mt-2 d-none" data-attente-erreur role="alert"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="show-btn show-btn-primary" data-attente-envoyer>Envoyer</button>
                </div>
            </form>
        </div>
    </div>

<style>
.achat-section{font-size:.78rem;font-weight:700;color:var(--violet);text-transform:uppercase;letter-spacing:.03em;margin:1.1rem 0 .5rem}
.achat-ligne{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:1rem;align-items:center;border:1.5px solid #e6e3eb;border-radius:14px;padding:.8rem 1rem;margin-bottom:.5rem;background:#fff;transition:border-color .15s}
.achat-ligne.is-on{border-color:#542680;background:#faf7fc}
.achat-pack{background:#faf7fc;border-color:#cdbbe0}
.achat-vip{background:#211C31;border-color:#211C31;color:#fff}
.achat-vip .achat-sous,.achat-vip .achat-bas,.achat-vip .achat-complet{color:#e4dfea}
.achat-vip .achat-montant{color:#FED514}
.achat-vip.is-on{border-color:#FED514;background:#211C31}
.achat-nom{font-weight:700}
.achat-l1,.achat-l2{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.achat-l1{font-size:1.3rem}
.achat-l2{font-size:.86rem;color:#6b6474;margin-top:.2rem}
.achat-sep{color:#b7b0c0;margin:0 .15rem}
.achat-l1 .achat-montant{display:inline}
.achat-step{display:inline-flex;align-items:center;gap:.6rem}
@media (max-width:575.98px){.achat-ligne{gap:.5rem;padding:.7rem .75rem}.achat-step{gap:.35rem}.achat-step button{width:32px!important;height:32px!important}.achat-l1{font-size:1.15rem}.achat-l2{font-size:.78rem}.achat-sep{margin:0 .05rem}#reserver .show-card{padding-left:.85rem;padding-right:.85rem}.achat-eco{font-size:.66rem;padding:.1rem .4rem;margin-left:.2rem}}
.achat-step button{width:36px;height:36px;border-radius:50%;border:0;background:#3b3a8f;color:#fff;font-size:1.15rem;line-height:1;display:flex;align-items:center;justify-content:center;padding:0}
.achat-step button[data-moins]{background:#fff;border:1.5px solid #d9d4df;color:#6b6474}
.achat-step button:disabled{background:#fff;border:1.5px solid #e6e3eb;color:#c9c3d1}
.achat-step span{min-width:18px;text-align:center;font-weight:700;color:#6b6474}
.achat-vip .achat-step span,.achat-vip .achat-l2{color:#e4dfea}
.achat-vip .achat-step button[data-moins]{background:transparent;border-color:#6b6474;color:#e4dfea}
.achat-sous{font-size:.8rem;color:#6b6474}
.achat-eco{display:inline-block;margin-left:.35rem;background:#FED514;color:#211C31;font-size:.72rem;font-weight:700;padding:.15rem .55rem;border-radius:99px}
.achat-prix{text-align:right}
.achat-montant{font-weight:700;color:#542680;white-space:nowrap}
.achat-bas{font-weight:600}
.achat-complet{font-weight:600;color:#a5322a}
.achat-attente{border:0;background:none;color:#542680;font-weight:600;text-decoration:underline;font-size:.85rem;padding:.6rem 0}
.achat-vip .achat-attente{color:#FED514}
.achat-attente{border:1.5px solid #542680;border-radius:99px;text-decoration:none;padding:.4rem .9rem;white-space:nowrap}
.achat-attente.is-inscrit{border-color:#428c79;background:#e5f4e4;color:#245a4c}
.achat-vip .achat-attente{border-color:#FED514}
.achat-complet-ligne{opacity:.85}
.achat-barre{display:none}
@media (max-width:991.98px){
  .achat-barre.is-visible{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:1030;background:#fff;border-top:1px solid #e6e3eb;padding:.75rem 1rem;align-items:center;justify-content:space-between;gap:1rem;box-shadow:0 -6px 18px rgba(33,28,49,.08)}
  body.achat-barre-ouverte{padding-bottom:84px}
}
.achat-barre-n{font-size:.75rem;color:#6b6474}
.achat-barre-t{font-size:1.1rem;font-weight:800;color:#211C31}
</style>
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
                <form action="{{ route('evenements.contacter-organisateur', $evenement) }}" method="POST">
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
    padding: 0.75rem 0.9rem;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #ece8f0;
    text-decoration: none;
    transition: background 0.2s;
}
.show-other-card:hover { background: #faf7fc; border-color: #cdbbe0; }
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

@media (min-width: 992px) {
    .show-event-sidebar {
        position: sticky;
        top: 90px;
        align-self: start;
    }
}
@media (max-width: 991px) {
    .show-event-sidebar { position: static; }
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

/* ===== BANDEAU CO-PARRAINAGE ===== */
.co-parrain-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(254,213,20,0.14), rgba(84,38,128,0.06));
    border: 1px solid rgba(254,213,20,0.55) !important;
}
.co-parrain-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-size: 1.2rem;
}
.co-parrain-body { flex: 1; min-width: 200px; }
.co-parrain-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #211C31;
    margin: 0 0 0.2rem;
}
.co-parrain-text {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.6;
}
.co-parrain-text strong { color: #542680; }
.co-parrain-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 1.25rem;
    border-radius: 10px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #542680, #9972B0);
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.25s;
}
.co-parrain-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(84,38,128,0.35);
    color: #fff;
}
@media (max-width: 575px) {
    .co-parrain-banner { flex-direction: column; align-items: flex-start; }
    .co-parrain-btn { width: 100%; justify-content: center; }
}
@media (max-width: 767px) {
    .show-hero { padding: 2.5rem 0 2rem; }
    .show-title { font-size: 1.5rem; }
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
    var data = {
        title: @json($evenement->titre),
        text: @json($evenement->titre . ' - ' . ($evenement->date_event ? $evenement->date_event->isoFormat('D MMM YYYY') : '') . ($evenement->lieu ? ' à ' . $evenement->lieu : '') . ($evenement->description ? ' - ' . strip_tags(Str::limit($evenement->description, 120)) : '')),
        url: window.location.href
    };
    if (navigator.share) {
        navigator.share(data).catch(function (error) {
            if (error && error.name === 'AbortError') return;
            copyLink();
        });
        return;
    }
    copyLink();
}

function showToast(message, type) {
    const el = document.getElementById('shareToast');
    el.querySelector('.toast-body').textContent = message;
    el.className = 'toast align-items-center text-bg-' + type + ' border-0';
    const toast = bootstrap.Toast.getOrCreateInstance(el, { autohide: true, delay: 3000 });
    toast.show();
}

// Itinéraire : trajet affiché dans la carte depuis la position du visiteur
// (demandée seulement au clic), puis lien de navigation Google Maps.
document.addEventListener('DOMContentLoaded', function () {
    const bouton = document.getElementById('btnItineraire');
    if (!bouton) return;
    const carte = document.getElementById('carteLieu');
    const actions = document.getElementById('itineraireActions');
    const lien = document.getElementById('lienNavigation');
    const lieu = bouton.dataset.lieu;
    const srcInitial = carte.src;
    const libelleInitial = bouton.innerHTML;
    const urlNavigation = (origine) => 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lieu)
        + (origine ? '&origin=' + encodeURIComponent(origine) : '');
    const afficherActions = () => { bouton.style.display = 'none'; actions.style.display = 'flex'; };

    bouton.addEventListener('click', function () {
        if (!navigator.geolocation) { lien.href = urlNavigation(); afficherActions(); return; }
        bouton.disabled = true;
        bouton.textContent = 'Localisation…';
        navigator.geolocation.getCurrentPosition(function (position) {
            const origine = position.coords.latitude + ',' + position.coords.longitude;
            carte.src = 'https://maps.google.com/maps?saddr=' + encodeURIComponent(origine) + '&daddr=' + encodeURIComponent(lieu) + '&output=embed';
            lien.href = urlNavigation(origine);
            bouton.disabled = false;
            bouton.innerHTML = libelleInitial;
            afficherActions();
        }, function () {
            // Position refusée ou indisponible : on propose l'itinéraire dans Google Maps.
            lien.href = urlNavigation();
            bouton.disabled = false;
            bouton.innerHTML = libelleInitial;
            afficherActions();
        }, { timeout: 10000, maximumAge: 300000 });
    });

    document.getElementById('btnMasquerItineraire').addEventListener('click', function () {
        carte.src = srcInitial;
        actions.style.display = 'none';
        bouton.style.display = '';
    });
});

@if($evenement->gratuit)
document.addEventListener('DOMContentLoaded', function() {
    const qty = document.getElementById('quantiteInputG');
    const hiddenQuantiteG = document.getElementById('hiddenQuantiteG');
    document.getElementById('qtyMinusG').addEventListener('click', function() {
        const v = parseInt(qty.value);
        if (v > 1) { qty.value = v - 1; hiddenQuantiteG.value = v - 1; }
    });
    document.getElementById('qtyPlusG').addEventListener('click', function() {
        const v = parseInt(qty.value);
        if (v < {{ max(1, $placesRestantes) }}) { qty.value = v + 1; hiddenQuantiteG.value = v + 1; }
    });
});
@else
// Panier : un compteur par billet, coordonnées juste en dessous, un seul récapitulatif ensuite.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formAchat');
    if (!form) return;

    const QUANTITES_INITIALES = @json((object) $quantitesInitiales);
    const lignes = Array.from(form.querySelectorAll('[data-tarif]'));
    const btnPayer = document.getElementById('btnPayer');
    const libelle = btnPayer.querySelector('[data-libelle]');
    const barre = document.getElementById('barrePanier');
    const blocPromo = document.getElementById('blocPromo');
    const blocTotal = document.getElementById('blocTotal');

    const fmt = (n) => n <= 0 ? 'Gratuit' : new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
    const quantite = (ligne) => parseInt(ligne.querySelector('[data-input]').value, 10) || 0;

    function definirQuantite(ligne, valeur) {
        const max = parseInt(ligne.dataset.max, 10) || 0;
        const v = Math.max(0, Math.min(max, valeur));
        ligne.querySelector('[data-input]').value = v;
        const affichage = ligne.querySelector('[data-qte]');
        if (affichage) affichage.textContent = v;
        const moins = ligne.querySelector('[data-moins]');
        const plus = ligne.querySelector('[data-plus]');
        if (moins) moins.disabled = v <= 0;
        if (plus) plus.disabled = v >= max;
        ligne.classList.toggle('is-on', v > 0);
    }

    function rendre() {
        const nombre = lignes.reduce((s, l) => s + quantite(l), 0);
        const total = lignes.reduce((s, l) => s + quantite(l) * parseFloat(l.dataset.prix), 0);
        const texteNombre = nombre === 0 ? 'Aucun billet choisi' : nombre + (nombre > 1 ? ' billets' : ' billet');
        document.querySelectorAll('[data-nombre]').forEach((el) => { el.textContent = texteNombre; });
        document.querySelectorAll('[data-total]').forEach((el) => { el.textContent = nombre === 0 ? '0 FCFA' : fmt(total); });

        libelle.textContent = nombre > 0 && total <= 0 ? 'Réserver gratuitement' : 'Payer avec FedaPay';

        const visible = nombre > 0;
        barre.classList.toggle('is-visible', visible);
        document.body.classList.toggle('achat-barre-ouverte', visible);
    }

    form.addEventListener('click', function (e) {
        const bouton = e.target.closest('button');
        if (!bouton) return;
        const ligne = bouton.closest('[data-tarif]');
        if (bouton.hasAttribute('data-plus')) { definirQuantite(ligne, quantite(ligne) + 1); }
        if (bouton.hasAttribute('data-moins')) { definirQuantite(ligne, quantite(ligne) - 1); }
        rendre();
    });

    document.getElementById('btnContinuerBarre').addEventListener('click', function () {
        document.getElementById('nom_acheteur').scrollIntoView({ behavior: 'smooth', block: 'center' });
        document.getElementById('nom_acheteur').focus({ preventScroll: true });
    });

    form.addEventListener('submit', function (e) {
        if (lignes.every((l) => quantite(l) === 0)) {
            e.preventDefault();
            alert('Choisissez au moins un billet.');
            return;
        }
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        btnPayer.disabled = true;
        libelle.textContent = 'Traitement…';
    });

    // État initial : quantités restaurées après une erreur serveur.
    lignes.forEach((l) => definirQuantite(l, QUANTITES_INITIALES[l.dataset.tarif] || 0));
    rendre();
});

// Liste d'attente : fenêtre email + nombre de places, puis « Inscrit ».
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalListeAttente');
    const formAttente = document.getElementById('formListeAttente');
    if (!modal || !formAttente) return;

    const URL_ATTENTE = @json(route('evenements.liste-attente', $evenement));
    const CLE = (id) => 'pax-attente-{{ $evenement->id }}-' + id;
    const erreur = formAttente.querySelector('[data-attente-erreur]');
    const envoyer = formAttente.querySelector('[data-attente-envoyer]');
    let boutonCourant = null;

    const marquerInscrit = (bouton) => {
        bouton.textContent = 'Inscrit ✓';
        bouton.classList.add('is-inscrit');
        bouton.disabled = true;
        bouton.removeAttribute('data-bs-toggle');
    };
    document.querySelectorAll('[data-attente]').forEach((bouton) => {
        try { if (localStorage.getItem(CLE(bouton.closest('[data-tarif]').dataset.tarif))) marquerInscrit(bouton); } catch (e) { /* stockage indisponible */ }
    });

    modal.addEventListener('show.bs.modal', function (e) {
        boutonCourant = e.relatedTarget;
        const ligne = boutonCourant ? boutonCourant.closest('[data-tarif]') : null;
        if (!ligne) return;
        formAttente.querySelector('[name="tarif_id"]').value = ligne.dataset.tarif;
        modal.querySelector('[data-attente-tarif]').textContent = ligne.dataset.nom;
        erreur.classList.add('d-none');
    });

    formAttente.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!formAttente.checkValidity()) { formAttente.reportValidity(); return; }
        envoyer.disabled = true;
        envoyer.textContent = 'Envoi…';
        erreur.classList.add('d-none');
        try {
            const reponse = await fetch(URL_ATTENTE, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: new FormData(formAttente),
            });
            const donnees = await reponse.json().catch(() => ({}));
            if (!reponse.ok) {
                const premiere = donnees.errors ? Object.values(donnees.errors)[0][0] : (donnees.message || 'Envoi impossible, réessayez.');
                throw new Error(reponse.status === 429 ? 'Trop de demandes, réessayez dans une minute.' : premiere);
            }
            const tarifId = formAttente.querySelector('[name="tarif_id"]').value;
            try { localStorage.setItem(CLE(tarifId), '1'); } catch (err) { /* rien */ }
            if (boutonCourant) marquerInscrit(boutonCourant);
            bootstrap.Modal.getOrCreateInstance(modal).hide();
            formAttente.reset();
        } catch (err) {
            erreur.textContent = err.message;
            erreur.classList.remove('d-none');
        } finally {
            envoyer.disabled = false;
            envoyer.textContent = 'Envoyer';
        }
    });
});
@endif
</script>
@endsection
