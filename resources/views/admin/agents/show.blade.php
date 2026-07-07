@extends('layouts.app')

@section('title', $agent->nom . ' — Agent de scan')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('admin.agents.index') }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Tous les agents
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    {{-- Carte agent --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                        style="width: 64px; height: 64px; background: #f5f3ff;">
                        <i class="bi bi-person text-purple-700" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold">{{ $agent->nom }}</h5>
                    <p class="text-muted small mb-2">{{ $agent->email }}</p>
                    <span class="badge bg-{{ $agent->actif ? 'success' : 'secondary' }} mb-2">
                        {{ $agent->actif ? 'Actif' : 'Inactif' }}
                    </span>
                    <p class="small mb-0">
                        <i class="bi bi-calendar-event"></i>
                        <a href="{{ route('admin.evenements.show', $agent->evenement) }}" class="text-decoration-none">
                            {{ $agent->evenement->titre }}
                        </a>
                    </p>
                    <div class="text-start small mt-3 pt-2 border-top">
                        <div class="mb-1"><strong>Code d'accès :</strong> <code>{{ $agent->code_acces }}</code></div>
                        <div class="mb-1"><strong>Dernier accès :</strong> {{ $stats['dernier_acces'] ? $stats['dernier_acces']->format('d/m/Y H:i') : 'Jamais' }}</div>
                        <div class="mb-1"><strong>Créé le :</strong> {{ $agent->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <form action="{{ route('admin.agents.toggle-actif', $agent) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-{{ $agent->actif ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $agent->actif ? 'pause' : 'play' }}"></i>
                            {{ $agent->actif ? 'Désactiver' : 'Réactiver' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-purple-50 text-center py-3">
                        <div class="fw-bold fs-4 text-purple-700">{{ $stats['total_scans'] }}</div>
                        <small class="text-muted">Scans effectués</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-green-50 text-center py-3">
                        <div class="fw-bold fs-4 text-green-700">{{ $stats['scans_ajd'] }}</div>
                        <small class="text-muted">Aujourd'hui</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-blue-50 text-center py-3">
                        <div class="fw-bold fs-4 text-blue-700">{{ $stats['valides'] }}</div>
                        <small class="text-muted">Validés</small>
                    </div>
                </div>
            </div>

            {{-- Stats détaillées --}}
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-check-circle"></i> Par résultat</h6>
                            <div class="d-flex justify-content-between small">
                                <span class="text-success">Validés</span>
                                <span class="fw-medium">{{ $stats['valides'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-warning">Déjà utilisés</span>
                                <span class="fw-medium">{{ $stats['deja_utilises'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-danger">Invalides</span>
                                <span class="fw-medium">{{ $stats['invalides'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-info-circle"></i> Informations</h6>
                            <div class="d-flex justify-content-between small">
                                <span>Statut</span>
                                <span class="fw-medium">{{ $agent->actif ? 'Actif' : 'Inactif' }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Dernier accès</span>
                                <span class="fw-medium">{{ $stats['dernier_acces'] ? $stats['dernier_acces']->diffForHumans() : 'Jamais' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historique des scans --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Historique des scans</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Ticket</th>
                        <th>Résultat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td class="ps-3">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            @if($log->ticket)
                                <code>{{ $log->ticket->code_unique }}</code>
                            @else
                                <code>{{ $log->details['code'] ?? 'N/A' }}</code>
                            @endif
                        </td>
                        <td>
                            @php $resultat = $log->details['resultat'] ?? ''; @endphp
                            @if($resultat === 'valide')
                                <span class="badge bg-success">Validé</span>
                            @elseif($resultat === 'deja_utilise')
                                <span class="badge bg-warning text-dark">Déjà utilisé</span>
                            @else
                                <span class="badge bg-danger">Invalide</span>
                            @endif
                            @if(!empty($log->details['raison']))
                            <small class="d-block text-muted">{{ $log->details['raison'] }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">Aucun scan effectué</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.bg-purple-50 { background: #f5f3ff; }
.text-purple-700 { color: #7c3aed; }
.bg-green-50 { background: #f0fdf4; }
.text-green-700 { color: #16a34a; }
.bg-blue-50 { background: #eff6ff; }
.text-blue-700 { color: #2563eb; }
</style>
@endsection