@extends('superadmin.layouts.master')

@php $nlPrefill = []; @endphp

@section('title', 'Campagnes marketing - Super Admin')
@section('page-title', 'Campagnes marketing')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-megaphone-fill me-2" style="color: var(--sa-primary);"></i>Campagnes marketing</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $messages->total() }} demande(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Type</th><th>Expéditeur</th><th>Objet</th><th>Message</th><th>Lu</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($messages as $msg)
                <tr id="row-{{ $msg->id }}" style="{{ !$msg->lu ? 'background:rgba(107,63,160,0.03);' : '' }}">
                    <td>
                        <span class="sa-badge" style="background:#7B3FA0;color:#fff;">Campagne</span>
                    </td>
                    <td>
                        <strong>{{ $msg->nom_complet }}</strong><br><small style="font-size:0.7rem;">{{ $msg->email }}</small>
                        @if($msg->telephone)
                            <br><small style="font-size:0.7rem;">{{ $msg->telephone }}</small>
                        @endif
                        @if($msg->evenement)
                            <br><small style="font-size:0.7rem;color:var(--sa-primary);"><i class="bi bi-calendar-event me-1"></i>{{ $msg->evenement->titre }}</small>
                        @endif
                    </td>
                    <td>{{ $msg->objet }}</td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $msg->message }}</td>
                    <td id="lu-{{ $msg->id }}">
                        @if($msg->lu)
                            <span class="sa-badge sa-badge-success">Lu</span>
                        @else
                            <span class="sa-badge sa-badge-warning">Non lu</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $msg->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td style="white-space:nowrap;vertical-align:top;">
                        <div style="display:flex;gap:0.3rem;margin-bottom:0.35rem;">
                            <button class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;"
                                onclick="voirNotification({{ $msg->id }})" title="Voir">
                                <i class="bi bi-eye"></i>
                            </button>
                            <form action="{{ route('superadmin.notifications.supprimer', $msg) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette demande ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="sa-btn sa-btn-sm" style="background:transparent;border:1px solid #e74c3c;color:#e74c3c;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @php
                            $ev = $msg->evenement;
                            $nlObjet = $ev ? ('🎉 '.$ev->titre.' — réservez vos billets !') : '';
                            $nlMessage = $ev
                                ? ('🎉 '.$ev->titre." arrive bientôt !\n\n"
                                    .($ev->date_event ? '📅 '.$ev->date_event->isoFormat('D MMM YYYY HH:mm')."\n" : '')
                                    .($ev->lieu ? '📍 '.$ev->lieu."\n" : '')
                                    ."\nRéservez vos billets dès maintenant sur PaxEvent :\n"
                                    .route('evenements.public.show', $ev)."\n\nÉquipe PaxEvent")
                                : '';
                            $nlPrefill[$msg->id] = ['objet' => $nlObjet, 'message' => $nlMessage];
                        @endphp
                        <div id="quick-{{ $msg->id }}" class="quick-actions" style="{{ $msg->lu ? '' : 'display:none;' }}">
                            @if($ev)
                                <a href="{{ route('superadmin.evenements.voir', $ev) }}" class="sa-btn sa-btn-sm sa-btn-outline" style="text-decoration:none;margin-bottom:0.2rem;">
                                    <i class="bi bi-calendar-event"></i> Voir l'événement
                                </a>
                                @if($ev->statut !== 'publié')
                                    <form action="{{ route('superadmin.evenements.mettre-en-avant', $ev) }}" method="POST" class="d-inline-block" style="margin-bottom:0.2rem;margin-right:0.2rem;">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-sm" style="background:var(--sa-success);color:#fff;border:none;" title="Publier / remettre en avant">
                                            <i class="bi bi-check-lg"></i> Publier
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('superadmin.evenements.a-la-une', $ev) }}" method="POST" class="d-inline-block" style="margin-bottom:0.2rem;margin-right:0.2rem;">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-sm {{ $ev->a_la_une ? 'sa-btn-danger' : 'sa-btn-outline' }}" title="{{ $ev->a_la_une ? 'Retirer de la une' : 'Mettre à la une' }}">
                                        <i class="bi bi-{{ $ev->a_la_une ? 'star-fill' : 'star' }}"></i>
                                        {{ $ev->a_la_une ? 'Retirer de la une' : 'Mettre à la une' }}
                                    </button>
                                </form>
                                @if($ev->a_la_une)
                                    <form action="{{ route('superadmin.evenements.a-la-une.ordre', [$ev, 'haut']) }}" method="POST" class="d-inline-block" style="margin-bottom:0.2rem;margin-right:0.2rem;">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Monter dans la une"><i class="bi bi-arrow-up"></i></button>
                                    </form>
                                    <form action="{{ route('superadmin.evenements.a-la-une.ordre', [$ev, 'bas']) }}" method="POST" class="d-inline-block" style="margin-bottom:0.2rem;margin-right:0.2rem;">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-sm sa-btn-outline" title="Descendre dans la une"><i class="bi bi-arrow-down"></i></button>
                                    </form>
                                @endif
                                <button type="button" class="sa-btn sa-btn-sm" style="background:#7B3FA0;color:#fff;border:none;margin-bottom:0.2rem;"
                                    data-prefill-id="{{ $msg->id }}" onclick="ouvrirNewsletter(this)" title="Envoyer une newsletter">
                                    <i class="bi bi-send-fill"></i> Newsletter
                                </button>
                            @else
                                <small class="text-muted" style="font-size:0.7rem;">Aucun événement lié</small>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $messages->links() }}</div>

