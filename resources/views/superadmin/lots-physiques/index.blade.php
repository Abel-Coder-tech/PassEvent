@extends('superadmin.layouts.master')

@section('title', 'Tickets physiques - Super Admin')
@section('page-title', 'Tickets physiques')

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0" style="font-size:0.85rem;">Lots de tickets physiques générés pour les organisateurs (vente au guichet).</p>
    <a href="{{ route('superadmin.tickets-physiques.creer') }}" class="sa-btn sa-btn-primary">
        <i class="bi bi-plus-lg"></i> Generer un lot
    </a>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i>Lots</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $lots->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        @if($lots->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-ticket-perforated" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Aucun lot généré pour le moment.</p>
        </div>
        @else
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Organisateur</th>
                    <th>Evenement</th>
                    <th>Tarif</th>
                    <th class="text-center">Tickets</th>
                    <th class="text-center">Annués</th>
                    <th class="text-center">Scannés</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lots as $lot)
                <tr>
                    <td><strong>{{ $lot->nom }}</strong></td>
                    <td>{{ $lot->user?->nom ?? '---' }}</td>
                    <td>{{ $lot->evenement?->titre ?? '---' }}</td>
                    <td>{{ $lot->tarif?->nom ?? '---' }}</td>
                    <td class="text-center">{{ $lot->nb_tickets }}</td>
                    <td class="text-center">@if($lot->nb_annules > 0)<span class="sa-badge sa-badge-danger">{{ $lot->nb_annules }}</span>@else 0 @endif</td>
                    <td class="text-center">@if($lot->nb_scannes > 0)<span class="sa-badge sa-badge-success">{{ $lot->nb_scannes }}</span>@else 0 @endif</td>
                    <td>
                        @if($lot->estTransmis)
                            <span class="sa-badge sa-badge-success">Transmis {{ $lot->transmis_at?->format('d/m') }}</span>
                        @else
                            <span class="sa-badge sa-badge-warning">Genere</span>
                        @endif
                    </td>
                    <td class="text-end" style="white-space:nowrap;">
                        <div class="d-flex flex-nowrap gap-1 justify-content-end">
                            <a href="{{ route('superadmin.tickets-physiques.voir', $lot) }}" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.45rem;border-radius:6px;font-size:0.7rem;line-height:1;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;" title="Voir le lot">
                                <i class="bi bi-eye"></i>
                            </a>
                            @unless($lot->estTransmis)
                                <form action="{{ route('superadmin.tickets-physiques.transmettre', $lot) }}" method="POST" class="d-inline" onsubmit="return confirm('Transmettre ce lot à l\'organisateur ? Il pourra alors télécharger la planche de QR codes.')">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Transmettre"><i class="bi bi-send-fill"></i></button>
                                </form>
                                <form action="{{ route('superadmin.tickets-physiques.supprimer', $lot) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce lot et ses {{ $lot->nb_tickets }} tickets ? Cette action est irreversible.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">
            {{ $lots->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
