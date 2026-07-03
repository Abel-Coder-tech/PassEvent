@extends('layouts.app')

@section('title', 'Codes promos — ' . $evenement->titre)

@section('page-title', 'Codes promos étudiants')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.show', $evenement->id) }}">{{ $evenement->titre }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Codes promos</li>
@endsection

@section('page-subtitle', 'Liste des codes promos — ' . $evenement->titre . ' · ' . $totalGeneres . ' codes générés')

@section('topbar-actions')
<a href="{{ route('admin.evenements.show', $evenement->id) }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
@endsection

@section('content')
<div class="page-content">
    <!-- Stat Cards -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-label">Total générés</div>
                <div class="metric-value">{{ $totalGeneres }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-label">Utilisés</div>
                <div class="metric-value" style="color: var(--vert);">{{ $utilises }}</div>
                <div class="metric-subtitle">{{ $pctUtilises }}% du total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-label">Disponibles</div>
                <div class="metric-value" style="color: var(--teal);">{{ $disponibles }}</div>
                <div class="metric-subtitle">{{ $pctDisponibles }}% restant</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card" style="border-top-color: var(--menthe);">
                <div class="metric-label">Économie étudiants</div>
                <div class="metric-value" style="font-size: 1.3rem; color: var(--violet);">{{ number_format($economieEtudiants, 0, ',', ' ') }} F</div>
                <div class="metric-subtitle">Réductions appliquées</div>
            </div>
        </div>
    </div>

    <!-- Toolbar: Search -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div class="position-relative w-100" style="max-width: 320px;">
            <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--gris); font-size: 0.9rem;"></i>
            <input type="text" class="form-control ps-5 py-2 border rounded-3" id="searchInput" placeholder="Rechercher un code…" style="border-color: #e2e0e4; font-size: 0.88rem;">
        </div>
    </div>

    <!-- Codes Table -->
    <div class="panel-card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table custom-table table-hover mb-0" id="codesTable">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>UTILISÉ PAR</th>
                            <th>STATUT</th>
                            <th>DATE</th>
                            <th>TARIF</th>
                            <th class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codesPromos as $code)
                            <tr class="code-row" data-code="{{ strtolower($code->code) }}">
                                <td>
                                    <code class="fw-semibold" style="color: var(--violet); font-size: 0.95rem;">{{ $code->code }}</code>
                                </td>
                                <td>
                                    @if($code->nb_utilisations > 0)
                                        {{ $code->ticket?->nom_acheteur ?? '—' }}
                                    @else
                                        <span class="text-muted">Non utilisé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($code->nb_utilisations > 0)
                                        <span class="status-badge status-en-cours">Utilisé</span>
                                    @elseif(!$code->actif)
                                        <span class="status-badge status-termine">Désactivé</span>
                                    @else
                                        <span class="status-badge status-a-venir">Disponible</span>
                                    @endif
                                </td>
                                <td>
                                    @if($code->nb_utilisations > 0 && $code->ticket)
                                        {{ $code->ticket->date_achat->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">
                                    {{ $code->tarif ? number_format($code->tarif->prix, 0, ',', ' ') . ' F' : '—' }}
                                </td>
                                <td class="text-end">
                                    @if($code->nb_utilisations > 0 && $code->ticket)
                                        <a href="{{ route('tickets.show', $code->ticket->id) }}" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px;">
                                            <i class="bi bi-ticket-perforated me-1"></i>Ticket
                                        </a>
                                    @else
                                        <form action="{{ route('admin.codes-promos.destroy', [$evenement->id, $code->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="border-radius: 6px; border: 1px solid #e74c3c; color: #e74c3c; background: transparent;" onclick="return confirm('Désactiver ce code ?')">
                                                <i class="bi bi-x-circle me-1"></i>Désactiver
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-tag d-block mb-2" style="font-size: 2rem; color: var(--gris);"></i>
                                    <small>Aucun code promo généré</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($codesPromos->hasPages())
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size: 0.82rem;">
                            Affichage {{ $codesPromos->firstItem() }} à {{ $codesPromos->lastItem() }} sur {{ $codesPromos->total() }} codes
                        </span>
                        {{ $codesPromos->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Generate Codes Form -->
    <div class="panel-card mb-4">
        <div class="panel-card-header">
            <h5>Générer des codes</h5>
        </div>
        <div class="panel-card-body">
            <form action="{{ route('admin.codes-promos.store', $evenement->id) }}" method="POST" id="generateForm">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="evenement_select" class="form-label fw-semibold">Événement</label>
                        <select class="form-select" id="evenement_select" name="evenement_id" disabled>
                            @foreach($userEvenements as $evt)
                                <option value="{{ $evt->id }}" {{ $evt->id === $evenement->id ? 'selected' : '' }}>{{ $evt->titre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="tarif_id" class="form-label fw-semibold">Tarif lié <span class="text-danger">*</span></label>
                        <select class="form-select @error('tarif_id') is-invalid @enderror" id="tarif_id" name="tarif_id" required>
                            <option value="">Choisir un tarif —</option>
                            @foreach($tarifs as $tarif)
                                <option value="{{ $tarif->id }}" {{ old('tarif_id') == $tarif->id ? 'selected' : '' }}>{{ $tarif->getLabel() }} — {{ number_format($tarif->prix, 0, ',', ' ') }} F</option>
                            @endforeach
                        </select>
                        @error('tarif_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="prefixe" class="form-label fw-semibold">Préfixe personnalisé</label>
                        <input type="text" class="form-control" id="prefixe" name="prefixe" value="{{ old('prefixe') }}" maxlength="10" placeholder="Ex: PROMO, ETU2025">
                        <small class="text-muted">Le suffixe sera généré automatiquement de manière unique</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="count" class="form-label fw-semibold">Nombre de codes</label>
                        <div class="input-group" style="max-width: 160px;">
                            <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyMinus" style="border-radius: 6px 0 0 6px;">−</button>
                            <input type="number" class="form-control text-center" id="count" name="count" value="{{ old('count', 1) }}" min="1" max="100" style="border-left:0;border-right:0;">
                            <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyPlus" style="border-radius: 0 6px 6px 0;">+</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Aperçu du format</label>
                        <div class="p-2 rounded" style="background: #f5f5f5; font-family: 'Courier New', monospace; font-weight: 700; font-size: 1rem; color: var(--violet);" id="previewCode">
                            PRÉFIXE-XXXXXX
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="date_expiration" class="form-label fw-semibold">Date d'expiration</label>
                        <input type="date" class="form-control" id="date_expiration" name="date_expiration" value="{{ old('date_expiration') }}">
                        <small class="text-muted">Laissez vide pour pas d'expiration</small>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-custom" style="border-radius: 8px;">
                        <i class="bi bi-magic me-1"></i> Générer les codes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Block -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5>Distribution</h5>
            <span class="text-muted" style="font-size: 0.82rem;">
                {{ $disponibles }} disponibles · {{ $utilises }} utilisés
            </span>
        </div>
        <div class="panel-card-body">
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="{{ route('admin.codes-promos.export', ['evenement' => $evenement->id, 'disponibles' => 1]) }}" class="btn btn-secondary-custom" style="border-radius: 8px;">
                    <i class="bi bi-download me-1"></i> Exporter codes disponibles (CSV)
                </a>
                <a href="{{ route('admin.codes-promos.export', $evenement->id) }}" class="btn btn-secondary-custom" style="border-radius: 8px;">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exporter tous les codes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('.code-row');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        rows.forEach(row => {
            row.style.display = row.dataset.code.includes(query) ? '' : 'none';
        });
    });

    const prefixeInput = document.getElementById('prefixe');
    const previewCode = document.getElementById('previewCode');

    function updatePreview() {
        const prefix = prefixeInput.value.trim().toUpperCase();
        if (prefix) {
            previewCode.textContent = prefix + '-' + 'XXXXXX';
        } else {
            previewCode.textContent = 'XXXXXXXXXXXX';
        }
    }

    prefixeInput.addEventListener('input', updatePreview);

    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const countInput = document.getElementById('count');

    qtyMinus.addEventListener('click', function() {
        const v = parseInt(countInput.value);
        if (v > 1) { countInput.value = v - 1; }
    });

    qtyPlus.addEventListener('click', function() {
        const v = parseInt(countInput.value);
        if (v < 100) { countInput.value = v + 1; }
    });
});
</script>
@endsection
