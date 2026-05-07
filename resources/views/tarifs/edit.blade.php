@extends('layouts.app')

@section('title', 'Modifier le tarif')

@section('page-title', 'Modifier le tarif')

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

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="categorie" class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                        <select class="form-select @error('categorie') is-invalid @enderror" id="categorie" name="categorie" required>
                            <option value="etudiant" {{ old('categorie', $tarif->categorie) == 'etudiant' ? 'selected' : '' }}>Étudiant</option>
                            <option value="externe" {{ old('categorie', $tarif->categorie) == 'externe' ? 'selected' : '' }}>Externe</option>
                        </select>
                        @error('categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="normal" {{ old('type', $tarif->type) == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="vip" {{ old('type', $tarif->type) == 'vip' ? 'selected' : '' }}>VIP</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="prix" class="form-label fw-semibold">Prix (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix', $tarif->prix) }}" step="0.01" min="0" required>
                    @error('prix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

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
