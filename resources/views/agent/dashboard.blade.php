@extends('layouts.agent')

@section('title', 'Tableau de bord — Agent PaxEvent')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3 mb-md-4">
        <h4 class="fw-bold mb-0 fs-5 fs-md-4">
            <i class="bi bi-house-door me-2" style="color:var(--violet);"></i>Tableau de bord
        </h4>
        <small class="text-muted d-none d-sm-block">{{ now()->format('d/m/Y H:i') }}</small>
    </div>

    @if(!$agent->actif)
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>Votre compte a été désactivé par l'organisateur.
        </div>
    @elseif($evenement->date_event < now())
        <div class="alert alert-warning">
            <i class="bi bi-clock me-2"></i>L'événement <strong>{{ $evenement->titre }}</strong> est terminé ({{ $evenement->date_event->format('d/m/Y') }}).
        </div>
    @endif

    @if(session('scan_ok'))
        <div class="alert alert-success py-2" style="font-size:0.85rem;border-radius:10px;">
            <i class="bi bi-check-circle me-1"></i>Code d'accès validé. Vous pouvez scanner.
        </div>
    @endif

    <div class="row g-2 g-sm-3 mb-3 mb-md-4">
        <div class="col-4">
            <div class="stat-card card-agent">
                <div class="value">{{ $stats['total'] }}</div>
                <div class="label">Total</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card card-agent">
                <div class="value" style="color:#28a745;">{{ $stats['valides'] }}</div>
                <div class="label">Validés</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card card-agent">
                <div class="value" style="color:#dc3545;">{{ $stats['invalides'] }}</div>
                <div class="label">Invalides</div>
            </div>
        </div>
    </div>

    <div class="card-agent p-3 p-md-4 mb-3 mb-md-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <h5 class="fw-bold mb-1">{{ $evenement->titre }}</h5>
                <p class="text-muted mb-0 small">
                    <span class="d-block d-sm-inline"><i class="bi bi-calendar3 me-1"></i>{{ $evenement->date_event->isoFormat('D MMMM YYYY') }}</span>
                    <span class="d-block d-sm-inline mt-1 mt-sm-0"><i class="bi bi-geo-alt ms-sm-3 me-1"></i>{{ $evenement->lieu }}</span>
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                @if($agent->actif && $evenement->date_event >= now())
                    @if(session('agent_scan_ok') === $agent->id)
                        <a href="{{ route('agent.scan.index') }}" class="btn btn-violet w-100 w-md-auto">
                            <i class="bi bi-qr-code-scan me-1"></i>Scanner
                        </a>
                    @else
                        <a href="{{ route('agent.scan.pin') }}" class="btn btn-violet w-100 w-md-auto">
                            <i class="bi bi-shield-lock me-1"></i>Accéder au scan
                        </a>
                    @endif
                @else
                    <button class="btn btn-secondary w-100 w-md-auto" disabled>
                        <i class="bi bi-lock me-1"></i>Scan indisponible
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($scans->isNotEmpty())
    <div class="card-agent p-0">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2" style="color:var(--violet);"></i>Derniers scans</h6>
        </div>
        <div class="table-responsive scan-card-desktop">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Résultat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scans as $log)
                    <tr>
                        <td class="small">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td><code>{{ $log->details['code'] ?? '' }}</code></td>
                        <td>
                            @if(($log->details['resultat'] ?? '') === 'valide')
                                <span class="badge bg-success">Validé</span>
                            @elseif(($log->details['resultat'] ?? '') === 'deja_utilise')
                                <span class="badge bg-warning text-dark">Déjà utilisé</span>
                            @else
                                <span class="badge bg-danger">Invalide</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="scan-card-mobile">
            @foreach($scans as $log)
            <div class="scan-item">
                <div class="scan-info">
                    <div class="scan-code">{{ $log->details['code'] ?? '' }}</div>
                    <div class="scan-time">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div>
                    @if(($log->details['resultat'] ?? '') === 'valide')
                        <span class="badge bg-success">Validé</span>
                    @elseif(($log->details['resultat'] ?? '') === 'deja_utilise')
                        <span class="badge bg-warning text-dark">Déjà utilisé</span>
                    @else
                        <span class="badge bg-danger">Invalide</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
