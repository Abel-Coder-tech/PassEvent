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
    <input type="password" name="mot_de_passe" id="org_password" class="sa-form-control" placeholder="Mot de passe" required>
    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 24px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
    </button>
</div>
                        <div class="col-3"><input type="text" name="telephone" class="sa-form-control" placeholder="Telephone"></div>
                        <div class="col-3"><input type="text" name="organisation" class="sa-form-control" placeholder="Organisation"></div>
                        <div class="col-4">
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
        <span><i class="bi bi-person-badge-fill me-2" style="color: var(--sa-primary);"></i>Organisateurs</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $organisateurs->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Type</th><th>Organisation</th><th>Statut</th><th>Evenements</th><th>Téléphone</th><th>Inscrit</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($organisateurs as $org)
                <tr>
                    <td><strong>{{ $org->nom }}</strong></td>
                    <td>{{ $org->email }}</td>
                    <td>@if($org->type)<span class="sa-badge sa-badge-info">{{ ucfirst($org->type) }}</span>@else-@endif</td>
                    <td>{{ $org->organisation ?? '-' }}</td>
                    <td>
                        @if($org->statut === 'en_attente')
                            <span class="sa-badge sa-badge-warning">En attente</span>
                        @elseif($org->statut === 'actif')
                            <span class="sa-badge sa-badge-success">Actif</span>
                        @elseif($org->statut === 'corrections_demandees')
                            <span class="sa-badge sa-badge-warning" style="background:rgba(237,173,8,0.12);color:#8b6914;">Corrections demandées</span>
                        @elseif($org->statut === 'bloque')
                            <span class="sa-badge sa-badge-danger">Bloqué</span>
                        @else
                            <span class="sa-badge sa-badge-secondary">{{ $org->statut }}</span>
                        @endif
                    </td>
                    <td>{{ $org->evenements_count }}</td>
                    <td>{{ $org->telephone ?? '-' }}</td>
                    <td style="font-size:0.75rem;">{{ $org->created_at->format('d M Y') }}</td>
                    <td style="white-space:nowrap;">
                        <div class="d-flex flex-nowrap gap-1">
                            <button class="sa-btn sa-btn-sm sa-btn-info" title="Voir les détails"
                                onclick="document.getElementById('voirModal{{ $org->id }}').style.display='flex'">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($org->statut === 'en_attente')
                                <form action="{{ route('superadmin.organisateurs.approuver', $org) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <button class="sa-btn sa-btn-sm sa-btn-warning" title="Demander des corrections"
                                    onclick="document.getElementById('correctionsModal{{ $org->id }}').style.display='flex'">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="sa-btn sa-btn-sm sa-btn-danger" title="Rejeter"
                                    onclick="document.getElementById('rejetModal{{ $org->id }}').style.display='flex'">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @endif
                            @if($org->statut === 'actif')
                                <form action="{{ route('superadmin.organisateurs.suspendre', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspendre {{ $org->nom }} ? Ses événements seront annulés.')">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-sm sa-btn-warning" title="Suspendre"><i class="bi bi-pause-fill"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Modal Voir --}}
                <div id="voirModal{{ $org->id }}" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h5><i class="bi bi-person-badge me-2" style="color:var(--sa-primary);"></i>{{ $org->nom }}</h5>
                            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="org-detail-row">
                                <span class="org-detail-label">Email</span>
                                <span class="org-detail-value">{{ $org->email }}</span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Téléphone</span>
                                <span class="org-detail-value">{{ $org->telephone ?? '-' }}</span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Type</span>
                                <span class="org-detail-value">{{ $org->type ? ucfirst($org->type) : '-' }}</span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Organisation</span>
                                <span class="org-detail-value">{{ $org->organisation ?? '-' }}</span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Statut</span>
                                <span class="org-detail-value">
                                    @if($org->statut === 'en_attente')<span class="sa-badge sa-badge-warning">En attente</span>
                                    @elseif($org->statut === 'actif')<span class="sa-badge sa-badge-success">Actif</span>
                                    @elseif($org->statut === 'corrections_demandees')<span class="sa-badge sa-badge-warning" style="background:rgba(237,173,8,0.12);color:#8b6914;">Corrections demandées</span>
                                    @elseif($org->statut === 'bloque')<span class="sa-badge sa-badge-danger">Bloqué</span>
                                    @else<span class="sa-badge sa-badge-secondary">{{ $org->statut }}</span>@endif
                                </span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Description</span>
                                <span class="org-detail-value">{{ $org->description ?? 'Aucune description' }}</span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Evenements</span>
                                <span class="org-detail-value">{{ $org->evenements_count }}</span>
                            </div>
                            <div class="org-detail-row">
                                <span class="org-detail-label">Inscrit le</span>
                                <span class="org-detail-value">{{ $org->created_at->format('d M Y à H:i') }}</span>
                            </div>

                            {{-- Envoyer un email --}}
                            <hr style="margin:1rem 0;border-color:#eee;">
                            <h6 style="font-size:0.85rem;font-weight:700;margin-bottom:0.5rem;color:var(--sa-primary);">
                                <i class="bi bi-envelope me-1"></i> Envoyer un email
                            </h6>
                            <form action="{{ route('superadmin.organisateurs.email', $org) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="sujet" class="sa-form-control" placeholder="Sujet" required>
                                </div>
                                <div class="mb-2">
                                    <textarea name="message" class="sa-form-control" rows="3" placeholder="Votre message..." required style="resize:vertical;"></textarea>
                                </div>
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-primary">
                                    <i class="bi bi-send"></i> Envoyer
                                </button>
                            </form>
                        </div>
                        <div class="modal-footer" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                                @if($org->statut === 'en_attente')
                                    <form action="{{ route('superadmin.organisateurs.approuver', $org) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="sa-btn sa-btn-primary">
                                            <i class="bi bi-check-lg"></i> Valider
                                        </button>
                                    </form>
                                    <button class="sa-btn sa-btn-warning" onclick="this.closest('.modal-overlay').style.display='none';document.getElementById('correctionsModal{{ $org->id }}').style.display='flex'">
                                        <i class="bi bi-pencil-square"></i> Corrections
                                    </button>
                                @endif
                                <form action="{{ route('superadmin.organisateurs.supprimer', $org) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer définitivement {{ $org->nom }} ? Cette action est irréversible.')">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-danger">
                                        <i class="bi bi-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                            <button class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Fermer</button>
                        </div>
                    </div>
{{-- Modal Rejet --}}
                <div id="rejetModal{{ $org->id }}" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h5><i class="bi bi-x-circle me-2" style="color:var(--sa-danger);"></i>Rejeter {{ $org->nom }}</h5>
                            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                        </div>
                        <form action="{{ route('superadmin.organisateurs.rejeter', $org) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Expliquez le motif du rejet. L'organisateur recevra un email avec cette explication.</p>
                                <textarea name="motif" class="sa-form-control" rows="4" placeholder="Motif du rejet..." required style="resize:vertical;"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                                <button type="submit" class="sa-btn sa-btn-danger"><i class="bi bi-x-lg"></i> Rejeter</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal Corrections --}}
                <div id="correctionsModal{{ $org->id }}" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h5><i class="bi bi-pencil-square me-2" style="color:var(--sa-warning);"></i>Demander des corrections</h5>
                            <button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button>
                        </div>
                        <form action="{{ route('superadmin.organisateurs.corrections', $org) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">Indiquez les modifications nécessaires. L'organisateur recevra un email et pourra corriger son profil.</p>
                                <textarea name="motif" class="sa-form-control" rows="4" placeholder="Détail des corrections à apporter..." required style="resize:vertical;"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="sa-btn sa-btn-secondary" onclick="this.closest('.modal-overlay').style.display='none'">Annuler</button>
                                <button type="submit" class="sa-btn sa-btn-warning"><i class="bi bi-send"></i> Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>

                </div>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $organisateurs->links() }}</div>

<style>
.sa-badge {
    font-size: 0.68rem;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
}
.sa-badge-success { background: rgba(46,125,79,0.12); color: #2e7d4f; }
.sa-badge-warning { background: rgba(237,173,8,0.12); color: #b8860b; }
.sa-badge-danger { background: rgba(231,76,60,0.12); color: #e74c3c; }
.sa-badge-secondary { background: rgba(152,145,155,0.15); color: #6c757d; }
.sa-badge-info { background: rgba(13,110,253,0.12); color: #0a58ca; }
.sa-btn-success {
    background: #2e7d4f; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-success:hover { opacity: 0.85; }
.sa-btn-warning {
    background: #e0a800; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
}
.sa-btn-warning:hover { opacity: 0.85; }
.sa-btn-info {
    background: #3b82f6; border: none; color: #fff; padding: 0.3rem 0.6rem;
    border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
    transition: opacity 0.15s;
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
</script>
@endpush
@endsection
