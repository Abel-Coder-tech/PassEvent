@extends('superadmin.layouts.master')

@section('title', 'Notifications - Super Admin')
@section('page-title', 'Notifications')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-bell-fill me-2" style="color: var(--sa-primary);"></i>Notifications</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $messages->total() }} notification(s)</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Type</th><th>Expéditeur</th><th>Objet</th><th>Message</th><th>Lu</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($messages as $msg)
                <tr style="{{ !$msg->lu ? 'background:rgba(107,63,160,0.03);' : '' }}">
                    <td>
                        @if(str_starts_with($msg->objet, '[Remboursement]'))
                            <span class="sa-badge" style="background:#f59e0b;color:#fff;">Remboursement</span>
                        @else
                            <span class="sa-badge sa-badge-primary">Contact</span>
                        @endif
                    </td>
                    <td><strong>{{ $msg->nom_complet }}</strong><br><small style="font-size:0.7rem;">{{ $msg->email }}</small></td>
                    <td>{{ $msg->objet }}</td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $msg->message }}</td>
                    <td>
                        @if($msg->lu)
                            <span class="sa-badge sa-badge-success">Lu</span>
                        @else
                            <span class="sa-badge sa-badge-warning">Non lu</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $msg->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td style="white-space:nowrap;">
                        <button class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;"
                            onclick="voirNotification({{ $msg->id }})" title="Voir">
                            <i class="bi bi-eye"></i>
                        </button>
                        <form action="{{ route('superadmin.notifications.supprimer', $msg) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette notification ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-sm" style="background:transparent;border:1px solid #e74c3c;color:#e74c3c;padding:0.25rem 0.5rem;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $messages->links() }}</div>

{{-- Modal Voir notification --}}
<div id="notifModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h5><i class="bi bi-bell-fill me-2" style="color:var(--sa-primary);"></i>Notification</h5>
            <button class="modal-close" onclick="document.getElementById('notifModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <div class="org-detail-row">
                <span class="org-detail-label">Expéditeur</span>
                <span class="org-detail-value"><strong id="modalNom"></strong></span>
            </div>
            <div class="org-detail-row">
                <span class="org-detail-label">Email</span>
                <span class="org-detail-value" id="modalEmail"></span>
            </div>
            <div class="org-detail-row">
                <span class="org-detail-label">Objet</span>
                <span class="org-detail-value" id="modalObjet"></span>
            </div>
            <div class="org-detail-row" style="border-bottom:none;">
                <span class="org-detail-label">Message</span>
                <span class="org-detail-value" id="modalMessage" style="white-space:pre-wrap;"></span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="sa-btn sa-btn-secondary" onclick="document.getElementById('notifModal').style.display='none'">Fermer</button>
        </div>
    </div>
</div>

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

function voirNotification(id) {
    const msg = notifs.find(n => n.id === id);
    if (!msg) return;
    document.getElementById('modalNom').textContent = msg.nom_complet;
    document.getElementById('modalEmail').textContent = msg.email;
    document.getElementById('modalObjet').textContent = msg.objet;
    document.getElementById('modalMessage').textContent = msg.message;
    document.getElementById('notifModal').style.display = 'flex';

    if (!msg.lu) {
        fetch('{{ url("/superadmin/notifications") }}/' + id + '/lire', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(() => location.reload());
    }
}
</script>
@endpush