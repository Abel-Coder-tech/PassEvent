@extends('layouts.app')

@section('title', 'Modifier l\'événement')

@section('page-title', 'Modifier l\'événement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modifier</li>
@endsection

@section('topbar-actions')
<a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
@endsection

@section('content')
<div class="page-content">
    <div class="panel-card" style="max-width: 700px;">
        <div class="panel-card-body p-3 p-md-4">
            <form action="{{ route('admin.evenements.update', $evenement->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="titre" class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre', $evenement->titre) }}" required>
                    @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $evenement->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="date_event" class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('date_event') is-invalid @enderror" id="date_event" name="date_event" value="{{ old('date_event', $evenement->date_event->format('Y-m-d\TH:i')) }}" required>
                        @error('date_event') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="lieu" class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('lieu') is-invalid @enderror" id="lieu" name="lieu" value="{{ old('lieu', $evenement->lieu) }}" required>
                        @error('lieu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="categorie" class="form-label fw-semibold">Categorie <span class="text-danger">*</span></label>
                    <select class="form-select @error('categorie') is-invalid @enderror" id="categorie" name="categorie" required>
                        <option value="">Selectionner une categorie</option>
                        @php
                            $catList = ['Sport', 'Soiree gala', 'Ceremonie officielle', 'Webinaire'];
                            $currentCat = old('categorie', $evenement->categorie);
                        @endphp
                        @foreach($catList as $cat)
                            <option value="{{ $cat }}" {{ $currentCat == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                        <option value="Autre" {{ !in_array($currentCat, $catList) && $currentCat ? 'selected' : '' }}>Autre</option>
                    </select>
                    <div id="autre-categorie-wrapper" style="display:none;margin-top:0.5rem;">
                        <input type="text" class="form-control" id="autre_categorie" name="autre_categorie" placeholder="Precisez la categorie" value="{{ old('autre_categorie', !in_array($currentCat, $catList) ? $currentCat : '') }}">
                    </div>
                    @error('categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('autre_categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="capacite" class="form-label fw-semibold">Capacité <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('capacite') is-invalid @enderror" id="capacite" name="capacite" value="{{ old('capacite', $evenement->capacite) }}" min="1" required>
                        @error('capacite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="date_fin_vente" class="form-label fw-semibold">Date fin de vente</label>
                        <input type="datetime-local" class="form-control @error('date_fin_vente') is-invalid @enderror" id="date_fin_vente" name="date_fin_vente" value="{{ old('date_fin_vente', $evenement->date_fin_vente?->format('Y-m-d\TH:i')) }}">
                        @error('date_fin_vente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Image d'illustration</label>
                    @if($evenement->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $evenement->image) }}" alt="Image actuelle" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="gratuit" name="gratuit" value="1" {{ old('gratuit', $evenement->gratuit ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="gratuit">
                        <strong>Evenement gratuit</strong>
                        <small class="text-muted d-block">Les billets sont gratuits pour tous les participants</small>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Statut <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_brouillon" name="statut" value="brouillon" {{ old('statut', $evenement->statut) == 'brouillon' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_brouillon">
                                <i class="bi bi-pencil-square me-1"></i>Brouillon
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_publie" name="statut" value="publié" {{ old('statut', $evenement->statut) == 'publié' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_publie">
                                <i class="bi bi-globe2 me-1"></i>Publié
                            </label>
                        </div>
                    </div>
                    @error('statut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="panel-card mt-4 mb-4" style="border-left: 3px solid var(--violet);">
                    <div class="panel-card-body p-3">
                        <h6 class="fw-bold mb-3" style="color: var(--violet);">
                            <i class="bi bi-cash-coin me-2"></i>Tarifs
                        </h6>

                        @if($evenement->tarifs->isEmpty())
                            <p class="text-muted">Aucun tarif défini pour cet événement.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 0.75rem;">Catégorie</th>
                                            <th style="font-size: 0.75rem;">Type</th>
                                            <th style="font-size: 0.75rem;">Prix</th>
                                            <th style="font-size: 0.75rem;">Dispo.</th>
                                            <th style="font-size: 0.75rem;">Vendu</th>
                                            <th style="font-size: 0.75rem;">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($evenement->tarifs as $tarif)
                                            <tr>
                                                <td style="font-size: 0.82rem;">
                                                    {{ ucfirst($tarif->categorie) }}
                                                    @if($tarif->categorie === 'etudiant')
                                                        <span class="badge" style="background: rgba(135,66,139,0.12); color: var(--violet); font-size: 0.6rem;">Étudiant</span>
                                                    @endif
                                                </td>
                                                <td style="font-size: 0.82rem;">{{ $tarif->type === 'normal' ? 'Standard' : 'VIP' }}</td>
                                                <td style="font-size: 0.82rem;" class="fw-bold">{{ number_format($tarif->prix, 0, ',', ' ') }} F</td>
                                                <td style="font-size: 0.82rem;">{{ $tarif->quantite_disponible }}</td>
                                                <td style="font-size: 0.82rem;">{{ $tarif->quantite_vendue }}</td>
                                                <td style="font-size: 0.82rem;">
                                                    @if($tarif->statut === 'actif')
                                                        <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert);">Actif</span>
                                                    @else
                                                        <span class="badge" style="background: rgba(152,145,155,0.15); color: var(--gris);">Inactif</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted mt-3 mb-0" style="font-size: 0.78rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                Pour modifier les tarifs, accédez à la section "Tarifs" depuis la page de l'événement.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
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
document.getElementById('categorie')?.addEventListener('change', function() {
    const wrapper = document.getElementById('autre-categorie-wrapper');
    if (this.value === 'Autre') {
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
    }
});
if (document.getElementById('categorie')?.value === 'Autre') {
    document.getElementById('autre-categorie-wrapper').style.display = 'block';
}
</script>
@endsection
