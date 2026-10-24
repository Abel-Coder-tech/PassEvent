@extends('layouts.affiliate')

@section('title', $evenement->titre . ' — Espace affilié PaxEvent')

@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
    $fmtPct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',');
    $placesRestantes = max(0, (int) $evenement->capacite - (int) $evenement->quota_vendu);
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('affiliate.liens') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Retour à mes liens
            </a>
            <h1 class="h4 fw-bold mb-1 mt-2">{{ $evenement->titre }}</h1>
            <div class="text-muted small d-flex flex-wrap gap-3">
                @if($evenement->date_event)
                    <span><i class="bi bi-calendar3 me-1"></i>{{ $evenement->date_event->format('d/m/Y à H:i') }}</span>
                @endif
                @if($evenement->lieu)
                    <span><i class="bi bi-geo-alt me-1"></i>{{ $evenement->lieu }}</span>
                @endif
                @if($evenement->categorie)
                    <span><i class="bi bi-tag me-1"></i>{{ ucfirst($evenement->categorie) }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($evenement->statut === 'publié')
                <span class="badge bg-success-subtle text-success-emphasis">Co-parrainage actif</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary-emphasis">Événement {{ $evenement->statut }}</span>
            @endif
            @if($lien)
                <span class="badge {{ $lien->actif ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                    {{ $lien->actif ? 'Votre lien est actif' : 'Votre lien est inactif' }}
                </span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card aff-card overflow-hidden mb-4">
                @if($evenement->image)
                    <img src="{{ asset('storage/' . $evenement->image) }}" alt="{{ $evenement->titre }}" class="aff-detail-cover">
                @else
                    <div class="aff-detail-placeholder"><i class="bi bi-calendar-event"></i></div>
                @endif
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> À propos de l'événement</h2>
                    <p class="text-muted mb-4" style="white-space:pre-line;">{{ $evenement->description ?: 'Aucune description fournie par l\'organisateur.' }}</p>

                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Date</div>
                                <div class="value">{{ $evenement->date_event?->format('d/m/Y') ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Heure</div>
                                <div class="value">{{ $evenement->date_event?->format('H:i') ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Lieu</div>
                                <div class="value text-truncate" title="{{ $evenement->lieu }}">{{ $evenement->lieu ?: '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Catégorie</div>
                                <div class="value">{{ $evenement->categorie ? ucfirst($evenement->categorie) : '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Capacité</div>
                                <div class="value">{{ $fmt($evenement->capacite) }} places</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="aff-meta-box">
                                <div class="label">Places restantes</div>
                                <div class="value">{{ $fmt($placesRestantes) }} places</div>
                            </div>
                        </div>
                    </div>

                    @if($evenement->dates->count() > 1)
                        <hr class="my-4">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-calendar-week me-1"></i> Sessions</h3>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($evenement->dates as $date)
                                <span class="badge bg-light text-dark border">{{ $date->date_debut->format('d/m/Y H:i') }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card aff-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h6 fw-bold mb-1"><i class="bi bi-receipt me-1"></i> Note détaillée PaxEvent</h2>
                            <p class="text-muted small mb-0">Décomposition du prix de chaque billet pour cet événement.</p>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="text-muted small">Taux PaxEvent</div>
                            <div class="fw-bold" style="color:var(--violet);">{{ $fmtPct($commissionPax) }} %</div>
                        </div>
                    </div>

                    @if($noteLignes->isEmpty())
                        <div class="alert alert-light border small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            L'organisateur n'a pas encore publié de tarif actif. La note détaillée sera disponible dès que les tarifs seront en ligne.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-3 aff-note-table">
                                <thead>
                                    <tr class="small text-muted">
                                        <th>Tarif</th>
                                        <th class="text-end">Prix du billet</th>
                                        <th class="text-end">Commission PaxEvent</th>
                                        <th class="text-end">Votre commission</th>
                                        <th class="text-end">Net organisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($noteLignes as $ligne)
                                        <tr>
                                            <td class="small fw-semibold">{{ $ligne['nom'] }}</td>
                                            <td class="text-end small">{{ $fmt($ligne['prix']) }} F</td>
                                            <td class="text-end small">
                                                {{ $fmt($ligne['part_pax']) }} F
                                                <span class="text-muted">({{ $fmtPct($commissionPax) }} %)</span>
                                            </td>
                                            <td class="text-end small fw-semibold" style="color:var(--violet);">
                                                {{ $fmt($ligne['part_affilie']) }} F
                                                <span class="text-muted">({{ $fmtPct($commissionAffilie) }} %)</span>
                                            </td>
                                            <td class="text-end small">{{ $fmt($ligne['part_organisateur']) }} F</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-light border small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            La commission de co-parrainage est <strong>à la charge de l'organisateur</strong> : elle est prélevée sur ses recettes et s'ajoute à la commission PaxEvent. Elle n'est jamais facturée à l'acheteur du billet.
                            @if($commissionAffilie <= 0)
                                <br><span class="text-danger">Aucune commission de co-parrainage n'est définie pour cet événement.</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card aff-card">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-person-badge me-1"></i> L'organisateur</h2>
                    @if($organisateur)
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            @if($organisateur->avatar)
                                <img src="{{ asset('storage/' . $organisateur->avatar) }}" alt="{{ $organisateur->nom }}" class="aff-organizer-avatar">
                            @else
                                <div class="aff-organizer-avatar">{{ strtoupper(substr($organisateur->prenom ?: $organisateur->nom, 0, 1)) }}</div>
                            @endif
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold">{{ trim(($organisateur->prenom ?? '') . ' ' . ($organisateur->nom ?? '')) }}</div>
                                @if($organisateur->organisation)
                                    <div class="text-muted small">{{ $organisateur->organisation }}</div>
                                @endif
                                @if($organisateur->description)
                                    <p class="text-muted small mt-2 mb-2" style="white-space:pre-line;">{{ $organisateur->description }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                    <span><i class="bi bi-calendar-check me-1"></i>{{ $nbEvenementsOrganisateur }} événement(s) publié(s)</span>
                                    @if($organisateur->created_at)
                                        <span><i class="bi bi-clock-history me-1"></i>Membre depuis {{ $organisateur->created_at->format('m/Y') }}</span>
                                    @endif
                                    @if($organisateur->type_detail)
                                        <span><i class="bi bi-briefcase me-1"></i>{{ ucfirst($organisateur->type_detail) }}</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-3 small mt-1">
                                    @if($organisateur->email)
                                        <a href="mailto:{{ $organisateur->email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>{{ $organisateur->email }}
                                        </a>
                                    @endif
                                    @if($organisateur->telephone)
                                        <a href="tel:{{ $organisateur->telephone }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $organisateur->telephone }}
                                        </a>
                                    @endif
                                </div>
                                @if($organisateur->facebook_url || $organisateur->instagram_url || $organisateur->tiktok_url || $organisateur->twitter_url || $organisateur->youtube_url || $organisateur->linkedin_url || $organisateur->website_url)
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @if($organisateur->facebook_url)
                                            <a href="{{ $organisateur->facebook_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Facebook"><i class="bi bi-facebook"></i></a>
                                        @endif
                                        @if($organisateur->instagram_url)
                                            <a href="{{ $organisateur->instagram_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Instagram"><i class="bi bi-instagram"></i></a>
                                        @endif
                                        @if($organisateur->tiktok_url)
                                            <a href="{{ $organisateur->tiktok_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="TikTok"><i class="bi bi-tiktok"></i></a>
                                        @endif
                                        @if($organisateur->twitter_url)
                                            <a href="{{ $organisateur->twitter_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="X / Twitter"><i class="bi bi-twitter-x"></i></a>
                                        @endif
                                        @if($organisateur->youtube_url)
                                            <a href="{{ $organisateur->youtube_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="YouTube"><i class="bi bi-youtube"></i></a>
                                        @endif
                                        @if($organisateur->linkedin_url)
                                            <a href="{{ $organisateur->linkedin_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                                        @endif
                                        @if($organisateur->website_url)
                                            <a href="{{ $organisateur->website_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Site web"><i class="bi bi-globe"></i></a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mb-0">Les informations de l'organisateur ne sont pas disponibles.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card aff-card mb-4">
                <div class="card-body p-4">
                    <div class="text-muted small">Votre commission</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <div class="display-6 fw-bold" style="color:var(--violet);">{{ $fmtPct($commissionAffilie) }} %</div>
                        <div class="text-muted small">du prix du billet</div>
                    </div>
                    @if($noteLignes->isNotEmpty())
                        <div class="text-muted small mb-3">
                            Soit {{ $fmt($noteLignes->min('part_affilie')) }} à {{ $fmt($noteLignes->max('part_affilie')) }} FCFA par billet vendu via votre lien.
                        </div>
                    @endif
                    <hr>
                    <div class="aff-stat-row"><span>Clics sur votre lien</span><strong>{{ $lien?->clics ?? 0 }}</strong></div>
                    <div class="aff-stat-row"><span>Commissions générées</span><strong>{{ $statsCommissions['nombre'] }}</strong></div>
                    <div class="aff-stat-row"><span>Total gagné sur cet événement</span><strong>{{ $fmt($statsCommissions['total']) }} F</strong></div>
                    <div class="aff-stat-row"><span>En attente</span><strong>{{ $fmt($statsCommissions['en_attente']) }} F</strong></div>
                    <div class="aff-stat-row"><span>Disponible</span><strong>{{ $fmt($statsCommissions['disponible']) }} F</strong></div>
                    <div class="aff-stat-row"><span>Déjà payé</span><strong>{{ $fmt($statsCommissions['paye']) }} F</strong></div>
                </div>
            </div>

            <div class="card aff-card">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-link-45deg me-1"></i> Vos liens tracés</h2>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Lien d'achat / d'inscription à l'événement</label>
                        @if($lien)
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" value="{{ $lienAchat }}" readonly onclick="this.select()">
                                <button class="btn btn-outline-secondary" type="button" onclick="copierLien(this)" title="Copier le lien">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <a href="{{ $lienAchat }}" target="_blank" rel="noopener" class="btn btn-outline-secondary" title="Tester le lien tracé">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                            <div class="form-text">
                                Code : <span class="fw-semibold">{{ $lien->code }}</span> ·
                                {{ $lien->clics }} clic(s) ·
                                {{ $lien->actif ? 'Actif' : 'Inactif' }}
                            </div>
                            <p class="text-muted small mb-0 mt-1">
                                Chaque visite via ce lien est comptée (une fois par session). Les billets payés sont rattachés à votre lien et génèrent votre commission après la tenue de l'événement.
                            </p>
                            <form method="POST" action="{{ route('affiliate.liens.toggle', $lien) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $lien->actif ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $lien->actif ? 'Désactiver mon lien' : 'Réactiver mon lien' }}
                                </button>
                            </form>
                        @else
                            <p class="text-muted small">Générez votre lien personnel pour suivre les achats et inscriptions à cet événement.</p>
                            <form action="{{ route('affiliate.liens.generer', $evenement) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-violet w-100">
                                    <i class="bi bi-link-45deg me-1"></i> Générer mon lien
                                </button>
                            </form>
                        @endif
                    </div>

                    <hr>

                    <div>
                        <label class="form-label small fw-semibold text-muted mb-1">Lien d'inscription organisateur</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" value="{{ $lienInscription }}" readonly onclick="this.select()">
                            <button class="btn btn-outline-secondary" type="button" onclick="copierLien(this)" title="Copier le lien">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Code : <span class="fw-semibold">{{ $affiliate->code_parrainage }}</span>
                            @if(isset($affiliate->clics_parrainage))
                                · {{ $affiliate->clics_parrainage }} clic(s)
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            Partagez ce lien avec des organisateurs : chaque inscription est rattachée à votre code et vous ouvre 20 % de la commission PaxEvent sur leur premier événement.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .aff-detail-cover { width: 100%; height: 320px; object-fit: cover; display: block; }
    .aff-detail-placeholder {
        width: 100%; height: 240px; display: flex; align-items: center; justify-content: center;
        background: #f0ecf6; color: var(--violet-clair); font-size: 3rem;
    }
    .aff-organizer-avatar {
        width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex: 0 0 auto;
        background: #f0ecf6; color: var(--violet); display: flex; align-items: center;
        justify-content: center; font-weight: 800; font-size: 1.4rem;
    }
    .aff-stat-row {
        display: flex; justify-content: space-between; gap: 1rem;
        padding: .4rem 0; border-bottom: 1px solid #f0eef2; font-size: .84rem;
    }
    .aff-stat-row:last-child { border-bottom: 0; }
    .aff-note-table th { font-weight: 600; white-space: nowrap; }
    .aff-note-table td, .aff-note-table th { padding: .55rem .5rem; }
    .aff-meta-box { background: #f8f7fa; border-radius: 12px; padding: .8rem .9rem; height: 100%; }
    .aff-meta-box .label { font-size: .72rem; color: var(--gris); text-transform: uppercase; letter-spacing: .04em; }
    .aff-meta-box .value { font-weight: 700; font-size: .9rem; }
    @media (max-width: 575.98px) {
        .aff-detail-cover { height: 200px; }
    }
</style>
@endpush

@push('scripts')
<script>
function copierLien(btn) {
    var group = btn.closest('.input-group');
    var input = group ? group.querySelector('input') : null;
    if (!input) return;
    var val = input.value;
    function done() {
        var html = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(function () { btn.innerHTML = html; }, 1500);
    }
    function fallback() {
        input.select();
        input.setSelectionRange(0, 99999);
        try { document.execCommand('copy'); } catch (e) {}
        done();
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(val).then(done).catch(fallback);
    } else {
        fallback();
    }
}
</script>
@endpush
