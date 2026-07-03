@extends('superadmin.layouts.master')

@section('title', 'Modération - Super Admin')
@section('page-title', 'Moderation de contenu')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-shield-exclamation me-2" style="color: var(--sa-danger);"></i>Evenements suspendus / annules</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Evenement</th><th>Organisateur</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($evenementsSuspendus as $ev)
                <tr>
                    <td><strong>{{ $ev->titre }}</strong></td>
                    <td>{{ $ev->user->nom ?? '-' }}</td>
                    <td><span class="sa-badge sa-badge-danger">{{ $ev->statut }}</span></td>
                    <td style="font-size:0.75rem;">{{ $ev->date_event->isoFormat('D MMM YYYY') }}</td>
                    <td>
                        <form action="{{ route('superadmin.evenements.mettre-en-avant', $ev) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-success);color:#fff;border:none;">Reactiver</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Aucun evenement suspendu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $evenementsSuspendus->links() }}</div>
@endsection
