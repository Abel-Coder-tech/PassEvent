@extends('auth.register.layout')

@section('title', 'Récapitulatif — PaxEvent')
@section('step', 3)
@section('page-title', 'Récapitulatif')

@section('card-content')
@if($errors->any())
    <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
        @foreach($errors->all() as $e) {{ $e }} @break @endforeach
    </div>
@endif

<div class="recap-section">
    <div class="recap-section-title">Identité</div>
    <div class="recap-row">
        <span class="recap-label">Nom</span>
        <span class="recap-value">{{ $reg['identity']['nom'] }}</span>
    </div>
    <div class="recap-row">
        <span class="recap-label">Email</span>
        <span class="recap-value">{{ $reg['email'] }}</span>
    </div>
    <div class="recap-row">
        <span class="recap-label">Téléphone</span>
        <span class="recap-value">{{ $reg['identity']['telephone'] }}</span>
    </div>
    @if(!empty($reg['identity']['avatar']))
    <div class="recap-row">
        <span class="recap-label">Photo</span>
        <span class="recap-value"><img src="{{ asset('storage/' . $reg['identity']['avatar']) }}" class="avatar-preview" alt="Avatar"></span>
    </div>
    @endif
</div>

<div class="recap-section">
    <div class="recap-section-title">Organisation</div>
    <div class="recap-row">
        <span class="recap-label">Type</span>
        <span class="recap-value">
            @if($reg['type'] === 'universitaire') <i class="bi bi-building-columns"></i>
            @elseif($reg['type'] === 'particulier') <i class="bi bi-person"></i>
            @else <i class="bi bi-building"></i>
            @endif
            {{ ucfirst($reg['type']) }}
        </span>
    </div>
    @if($reg['type'] === 'universitaire' || $reg['type'] === 'organisation')
        <div class="recap-row">
            <span class="recap-label">{{ $reg['type'] === 'universitaire' ? 'Université' : 'Organisation' }}</span>
            <span class="recap-value">{{ $reg['org_data']['organisation'] }}</span>
        </div>
        @if($reg['type'] === 'organisation')
        <div class="recap-row">
            <span class="recap-label">Type détail</span>
            <span class="recap-value">{{ ucfirst($reg['org_data']['type_detail']) }}</span>
        </div>
        @endif
    @endif
    @if(!empty($reg['org_data']['description']))
    <div class="recap-row">
        <span class="recap-label">Description</span>
        <span class="recap-value" style="max-width:70%;">{{ Str::limit($reg['org_data']['description'], 60) }}</span>
    </div>
    @endif
    <div class="recap-row">
        <span class="recap-label">Justificatif</span>
        <span class="recap-value">Fourni</span>
    </div>
</div>

<form method="POST" action="{{ route('inscriptions.confirm') }}">
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
    <button type="submit" class="btn-primary">Créer mon compte</button>
    <a href="{{ route('inscriptions.previous', 2) }}" class="btn-secondary">Précédent</a>
</form>
@endsection