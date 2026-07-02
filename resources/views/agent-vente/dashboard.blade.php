@extends('layouts.agent-vente')

@section('title', 'Tableau de bord - ' . $agent->evenement->titre)

@section('content')
<div class="container py-4">
    {{-- En-tête --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="60">
        <form method="POST" action="{{ route('agent-vente.logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

    @if (session('success'))
    <div class="alert alert-success py-2 small" id="successAlert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        @if (session('ticket_created'))
        <a href="{{ route('agent-vente.ticket.pdf', session('ticket_created')) }}"
            class="btn btn-sm btn-outline-success ms-3" target="_blank">
            <i class="bi bi-filetype-pdf"></i> Télécharger PDF
        </a>
        @endif
    </div>
    @endif

    {{-- Cartes stats --}}
    <div class="row g-3 mb-4" id="statsCards">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-purple-50">
                <div class="card-body text-center py-3">
                    <div class="text-purple-700 fw-bold fs-3" id="statTotalTickets">{{ $stats['total_tickets'] }}</div>
                    <small class="text-muted">Tickets vendus</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-green-50">
                <div class="card-body text-center py-3">
                    <div class="text-green-700 fw-bold fs-3" id="statMontantTotal">{{ number_format($stats['montant_total'], 0, ',', ' ') }} F</div>
                    <small class="text-muted">Montant total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-blue-50">
                <div class="card-body text-center py-3">
                    <div class="text-blue-700 fw-bold fs-3" id="statAujourdHui">{{ $stats['aujourd_hui'] }}</div>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-amber-50">
                <div class="card-body text-center py-3">
                    <div class="text-amber-700 fw-bold fs-3" id="statMontantAjd">{{ number_format($stats['montant_ajd'], 0, ',', ' ') }} F</div>
                    <small class="text-muted">Montant aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Formulaire de vente --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom-0 pt-3 px-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-cart-plus"></i> Nouvelle vente</h6>
                </div>
                <div class="card-body px-3">
                    <form method="POST" action="{{ route('agent-vente.vendre') }}" id="venteForm">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Nom de l'acheteur</label>
                            <input type="text" name="nom_acheteur" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email_acheteur" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Téléphone</label>
                            <input type="tel" name="telephone_acheteur" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Tarif</label>
                            <select name="tarif_id" class="form-select form-select-sm" required>
                                <option value="">Sélectionner...</option>
                                @foreach ($agent->evenement->tarifs as $tarif)
                                <option value="{{ $tarif->id }}">
                                    {{ $tarif->getLabel() }} &mdash; {{ number_format($tarif->prix, 0, ',', ' ') }} F
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Paiement</label>
                            <select name="methode_paiement" class="form-select form-select-sm" required>
                                <option value="">Choisir...</option>
                                <option value="cash">Espèces (Cash)</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Code de vente</label>
                            <input type="text" name="code_vente" class="form-control form-control-sm"
                                maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                placeholder="Code à 6 chiffres" required>
                            @error('code_vente')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn w-100 text-white py-2 fw-medium" style="background: #7c3aed;">
                            <i class="bi bi-check-lg"></i> Vendre le ticket
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Dernières ventes --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Ventes du jour</h6>
                    <small class="text-muted">
                        <i class="bi bi-arrow-repeat"></i> Actualisation automatique
                    </small>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">Heure</th>
                                    <th>Acheteur</th>
                                    <th>Tarif</th>
                                    <th>Montant</th>
                                    <th>Paiement</th>
                                    <th class="pe-3">PDF</th>
                                </tr>
                            </thead>
                            <tbody id="historiqueBody">
                                @forelse ($ticketsAujourdHui as $ticket)
                                <tr>
                                    <td class="ps-3 small">{{ $ticket->date_achat->format('H:i') }}</td>
                                    <td class="small">{{ $ticket->nom_acheteur }}</td>
                                    <td class="small">{{ $ticket->tarif?->getLabel() ?? 'N/A' }}</td>
                                    @if($ticket->montant > 0)
                                    <td class="small fw-medium">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                                    <td class="small">{{ $ticket->methode_paiement === 'cash' ? 'Espèces' : 'Mobile Money' }}</td>
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
                                    <td colspan="6" class="text-center text-muted py-3 small">
                                        <i class="bi bi-inbox"></i> Aucune vente aujourd'hui
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
.bg-blue-50 { background: #eff6ff; }
.text-blue-700 { color: #2563eb; }
.bg-amber-50 { background: #fffbeb; }
.text-amber-700 { color: #d97706; }
</style>
@endsection

@push('scripts')
<script>
let historiqueInterval;

function chargerHistorique() {
    fetch('{{ route("agent-vente.historique.json") }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('statTotalTickets').textContent = data.total_tickets;
            document.getElementById('statMontantTotal').textContent = data.montant_total;
            document.getElementById('statAujourdHui').textContent = data.aujourd_hui;
            document.getElementById('statMontantAjd').textContent = data.montant_ajd;

            const tbody = document.getElementById('historiqueBody');
            if (data.tickets.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3 small"><i class="bi bi-inbox"></i> Aucune vente aujourd\'hui</td></tr>';
            } else {
                tbody.innerHTML = data.tickets.map(t => `
                    <tr>
                        <td class="ps-3 small">${t.date}</td>
                        <td class="small">${t.nom}</td>
                        <td class="small">${t.tarif}</td>
                        ${t.montant_val > 0 ? `
                        <td class="small fw-medium">${t.montant}</td>
                        <td class="small">${t.methode === 'cash' ? 'Espèces' : 'Mobile Money'}</td>
                        ` : `
                        <td class="small text-muted">Gratuit</td>
                        <td class="small">—</td>
                        `}
                        <td class="pe-3">
                            <a href="/vente/tickets/${t.id}/pdf" class="btn btn-sm btn-outline-secondary py-0 px-2" target="_blank">
                                <i class="bi bi-filetype-pdf"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    historiqueInterval = setInterval(chargerHistorique, 10000);

    setTimeout(() => {
        const alert = document.getElementById('successAlert');
        if (alert) alert.style.display = 'none';
    }, 8000);
});
</script>
@endpush
