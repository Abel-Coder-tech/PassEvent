@extends('superadmin.layouts.master')

@section('title', 'Journal général - Super Admin')
@section('page-title', 'Journal général')

@section('content')
<style>
    .sa-label { display: block; font-size: 0.72rem; font-weight: 600; color: var(--sa-text-muted); margin-bottom: 0.25rem; }
    .sa-form-control-sm { padding: 0.35rem 0.6rem; font-size: 0.78rem; }
</style>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="sa-card"><div class="sa-card-body py-3">
            <div class="text-muted" style="font-size:0.72rem;">Opérations</div>
            <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($stats['total']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="sa-card"><div class="sa-card-body py-3">
            <div class="text-muted" style="font-size:0.72rem;">Achats</div>
            <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($stats['achats']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="sa-card"><div class="sa-card-body py-3">
            <div class="text-muted" style="font-size:0.72rem;">Envois / Scans</div>
            <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($stats['envois']) }} / {{ number_format($stats['scans']) }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="sa-card"><div class="sa-card-body py-3">
            <div class="text-muted" style="font-size:0.72rem;">Échecs de paiement</div>
            <div class="fw-bold" style="font-size:1.4rem; color:var(--sa-danger);">{{ number_format($stats['echecs']) }}</div>
        </div></div>
    </div>
</div>

<div class="sa-card mb-4">
    <div class="sa-card-body py-3">
        <form method="GET" action="{{ route('superadmin.logs') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="sa-label">Recherche</label>
                <input type="text" name="q" class="sa-form-control sa-form-control-sm" placeholder="Acheteur, email, code ticket ou IP" value="{{ $q }}" autocomplete="off">
            </div>
            <div class="col-6 col-md-3">
                <label class="sa-label">Type d'opération</label>
                <select name="type" class="sa-form-control sa-form-control-sm">
                    <option value="">Tous</option>
                    @foreach($types as $valeur => $infos)
                        <option value="{{ $valeur }}" @selected($type === $valeur)>{{ $infos['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="sa-label">Période</label>
                <select name="periode" class="sa-form-control sa-form-control-sm">
                    @foreach(['7' => '7 jours', '30' => '30 jours', '90' => '3 mois', 'annee' => 'Cette année', 'tout' => 'Tout'] as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected((string) $periode === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm flex-fill"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                <a href="{{ route('superadmin.logs') }}" class="sa-btn sa-btn-sm" style="background:#f1f2f6;border:1px solid #e3e6ed;color:#555;" title="Réinitialiser">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-journal-text me-2" style="color: var(--sa-primary);"></i>Journal des actions — toutes organisations</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $logs->total() }} entrées</span>
    </div>
    <div class="sa-card-body p-0" style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr><th>Email acheteur</th><th>Action</th><th>Événement</th><th>Détails</th><th>IP</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size:0.75rem;">{{ $log->ticket?->email_acheteur ?? 'Système' }}</td>
                    <td>
                        @php $info = $types[$log->type_operation] ?? null; @endphp
                        <span class="sa-badge sa-badge-info" @if($info) style="color:{{ $info['color'] }};" @endif>{{ $info['label'] ?? $log->type_operation }}</span>
                    </td>
                    <td>{{ $log->ticket?->evenement?->titre ?? 'N/A' }}</td>
                    <td style="font-size:0.72rem; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $log->details ? (is_string($log->details) ? \Illuminate\Support\Str::limit($log->details, 60) : \Illuminate\Support\Str::limit(json_encode($log->details), 60)) : '-' }}
                    </td>
                    <td style="font-family:monospace;font-size:0.75rem;">{{ $log->ip ?? '-' }}</td>
                    <td style="font-size:0.72rem; white-space:nowrap;">{{ $log->created_at->isoFormat('D MMM YYYY HH:mm:ss') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune opération sur cette période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $logs->links() }}</div>
@endsection
