@extends('layouts.app')

@section('title', $agent->nom . ' — Agent - PaxEvent')

@section('page-title', $agent->nom)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $agent->nom }}</li>
@endsection

@section('content')
<div class="page-content">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="panel-card">
                <div class="panel-card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-circle mx-auto" style="width:80px;height:80px;background:var(--violet);color:#fff;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:2rem;font-weight:700;">
                            {{ strtoupper(substr($agent->nom, 0, 1)) }}
                        </div>
                    </div>
                    <h5 class="fw-bold">{{ $agent->nom }}</h5>
                    <p class="text-muted mb-1">{{ $agent->email }}</p>
                    <p class="mb-2">
                        @if($agent->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </p>
                    <hr>
                    <div class="text-start small">
                        <div class="mb-1"><strong>Code d'accès :</strong> <code>{{ $agent->code_acces }}</code></div>
                        <div class="mb-1"><strong>Dernier accès :</strong> {{ $agent->dernier_acces ? $agent->dernier_acces->format('d/m/Y H:i') : 'Jamais' }}</div>
                        <div class="mb-1"><strong>Créé le :</strong> {{ $agent->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-shield-check me-2" style="color: var(--violet);"></i>Scans effectués</h5>
                </div>
                <div class="panel-card-body p-0">
                    @if($agent->logs->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucun scan effectué par cet agent.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Ticket</th>
                                        <th>Résultat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agent->logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
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
                                            <small class="d-block text-muted">{{ $log->details['raison'] ?? '' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
