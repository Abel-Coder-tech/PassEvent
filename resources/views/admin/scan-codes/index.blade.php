@extends('layouts.app')

@section('title', 'Codes d\'accès scan')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-key"></i> Codes d'accès scan</h5>
    </div>

    @if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Événement</th>
                        <th>Date</th>
                        <th>Codes générés</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evenements as $evenement)
                    <tr>
                        <td class="ps-3 fw-medium">
                            <a href="{{ route('admin.evenements.show', $evenement) }}" class="text-decoration-none">
                                {{ $evenement->titre }}
                            </a>
                        </td>
                        <td>{{ $evenement->date_event->format('d/m/Y') }}</td>
                        <td>
                            @php $codes = $evenement->scanAccessCodes()->orderByDesc('created_at')->get(); @endphp
                            @if ($codes->count() > 0)
                                @foreach ($codes as $sac)
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <code style="font-size:0.78rem;letter-spacing:1px;">{{ $sac->code }}</code>
                                    @if(!$sac->actif)
                                    <span class="badge bg-secondary" style="font-size:0.6rem;">Inactif</span>
                                    @endif
                                    <form action="{{ route('admin.evenements.scan-codes.destroy', [$evenement->id, $sac->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce code ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm p-0 text-danger" title="Supprimer" style="font-size:0.7rem;line-height:1;">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <form action="{{ route('admin.evenements.scan-codes.generate', $evenement->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2">
                                    <i class="bi bi-plus-lg"></i> Générer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size:1.5rem;"></i><br>
                            Aucun événement. Créez d'abord un événement.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection