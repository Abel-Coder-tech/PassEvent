@extends('superadmin.layouts.master')

@section('title', 'Statistiques - Super Admin')
@section('page-title', 'Statistiques')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-bar-chart-fill me-2" style="color: var(--sa-primary);"></i>Taux de remplissage par evenement</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Evenement</th><th>Capacite</th><th>Vendus</th><th>Taux remplissage</th><th>Recettes</th><th>Date</th><th>Statut</th></tr>
            </thead>
            <tbody>
                @foreach($evenements as $ev)
                @php
                    $taux = $ev->capacite > 0 ? round(($ev->quota_vendu / $ev->capacite) * 100) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $ev->titre }}</strong></td>
                    <td>{{ $ev->capacite }}</td>
                    <td>{{ $ev->quota_vendu }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="flex:1;height:6px;background:#eee;border-radius:3px;overflow:hidden;">
                                <div style="height:100%;width:{{ min($taux,100) }}%;background:{{ $taux >= 80 ? 'var(--sa-success)' : ($taux >= 50 ? 'var(--sa-warning)' : 'var(--sa-primary)') }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:0.75rem;font-weight:600;">{{ $taux }}%</span>
                        </div>
                    </td>
                    <td>{{ number_format($ev->recettes ?? 0, 0, ',', ' ') }} F</td>
                    <td style="font-size:0.75rem;">{{ $ev->date_event->isoFormat('D MMM YYYY') }}</td>
                    <td>
                        @if($ev->statut === 'publié') <span class="sa-badge sa-badge-success">Publie</span>
                        @elseif($ev->statut === 'brouillon') <span class="sa-badge sa-badge-secondary">Brouillon</span>
                        @else <span class="sa-badge sa-badge-danger">{{ $ev->statut }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
