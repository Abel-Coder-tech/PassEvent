@extends('superadmin.layouts.master')

@section('title', 'Lot ' . $lot->nom . ' - Super Admin')
@section('page-title', 'Lot : ' . $lot->nom)

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i>{{ $lot->nom }}</span>
                @if($lot->estTransmis)
                    <span class="sa-badge sa-badge-success">Transmis le {{ $lot->transmis_at?->format('d/m/Y H:i') }}</span>
                @else
                    <span class="sa-badge sa-badge-warning">Genere</span>
                @endif
            </div>
            <div class="sa-card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div style="font-size:1.4rem;font-weight:800;color:var(--sa-primary);">{{ $lot->quantite }}</div>
                        <div style="font-size:0.72rem;color:#888;">Tickets</div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:1.4rem;font-weight:800;color:#e74c3c;">{{ $lot->tickets->where('annule', true)->count() }}</div>
                        <div style="font-size:0.72rem;color:#888;">Annues</div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:1.4rem;font-weight:800;color:#27ae60;">{{ $lot->tickets->where('utilise', true)->where('annule', false)->count() }}</div>
                        <div style="font-size:0.72rem;color:#888;">Scannes</div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:1.4rem;font-weight:800;color:#7B3FA0;">{{ $lot->download_count }}/3</div>
                        <div style="font-size:0.72rem;color:#888;">Telechargements</div>
                    </div>
                </div>
                <hr>
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-person"></i></div>
                            <div>
                                <div class="sa-info-label">Organisateur</div>
                                <div class="sa-info-value">{{ $lot->user?->nom ?? '---' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-envelope"></i></div>
                            <div>
                                <div class="sa-info-label">Email</div>
                                <div class="sa-info-value">{{ $lot->user?->email ?? '---' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-calendar-event"></i></div>
                            <div>
                                <div class="sa-info-label">Evenement</div>
                                <div class="sa-info-value">{{ $lot->evenement?->titre ?? '---' }}</div>
                            </div>
                        </div>
                    </div>
                    @if($lot->evenement?->date_event)
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-calendar"></i></div>
                            <div>
                                <div class="sa-info-label">Date</div>
                                <div class="sa-info-value">{{ $lot->evenement->date_event->isoFormat('D MMM YYYY') }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-ticket-perforated"></i></div>
                            <div>
                                <div class="sa-info-label">Tarif</div>
                                <div class="sa-info-value">{{ $lot->tarif?->nom ?? '---' }} @if($lot->tarif?->prix)({{ number_format($lot->tarif->prix, 0, ',', ' ') }} FCFA)@endif</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="sa-info-box">
                            <div class="sa-info-icon"><i class="bi bi-cash-stack"></i></div>
                            <div>
                                <div class="sa-info-label">Valeur du lot</div>
                                <div class="sa-info-value">{{ number_format($lot->quantite * ($lot->tarif?->prix ?? 0), 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sa-card-body" style="border-top:1px solid #f1f2f6;">
                <div class="d-flex gap-2 flex-wrap">
                    @unless($lot->estTransmis)
                        <button type="button" class="sa-btn sa-btn-success" data-bs-toggle="modal" data-bs-target="#transmettreModal">
                            <i class="bi bi-send-fill"></i> Transmettre a l'organisateur
                        </button>
                        <form action="{{ route('superadmin.tickets-physiques.supprimer', $lot) }}" method="POST" onsubmit="return confirm('Supprimer ce lot et ses {{ $lot->quantite }} tickets ? Cette action est irreversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-trash"></i> Supprimer le lot</button>
                        </form>
                    @endunless
                    <a href="{{ route('superadmin.tickets-physiques.planche', $lot) }}" class="sa-btn" style="background:#3b82f6;border:none;color:#fff;" title="Télécharger la planche PDF de ce lot">
                        <i class="bi bi-file-earmark-pdf"></i> Planche PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <span><i class="bi bi-info-circle me-2" style="color: var(--sa-primary);"></i>Rappels</span>
            </div>
            <div class="sa-card-body" style="font-size:0.82rem;color:#555;line-height:1.6;">
                <ul class="mb-0 ps-3">
                    <li>Annulez un ticket uniquement en cas d'erreur d'impression ou de perte (il ne sera plus scannable).</li>
                    <li>Les tickets annules sont retires de la commission attendue.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-upc-scan me-2" style="color: var(--sa-primary);"></i>Tickets du lot ({{ $lot->tickets->count() }})</span>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="sa-btn sa-btn-sm" style="background:#f1f2f6;border:none;color:#666;" onclick="toggleAllQr()"><i class="bi bi-qr-code"></i> Afficher / masquer les QR</button>
        </div>
    </div>
    <div class="sa-card-body p-0">
        <form id="actionMasseForm" method="POST" action="{{ route('superadmin.tickets-physiques.action-masse', $lot) }}">
            @csrf
            <input type="hidden" name="action" id="actionMasseValue" value="">
            <div class="d-flex flex-wrap gap-2 align-items-center px-3 py-2" style="background:#fafafa;border-bottom:1px solid #f1f2f6;">
                <label class="sa-check-inline d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox" id="selectAll" class="form-check-input mt-0" style="width:16px;height:16px;">
                    <span class="small">Tout cocher</span>
                </label>
                <span class="small text-muted" id="selectedCount">0 sélectionné(s)</span>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="sa-btn sa-btn-sm sa-btn-danger" id="btnAnnulerMasse" disabled onclick="submitMasse('annuler')">
                        <i class="bi bi-x-circle"></i> Annuler la sélection
                    </button>
                    <button type="button" class="sa-btn sa-btn-sm sa-btn-danger" id="btnSupprimerMasse" disabled onclick="submitMasse('supprimer')">
                        <i class="bi bi-trash"></i> Supprimer la sélection
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th style="width:32px;"></th>
                            <th>Code</th>
                            <th class="text-center">QR</th>
                            <th class="text-end">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <input type="checkbox" name="tickets[]" value="{{ $ticket->id }}" class="ticket-check form-check-input mt-0"
                                    @if($ticket->utilise) disabled @endif>
                            </td>
                            <td><code style="font-size:0.9rem;">{{ $ticket->code_unique }}</code></td>
                            <td class="text-center">
                                <img src="{{ $qrs[$ticket->id] }}" alt="QR" style="width:44px;height:44px;" class="ticket-qr">
                            </td>
                            <td class="text-end">
                                @if($ticket->annule)
                                    <span class="sa-badge sa-badge-danger">Annule</span>
                                @elseif($ticket->utilise)
                                    <span class="sa-badge sa-badge-success">Scanne</span>
                                @else
                                    <span class="sa-badge sa-badge-secondary">Valide</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$ticket->annule && !$ticket->utilise)
                                    <form action="{{ route('superadmin.tickets-physiques.annuler', [$lot, $ticket]) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler le ticket {{ $ticket->code_unique }} ? Il ne sera plus scannable.')">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Annuler le ticket"><i class="bi bi-x-circle"></i> Annuler</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

{{-- Modal de transmission --}}
<div class="modal fade" id="transmettreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('superadmin.tickets-physiques.transmettre', $lot) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background:#542680;color:#fff;border-radius:0;">
                    <h5 class="modal-title"><i class="bi bi-send-fill me-2"></i>Transmettre le lot</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Organisateur</label>
                        <input type="text" class="form-control" value="{{ $lot->user?->nom }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Adresse email de l'organisateur</label>
                        <input type="email" name="email" class="form-control" value="{{ $lot->user?->email }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Note (facultative)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Un petit message pour l'organisateur..."></textarea>
                    </div>
                    <div class="small text-muted">
                        Le lot sera transmis : l'organisateur recevra un email et une notification dans son espace. Il pourra ensuite télécharger la planche de QR codes (3 téléchargements maximum).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn" style="background:#27ae60;color:#fff;font-weight:600;"><i class="bi bi-send-check"></i> Transmettre</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAllQr() {
    document.querySelectorAll('.ticket-qr').forEach(function (img) {
        img.style.display = img.style.display === 'none' ? '' : 'none';
    });
}

var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.ticket-check'));
var selectAll = document.getElementById('selectAll');
var countLabel = document.getElementById('selectedCount');
var btnAnnuler = document.getElementById('btnAnnulerMasse');
var btnSupprimer = document.getElementById('btnSupprimerMasse');

function updateSelection() {
    var checked = checkboxes.filter(function (c) { return c.checked; });
    countLabel.textContent = checked.length + ' sélectionné(s)';
    var has = checked.length > 0;
    btnAnnuler.disabled = !has;
    btnSupprimer.disabled = !has;
    if (checkboxes.length > 0) {
        selectAll.checked = checked.length === checkboxes.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
    }
}

selectAll.addEventListener('change', function () {
    checkboxes.forEach(function (c) { c.checked = selectAll.checked; });
    updateSelection();
});

checkboxes.forEach(function (c) { c.addEventListener('change', updateSelection); });
updateSelection();

function submitMasse(action) {
    var checked = checkboxes.filter(function (c) { return c.checked; });
    if (checked.length === 0) return;
    var libelle = action === 'annuler' ? 'annuler' : 'supprimer';
    if (!confirm('Voulez-vous vraiment ' + libelle + ' ' + checked.length + ' ticket(s) sélectionné(s) ?')) return;
    document.getElementById('actionMasseValue').value = action;
    document.getElementById('actionMasseForm').submit();
}
</script>
@endpush
