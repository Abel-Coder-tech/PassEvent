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
                <button type="button" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;" onclick="supportRenvoyer({{ $ticket->id }})" title="Renvoyer l'email">
                    <i class="bi bi-envelope"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

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
</script>
