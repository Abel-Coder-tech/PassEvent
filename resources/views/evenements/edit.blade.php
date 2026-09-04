@extends('layouts.app')

@section('title', 'Modifier l\'événement')

@section('page-title', 'Modifier l\'événement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
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
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-semibold mb-0">Jours supplémentaires (événement multi-jours)</label>
                        <small class="text-muted">Optionnel</small>
                    </div>
                    <small class="text-muted d-block mb-2">
                        Si votre événement dure plusieurs jours, ajoutez la date et l'heure de début de chaque jour. Le QR code restera valable pour tous les jours (1 scan par jour).
                    </small>
                    <div id="dates-supplementaires-container">
                        @php
                            $datesExistantes = $evenement->dates()->where('date_debut', '!=', $evenement->date_event)->get();
                        @endphp
                        @foreach(old('dates_supplementaires', $datesExistantes->pluck('date_debut')->map(fn($d) => $d->format('Y-m-d\TH:i'))->all()) as $i => $dateSupp)
                            <div class="date-supp-row mb-2 d-flex gap-2 align-items-center" id="date-supp-row-{{ $i + 1 }}">
                                <input type="datetime-local" class="form-control" name="dates_supplementaires[]" value="{{ $dateSupp }}">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDateSupp(this)" style="flex-shrink:0;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addDateSuppBtn" onclick="addDateSupp()">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter un jour
                    </button>
                </div>

                <div class="mb-3">
                    <label for="type_evenement" class="form-label fw-semibold">Type d'événement <span class="text-danger">*</span></label>
                    <select class="form-select @error('type_evenement') is-invalid @enderror" id="type_evenement" name="type_evenement" required>
                        @php $typeActuel = old('type_evenement', $evenement->type_evenement ?? 'spectacle'); @endphp
                        <option value="spectacle" {{ $typeActuel == 'spectacle' ? 'selected' : '' }}>Spectacle / Soirée</option>
                        <option value="formation" {{ $typeActuel == 'formation' ? 'selected' : '' }}>Formation</option>
                        <option value="conference" {{ $typeActuel == 'conference' ? 'selected' : '' }}>Conférence</option>
                    </select>
                    <div class="form-text">Les textes d'achat s'adaptent au type (acheter un billet / s'inscrire).</div>
                    @error('type_evenement') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                <div class="panel-card mt-4 mb-4" style="border-left: 3px solid var(--violet);">
                    <div class="panel-card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0" style="color: var(--violet);">
                                <i class="bi bi-cash-coin me-2"></i>Tarifs
                            </h6>
                            <a href="{{ route('admin.tarifs.index', $evenement->id) }}" class="btn btn-sm btn-primary-custom">
                                <i class="bi bi-gear me-1"></i> Gérer les tarifs
                            </a>
                        </div>

                        @if($evenement->tarifs->isEmpty())
                            <p class="text-muted">Aucun tarif défini pour cet événement.
                                <a href="{{ route('admin.tarifs.create', $evenement->id) }}">Ajouter un tarif</a>.
                            </p>
                        @else
                            @php $evenementAVendu = $evenement->tarifs()->sum('quantite_vendue') > 0; @endphp
                            @foreach($evenement->tarifs as $tarif)
                                @php
                                    $tarifVendu = $tarif->quantite_vendue > 0;
                                    $dejaDemande = $tarifVendu && $tarif->demandesModification()->where('statut', 'en_attente')->exists();
                                @endphp
                                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:#f0f0f0;">
                                    <div style="flex:1;min-width:0;">
                                        <div class="fw-semibold" style="font-size:0.85rem;">{{ $tarif->nom }}</div>
                                        <div style="font-size:0.72rem;color:var(--gris);">
                                            {{ $tarif->quantite_disponible ?? 'Illimité' }} dispo. · {{ $tarif->quantite_vendue }} vendu(s)
                                            · <span class="{{ $tarif->statut === 'actif' ? 'text-success' : 'text-muted' }}">{{ ucfirst($tarif->statut) }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2" style="flex-shrink:0;">
                                        <div style="position:relative;">
                                            <input type="number" step="0.01" min="0"
                                                   name="prix_tarifs[{{ $tarif->id }}]"
                                                   id="prix-tarif-{{ $tarif->id }}"
                                                   value="{{ old('prix_tarifs.'.$tarif->id, $tarif->prix) }}"
                                                   class="form-control form-control-sm"
                                                   style="width:130px;text-align:right;"
                                                   {{ $tarifVendu ? 'data-demande="'.$tarif->id.'" oninput="syncDemande(this)"' : '' }}
                                                   @error('prix_tarifs.'.$tarif->id) is-invalid @enderror>
                                            <span style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);font-size:0.7rem;color:var(--gris);pointer-events:none;">F</span>
                                        </div>

                                        @if($tarifVendu)
                                            @if($dejaDemande)
                                                <span class="badge" style="background:rgba(224,168,0,0.15);color:#8b6914;font-size:0.7rem;white-space:nowrap;">
                                                    <i class="bi bi-hourglass-split me-1"></i>Demande en attente
                                                </span>
                                            @else
                                                <button type="submit" form="demande-form-{{ $tarif->id }}" class="btn btn-sm" style="background:var(--violet);color:#fff;border-radius:8px;font-weight:600;white-space:nowrap;">
                                                    <i class="bi bi-send me-1"></i> Envoyer pour approbation
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <p class="text-muted mt-3 mb-0" style="font-size:0.78rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                @if($evenementAVendu)
                                    Un tarif ayant déjà des ventes ne se modifie plus directement : saisissez le nouveau prix puis cliquez sur « Envoyer pour approbation » ; PaxEvent devra le valider. Les autres tarifs se modifient directement avec « Enregistrer » ci-dessous.
                                @else
                                    Aucun billet vendu pour l'instant : vous pouvez modifier librement le prix des tarifs (sauvegardé via « Enregistrer »).
                                @endif
                            </p>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Statut <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_publie" name="statut" value="publié" {{ old('statut', $evenement->statut) == 'publié' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_publie">
                                <i class="bi bi-globe2 me-1"></i>Publié
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input @error('statut') is-invalid @enderror" id="statut_brouillon" name="statut" value="brouillon" {{ old('statut', $evenement->statut) == 'brouillon' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statut_brouillon">
                                <i class="bi bi-pencil-square me-1"></i>Brouillon
                            </label>
                        </div>
                    </div>
                    @error('statut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom w-100 w-md-auto">Annuler</a>
                    <button type="submit" class="btn btn-primary-custom w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                </div>
            </form>

            {{-- Formulaires de demande d'approbation (tarifs avec ventes), hors formulaire principal --}}
            @foreach($evenement->tarifs as $tarif)
                @php $tarifVendu = $tarif->quantite_vendue > 0; @endphp
                @if($tarifVendu)
                    <form id="demande-form-{{ $tarif->id }}" action="{{ route('admin.tarifs.demande-modification', [$evenement->id, $tarif->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="nouveau_prix" id="demande-prix-{{ $tarif->id }}" value="{{ $tarif->prix }}">
                    </form>
                @endif
            @endforeach
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

let dateSuppCount = document.querySelectorAll('#dates-supplementaires-container .date-supp-row').length || 0;
const maxDatesSupp = 6;

function addDateSupp() {
    if (dateSuppCount >= maxDatesSupp) return;
    dateSuppCount++;
    const html = `
        <div class="date-supp-row mb-2 d-flex gap-2 align-items-center" id="date-supp-row-${dateSuppCount}">
            <input type="datetime-local" class="form-control" name="dates_supplementaires[]">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDateSupp(this)" style="flex-shrink:0;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `;
    document.getElementById('dates-supplementaires-container').insertAdjacentHTML('beforeend', html);
    if (dateSuppCount >= maxDatesSupp) {
        document.getElementById('addDateSuppBtn').style.display = 'none';
    }
}

function removeDateSupp(btn) {
    const row = btn.closest('.date-supp-row');
    if (row) row.remove();
    dateSuppCount--;
    document.getElementById('addDateSuppBtn').style.display = dateSuppCount >= maxDatesSupp ? 'none' : 'inline-flex';
}

function syncDemande(input) {
    const tarifId = input.getAttribute('data-demande');
    const hidden = document.getElementById('demande-prix-' + tarifId);
    if (hidden) hidden.value = input.value;
}
</script>
@endsection
