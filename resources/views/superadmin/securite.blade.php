@extends('superadmin.layouts.master')

@section('title', 'Sécurité - Super Admin')
@section('page-title', 'Securite')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-shield-fill me-2" style="color: var(--sa-danger);"></i>Alertes securite</span></div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Paiements echoues</span><strong style="color:var(--sa-danger);">{{ $logsSuspects->whereIn('type_operation', ['echec_paiement'])->count() }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Erreurs paiement</span><strong style="color:var(--sa-warning);">{{ $logsSuspects->whereIn('type_operation', ['erreur_paiement'])->count() }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Total evenements annules</span><strong>{{ \App\Models\Evenement::where('statut', 'annulé')->count() }}</strong></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-exclamation-triangle-fill me-2" style="color: var(--sa-warning);"></i>Activite suspecte (50 dernieres entrees)</span></div>
            <div class="sa-card-body p-0" style="max-height:400px;overflow-y:auto;">
                <table class="sa-table">
                    <thead><tr><th>Type</th><th>Evenement</th><th>IP</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($logsSuspects as $log)
                        <tr>
                            <td><span class="sa-badge sa-badge-danger">{{ $log->type_operation }}</span></td>
                            <td>{{ $log->ticket?->evenement?->titre ?? 'N/A' }}</td>
                            <td style="font-family:monospace;font-size:0.75rem;">{{ $log->ip ?? '-' }}</td>
                            <td style="font-size:0.75rem;">{{ $log->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucune activite suspecte</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
