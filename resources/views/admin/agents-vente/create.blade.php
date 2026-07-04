@extends('layouts.app')

@section('title', 'Nouvel agent de vente')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('admin.agents-vente.index') }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h5 class="fw-bold mt-2"><i class="bi bi-person-plus"></i> Nouvel agent de vente</h5>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.agents-vente.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nom complet</label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                value="{{ old('nom') }}" required>
                            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Événement</label>
                            <select name="evenement_id" class="form-select @error('evenement_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un événement...</option>
                                @foreach ($evenements as $e)
                                <option value="{{ $e->id }}" @selected(old('evenement_id')==$e->id)>
                                    {{ $e->titre }} ({{ $e->date_event->format('d/m/Y') }})
                                    &mdash; {{ $e->agents_ventes_count ?? 0 }} agent(s)
                                </option>
                                @endforeach
                            </select>
                            @error('evenement_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle"></i>
                            Un email sera envoyé automatiquement avec les identifiants de connexion
                            et le code de vente à 6 chiffres.
                        </div>
                        <button type="submit" class="btn text-white w-100 py-2" style="background: #7c3aed;">
                            <i class="bi bi-person-plus"></i> Créer l'agent 
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
