@extends('layouts.app')

@section('title', $evenement->titre)

@section('page-title', $evenement->titre)

@section('topbar-actions')
<a href="{{ route('admin.tarifs.index', $evenement->id) }}" class="btn btn-secondary-custom me-2">
    <i class="bi bi-tag me-1"></i> Gérer les tarifs
</a>
<a href="{{ route('admin.evenements.edit', $evenement->id) }}" class="btn btn-secondary-custom me-2">
    <i class="bi bi-pencil me-1"></i> Modifier
</a>
<a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
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
                    @if($evenement->date_fin_vente)
                        <div class="col-md-6 mb-3">
                            <strong>Fin de vente :</strong> {{ $evenement->date_fin_vente->format('d/m/Y à H:i') }}
                        </div>
                    @endif
                </div>
                @if($evenement->description)
                    <div class="mt-3">
                        <strong>Description :</strong>
                        <p class="text-muted mt-1">{{ $evenement->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel-card mb-4">
            <div class="panel-card-header">
                <h5><i class="bi bi-tag me-2" style="color: var(--violet);"></i>Tarifs</h5>
            </div>
            <div class="panel-card-body p-0">
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
                            <tr>
                                <td style="font-size: 0.82rem;">
                                    {{ ucfirst($tarif->categorie) }} / {{ $tarif->type === 'normal' ? 'Standard' : 'VIP' }}
                                    @if($tarif->categorie === 'etudiant')
                                        <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-size: 0.6rem; vertical-align: middle;">Étudiant</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.82rem;">{{ number_format($tarif->prix, 0, ',', ' ') }} F</td>
                                <td style="font-size: 0.82rem;">{{ $tarif->quantite_disponible - $tarif->quantite_vendue }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
