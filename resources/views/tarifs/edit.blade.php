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
                    <input type="number" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix', $tarif->prix) }}" step="0.01" min="0" {{ $aVendu ? 'disabled readonly' : '' }}>
                    @error('prix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($aVendu)
                        <small class="text-muted d-block mt-1">Des billets ont déjà été vendus : le prix ne peut pas être modifié directement. Utilisez le bouton « Demander une modification » ci-dessous, qui sera validé par PaxEvent.</small>
                    @else
                        <small class="text-muted">Aucun billet vendu : vous pouvez modifier librement le prix.</small>
                    @endif
                </div>

                @if($aVendu)
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary-custom" onclick="document.getElementById('demandeModificationModal').style.display='flex'">
                            <i class="bi bi-send me-1"></i> Demander une modification
                        </button>
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

            @if($aVendu)
            <div id="demandeModificationModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
                <div class="modal-box">
                    <div class="modal-header">
                        <h5><i class="bi bi-send me-2" style="color:var(--violet);"></i>Demander une modification de prix</h5>
                        <button class="modal-close" onclick="document.getElementById('demandeModificationModal').style.display='none'">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:0.85rem;color:#6c757d;margin-bottom:1rem;">
                            Le tarif « {{ $tarif->nom }} » a des billets vendus (prix actuel : <strong>{{ number_format($tarif->prix, 0, ',', ' ') }} F</strong>).
                            Indiquez le nouveau prix souhaité : PaxEvent devra le valider.
                        </p>
                        <form action="{{ route('admin.tarifs.demande-modification', [$evenement->id, $tarif->id]) }}" method="POST" id="demandeForm">
                            @csrf
                            <div class="mb-3">
                                <label for="nouveau_prix" class="form-label fw-semibold">Nouveau prix (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('nouveau_prix') is-invalid @enderror" id="nouveau_prix" name="nouveau_prix" value="{{ old('nouveau_prix') }}" step="0.01" min="0" required>
                                @error('nouveau_prix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-custom" onclick="document.getElementById('demandeModificationModal').style.display='none'">Annuler</button>
                        <button type="submit" form="demandeForm" class="btn btn-primary-custom">
                            <i class="bi bi-send me-1"></i> Envoyer la demande
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-box {
    background: #fff;
    border-radius: 14px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: modalIn 0.2s ease;
}
@keyframes modalIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #eee;
}
.modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.modal-close {
    background: none; border: none;
    font-size: 1.5rem; cursor: pointer;
    color: #999; line-height: 1;
}
.modal-close:hover { color: #333; }
.modal-body { padding: 1.25rem; }
.modal-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
</style>
@endsection
