@extends('superadmin.layouts.master')

@section('title', 'Newsletter - Super Admin')
@section('page-title', 'Newsletter')

@section('content')
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="sa-card" style="padding:1rem 1.25rem;text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:var(--sa-primary);">{{ $totalActifs }}</div>
        <div style="font-size:0.78rem;color:#888;">Abonnés actifs</div>
    </div>
    <div class="sa-card" style="padding:1rem 1.25rem;text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:#e74c3c;">{{ $totalInactifs }}</div>
        <div style="font-size:0.78rem;color:#888;">Désabonnés</div>
    </div>
    <div class="sa-card" style="padding:1rem 1.25rem;text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:#333;">{{ $totalActifs + $totalInactifs }}</div>
        <div style="font-size:0.78rem;color:#888;">Total inscrits</div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-envelope-fill me-2" style="color: var(--sa-primary);"></i>Abonnés newsletter</span>
        <button class="sa-btn sa-btn-primary" onclick="document.getElementById('envoiModal').style.display='flex'">
            <i class="bi bi-send-fill me-1"></i> Envoyer un message
        </button>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Email</th><th>Statut</th><th>Inscrit le</th><th>Désabonné le</th></tr>
            </thead>
            <tbody>
                @forelse($abonnes as $abonne)
                <tr>
                    <td><strong>{{ $abonne->email }}</strong></td>
                    <td>
                        @if($abonne->actif)
                            <span class="sa-badge sa-badge-success">Actif</span>
                        @else
                            <span class="sa-badge" style="background:#e74c3c;color:#fff;">Désabonné</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $abonne->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                    <td style="font-size:0.75rem;">{{ $abonne->desabonne_le ? $abonne->desabonne_le->isoFormat('D MMM YYYY HH:mm') : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:2rem;color:#999;">Aucun abonné pour le moment.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $abonnes->links() }}</div>

{{-- Modal Envoi newsletter --}}
<div id="envoiModal" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h5><i class="bi bi-send-fill me-2" style="color:var(--sa-primary);"></i>Envoyer un message</h5>
            <button class="modal-close" onclick="document.getElementById('envoiModal').style.display='none'">&times;</button>
        </div>
        <form action="{{ route('superadmin.newsletter.envoyer') }}" method="POST" id="formEnvoi">
            @csrf
            <div class="modal-body">
                <p style="font-size:0.82rem;color:#666;margin-bottom:1rem;">Ce message sera envoyé par email à <strong>{{ $totalActifs }}</strong> abonné(s) actif(s).</p>
                <div style="margin-bottom:1rem;">
                    <label style="font-weight:600;font-size:0.82rem;display:block;margin-bottom:0.3rem;">Objet</label>
                    <input type="text" name="objet" required maxlength="255" placeholder="Ex : Nouveautés, promo, événement..."
                        style="width:100%;padding:0.5rem 0.75rem;border:1px solid #ddd;border-radius:8px;font-size:0.85rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-weight:600;font-size:0.82rem;display:block;margin-bottom:0.3rem;">Message</label>
                    <textarea name="message" required maxlength="5000" rows="6" placeholder="Votre message aux abonnés..."
                        style="width:100%;padding:0.5rem 0.75rem;border:1px solid #ddd;border-radius:8px;font-size:0.85rem;resize:vertical;box-sizing:border-box;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sa-btn sa-btn-secondary" onclick="document.getElementById('envoiModal').style.display='none'">Annuler</button>
                <button type="submit" class="sa-btn sa-btn-primary" onclick="return confirm('Envoyer ce message à {{ $totalActifs }} abonné(s) ?')">
                    <i class="bi bi-send-fill me-1"></i> Envoyer
                </button>
            </div>
        </form>
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
</style>
@endsection
