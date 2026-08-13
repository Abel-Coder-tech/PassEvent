@extends('layouts.agent-vente')

@section('title', 'Historique des ventes - ' . $agent->evenement->titre)

@section('content')
@php $textes = $agent->evenement->getTextes(); @endphp
<div class="container py-4">
    {{-- En-tête --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="60">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ $agent->nom }}</span>
            <form method="POST" action="{{ route('agent-vente.logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Déconnecter
                </button>
            </form>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Historique des ventes</h5>
        <a href="{{ route('agent-vente.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>

    {{-- Filtres par période --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('agent-vente.historique') }}" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                @foreach ([
                    'aujourdhui' => 'Aujourd\'hui',
                    'hier' => 'Hier',
                    'semaine' => 'Cette semaine',
                    'mois' => 'Ce mois',
                    'tout' => 'Tout',
                ] as $value => $label)
                <button type="submit" name="periode" value="{{ $value }}"
                    class="btn btn-sm {{ $periode === $value ? 'text-white' : 'btn-outline-secondary' }}"
                    style="{{ $periode === $value ? 'background: #7c3aed; border-color: #7c3aed;' : '' }}">
                    {{ $label }}
                </button>
                @endforeach
            </form>
        </div>
    </div>

    {{-- Récapitulatif de la période filtrée --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-purple-50">
                <div class="card-body text-center py-3">
                    <div class="text-purple-700 fw-bold fs-3">{{ $tickets->total() }}</div>
                    <small class="text-muted">Ventes (page)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-green-50">
                <div class="card-body text-center py-3">
                    <div class="text-green-700 fw-bold fs-3">{{ number_format($montantFiltre, 0, ',', ' ') }} F</div>
                    <small class="text-muted">Montant de la page</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tableau paginé --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Acheteur</th>
                            <th>Tarif</th>
                            <th>Montant</th>
                            <th>Paiement</th>
                            <th class="pe-3">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                        <tr>
                            <td class="ps-3 small">{{ $ticket->date_achat->format('d/m/Y H:i') }}</td>
                            <td class="small">{{ $ticket->nom_acheteur }}</td>
                            <td class="small">{{ $ticket->tarif?->getLabel() ?? 'N/A' }}</td>
                            @if($ticket->montant > 0)
                            <td class="small fw-medium">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                            <td class="small">{{ \App\Models\Ticket::methodePaiementLabel($ticket->methode_paiement) }}</td>
                            @else
                            <td class="small text-muted">Gratuit</td>
                            <td class="small">—</td>
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
                            <td colspan="6" class="text-center text-muted py-4 small">
                                <i class="bi bi-inbox"></i> Aucune vente pour cette période
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                    {{ $tickets->total() }} vente(s) au total
                    @if($periode !== 'tout')
                    <span class="text-muted">— période sélectionnée</span>
                    @endif
                </div>
                <div>
                    {{ $tickets->appends(['periode' => $periode])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-purple-50 { background: #f5f3ff; }
.text-purple-700 { color: #7c3aed; }
.bg-green-50 { background: #f0fdf4; }
.text-green-700 { color: #16a34a; }
</style>
@endsection
