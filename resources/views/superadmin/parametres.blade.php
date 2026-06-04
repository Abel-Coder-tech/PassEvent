@extends('superadmin.layouts.master')

@section('title', 'Paramètres - Super Admin')
@section('page-title', 'Parametres de la plateforme')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-gear-fill me-2" style="color: var(--sa-primary);"></i>Configuration plateforme</span></div>
            <div class="sa-card-body">
                <p class="text-muted" style="font-size:0.85rem;">Les parametres de configuration sont definis dans le fichier <code>.env</code> et les fichiers de configuration Laravel.</p>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Mode maintenance</span>
                    <span class="sa-badge sa-badge-secondary">Desactive</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>PassEvent Mail</span>
                    <span class="text-muted" style="font-size:0.8rem;">passevent2026@gmail.com</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>KKiaPay</span>
                    <span class="sa-badge sa-badge-success">Configure</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sa-card">
            <div class="sa-card-header"><span><i class="bi bi-bar-chart-steps me-2" style="color: var(--sa-primary);"></i>Informations systeme</span></div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Version Laravel</span><span class="text-muted">{{ app()->version() }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Environnement</span><span class="text-muted">{{ app()->environment() }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Total utilisateurs</span><strong>{{ \App\Models\User::count() }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Total evenements</span><strong>{{ \App\Models\Evenement::count() }}</strong></div>
            </div>
        </div>
    </div>
</div>
@endsection
