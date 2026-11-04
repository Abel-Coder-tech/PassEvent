@extends('admin.layouts.master')
@section('title', 'Scan hors ligne - ' . $evenement->titre)
@section('page-title', 'Scan hors ligne')
@section('content')
<div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Bail hors ligne</h5><a href="{{ route('admin.evenements.offline.report', $evenement) }}" class="btn btn-sm btn-outline-secondary">Rapport</a></div>
        <div class="card-body">
            @if($lease)
                <p><strong>Statut:</strong> {{ $lease->statut }}</p>
                <p><strong>Agent titulaire:</strong> {{ $lease->agent->nom ?? '-' }}</p>
                <p><strong>Activé le:</strong> {{ $lease->activated_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>Expire le:</strong> {{ $lease->expires_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>Dernier sync:</strong> {{ $lease->last_sync_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    @if(in_array($lease->statut, ['autorise','arme']))<form action="{{ route('admin.evenements.offline.activate', $evenement) }}" method="POST">@csrf<button class="btn btn-primary">Demander l'activation</button></form>@endif
                    @if($lease->statut === 'actif')<form action="{{ route('admin.evenements.offline.revoke', $evenement) }}" method="POST">@csrf<button class="btn btn-warning">Révoquer (sûr)</button></form><form action="{{ route('admin.evenements.offline.force-revoke', $evenement) }}" method="POST" onsubmit="return confirm('Révocation forcée ?');">@csrf<button class="btn btn-danger">Révocation forcée</button></form>@endif
                </div>
            @else <p class="text-muted">Aucun bail pour cet événement.</p> @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Autoriser un agent</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.evenements.offline.authorize', $evenement) }}" method="POST" class="d-flex gap-2">@csrf
                <select name="agent_id" class="form-select" required><option value="">Sélectionner un agent</option>@foreach($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->nom }} ({{ $agent->email }})</option>@endforeach</select>
                <button class="btn btn-primary">Autoriser le scan hors ligne</button>
            </form>
        </div>
    </div>
</div>
@endsection
