@extends('layouts.affiliate')

@section('title', 'Profil — Espace affilié PaxEvent')

@section('content')
    <h1 class="h4 fw-bold mb-1">Mon profil</h1>
    <p class="text-muted small mb-4">Gérez vos informations personnelles et vos coordonnées de paiement.</p>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card aff-card">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">Informations du compte</h2>
                    <form method="POST" action="{{ route('affiliate.profil.update') }}" class="row g-3">
                        @csrf
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Prénom</label>
                            <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom', $affiliate->prenom) }}" required>
                            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Nom</label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $affiliate->nom) }}" required>
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $affiliate->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Téléphone</label>
                            <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                                   value="{{ old('telephone', $affiliate->telephone) }}">
                            @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12"><hr class="my-1"></div>
                        @php
                            $missing = array_filter([
                                'opérateur' => !$affiliate->operateur_paiement,
                                'numéro Mobile Money' => !$affiliate->numero_paiement,
                                'titulaire' => !$affiliate->titulaire_paiement,
                            ]);
                        @endphp
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-wallet2" style="color: var(--violet);"></i>
                                    <span class="fw-semibold small">Coordonnées de paiement</span>
                                </div>
                                @if(empty($missing))
                                    <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-check2-circle me-1"></i>Complet</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-exclamation-triangle me-1"></i>À compléter</span>
                                @endif
                            </div>
                            @if(!empty($missing))
                                <div class="text-muted small mt-1">Requis pour demander un retrait : {{ implode(', ', array_keys($missing)) }}.</div>
                            @endif
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-muted mb-1">Opérateur</label>
                            <select name="operateur_paiement" class="form-select @error('operateur_paiement') is-invalid @enderror">
                                <option value="">— Choisir —</option>
                                @foreach(['MTN MoMo', 'Moov Money', 'Celtiis Cash'] as $op)
                                    <option value="{{ $op }}" @selected(old('operateur_paiement', $affiliate->operateur_paiement) === $op)>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('operateur_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="numero_paiement" class="form-label small fw-semibold text-muted mb-1">Numéro Mobile Money</label>
                            <input id="numero_paiement" type="text" name="numero_paiement" class="form-control @error('numero_paiement') is-invalid @enderror"
                                   value="{{ old('numero_paiement', $affiliate->numero_paiement) }}">
                            <div class="form-text">Indiquez le numéro associé à votre compte chez l'opérateur choisi.</div>
                            @error('numero_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Titulaire du compte</label>
                            <input type="text" name="titulaire_paiement" class="form-control @error('titulaire_paiement') is-invalid @enderror"
                                   value="{{ old('titulaire_paiement', $affiliate->titulaire_paiement) }}">
                            @error('titulaire_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-violet"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card aff-card">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">Mot de passe</h2>
                    <form method="POST" action="{{ route('affiliate.profil.password') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-violet w-100"><i class="bi bi-shield-lock me-1"></i> Modifier le mot de passe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
