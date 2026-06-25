@extends('layouts.app')

@section('title', 'Créer un événement')

@section('page-title', 'Créer un événement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">Créer</li>
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
            <form action="{{ route('admin.evenements.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="titre" class="form-label fw-semibold">Titre de l'événement <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre') }}" required>
                    @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="date_event" class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('date_event') is-invalid @enderror" id="date_event" name="date_event" value="{{ old('date_event') }}" required>
                        @error('date_event') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="lieu" class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('lieu') is-invalid @enderror" id="lieu" name="lieu" value="{{ old('lieu') }}" required>
                        @error('lieu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="categorie" class="form-label fw-semibold">Categorie <span class="text-danger">*</span></label>
                    <select class="form-select @error('categorie') is-invalid @enderror" id="categorie" name="categorie" required>
                        <option value="">Selectionner une categorie</option>
                        <option value="Sport" {{ old('categorie') == 'Sport' ? 'selected' : '' }}>Sport</option>
                        <option value="Soiree gala" {{ old('categorie') == 'Soiree gala' ? 'selected' : '' }}>Soiree gala</option>
                        <option value="Ceremonie officielle" {{ old('categorie') == 'Ceremonie officielle' ? 'selected' : '' }}>Ceremonie officielle</option>
                        <option value="Webinaire" {{ old('categorie') == 'Webinaire' ? 'selected' : '' }}>Webinaire</option>
                        <option value="Autre" {{ old('categorie') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    <div id="autre-categorie-wrapper" style="display:none;margin-top:0.5rem;">
                        <input type="text" class="form-control" id="autre_categorie" name="autre_categorie" placeholder="Precisez la categorie" value="{{ old('autre_categorie') }}">
                    </div>
                    @error('categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('autre_categorie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="capacite" class="form-label fw-semibold">Capacité <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('capacite') is-invalid @enderror" id="capacite" name="capacite" value="{{ old('capacite') }}" min="1" required>
                        @error('capacite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="date_fin_vente" class="form-label fw-semibold">Date fin de vente</label>
                        <input type="datetime-local" class="form-control @error('date_fin_vente') is-invalid @enderror" id="date_fin_vente" name="date_fin_vente" value="{{ old('date_fin_vente') }}">
                        @error('date_fin_vente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Image d'illustration</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Statut <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_brouillon" name="statut" value="brouillon" {{ old('statut', 'brouillon') == 'brouillon' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_brouillon">
                                <i class="bi bi-pencil-square me-1"></i>Brouillon
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_publie" name="statut" value="publié" {{ old('statut') == 'publié' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_publie">
                                <i class="bi bi-globe2 me-1"></i>Publié
                            </label>
                        </div>
                    </div>
                    <small class="text-muted">Le statut pourra etre modifie plus tard.</small>
                    @error('statut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="panel-card mt-4 mb-4" style="border-left: 3px solid var(--violet);">
                    <div class="panel-card-body p-3">
                        <h6 class="fw-bold mb-3" style="color: var(--violet);">
                            <i class="bi bi-cash-coin me-2"></i>Tarification
                        </h6>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="gratuit" name="gratuit" value="1" {{ old('gratuit') ? 'checked' : '' }}>
                            <label class="form-check-label" for="gratuit">
                                <strong>Evenement gratuit</strong>
                                <small class="text-muted d-block">Les billets seront gratuits pour tous les participants (aucun paiement requis)</small>
                            </label>
                        </div>

                        <div id="pricing-fields">
                            <div class="mb-3">
                                <label for="prix_base" class="form-label fw-semibold">Prix de base (Externe Simple) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('prix_base') is-invalid @enderror" id="prix_base" name="prix_base" value="{{ old('prix_base') }}" min="0" step="100" placeholder="Ex: 10000" required>
                                <small class="text-muted">Prix du billet externe simple</small>
                                @error('prix_base') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="multiplicateur_vip" class="form-label fw-semibold">Multiplicateur VIP</label>
                                    <select class="form-select" id="multiplicateur_vip" name="multiplicateur_vip">
                                        <option value="1.5" {{ old('multiplicateur_vip') == '1.5' ? 'selected' : '' }}>x1.5 (50% plus cher)</option>
                                        <option value="2" {{ old('multiplicateur_vip') == '2' ? 'selected' : '' }}>x2 (2x plus cher)</option>
                                    </select>
                                    <small class="text-muted">Les billets VIP seront calculés automatiquement</small>
                                </div>
                                @if(auth()->user()->type === 'universitaire')
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="reduction_etudiant" class="form-label fw-semibold">Réduction étudiant (%)</label>
                                    <input type="number" class="form-control" id="reduction_etudiant" name="reduction_etudiant" value="{{ old('reduction_etudiant', 30) }}" min="0" max="100">
                                    <small class="text-muted">Les étudiants bénéficient d'une réduction sur tous les tarifs</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="alert" style="background: rgba(135,66,139,0.08); border: 1px solid rgba(135,66,139,0.2); border-radius: 8px; padding: 0.75rem 1rem;">
                            <small class="text-muted">
                                <strong>Aperçu des tarifs :</strong><br>
                                <span id="preview-tarifs">Configurez les paramètres pour voir l'aperçu</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Créer l'événement
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

function updatePreview() {
    const gratuit = document.getElementById('gratuit')?.checked;
    if (gratuit) {
        document.getElementById('preview-tarifs').innerHTML =
            '<strong>Étudiant Simple:</strong> Gratuit<br>' +
            '<strong>Étudiant VIP:</strong> Gratuit<br>' +
            '<strong>Externe Simple:</strong> Gratuit<br>' +
            '<strong>Externe VIP:</strong> Gratuit';
        return;
    }

    const isUniv = {{ auth()->user()->type === 'universitaire' ? 'true' : 'false' }};
    const base = parseFloat(document.getElementById('prix_base')?.value || 0);
    const mult = parseFloat(document.getElementById('multiplicateur_vip')?.value || 1.5);

    if (base <= 0) {
        document.getElementById('preview-tarifs').textContent = 'Entrez un prix de base pour voir l\'aperçu';
        return;
    }

    const extSimple = base;
    const extVip = base * mult;

    if (isUniv) {
        const reducInput = document.getElementById('reduction_etudiant');
        const reduc = parseFloat(reducInput?.value || 0) / 100;
        const etuSimple = base * (1 - reduc);
        const etuVip = base * mult * (1 - reduc);
        document.getElementById('preview-tarifs').innerHTML =
            '<strong>Étudiant Simple:</strong> ' + formatPrice(etuSimple) + '<br>' +
            '<strong>Étudiant VIP:</strong> ' + formatPrice(etuVip) + '<br>' +
            '<strong>Externe Simple:</strong> ' + formatPrice(extSimple) + '<br>' +
            '<strong>Externe VIP:</strong> ' + formatPrice(extVip);
    } else {
        document.getElementById('preview-tarifs').innerHTML =
            '<strong>Simple:</strong> ' + formatPrice(extSimple) + '<br>' +
            '<strong>VIP:</strong> ' + formatPrice(extVip);
    }
}

function formatPrice(price) {
    return Math.round(price).toLocaleString('fr-FR') + ' F';
}

function toggleGratuit() {
    const checked = document.getElementById('gratuit')?.checked;
    const fields = document.getElementById('pricing-fields');
    fields.style.display = checked ? 'none' : 'block';
    updatePreview();
}

document.getElementById('gratuit')?.addEventListener('change', toggleGratuit);
document.getElementById('prix_base')?.addEventListener('input', updatePreview);
document.getElementById('multiplicateur_vip')?.addEventListener('change', updatePreview);
document.getElementById('reduction_etudiant')?.addEventListener('input', updatePreview);
toggleGratuit();
</script>
@endsection
