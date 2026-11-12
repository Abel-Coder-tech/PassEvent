@extends('layouts.app')

@section('title', 'Vente manuelle')

@section('page-title', 'Vente manuelle')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vente manuelle</li>
@endsection

@section('page-subtitle', 'Vendre un billet sur place — espèces ou paiement mobile via FedaPay')

@section('content')
<div class="page-content">

    <div class="row g-3">
        <!-- Left Column: Form + Summary -->
        <div class="col-12 col-lg-7">
            <form id="venteForm" novalidate>
                @csrf

                <!-- Section 1: Event Selection -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5><span style="display:inline-flex;align-items-center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--violet);color:#fff;font-size:0.75rem;font-weight:700;margin-right:0.5rem;">1</span> Sélectionner l'événement</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="mb-0">
                            <label for="evenement_id" class="form-label fw-semibold">Événement <span class="text-danger">*</span></label>
                            <select class="form-select" id="evenement_id" name="evenement_id" required>
                                <option value="">Choisir un événement —</option>
                                @foreach($evenements as $evt)
                                    <option value="{{ $evt->id }}" data-tarif-min="{{ $evt->tarifs->min('prix') }}" data-gratuit="{{ $evt->gratuit ? '1' : '0' }}" data-type="{{ $evt->type_evenement }}">{{ $evt->titre }} — {{ $evt->date_event->isoFormat('D MMM YYYY') }}</option>
                                @endforeach
                            </select>
                            @error('evenement_id') <div class="text-danger mt-1" style="font-size:0.85rem;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Buyer Info -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5><span style="display:inline-flex;align-items-center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--teal);color:#fff;font-size:0.75rem;font-weight:700;margin-right:0.5rem;">2</span> Informations de l'acheteur</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nom_acheteur" class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom_acheteur" name="nom_acheteur" placeholder="Ex: Adja Koné" required>
                            </div>
                            <div class="col-md-6">
                                <label for="whatsapp" class="form-label fw-semibold">N° WhatsApp <span class="text-muted fw-normal">(facultatif)</span></label>
                                <input type="tel" class="form-control" id="whatsapp" name="whatsapp" placeholder="+229 62 83 66 29">
                            </div>
                            <div class="col-12">
