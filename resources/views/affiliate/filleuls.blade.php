@extends('layouts.affiliate')

@section('title', 'Mes filleuls — Espace affilié PaxEvent')

@section('content')
    <h1 class="h4 fw-bold mb-1">Mes filleuls</h1>
    <p class="text-muted small mb-4">Les organisateurs que vous avez parrainés (parrainage direct).</p>

    <div class="card aff-card mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-2"><i class="bi bi-share me-1"></i> Votre lien de parrainage</h2>
            <p class="text-muted small mb-2">Partagez ce lien : chaque organisateur qui s'inscrit via celui-ci devient votre filleul et vous rapporte 20 % de la commission PaxEvent sur son premier événement.</p>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="code-chip">{{ $affiliate->code_parrainage }}</span>
                <div class="input-group" style="max-width: 420px;">
                    <input type="text" class="form-control" id="lienParrainage" value="{{ $lienParrainage }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copierLien(this)">
                        <i class="bi bi-clipboard"></i><span class="d-none d-sm-inline"> Copier</span>
                    </button>
                </div>

                <button class="btn btn-violet" type="button" id="btnShareNative" style="display:none" onclick="partagerLien()">
                    <i class="bi bi-share"></i><span class="d-none d-sm-inline"> Partager</span>
                </button>

                <div class="dropdown" id="grpShareFallback">
                    <button class="btn btn-violet dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-share"></i><span class="d-none d-sm-inline"> Partager</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" id="shareWhatsapp" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2 text-success"></i> WhatsApp</a></li>
                        <li><a class="dropdown-item" id="shareTelegram" target="_blank" rel="noopener"><i class="bi bi-telegram me-2 text-info"></i> Telegram</a></li>
                        <li><a class="dropdown-item" id="shareFacebook" target="_blank" rel="noopener"><i class="bi bi-facebook me-2 text-primary"></i> Facebook</a></li>
                        <li><a class="dropdown-item" id="shareEmail"><i class="bi bi-envelope me-2 text-secondary"></i> Email</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="shareNatifMenu"><i class="bi bi-phone me-2"></i> Autres options…</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Filleuls</div>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Filleuls validés</div>
                <div class="value">{{ $stats['valides'] }}</div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="stat-card card h-100">
                <div class="label">Commissions générées</div>
                <div class="value">{{ number_format((float) $stats['commissions'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
            </div>
        </div>
    </div>

    <div class="card aff-card">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Liste de vos filleuls</h2>
            @if($referrals->isEmpty())
                @include('affiliate.partials.empty-state', [
                    'icon' => 'bi-people',
                    'title' => 'Aucun filleul',
                    'text' => 'Partagez votre lien de parrainage pour commencer.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Organisateur</th>
                                <th>Inscrit le</th>
                                <th>Premier événement</th>
                                <th class="text-end">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $ref)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $ref->organisateur?->nom ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:.78rem;">{{ $ref->organisateur?->email }}</div>
                                    </td>
                                    <td class="small">{{ $ref->created_at?->format('d/m/Y') }}</td>
                                    <td class="small">{{ $ref->premierEvenement?->titre ?? '—' }}</td>
                                    <td class="text-end">
                                        @php
                                            $badge = [
                                                'en_attente' => 'bg-warning-subtle text-warning-emphasis',
                                                'valide' => 'bg-success-subtle text-success-emphasis',
                                                'annule' => 'bg-secondary-subtle text-secondary-emphasis',
                                            ][$ref->statut] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            $label = ['en_attente' => 'En attente', 'valide' => 'Validé', 'annule' => 'Annulé'][$ref->statut] ?? $ref->statut;
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $label }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('lienParrainage');
    var lien = input ? input.value : '';
    var texte = "Crée ton événement sur PaxEvent, la billetterie en ligne : " + lien;

    function feedback(btn, label, icon) {
        if (!btn) return;
        var html = btn.innerHTML;
        btn.innerHTML = '<i class="bi ' + icon + '"></i><span class="d-none d-sm-inline"> ' + label + '</span>';
        setTimeout(function () { btn.innerHTML = html; }, 1500);
    }

    function execCommand() {
        input.select();
        input.setSelectionRange(0, 99999);
        try { document.execCommand('copy'); } catch (e) {}
    }

    window.copierLien = function (btn) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(lien).then(function () {
                feedback(btn, 'Copié', 'bi-check2');
            }).catch(function () { execCommand(); feedback(btn, 'Copié', 'bi-check2'); });
        } else {
            execCommand();
            feedback(btn, 'Copié', 'bi-check2');
        }
    };

    window.partagerLien = function () {
        if (navigator.share) {
            navigator.share({ title: 'PaxEvent', text: "Rejoins PaxEvent, la billetterie en ligne", url: lien }).catch(function () {});
        }
    };

    var wa = document.getElementById('shareWhatsapp');
    var tg = document.getElementById('shareTelegram');
    var fb = document.getElementById('shareFacebook');
    var em = document.getElementById('shareEmail');
    var natifMenu = document.getElementById('shareNatifMenu');
    if (wa) wa.href = 'https://wa.me/?text=' + encodeURIComponent(texte);
    if (tg) tg.href = 'https://t.me/share/url?url=' + encodeURIComponent(lien) + '&text=' + encodeURIComponent('Rejoins PaxEvent');
    if (fb) fb.href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(lien);
    if (em) em.href = 'mailto:?subject=' + encodeURIComponent('Découvre PaxEvent') + '&body=' + encodeURIComponent(texte);
    if (natifMenu) natifMenu.addEventListener('click', function (e) { e.preventDefault(); window.partagerLien(); });

    if (navigator.share) {
        var nativeBtn = document.getElementById('btnShareNative');
        var fallback = document.getElementById('grpShareFallback');
        if (nativeBtn) nativeBtn.style.display = '';
        if (fallback) fallback.style.display = 'none';
    }
})();
</script>
@endpush
