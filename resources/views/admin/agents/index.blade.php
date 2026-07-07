@extends('layouts.app')

@section('title', 'Agents de scan')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-people"></i> Agents de scan</h5>
        <a href="{{ route('admin.agents.create') }}" class="btn btn-sm text-white" style="background: #7c3aed;">
            <i class="bi bi-plus-lg"></i> Nouvel agent
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    @if ($agents->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-people" style="font-size: 3rem;"></i>
        <p class="mt-2">Aucun agent de scan pour le moment.</p>
        <a href="{{ route('admin.agents.create') }}" class="btn btn-sm text-white" style="background: #7c3aed;">
            Créer un agent
        </a>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Agent</th>
                        <th>Email</th>
                        <th>Événement</th>
                        <th>Code accès</th>
                        <th class="text-center">Statut</th>
                        <th>Dernier accès</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agents as $agent)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $agent->nom }}</td>
                        <td>{{ $agent->email }}</td>
                        <td>
                            <a href="{{ route('admin.evenements.show', $agent->evenement) }}"
                                class="text-decoration-none">{{ $agent->evenement->titre }}</a>
                        </td>
                        <td><code>{{ $agent->code_acces }}</code></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $agent->actif ? 'success' : 'secondary' }}">
                                {{ $agent->actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td>{{ $agent->dernier_acces ? $agent->dernier_acces->format('d/m/Y H:i') : 'Jamais' }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.agents.show', $agent) }}"
                                class="btn btn-sm btn-outline-secondary py-0 px-2" title="Voir détails">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('admin.agents.toggle-actif', $agent) }}"
                                method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-{{ $agent->actif ? 'warning' : 'success' }} py-0 px-2"
                                    title="{{ $agent->actif ? 'Désactiver' : 'Activer' }}">
                                    <i class="bi bi-{{ $agent->actif ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.agents.destroy', $agent) }}"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('Supprimer cet agent définitivement ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection