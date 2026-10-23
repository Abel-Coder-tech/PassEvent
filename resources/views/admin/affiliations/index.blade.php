@extends('layouts.app')

@section('title', 'Affiliation')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-share"></i> Affiliation</h5>
            <div class="text-muted small">Affiliés qui promeuvent vos événements et commissions de co-parrainage.</div>
        </div>
        <a href="{{ route('admin.evenements.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar-event"></i> Mes événements
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Affiliés actifs</div>
                    <div class="fs-4 fw-bold">{{ $stats['affilies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Liens de co-parrainage</div>
                    <div class="fs-4 fw-bold">{{ $stats['liens_actifs'] }} <small class="text-muted fs-6">/ {{ $stats['liens'] }}</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Commissions dues</div>
                    <div class="fs-5 fw-bold">{{ number_format($stats['commissions'], 0, ',', ' ') }} F</div>
                    <div class="text-muted small">dont {{ number_format($stats['commissions_en_attente'], 0, ',', ' ') }} F en attente</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Clics générés</div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['clics'], 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-calendar-check" style="color:#7B3FA0;"></i> Événements ouverts au co-parrainage
        </div>
        <div class="card-body p-0">
            @if ($parEvenement->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Événement</th>
                                <th>Date</th>
                                <th class="text-center">Liens</th>
                                <th class="text-end">Commissions</th>
                                <th class="text-end">En attente</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($parEvenement as $ligne)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.evenements.show', $ligne['evenement']) }}" class="text-decoration-none fw-semibold" style="color:#7B3FA0;">
                                            {{ $ligne['evenement']->titre }}
                                        </a>
                                    </td>
                                    <td>{{ $ligne['evenement']->date_event?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-center">{{ $ligne['liens'] }}</td>
                                    <td class="text-end">{{ number_format($ligne['commissions'], 0, ',', ' ') }} F</td>
                                    <td class="text-end">{{ number_format($ligne['en_attente'], 0, ',', ' ') }} F</td>
                                    <td>
                                        @if ($ligne['evenement']->statut === 'publié')
                                            <span class="badge bg-success">Publié</span>
                                        @elseif ($ligne['evenement']->statut === 'terminé')
                                            <span class="badge bg-secondary">Terminé</span>
                                        @elseif ($ligne['evenement']->statut === 'annulé')
                                            <span class="badge bg-danger">Annulé</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Brouillon</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-muted small">
                    Aucun événement ouvert au co-parrainage. Activez l'option dans les paramètres d'un événement payant pour permettre aux affiliés de générer un lien.
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-link-45deg" style="color:#7B3FA0;"></i> Liens des affiliés
                </div>
                <div class="card-body p-0">
                    @if ($liens->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Affilié</th>
                                        <th>Événement</th>
                                        <th>%</th>
                                        <th class="text-center">Clics</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($liens as $lien)
                                        <tr>
                                            <td>{{ $lien->affiliate?->nom_complet ?? '—' }}</td>
                                            <td class="text-truncate" style="max-width:180px;">{{ $lien->evenement?->titre ?? '—' }}</td>
                                            <td>{{ rtrim(rtrim(number_format((float) $lien->pourcentage, 2, ',', ' '), '0'), ',') }} %</td>
                                            <td class="text-center">{{ $lien->clics }}</td>
                                            <td>
                                                @if ($lien->actif)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-muted small">Aucun affilié n'a encore généré de lien.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-cash-stack" style="color:#7B3FA0;"></i> Dernières commissions
                </div>
                <div class="card-body p-0">
                    @if ($commissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Affilié</th>
                                        <th class="text-end">Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($commissions as $commission)
                                        <tr>
                                            <td>{{ $commission->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $commission->affiliate?->nom_complet ?? '—' }}</td>
                                            <td class="text-end">{{ number_format((float) $commission->montant, 0, ',', ' ') }} F</td>
                                            <td>
                                                @if ($commission->statut === 'en_attente')
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                @elseif ($commission->statut === 'disponible')
                                                    <span class="badge bg-info text-dark">Disponible</span>
                                                @elseif ($commission->statut === 'paye')
                                                    <span class="badge bg-success">Payée</span>
                                                @else
                                                    <span class="badge bg-danger">Annulée</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-muted small">Aucune commission de co-parrainage pour le moment.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