<label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
<input type="email" class="form-control" id="email" name="email" placeholder="adja@email.com" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Status, Tariff, Payment -->
                <div class="panel-card mb-3" id="sectionTarif">
                    <div class="panel-card-header">
                        <h5><span style="display:inline-flex;align-items-center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--menthe);color:var(--sombre);font-size:0.75rem;font-weight:700;margin-right:0.5rem;">3</span> Statut et tarif</h5>
                    </div>
                    <div class="panel-card-body">
                        <div class="row g-3">
                            <div class="col-md-6 d-none" id="fieldStatut"></div>
                            <div class="col-12" id="fieldTarif">
                                <span class="form-label fw-semibold d-block">Billets <span class="text-danger">*</span></span>
                                <div id="listeBillets" class="vm-billets">
                                    <div class="text-muted small">Sélectionnez d'abord un événement.</div>
                                </div>
                            </div>
                            <div class="col-md-6" id="fieldQuantite">
                                <label for="quantite" class="form-label fw-semibold">Nombre de places</label>
                                <div class="input-group" style="max-width: 160px;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyMinus" style="border-radius: 6px 0 0 6px;">−</button>
                                    <input type="number" class="form-control text-center" id="quantite" name="quantite" value="1" min="1" max="20" style="border-left:0;border-right:0;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyPlus" style="border-radius: 0 6px 6px 0;">+</button>
                                </div>
                            </div>
                            <div class="col-md-6" id="fieldPaiement">
                                <label for="methode_paiement" class="form-label fw-semibold">Moyen de paiement <span class="text-danger">*</span></label>
                                <select class="form-select" id="methode_paiement" name="methode_paiement" required>
                                    <option value="especes" id="optEspeces">Espèces</option>
                                    <option value="mobile">Mobile</option>
                                </select>
                                <div id="alertEspeces" class="mt-2" style="display:none;"></div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6" id="fieldPromo">
                                <label for="code_promo" class="form-label fw-semibold">Code promo <span class="text-muted fw-normal">(facultatif)</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="code_promo" name="code_promo" placeholder="Ex: BIENVENUE10" maxlength="50">
                                    <button type="button" class="btn btn-outline-secondary" id="btnVerifierPromo" style="border-radius:0 6px 6px 0;">Vérifier</button>
                                </div>
                                <div id="promoFeedback" class="mt-1" style="font-size:0.85rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Block -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5>Récapitulatif</h5>
                    </div>
                    <div class="panel-card-body">
                        <div id="recapContent" style="font-size:0.9rem;">
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Événement</span>
                                <span class="fw-semibold" id="recapEvent">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Statut</span>
                                <span class="fw-semibold" id="recapStatut">Externe</span>
                            </div>
                            <div class="py-2" style="border-bottom:1px solid #f5f5f5;" id="recapBillets">
                                <div id="recapLignes"><span class="text-muted">Aucun billet</span></div>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;" id="recapQuantiteRow">
                                <span class="text-muted">Quantité</span>
                                <span class="fw-semibold" id="recapQuantite">1</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Moyen de paiement</span>
                                <span class="fw-semibold" id="recapPaiement">Espèces</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 d-none" id="recapPromoRow" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Code promo</span>
                                <span class="fw-semibold" id="recapPromo" style="color: var(--vert);">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-3 mt-1" style="border-top:2px solid #f0f0f0;">
                                <span class="fw-bold" style="font-size:1rem;" id="recapTotalLabel">Total à encaisser</span>
                                <span class="fw-bold" style="font-size:1.2rem; color: var(--vert);" id="recapTotal">0 FCFA</span>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold text-white mt-3" id="btnSubmit" style="background: var(--vert); border: none; border-radius: 8px; transition: background 0.2s ease;" disabled>
                            <i class="bi bi-check-circle me-2"></i><span id="btnLabel">Enregistrer la vente</span>
                        </button>
                        <p class="text-center mt-2 mb-0" style="font-size:0.82rem;">
                            <span id="submitInfo" class="text-muted"><i class="bi bi-whatsapp me-1" style="color: #25D366;"></i>Le billet QR sera envoyé par mail</span>
                        </p>
                        <div id="digitalInfo" class="alert alert-info py-2 px-3 mt-2 mb-0 d-none" style="font-size:0.82rem; border-radius:8px;">
                            <i class="bi bi-phone me-1"></i> L'acheteur sera redirigé pour payer via son téléphone.
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Today's Sales History -->
        <div class="col-12 col-lg-5">
            <div class="panel-card" style="height: 100%;">
                <div class="panel-card-header">
                    <h5>Ventes manuelles du jour</h5>
                    <span style="font-size:0.82rem; color: var(--gris);">
                        {{ $totalVentesJour }} vente{{ $totalVentesJour > 1 ? 's' : '' }} — {{ number_format($montantVentesJour, 0, ',', ' ') }} FCFA
                    </span>
                </div>
                <div class="panel-card-body" style="padding: 0; max-height: 600px; overflow-y: auto;">
                    @forelse($ventesJour as $vente)
                        <div class="px-3 py-3" style="border-bottom: 1px solid #f5f5f5;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-semibold" style="font-size: 0.92rem;">{{ $vente->nom_acheteur }}</div>
                                <div class="fw-bold" style="font-size: 0.9rem; color: var(--vert);">{{ number_format($vente->montant, 0, ',', ' ') }} FCFA</div>
                            </div>
                            <div class="text-muted" style="font-size: 0.82rem;">
                                {{ $vente->evenement?->titre ?? '—' }} — {{ $vente->nom_tarif }} · {{ \App\Models\Ticket::methodePaiementLabel($vente->methode_paiement) }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--gris); margin-top: 0.25rem;">
                                {{ $vente->date_achat->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5" style="color: var(--gris);">
                            <i class="bi bi-bag d-block mb-2" style="font-size: 2rem;"></i>
                            <small>Aucune vente manuelle aujourd'hui</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal (cash only) -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-body text-center p-4">
                <div class="mb-3" style="font-size: 3rem;">🎫</div>
                <h5 class="fw-bold mb-2">Vente enregistrée !</h5>
                <p class="text-muted mb-3" id="successMessage"></p>
                <div id="successTickets" class="text-start mb-3" style="font-size: 0.85rem;"></div>
                <button type="button" class="btn w-100 py-2 fw-bold text-white" style="background: var(--vert); border: none; border-radius: 8px;" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.vm-billets{display:flex;flex-direction:column;gap:8px}
.vm-section{font-size:.75rem;font-weight:700;color:var(--violet);text-transform:uppercase;letter-spacing:.03em;margin-top:6px}
.vm-ligne{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:12px;align-items:center;border:1.5px solid #e6e3eb;border-radius:12px;padding:10px 12px;background:#fff}
.vm-ligne.is-on{border-color:var(--violet);background:#faf7fc}
.vm-ligne.is-off{opacity:.6}
.vm-step{display:inline-flex;align-items:center;border:1.5px solid #d9d4df;border-radius:10px;overflow:hidden}
.vm-step button{width:44px;height:44px;border:0;background:#fff;color:var(--violet);font-size:1.2rem}
.vm-step button:disabled{color:#c9c3d1}
.vm-step span{min-width:28px;text-align:center;font-weight:700}
@media (max-width:575.98px){.vm-ligne{grid-template-columns:minmax(0,1fr) auto}.vm-ligne .vm-step{grid-column:1 / -1;justify-self:end}}
</style>
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('venteForm');
    const eventSelect = document.getElementById('evenement_id');
    const listeBillets = document.getElementById('listeBillets');
    const quantiteInput = document.getElementById('quantite');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const btnSubmit = document.getElementById('btnSubmit');
    const methodeSelect = document.getElementById('methode_paiement');
    const emailInput = document.getElementById('email');
    const methodLabels = {
        'especes': 'Espèces',
        'mobile': 'Mobile',
    };
    const fedapayPublicKey = '{{ $publicKey }}';
    const fedapaySandbox = {{ $sandbox ? 'true' : 'false' }};
    const MAX_BILLETS = 20;

    const isUniversitaire = {{ auth()->user()->type === 'universitaire' ? 'true' : 'false' }};
    let isFreeEvent = false;
    let eventType = 'spectacle';
    let especesActivees = true;
    // Panier : billets chargés pour l'événement et quantités choisies (tarif_id => quantité)
    let billets = [];
    let panier = {};
    // Code promo appliqué : réduction unitaire sur un tarif du panier
    let promoCodeApplique = '';
    let promoTarifId = null;
    let promoReduction = 0;

    function escapeHtml(v) {
        return String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function numberFormat(n) {
        return new Intl.NumberFormat('fr-FR').format(n);
    }

    const nombreBillets = () => Object.values(panier).reduce((s, q) => s + q, 0);

    function toggleFieldsPayants(hide) {
        document.getElementById('fieldStatut').style.display = (hide || !isUniversitaire) ? 'none' : '';
        document.getElementById('fieldTarif').style.display = hide ? 'none' : '';
        document.getElementById('fieldPaiement').style.display = hide ? 'none' : '';
        document.getElementById('fieldPromo').style.display = hide ? 'none' : '';
        document.getElementById('fieldQuantite').style.display = hide ? '' : 'none';
        document.getElementById('recapBillets').style.display = hide ? 'none' : '';
        document.getElementById('recapQuantiteRow').style.display = hide ? '' : 'none';

        if (hide) {
            document.getElementById('recapTotalLabel').textContent = eventType === 'spectacle' ? 'Billet' : 'Inscription';
            document.getElementById('recapTotal').textContent = 'Gratuite';
            document.getElementById('recapStatut').closest('.d-flex').style.display = 'none';
            document.getElementById('recapPaiement').closest('.d-flex').style.display = 'none';
            document.getElementById('submitInfo').innerHTML = '<i class="bi bi-whatsapp me-1" style="color: #25D366;"></i>Le billet QR sera envoyé par mail';
            document.getElementById('digitalInfo').classList.add('d-none');
            document.getElementById('btnLabel').textContent = 'Enregistrer';
        } else {
            document.getElementById('recapTotalLabel').textContent = 'Total à encaisser';
            document.getElementById('recapTotal').textContent = '0 FCFA';
            document.getElementById('recapStatut').closest('.d-flex').style.display = '';
            document.getElementById('recapPaiement').closest('.d-flex').style.display = '';
        }
    }

    function isMobile() {
        return methodeSelect.value === 'mobile';
    }

    function updateUI() {
        const mobile = isMobile();

        document.getElementById('btnLabel').textContent = mobile ? 'Payer via FedaPay' : 'Enregistrer la vente';

        document.getElementById('submitInfo').innerHTML = mobile
            ? '<i class="bi bi-phone me-1"></i> L\'acheteur paiera via FedaPay (Mobile Money)'
            : '<i class="bi bi-whatsapp me-1" style="color: #25D366;"></i>Le billet QR sera envoyé par mail';

        document.getElementById('digitalInfo').classList.toggle('d-none', !mobile);
    }

    // Une ligne par billet : jours dans l'ordre, puis les packs.
    function rendreBillets() {
        if (!billets.length) {
            listeBillets.innerHTML = '<div class="text-muted small">Aucun billet en vente pour cet événement.</div>';
            return;
        }
        let html = '';
        let packsAffiches = false;
        billets.forEach((b) => {
            if (b.pack && !packsAffiches) {
                html += '<div class="vm-section">Packs</div>';
                packsAffiches = true;
            }
            const q = panier[b.id] || 0;
            const epuise = b.restantes !== null && b.restantes <= 0;
            const prix = b.prix > 0 ? numberFormat(b.prix) + ' F' + (b.pack ? ' / ' + b.nb_jours + ' jours' : '') : 'Gratuit';
            html += '<div class="vm-ligne' + (q > 0 ? ' is-on' : '') + (epuise ? ' is-off' : '') + '" data-tarif="' + b.id + '">'
                + '<div><div class="fw-semibold">' + escapeHtml(b.nom) + '</div>'
                + (b.jour_date ? '<small class="text-muted">' + escapeHtml(b.jour_date) + '</small>' : '') + '</div>'
                + '<div class="text-end"><div class="fw-bold" style="color:var(--violet);">' + prix + '</div>'
                + '<small class="' + (epuise ? 'text-danger fw-semibold' : 'text-muted') + '">' + (epuise ? 'Complet' : (b.restantes === null ? '' : b.restantes + ' restantes')) + '</small></div>'
                + (epuise ? '<span></span>' : '<div class="vm-step"><button type="button" data-moins aria-label="Retirer un billet ' + escapeHtml(b.nom) + '"' + (q <= 0 ? ' disabled' : '') + '>−</button>'
                    + '<span>' + q + '</span>'
                    + '<button type="button" data-plus aria-label="Ajouter un billet ' + escapeHtml(b.nom) + '"' + (q >= b.restantes || nombreBillets() >= MAX_BILLETS ? ' disabled' : '') + '>+</button></div>')
                + '</div>';
        });
        listeBillets.innerHTML = html;
    }

    listeBillets.addEventListener('click', function (e) {
        const bouton = e.target.closest('button');
        if (!bouton) return;
        const id = bouton.closest('[data-tarif]').dataset.tarif;
        const billet = billets.find((b) => String(b.id) === id);
        if (!billet) return;
        const q = panier[id] || 0;
        if (bouton.hasAttribute('data-plus') && q < billet.restantes && nombreBillets() < MAX_BILLETS) panier[id] = q + 1;
        if (bouton.hasAttribute('data-moins') && q > 0) panier[id] = q - 1;
        if (!panier[id]) delete panier[id];
        if (promoTarifId && !panier[promoTarifId]) resetPromo();
        rendreBillets();
        updateRecap();
    });

    function updateRecap() {
        const eventOption = eventSelect.options[eventSelect.selectedIndex];

        document.getElementById('recapEvent').textContent = eventOption.text || '—';
        document.getElementById('recapQuantite').textContent = quantiteInput.value;

        const nomOk = document.getElementById('nom_acheteur').value.trim() !== '';

        if (isFreeEvent) {
            btnSubmit.disabled = !(eventSelect.value && nomOk);
            return;
        }

        document.getElementById('recapStatut').textContent = '—';
        document.getElementById('recapPaiement').textContent = methodLabels[methodeSelect.value] || '—';

        let total = 0;
        const lignes = billets.filter((b) => panier[b.id]).map((b) => {
            const q = panier[b.id];
            const unitaire = Math.max(0, b.prix - (String(b.id) === String(promoTarifId) ? promoReduction : 0));
            total += unitaire * q;
            return '<div class="d-flex justify-content-between"><span>' + q + ' × ' + escapeHtml(b.nom) + '</span><span class="fw-semibold">' + (unitaire * q > 0 ? numberFormat(unitaire * q) + ' FCFA' : 'Gratuit') + '</span></div>';
        });
        document.getElementById('recapLignes').innerHTML = lignes.length ? lignes.join('') : '<span class="text-muted">Aucun billet</span>';
        document.getElementById('recapTotal').textContent = numberFormat(total) + ' FCFA';

        const promoRow = document.getElementById('recapPromoRow');
        const promoEl = document.getElementById('recapPromo');
        if (promoCodeApplique && promoReduction > 0) {
            promoEl.textContent = promoCodeApplique + ' (−' + numberFormat(promoReduction) + ' FCFA / billet)';
            promoRow.classList.remove('d-none');
        } else {
            promoRow.classList.add('d-none');
        }

        const emailOk = emailInput.value.trim() !== '';
        btnSubmit.disabled = !(eventSelect.value && lignes.length && nomOk && emailOk);
    }

    function resetPromo() {
        promoReduction = 0;
        promoCodeApplique = '';
        promoTarifId = null;
        const feedback = document.getElementById('promoFeedback');
        feedback.className = 'mt-1';
        feedback.style.color = '';
        feedback.textContent = '';
        document.getElementById('recapPromoRow').classList.add('d-none');
        updateRecap();
    }

    // Le code promo vise un tarif : on l'essaie sur chaque billet du panier.
    async function verifierPromo() {
        const codeInput = document.getElementById('code_promo');
        const code = codeInput.value.trim();
        const feedback = document.getElementById('promoFeedback');

        if (!code) {
            resetPromo();
            return;
        }

        const eventId = eventSelect.value;
        const tarifIds = Object.keys(panier);
        if (!eventId || !tarifIds.length || isFreeEvent) {
            feedback.className = 'mt-1 text-danger';
            feedback.style.color = '';
            feedback.textContent = 'Ajoutez d\'abord des billets au panier.';
            return;
        }

        feedback.className = 'mt-1 text-muted';
        feedback.style.color = '';
        feedback.textContent = 'Vérification…';

        let derniereErreur = 'Code promo invalide.';
        try {
            for (const tarifId of tarifIds) {
                const r = await fetch('{{ route('ventes-manuelles.verifier-code-promo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ evenement_id: eventId, tarif_id: tarifId, code: code }),
                });
                const data = await r.json();
                if (data.valide) {
                    promoCodeApplique = data.code;
                    promoReduction = data.reduction;
                    promoTarifId = tarifId;
                    codeInput.value = data.code;
                    const billet = billets.find((b) => String(b.id) === String(tarifId));
                    feedback.className = 'mt-1';
                    feedback.style.color = 'var(--vert)';
                    feedback.textContent = 'Code promo appliqué sur « ' + (billet ? billet.nom : '') + ' » : −' + numberFormat(data.reduction) + ' FCFA par billet';
                    updateRecap();
                    return;
                }
                derniereErreur = data.erreur || derniereErreur;
            }
            resetPromo();
            feedback.className = 'mt-1 text-danger';
            feedback.textContent = derniereErreur;
        } catch (e) {
            feedback.className = 'mt-1 text-danger';
            feedback.style.color = '';
            feedback.textContent = 'Erreur de vérification. Réessayez.';
        }
    }

    function loadTarifs() {
        const eventId = eventSelect.value;

        panier = {};
        billets = [];
        resetPromo();

        if (!eventId) {
            listeBillets.innerHTML = '<div class="text-muted small">Sélectionnez d\'abord un événement.</div>';
            isFreeEvent = false;
            toggleFieldsPayants(false);
            updateRecap();
            return;
        }

        const eventOption = eventSelect.options[eventSelect.selectedIndex];
        eventType = eventOption.dataset.type || 'spectacle';
        isFreeEvent = eventOption.dataset.gratuit === '1';

        if (isFreeEvent) {
            toggleFieldsPayants(true);
            updateRecap();
            return;
        }

        toggleFieldsPayants(false);
        listeBillets.innerHTML = '<div class="text-muted small">Chargement…</div>';

        fetch('{{ route('ventes-manuelles.tarifs') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ evenement_id: eventId }),
        })
        .then(r => r.json())
        .then(data => {
            billets = data.tarifs || [];
            rendreBillets();

            // Gestion du seuil espèces
            especesActivees = data.especes_activees;
            const optEspeces = document.getElementById('optEspeces');
            const alertEspeces = document.getElementById('alertEspeces');

            if (!data.gratuit && !data.especes_activees) {
                optEspeces.disabled = true;
                optEspeces.textContent = 'Espèces (bloquées — seuil non atteint)';
                methodeSelect.value = 'mobile';
                alertEspeces.style.display = 'block';
                alertEspeces.innerHTML = '<div class="alert alert-warning py-1 px-2 mb-0" style="font-size:0.78rem;border-radius:6px;">'
                    + '<i class="bi bi-exclamation-triangle me-1"></i>'
                    + 'Ventes espèces bloquées. <strong>' + data.vendus_en_ligne + '/' + data.seuil + '</strong> tickets vendus en ligne (seuil : 15 %).'
                    + '</div>';
            } else {
                optEspeces.disabled = false;
                optEspeces.textContent = 'Espèces';
                alertEspeces.style.display = 'none';
                alertEspeces.innerHTML = '';
            }
            updateUI();
            updateRecap();
        })
        .catch(() => {
            listeBillets.innerHTML = '<div class="text-danger small">Erreur de chargement des billets.</div>';
        });
    }

    eventSelect.addEventListener('change', loadTarifs);

    document.getElementById('nom_acheteur').addEventListener('input', updateRecap);
    emailInput.addEventListener('input', updateRecap);

    methodeSelect.addEventListener('change', function() {
        updateUI();
        updateRecap();
    });

    document.getElementById('btnVerifierPromo').addEventListener('click', verifierPromo);
    document.getElementById('code_promo').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verifierPromo();
        }
    });
    document.getElementById('code_promo').addEventListener('input', function() {
        if (promoCodeApplique && this.value.trim().toUpperCase() !== promoCodeApplique) {
            resetPromo();
        }
    });

    // Quantité : uniquement pour un événement gratuit (billet unique)
    qtyMinus.addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v > 1) { quantiteInput.value = v - 1; updateRecap(); }
    });

    qtyPlus.addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v < MAX_BILLETS) { quantiteInput.value = v + 1; updateRecap(); }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {};
        formData.forEach((val, key) => data[key] = val);

        if (isFreeEvent) {
            delete data.methode_paiement;
        } else {
            delete data.quantite;
            data.lignes = panier;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Traitement…';

        fetch('{{ route('ventes-manuelles.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json().catch(() => null).then(body => ({ ok: r.ok, status: r.status, body })))
        .then(({ ok, status, body }) => {
            if (body && body.ticket && isMobile()) {
                const t = body.ticket;
                const nameParts = t.nom_acheteur.trim().split(' ');
                const firstname = nameParts.slice(0, -1).join(' ') || nameParts[0] || 'Client';
                const lastname = nameParts.length > 1 ? nameParts[nameParts.length - 1] : '';
                const callbackUrl = '{{ route('paiement.callback') }}?ticket=' + t.id + '&source=vente_manuelle';

                if (typeof FedaPay === 'undefined') {
                    alert('Le widget FedaPay n\'a pas pu être chargé. Veuillez réessayer.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Payer via FedaPay';
                    return;
                }

                let fedapayBtn = document.getElementById('fedaPayTrigger');
                if (!fedapayBtn) {
                    fedapayBtn = document.createElement('button');
                    fedapayBtn.id = 'fedaPayTrigger';
                    fedapayBtn.style.display = 'none';
                    document.body.appendChild(fedapayBtn);
                }

                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ouverture de FedaPay…';
                btnSubmit.disabled = true;

                FedaPay.init(fedapayBtn, {
                    public_key: fedapayPublicKey,
                    environment: fedapaySandbox ? 'sandbox' : 'live',
                    transaction: {
                        amount: t.montant,
                        description: 'Ticket - ' + t.evenement_titre,
                        external_id: String(t.id),
                        custom_metadata: {
                            ticket_id: String(t.id),
                            group_transaction_id: t.transaction_id || ''
                        }
                    },
                    customer: {
                        email: t.email_acheteur,
                        firstname: firstname,
                        lastname: lastname
                    },
                    currency: { iso: 'XOF' },
                    onComplete: function(data) {
                        if (data.transaction && data.transaction.id) {
                            var pm = data.transaction.payment_method || 'mobile_money';
                            var ph = data.transaction.phone || '';
                            window.location.href = callbackUrl + '&id=' + data.transaction.id + '&status=' + (data.transaction.status || 'approved') + '&payment_method=' + pm + '&phone=' + ph;
                        } else {
                            alert('Paiement annulé ou fermé. Vous pouvez réessayer.');
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Payer via FedaPay';
                        }
                    }
                });

                setTimeout(function() { fedapayBtn.click(); }, 300);
                return;
            }
            if (body && body.success) {
                document.getElementById('successMessage').textContent = body.message;
                let ticketsHtml = '<ul class="list-unstyled mb-0">';
                body.tickets.forEach(t => {
                    const reference = t.code_unique || t.transaction_id;
                    const label = t.code_unique ? 'Code' : 'Référence';
                    ticketsHtml += '<li><strong>' + label + ' :</strong> ' + escapeHtml(reference) + ' <span class="text-muted">· ' + escapeHtml(t.nom_tarif || '') + '</span></li>';
                });
                ticketsHtml += '<li class="fw-bold mt-1">' + (body.total > 0 ? 'Total : ' + numberFormat(body.total) + ' FCFA' : 'Gratuit') + '</li></ul>';
                document.getElementById('successTickets').innerHTML = ticketsHtml;
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();

                form.reset();
                quantiteInput.value = 1;
                panier = {};
                billets = [];
                isFreeEvent = false;
                listeBillets.innerHTML = '<div class="text-muted small">Sélectionnez d\'abord un événement.</div>';
                toggleFieldsPayants(false);
                updateRecap();

                setTimeout(() => location.reload(), 2000);
                return;
            }
            if (body && body.errors) {
                const msgs = Object.values(body.errors).flat().join('\n');
                alert(msgs);
            } else {
                alert('Une erreur est survenue (code ' + status + '). Vérifiez votre saisie et réessayez.');
            }
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + (isMobile() ? 'Payer via FedaPay' : 'Enregistrer la vente');
        });
    });

    toggleFieldsPayants(false);
    updateUI();
    updateRecap();
});
</script>
@endsection
