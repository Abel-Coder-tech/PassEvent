@extends('superadmin.layouts.master')

@section('title', $user->nom . ' — Organisateur')
@section('page-title', $user->nom)

@section('content')
<div class="mb-3">
    <a href="{{ route('superadmin.organisateurs') }}" class="text-decoration-none small" style="color: var(--sa-primary);">
        <i class="bi bi-arrow-left"></i> Tous les organisateurs
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="sa-card h-100">
            <div class="sa-card-body text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                    style="width: 64px; height: 64px; background: rgba(107,63,160,0.1);">
                    <i class="bi bi-person" style="font-size: 2rem; color: var(--sa-primary);"></i>
                </div>
                <h5 class="fw-bold">{{ $user->nom }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                @php
                    $badgeMap = ['actif' => 'success', 'bloque' => 'danger', 'rejete' => 'danger', 'en_attente' => 'warning', 'corrections_demandees' => 'warning', 'incomplet' => 'secondary'];
                    $labelMap = ['actif' => 'Actif', 'bloque' => 'Bloqué', 'rejete' => 'Rejeté', 'en_attente' => 'En attente', 'corrections_demandees' => 'Corrections demandées', 'incomplet' => 'Incomplet'];
                @endphp
                <span class="sa-badge sa-badge-{{ $badgeMap[$user->statut] ?? 'secondary' }} mb-2">
                    {{ $labelMap[$user->statut] ?? ucfirst($user->statut) }}
                </span>
                @if($user->organisation)
                    <p class="small mb-0 mt-2"><i class="bi bi-building"></i> {{ $user->organisation }}</p>
                @endif
                @if($user->telephone)
                    <p class="small mb-0"><i class="bi bi-telephone"></i> {{ $user->telephone }}</p>
                @endif
                @if($user->type)
                    <p class="small mb-0 mt-1"><i class="bi bi-tag"></i> {{ ucfirst($user->type) }}</p>
                @endif
                @if($user->document_justificatif)
                    <div class="mt-3 pt-2 border-top">
                        <a href="{{ asset('storage/' . $user->document_justificatif) }}" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                            <i class="bi bi-eye me-1"></i> Justificatif
                        </a>
                        <a href="{{ asset('storage/' . $user->document_justificatif) }}" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @endif
                @if($user->signature)
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $user->signature) }}" target="_blank" class="btn btn-sm text-white fw-semibold" style="background:var(--sa-primary);border-radius:6px;text-decoration:none;">
                            <i class="bi bi-pen me-1"></i> Signature
                        </a>
                        <a href="{{ asset('storage/' . $user->signature) }}" download class="btn btn-sm" style="border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#666;">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-2">
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: var(--sa-primary);">{{ $evenements->count() }}</div>
                    <small class="text-muted">Événements</small>
                </div>
            </div>
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: var(--sa-success);">{{ number_format($totalTickets, 0, ',', ' ') }}</div>
                    <small class="text-muted">Tickets vendus</small>
                </div>
            </div>
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: #3498db;">{{ number_format($totalRecettes, 0, ',', ' ') }} F</div>
                    <small class="text-muted">Revenus totaux</small>
                </div>
            </div>
            <div class="col-3">
                <div class="sa-card text-center py-3">
                    <div class="fw-bold fs-4" style="color: #f39c12;">{{ $aujourdhui }}</div>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: var(--sa-primary);">{{ $agentsScan }}</div>
                    <small class="text-muted">Agents scan</small>
                </div>
            </div>
            <div class="col-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: var(--sa-success);">{{ $agentsVente }}</div>
                    <small class="text-muted">Agents vente</small>
                </div>
            </div>
            <div class="col-4">
                <div class="sa-card text-center py-2">
                    <div class="fw-bold" style="color: #3498db;">{{ $scansAujourdhui }}</div>
                    <small class="text-muted">Scans aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:#3498db;">{{ number_format($mobileRecettes, 0, ',', ' ') }} F</div>
                <small class="text-muted">Mobile (FedaPay)</small>
            </div>
        </div>
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:#f39c12;">{{ number_format($cashRecettes, 0, ',', ' ') }} F</div>
                <small class="text-muted">Espèces</small>
            </div>
        </div>
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:var(--sa-danger);">{{ number_format($commission, 0, ',', ' ') }} F</div>
                <small class="text-muted">Commission ({{ $commissionPct }}%)</small>
            </div>
        </div>
        <div class="col-3">
            <div class="sa-card text-center py-2">
                <div class="fw-bold" style="color:var(--sa-success);">{{ number_format($retirable, 0, ',', ' ') }} F</div>
                <small class="text-muted">Retirable</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-calendar-event me-2" style="color: var(--sa-primary);"></i>Événements</span>
            </div>
            <div class="sa-card-body p-0">
                <table class="sa-table">
                    <thead>
                        <tr><th>Titre</th><th>Date</th><th>Tickets</th><th>Revenus</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @forelse($evenements as $ev)
                        <tr>
                            <td><strong>{{ $ev->titre }}</strong></td>
                            <td style="font-size:0.78rem;">{{ $ev->date_event->isoFormat('D MMM YYYY') }}</td>
                            <td>{{ $ev->tickets_vendus }} / {{ $ev->capacite }}</td>
                            <td>{{ number_format($ev->recettes, 0, ',', ' ') }} F</td>
                            <td>
                                @if($ev->statut === 'publié')
                                    <span class="sa-badge sa-badge-success">Publié</span>
                                @elseif($ev->statut === 'brouillon')
                                    <span class="sa-badge sa-badge-secondary">Brouillon</span>
                                @else
                                    <span class="sa-badge sa-badge-danger">{{ ucfirst($ev->statut) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun événement</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-pie-chart me-2" style="color: var(--sa-success);"></i>Répartition par événement</span>
            </div>
            <div class="sa-card-body p-3">
                @forelse($evenements as $ev)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong style="font-size:0.82rem;">{{ Str::limit($ev->titre, 30) }}</strong>
                        <div class="text-muted" style="font-size:0.72rem;">{{ $ev->tickets_vendus }} tickets</div>
                    </div>
                    <div class="fw-bold" style="color: var(--sa-success);">{{ number_format($ev->recettes, 0, ',', ' ') }} F</div>
                </div>
                @empty
                <p class="text-muted text-center py-3 mb-0">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-clock-history me-2" style="color: var(--sa-primary);"></i>Derniers tickets vendus</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Date</th><th>Événement</th><th>Acheteur</th><th>Email</th><th>Montant</th><th>Méthode</th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td style="font-size:0.78rem;">{{ $ticket->date_achat->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td>{{ Str::limit($ticket->evenement->titre, 25) }}</td>
                    <td>{{ $ticket->nom_acheteur }}</td>
                    <td>{{ $ticket->email_acheteur }}</td>
                    <td class="fw-bold" style="color: var(--sa-success);">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
                    <td>{{ $ticket->methode_paiement === 'cash' ? 'Espèces' : 'Mobile' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Aucun ticket vendu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($tickets->hasPages())
    <div class="p-3 d-flex justify-content-center">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection