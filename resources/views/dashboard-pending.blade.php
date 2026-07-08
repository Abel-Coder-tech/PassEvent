@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Bienvenue')

@section('content')
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="panel-card text-center py-5">
                @if($statut === 'incomplet')
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-person-plus" style="color: var(--violet);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Finalisez votre inscription
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    {{ $message }}
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('profil.step2') }}" class="btn btn-vert btn-sm">
                        <i class="bi bi-arrow-right-circle me-1"></i> Continuer
                    </a>
                    <a href="{{ route('logout') }}" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                @elseif($statut === 'corrections_demandees')
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-pencil-square" style="color: #e0a800;"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Corrections demandées
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    {{ $message }}
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('parametres.index') }}" class="btn btn-vert btn-sm">
                        <i class="bi bi-pencil me-1"></i> Modifier mon profil
                    </a>
                    <form action="{{ route('inscriptions.resubmit') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary-custom btn-sm">
                            <i class="bi bi-send me-1"></i> Soumettre à nouveau
                        </button>
                    </form>
                    <a href="{{ route('logout') }}" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                @elseif($statut === 'rejete')
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-x-circle" style="color: var(--danger);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Inscription rejetée
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    {{ $message }}
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('logout') }}" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                @elseif($statut === 'bloque')
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-shield-exclamation" style="color: var(--danger);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Compte bloqué
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    {{ $message }}
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('logout') }}" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                @else
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="bi bi-hourglass-split" style="color: var(--violet);"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--sombre); margin-bottom: 0.75rem;">
                    Profil en cours de validation
                </h3>
                <p style="color: var(--gris); max-width: 380px; margin: 0 auto 1.5rem; line-height: 1.6;">
                    {{ $message }}
                </p>
                <div style="background: #f8f6f9; border-radius: 12px; padding: 1rem 1.5rem; display: inline-block; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="bi bi-clock-history" style="color: var(--violet); font-size: 1.25rem;"></i>
                        <span style="font-size: 0.9rem; color: var(--sombre); font-weight: 600;">
                            Délai estimé : 12h à 24h
                        </span>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('parametres.index') }}" class="btn btn-vert btn-sm">
                        <i class="bi bi-pencil me-1"></i> Modifier mon profil
                    </a>
                    <a href="{{ route('logout') }}" class="btn btn-secondary-custom btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </div>
                @endif
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </div>
        </div>
    </div>
</div>
@endsection
