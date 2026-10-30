<div class="sa-card-body p-0">
    <table class="sa-table">
        <thead>
            <tr><th>Nom</th><th>Email</th><th>Type</th><th>Statut</th><th>Evenements</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($organisateurs as $org)
            <tr>
                <td><strong>{{ $org->nom }}</strong></td>
                <td>{{ $org->email }}</td>
                <td>@if($org->type)<span class="sa-badge sa-badge-info">{{ ucfirst($org->type) }}</span>@else-@endif</td>
                <td>
                    @php $st = $org->statut; @endphp
                    @if($st === 'en_attente')
                        <span class="sa-badge sa-badge-warning">En attente</span>
                    @elseif($st === 'actif')
                        <span class="sa-badge sa-badge-success">Actif</span>
                    @elseif($st === 'incomplet')
                        <span class="sa-badge sa-badge-secondary">Incomplet</span>
                    @elseif($st === 'corrections_demandees')
                        <span class="sa-badge sa-badge-warning" style="background:rgba(237,173,8,0.12);color:#8b6914;">Corrections demandées</span>
                    @elseif($st === 'corrections_apportees')
                        <span class="sa-badge sa-badge-info" style="background:rgba(13,110,253,0.12);color:#0a58ca;">Corrections apportées</span>
                    @elseif($st === 'rejete')
                        <span class="sa-badge sa-badge-danger">Rejeté</span>
                    @elseif($st === 'bloque')
                        <span class="sa-badge sa-badge-danger">Bloqué</span>
                    @else
                        <span class="sa-badge sa-badge-secondary">{{ $st }}</span>
                    @endif
                </td>
                <td>{{ $org->evenements_count }}</td>
                <td style="white-space:nowrap;">
                    <div class="d-flex flex-nowrap gap-1">
                        <a href="{{ route('superadmin.organisateurs.voir', $org) }}" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.45rem;border-radius:6px;font-size:0.7rem;line-height:1;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;" title="Voir le détail">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(in_array($org->statut, ['en_attente', 'incomplet', 'corrections_demandees', 'corrections_apportees']))
                            <form action="{{ route('superadmin.organisateurs.approuver', $org) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <button class="sa-btn sa-btn-sm sa-btn-danger" title="Rejeter"
                                onclick="document.getElementById('rejetModal{{ $org->id }}').style.display='flex'">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @elseif($org->statut === 'actif')
                            <form action="{{ route('superadmin.organisateurs.suspendre', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspendre {{ $org->nom }} ? Ses événements seront annulés.')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-warning" title="Suspendre"><i class="bi bi-pause-fill"></i></button>
                            </form>
                            <form action="{{ route('superadmin.organisateurs.supprimer', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement {{ $org->nom }} ? Cette action est irréversible.')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        @elseif(in_array($org->statut, ['bloque', 'rejete']))
                            <form action="{{ route('superadmin.organisateurs.reactiver', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Réactiver {{ $org->nom }} ?{{ $org->statut === 'bloque' ? ' Ses événements annulés seront republiés.' : '' }}')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-success" title="Réactiver"><i class="bi bi-play-fill"></i></button>
                            </form>
                            <form action="{{ route('superadmin.organisateurs.supprimer', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement {{ $org->nom }} ? Cette action est irréversible.')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        @else
                            <form action="{{ route('superadmin.organisateurs.supprimer', $org) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement {{ $org->nom }} ? Cette action est irréversible.')">
                                @csrf
                                <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Aucun organisateur trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@foreach($organisateurs as $org)
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
@endforeach

<div class="p-3" id="organisateurs-pagination">{{ $organisateurs->appends(request()->query())->links('vendor.pagination.pass', ['label' => 'organisateur(s)']) }}</div>
