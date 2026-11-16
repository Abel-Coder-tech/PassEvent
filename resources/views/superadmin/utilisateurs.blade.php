@extends('superadmin.layouts.master')

@section('title', 'Utilisateurs - Super Admin')
@section('page-title', 'Utilisateurs')

@section('content')
<form method="GET" action="{{ route('superadmin.utilisateurs') }}" class="sa-card mb-3">
    <div class="sa-card-body d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm" style="max-width:360px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Nom, email, téléphone, événement…">
        </div>
        <select name="type" class="form-select form-select-sm" style="max-width:200px;" onchange="this.form.submit()">
            <option value="tous" @selected($type === 'tous')>Tous</option>
            <option value="comptes" @selected($type === 'comptes')>Organisateurs / admins</option>
            <option value="agents" @selected($type === 'agents')>Agents de scan</option>
        </select>
        <button class="btn btn-sm btn-primary">Rechercher</button>
        @if($q !== '' || $type !== 'tous')
            <a href="{{ route('superadmin.utilisateurs') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
        @endif
    </div>
</form>

@if($type !== 'agents')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-people-fill me-2" style="color: var(--sa-primary);"></i>Tous les utilisateurs</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $users->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Role</th><th>Telephone</th><th>Evenements</th><th>Inscrit le</th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->nom }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role === 'super_admin')
                            <span class="sa-badge sa-badge-info">Super Admin</span>
                        @else
                            <span class="sa-badge sa-badge-secondary">Admin</span>
                        @endif
                    </td>
                    <td>{{ $user->telephone ?? '-' }}</td>
                    <td>{{ $user->evenements_count }}</td>
                    <td style="font-size:0.75rem; color:var(--sa-text-muted);">{{ $user->created_at->isoFormat('D MMM YYYY') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 mb-4">{{ $users->links('vendor.pagination.pass', ['label' => 'utilisateur(s)']) }}</div>
@endif

@if($type !== 'comptes')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-qr-code-scan me-2" style="color: var(--sa-primary);"></i>Agents de scan</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $agents->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Événement</th><th>Organisateur</th><th>Statut</th><th>Dernier accès</th><th>Créé le</th></tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                <tr>
                    <td><strong>{{ $agent->nom }}</strong></td>
                    <td>{{ $agent->email }}</td>
                    <td>{{ $agent->evenement->titre ?? '-' }}</td>
                    <td>{{ $agent->evenement?->user?->nom ?? '-' }}</td>
                    <td>
                        @if($agent->actif)
                            <span class="sa-badge sa-badge-success">Actif</span>
                        @else
                            <span class="sa-badge sa-badge-secondary">Inactif</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem; color:var(--sa-text-muted);">{{ $agent->dernier_acces ? \Illuminate\Support\Carbon::parse($agent->dernier_acces)->isoFormat('D MMM YYYY HH:mm') : 'Jamais' }}</td>
                    <td style="font-size:0.75rem; color:var(--sa-text-muted);">{{ $agent->created_at?->isoFormat('D MMM YYYY') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun agent de scan trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $agents->links('vendor.pagination.pass', ['label' => 'agent(s)']) }}</div>
@endif
@endsection
