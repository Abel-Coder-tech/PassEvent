@extends('layouts.app')

@section('title', $agentVente->nom . ' — Agent de vente')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('admin.agents-vente.index') }}" class="text-decoration-none small">
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
                    <h5 class="fw-bold">{{ $agentVente->nom }}</h5>
                    <p class="text-muted small mb-2">{{ $agentVente->email }}</p>
                    <span class="badge bg-{{ $agentVente->actif ? 'success' : 'secondary' }} mb-2">
                        {{ $agentVente->actif ? 'Actif' : 'Inactif' }}
                    </span>
                    <p class="small mb-0">
                        <i class="bi bi-calendar-event"></i>
                        <a href="{{ route('admin.evenements.show', $agentVente->evenement) }}" class="text-decoration-none">
                            {{ $agentVente->evenement->titre }}
                        </a>
                    </p>
                    <form action="{{ route('admin.agents-vente.toggle-actif', $agentVente) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-{{ $agentVente->actif ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $agentVente->actif ? 'pause' : 'play' }}"></i>
                            {{ $agentVente->actif ? 'Désactiver' : 'Réactiver' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-purple-50 text-center py-3">
                        <div class="fw-bold fs-4 text-purple-700">{{ $stats['total_tickets'] }}</div>
                        <small class="text-muted">Tickets vendus</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-green-50 text-center py-3">
                        <div class="fw-bold fs-4 text-green-700">{{ number_format($stats['montant_total'], 0, ',', ' ') }} F</div>
                        <small class="text-muted">Montant total</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm bg-blue-50 text-center py-3">
                        <div class="fw-bold fs-4 text-blue-700">{{ $stats['aujourd_hui'] }}</div>
                        <small class="text-muted">Aujourd'hui</small>
                    </div>
                </div>
            </div>

            {{-- Stats détaillées --}}
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-tag"></i> Par tarif</h6>
                            @forelse ($stats['par_tarif'] as $s)
                            <div class="d-flex justify-content-between small">
                                <span>{{ $s->tarif?->getLabel() ?? 'N/A' }}</span>
                                <span class="fw-medium">{{ $s->total }} ({{ number_format($s->montant, 0, ',', ' ') }} F)</span>
                            </div>
                            @empty
                            <small class="text-muted">Aucune donnée</small>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-credit-card"></i> Par méthode</h6>
                            @forelse ($stats['par_methode'] as $s)
                            <div class="d-flex justify-content-between small">
                                <span>{{ \App\Models\Ticket::methodePaiementLabel($s->methode_paiement) }}</span>
                                <span class="fw-medium">{{ $s->total }}</span>
                            </div>
                            @empty
                            <small class="text-muted">Aucune donnée</small>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historique des ventes --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Historique des ventes</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Acheteur</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Tarif</th>
                        <th class="text-end">Montant</th>
                        <th>Paiement</th>
                        <th class="pe-3">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                    <tr>
                        <td class="ps-3">{{ $ticket->date_achat->format('d/m/Y H:i') }}</td>
                        <td>{{ $ticket->nom_acheteur }}</td>
                        <td>{{ $ticket->email_acheteur }}</td>
                        <td>{{ $ticket->telephone_acheteur }}</td>
                        <td>{{ optional($ticket->tarif)->getLabel() ?? 'N/A' }}</td>
                        @if($ticket->montant > 0)
                        <td class="text-end fw-medium">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                        <td>{{ \App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement) }}</td>
                        @else
                        <td class="text-end text-muted">Gratuit</td>
                        <td>—</td>
                        @endif
                        <td class="pe-3">
                            <a href="{{ route('agent-vente.ticket.pdf', $ticket) }}"
                                class="btn btn-sm btn-outline-secondary py-0 px-2" target="_blank">
                                <i class="bi bi-filetype-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">Aucune vente</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets->hasPages())
        <div class="card-footer bg-white">
            {{ $tickets->links() }}
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
