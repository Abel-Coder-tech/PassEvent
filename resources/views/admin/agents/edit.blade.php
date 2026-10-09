@extends('layouts.app')

@section('title', 'Modifier l\'agent de scan')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('admin.agents.show', $agent) }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Retour à l'agent
        </a>
        <h5 class="fw-bold mt-2">
            <i class="bi bi-{{ $reaffectable ? 'arrow-repeat' : 'pencil-square' }}"></i>
            {{ $reaffectable ? 'Réaffecter l\'agent de scan' : 'Modifier l\'agent de scan' }}
        </h5>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.agents.update', $agent) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nom complet</label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                value="{{ old('nom', $agent->nom) }}" required>
                            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $agent->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Un email ne peut pas servir à la fois pour un agent de scan et un agent de vente.</div>
                        </div>

                        @if ($reaffectable)
                        @php
                            $selection = old('evenement_id', $agent->evenement_id);
                            $autresEvenements = $evenements->reject(fn ($ev) => $ev->id === $agent->evenement_id)->values();
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Événement</label>
                            <select name="evenement_id" class="form-select @error('evenement_id') is-invalid @enderror" required>
                                <option value="{{ $agent->evenement_id }}" @selected($selection == $agent->evenement_id)>
                                    {{ $agent->evenement->titre }} — événement actuel
                                </option>
                                @foreach ($autresEvenements as $ev)
                                <option value="{{ $ev->id }}" @selected($selection == $ev->id)>
                                    {{ $ev->titre }} — {{ $ev->date_event ? $ev->date_event->format('d/m/Y') : 'Date libre' }}
                                </option>
                                @endforeach
                            </select>
                            @error('evenement_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">L'agent conservera son historique de scans, désormais visible sur tous ses événements.</div>
                        </div>

                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Un agent ne peut être réaffecté que s'il est inactif ou si son événement est passé, annulé ou en brouillon.
                        </div>
                        @else
                        <input type="hidden" name="evenement_id" value="{{ $agent->evenement_id }}">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Événement</label>
                            <div class="form-control d-flex align-items-center justify-content-between" style="background:#f8f9fa;">
                                <span class="small">{{ $agent->evenement->titre }}</span>
                                <span class="badge bg-secondary">Affectation actuelle</span>
                            </div>
                            <div class="form-text">
                                Cet agent est actif sur un événement à venir : désactivez-le pour pouvoir le réaffecter à un autre événement.
                            </div>
                        </div>
                        @endif

                        <button type="submit" class="btn text-white w-100 py-2" style="background: #7c3aed;">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
