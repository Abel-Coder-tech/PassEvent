@extends('layouts.public')

@section('title', 'Compte créé — PaxEvent')

@section('styles')
<style>
    body { background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%); }
    .card-register {
        background: #fff;
        border-radius: 20px;
        padding: 1.5rem;
        max-width: 440px;
        margin: 0 auto;
        box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        text-align: center;
    }
    .card-register .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }
    .success-icon { font-size: 3.5rem; color: #2E7D4F; margin-bottom: 1rem; }
    .card-register h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .75rem; }
    .card-register p { font-size: .9rem; color: #6c757d; line-height: 1.6; margin-bottom: .5rem; }
    .card-register .email-highlight { font-weight: 700; color: #542680; word-break: break-all; }
    .card-register .btn-primary {
        display: inline-block; background: #542680; border: none; border-radius: 10px; padding: .7rem 2rem;
        font-weight: 600; text-decoration: none; color: #fff; transition: .2s; margin-top: 1.25rem;
    }
    .card-register .btn-primary:hover { background: #451e68; transform: translateY(-1px); color: #fff; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card-register">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
                <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
                <h1>Compte créé avec succès</h1>
                <p>Votre compte est en cours de validation.</p>
                <p>Vous recevrez un email de confirmation à&nbsp;<span class="email-highlight">{{ $email }}</span> sous 24h.</p>
                <a href="{{ route('accueil') }}" class="btn-primary">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</div>
@endsection
