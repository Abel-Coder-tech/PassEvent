@extends('layouts.app')

@section('title', 'Récapitulatif')
@section('page-title', 'Finaliser la création de votre compte')

@section('content')
<style>
    .wizard-card {
        max-width: 640px; margin: 0 auto; background: #fff; border-radius: 16px;
        padding: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.04);
    }
    .wizard-step { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; }
    .wizard-step .step { display: flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #ccc; font-weight: 500; }
    .wizard-step .step.active { color: #542680; font-weight: 700; }
    .wizard-step .step.done { color: #2e7d4f; font-weight: 600; }
    .wizard-step .num { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; background: #e0dde3; color: #fff; flex-shrink: 0; }
    .wizard-step .done .num { background: #2e7d4f; }
    .wizard-step .active .num { background: #542680; }
    .wizard-step .connector { width: 24px; height: 2px; background: #e0dde3; }
    .wizard-step .connector.done { background: #2e7d4f; }
</style>
<div class="page-content">
    <div class="wizard-card">
        <div class="wizard-step">
            <div class="step done"><span class="num"><i class="bi bi-check" style="font-size:0.7rem;"></i></span> Type &amp; Justificatifs</div>
            <div class="connector done"></div>
            <div class="step active"><span class="num">2</span> Récapitulatif</div>
        </div>
        <hr style="margin:0.75rem 0 1.25rem;border-color:#f0eeec;">

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <div class="recap-section">
            <div class="recap-section-title">Identité</div>
            <div class="recap-row">
                <span class="recap-label">Nom</span>
                <span class="recap-value">{{ $user->nom }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Email</span>
                <span class="recap-value">{{ $user->email }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Téléphone</span>
                <span class="recap-value">{{ $user->telephone }}</span>
            </div>
            @if($user->avatar)
            <div class="recap-row">
                <span class="recap-label">Photo</span>
                <span class="recap-value"><img src="{{ asset('storage/' . $user->avatar) }}" class="avatar-preview" alt="Avatar"></span>
            </div>
            @endif
        </div>

        <div class="recap-section">
            <div class="recap-section-title">Organisation</div>
            <div class="recap-row">
                <span class="recap-label">Type</span>
                <span class="recap-value">
                    @if($data['type'] === 'universitaire') <i class="bi bi-building-columns"></i>
                    @elseif($data['type'] === 'particulier') <i class="bi bi-person"></i>
                    @else <i class="bi bi-building"></i>
                    @endif
                    {{ ucfirst($data['type']) }}
                </span>
            </div>
            @if($data['type'] === 'universitaire' || $data['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label">{{ $data['type'] === 'universitaire' ? 'Université' : 'Organisation' }}</span>
                    <span class="recap-value">{{ $data['organisation'] }}</span>
                </div>
                @if($data['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label">Type détail</span>
                    <span class="recap-value">{{ ucfirst($data['type_detail']) }}</span>
                </div>
                @endif
            @endif
            <div class="recap-row">
                <span class="recap-label">Justificatif</span>
                <span class="recap-value">Fourni</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Signature</span>
                <span class="recap-value">Fournie</span>
            </div>
        </div>

        <form method="POST" action="{{ route('profil.submit') }}">
            @csrf
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cgu" id="cgu" value="1" required>
                    <label class="form-check-label" for="cgu">
                        J'accepte les <a href="{{ route('cgu') }}" target="_blank">conditions générales d'utilisation</a>
                        et la <a href="{{ route('confidentialite') }}" target="_blank">politique de confidentialité</a>
                    </label>
                    @error('cgu') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background:#542680;border:none;border-radius:10px;padding:0.7rem 1rem;font-weight:600;">Soumettre pour validation</button>
            <a href="{{ route('profil.step2') }}" class="btn btn-secondary w-100 d-block text-center" style="border-radius:10px;padding:0.6rem 1rem;font-weight:600;margin-top:0.5rem;text-decoration:none;">Modifier les informations</a>
        </form>
    </div>
</div>
@endsection
