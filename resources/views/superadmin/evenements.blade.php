@extends('superadmin.layouts.master')

@section('title', 'Événements - Super Admin')
@section('page-title', 'Evenements')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-calendar-event-fill me-2" style="color: var(--sa-primary);"></i>Tous les evenements</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $evenements->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Evenement</th>
                    <th>Organisateur</th>
                    <th>Statut</th>
                    <th>Places vendues</th>
                    <th>Recettes</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evenements as $ev)
                <tr>
                    <td><strong>{{ $ev->titre }}</strong></td>
                    <td>{{ $ev->user->nom ?? '-' }}</td>
                    <td>
                        @if($ev->statut === 'publié') <span class="sa-badge sa-badge-success">Publie</span>
                        @elseif($ev->statut === 'brouillon') <span class="sa-badge sa-badge-secondary">Brouillon</span>
                        @elseif($ev->statut === 'annulé') <span class="sa-badge sa-badge-danger">Annule</span>
                        @else <span class="sa-badge sa-badge-warning">{{ $ev->statut }}</span>
                        @endif
                    </td>
                    <td>{{ $ev->tickets_vendus ?? 0 }} / {{ $ev->capacite }}</td>
                    <td>{{ number_format($ev->recettes ?? 0, 0, ',', ' ') }} F</td>
                    <td style="font-size:0.75rem;">{{ $ev->date_event->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <form action="{{ route('superadmin.evenements.masquer', $ev) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Masquer"><i class="bi bi-eye-slash"></i></button>
                            </form>
                            <form action="{{ route('superadmin.evenements.suspendre', $ev) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspendre {{ $ev->titre }} ?')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Suspendre"><i class="bi bi-pause-fill"></i></button>
                            </form>
                            <form action="{{ route('superadmin.evenements.supprimer', $ev) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer definitivement {{ $ev->titre }} ? Cette action est irreversible.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash-fill"></i></button>
                            </form>
                            @if($ev->statut !== 'publié')
                                <form action="{{ route('superadmin.evenements.mettre-en-avant', $ev) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-success);color:#fff;border:none;" title="Publier"><i class="bi bi-check-lg"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $evenements->links() }}</div>
@endsection
