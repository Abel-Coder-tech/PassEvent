@extends('layouts.app')

@section('title', 'Modifier le tarif')

@section('page-title', 'Modifier le tarif')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.show', $evenement->id) }}">{{ $evenement->titre }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.tarifs.index', $evenement->id) }}">Tarifs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modifier</li>
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
            <form action="{{ route('admin.tarifs.update', [$evenement->id, $tarif->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nom" class="form-label fw-semibold">Nom du tarif <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $tarif->nom) }}" maxlength="100" required>
                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @php
                    $aVendu = $evenement->tarifs()->sum('quantite_vendue') > 0;
                @endphp

                <div class="mb-3">
                    <label for="prix" class="form-label fw-semibold">Prix (FCFA)</label>
                    <input type="number" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix', $tarif->prix) }}" step="0.01" min="0">
                    @error('prix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($aVendu)
                        <small class="text-muted d-block mt-1">Des billets ont déjà été vendus : leurs acheteurs gardent le prix payé, le nouveau prix s'applique aux prochains achats.</small>
                    @else
                        <small class="text-muted">Vous pouvez modifier librement le prix.</small>
                    @endif
                </div>

                @if($jours->count() > 1 && ! $evenement->gratuit)
                <div class="mb-3">
                    <label for="evenement_date_id" class="form-label fw-semibold">Valable</label>
                    <select class="form-select @error('evenement_date_id') is-invalid @enderror" id="evenement_date_id" name="evenement_date_id">
                        <option value="">Tous les jours — pack</option>
                        @foreach($jours as $jour)
                            <option value="{{ $jour->id }}" data-capacite="{{ $jour->capaciteEffective() }}" @selected((string) old('evenement_date_id', $tarif->evenement_date_id) === (string) $jour->id)>
                                {{ $jour->libelle() }} — {{ $jour->date_debut->isoFormat('ddd D MMM YYYY · HH:mm') }} ({{ $jour->capaciteEffective() }} places)
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        Un jour précis rend le billet valable uniquement ce jour-là. « Tous les jours » crée un pack valable à chaque date, en vente jusqu’à minuit du premier jour payant.
                        Les billets déjà vendus conservent le jour figé au moment de l'achat.
                    </small>
                    @error('evenement_date_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @endif

                <div class="mb-3">
                    <label for="quantite_disponible" class="form-label fw-semibold">Quantité disponible</label>
                    <input type="number" class="form-control @error('quantite_disponible') is-invalid @enderror" id="quantite_disponible" name="quantite_disponible" value="{{ old('quantite_disponible', $tarif->quantite_disponible) }}" min="1">
                    <small class="text-muted">Laissez vide pour illimité</small>
                    @error('quantite_disponible') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="{{ route('admin.tarifs.index', $evenement->id) }}" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
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
