@extends('superadmin.layouts.master')

@section('title', 'Consentements cookies - Super Admin')
@section('page-title', 'Consentements cookies')

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <form method="GET" action="{{ route('superadmin.consentements') }}" class="d-inline-flex align-items-center gap-2">
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:#888; font-size:0.8rem; pointer-events:none;"></i>
            <input type="text" name="q" class="form-control form-control-sm ps-4" style="border-radius:10px; min-width:320px;" placeholder="Email, nom, session ou IP" value="{{ request('q') }}" autocomplete="off">
        </div>
        <select name="statut" class="form-select form-select-sm" style="border-radius:10px; width:auto;">
            <option value="">Tous les statuts</option>
            @foreach(['accepte' => 'Accepté', 'refuse' => 'Refusé', 'personnalise' => 'Personnalisé'] as $valeur => $libelle)
            <option value="{{ $valeur }}" {{ request('statut') === $valeur ? 'selected' : '' }}>{{ $libelle }}</option>
            @endforeach
        </select>
        <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary">Filtrer</button>
        @if(request('q') || request('statut'))
        <a href="{{ route('superadmin.consentements') }}" class="sa-btn sa-btn-sm" style="background:#e74c3c;border:none;color:#fff;border-radius:6px;text-decoration:none;">Réinitialiser</a>
        @endif
    </form>
    <span class="text-muted" style="font-size:0.8rem;">{{ $consentements->total() }} décision(s)</span>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-shield-check me-2" style="color: var(--sa-primary);"></i>Journal des décisions cookies</span>
        <span class="text-muted ms-auto" style="font-size:0.8rem;">
            <i class="bi bi-info-circle me-1"></i>Chaque clic sur le bandeau est consigné (version politique, services, session, IP).
        </span>
    </div>
    <div class="sa-card-body p-0">
        @if($consentements->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shield-check" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Aucune décision de consentement pour le moment.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Personne</th>
                        <th>Statut</th>
                        <th>Services acceptés</th>
                        <th>Version politique</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consentements as $c)
                    <tr>
                        <td class="text-nowrap" style="font-size:0.8rem;">{{ $c->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($c->user)
                            <div>{{ $c->user->nom }} <span class="text-muted" style="font-size:0.75rem;">({{ $c->user->email }})</span></div>
                            @else
                            <span class="text-muted">Visiteur {{ $c->user_id ? '' : '(anonyme)' }}</span>
                            @endif
                            <div class="text-muted" style="font-size:0.7rem;">Session : {{ $c->session_id ? substr($c->session_id, 0, 12).'…' : '—' }}</div>
                        </td>
                        <td>
                            <span class="sa-badge sa-badge-{{ $c->badgeStatut() }}">{{ $c->libelleStatut() }}</span>
                        </td>
                        <td>
                            @if(empty($c->listeServices()))
                            <span class="text-muted">Aucun</span>
                            @else
                            <div style="font-size:0.75rem;">
                                @foreach($c->listeServices() as $service)
                                <span class="sa-badge sa-badge-secondary me-1">{{ $service }}</span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;">{{ $c->version_politique ?? '—' }}</td>
                        <td style="font-size:0.8rem;">{{ $c->ip_visiteur ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $consentements->links() }}</div>
@endsection