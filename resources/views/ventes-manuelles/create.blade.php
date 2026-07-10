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
                                    <option value="{{ $evt->id }}" data-tarif-min="{{ $evt->tarifs->min('prix') }}" data-gratuit="{{ $evt->gratuit ? '1' : '0' }}">{{ $evt->titre }} — {{ $evt->date_event->isoFormat('D MMM YYYY') }}</option>
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
                                <label for="telephone" class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="+229 62 83 66 29" required>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-muted fw-normal email-optional">(optionnel)</span><span class="text-danger email-required d-none">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="adja@email.com">
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
                            <div class="col-md-6" id="fieldStatut">
                                <label class="form-label fw-semibold">Statut de l'acheteur <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="categorie" id="cat_externe" value="externe" checked>
                                        <label class="form-check-label" for="cat_externe">Externe</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="categorie" id="cat_etudiant" value="etudiant">
                                        <label class="form-check-label" for="cat_etudiant">Étudiant</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" id="fieldTarif">
                                <label for="tarif_id" class="form-label fw-semibold">Tarif <span class="text-danger">*</span></label>
                                <select class="form-select" id="tarif_id" name="tarif_id" required>
                                    <option value="">— Sélectionnez d'abord un événement —</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quantite" class="form-label fw-semibold">Quantité</label>
                                <div class="input-group" style="max-width: 160px;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyMinus" style="border-radius: 6px 0 0 6px;">−</button>
                                    <input type="number" class="form-control text-center" id="quantite" name="quantite" value="1" min="1" max="20" style="border-left:0;border-right:0;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty" id="qtyPlus" style="border-radius: 0 6px 6px 0;">+</button>
                                </div>
                            </div>
                            <div class="col-md-6" id="fieldPaiement">
                                <label for="methode_paiement" class="form-label fw-semibold">Moyen de paiement <span class="text-danger">*</span></label>
                                <select class="form-select" id="methode_paiement" name="methode_paiement" required>
                                    <option value="especes">Espèces</option>
                                    <option value="mobile">Mobile</option>
                                </select>
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
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Tarif unitaire</span>
                                <span class="fw-semibold" id="recapTarif">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Quantité</span>
                                <span class="fw-semibold" id="recapQuantite">1</span>
                            </div>
                            <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #f5f5f5;">
                                <span class="text-muted">Moyen de paiement</span>
                                <span class="fw-semibold" id="recapPaiement">Espèces</span>
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
                                {{ $vente->evenement?->titre ?? '—' }} — {{ ucfirst($vente->categorie) }} · {{ \App\Models\Ticket::methodePaiementLabel($vente->methode_paiement) }}
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
<script src="https://cdn.fedapay.com/checkout.js?v=1.1.3"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('venteForm');
    const eventSelect = document.getElementById('evenement_id');
    const tarifSelect = document.getElementById('tarif_id');
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

    const isUniversitaire = {{ auth()->user()->type === 'universitaire' ? 'true' : 'false' }};
    let tarifUnitaire = 0;
    let isFreeEvent = false;

    function toggleFieldsPayants(hide) {
        document.getElementById('fieldStatut').style.display = (hide || !isUniversitaire) ? 'none' : '';
        document.getElementById('fieldTarif').style.display = hide ? 'none' : '';
        document.getElementById('fieldPaiement').style.display = hide ? 'none' : '';
        document.getElementById('sectionTarif').style.display = hide ? 'none' : '';

        if (hide) {
            document.getElementById('recapTotalLabel').textContent = 'Inscription';
            document.getElementById('recapTotal').textContent = 'Gratuite';
            document.getElementById('recapStatut').closest('.d-flex').style.display = 'none';
            document.getElementById('recapTarif').closest('.d-flex').style.display = 'none';
            document.getElementById('recapPaiement').closest('.d-flex').style.display = 'none';
            document.getElementById('submitInfo').innerHTML = '<i class="bi bi-whatsapp me-1" style="color: #25D366;"></i>Le billet QR sera envoyé par mail';
            document.getElementById('digitalInfo').classList.add('d-none');
            document.getElementById('btnLabel').textContent = 'Enregistrer';
        } else {
            document.getElementById('recapTotalLabel').textContent = 'Total à encaisser';
            document.getElementById('recapTotal').textContent = '0 FCFA';
            document.getElementById('recapStatut').closest('.d-flex').style.display = '';
            document.getElementById('recapTarif').closest('.d-flex').style.display = '';
            document.getElementById('recapPaiement').closest('.d-flex').style.display = '';
        }
    }

    function isMobile() {
        return methodeSelect.value === 'mobile';
    }

    function updateUI() {
        const mobile = isMobile();

        document.querySelector('.email-optional').classList.toggle('d-none', mobile);
        document.querySelector('.email-required').classList.toggle('d-none', !mobile);

        document.getElementById('btnLabel').textContent = mobile ? 'Payer via FedaPay' : 'Enregistrer la vente';

        document.getElementById('submitInfo').innerHTML = mobile
            ? '<i class="bi bi-phone me-1"></i> L\'acheteur paiera via FedaPay (Mobile Money)'
            : '<i class="bi bi-whatsapp me-1" style="color: #25D366;"></i>Le billet QR sera envoyé par mail';

        document.getElementById('digitalInfo').classList.toggle('d-none', !mobile);
    }

    function updateRecap() {
        const eventOption = eventSelect.options[eventSelect.selectedIndex];

        document.getElementById('recapEvent').textContent = eventOption.text || '—';
        document.getElementById('recapQuantite').textContent = quantiteInput.value;

        if (isFreeEvent) {
            const nomOk = document.getElementById('nom_acheteur').value.trim() !== '';
            const telOk = document.getElementById('telephone').value.trim() !== '';
            btnSubmit.disabled = !(eventSelect.value && nomOk && telOk);
            return;
        }

        const tarifOption = tarifSelect.options[tarifSelect.selectedIndex];
        const catRadio = document.querySelector('input[name="categorie"]:checked');

        document.getElementById('recapStatut').textContent = catRadio ? catRadio.nextElementSibling.textContent : '—';
        document.getElementById('recapTarif').textContent = tarifUnitaire > 0 ? numberFormat(tarifUnitaire) + ' FCFA' : '—';
        document.getElementById('recapPaiement').textContent = methodLabels[methodeSelect.value] || '—';

        const total = tarifUnitaire * parseInt(quantiteInput.value || 0);
        document.getElementById('recapTotal').textContent = numberFormat(total) + ' FCFA';

        const nomOk = document.getElementById('nom_acheteur').value.trim() !== '';
        const telOk = document.getElementById('telephone').value.trim() !== '';
        const emailOk = !isMobile() || emailInput.value.trim() !== '';
        btnSubmit.disabled = !(eventSelect.value && tarifSelect.value && nomOk && telOk && emailOk);
    }

    function numberFormat(n) {
        return new Intl.NumberFormat('fr-FR').format(n);
    }

    function loadTarifs() {
        const eventId = eventSelect.value;
        const catRadio = document.querySelector('input[name="categorie"]:checked');
        const cat = catRadio ? catRadio.value : 'externe';

        tarifSelect.innerHTML = '<option value="">Chargement…</option>';

        if (!eventId) {
            tarifSelect.innerHTML = '<option value="">— Sélectionnez d\'abord un événement —</option>';
            tarifUnitaire = 0;
            isFreeEvent = false;
            toggleFieldsPayants(false);
            updateRecap();
            return;
        }

        const eventOption = eventSelect.options[eventSelect.selectedIndex];
        isFreeEvent = eventOption.dataset.gratuit === '1';

        if (isFreeEvent) {
            toggleFieldsPayants(true);
            tarifUnitaire = 0;
            updateRecap();
            return;
        }

        toggleFieldsPayants(false);

        const body = { evenement_id: eventId };
        if (isUniversitaire) {
            body.categorie = cat;
        }

        fetch('{{ route('ventes-manuelles.tarifs') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(body),
        })
        .then(r => r.json())
        .then(data => {
            tarifSelect.innerHTML = '<option value="">Choisir un tarif —</option>';
            data.tarifs.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.type.toUpperCase() + ' — ' + numberFormat(t.prix) + ' FCFA';
                opt.dataset.prix = t.prix;
                tarifSelect.appendChild(opt);
            });
        })
        .catch(() => {
            tarifSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
    }

    eventSelect.addEventListener('change', loadTarifs);

    document.querySelectorAll('input[name="categorie"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (eventSelect.value) loadTarifs();
            updateRecap();
        });
    });

    tarifSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        tarifUnitaire = parseFloat(opt.dataset.prix || 0);
        updateRecap();
    });

    document.getElementById('nom_acheteur').addEventListener('input', updateRecap);
    document.getElementById('telephone').addEventListener('input', updateRecap);
    emailInput.addEventListener('input', updateRecap);

    methodeSelect.addEventListener('change', function() {
        updateUI();
        updateRecap();
    });

    qtyMinus.addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v > 1) { quantiteInput.value = v - 1; updateRecap(); }
    });

    qtyPlus.addEventListener('click', function() {
        const v = parseInt(quantiteInput.value);
        if (v < 20) { quantiteInput.value = v + 1; updateRecap(); }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {};
        formData.forEach((val, key) => data[key] = val);

        if (isFreeEvent) {
            delete data.tarif_id;
            delete data.categorie;
            delete data.methode_paiement;
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
                        description: 'Ticket - ' + t.evenement_titre
                    },
                    customer: {
                        email: t.email_acheteur,
                        firstname: firstname,
                        lastname: lastname
                    },
                    currency: { iso: 'XOF' },
                    onComplete: function(data) {
                        if (data.reason === 'CHECKOUT COMPLETE' && data.transaction && data.transaction.id) {
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
                    ticketsHtml += '<li><strong>Code :</strong> ' + t.code_unique + '</li>';
                });
                if (body.total > 0) {
                    ticketsHtml += '</li><li class="fw-bold mt-1">Total : ' + numberFormat(body.total) + ' FCFA</li></ul>';
                } else {
                    ticketsHtml += '</li><li class="fw-bold mt-1">Gratuit</li></ul>';
                }
                document.getElementById('successTickets').innerHTML = ticketsHtml;
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();

                form.reset();
                document.getElementById('quantite').value = 1;
                document.getElementById('cat_externe').checked = true;
                tarifUnitaire = 0;
                isFreeEvent = false;
                tarifSelect.innerHTML = '<option value="">— Sélectionnez d\'abord un événement —</option>';
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

    updateUI();
    updateRecap();
});
</script>
@endsection
