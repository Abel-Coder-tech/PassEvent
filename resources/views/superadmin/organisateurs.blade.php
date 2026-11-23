@extends('superadmin.layouts.master')

@section('title', 'Organisateurs - Super Admin')
@section('page-title', 'Organisateurs')

@section('content')
<div class="sa-card mb-3">
    <div class="sa-card-header">
        <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Validation</span>
    </div>
    <div class="sa-card-body">
        <p class="text-muted mb-0" style="font-size:0.8rem;">Les organisateurs en attente apparaissent avec un badge jaune. Utilisez les boutons ✅ ou ❌ pour les approuver ou les rejeter.</p>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
            <span class="d-flex align-items-center gap-2"><i class="bi bi-person-badge-fill" style="color: var(--sa-primary);"></i>Organisateurs</span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjoutOrganisateur"
                        style="background: var(--sa-primary); color: white; border-radius: 8px; font-size: 0.8rem;">
                    <i class="bi bi-person-plus-fill me-1"></i> Ajouter un organisateur
                </button>
                <div class="position-relative" style="width: 280px;">
                    <i class="bi bi-search position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                    <input type="text" id="organisateur-search" class="sa-form-control py-0 ps-4" style="height: 34px;" placeholder="Rechercher par nom ou prénom" value="{{ request('q') }}" autocomplete="off">
                </div>
                <select id="organisateur-statut" class="sa-form-control py-0" style="height: 34px; width: 200px;">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="corrections_apportees" {{ request('statut') === 'corrections_apportees' ? 'selected' : '' }}>Corrections apportées</option>
                    <option value="corrections_demandees" {{ request('statut') === 'corrections_demandees' ? 'selected' : '' }}>Corrections demandées</option>
                    <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="incomplet" {{ request('statut') === 'incomplet' ? 'selected' : '' }}>Incomplet</option>
                    <option value="bloque" {{ request('statut') === 'bloque' ? 'selected' : '' }}>Bloqué</option>
                    <option value="rejete" {{ request('statut') === 'rejete' ? 'selected' : '' }}>Rejeté</option>
                </select>
                <span class="text-muted text-nowrap" style="font-size:0.8rem;"><span id="organisateurs-count">{{ $organisateurs->total() }}</span> total</span>
                @php($nbCorbeille = \App\Models\User::onlyTrashed()->where('role', 'admin')->count())
                <a href="{{ route('superadmin.organisateurs.corbeille') }}" class="btn btn-sm text-nowrap" style="background: var(--sa-danger); color: #fff; border-radius: 8px; font-size: 0.8rem;" title="Organisateurs supprimés, restaurables">
                    <i class="bi bi-trash3-fill me-1"></i> Corbeille
                    @if($nbCorbeille)<span class="badge rounded-pill ms-1" style="background:#fff;color:var(--sa-danger);">{{ $nbCorbeille }}</span>@endif
                </a>
            </div>
        </div>
    </div>
    <div id="organisateurs-list">
        @include('superadmin.partials.organisateurs-table', ['organisateurs' => $organisateurs])
    </div>
</div>

