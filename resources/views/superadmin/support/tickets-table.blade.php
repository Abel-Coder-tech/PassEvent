@once
<div style="padding:0.75rem;border-bottom:1px solid rgba(0,0,0,0.05);display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
    <button type="button" class="sa-btn sa-btn-sm" style="background:var(--sa-success);border:none;color:#fff;" onclick="supportAction('confirmer')">
        <i class="bi bi-check-circle"></i> Confirmer les sélectionnés
    </button>
    <button type="button" class="sa-btn sa-btn-sm" style="background:#e74c3c;border:none;color:#fff;" onclick="supportAction('supprimer')">
        <i class="bi bi-trash"></i> Supprimer les sélectionnés
    </button>
    <button type="button" class="sa-btn sa-btn-sm" style="background:#8e44ad;border:none;color:#fff;" onclick="supportAction('rembourser')">
        <i class="bi bi-cash-coin"></i> Rembourser les sélectionnés
    </button>
    <span class="text-muted" style="font-size:0.72rem;margin-left:auto;" id="support-count">0 sélectionné(s)</span>
</div>
@endonce

<table class="sa-table">
    <thead>
        <tr>
            <th style="width:36px;"><input type="checkbox" id="support-check-all" onchange="supportToggleAll(this)"></th>
            <th>Billet</th>
            <th>Événement</th>
            <th>Acheteur</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>FedaPay</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr id="support-row-{{ $ticket->id }}">
            <td><input type="checkbox" class="support-check" value="{{ $ticket->id }}" onchange="supportUpdateCount()"></td>
            <td>
                <span class="fw-bold" style="font-size:0.75rem;color:var(--sa-primary);">{{ $ticket->code_unique }}</span><br>
                <small style="font-size:0.68rem;">#{{ $ticket->id }} · {{ $ticket->nom_tarif }}</small>
            </td>
            <td style="max-width:160px;font-size:0.75rem;">{{ $ticket->evenement?->titre ?? '—' }}</td>
            <td style="max-width:150px;">
                <div style="font-size:0.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $ticket->nom_acheteur }}</div>
                <small style="font-size:0.68rem;color:#666;">{{ $ticket->email_acheteur ?? '—' }}</small><br>
                <small style="font-size:0.68rem;color:#666;">{{ $ticket->telephone_acheteur ?? '—' }}</small>
            </td>
            <td class="fw-bold" style="font-size:0.78rem;">{{ number_format($ticket->montant, 0, ',', ' ') }} F</td>
            <td>
                @if($ticket->statut_paiement === 'payé')
                    <span class="sa-badge sa-badge-success">Payé</span>
                @elseif($ticket->statut_paiement === 'en_attente')
                    <span class="sa-badge sa-badge-warning">En attente</span>
                @elseif($ticket->statut_paiement === 'remboursé')
                    <span class="sa-badge" style="background:rgba(52,152,219,0.12);color:#2563eb;">Remboursé</span>
                @else
                    <span class="sa-badge sa-badge-danger">{{ $ticket->statut_paiement }}</span>
                @endif
            </td>
            <td style="font-size:0.7rem;">{{ $ticket->fedapay_transaction_id ?? '—' }}</td>
            <td style="white-space:nowrap;">
                <button type="button" class="sa-btn sa-btn-sm" style="background:#8b5cf6;border:none;color:#fff;" onclick="supportVoirIncident({{ $ticket->id }})" title="Voir la notification (incident)">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;" onclick="supportRenvoyer({{ $ticket->id }})" title="Renvoyer l'email">
                    <i class="bi bi-envelope"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@once
{{-- Formulaires d'action (remplis par JavaScript) --}}
<form id="support-form-confirmer" method="POST" action="{{ route('superadmin.support.confirmer') }}">
    @csrf
    <input type="hidden" name="transaction_id" value="{{ $transactionId ?? '' }}">
    <input type="hidden" name="notes" value="">
</form>
<form id="support-form-supprimer" method="POST" action="{{ route('superadmin.support.supprimer') }}">
    @csrf
    <input type="hidden" name="motif" value="">
</form>
<form id="support-form-rembourser" method="POST" action="{{ route('superadmin.support.rembourser') }}">
    @csrf
    <input type="hidden" name="motif" value="">
    <input type="hidden" name="notes" value="">
</form>
<form id="support-form-renvoyer" method="POST" action="{{ route('superadmin.support.renvoyer-email') }}">
    @csrf
    <input type="hidden" name="ticket_id" value="">
</form>

{{-- Modal Voir incident --}}
<div id="incidentModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h5><i class="bi bi-exclamation-triangle me-2" style="color:#e74c3c;"></i>Incident <span id="incidentTicketRef" style="font-weight:600;"></span></h5>
            <button class="modal-close" onclick="document.getElementById('incidentModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body" id="incidentModalBody"></div>
        <div class="modal-footer">
            <button class="sa-btn sa-btn-secondary" onclick="document.getElementById('incidentModal').style.display='none'">Fermer</button>
        </div>
    </div>
</div>

<style>
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center; }
.modal-box { background:#fff;border-radius:14px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.2s ease; }
@keyframes modalIn { from{transform:scale(0.95);opacity:0} to{transform:scale(1);opacity:1} }
.modal-header { display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #eee; }
.modal-header h5 { margin:0;font-size:1rem;font-weight:700; }
.modal-close { background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1; }
.modal-body { padding:1.25rem; }
.modal-footer { padding:0.75rem 1.25rem;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:0.5rem; }
.sa-btn-secondary { background:#6c757d;border:none;color:#fff;padding:0.4rem 1rem;border-radius:6px;font-size:0.82rem;font-weight:600;cursor:pointer; }
.incident-msg { padding:0.75rem 0;border-bottom:1px solid #f5f5f5;font-size:0.85rem; }
.incident-msg:last-child { border-bottom:none; }
</style>

<script>
function supportSelected() {
    return Array.from(document.querySelectorAll('.support-check:checked')).map(c => c.value);
}
function supportUpdateCount() {
    const n = supportSelected().length;
    document.getElementById('support-count').textContent = n + ' sélectionné(s)';
    document.querySelectorAll('.support-check:checked').forEach(c => {
        const row = document.getElementById('support-row-' + c.value);
        if (row) row.style.background = 'rgba(107,63,160,0.06)';
    });
    document.querySelectorAll('.support-check:not(:checked)').forEach(c => {
        const row = document.getElementById('support-row-' + c.value);
        if (row) row.style.background = '';
    });
}
function supportToggleAll(el) {
    document.querySelectorAll('.support-check').forEach(c => c.checked = el.checked);
    supportUpdateCount();
}
function supportAction(action) {
    const ids = supportSelected();
    if (!ids.length) { alert('Sélectionnez au moins un billet.'); return; }
    const form = document.getElementById('support-form-' + action);
    if (!form) return;

    let motif = '', notes = '';
    if (action === 'supprimer') {
        motif = prompt('Motif de la suppression :') || '';
        if (!motif) return;
    }
    if (action === 'rembourser') {
        motif = prompt('Motif du remboursement :') || '';
        if (!motif) return;
        notes = prompt('Notes internes (facultatif) :') || '';
    }
    if (action === 'confirmer') {
        const t = prompt('ID transaction FedaPay (preuve API) :');
        if (!t) {
            const force = confirm('Confirmer SANS preuve API ?\nOverride tracé, à n\'utiliser qu\'après contrôle manuel.');
            if (!force) return;
            form.querySelector('input[name="transaction_id"]').value = '';
        } else {
            form.querySelector('input[name="transaction_id"]').value = t;
        }
    }
    form.querySelectorAll('input[name="ticket_ids[]"]').forEach(el => el.remove());
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ticket_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    const m = form.querySelector('input[name="motif"]'); if (m) m.value = motif;
    const n = form.querySelector('input[name="notes"]'); if (n) n.value = notes;
    form.submit();
}
function supportRenvoyer(ticketId) {
    const form = document.getElementById('support-form-renvoyer');
    form.querySelector('input[name="ticket_id"]').value = ticketId;
    form.submit();
}
function supportVoirIncident(ticketId) {
    fetch('{{ route('superadmin.support.incident-message') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ticket_id: ticketId })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('incidentTicketRef').textContent = '#' + data.ticket_id + ' · ' + (data.code_unique || '');
        const body = document.getElementById('incidentModalBody');
        if (!data.messages || !data.messages.length) {
            body.innerHTML = '<div class="text-muted" style="font-size:0.85rem;">Aucune notification liée à ce ticket.</div>';
        } else {
            body.innerHTML = data.messages.map(m => `
                <div class="incident-msg">
                    <div><strong>${escapeHtml(m.nom_complet)}</strong> <small class="text-muted">${escapeHtml(m.email)}</small></div>
                    <div class="text-muted" style="margin-top:0.25rem;">${escapeHtml(m.objet)}</div>
                    <div style="margin-top:0.35rem;white-space:pre-wrap;">${escapeHtml(m.message)}</div>
                    ${m.telephone ? `<div class="text-muted" style="margin-top:0.25rem;">Tél : ${escapeHtml(m.telephone)}</div>` : ''}
                    ${m.email_achat ? `<div class="text-muted">Email d'achat : ${escapeHtml(m.email_achat)}</div>` : ''}
                    ${m.transaction_id ? `<div class="text-muted">Transaction : ${escapeHtml(m.transaction_id)}</div>` : ''}
                    <div class="text-muted" style="margin-top:0.25rem;">${escapeHtml(m.date)}</div>
                </div>
            `).join('');
        }
        document.getElementById('incidentModal').style.display = 'flex';
        const dot = document.querySelector('.sa-notif-dot');
        if (dot) {
            const n = parseInt(dot.textContent, 10);
            if (n > 1) dot.textContent = n - 1;
            else dot.remove();
        }
    })
    .catch(() => alert('Erreur lors du chargement de la notification.'));
}
</script>
@endonce
