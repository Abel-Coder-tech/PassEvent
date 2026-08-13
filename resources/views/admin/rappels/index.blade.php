@extends('layouts.app')

@section('title', 'Rappels')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-bell"></i> Rappels</h5>
    </div>

    @if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    @if ($evenements->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-bell" style="font-size: 3rem;"></i>
        <p class="mt-2">Aucun événement à venir pour l'instant.</p>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Événement</th>
                        <th>Date</th>
                        <th class="text-center">Billets payés</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($evenements as $evenement)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $evenement->titre }}</td>
                        <td>{{ \Carbon\Carbon::parse($evenement->date_event)->format('d/m/Y à H:i') }}</td>
                        <td class="text-center">{{ $evenement->tickets_count }}</td>
                        <td class="text-end pe-3">
                            <form action="{{ route('rappels.envoyer') }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Envoyer un rappel à tous les acheteurs de « {{ $evenement->titre }} » ?');">
                                @csrf
                                <input type="hidden" name="evenement_id" value="{{ $evenement->id }}">
                                <button type="submit" class="btn btn-sm text-white" style="background: #7c3aed;"
                                    {{ $evenement->tickets_count === 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-envelope"></i> Envoyer le rappel
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
