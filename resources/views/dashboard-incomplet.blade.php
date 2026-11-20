@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
@endsection

@section('content')
<div class="page-content">
    <div class="app-alert alert-warn">
        <div>
            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Profil incomplet</strong>
            <span class="d-block mt-1">Finalisez votre profil pour débloquer toutes les rubriques. Vous pouvez dès à présent créer un événement.</span>
        </div>
        <a href="{{ route('profil.step2') }}" class="app-btn primary">
            <i class="bi bi-arrow-right-circle me-1"></i> Compléter mon profil
        </a>
    </div>

    <div class="section-title mb-3"><i class="bi bi-grid-1x2"></i> Que souhaitez-vous faire ?</div>

    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('admin.evenements.create') }}" class="panel-card d-block h-100 text-decoration-none" style="border-left: 4px solid var(--vert); padding: 1.4rem;">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--vert-pale); color: var(--vert); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: .9rem;">
                    <i class="bi bi-calendar-plus"></i>
                </div>
                <h3 style="font-size: 1rem; font-weight: 800; color: var(--sombre); margin-bottom: .35rem;">Créer un événement</h3>
                <p style="font-size: .8rem; color: var(--gris); line-height: 1.5; margin-bottom: 0;">
                    Préparez votre événement et ses billets. La billetterie s'ouvrira dès la validation de votre profil.
                </p>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('profil.step2') }}" class="panel-card d-block h-100 text-decoration-none" style="border-left: 4px solid var(--violet); padding: 1.4rem;">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--violet-pale); color: var(--violet); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: .9rem;">
                    <i class="bi bi-person-check"></i>
                </div>
                <h3 style="font-size: 1rem; font-weight: 800; color: var(--sombre); margin-bottom: .35rem;">Compléter mon profil</h3>
                <p style="font-size: .8rem; color: var(--gris); line-height: 1.5; margin-bottom: 0;">
                    Renseignez le type de compte et vos documents pour accéder aux autres rubriques de votre espace.
                </p>
            </a>
        </div>
    </div>
</div>
@endsection
