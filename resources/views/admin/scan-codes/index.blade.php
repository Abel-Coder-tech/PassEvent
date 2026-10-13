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

    {{-- Vue tableau : écrans >= md --}}
    <div class="card border-0 shadow-sm d-none d-md-block">
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
                            @php $codes = $evenement->scanAccessCodes->sortByDesc('created_at'); @endphp
                            @if ($codes->count() > 0)
                                @foreach ($codes as $sac)
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <code style="font-size:0.78rem;letter-spacing:1px;">{{ $sac->code }}</code>
                                    <button type="button" class="js-copier-code btn btn-sm p-0 text-secondary" data-code="{{ $sac->code }}" title="Copier le code" style="font-size:0.72rem;line-height:1;">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
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
                                <button type="submit" class="btn btn-sm py-1 px-2" style="background:#7B3FA0;color:#fff;border-radius:6px;">
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

    {{-- Vue cartes : écrans < md --}}
    <div class="d-md-none">
        @forelse ($evenements as $evenement)
        @php $codes = $evenement->scanAccessCodes->sortByDesc('created_at'); @endphp
        <div class="card border-0 shadow-sm mb-2">
            <div class="card-body p-3">
                <a href="{{ route('admin.evenements.show', $evenement) }}" class="text-decoration-none fw-semibold d-block text-truncate" style="font-size:0.9rem;">
                    {{ $evenement->titre }}
                </a>
                <small class="text-muted">{{ $evenement->date_event->format('d/m/Y') }}</small>

                @if ($codes->count() > 0)
                <div class="d-flex flex-column gap-2 mt-3 mb-3">
                    @foreach ($codes as $sac)
                    <div class="d-flex align-items-center justify-content-between gap-2 rounded-3 px-2 py-2" style="background:#f8f7fa;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-key" style="color:{{ $sac->actif ? '#7B3FA0' : '#bbb' }};font-size:0.85rem;"></i>
                            <code style="font-size:0.82rem;letter-spacing:1px;">{{ $sac->code }}</code>
                            @if(!$sac->actif)
                            <span class="badge bg-secondary" style="font-size:0.6rem;">Inactif</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <button type="button" class="js-copier-code btn btn-sm p-1" data-code="{{ $sac->code }}" title="Copier le code" style="color:#7B3FA0;line-height:1;">
                                <i class="bi bi-clipboard"></i>
                            </button>
                            <form action="{{ route('admin.evenements.scan-codes.destroy', [$evenement->id, $sac->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce code ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm p-1" style="color:#dc3545;line-height:1;" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted small mt-3 mb-3">Aucun code d'accès généré.</p>
                @endif

                <form action="{{ route('admin.evenements.scan-codes.generate', $evenement->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm w-100" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-plus-lg me-1"></i> Générer un code d'accès
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size:1.5rem;"></i><br>
                Aucun événement. Créez d'abord un événement.
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
