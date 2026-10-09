@extends('superadmin.layouts.master')

@section('title', 'Tickets physiques - Super Admin')
@section('page-title', 'Tickets physiques')

@section('content')
@if (session('success'))
<div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger py-2 small">{{ session('error') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <form method="GET" action="{{ route('superadmin.tickets-physiques') }}" id="searchForm" class="d-inline-flex align-items-center">
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:#888; font-size:0.8rem; pointer-events:none;"></i>
            <input type="text" name="q" id="qInput" class="form-control form-control-sm ps-4" style="border-radius:10px; min-width:500px;" placeholder="Rechercher par événement ou organisateur" value="{{ $q ?? '' }}" autocomplete="off">
        </div>
        @if(!empty($q))
        <a href="{{ route('superadmin.tickets-physiques') }}" class="sa-btn sa-btn-sm ms-1" style="background:#e74c3c;border:none;color:#fff;padding:0.2rem 0.5rem;border-radius:6px;font-size:0.7rem;text-decoration:none;" title="Effacer la recherche">
            <i class="bi bi-x-lg"></i>
        </a>
        @endif
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('superadmin.tickets-physiques.planches') }}" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;">
            <i class="bi bi-file-earmark-pdf"></i> Toutes les planches (1 PDF)
        </a>
        <a href="{{ route('superadmin.tickets-physiques.creer') }}" class="sa-btn sa-btn-primary">
            <i class="bi bi-plus-lg"></i> Générer un lot
        </a>
    </div>
</div>

{{-- Mini-dashboard physique (global) --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="font-size:1.35rem;color:var(--sa-primary);">{{ number_format($statsPhysiques['global']['quantite'], 0, ',', ' ') }}</div>
            <small class="text-muted">Tickets générés</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="font-size:1.35rem;color:#7B3FA0;">{{ number_format($statsPhysiques['global']['valeur'], 0, ',', ' ') }} F</div>
            <small class="text-muted">Valeur totale</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="font-size:1.35rem;color:#e67e22;">{{ number_format($statsPhysiques['global']['commission'], 0, ',', ' ') }} F</div>
            <small class="text-muted">Commission</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sa-card text-center py-2">
            <div class="fw-bold" style="font-size:1.35rem;color:#27ae60;">{{ count($statsPhysiques['organisateurs']) }}</div>
            <small class="text-muted">Organisateurs</small>
        </div>
    </div>
</div>

@if(!empty($statsPhysiques['organisateurs']))
<div class="sa-card mb-3">
    <div class="sa-card-header">
        <span><i class="bi bi-people-fill me-2" style="color: var(--sa-primary);"></i>Commission par organisateur</span>
        <span class="text-muted ms-auto" style="font-size:0.8rem;">Tickets générés hors annulés</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Organisateur</th>
                    <th class="text-center">Tickets générés</th>
                    <th class="text-end">Valeur</th>
                    <th class="text-end">Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsPhysiques['organisateurs'] as $ligne)
                <tr>
                    <td>{{ $ligne['nom'] }}</td>
                    <td class="text-center">{{ number_format($ligne['quantite'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($ligne['valeur'], 0, ',', ' ') }} F</td>
                    <td class="text-end"><strong style="color:#e67e22;">{{ number_format($ligne['commission'], 0, ',', ' ') }} F</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i>Lots</span>
        <span class="text-muted ms-auto" style="font-size:0.8rem;">{{ $lots->total() }} total</span>
    </div>
    <div class="sa-card-body p-0">
        @if($lots->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-ticket-perforated" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Aucun lot généré pour le moment.</p>
        </div>
        @else
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Organisateur</th>
                    <th>Evenement</th>
                    <th>Tarif</th>
                    <th class="text-center">Tickets</th>
                    <th class="text-center">Annués</th>
                    <th class="text-center">Scannés</th>
                    <th>Statut</th>
                <th class="text-center">Template</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lots as $lot)
                <tr>
                    <td><strong>{{ $lot->nom }}</strong>
                        @if($lot->estUneDemande())<br><span class="sa-badge sa-badge-warning" style="font-size:0.65rem;">DEMANDE</span>@endif
                    </td>
                    <td>{{ $lot->user?->nom ?? '---' }}</td>
                    <td>{{ $lot->evenement?->titre ?? '---' }}</td>
                    <td>{{ $lot->tarif?->nom ?? '---' }}
                        @if($lot->commission_pourcentage !== null && $lot->commission_pourcentage !== '')
                            <br><small style="font-size:0.7rem;color:#888;">comm. {{ number_format($lot->commission_pourcentage, 2, ',', ' ') }}%</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $lot->nb_tickets }}</td>
                    <td class="text-center">@if($lot->nb_annules > 0)<span class="sa-badge sa-badge-danger">{{ $lot->nb_annules }}</span>@else 0 @endif</td>
                    <td class="text-center">@if($lot->nb_scannes > 0)<span class="sa-badge sa-badge-success">{{ $lot->nb_scannes }}</span>@else 0 @endif</td>
                    <td>
                        @if($lot->estTransmis)
                            <span class="sa-badge sa-badge-success">Transmis {{ $lot->transmis_at?->format('d/m') }}</span>
                        @else
                            <span class="sa-badge sa-badge-warning" style="background:{{ $lot->statutCouleur() }};color:#fff;">{{ $lot->statutLibelle() }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($lot->templatePresent())
                            <span class="sa-badge sa-badge-success" title="Template fourni par l'organisateur"><i class="bi bi-check-circle-fill"></i> Validé</span>
                        @else
                            <span class="sa-badge sa-badge-danger" title="Aucun template"><i class="bi bi-x-circle"></i></span>
                        @endif
                    </td>
                    <td class="text-end" style="white-space:nowrap;">
                        <div class="d-flex flex-nowrap gap-1 justify-content-end">
                            <a href="{{ route('superadmin.tickets-physiques.voir', $lot) }}" class="sa-btn sa-btn-sm" style="background:#3b82f6;border:none;color:#fff;padding:0.25rem 0.45rem;border-radius:6px;font-size:0.7rem;line-height:1;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;" title="Voir le lot">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('superadmin.tickets-physiques.template', $lot) }}" class="sa-btn sa-btn-sm" style="background:var(--sa-primary);border:none;color:#fff;padding:0.25rem 0.45rem;border-radius:6px;font-size:0.7rem;line-height:1;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;" title="{{ $lot->estUneDemande() ? 'Positionner le QR code sur le template de l\'organisateur' : 'Configurer le template du ticket' }}">
                                <i class="bi bi-image"></i>
                            </a>
                            @unless($lot->estTransmis)
                                <button type="button" class="sa-btn sa-btn-sm sa-btn-success" title="{{ $lot->estUneDemande() && $lot->templatePresent() && ! $lot->aUnTemplate() ? 'QR code à positionner avant transmission' : 'Transmettre' }}"
                                    data-bs-toggle="modal" data-bs-target="#transmettreModal"
                                    data-action="{{ route('superadmin.tickets-physiques.transmettre', $lot) }}"
                                    data-organisateur="{{ $lot->user?->nom }}"
                                    data-email="{{ $lot->user?->email }}"><i class="bi bi-send-fill"></i></button>
                                <form action="{{ route('superadmin.tickets-physiques.supprimer', $lot) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce lot et ses {{ $lot->nb_tickets }} tickets ? Cette action est irreversible.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">
            {{ $lots->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal de transmission --}}
