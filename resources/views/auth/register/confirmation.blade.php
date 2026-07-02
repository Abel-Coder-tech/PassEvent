@extends('auth.register.layout')

@section('title', 'Compte créé — PaxEvent')
@section('page-title', 'Compte créé avec succès')

@section('card-content')
<img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo" style="max-width:150px;height:auto;display:block;margin:0 auto 1rem;">
<div class="success-icon text-center"><i class="bi bi-check-circle-fill"></i></div>
<p style="text-align:center;">Votre compte est en cours de validation.</p>
<p style="text-align:center;">Vous recevrez un email de confirmation à&nbsp;<span class="text-center" style="font-weight:700;color:#542680;word-break:break-all;display:block;">{{ $email }}</span> sous 24h.</p>
<div class="text-center">
    <a href="{{ route('accueil') }}" class="btn-primary" style="display:inline-block;width:auto;padding:0.7rem 2rem;">Retour à l'accueil</a>
</div>
@endsection