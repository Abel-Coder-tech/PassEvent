{{-- Modale d'envoi de newsletter (réutilisée par campagnes & événement) --}}
<div id="newsletterModal" class="nl-modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="nl-modal-box">
        <div class="nl-modal-header">
            <h5><i class="bi bi-send-fill me-2" style="color:var(--sa-primary);"></i>Envoyer une newsletter</h5>
            <button class="nl-modal-close" onclick="document.getElementById('newsletterModal').style.display='none'">&times;</button>
        </div>
        <div class="nl-modal-body">
            <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.78rem;border-radius:8px;">
                <i class="bi bi-info-circle me-1"></i>Le message part à tous les abonnés actifs de la newsletter PaxEvent.
            </div>
            <form action="{{ route('superadmin.newsletter.envoyer') }}" method="POST">
                @csrf
                <label class="nl-label" style="display:block;margin-bottom:0.3rem;">Objet</label>
                <input type="text" name="objet" id="nlObjet" class="form-control" maxlength="255" required
                       style="width:100%;border:1px solid #ddd;border-radius:8px;padding:0.5rem 0.7rem;font-size:0.85rem;">
                <label class="nl-label" style="display:block;margin:0.8rem 0 0.3rem;">Message</label>
                <textarea name="message" id="nlMessage" class="form-control" rows="8" maxlength="5000" required
                          style="width:100%;border:1px solid #ddd;border-radius:8px;padding:0.5rem 0.7rem;font-size:0.85rem;resize:vertical;"></textarea>
                <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:0.8rem;">
                    <button type="button" class="nl-btn-secondary" onclick="document.getElementById('newsletterModal').style.display='none'">Annuler</button>
                    <button type="submit" class="nl-btn-send"><i class="bi bi-send me-1"></i> Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.nl-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center; }
.nl-modal-box { background:#fff;border-radius:14px;width:90%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.2s ease; }
.nl-modal-header { display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #eee; }
.nl-modal-header h5 { margin:0;font-size:1rem;font-weight:700; }
.nl-modal-close { background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1; }
.nl-modal-body { padding:1.25rem; }
.nl-label { font-weight:600;color:#666;font-size:0.85rem; }
.nl-btn-secondary { background:#6c757d;border:none;color:#fff;padding:0.4rem 1rem;border-radius:6px;font-size:0.82rem;font-weight:600;cursor:pointer; }
.nl-btn-send { background:var(--sa-primary);border:none;color:#fff;padding:0.4rem 1rem;border-radius:6px;font-size:0.82rem;font-weight:600;cursor:pointer; }
</style>

@push('scripts')
<script>
function ouvrirNewsletter(btn) {
    const pid = btn && btn.dataset.prefillId ? btn.dataset.prefillId : null;
    const pre = (typeof NEWSLETTER_PREFILL !== 'undefined' && pid && NEWSLETTER_PREFILL[pid])
        ? NEWSLETTER_PREFILL[pid]
        : null;
    document.getElementById('nlObjet').value = (pre && pre.objet) || (btn && btn.dataset.objet || '');
    document.getElementById('nlMessage').value = (pre && pre.message) || (btn && btn.dataset.message || '');
    document.getElementById('newsletterModal').style.display = 'flex';
}
</script>
@endpush