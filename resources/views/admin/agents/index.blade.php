@extends('layouts.app')

@section('title', 'Agents de scan - PaxEvent')

@section('page-title', 'Agents de scan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Agents</li>
@endsection

@section('content')
<div class="page-content">
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-people me-2" style="color: var(--violet);"></i>Mes agents</h5>
            <a href="{{ route('admin.agents.create') }}" class="btn btn-violet btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nouvel agent
            </a>
        </div>
        <div class="panel-card-body p-0">
            @if($agents->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-person-plus" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="mt-2 text-muted">Aucun agent créé pour le moment.</p>
                    <a href="{{ route('admin.agents.create') }}" class="btn btn-violet btn-sm">Créer un agent</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Événement</th>
                                <th>Code accès</th>
                                <th>Statut</th>
                                <th>Dernier accès</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agents as $agent)
                            <tr>
                                <td class="fw-semibold">{{ $agent->nom }}</td>
                                <td>{{ $agent->email }}</td>
                                <td>{{ $agent->evenement->titre }}</td>
                                <td><code>{{ $agent->code_acces }}</code></td>
                                <td>
                                    @if($agent->actif)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td>{{ $agent->dernier_acces ? $agent->dernier_acces->format('d/m/Y H:i') : 'Jamais' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.agents.toggle-actif', $agent) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $agent->actif ? 'btn-warning' : 'btn-success' }}" title="{{ $agent->actif ? 'Désactiver' : 'Activer' }}">
                                                <i class="bi {{ $agent->actif ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet agent ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
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
@endsection
