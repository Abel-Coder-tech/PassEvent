@extends('superadmin.layouts.master')

@section('title', 'Organisateurs - Super Admin')
@section('page-title', 'Organisateurs')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-person-plus-fill me-2" style="color: var(--sa-success);"></i>Creer un organisateur</span>
            </div>
            <div class="sa-card-body">
                <form action="{{ route('superadmin.organisateurs.creer') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6"><input type="text" name="nom" class="sa-form-control" placeholder="Nom complet" required></div>
                        <div class="col-6"><input type="email" name="email" class="sa-form-control" placeholder="Email" required></div>
                        <div class="col-6 position-relative">
    <input type="password" name="mot_de_passe" id="org_password" class="sa-form-control" placeholder="Mot de passe" required oninput="checkPasswordStrengthOrg(this.value)">
    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 24px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
    </button>
    <div id="passwordMeterOrg" class="mt-1" style="display:none;">
        <div style="height:3px;background:#e9ecef;border-radius:2px;overflow:hidden;">
            <div id="passwordBarOrg" style="height:100%;width:0;border-radius:2px;transition:width .3s,background .3s;"></div>
        </div>
        <div id="passwordLabelOrg" class="mt-1" style="font-size:0.75rem;font-weight:600;"></div>
    </div>
</div>
                        <div class="col-6 col-md-3"><input type="text" name="telephone" class="sa-form-control" placeholder="Telephone"></div>
                        <div class="col-6 col-md-3"><input type="text" name="organisation" class="sa-form-control" placeholder="Organisation"></div>
                        <div class="col-6 col-md-4">
                            <select name="type" class="sa-form-control">
                                <option value="">Type</option>
                                <option value="universitaire">Universitaire</option>
                                <option value="professionnel">Professionnel</option>
                            </select>
                        </div>
                        <div class="col-8"><textarea name="description" class="sa-form-control" placeholder="Description de l'organisation" rows="2" style="resize: vertical;"></textarea></div>
                        <div class="col-12 mt-2"><button type="submit" class="sa-btn sa-btn-primary"><i class="bi bi-plus-lg"></i> Créer</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Validation</span>
            </div>
            <div class="sa-card-body">
                <p class="text-muted mb-0" style="font-size:0.8rem;">Les organisateurs en attente apparaissent avec un badge jaune. Utilisez les boutons ✅ ou ❌ pour les approuver ou les rejeter.</p>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
            <span class="d-flex align-items-center gap-2"><i class="bi bi-person-badge-fill" style="color: var(--sa-primary);"></i>Organisateurs</span>
            <div class="d-flex align-items-center gap-2">
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
            </div>
        </div>
    </div>
    <div id="organisateurs-list">
        @include('superadmin.partials.organisateurs-table', ['organisateurs' => $organisateurs])
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

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
});

function checkPasswordStrengthOrg(pwd) {
    var meter = document.getElementById('passwordMeterOrg');
    var bar = document.getElementById('passwordBarOrg');
    var label = document.getElementById('passwordLabelOrg');
    if (!pwd) { meter.style.display = 'none'; return; }
    meter.style.display = 'block';
    var score = 0;
    if (pwd.length >= 8) score += 25;
    if (pwd.length >= 12) score += 15;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 20;
    if (/\d/.test(pwd)) score += 20;
    if (/[^a-zA-Z0-9]/.test(pwd)) score += 20;
    if (pwd.length > 15) score += 10;
    score = Math.min(100, score);
    var color, text, icon;
    if (score < 40) { color = '#dc3545'; text = 'Faible'; icon = 'bi-exclamation-triangle-fill'; }
    else if (score < 70) { color = '#f59e0b'; text = 'Moyen'; icon = 'bi-shield-exclamation'; }
    else { color = '#10b981'; text = 'Fort'; icon = 'bi-shield-fill-check'; }
    bar.style.width = score + '%';
    bar.style.background = color;
    label.innerHTML = '<i class="bi ' + icon + '" style="color:' + color + '"></i> <span style="color:' + color + '">' + text + '</span>';
}
</script>
@endpush
@endsection
