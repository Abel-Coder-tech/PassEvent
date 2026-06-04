@extends('superadmin.layouts.master')

@section('title', 'Organisateurs - Super Admin')
@section('page-title', 'Organisateurs')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-person-plus-fill me-2" style="color: var(--sa-success);"></i>Creer un organisateur</span>
            </div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.organisateurs.creer') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6"><input type="text" name="nom" class="sa-form-control" placeholder="Nom complet" required></div>
                        <div class="col-6"><input type="email" name="email" class="sa-form-control" placeholder="Email" required></div>
                        <div class="col-6"><input type="password" name="mot_de_passe" class="sa-form-control" placeholder="Mot de passe" required></div>
                        <div class="col-3"><input type="text" name="telephone" class="sa-form-control" placeholder="Telephone"></div>
                        <div class="col-3"><input type="text" name="organisation" class="sa-form-control" placeholder="Organisation"></div>
                        <div class="col-12 mt-2"><button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-plus-lg"></i> Creer</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Validation</span>
            </div>
            <div class="sa-card-body">
                <p class="text-muted mb-0" style="font-size:0.8rem;">Les organisateurs en attente apparaissent avec un badge jaune. Utilisez les boutons ✅ ou ❌ pour les approuver ou les rejeter.</p>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-person-badge-fill me-2" style="color: var(--sa-primary);"></i>Organisateurs</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $organisateurs->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Organisation</th><th>Statut</th><th>Evenements</th><th>Telephone</th><th>Inscrit</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($organisateurs as $org)
                <tr>
                    <td><strong>{{ $org->nom }}</strong></td>
                    <td>{{ $org->email }}</td>
                    <td>{{ $org->organisation ?? '-' }}</td>
                    <td>
                        @if($org->statut === 'en_attente')
                            <span class="sa-badge sa-badge-warning">En attente</span>
                        @elseif($org->statut === 'actif')
                            <span class="sa-badge sa-badge-success">Actif</span>
                        @elseif($org->statut === 'bloque')
                            <span class="sa-badge sa-badge-danger">Bloque</span>
                        @else
                            <span class="sa-badge sa-badge-secondary">{{ $org->statut }}</span>
                        @endif
                    </td>
                    <td>{{ $org->evenements_count }}</td>
                    <td>{{ $org->telephone ?? '-' }}</td>
                    <td style="font-size:0.75rem;">{{ $org->created_at->format('d M Y') }}</td>
                    <td>
                        @if($org->statut === 'en_attente')
                            <form action="{{ route('superadmin.organisateurs.approuver', $org) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form action="{{ route('superadmin.organisateurs.rejeter', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Rejeter {{ $org->nom }} ?')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Rejeter"><i class="bi bi-x-lg"></i></button>
                            </form>
                        @endif
                        @if($org->statut === 'actif')
                            <form action="{{ route('superadmin.organisateurs.suspendre', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspendre {{ $org->nom }} ? Ses evenements seront annules.')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-warning" title="Suspendre"><i class="bi bi-pause-fill"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $organisateurs->links() }}</div>

<style>
.sa-badge {
    font-size: 0.68rem;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
}
.sa-badge-success { background: rgba(46,125,79,0.12); color: #2e7d4f; }
.sa-badge-warning { background: rgba(237,173,8,0.12); color: #b8860b; }
.sa-badge-danger { background: rgba(231,76,60,0.12); color: #e74c3c; }
.sa-badge-secondary { background: rgba(152,145,155,0.15); color: #6c757d; }
.sa-btn-success {
    background: #2e7d4f; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-success:hover { opacity: 0.85; }
.sa-btn-warning {
    background: #e0a800; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-warning:hover { opacity: 0.85; }
</style>
@endsection
