@extends('layouts.app')

@section('title', 'Créer un événement')

@section('page-title', 'Créer un événement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
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
                        <input type="datetime-local" class="form-control @error('date_event') is-invalid @enderror" id="date_event" name="date_event" value="{{ old('date_event') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required>
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

                </div>

                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Image d'illustration</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <div id="tarifs-container">
                                <div class="tarif-row mb-3 p-3 rounded" style="background: #f8f6f9; border: 1px solid #ede5f0;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Nom du tarif <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="tarif_nom_1" value="{{ old('tarif_nom_1') }}" placeholder="Ex: Enfant" required>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Prix (FCFA) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="tarif_prix_1" value="{{ old('tarif_prix_1') }}" min="0" step="100" placeholder="Ex: 1000" required>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label fw-semibold" style="font-size:0.8rem;">Places max</label>
                                            <input type="number" class="form-control" name="tarif_qte_1" value="{{ old('tarif_qte_1') }}" min="1" placeholder="Illimité">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <small class="text-muted">Obligatoire</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="addTarifBtn" onclick="addTarif()">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter un tarif
                            </button>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="generer_vip" name="generer_vip" value="1" {{ old('generer_vip') ? 'checked' : '' }}>
                                <label class="form-check-label" for="generer_vip">
                                    <strong>Générer automatiquement le tarif VIP</strong>
                                    <small class="text-muted d-block">Crée un 2ème tarif à 2× le prix du premier tarif saisi</small>
                                </label>
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

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Statut <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_publie" name="statut" value="publié" {{ old('statut', 'publié') == 'publié' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_publie">
                                <i class="bi bi-globe2 me-1"></i>Publié
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_brouillon" name="statut" value="brouillon" {{ old('statut') == 'brouillon' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_brouillon">
                                <i class="bi bi-pencil-square me-1"></i>Brouillon
                            </label>
                        </div>
                    </div>
                    <small class="text-muted">Le statut pourra être modifié plus tard.</small>
                    @error('statut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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

let tarifCount = 1;
const maxTarifs = 4;

function addTarif() {
    if (tarifCount >= maxTarifs) return;
    tarifCount++;
    const num = tarifCount;
    const html = `
        <div class="tarif-row mb-3 p-3 rounded" style="background: #f8f6f9; border: 1px solid #ede5f0;" id="tarif-row-${num}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-bold text-muted">Tarif ${num}</small>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarif(${num})" style="font-size:0.7rem; padding:0.15rem 0.5rem;">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Nom du tarif</label>
                    <input type="text" class="form-control" name="tarif_nom_${num}" placeholder="Ex: VIP">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Prix (FCFA)</label>
                    <input type="number" class="form-control" name="tarif_prix_${num}" min="0" step="100" placeholder="Ex: 5000">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold" style="font-size:0.8rem;">Places max</label>
                    <input type="number" class="form-control" name="tarif_qte_${num}" min="1" placeholder="Illimité">
                </div>
                <div class="col-12 col-md-2"></div>
            </div>
        </div>
    `;
    document.getElementById('tarifs-container').insertAdjacentHTML('beforeend', html);
    if (tarifCount >= maxTarifs) {
        document.getElementById('addTarifBtn').style.display = 'none';
    }
    updatePreview();
}

function removeTarif(num) {
    const row = document.getElementById('tarif-row-' + num);
    if (row) row.remove();
    tarifCount--;
    document.getElementById('addTarifBtn').style.display = tarifCount >= maxTarifs ? 'none' : 'inline-flex';
    updatePreview();
}

function updatePreview() {
    const gratuit = document.getElementById('gratuit')?.checked;
    if (gratuit) {
        document.getElementById('preview-tarifs').innerHTML = '<strong>Gratuit :</strong> 0 F';
        return;
    }

    let lines = [];
    for (let i = 1; i <= 4; i++) {
        const nomInput = document.querySelector(`[name="tarif_nom_${i}"]`);
        const prixInput = document.querySelector(`[name="tarif_prix_${i}"]`);
        if (nomInput && prixInput && nomInput.value.trim() && prixInput.value) {
            const nom = nomInput.value.trim();
            const prix = Math.round(parseFloat(prixInput.value));
            lines.push(`<strong>${nom}:</strong> ${formatPrice(prix)}`);
        }
    }

    if (document.getElementById('generer_vip')?.checked && lines.length > 0) {
        const firstPrix = parseFloat(document.querySelector('[name="tarif_prix_1"]')?.value || 0);
        if (firstPrix > 0) {
            lines.push(`<strong>VIP (auto):</strong> ${formatPrice(firstPrix * 2)}`);
        }
    }

    document.getElementById('preview-tarifs').innerHTML = lines.length > 0 ? lines.join('<br>') : 'Ajoutez un tarif pour voir l\'aperçu';
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
document.getElementById('generer_vip')?.addEventListener('change', updatePreview);
document.querySelectorAll('[name^="tarif_prix_"]').forEach(el => el.addEventListener('input', updatePreview));
document.querySelectorAll('[name^="tarif_nom_"]').forEach(el => el.addEventListener('input', updatePreview));
toggleGratuit();
</script>
@endsection
