@extends('layouts.app')

@section('title', 'Logs système - PaxEvent')

@section('page-title', 'Logs système')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Logs</li>
@endsection

@section('topbar-actions')
    <button type="button" class="btn btn-vert btn-sm" data-bs-toggle="modal" data-bs-target="#modalRecuperer">
        <i class="bi bi-search me-1"></i> <span class="btn-text">Récupérer un ticket</span>
    </button>
@endsection

@section('content')
<div class="page-content">
    {{-- Banner explicatif --}}
    <div class="alert mb-4 d-flex align-items-start" style="background: rgba(135,66,139,0.06); border-left: 4px solid var(--violet); border-radius: 8px; padding: 1rem 1.25rem;">
        <i class="bi bi-info-circle me-2 mt-1" style="color: var(--violet); font-size: 1.25rem;"></i>
        <div>
            <strong style="color: var(--violet); font-size: 0.85rem;">Traçabilité des opérations</strong>
            <p class="mb-0 text-muted" style="font-size: 0.82rem;">
                Les logs assurent la traçabilité complète des actions (achats, envois, scans, échecs...) sans compte participant. Chaque opération est enregistrée avec horodatage, adresse IP et détails techniques.
            </p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="panel-card mb-4">
        <div class="panel-card-body py-3">
            <form action="{{ route('logs.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Rechercher</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Nom, email, code ticket, IP..." value="{{ $q }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Type d'action</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($types as $key => $info)
                            <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold" style="font-size: 0.78rem;">Période</label>
                    <select name="periode" class="form-select form-select-sm">
                        <option value="7" {{ $periode === '7' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="30" {{ $periode === '30' ? 'selected' : '' }}>7 derniers jours</option>
                        <option value="90" {{ $periode === '90' ? 'selected' : '' }}>30 derniers jours</option>
                        <option value="annee" {{ $periode === 'annee' ? 'selected' : '' }}>Cette année</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-vert btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrer
                    </button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('logs.index') }}" class="btn btn-secondary-custom btn-sm w-100" style="border-radius: 6px;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- 5 KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--sombre);">
                <div class="metric-icon" style="background: rgba(61,67,69,0.1); color: var(--sombre);">
                    <i class="bi bi-activity"></i>
                </div>
                <div class="metric-label">Total opérations</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--violet);">
                <div class="metric-icon" style="background: rgba(135,66,139,0.1); color: var(--violet);">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div class="metric-label">Achats</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['achats']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--vert);">
                <div class="metric-icon" style="background: rgba(18,151,110,0.1); color: var(--vert);">
                    <i class="bi bi-send"></i>
                </div>
                <div class="metric-label">Envois tickets</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['envois']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--teal);">
                <div class="metric-icon" style="background: rgba(66,140,121,0.1); color: var(--teal);">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <div class="metric-label">Scans validés</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['scans']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="metric-card" style="border-top-color: var(--danger);">
                <div class="metric-icon" style="background: rgba(231,76,60,0.1); color: var(--danger);">
                    <i class="bi bi-x-octagon"></i>
                </div>
                <div class="metric-label">Opérations échouées</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ number_format($stats['echecs']) }}</div>
            </div>
        </div>
    </div>

    {{-- Tableau des logs --}}
    <div class="panel-card">
        <div class="panel-card-header">
            <h5><i class="bi bi-journal-text me-2" style="color: var(--sombre);"></i>Journal des opérations</h5>
            <span class="text-muted" style="font-size: 0.78rem;">{{ $logs->total() }} opération{{ $logs->total() > 1 ? 's' : '' }}</span>
        </div>
        <div class="panel-card-body p-0">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Horodatage</th>
                                <th>Participant</th>
                                <th>Type d'action</th>
                                <th>Détails</th>
                                <th>Canal</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <div style="font-size: 0.82rem; white-space: nowrap;">{{ $log->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @if($log->ticket)
                                            <div class="fw-bold" style="font-size: 0.82rem;">{{ Str::limit($log->ticket->nom_acheteur, 25) }}</div>
                                            <small class="text-muted" style="font-size: 0.72rem;">{{ Str::limit($log->ticket->email_acheteur, 30) }}</small>
                                        @else
                                            <span class="text-muted" style="font-size: 0.82rem;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $typeInfo = $types[$log->type_operation] ?? ['label' => $log->type_operation, 'color' => 'var(--gris)'];
                                        @endphp
                                        <span class="status-badge" style="background: {{ $typeInfo['color'] }}15; color: {{ $typeInfo['color'] }};">{{ $typeInfo['label'] }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.78rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            @if(is_array($log->details))
                                                {{ $log->details['message'] ?? ($log->details['canal'] ?? json_encode($log->details)) }}
                                            @else
                                                {{ Str::limit($log->details, 40) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(is_array($log->details) && isset($log->details['canal']))
                                            @php
                                                $canalIcon = match($log->details['canal']) {
                                                    'email' => 'bi-envelope',
                                                    'whatsapp' => 'bi-whatsapp',
                                                    'sms' => 'bi-phone',
                                                    'web' => 'bi-globe',
                                                    'manuel' => 'bi-person',
                                                    default => 'bi-question-circle'
                                                };
                                            @endphp
                                            <span style="font-size: 0.78rem;"><i class="{{ $canalIcon }} me-1"></i>{{ ucfirst($log->details['canal']) }}</span>
                                        @else
                                            <span class="text-muted" style="font-size: 0.78rem;">web</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(in_array($log->type_operation, ['achat', 'envoi', 'scan', 'recuperation']))
                                            <span class="badge" style="background: rgba(18,151,110,0.12); color: var(--vert); font-size: 0.68rem;">Succès</span>
                                        @elseif($log->type_operation === 'echec_paiement')
                                            <span class="badge" style="background: rgba(231,76,60,0.12); color: var(--danger); font-size: 0.68rem;">Échec</span>
                                        @else
                                            <span class="badge" style="background: rgba(152,145,155,0.15); color: var(--gris); font-size: 0.68rem;">Info</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-secondary-custom" style="border-radius: 6px; padding: 0.25rem 0.5rem;" onclick="showLogDetail({{ $log->id }})" title="Détail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $logs->appends(['type' => $type, 'q' => $q, 'periode' => $periode])->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-journal-x" style="font-size: 3rem; color: var(--gris);"></i>
                    <p class="text-muted mt-3 mb-0">Aucune opération enregistrée pour cette période.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal détail log --}}
<div class="modal fade" id="modalLogDetail" tabindex="-1" aria-labelledby="modalLogDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem;">
                <h5 class="modal-title" id="modalLogDetailLabel">
                    <i class="bi bi-journal-text me-2" style="color: var(--violet);"></i>Détail du log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-4" id="logDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-muted" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal récupération ticket --}}
<div class="modal fade" id="modalRecuperer" tabindex="-1" aria-labelledby="modalRecupererLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem;">
                <h5 class="modal-title" id="modalRecupererLabel">
                    <i class="bi bi-search me-2" style="color: var(--vert);"></i>Récupérer un ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" style="font-size: 0.85rem;">
                    Entrez le numéro de téléphone et l'email de l'acheteur pour retrouver et renvoyer le ticket.
                </p>
                <form id="formRecuperer">
                    <div class="mb-3">
                        <label for="recTelephone" class="form-label fw-semibold" style="font-size: 0.82rem;">Numéro de téléphone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="recTelephone" name="telephone" placeholder="+229 62 83 66 29" required>
                    </div>
                    <div class="mb-3">
                        <label for="recEmail" class="form-label fw-semibold" style="font-size: 0.82rem;">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="recEmail" name="email" placeholder="exemple@email.com" required>
                    </div>
                    <div id="recResult"></div>
                    <button type="submit" class="btn btn-vert w-100" id="btnRecuperer" style="border-radius: 8px;">
                        <i class="bi bi-send me-1"></i> Rechercher et renvoyer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showLogDetail(logId) {
    const modal = new bootstrap.Modal(document.getElementById('modalLogDetail'));
    modal.show();

    const content = document.getElementById('logDetailContent');
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-muted" role="status"></div></div>';

    fetch(`/admin/logs/${logId}/detail`)
        .then(res => res.json())
        .then(data => {
            let detailsHtml = '';
            if (data.details && typeof data.details === 'object') {
                detailsHtml = Object.entries(data.details).map(([key, value]) => {
                    return `<div class="mb-2"><strong>${key}:</strong> <code style="font-size: 0.78rem;">${typeof value === 'object' ? JSON.stringify(value) : value}</code></div>`;
                }).join('');
            } else {
                detailsHtml = `<div class="mb-2"><code style="font-size: 0.78rem; white-space: pre-wrap;">${JSON.stringify(data.details, null, 2)}</code></div>`;
            }

            content.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background: var(--blanc-casse);">
                            <label class="text-muted" style="font-size: 0.72rem; font-weight: 600;">TYPE D'OPÉRATION</label>
                            <div class="fw-bold" style="font-size: 0.9rem;">${data.type_operation}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background: var(--blanc-casse);">
                            <label class="text-muted" style="font-size: 0.72rem; font-weight: 600;">HORODATAGE</label>
                            <div class="fw-bold" style="font-size: 0.9rem;">${data.created_at}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background: var(--blanc-casse);">
                            <label class="text-muted" style="font-size: 0.72rem; font-weight: 600;">ADRESSE IP</label>
                            <div class="fw-bold" style="font-size: 0.85rem;">${data.ip || '—'}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background: var(--blanc-casse);">
                            <label class="text-muted" style="font-size: 0.72rem; font-weight: 600;">USER AGENT</label>
                            <div style="font-size: 0.75rem; word-break: break-all;">${data.user_agent || '—'}</div>
                        </div>
                    </div>
                    ${data.ticket ? `
                    <div class="col-12">
                        <div class="p-3 rounded" style="background: rgba(135,66,139,0.06);">
                            <label class="text-muted" style="font-size: 0.72rem; font-weight: 600;">TICKET ASSOCIÉ</label>
                            <div class="row g-2 mt-1">
                                <div class="col-md-4"><strong>Code:</strong> <code>${data.ticket.code_unique}</code></div>
                                <div class="col-md-4"><strong>Acheteur:</strong> ${data.ticket.nom_acheteur}</div>
                                <div class="col-md-4"><strong>Email:</strong> ${data.ticket.email_acheteur}</div>
                                ${data.ticket.evenement ? `<div class="col-12"><strong>Événement:</strong> ${data.ticket.evenement}</div>` : ''}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    <div class="col-12">
                        <div class="p-3 rounded" style="background: #f5f5f5;">
                            <label class="text-muted" style="font-size: 0.72rem; font-weight: 600;">JSON BRUT</label>
                            <pre style="font-size: 0.72rem; margin-top: 0.5rem; white-space: pre-wrap; word-break: break-all; max-height: 200px; overflow-y: auto;">${JSON.stringify(data.details, null, 2)}</pre>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            content.innerHTML = '<div class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle"></i> Erreur lors du chargement du détail.</div>';
        });
}

document.getElementById('formRecuperer').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnRecuperer');
    const result = document.getElementById('recResult');
    const telephone = document.getElementById('recTelephone').value;
    const email = document.getElementById('recEmail').value;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Recherche en cours...';
    result.innerHTML = '';

    fetch('{{ route("tickets.recuperer") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ telephone, email }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = `
                <div class="alert alert-success mt-3" style="border-radius: 8px; font-size: 0.85rem;">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Ticket trouvé et envoyé !</strong><br>
                    <span class="mt-1 d-block">
                        Code: <strong>${data.ticket.code}</strong><br>
                        Événement: ${data.ticket.evenement}<br>
                        Envoyé à: ${data.ticket.email}
                    </span>
                </div>
            `;
            document.getElementById('formRecuperer').reset();
        } else {
            result.innerHTML = `
                <div class="alert alert-danger mt-3" style="border-radius: 8px; font-size: 0.85rem;">
                    <i class="bi bi-x-circle me-2"></i> ${data.message}
                </div>
            `;
        }
    })
    .catch(err => {
        result.innerHTML = `
            <div class="alert alert-danger mt-3" style="border-radius: 8px; font-size: 0.85rem;">
                <i class="bi bi-exclamation-triangle me-2"></i> Erreur lors de la recherche.
            </div>
        `;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Rechercher et renvoyer';
    });
});
</script>
@endsection
