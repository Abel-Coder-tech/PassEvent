@php
    $evenementsPourDemande = Auth::user()->evenements()
        ->with(['tarifs' => fn ($q) => $q->where('statut', 'actif')->orderBy('prix')])
        ->where('date_event', '>=', now())
        ->orderBy('date_event')
        ->get();

    // Réouverture automatique du modal si le serveur a renvoyé des erreurs pour cette demande
    $restaurationDemande = $errors->any() && old('objet');
@endphp

<style>
    .champ-erreur { display: block; color: #dc3545; font-size: 0.74rem; margin-top: 0.25rem; }
    .champ-erreur:empty { display: none; }
    .demande-badge-etape { font-size: 0.66rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 999px; background: #f3ecfa; color: #7B3FA0; vertical-align: middle; }
</style>

<script>
    window.DEMANDE_RESTORE = {!! $restaurationDemande ? json_encode([
        'objet' => old('objet'),
        'evenement_id' => old('evenement_id'),
        'quantites' => (array) old('quantites', []),
        'message' => old('message'),
        'format' => old('format'),
        'largeur' => old('largeur_personnalisee'),
        'hauteur' => old('hauteur_personnalisee'),
        'commission' => old('commission_pourcentage'),
        'persona' => old('cible_persona'),
        'canal' => old('canal_souhaite'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) : 'null' !!};
</script>

{{-- Modal : demander au super admin --}}
<div class="modal fade" id="demandeSuperadminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;">
            <form action="{{ route('demande-superadmin.store') }}" method="POST" id="demandeSuperadminForm" novalidate>
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #f0eef2;padding:1rem 1.25rem;">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700;color:var(--sombre);">
                        <i class="bi bi-headset me-1" style="color:#7B3FA0;"></i> Contacter PaxEvent
                        <span class="badge demande-badge-etape ms-2" id="demandeBadgeEtape" style="display:none;">Étape 1 / 2</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.25rem;">
                    {{-- Étape 1 : formulaire --}}
                    <div id="demandeEtape1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Motif de la demande</label>
                            <select name="objet" id="demande_objet" class="form-select form-select-sm" required>
                                <option value="">-- Choisir un motif --</option>
                                <option value="ticket_physique">Ticket physique (QR Code)</option>
                                <option value="reduction_commission">Réduction Commission</option>
                                <option value="augmentation_agents">Augmentation des agents</option>
                                <option value="evenement_a_la_une">Événement à la une</option>
                                <option value="booster_promouvoir">Booster ou promouvoir un événement</option>
                                <option value="probleme_technique">Problème technique</option>
                            </select>
                            <div class="champ-erreur" id="err_objet">@error('objet'){{ $message }}@enderror</div>
                        </div>

                        <div class="mb-3" id="demande_evenement_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Événement concerné</label>
                            <select name="evenement_id" id="demande_evenement" class="form-select form-select-sm">
                                <option value="">-- Choisir un événement --</option>
                                @foreach($evenementsPourDemande as $evt)
                                    <option value="{{ $evt->id }}" data-tarifs='@json($evt->tarifs->map(fn($t) => ["id" => $t->id, "nom" => $t->nom]))'>{{ $evt->titre }}</option>
                                @endforeach
                            </select>
                            <div class="champ-erreur" id="err_evenement_id">@error('evenement_id'){{ $message }}@enderror</div>
                        </div>

                        {{-- Quantités par tarif (ticket physique) --}}
                        <div class="mb-3" id="demande_quantites_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Quantités par tarif</label>
                            <div id="demande_quantites" class="vstack gap-2"></div>
                            <div class="champ-erreur" id="err_quantites">@error('quantites'){{ $message }}@enderror</div>
                        </div>

                        {{-- Template du ticket (demande de QR codes) --}}
                        <div class="mb-3" id="demande_template_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">
                                Template de votre ticket <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="template_image" id="demande_template_image" class="form-control form-control-sm" accept="image/png">
                            <div class="champ-erreur" id="err_template_image">@error('template_image'){{ $message }}@enderror</div>
                            <div class="form-text" style="font-size:0.72rem;">
                                Veuillez importer une image au format PNG (fond, logo, texte), 10 Mo maximum.
                                Le QR code est positionné par notre équipe.
                            </div>

                            <div class="row g-2 mt-1">
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Format du ticket</label>
                                    <select name="format" id="demande_format" class="form-select form-select-sm">
                                        <option value="s1">Standard (14×5)</option>
                                        <option value="s2">Standard 2 (14×7)</option>
                                        <option value="v1">VIP (18×7)</option>
                                        <option value="v2">VIP 2 (9,9×7)</option>
                                        <option value="custom">Personnalisé (dimensions sur mesure)</option>
                                    </select>
                                    <div class="champ-erreur" id="err_format">@error('format'){{ $message }}@enderror</div>
                                </div>
                                <div class="col-6" id="demande_largeur_group" style="display:none;">
                                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Largeur (mm)</label>
                                    <input type="number" name="largeur_personnalisee" id="demande_largeur" class="form-control form-control-sm" min="30" max="200">
                                    <div class="champ-erreur" id="err_largeur_personnalisee">@error('largeur_personnalisee'){{ $message }}@enderror</div>
                                </div>
                                <div class="col-6" id="demande_hauteur_group" style="display:none;">
                                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Hauteur (mm)</label>
                                    <input type="number" name="hauteur_personnalisee" id="demande_hauteur" class="form-control form-control-sm" min="30" max="200">
                                    <div class="champ-erreur" id="err_hauteur_personnalisee">@error('hauteur_personnalisee'){{ $message }}@enderror</div>
                                </div>
                            </div>
                        </div>

                        {{-- Pourcentage de commission (réduction commission) --}}
                        <div class="mb-3" id="demande_commission_group" style="display:none;">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Pourcentage demandé (%)</label>
                            <input type="number" name="commission_pourcentage" id="demande_commission" class="form-control form-control-sm" min="0" max="100" step="0.01" placeholder="Ex : 5">
                            <div class="champ-erreur" id="err_commission_pourcentage">@error('commission_pourcentage'){{ $message }}@enderror</div>
                        </div>

                        {{-- Campagne marketing ciblée (booster / promouvoir) --}}
                        <div class="mb-2" id="demande_campagne_group" style="display:none;">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Public ciblé</label>
                                    <select name="cible_persona" id="demande_persona" class="form-select form-select-sm">
                                        <option value="">-- Choisir le public ciblé --</option>
                                        <option value="tous">Tous publics</option>
                                        <option value="jeunes">Étudiants / jeunes</option>
                                        <option value="professionnels">Professionnels</option>
                                        <option value="local">Public local de la ville</option>
                                        <option value="fideles">Fidèles de l'organisateur</option>
                                    </select>
                                    <div class="champ-erreur" id="err_cible_persona">@error('cible_persona'){{ $message }}@enderror</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Canal souhaité</label>
                                    <select name="canal_souhaite" id="demande_canal" class="form-select form-select-sm">
                                        <option value="">-- Choisir le canal --</option>
                                        <option value="site">Mise en avant sur le site (une / bannière)</option>
                                        <option value="newsletter">Newsletter PaxEvent</option>
                                        <option value="reseaux">Réseaux sociaux</option>
                                        <option value="whatsapp">WhatsApp / SMS</option>
                                        <option value="mixte">Mixte</option>
                                    </select>
                                    <div class="champ-erreur" id="err_canal_souhaite">@error('canal_souhaite'){{ $message }}@enderror</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Message <span class="text-muted fw-normal">(facultatif)</span></label>
                            <textarea name="message" id="demande_message" class="form-control form-control-sm" rows="3" maxlength="2000" placeholder="Décrivez votre demande (facultatif)..."></textarea>
                            <div class="champ-erreur" id="err_message">@error('message'){{ $message }}@enderror</div>
                        </div>

                        {{-- Info commission tickets physiques (QR Code) --}}
                        <div id="demande_info_commission" class="mt-2" style="display:none;">
                            <div class="alert alert-info py-2 px-3 mb-0" style="border-radius:8px;font-size:0.78rem;background:#f0f7ff;border:1px solid #d6e6ff;color:#1c5ba8;">
                                <i class="bi bi-info-circle me-1"></i>
                                La commission sur les tickets physiques (QR Code) est de <strong>5 %</strong>.
                                Votre demande est envoyée avec votre template, puis vous êtes redirigé vers le paiement de la commission.
                                Après le paiement, notre équipe positionne les QR codes sur votre template et vous transmet la planche.
                            </div>
                        </div>
                    </div>

                    {{-- Étape 2 : récapitulatif (demande de QR codes uniquement) --}}
                    <div id="demandeEtape2" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold" style="font-size:0.92rem;">
                                <i class="bi bi-clipboard-check me-1" style="color:#7B3FA0;"></i> Récapitulatif
                            </span>
                            <span class="text-muted" style="font-size:0.74rem;">Vérifiez avant de payer</span>
                        </div>

                        <div class="p-3" style="background:#f8f7fa;border:1px solid #eee8f0;border-radius:10px;font-size:0.82rem;">
                            <div class="d-flex justify-content-between gap-3 py-1" style="border-bottom:1px solid #eee8f0;">
                                <span class="text-muted">Motif</span>
                                <span class="fw-semibold text-end" id="recap_objet" style="color:#3d3d45;"></span>
                            </div>
                            <div class="d-flex justify-content-between gap-3 py-1" style="border-bottom:1px solid #eee8f0;">
                                <span class="text-muted">Événement</span>
                                <span class="fw-semibold text-end" id="recap_evenement" style="color:#3d3d45;"></span>
                            </div>
                            <div class="py-1" style="border-bottom:1px solid #eee8f0;">
                                <span class="text-muted d-block mb-1">Quantités</span>
                                <div id="recap_quantites" class="fw-semibold" style="color:#3d3d45;"></div>
                            </div>
                            <div class="d-flex justify-content-between gap-3 py-1" style="border-bottom:1px solid #eee8f0;">
                                <span class="text-muted">Template</span>
                                <span class="fw-semibold text-end text-truncate" id="recap_template" style="color:#3d3d45;max-width:60%;"></span>
                            </div>
                            <div class="d-flex justify-content-between gap-3 py-1" style="border-bottom:1px solid #eee8f0;">
                                <span class="text-muted">Format</span>
                                <span class="fw-semibold text-end" id="recap_format" style="color:#3d3d45;"></span>
                            </div>
                            <div class="py-1">
                                <span class="text-muted d-block mb-1">Message</span>
                                <div id="recap_message" style="white-space:pre-wrap;color:#3d3d45;"></div>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 px-3 mb-0 mt-2" style="border-radius:8px;font-size:0.78rem;background:#f0f7ff;border:1px solid #d6e6ff;color:#1c5ba8;">
                            <i class="bi bi-info-circle me-1"></i>
                            Commission PaxEvent : <strong>5 %</strong> du prix du ticket. Le paiement s'effectue via FedaPay ;
                            vos QR codes sont positionnés par notre équipe après confirmation du paiement.
                        </div>
                    </div>
                </div>

                <div class="modal-footer" id="demandeFooterEtape1" style="border-top:1px solid #f0eef2;padding:0.75rem 1.25rem;">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="border:1px solid #e0dde3;border-radius:8px;">Annuler</button>
                    <button type="submit" class="btn btn-sm" id="demandeBtnEtape1" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-send me-1"></i> Envoyer la demande
                    </button>
                </div>

                <div class="modal-footer" id="demandeFooterEtape2" style="border-top:1px solid #f0eef2;padding:0.75rem 1.25rem;display:none;">
                    <button type="button" class="btn btn-sm" id="demandeBtnPrecedent" style="border:1px solid #e0dde3;border-radius:8px;">
                        <i class="bi bi-arrow-left me-1"></i> Précédent
                    </button>
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="border:1px solid #e0dde3;border-radius:8px;">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background:#7B3FA0;color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-lock me-1"></i> Payer via FedaPay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('demandeSuperadminForm');
    const modal = document.getElementById('demandeSuperadminModal');
    const demandeObjet = document.getElementById('demande_objet');
    const demandeEvenementGroup = document.getElementById('demande_evenement_group');
    const demandeEvenement = document.getElementById('demande_evenement');
    const demandeQuantitesGroup = document.getElementById('demande_quantites_group');
    const demandeQuantites = document.getElementById('demande_quantites');
    const demandeTemplateGroup = document.getElementById('demande_template_group');
    const demandeTemplateImage = document.getElementById('demande_template_image');
    const demandeFormat = document.getElementById('demande_format');
    const demandeLargeurGroup = document.getElementById('demande_largeur_group');
    const demandeHauteurGroup = document.getElementById('demande_hauteur_group');
    const demandeLargeur = document.getElementById('demande_largeur');
    const demandeHauteur = document.getElementById('demande_hauteur');
    const demandeCommissionGroup = document.getElementById('demande_commission_group');
    const demandeInfoCommission = document.getElementById('demande_info_commission');
    const demandeCampagneGroup = document.getElementById('demande_campagne_group');
    const demandeMessage = document.getElementById('demande_message');
    const etape1 = document.getElementById('demandeEtape1');
    const etape2 = document.getElementById('demandeEtape2');
    const footerEtape1 = document.getElementById('demandeFooterEtape1');
    const footerEtape2 = document.getElementById('demandeFooterEtape2');
    const badgeEtape = document.getElementById('demandeBadgeEtape');
    const btnEtape1 = document.getElementById('demandeBtnEtape1');

    if (!demandeObjet || !form) return;

    const OBJET_QR = 'ticket_physique';
    const OBJET_EVENEMENT = ['ticket_physique', 'reduction_commission', 'augmentation_agents', 'evenement_a_la_une', 'booster_promouvoir'];
    const TAILLE_MAX_FICHIER = 10 * 1024 * 1024;

    const ERREURS_CHAMPS = {
        demande_objet: 'err_objet',
        demande_evenement: 'err_evenement_id',
        demande_template_image: 'err_template_image',
        demande_format: 'err_format',
        demande_largeur: 'err_largeur_personnalisee',
        demande_hauteur: 'err_hauteur_personnalisee',
        demande_commission: 'err_commission_pourcentage',
        demande_persona: 'err_cible_persona',
        demande_canal: 'err_canal_souhaite',
        demande_message: 'err_message'
    };

    let etapeCourante = 1;
    let enRestauration = false;

    function erreur(id, texte) {
        const el = document.getElementById(id);
        if (el) el.textContent = texte || '';
    }

    function viderErreurChamp(idChamp) {
        if (enRestauration) return;
        if (ERREURS_CHAMPS[idChamp]) erreur(ERREURS_CHAMPS[idChamp], '');
    }

    function viderToutesLesErreurs() {
        document.querySelectorAll('#demandeSuperadminForm .champ-erreur').forEach(el => {
            el.textContent = '';
        });
    }

    function estWizard() {
        return demandeObjet.value === OBJET_QR;
    }

    function afficherEtape(num) {
        etapeCourante = num;
        const wizard = estWizard();

        etape1.style.display = num === 1 ? '' : 'none';
        etape2.style.display = (wizard && num === 2) ? '' : 'none';
        footerEtape1.style.display = num === 1 ? '' : 'none';
        footerEtape2.style.display = (wizard && num === 2) ? '' : 'none';

        badgeEtape.style.display = wizard ? '' : 'none';
        if (wizard) badgeEtape.textContent = 'Étape ' + num + ' / 2';

        btnEtape1.innerHTML = wizard
            ? '<i class="bi bi-arrow-right me-1"></i> Suivant'
            : '<i class="bi bi-send me-1"></i> Envoyer la demande';

        const corps = document.querySelector('#demandeSuperadminForm .modal-body');
        if (corps) corps.scrollTop = 0;
    }

    function resetDemande() {
        demandeEvenementGroup.style.display = 'none';
        demandeQuantitesGroup.style.display = 'none';
        demandeCommissionGroup.style.display = 'none';
        demandeInfoCommission.style.display = 'none';
        demandeCampagneGroup.style.display = 'none';
        demandeTemplateGroup.style.display = 'none';
        demandeTemplateImage.required = false;
        demandeFormat.required = false;
        demandeTemplateImage.value = '';
        demandeLargeur.required = false;
        demandeHauteur.required = false;
        demandeEvenement.value = '';
        demandeQuantites.innerHTML = '';
    }

    // Vérification du fichier PNG : immédiate au choix, bloquante à la validation
    function verifierFichier(strict) {
        const fichier = demandeTemplateImage.files && demandeTemplateImage.files[0];

        if (!fichier) {
            erreur('err_template_image', strict ? 'Veuillez importer votre image de ticket au format PNG.' : '');
            return false;
        }

        const nom = (fichier.name || '').toLowerCase();
        if (!nom.endsWith('.png') || (fichier.type && fichier.type !== 'image/png')) {
            erreur('err_template_image', 'Format accepté : PNG uniquement. Veuillez importer une image PNG.');
            demandeTemplateImage.value = '';
            return false;
        }

        if (fichier.size > TAILLE_MAX_FICHIER) {
            erreur('err_template_image', 'L\'image ne doit pas dépasser 10 Mo.');
            demandeTemplateImage.value = '';
            return false;
        }

        erreur('err_template_image', '');
        return true;
    }

    function validerQuantites() {
        const champs = demandeQuantites.querySelectorAll('input[name^="quantites["]');
        if (!champs.length) {
            erreur('err_quantites', 'Sélectionnez un événement disposant de tarifs actifs.');
            return false;
        }

        let total = 0;
        let valide = true;
        champs.forEach(champ => {
            const valeur = parseInt(champ.value, 10);
            if (!isNaN(valeur) && valeur > 5000) {
                erreur('err_quantites', 'Maximum 5000 tickets par tarif.');
                valide = false;
            }
            if (!isNaN(valeur) && valeur > 0) total += valeur;
        });

        if (!valide) return false;

        if (total <= 0) {
            erreur('err_quantites', 'Indiquez au moins une quantité de QR codes.');
            return false;
        }

        erreur('err_quantites', '');
        return true;
    }

    function validerDimension(champ, idErreur, messageVide, messageBornes) {
        if (champ.value === '') {
            erreur(idErreur, messageVide);
            return false;
        }
        const valeur = parseInt(champ.value, 10);
        if (isNaN(valeur) || valeur < 30 || valeur > 200) {
            erreur(idErreur, messageBornes);
            return false;
        }
        erreur(idErreur, '');
        return true;
    }

    // Validation de l'étape 1 : erreurs affichées sous chaque champ concerné
    function validerEtape1() {
        viderToutesLesErreurs();

        let valide = true;
        const objet = demandeObjet.value;

        if (!objet) {
            erreur('err_objet', 'Veuillez choisir un motif de demande.');
            valide = false;
        }

        if (objet && OBJET_EVENEMENT.includes(objet) && !demandeEvenement.value) {
            erreur('err_evenement_id', 'Sélectionnez l\'événement concerné.');
            valide = false;
        }

        if (objet === 'reduction_commission') {
            const commission = demandeCommission.value;
            const numerique = parseFloat(commission);
            if (commission === '' || isNaN(numerique) || numerique < 0 || numerique > 100) {
                erreur('err_commission_pourcentage', 'Indiquez un pourcentage compris entre 0 et 100.');
                valide = false;
            }
        }

        if (objet === OBJET_QR) {
            if (!validerQuantites()) valide = false;
            if (!verifierFichier(true)) valide = false;

            if (!demandeFormat.value) {
                erreur('err_format', 'Veuillez choisir un format.');
                valide = false;
            } else if (demandeFormat.value === 'custom') {
                if (!validerDimension(demandeLargeur, 'err_largeur_personnalisee',
                    'Renseignez la largeur de votre ticket (mm).',
                    'La largeur doit être comprise entre 30 et 200 mm.')) valide = false;
                if (!validerDimension(demandeHauteur, 'err_hauteur_personnalisee',
                    'Renseignez la hauteur de votre ticket (mm).',
                    'La hauteur doit être comprise entre 30 et 200 mm.')) valide = false;
            }
        }

        if (!valide) {
            const premiere = document.querySelector('#demandeSuperadminForm .champ-erreur:not(:empty)');
            if (premiere && premiere.scrollIntoView) premiere.scrollIntoView({ block: 'nearest' });
        }

        return valide;
    }

    function construireRecap() {
        const optionObjet = demandeObjet.selectedOptions && demandeObjet.selectedOptions[0];
        document.getElementById('recap_objet').textContent = optionObjet ? optionObjet.textContent.trim() : '';

        const optionEvenement = demandeEvenement.selectedOptions && demandeEvenement.selectedOptions[0];
        document.getElementById('recap_evenement').textContent = optionEvenement ? optionEvenement.textContent.trim() : '—';

        const lignes = [];
        demandeQuantites.querySelectorAll('div').forEach(rang => {
            const champ = rang.querySelector('input');
            const nom = rang.querySelector('span');
            if (!champ || !nom) return;
            const quantite = parseInt(champ.value, 10);
            if (!isNaN(quantite) && quantite > 0) {
                lignes.push('<div class="mb-1"><i class="bi bi-ticket-perforated me-1" style="color:#7B3FA0;"></i>'
                    + escapeHtml(nom.textContent.trim()) + ' : <strong>' + quantite + '</strong> ticket(s)</div>');
            }
        });
        document.getElementById('recap_quantites').innerHTML = lignes.join('');

        const fichier = demandeTemplateImage.files && demandeTemplateImage.files[0];
        document.getElementById('recap_template').textContent = fichier ? fichier.name : '—';

        const optionFormat = demandeFormat.selectedOptions && demandeFormat.selectedOptions[0];
        let format = optionFormat ? optionFormat.textContent.trim() : '—';
        if (demandeFormat.value === 'custom') {
            format += ' — ' + demandeLargeur.value + ' × ' + demandeHauteur.value + ' mm';
        }
        document.getElementById('recap_format').textContent = format;

        document.getElementById('recap_message').textContent = demandeMessage.value.trim() || '—';
    }

    demandeObjet.addEventListener('change', function () {
        resetDemande();
        if (!enRestauration) viderToutesLesErreurs();
        afficherEtape(1);

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

        if (objet === 'booster_promouvoir') {
            demandeCampagneGroup.style.display = '';
        }

        if (objet === OBJET_QR) {
            demandeInfoCommission.style.display = '';
            demandeTemplateGroup.style.display = '';
            demandeTemplateImage.required = true;
            demandeFormat.required = true;
            syncDimensionsTemplate();
        }
    });

    // Dimensions personnalisées : affichées uniquement si le format « custom » est choisi
    function syncDimensionsTemplate() {
        const custom = demandeFormat.value === 'custom';
        demandeLargeurGroup.style.display = custom ? '' : 'none';
        demandeHauteurGroup.style.display = custom ? '' : 'none';
        demandeLargeur.required = custom;
        demandeHauteur.required = custom;
    }

    demandeFormat.addEventListener('change', function () {
        syncDimensionsTemplate();
        if (!enRestauration) viderErreurChamp('demande_format');
    });

    demandeEvenement.addEventListener('change', function () {
        demandeQuantites.innerHTML = '';
        if (!enRestauration) {
            erreur('err_evenement_id', '');
            erreur('err_quantites', '');
        }

        const objet = demandeObjet.value;
        if (objet !== OBJET_QR) return;

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
            div.innerHTML = '<span style="font-size:0.8rem;color:#444;flex:1;">' + escapeHtml(t.nom) + '</span>' +
                '<input type="number" class="form-control form-control-sm" style="width:110px;" ' +
                'name="quantites[' + t.id + ']" min="0" max="5000" step="1" placeholder="Qté">';
            div.querySelector('input').addEventListener('input', function () {
                if (!enRestauration) erreur('err_quantites', '');
            });
            demandeQuantites.appendChild(div);
        });
    });

    Object.keys(ERREURS_CHAMPS).forEach(idChamp => {
        const champ = document.getElementById(idChamp);
        if (!champ) return;
        champ.addEventListener('change', () => viderErreurChamp(idChamp));
        champ.addEventListener('input', () => viderErreurChamp(idChamp));
    });

    demandeTemplateImage.addEventListener('change', function () {
        verifierFichier(false);
    });

    // Le passage à l'étape 2 (QR) ou l'envoi direct (autres motifs) passe par la validation
    form.addEventListener('submit', function (e) {
        if (etapeCourante !== 1) return;

        if (!validerEtape1()) {
            e.preventDefault();
            return;
        }

        if (estWizard()) {
            e.preventDefault();
            construireRecap();
            afficherEtape(2);
        }
    });

    document.getElementById('demandeBtnPrecedent').addEventListener('click', function () {
        afficherEtape(1);
    });

    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            demandeObjet.value = '';
            resetDemande();
            demandeMessage.value = '';
            viderToutesLesErreurs();
            afficherEtape(1);
        });
    }

    // Ouvre le modal avec un objet pré-sélectionné (boutons contextuels des pages)
    window.openDemande = function (objet) {
        if (!OBJET_EVENEMENT.includes(objet) && objet !== 'probleme_technique') return;
        demandeObjet.value = objet;
        demandeObjet.dispatchEvent(new Event('change'));
        bootstrap.Modal.getOrCreateInstance(modal).show();
    };

    // Réouverture après erreur serveur : le formulaire est restauré et l'étape 1 affichée
    const restauration = window.DEMANDE_RESTORE;
    if (restauration && restauration.objet) {
        enRestauration = true;

        demandeObjet.value = restauration.objet;
        demandeObjet.dispatchEvent(new Event('change'));

        if (restauration.format) {
            demandeFormat.value = restauration.format;
            syncDimensionsTemplate();
        }

        if (restauration.evenement_id) {
            demandeEvenement.value = restauration.evenement_id;
            demandeEvenement.dispatchEvent(new Event('change'));

            const quantites = restauration.quantites || {};
            Object.keys(quantites).forEach(tarifId => {
                const champ = document.querySelector('#demande_quantites input[name="quantites[' + tarifId + ']"]');
                if (champ) champ.value = quantites[tarifId];
            });
        }

        if (restauration.message) demandeMessage.value = restauration.message;
        if (restauration.commission !== null && restauration.commission !== undefined && restauration.commission !== '') {
            document.getElementById('demande_commission').value = restauration.commission;
        }
        if (restauration.persona) document.getElementById('demande_persona').value = restauration.persona;
        if (restauration.canal) document.getElementById('demande_canal').value = restauration.canal;

        enRestauration = false;
        afficherEtape(1);
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});
</script>
