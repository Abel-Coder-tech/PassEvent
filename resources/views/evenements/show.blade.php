@extends('layouts.app')

@section('title', $evenement->titre)

@section('page-title', $evenement->titre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $evenement->titre }}</li>
@endsection

@section('topbar-actions')
<a href="{{ route('admin.tarifs.index', $evenement->id) }}" class="btn btn-secondary-custom me-2">
    <i class="bi bi-tag me-1"></i> <span class="btn-text">Gérer les tarifs</span>
</a>
@if($evenement->modificationGlobaleVerrouillee())
    <span class="badge text-bg-secondary me-2">Événement verrouillé</span>
@else
    <a href="{{ route('admin.evenements.edit', $evenement->id) }}" class="btn btn-secondary-custom me-2">
        <i class="bi bi-pencil me-1"></i> <span class="btn-text">Modifier</span>
    </a>
@endif
<a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> <span class="btn-text">Retour</span>
</a>
@endsection

@section('content')
<div class="page-content">
    <!-- Stats -->
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--vert);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Tickets vendus</div>
                    <div class="metric-value">{{ $ventes }}</div>
                </div>
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);">🎫</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--teal);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Revenus</div>
                    <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($revenus, 0, ',', ' ') }} F</div>
                </div>
                <div class="metric-icon" style="background: rgba(66,140,121,0.1);">💰</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--menthe);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Places restantes</div>
                    <div class="metric-value">{{ $placesRestantes }}</div>
                </div>
                <div class="metric-icon" style="background: rgba(178,224,214,0.2);">👥</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric-card" style="border-top-color: var(--violet);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="metric-label">Remplissage</div>
                    <div class="metric-value">{{ number_format($tauxRemplissage, 0) }}%</div>
                </div>
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);">📊</div>
            </div>
        </div>
    </div>
</div>

