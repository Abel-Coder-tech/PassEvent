@extends('layouts.app')

@section('title', 'Générer mes QR codes - Billetterie')
@section('page-title', 'Générer mes QR codes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.lots-physiques.index') }}">Vente physique</a></li>
    <li class="breadcrumb-item active" aria-current="page">Générer</li>
@endsection

@section('content')
<div class="page-content">
    <style>
    .steps-bar { display: flex; align-items: center; gap: 0; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: .25rem; }
    .step-item { display: flex; align-items: center; flex: 1; min-width: max-content; }
    .step-dot { width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; flex-shrink: 0; transition: all .3s; }
    .step-label { font-size: .72rem; color: #6c757d; margin-left: .4rem; white-space: nowrap; }
    .step-line { flex: 1; height: 2px; background: #e9ecef; margin: 0 .5rem; min-width: 16px; transition: background .3s; }
    .step-item.done .step-dot { background: #198754; color: #fff; }
    .step-item.done .step-label { color: #198754; }
    .step-item.done .step-line { background: #198754; }
    .step-item.active .step-dot { background: #542680; color: #fff; box-shadow: 0 0 0 4px rgba(84,38,128,.15); }
    .step-item.active .step-label { color: #542680; font-weight: 700; }
    .event-card { cursor: pointer; border: 2px solid #e9ecef; border-radius: 12px; padding: .9rem 1rem; transition: all .2s; height: 100%; }
    .event-card:hover { border-color: #c4a6dd; }
    .event-card.selected { border-color: #542680; background: rgba(84,38,128,.05); }
    .qty-input { max-width: 90px; }
    .total-card { position: sticky; bottom: .75rem; z-index: 5; }
    </style>

    @if($errors->any())
    <div class="alert alert-danger py-2 small">{{ $errors->all()[0] }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif

    <!-- Barre de progression -->
    <div class="steps-bar">
        <div class="step-item" id="step1">
            <span class="step-dot">1</span><span class="step-label">Événement</span><span class="step-line"></span>
        </div>
        <div class="step-item" id="step2">
            <span class="step-dot">2</span><span class="step-label">Quantités</span><span class="step-line"></span>
        </div>
        <div class="step-item" id="step3">
            <span class="step-dot">3</span><span class="step-label">Récapitulatif</span><span class="step-line"></span>
        </div>
        <div class="step-item" id="step4">
            <span class="step-dot">4</span><span class="step-label">Paiement</span><span class="step-line"></span>
        </div>
        <div class="step-item" id="step5">
            <span class="step-dot">5</span><span class="step-label">Téléchargement</span>
        </div>
    </div>

    @if($evenements->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-x" style="font-size:3rem;"></i>
        <p class="mt-2">Aucun événement à venir avec des tarifs actifs.</p>
        <p style="font-size:.85rem;">Créez un événement avec au moins un tarif actif pour générer vos QR codes.</p>
    </div>
    @else
    <form method="POST" action="{{ route('admin.lots-physiques.commander') }}" id="formCommande">
        @csrf
        <input type="hidden" name="evenement_id" id="evenement_id" value="">

        <!-- Étape 1 : choix de l'événement -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3"><strong><i class="bi bi-calendar-event me-1"></i> Choisissez l'événement</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($evenements as $ev)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="event-card" onclick="selectEvent({{ $ev['id'] }}, this)">
                            <div class="fw-semibold text-truncate">{{ $ev['titre'] }}</div>
                            <div class="text-muted" style="font-size:.8rem;">
                                @if($ev['date_event'])<i class="bi bi-clock me-1"></i>{{ $ev['date_event'] }}@else<i class="bi bi-infinity me-1"></i>Date libre @endif
                                @if($ev['gratuit'])<span class="badge bg-success ms-1">Gratuit</span>@endif
                            </div>
                            <div class="mt-1" style="font-size:.78rem;">
                                @foreach($ev['tarifs'] as $t)
                                <span class="badge bg-light text-dark border">{{ $t['nom'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Étape 2 : quantités par tarif -->
        <div class="card border-0 shadow-sm mb-4" id="cardTarifs" style="display:none;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-1">
                <strong><i class="bi bi-ticket-detailed me-1"></i> Tarifs &amp; quantités</strong>
                <small class="text-muted">Commission PaxEvent : <strong>{{ number_format($tauxCommission, 1, ',', '') }} %</strong> du prix du billet (non négociable)</small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tarif</th>
                            <th>Prix unitaire</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-end">Valeur billets</th>
                            <th class="text-end pe-3">Commission ({{ number_format($tauxCommission, 1, ',', '') }} %)</th>
                        </tr>
                    </thead>
                    <tbody id="tarifsBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Total à payer -->
        <div class="total-card mb-2" id="cardTotal" style="display:none;">
            <div class="card border-0 shadow" style="border-top: 3px solid #542680 !important;">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="text-muted small">Total billets : <strong id="totalBillets">0</strong></span>
                        <span class="text-muted small ms-3">Total à payer (commission) :</span>
                        <strong id="totalPayer" style="font-size:1.25rem;color:#542680;">0 F</strong>
                    </div>
                    <button type="button" class="btn btn-sm text-white" id="btnGenerer" style="background:#542680;border-radius:8px;font-weight:600;" onclick="openRecap()" disabled>
                        <i class="bi bi-qr-code me-1"></i> Générer mes QR codes
                    </button>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>

<!-- Modal récapitulatif -->
<div class="modal fade" id="modalRecap" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-1" style="color:#542680;"></i> Récapitulatif de votre commande</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="{{ route('admin.lots-physiques.commander') }}">
                @csrf
                <input type="hidden" name="evenement_id" id="evenement_id_modal" value="">
                <div id="quantitesContainer"></div>
                <div class="modal-body">
                    <div id="recapLignes" class="mb-3"></div>
                    <div class="d-flex justify-content-between border-top pt-2 mb-3">
                        <span class="fw-semibold small">Total à payer</span>
                        <strong id="recapTotal" style="color:#542680;"></strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Email de réception</label>
                        <input type="email" name="email_reception" class="form-control form-control-sm" value="{{ old('email_reception', $emailDefaut) }}" placeholder="Recevra la confirmation">
                        <div class="form-text">Un lien vers vos planches sera envoyé à cette adresse après paiement.</div>
                    </div>
                    <div class="alert alert-light border py-2 mb-0" style="font-size:.75rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        La commission de {{ number_format($tauxCommission, 1, ',', '') }} % est payée d'avance via FedaPay. Vos planches PDF seront disponibles immédiatement après le paiement.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary-custom" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm text-white" style="background:#542680;font-weight:600;" id="btnPayer">
                        <i class="bi bi-shield-lock me-1"></i> <span id="btnPayerLabel">Payer maintenant</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const EVENEMENTS = @json($evenements);
const TAUX = {{ $tauxCommission }};
let evenementCourant = null;

function marquerEtape(n) {
    for (let i = 1; i <= n; i++) {
        const el = document.getElementById('step' + i);
        el.classList.add('done');
        el.classList.remove('active');
    }
    const suivant = document.getElementById('step' + (n + 1));
    if (suivant) suivant.classList.add('active');
}

function selectEvent(id, el) {
    document.querySelectorAll('.event-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    evenementCourant = EVENEMENTS.find(e => e.id === id);
    document.getElementById('evenement_id').value = id;
    document.getElementById('evenement_id_modal').value = id;

    const body = document.getElementById('tarifsBody');
    body.innerHTML = '';
    evenementCourant.tarifs.forEach(t => {
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td class="ps-3">' + escapeHtml(t.nom) + '</td>' +
            '<td>' + fmt(t.prix) + ' F</td>' +
            '<td class="text-center"><input type="number" class="form-control form-control-sm qty-input mx-auto" min="0" max="500" value="0" data-tarif="' + t.id + '" data-prix="' + t.prix + '" oninput="recalc()"></td>' +
            '<td class="text-end ligne-valeur">0 F</td>' +
            '<td class="text-end pe-3 ligne-commission fw-semibold">0 F</td>';
        body.appendChild(tr);
    });

    document.getElementById('cardTarifs').style.display = '';
    document.getElementById('cardTotal').style.display = '';
    marquerEtape(1);
    recalc();
}

function recalc() {
    let totalBillets = 0, totalCommission = 0;
    document.querySelectorAll('#tarifsBody tr').forEach(tr => {
        const input = tr.querySelector('input');
        const qte = Math.max(0, Math.min(500, parseInt(input.value) || 0));
        input.value = qte;
        const prix = parseFloat(input.dataset.prix);
        const valeur = qte * prix;
        const commission = Math.round(valeur * TAUX) / 100;
        tr.querySelector('.ligne-valeur').textContent = fmt(valeur) + ' F';
        tr.querySelector('.ligne-commission').textContent = fmt(commission) + ' F';
        totalBillets += qte;
        totalCommission += commission;
    });
    totalCommission = Math.round(totalCommission * 100) / 100;
    document.getElementById('totalBillets').textContent = totalBillets;
    document.getElementById('totalPayer').textContent = fmt(totalCommission) + ' F';
    document.getElementById('btnGenerer').disabled = totalBillets === 0;
    return { totalBillets, totalCommission };
}

function openRecap() {
    const totaux = recalc();
    if (totaux.totalBillets === 0) return;

    const recap = document.getElementById('recapLignes');
    let html = '';
    document.querySelectorAll('#tarifsBody tr').forEach(tr => {
        const input = tr.querySelector('input');
        const qte = parseInt(input.value) || 0;
        if (qte <= 0) return;
        const nom = tr.querySelector('td').textContent.trim();
        const prix = parseFloat(input.dataset.prix);
        const commission = Math.round(qte * prix * TAUX) / 100;
        html += '<div class="d-flex justify-content-between small py-1 border-bottom">' +
            '<span>' + qte + ' × ' + escapeHtml(nom) + '</span>' +
            '<span class="fw-semibold">' + fmt(commission) + ' F</span></div>';
    });
    recap.innerHTML = html || '<div class="text-muted small">Aucune ligne</div>';
    document.getElementById('recapTotal').textContent = fmt(totaux.totalCommission) + ' F';

    const gratuit = totaux.totalCommission <= 0;
    document.getElementById('btnPayerLabel').textContent = gratuit ? 'Générer gratuitement' : 'Payer ' + fmt(totaux.totalCommission) + ' F';

    // Sérialise les quantités en champs quantites[tarif_id] pour la soumission du modal
    const container = document.getElementById('quantitesContainer');
    container.innerHTML = '';
    document.querySelectorAll('#tarifsBody input').forEach(input => {
        const qte = parseInt(input.value) || 0;
        if (qte > 0) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'quantites[' + input.dataset.tarif + ']';
            hidden.value = qte;
            container.appendChild(hidden);
        }
    });

    marquerEtape(2);
    new bootstrap.Modal(document.getElementById('modalRecap')).show();
}

// Le formulaire du modal doit soumettre les mêmes données que le formulaire principal :
// on copie l'état juste avant l'envoi
document.querySelector('#modalRecap form').addEventListener('submit', function () {
    document.getElementById('evenement_id_modal').value = document.getElementById('evenement_id').value;
    marquerEtape(3);
});

function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n * 100) / 100);
}

function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
</script>
@endsection
