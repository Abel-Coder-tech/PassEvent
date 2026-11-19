@extends('superadmin.layouts.master')

@section('title', 'Événements internes - Super Admin')
@section('page-title', 'Événements internes')

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <p class="text-muted small mb-0">
        <i class="bi bi-eye-slash me-1"></i>Événements créés par PaxEvent hors billetterie en ligne : toujours masqués du public.
        Leurs QR codes se génèrent dans « QR hors plateforme » et se scannent avec PaxAgent.
    </p>
    @if ($evenements->total() > 0)
        <a href="{{ route('superadmin.evenements-internes.creer') }}" class="btn btn-sm btn-primary ms-auto" style="background:var(--sa-primary);border-color:var(--sa-primary);">
            <i class="bi bi-plus-lg me-1"></i>Nouvel événement
        </a>
    @endif
</div>

<div class="sa-card">
    <div class="sa-card-body p-0">
        <div class="table-responsive">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Événement</th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th class="text-center">Tarifs</th>
                        <th class="text-center">QR générés</th>
                        <th class="text-center">Scannés</th>
                        <th class="text-center">Agents de scan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evenements as $ev)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $ev->titre }}</div>
                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $ev->lieu }}</div>
                            </td>
                            <td>{{ $ev->auteurComplet() ?? '—' }}</td>
                            <td>
                                {{ $ev->date_event?->isoFormat('D MMM YYYY') ?? '—' }}
                                @if ($ev->dates->count() > 1)
                                    <span class="text-muted small">(+{{ $ev->dates->count() - 1 }} date{{ $ev->dates->count() > 2 ? 's' : '' }})</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $ev->tarifs_count }}</td>
                            <td class="text-center">{{ $ev->nb_tickets }}</td>
                            <td class="text-center">{{ $ev->nb_scannes }}</td>
                            <td class="text-center">{{ $ev->agents_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.evenements-internes.voir', $ev) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-plus d-block mb-2" style="font-size:2rem;"></i>
                                Aucun événement interne pour le moment.
                                <div class="mt-3">
                                    <a href="{{ route('superadmin.evenements-internes.creer') }}" class="btn btn-sm btn-primary" style="background:var(--sa-primary);border-color:var(--sa-primary);">
                                        <i class="bi bi-plus-lg me-1"></i>Créer un événement interne
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $evenements->links() }}</div>
@endsection