<!-- Détails -->
<div class="row">
    <div class="col-md-8">
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5>Détails de l'événement</h5>
            </div>
            <div class="panel-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Date :</strong> {{ $evenement->date_event->format('d/m/Y à H:i') }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Lieu :</strong> {{ $evenement->lieu }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Capacité :</strong> {{ $evenement->capacite }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Statut :</strong>
                        @php
                            $badgeClass = match($evenement->statut) {
                                'publié' => 'badge-publie',
                                'brouillon' => 'status-brouillon',
                                'terminé' => 'status-termine',
                                'annulé' => 'badge bg-danger',
                                default => 'status-brouillon',
                            };
                        @endphp
                        <span class="status-badge {{ $badgeClass }}">{{ ucfirst($evenement->statut) }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Type :</strong>
                        @php $typeLabel = match($evenement->type_evenement) { 'formation' => 'Formation', 'conference' => 'Conférence', default => 'Spectacle / Soirée' }; @endphp
                        <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet);">{{ $typeLabel }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Ventes :</strong>
                        @if($evenement->ventes_fermees)
                            <span class="badge bg-danger">Fermées</span>
                        @else
                            <span class="badge bg-success">Ouvertes</span>
                        @endif
                    </div>
                </div>
                @if($evenement->description)
                    <div class="mt-3">
                        <strong>Description :</strong>
                        <p class="text-muted mt-1">{{ $evenement->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($jours->count() > 1)
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-calendar3 me-2" style="color: var(--violet);"></i>Jours et jauges</h5>
            </div>
            <div class="panel-card-body p-0">
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th style="font-size: 0.75rem;">Jour</th>
                                <th style="font-size: 0.75rem;">Date</th>
                                <th style="font-size: 0.75rem;">Capacité</th>
                                <th style="font-size: 0.75rem;">Jauge du jour</th>
                                <th style="font-size: 0.75rem;">Occupées</th>
                                <th style="font-size: 0.75rem;">Restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jours as $jour)
                                @php
                                    $capaciteJour = $jour->capaciteEffective();
                                    $occupeesJour = $evenement->occupationJour($jour);
                                @endphp
                                <tr>
                                    <td class="fw-semibold" style="font-size:0.82rem;">
                                        {{ $jour->libelle() }}
                                        @if($jour->estTermine())
                                            <span class="badge bg-secondary ms-1" style="font-size:0.62rem;">Terminé</span>
                                        @endif
                                    </td>
                                    <td style="font-size:0.82rem;">{{ $jour->date_debut->isoFormat('ddd D MMM · HH:mm') }}</td>
                                    <td style="font-size:0.82rem;">
                                        {{ $capaciteJour }}
                                        @if($jour->capacite === null)
                                            <small class="text-muted d-block" style="font-size:0.68rem;">Défaut événement</small>
                                        @endif
                                    </td>
                                    <td style="font-size:0.82rem;">
                                        <form action="{{ route('admin.evenements.jours.capacite', [$evenement->id, $jour->id]) }}" method="POST" class="d-flex gap-1 align-items-center">
                                            @csrf
                                            <input type="number" name="capacite" class="form-control form-control-sm" style="width:90px;" min="1" value="{{ $jour->capacite }}" placeholder="{{ $evenement->capacite }}" title="Laisser vide pour utiliser la capacité de l'événement">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Enregistrer la jauge de ce jour">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td style="font-size:0.82rem;">{{ $occupeesJour }}</td>
                                    <td style="font-size:0.82rem;">
                                        <span class="badge {{ max(0, $capaciteJour - $occupeesJour) > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ max(0, $capaciteJour - $occupeesJour) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-semibold" style="font-size:0.82rem;">
                                <td colspan="5">Total des jours à venir</td>
                                <td>
                                    <span class="badge {{ $placesRestantes > 0 ? 'bg-success' : 'bg-danger' }}">{{ $placesRestantes }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if(!$evenement->gratuit)
        <div class="panel-card mb-4" id="coparrainage">
            <div class="panel-card-header">
                <h5><i class="bi bi-share me-2" style="color: var(--violet);"></i>Co-parrainage</h5>
            </div>
            <div class="panel-card-body">
                @if($evenement->co_parrainage_actif)
                    <p class="mb-2" style="font-size:0.85rem;">
                        <span class="badge bg-success">Activé</span>
                        <span class="badge bg-light text-dark border">
                            {{ rtrim(rtrim(number_format((float) $evenement->co_parrainage_pourcentage, 2, ',', ' '), '0'), ',') }} % / billet
                        </span>
                    </p>
                    <p class="text-muted" style="font-size:0.82rem;">Les affiliés peuvent générer un lien dédié et touchent ce pourcentage sur chaque billet vendu via ce lien.</p>
                    <form action="{{ route('admin.evenements.coparrainage', $evenement->id) }}" method="POST" onsubmit="return confirm('Désactiver le co-parrainage de cet événement ?')">
                        @csrf
                        <button type="submit" class="btn py-2 fw-semibold" style="border-radius:8px; border:1px solid var(--danger); color: var(--danger); background:transparent;">
                            <i class="bi bi-x-circle me-1"></i> Désactiver le co-parrainage
                        </button>
                    </form>
                @else
                    <p class="text-muted" style="font-size:0.82rem;">Ouvrez cet événement aux affiliés : ils touchent un pourcentage du prix de chaque billet vendu via leur lien.</p>
                    <form action="{{ route('admin.evenements.coparrainage', $evenement->id) }}" method="POST" class="d-flex gap-2 align-items-stretch" style="max-width: 420px;">
                        @csrf
                        <div class="input-group">
                            <input type="number" name="co_parrainage_pourcentage" class="form-control @error('co_parrainage_pourcentage') is-invalid @enderror"
                                   min="1" step="0.5" max="{{ max(0, 100 - $evenement->commissionEffective()) }}"
                                   value="{{ old('co_parrainage_pourcentage') }}" placeholder="ex. 10">
                            <span class="input-group-text">%</span>
                        </div>
                        <button type="submit" class="btn text-white fw-semibold flex-shrink-0 px-3" style="white-space:nowrap; border-radius:8px; background: var(--violet);">
                            <i class="bi bi-share me-1"></i> Activer
                        </button>
                    </form>
                    @error('co_parrainage_pourcentage') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @endif

                <div class="alert alert-warning d-flex align-items-start gap-2 mt-3 mb-0" style="border:none;background:#fff8e1;border-radius:12px;padding:0.75rem 1rem;border-left:4px solid #e0a800;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#8b6914;"></i>
                    <small style="color:#6c5b3a;">La commission de co-parrainage est <strong style="color:#8b6914;">à votre charge</strong> (l'organisateur) : elle est prélevée sur vos recettes et s'ajoute à la commission PaxEvent. Elle n'est jamais facturée à l'acheteur du billet.</small>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-tag me-2" style="color: var(--violet);"></i>Tarifs</h5>
            </div>
            <div class="panel-card-body p-0">
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th style="font-size: 0.75rem;">Catégorie</th>
                                <th style="font-size: 0.75rem;">Prix</th>
                                <th style="font-size: 0.75rem;">Dispo.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tarifs as $tarif)
                                @php
                                    $stockTarif = $tarif->quantite_disponible === null
                                        ? null
                                        : max(0, (int) $tarif->quantite_disponible - (int) $tarif->quantite_vendue);
                                    $restantesJour = $evenement->placesRestantesPourTarif($tarif);
                                    $dispoTarif = $stockTarif === null ? $restantesJour : min($stockTarif, $restantesJour);
                                @endphp
                                <tr>
                                    <td style="font-size: 0.82rem;">
                                        {{ $tarif->nom }}
                                        @if($tarif->jour)
                                            <small class="text-muted d-block" style="font-size:0.7rem;">
                                                {{ $tarif->jour->libelle() }} — {{ $tarif->jour->date_debut->isoFormat('ddd D MMM · HH:mm') }}
                                            </small>
                                        @elseif($jours->count() > 1)
                                            <small class="text-muted d-block" style="font-size:0.7rem;">Tous les jours (pack)</small>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.82rem;">{{ number_format($tarif->prix, 0, ',', ' ') }} F</td>
                                    <td style="font-size: 0.82rem;">{{ $dispoTarif }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Codes d'accès scan --}}
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-shield-lock me-2" style="color: var(--vert);"></i>Codes d'accès scan</h5>
            </div>
            <div class="panel-card-body">
                @if($scanAccessCodes->count() > 0)
                    <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                        @foreach($scanAccessCodes as $sac)
                            <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid #f0f0f0;">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <i class="bi bi-key" style="color: {{ $sac->actif ? 'var(--vert)' : '#ccc' }}; font-size: 0.9rem;"></i>
                                    <code style="font-size: 0.82rem; letter-spacing: 1px;">{{ $sac->code }}</code>
                                    <button type="button" class="js-copier-code btn btn-sm p-0" data-code="{{ $sac->code }}" title="Copier le code" style="color: #999; font-size: 0.8rem; line-height: 1;">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                    @if(!$sac->actif)
                                        <span class="badge bg-secondary" style="font-size: 0.6rem;">Inactif</span>
                                    @endif
                                </div>
                                <form action="{{ route('admin.evenements.scan-codes.destroy', [$evenement->id, $sac->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce code ? Les agents utilisant ce code perdront l\'accès au scan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm p-1" style="color: #ccc;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-3" style="font-size: 0.82rem;">Aucun code d'accès généré. Générez un code pour permettre à vos agents de scanner les tickets.</p>
                @endif

                <form action="{{ route('admin.evenements.scan-codes.generate', $evenement->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-vert btn-sm w-100" style="border-radius: 8px;">
                        <i class="bi bi-plus-lg me-1"></i> Générer un code d'accès
                    </button>
                </form>
            </div>
        </div>

        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-toggle-on me-2" style="color: var(--violet);"></i>Gestion des ventes</h5>
            </div>
            <div class="panel-card-body">
                <p class="mb-2" style="font-size:0.85rem;">
                    @if($evenement->ventes_fermees)
                        <span class="badge bg-danger">Ventes fermées</span>
                    @else
                        <span class="badge bg-success">Ventes ouvertes</span>
                    @endif
                </p>
                <form action="{{ route('admin.evenements.fermer-vente', $evenement->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn w-100 py-2 text-white fw-semibold" style="border-radius:8px; background: {{ $evenement->ventes_fermees ? 'var(--vert)' : 'var(--danger)' }};">
                        <i class="bi {{ $evenement->ventes_fermees ? 'bi-unlock' : 'bi-lock' }} me-1"></i>
                        {{ $evenement->ventes_fermees ? 'Rouvrir les ventes' : 'Clôturer les ventes' }}
                    </button>
                </form>
            </div>
        </div>

        @if($evenement->image)
            <div class="panel-card">
                <div class="panel-card-body p-0">
                    <img src="{{ asset('storage/' . $evenement->image) }}" class="img-fluid rounded-bottom" alt="{{ $evenement->titre }}">
                </div>
            </div>
        @endif
@endsection
