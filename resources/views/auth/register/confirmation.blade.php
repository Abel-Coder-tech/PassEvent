@extends('auth.register.layout')

@section('title', 'Compte créé — PaxEvent')

@section('steps')
    @include('auth.register.steps', ['current' => 4])
@endsection

@section('page-title', 'Inscription terminée')
@section('page-subtitle', 'Votre demande a été soumise avec succès')

@section('card-content')
    <div style="text-align:center;">
        <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
        <h1 style="font-size:1.25rem;font-weight:700;color:#1d1d1f;margin-bottom:.75rem;">Compte créé avec succès</h1>
        <p style="font-size:.9rem;color:#6c757d;line-height:1.6;margin-bottom:.5rem;">Votre compte est en cours de validation.</p>
        <p style="font-size:.85rem;color:#6c757d;line-height:1.6;">
            Vous recevrez un email de confirmation à<br>
            <strong style="color:#542680;word-break:break-all;">{{ $email }}</strong>
            <br>sous 12h à 24h.
        </p>
        <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:.5rem;">
            <a href="{{ route('login') }}" class="btn-primary" style="text-decoration:none;">Se connecter</a>
            <a href="{{ route('accueil') }}" class="btn-secondary" style="text-decoration:none;font-size:.85rem;">Retour à l'accueil</a>
        </div>
    </div>
@endsection
