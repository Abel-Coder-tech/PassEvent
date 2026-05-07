@extends('layouts.app')

@section('title', 'Messages - PassEvent')

@section('page-title', 'Messages de contact')

@section('topbar-actions')
    @if($nonLus > 0)
        <span class="badge bg-danger">{{ $nonLus }} non lu{{ $nonLus > 1 ? 's' : '' }}</span>
    @endif
@endsection

@section('content')
<div class="page-content">
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-envelope me-2" style="color: var(--violet);"></i>Messages recus</h5>
            <div class="d-flex gap-2 align-items-center">
                @if($nonLus > 0)
                    <span class="badge bg-danger">{{ $nonLus }} non lu{{ $nonLus > 1 ? 's' : '' }}</span>
                @endif
            </div>
        </div>
        <div class="panel-card-body p-0">
            @if($messages->count() > 0)
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th>Expediteur</th>
                                <th>Objet</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $msg)
                                <tr>
                                    <td>
                                        @if(!$msg->lu)
                                            <span class="badge bg-primary">Nouveau</span>
                                        @elseif($msg->reponse_admin)
                                            <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Repondu</span>
                                        @else
                                            <span class="status-badge status-termine">Lu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="font-size: 0.85rem;">{{ $msg->nom_complet }}</div>
                                        <small class="text-muted">{{ $msg->email }}</small>
                                    </td>
                                    <td>{{ Str::limit($msg->objet, 50) }}</td>
                                    <td>
                                        <div style="font-size: 0.82rem;">{{ $msg->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $msg->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.messages.show', $msg->id) }}" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px; padding: 0.25rem 0.5rem;" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.messages.destroy', $msg->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce message ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="border-radius: 6px; padding: 0.25rem 0.5rem; border: 1px solid var(--danger); color: var(--danger); background: transparent;" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-envelope-open" style="font-size: 3rem; color: var(--gris);"></i>
                    <p class="text-muted mt-3 mb-0">Aucun message recu pour le moment.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