<div class="modal fade" id="transmettreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="transmettreForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background:#542680;color:#fff;border-radius:0;">
                    <h5 class="modal-title"><i class="bi bi-send-fill me-2"></i>Transmettre le lot</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Organisateur</label>
                        <input type="text" id="modalOrganisateur" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Adresse email de l'organisateur</label>
                        <input type="email" id="modalEmail" name="email" class="form-control" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="hidden" name="notifier" value="0">
                        <input class="form-check-input" type="checkbox" name="notifier" id="modalNotifier" value="1" checked
                            onchange="var e=document.getElementById('modalEmail');if(e){e.required=this.checked;e.disabled=!this.checked;}">
                        <label class="form-check-label small" for="modalNotifier">
                            Notifier l'organisateur par email
                            <span class="text-muted d-block" style="font-size:0.72rem;">La planche PDF du ticket est jointe automatiquement à cet email.</span>
                        </label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Note (facultative)</label>
                        <textarea name="note" id="modalNote" class="form-control" rows="3" placeholder="Un petit message pour l'organisateur..."></textarea>
                    </div>
                    <div class="small text-muted">
                        Le lot sera transmis : l'organisateur recevra une notification dans son espace organisateur. Il pourra ensuite télécharger la planche de QR codes (3 téléchargements maximum).
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
document.querySelectorAll('[data-bs-toggle="modal"][data-target="#transmettreModal"], [data-bs-toggle="modal"][data-bs-target="#transmettreModal"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const form = document.getElementById('transmettreForm');
        form.setAttribute('action', btn.getAttribute('data-action'));
        document.getElementById('modalOrganisateur').value = btn.getAttribute('data-organisateur') || '';
        var email = document.getElementById('modalEmail');
        email.value = btn.getAttribute('data-email') || '';
        email.required = true;
        email.disabled = false;
        var notifier = document.getElementById('modalNotifier');
        if (notifier) { notifier.checked = true; }
        document.getElementById('modalNote').value = '';
    });
});

// Recherche automatique (debounce 350ms)
const qInput = document.getElementById('qInput');
if (qInput) {
    let debounce;
    qInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            const url = new URL(qInput.closest('form').action);
            url.searchParams.set('q', qInput.value.trim());
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 350);
    });
}
</script>
@endpush