{{-- Modal Voir la demande de campagne --}}
<div id="notifModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h5><i class="bi bi-megaphone-fill me-2" style="color:var(--sa-primary);"></i>Demande de campagne</h5>
            <button class="modal-close" onclick="document.getElementById('notifModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <div class="org-detail-row">
                <span class="org-detail-label">Organisateur</span>
                <span class="org-detail-value"><strong id="modalNom"></strong></span>
            </div>
            <div class="org-detail-row">
                <span class="org-detail-label">Email</span>
                <span class="org-detail-value" id="modalEmail"></span>
            </div>
            <div class="org-detail-row" id="modalTelRow" style="display:none;">
                <span class="org-detail-label">Téléphone</span>
                <span class="org-detail-value" id="modalTel"></span>
            </div>
            <div class="org-detail-row" id="modalEvenementRow" style="display:none;">
                <span class="org-detail-label">Événement</span>
                <span class="org-detail-value" id="modalEvenement"></span>
            </div>
            <div class="org-detail-row">
                <span class="org-detail-label">Objet</span>
                <span class="org-detail-value" id="modalObjet"></span>
            </div>
            <div class="org-detail-row" style="border-bottom:none;">
                <span class="org-detail-label">Message</span>
                <span class="org-detail-value" id="modalMessage" style="white-space:pre-wrap;"></span>
            </div>

            <div id="modalQuickSection" style="display:none;margin-top:1rem;border-top:1px solid #f5f5f5;padding-top:1rem;">
                <span class="org-detail-label" style="display:block;margin-bottom:0.4rem;"><i class="bi bi-lightning-charge-fill me-1" style="color:var(--sa-primary);"></i>Actions rapides (booster)</span>
                <div id="modalQuickActions" style="display:flex;flex-wrap:wrap;gap:0.4rem;"></div>
            </div>

            <form id="repondreNotifForm" action="" method="POST" style="margin-top:1rem;border-top:1px solid #f5f5f5;padding-top:1rem;">
                @csrf
                <label class="org-detail-label" style="display:block;margin-bottom:0.4rem;">Envoyer une note</label>
                <textarea name="note" id="modalNote" class="form-control" rows="3" style="font-size:0.85rem;width:100%;border:1px solid #ddd;border-radius:8px;padding:0.5rem 0.7rem;resize:vertical;"></textarea>
                <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:0.6rem;">
                    <button type="button" class="sa-btn sa-btn-secondary" onclick="document.getElementById('notifModal').style.display='none'">Fermer</button>
                    <button type="submit" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.4rem 1rem;border-radius:6px;font-size:0.82rem;font-weight:600;cursor:pointer;">
                        <i class="bi bi-send me-1"></i> Envoyer la note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('superadmin.partials.newsletter-modal')

