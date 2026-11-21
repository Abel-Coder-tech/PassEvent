@extends('layouts.app')

@section('title', 'Profil incomplet')
@section('page-title', 'Accès à débloquer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profil incomplet</li>
@endsection

@section('content')
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="panel-card text-center py-5">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">
                    <i class="bi bi-lock" style="color: var(--violet);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: .75rem;">
                    Finalisez votre profil pour accéder à cette rubrique
                </h3>
                <p style="color: var(--gris); max-width: 420px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    Cette partie de votre espace sera disponible dès que votre profil sera complet et validé.
                    Vous pouvez déjà créer un événement.
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('profil.step2') }}" class="btn btn-vert btn-sm">
                        <i class="bi bi-arrow-right-circle me-1"></i> Compléter mon profil
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary-custom btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
