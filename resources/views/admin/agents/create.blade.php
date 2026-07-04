@extends('layouts.app')

@section('title', 'Nouvel agent - PaxEvent')

@section('page-title', 'Créer un agent de scan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
    <li class="breadcrumb-item active" aria-current="page">Nouvel agent</li>
@endsection

@section('content')
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="panel-card">
                <div class="panel-card-header">
                    <h5><i class="bi bi-person-plus me-2" style="color: var(--violet);"></i>Informations de l'agent</h5>
                </div>
                <div class="panel-card-body">
                    <form method="POST" action="{{ route('admin.agents.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required placeholder="Nom de l'agent">
                            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="agent@exemple.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Min. 8 caractères" minlength="8">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">L'agent utilisera ce mot de passe pour se connecter.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Événement assigné</label>
                            <select name="evenement_id" class="form-select @error('evenement_id') is-invalid @enderror" required>
                                <option value="">-- Sélectionner un événement --</option>
                                @foreach($evenements as $ev)
                                    <option value="{{ $ev->id }}" @selected(old('evenement_id') == $ev->id)>
                                        {{ $ev->titre }} — {{ $ev->date_event ? $ev->date_event->format('d/m/Y') : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evenement_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">L'agent sera affecté à cet événement pour le scan des tickets.</div>
                        </div>

                        <div class="alert alert-info py-2" style="font-size:0.85rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Un code d'accès à 6 chiffres sera généré automatiquement. L'agent recevra un email avec ses identifiants et les détails de l'événement.
                        </div>

                        <button type="submit" class="btn text-white w-100 py-2" style="background: #7c3aed;">
                            <i class="bi bi-person-plus me-1"></i> Créer l'agent de scan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
