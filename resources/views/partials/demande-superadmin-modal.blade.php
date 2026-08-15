@php
    $evenementsPourDemande = Auth::user()->evenements()
        ->with(['tarifs' => fn ($q) => $q->where('statut', 'actif')->orderBy('prix')])
        ->orderBy('date_event')
        ->get();
@endphp

{{-- Modal : demander au super admin --}}
<div class="modal fade" id="demandeSuperadminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <form action="{{ route('demande-superadmin.store') }}" method="POST" id="demandeSuperadminForm">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #f0eef2;padding:1rem 1.25rem;">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700;color:var(--sombre);">
                        <i class="bi bi-headset me-1" style="color:#7B3FA0;"></i> Contacter PaxEvent
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.25rem;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;">Motif de la demande</label>
                        <select name="objet" id="demande_objet" class="form-select form-select-sm" required>
                            <option value="">-- Choisir un motif --</option>
                            <option value="ticket_physique">Ticket physique (QR Code)</option>
                            <option value="reduction_commission">Réduction Commission</option>
                            <option value="augmentation_agents">Augmentation des agents</option>
                            <option value="evenement_a_la_une">Événement à la une</option>
                            <option value="probleme_technique">Problème technique</option>
                        </select>
                    </div>

                    <div class="mb-3" id="demande_evenement_group" style="display:none;">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;">Événement concerné</label>
                        <select name="evenement_id" id="demande_evenement" class="form-select form-select-sm">
                            <option value="">-- Choisir un événement --</option>
                            @foreach($evenementsPourDemande as $evt)
                                <option value="{{ $evt->id }}" data-tarifs='@json($evt->tarifs->map(fn($t) => ["id" => $t->id, "nom" => $t->nom]))'>{{ $evt->titre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Quantités par tarif (ticket physique) --}}
                    <div class="mb-3" id="demande_quantites_group" style="display:none;">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;">Quantités par tarif</label>
                        <div id="demande_quantites" class="vstack gap-2"></div>
                    </div>

                    {{-- Pourcentage de commission (réduction commission) --}}
                    <div class="mb-3" id="demande_commission_group" style="display:none;">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;">Pourcentage demandé (%)</label>
                        <input type="number" name="commission_pourcentage" id="demande_commission" class="form-control form-control-sm" min="0" max="100" step="0.01" placeholder="Ex : 5">
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;">Message</label>
                        <textarea name="message" id="demande_message" class="form-control form-control-sm" rows="3" maxlength="2000" placeholder="Décrivez votre demande..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0eef2;padding:0.75rem 1.25rem;">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="border:1px solid #e0dde3;border-radius:8px;">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-send me-1"></i> Envoyer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const demandeObjet = document.getElementById('demande_objet');
    const demandeEvenementGroup = document.getElementById('demande_evenement_group');
    const demandeEvenement = document.getElementById('demande_evenement');
    const demandeQuantitesGroup = document.getElementById('demande_quantites_group');
    const demandeQuantites = document.getElementById('demande_quantites');
    const demandeCommissionGroup = document.getElementById('demande_commission_group');

    if (!demandeObjet) return;

    const OBJET_EVENEMENT = ['ticket_physique', 'reduction_commission', 'augmentation_agents', 'evenement_a_la_une'];

    function resetDemande() {
        demandeEvenementGroup.style.display = 'none';
        demandeQuantitesGroup.style.display = 'none';
        demandeCommissionGroup.style.display = 'none';
        demandeEvenement.value = '';
        demandeQuantites.innerHTML = '';
    }

    demandeObjet.addEventListener('change', function () {
        resetDemande();
        const objet = this.value;
        if (!objet) return;

        if (OBJET_EVENEMENT.includes(objet)) {
            demandeEvenementGroup.style.display = '';
            demandeEvenement.required = true;
        } else {
            demandeEvenement.required = false;
        }

        if (objet === 'reduction_commission') {
            demandeCommissionGroup.style.display = '';
        }
    });

    demandeEvenement.addEventListener('change', function () {
        demandeQuantites.innerHTML = '';
        const objet = demandeObjet.value;
        if (objet !== 'ticket_physique') return;

        const option = this.selectedOptions && this.selectedOptions[0];
        if (!option) return;
        let tarifs = [];
        try { tarifs = JSON.parse(option.dataset.tarifs || '[]'); } catch (e) {}

        if (!tarifs.length) {
            demandeQuantitesGroup.style.display = 'none';
            return;
        }
        demandeQuantitesGroup.style.display = '';

        tarifs.forEach(t => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between gap-2';
            div.innerHTML = '<span style="font-size:0.8rem;color:#444;flex:1;">' + t.nom + '</span>' +
                '<input type="number" class="form-control form-control-sm" style="width:110px;" ' +
                'name="quantites[' + t.id + ']" min="0" max="5000" step="1" placeholder="Qté">';
            demandeQuantites.appendChild(div);
        });
    });

    const modal = document.getElementById('demandeSuperadminModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            demandeObjet.value = '';
            resetDemande();
            const msg = document.getElementById('demande_message');
            msg.value = '';
            msg.required = true;
        });
    }
});
</script>