<style>
.org-detail-row { display:flex;gap:1rem;padding:0.5rem 0;border-bottom:1px solid #f5f5f5;font-size:0.85rem; }
.org-detail-row:last-child { border-bottom:none; }
.org-detail-label { font-weight:600;color:#666;min-width:100px;flex-shrink:0; }
.org-detail-value { color:#1a1a1a; }
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center; }
.modal-box { background:#fff;border-radius:14px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.2s ease; }
@keyframes modalIn { from{transform:scale(0.95);opacity:0} to{transform:scale(1);opacity:1} }
.modal-header { display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #eee; }
.modal-header h5 { margin:0;font-size:1rem;font-weight:700; }
.modal-close { background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1; }
.modal-body { padding:1.25rem; }
.modal-footer { padding:0.75rem 1.25rem;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:0.5rem; }
.sa-btn-secondary { background:#6c757d;border:none;color:#fff;padding:0.4rem 1rem;border-radius:6px;font-size:0.82rem;font-weight:600;cursor:pointer; }
</style>
@endsection

@push('scripts')
<script>
const notifs = @json($messages->items());
const NEWSLETTER_PREFILL = @json($nlPrefill);

function voirNotification(id) {
    const msg = notifs.find(n => n.id === id);
    if (!msg) return;
    document.getElementById('modalNom').textContent = msg.nom_complet;
    document.getElementById('modalEmail').textContent = msg.email;
    document.getElementById('modalObjet').textContent = msg.objet;
    document.getElementById('modalMessage').textContent = msg.message;

    const telRow = document.getElementById('modalTelRow');
    const telEl = document.getElementById('modalTel');
    if (msg.telephone) { telEl.textContent = msg.telephone; telRow.style.display = ''; }
    else { telRow.style.display = 'none'; }

    const evenementRow = document.getElementById('modalEvenementRow');
    const evenementEl = document.getElementById('modalEvenement');
    if (msg.evenement && msg.evenement.titre) {
        evenementEl.textContent = msg.evenement.titre;
        evenementRow.style.display = '';
    } else {
        evenementRow.style.display = 'none';
    }

    document.getElementById('repondreNotifForm').action = '{{ url("/superadmin/notifications") }}/' + id + '/repondre';
    document.getElementById('modalNote').value = "Bonjour " + (msg.nom_complet || 'cher organisateur') + ",\n\nNous avons bien reçu votre demande de campagne marketing et nous revenons vers vous rapidement.\n\nCordialement,\nL'équipe PaxEvent";

    const quick = document.getElementById('quick-' + id);
    if (quick) quick.style.display = '';

    const quickSection = document.getElementById('modalQuickSection');
    const quickActions = document.getElementById('modalQuickActions');
    if (quick && quickActions) {
        quickActions.innerHTML = quick.innerHTML;
        quickSection.style.display = quick.innerHTML.trim() ? '' : 'none';
    }

    document.getElementById('notifModal').style.display = 'flex';

    if (!msg.lu) {
        fetch('{{ url("/superadmin/notifications") }}/' + id + '/lire', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(() => {
                const row = document.getElementById('row-' + id);
                if (row) row.style.background = '';
                const luCell = document.getElementById('lu-' + id);
                if (luCell) luCell.innerHTML = '<span class="sa-badge sa-badge-success">Lu</span>';
                const m = notifs.find(x => x.id === id);
                if (m) m.lu = true;
            });
    }
}
</script>
@endpush