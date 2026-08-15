@extends('layouts.app')

@section('title', 'Vente physique - Billetterie')
@section('page-title', 'Vente physique')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Vente physique</li>
@endsection

@section('content')
<div class="page-content">
    @if(session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif

    <!-- Mini-dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1);"><i class="bi bi-ticket-perforated" style="color: var(--violet);"></i></div>
                <div class="metric-label">Tickets physiques</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ $nbTickets }}</div>
                <div class="metric-subtitle">Dont {{ $nbAnnules }} annule(s)</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1);"><i class="bi bi-upc-scan" style="color: var(--vert);"></i></div>
                <div class="metric-label">Scannes a l'entree</div>
                <div class="metric-value" style="font-size:1.3rem; color: var(--vert);">{{ $nbScannes }}</div>
                <div class="metric-subtitle">{{ max(0, $nbTickets - $nbAnnules - $nbScannes) }} restant(s)</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--orange);">
                <div class="metric-icon" style="background: rgba(241,159,29,0.1);"><i class="bi bi-cash-coin" style="color: var(--orange);"></i></div>
                <div class="metric-label">Recettes physiques</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($recettesPhysiques, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Encaissées au guichet</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card" style="border-top-color: var(--gris);">
                <div class="metric-icon" style="background: rgba(152,145,155,0.1);"><i class="bi bi-percent" style="color: var(--gris);"></i></div>
                <div class="metric-label">Commission attendue</div>
                <div class="metric-value" style="font-size:1.3rem;">{{ number_format($commissionPhysique, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">A verser a PaxEvent</div>
            </div>
        </div>
    </div>

    @if($lots->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-ticket-perforated" style="font-size:3rem;"></i>
        <p class="mt-2">Aucun lot de tickets physiques pour le moment.</p>
        <p style="font-size:0.85rem;">Les lots sont generes par l'equipe PaxEvent. Vous serez notifie des qu'un lot vous est transmis.</p>
        <button type="button" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="openDemande('ticket_physique')">
            <i class="bi bi-qr-code me-1"></i> Demander des QR codes
        </button>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-stack me-1"></i> Mes lots de tickets physiques</strong>
            <button type="button" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;font-size:0.78rem;" onclick="openDemande('ticket_physique')">
                <i class="bi bi-qr-code me-1"></i> Demander des QR codes
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Lot</th>
                        <th>Evenement</th>
                        <th>Tarif</th>
                        <th class="text-center">Tickets</th>
                        <th class="text-center">Annues</th>
                        <th class="text-center">Scannes</th>
                        <th>Statut</th>
                        <th class="text-center">Telechargements</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lots as $lot)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $lot->nom }}</td>
                        <td>
                            @if($lot->evenement)
                            <a href="{{ route('admin.evenements.show', $lot->evenement) }}" class="text-decoration-none">{{ $lot->evenement->titre }}</a>
                            @else
                            ---
                            @endif
                        </td>
                        <td>{{ $lot->tarif?->nom ?? '---' }}</td>
                        <td class="text-center">{{ $lot->nb_tickets }}</td>
                        <td class="text-center">
                            @if($lot->nb_annules > 0)<span class="badge bg-danger">{{ $lot->nb_annules }}</span>@else 0 @endif
                        </td>
                        <td class="text-center">
                            @if($lot->nb_scannes > 0)<span class="badge bg-success">{{ $lot->nb_scannes }}</span>@else 0 @endif
                        </td>
                        <td>
                            @if($lot->estTransmis)
                                <span class="badge bg-success">Transmis</span>
                            @else
                                <span class="badge bg-warning text-dark">En attente</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $lot->download_count }}/3</td>
                        <td class="text-end pe-3">
                            @if($lot->estTransmis && $lot->nb_tickets - $lot->nb_annules > 0)
                                <a href="{{ route('admin.lots-physiques.download', $lot) }}" class="btn btn-sm text-white" style="background:#7c3aed;">
                                    <i class="bi bi-download"></i> Planche PDF
                                </a>
                            @else
                                <span class="text-muted" style="font-size:0.78rem;">
                                    @if(!$lot->estTransmis) En attente de transmission @else Aucun ticket valide @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $lots->links() }}
        </div>
    </div>
    @endif

    <div class="alert alert-light border mt-3 py-2 small text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Les tickets physiques ne comptent pas dans la capacite de vos evenements. Ils sont scannables a l'entree comme les tickets en ligne. La commission y afferente est suivie separement (rubrique ci-dessus).
    </div>
</div>
@endsection
