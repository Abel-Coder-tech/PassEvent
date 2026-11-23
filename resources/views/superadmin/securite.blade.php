@extends('superadmin.layouts.master')

@section('title', 'Sécurité - Super Admin')
@section('page-title', 'Securite')

@section('content')
@php
    $ipVue = request()->ip();
    $remoteAddr = request()->server('REMOTE_ADDR');
    $xff = request()->header('X-Forwarded-For');
    $ipPrivee = ! filter_var($ipVue, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
@endphp
<div class="sa-card mb-4">
    <div class="sa-card-header"><span><i class="bi bi-hdd-network me-2" style="color: var(--sa-primary);"></i>Votre connexion vue par PaxEvent</span></div>
    <div class="sa-card-body" style="font-size:.85rem;">
        <div class="row g-2">
            <div class="col-md-3"><div class="text-muted" style="font-size:.72rem;">IP retenue (journaux, limites d'essais)</div><code>{{ $ipVue }}</code></div>
            <div class="col-md-3"><div class="text-muted" style="font-size:.72rem;">Adresse de connexion (REMOTE_ADDR)</div><code>{{ $remoteAddr }}</code></div>
            <div class="col-md-4"><div class="text-muted" style="font-size:.72rem;">En-tête X-Forwarded-For</div><code>{{ $xff ?: '—' }}</code></div>
            <div class="col-md-2"><div class="text-muted" style="font-size:.72rem;">HTTPS détecté</div><strong style="color:{{ request()->isSecure() ? 'var(--sa-success)' : 'var(--sa-danger)' }};">{{ request()->isSecure() ? 'Oui' : 'Non' }}</strong></div>
        </div>
        <div class="mt-2" style="font-size:.78rem;color:{{ $ipPrivee || ! request()->isSecure() ? 'var(--sa-danger)' : 'var(--sa-text-muted)' }};">
            @if($ipPrivee)
                <i class="bi bi-exclamation-triangle-fill me-1"></i>L'IP retenue est une adresse interne : le proxy de l'hébergeur n'est pas reconnu, toutes les visites semblent venir de la même adresse.
            @elseif(! request()->isSecure())
                <i class="bi bi-exclamation-triangle-fill me-1"></i>Le HTTPS n'est pas détecté : le proxy de l'hébergeur n'est pas reconnu.
            @else
                <i class="bi bi-check-circle-fill me-1" style="color:var(--sa-success);"></i>L'IP retenue doit être votre IP publique (comparez avec un site comme « quel est mon IP »).
            @endif
        </div>
    </div>
</div>
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