{{-- Modal : créer le compte d'un organisateur (PaxEvent saisit les informations, puis « Gérer » complète le profil) --}}
<div class="modal fade" id="modalAjoutOrganisateur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header" style="background: var(--sa-primary); color: #fff; border-radius: 14px 14px 0 0;">
                <h5 class="modal-title" style="font-size: 1rem;"><i class="bi bi-person-plus-fill me-2"></i>Ajouter un organisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.organisateurs.creer') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="text-muted mb-3" style="font-size: 0.78rem;">Le compte est créé « incomplet ». Depuis sa fiche, « Gérer l'organisateur » permet ensuite de saisir son profil, ses documents et sa signature, puis de générer le contrat.</p>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control form-control-sm" value="{{ old('nom') }}" required>
                            @error('nom')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Adresse email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" required>
                            @error('email')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 0.8rem;">Téléphone</label>
                            <input type="text" name="telephone" class="form-control form-control-sm" value="{{ old('telephone') }}" placeholder="+229 XX XX XX XX">
                            @error('telephone')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                <span>Mot de passe <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-link p-0" id="btnGenererMdpOrganisateur" style="font-size: 0.75rem; color: var(--sa-primary);">Générer</button>
                            </label>
                            <input type="text" name="mot_de_passe" id="champMdpOrganisateur" class="form-control form-control-sm font-monospace"
                                   value="{{ old('mot_de_passe') }}" autocomplete="new-password" required minlength="8">
                            <small class="text-muted" style="font-size: 0.7rem;">À transmettre à l'organisateur, qui pourra le changer depuis ses paramètres.</small>
                            @error('mot_de_passe')<div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background: var(--sa-primary); color: #fff;">
                        <i class="bi bi-check-lg me-1"></i>Créer le compte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.sa-badge {
    font-size: 0.68rem;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
}
.sa-table td [class*="sa-btn"] {
    padding: 0.25rem 0.45rem;
    font-size: 0.7rem;
    line-height: 1;
    white-space: nowrap;
}
.sa-badge-success { background: rgba(46,125,79,0.12); color: #2e7d4f; }
.sa-badge-warning { background: rgba(237,173,8,0.12); color: #b8860b; }
.sa-badge-danger { background: rgba(231,76,60,0.12); color: #e74c3c; }
.sa-badge-secondary { background: rgba(152,145,155,0.15); color: #6c757d; }
.sa-badge-info { background: rgba(13,110,253,0.12); color: #0a58ca; }
.sa-btn-success {
    background: #2e7d4f; border: none; color: #fff; padding: 0.25rem 0.45rem;
    border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s; line-height: 1;
}
.sa-btn-success:hover { opacity: 0.85; }
.sa-btn-warning {
    background: #e0a800; border: none; color: #fff; padding: 0.25rem 0.45rem;
    border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s; line-height: 1;
}
.sa-btn-warning:hover { opacity: 0.85; }
.sa-btn-info {
    background: #3b82f6; border: none; color: #fff; padding: 0.25rem 0.45rem;
    border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s; line-height: 1;
}
.sa-btn-info:hover { opacity: 0.85; }
.sa-btn-secondary {
    background: #6c757d; border: none; color: #fff; padding: 0.4rem 1rem;
    border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-secondary:hover { opacity: 0.85; }

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
}
.org-detail-row {
    display: flex;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.85rem;
}
.org-detail-row:last-child { border-bottom: none; }
.org-detail-label {
    font-weight: 600;
    color: #666;
    min-width: 120px;
    flex-shrink: 0;
}
.org-detail-value { color: #1a1a1a; }
</style>
@push('scripts')
<script>
let orgSearchTimeout;
const orgSearchInput = document.getElementById('organisateur-search');
const orgStatutSelect = document.getElementById('organisateur-statut');
const orgList = document.getElementById('organisateurs-list');
const orgCount = document.getElementById('organisateurs-count');

function fetchOrganisateurs() {
    const q = orgSearchInput ? orgSearchInput.value.trim() : '';
    const statut = orgStatutSelect ? orgStatutSelect.value : '';
    const url = new URL('{{ route('superadmin.organisateurs') }}', window.location.origin);
    if (q !== '') {
        url.searchParams.set('q', q);
    } else {
        url.searchParams.delete('q');
    }
    if (statut !== '') {
        url.searchParams.set('statut', statut);
    } else {
        url.searchParams.delete('statut');
    }
    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            orgList.innerHTML = data.html;
            if (orgCount) orgCount.textContent = data.count;
            window.history.replaceState({}, '', url.toString());
        });
}

if (orgSearchInput) {
    orgSearchInput.addEventListener('input', function() {
        clearTimeout(orgSearchTimeout);
        orgSearchTimeout = setTimeout(fetchOrganisateurs, 250);
    });
}

if (orgStatutSelect) {
    orgStatutSelect.addEventListener('change', fetchOrganisateurs);
}

document.addEventListener('DOMContentLoaded', function () {
    var generer = document.getElementById('btnGenererMdpOrganisateur');
    var champ = document.getElementById('champMdpOrganisateur');
    if (generer && champ) {
        generer.addEventListener('click', function () {
            var caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            var motDePasse = '';
            for (var i = 0; i < 12; i++) {
                motDePasse += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            champ.value = motDePasse;
        });
    }
@if($errors->any() && old('email') !== null)
    new bootstrap.Modal(document.getElementById('modalAjoutOrganisateur')).show();
@endif
});
</script>
@endpush
@endsection
