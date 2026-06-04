@extends('superadmin.layouts.master')

@section('title', 'Utilisateurs - Super Admin')
@section('page-title', 'Utilisateurs')

@section('content')
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
                @foreach($users as $user)
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
                    <td style="font-size:0.75rem; color:var(--sa-text-muted);">{{ $user->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $users->links() }}</div>
@endsection
