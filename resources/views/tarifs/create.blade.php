@extends('layouts.app')

@section('title', 'Ajouter un tarif')

@section('page-title', 'Ajouter un tarif — ' . $evenement->titre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.show', $evenement->id) }}">{{ $evenement->titre }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.tarifs.index', $evenement->id) }}">Tarifs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ajouter</li>
@endsection

@section('topbar-actions')
<a href="{{ route('admin.tarifs.index', $evenement->id) }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
@endsection

@section('content')
<div class="page-content">
    <div class="panel-card" style="max-width: 600px;">
        <div class="panel-card-body p-3 p-md-4">
            <form action="{{ route('admin.tarifs.store', $evenement->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="nom" class="form-label fw-semibold">Nom du tarif <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" placeholder="Ex: Enfant, Ordinaire, VIP..." maxlength="100" required>
                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="prix" class="form-label fw-semibold">Prix (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix') }}" step="0.01" min="0" required>
                    @error('prix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if($jours->count() > 1 && ! $evenement->gratuit)
                <div class="mb-3">
                    <label for="evenement_date_id" class="form-label fw-semibold">Valable</label>
                    <select class="form-select @error('evenement_date_id') is-invalid @enderror" id="evenement_date_id" name="evenement_date_id">
                        <option value="">Tous les jours — pack</option>
                        @php $joursPris = $evenement->tarifs()->whereNotNull('evenement_date_id')->pluck('evenement_date_id'); @endphp
                        @foreach($jours as $jour)
                            <option value="{{ $jour->id }}" data-capacite="{{ $jour->capaciteEffective() }}" @selected((string) old('evenement_date_id') === (string) $jour->id) @disabled($joursPris->contains($jour->id))>
                                {{ $jour->libelle() }} — {{ $jour->date_debut->isoFormat('ddd D MMM YYYY · HH:mm') }} ({{ $jour->capaciteEffective() }} places){{ $joursPris->contains($jour->id) ? ' · a déjà son tarif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Un seul tarif par jour. « Tous les jours » crée un pack (ex. Pack Simple, Pack VIP) valable à chaque date.</small>
                    @error('evenement_date_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @endif

                <div class="mb-3">
                    <label for="quantite_disponible" class="form-label fw-semibold">Quantité disponible</label>
                    <input type="number" class="form-control @error('quantite_disponible') is-invalid @enderror" id="quantite_disponible" name="quantite_disponible" value="{{ old('quantite_disponible') }}" min="1">
                    <small class="text-muted">Laissez vide pour illimité</small>
                    @error('quantite_disponible') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="{{ route('admin.tarifs.index', $evenement->id) }}" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('evenement_date_id');
    const quantite = document.getElementById('quantite_disponible');
    if (!select || !quantite) return;

    function synchroniserMaximum() {
        const option = select.selectedOptions[0];
        quantite.max = option?.dataset.capacite || {{ (int) $evenement->capacite }};
    }

    select.addEventListener('change', synchroniserMaximum);
    synchroniserMaximum();
});
</script>
@endsection
