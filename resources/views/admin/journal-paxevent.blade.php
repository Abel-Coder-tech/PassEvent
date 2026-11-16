@extends('layouts.app')

@section('title', 'Journal PaxEvent')
@section('page-title', 'Journal PaxEvent')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Journal PaxEvent</li>
@endsection

@section('content')
<div class="page-content">
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-journal-text me-2" style="color: var(--violet);"></i>Actions réalisées par PaxEvent en votre nom</h5>
        </div>
        <div class="panel-card-body p-3">
            @forelse($actions as $action)
                <div class="d-flex justify-content-between flex-wrap gap-2 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold">{{ $action->resume }}</div>
                        <div class="small text-muted">Par PaxEvent ({{ $action->admin_email }})</div>
                    </div>
                    <div class="small text-muted text-end">{{ $action->created_at->isoFormat('D MMM YYYY [à] HH[h]mm') }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">PaxEvent n'a réalisé aucune action en votre nom.</p>
            @endforelse
            <div class="mt-3">{{ $actions->links() }}</div>
        </div>
    </div>
</div>
@endsection
