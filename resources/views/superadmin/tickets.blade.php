@extends('superadmin.layouts.master')

@section('title', 'Tickets - Super Admin')
@section('page-title', 'Tickets')

@section('content')
@php
    // Données du modal : tout ce qu'il faut pour contrôler un encaissement
    // avant de renvoyer le billet. Aucun nouveau billet n'est créé au renvoi.
    $ticketsModal = $allTickets->getCollection()->map(fn ($t) => [
        'id' => $t->id,
        'code' => $t->code_unique,
        'statut' => $t->statut_paiement,
        'renvoyable' => $t->estDelivrable() && filled($t->email_acheteur),
        'evenement' => $t->evenement?->titre,
        'date_evenement' => $t->evenement?->date_event?->isoFormat('D MMM YYYY HH:mm'),
        'lieu' => $t->evenement?->lieu,
        'tarif' => $t->nom_tarif,
        'quantite' => (int) ($t->quantite ?? 1),
        'nom' => $t->nom_acheteur,
        'email' => $t->email_acheteur,
        'telephone' => $t->telephone_acheteur,
        'whatsapp' => $t->whatsapp_acheteur,
        'telephone_paiement' => $t->telephone_paiement,
        'transaction' => $t->transaction_id,
        'fedapay' => $t->fedapay_transaction_id,
        'methode' => $t->methode_paiement_label,
        'montant' => (float) $t->montant,
        'reduction' => (float) $t->montant_reduction,
        'date_achat' => $t->date_achat?->isoFormat('D MMM YYYY HH:mm'),
        'email_envoye_at' => $t->email_envoye_at?->isoFormat('D MMM YYYY HH:mm'),
        'email_tentatives' => (int) $t->email_tentatives,
        'email_erreur' => $t->email_derniere_erreur,
        'utilise' => (bool) $t->utilise,
        'annule' => (bool) $t->annule,
        'annule_at' => $t->annule_at?->isoFormat('D MMM YYYY HH:mm'),
        'expire_at' => $t->expire_at?->isoFormat('D MMM YYYY HH:mm'),
        'source' => $t->source,
    ])->values();
@endphp

<div class="sa-card">
    <div class="sa-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <span><i class="bi bi-ticket-perforated-fill me-2" style="color: var(--sa-primary);"></i>Tous les tickets</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $allTickets->total() }} total — cliquez sur un billet pour voir le detail</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Code unique</th><th>Evenement</th><th>Acheteur</th><th>WhatsApp</th><th>N° paiement</th><th>Montant</th><th>Statut</th><th>Email</th><th>Utilise</th><th>Date achat</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($allTickets as $t)
                <tr class="tm-row" data-ticket="{{ $t->id }}" tabindex="0"
                    onclick="ouvrirTicketModal({{ $t->id }})"
                    onkeydown="if (event.key === 'Enter') ouvrirTicketModal({{ $t->id }})">
                    <td style="font-family:monospace;font-size:0.75rem;">{{ $t->code_unique }}</td>
                    <td>{{ $t->evenement->titre ?? '-' }}</td>
                    <td>{{ $t->email_acheteur }}<br><small class="text-muted">{{ $t->nom_acheteur ?? '-' }}</small></td>
                    <td>@if($t->whatsapp_acheteur){{ $t->whatsapp_acheteur }}@else <span class="text-muted">—</span> @endif</td>
                    <td>@if($t->telephone_paiement){{ $t->telephone_paiement }}@elseif($t->telephone_acheteur){{ $t->telephone_acheteur }}@else <span class="text-muted">—</span> @endif</td>
                    <td><strong>@if($t->montant > 0){{ number_format($t->montant, 0, ',', ' ') }} F @else Gratuit @endif</strong></td>
                    <td>
                        @if($t->statut_paiement === 'payé') <span class="sa-badge sa-badge-success">Paye</span>
                        @elseif($t->statut_paiement === 'échoué') <span class="sa-badge sa-badge-danger">Echoue</span>
                        @elseif($t->statut_paiement === 'remboursé') <span class="sa-badge sa-badge-warning">Rembourse</span>
                        @else <span class="sa-badge sa-badge-secondary">{{ $t->statut_paiement }}</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">
                        @if($t->email_envoye_at)
                            <span class="sa-badge sa-badge-success">Envoyé</span><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($t->email_envoye_at)->isoFormat('D MMM YYYY HH:mm') }}</small>
                        @elseif($t->email_derniere_erreur)
                            <span class="sa-badge sa-badge-danger" title="{{ $t->email_derniere_erreur }}">Échec</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($t->utilise) <span class="sa-badge sa-badge-success">Oui</span>
                        @else <span class="sa-badge sa-badge-secondary">Non</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $t->date_achat ? \Carbon\Carbon::parse($t->date_achat)->isoFormat('D MMM YYYY HH:mm') : '-' }}</td>
                    <td onclick="event.stopPropagation()">
                        @include('superadmin.partials.renvoyer-ticket', ['t' => $t])
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $allTickets->links() }}</div>

{{-- Modal : details du billet + renvoi --}}
<div id="ticketModal" class="tm-overlay" onclick="if (event.target === this) fermerTicketModal()">
    <div class="tm-box" role="dialog" aria-modal="true" aria-labelledby="tm-titre">
        <div class="tm-header">
            <h5 id="tm-titre"><i class="bi bi-receipt me-2" style="color: var(--sa-primary);"></i>Billet <span id="tm-code" style="font-family:monospace;"></span></h5>
            <button type="button" class="tm-close" onclick="fermerTicketModal()" aria-label="Fermer">&times;</button>
        </div>

        <div class="tm-body">
            <div class="tm-section">
                <div class="tm-section-title"><i class="bi bi-credit-card-2-front me-1"></i>Paiement</div>
                <div class="tm-grid">
                    <div><span>Statut du paiement</span><strong><span id="tm-statut" class="tm-badge"></span></strong></div>
                    <div><span>Montant</span><strong id="tm-montant">—</strong></div>
                    <div><span>Methode</span><strong id="tm-methode">—</strong></div>
                    <div><span>Reduction</span><strong id="tm-reduction">—</strong></div>
                    <div><span>N° de transaction</span><strong id="tm-transaction" style="font-family:monospace;font-size:0.78rem;">—</strong></div>
                    <div><span>ID FedaPay</span><strong id="tm-fedapay" style="font-family:monospace;font-size:0.78rem;">—</strong></div>
                    <div><span>Telephone de paiement</span><strong id="tm-tel-paiement">—</strong></div>
                    <div><span>Date d'achat</span><strong id="tm-date-achat">—</strong></div>
                    <div><span>Source</span><strong id="tm-source">—</strong></div>
                </div>
            </div>

            <div class="tm-section">
                <div class="tm-section-title"><i class="bi bi-ticket-perforated me-1"></i>Billet</div>
                <div class="tm-grid">
                    <div><span>Evenement</span><strong id="tm-evenement">—</strong></div>
                    <div><span>Date de l'evenement</span><strong id="tm-date-evenement">—</strong></div>
                    <div><span>Lieu</span><strong id="tm-lieu">—</strong></div>
                    <div><span>Tarif</span><strong id="tm-tarif">—</strong></div>
                    <div><span>Quantite</span><strong id="tm-quantite">—</strong></div>
                    <div><span>Utilise</span><strong id="tm-utilise">—</strong></div>
                    <div><span>Annule</span><strong id="tm-annule">—</strong></div>
                    <div><span>Expire le</span><strong id="tm-expire">—</strong></div>
                </div>
            </div>

            <div class="tm-section">
                <div class="tm-section-title"><i class="bi bi-person-lines-fill me-1"></i>Acheteur</div>
                <div class="tm-grid">
                    <div><span>Nom</span><strong id="tm-nom">—</strong></div>
                    <div><span>Email</span><strong id="tm-email" style="word-break:break-all;">—</strong></div>
                    <div><span>Telephone</span><strong id="tm-telephone">—</strong></div>
                    <div><span>WhatsApp</span><strong id="tm-whatsapp">—</strong></div>
                </div>
            </div>

            <div class="tm-section">
                <div class="tm-section-title"><i class="bi bi-envelope-check me-1"></i>Email du billet</div>
                <div class="tm-grid">
                    <div><span>Dernier envoi accepte</span><strong id="tm-email-date">—</strong></div>
                    <div><span>Tentatives</span><strong id="tm-email-tentatives">—</strong></div>
                    <div style="grid-column:1 / -1;"><span>Derniere erreur</span><strong id="tm-email-erreur" style="font-weight:500;">—</strong></div>
                </div>
            </div>

            <div id="tm-renvoyer-ok" class="tm-renvoyer" style="display:none;">
                <div class="tm-renvoyer-texte">
                    <i class="bi bi-shield-check me-1"></i>Paiement encaisse : le billet peut etre renvoye tel quel (meme code, meme QR).
                </div>
                <form id="tm-renvoyer-form" method="POST" action="#"
                      onsubmit="return confirm('Renvoyer ce billet par email a l\'acheteur ?')">
                    @csrf
                    <button type="submit" class="tm-btn-send">
                        <i class="bi bi-envelope-paper me-1"></i>Renvoyer le billet
                    </button>
                </form>
            </div>

            <div id="tm-renvoyer-refus" class="tm-renvoyer-refus" style="display:none;">
                <i class="bi bi-exclamation-triangle me-1"></i><span id="tm-renvoyer-refus-texte"></span>
            </div>
        </div>

        <div class="tm-footer">
            <button type="button" class="tm-btn-fermer" onclick="fermerTicketModal()">Fermer</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tm-row { cursor: pointer; }
    .tm-row:hover { background: rgba(107, 63, 160, 0.05); }
    .tm-row:focus { outline: 2px solid var(--sa-primary); outline-offset: -2px; }

    .tm-overlay { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; }
    .tm-box { background: #fff; border-radius: 14px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25); animation: tmIn 0.18s ease; }
    @keyframes tmIn { from { transform: scale(0.96); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .tm-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #eee; }
    .tm-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
    .tm-close { background: none; border: none; font-size: 1.5rem; line-height: 1; color: #999; cursor: pointer; }
    .tm-body { padding: 1.25rem; overflow-y: auto; }
    .tm-footer { padding: 0.75rem 1.25rem; border-top: 1px solid #eee; display: flex; justify-content: flex-end; }
    .tm-section { margin-bottom: 1.1rem; }
    .tm-section:last-of-type { margin-bottom: 0.5rem; }
    .tm-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--sa-primary); margin-bottom: 0.5rem; }
    .tm-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem 1rem; }
    .tm-grid > div { font-size: 0.82rem; min-width: 0; }
    .tm-grid span { display: block; color: #8a8f9a; font-size: 0.72rem; }
    .tm-grid strong { display: block; font-weight: 600; color: #2b2b2b; }
    @media (max-width: 575px) { .tm-grid { grid-template-columns: 1fr; } }
    .tm-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
    .tm-badge.is-ok { background: rgba(39, 174, 96, 0.12); color: #1e8449; }
    .tm-badge.is-ko { background: rgba(231, 76, 60, 0.12); color: #c0392b; }
    .tm-badge.is-warn { background: rgba(243, 156, 18, 0.15); color: #b9770e; }
    .tm-badge.is-muted { background: #f1f2f6; color: #666; }
    .tm-renvoyer { margin-top: 1rem; padding: 0.85rem 1rem; border: 1px solid rgba(39, 174, 96, 0.35); background: rgba(39, 174, 96, 0.06); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }
    .tm-renvoyer-texte { font-size: 0.8rem; color: #1e8449; }
    .tm-btn-send { background: var(--sa-primary); border: none; color: #fff; padding: 0.45rem 1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
    .tm-btn-send:hover { filter: brightness(0.95); }
    .tm-renvoyer-refus { margin-top: 1rem; padding: 0.75rem 1rem; border: 1px solid #f3d6d2; background: #fdf3f2; border-radius: 10px; font-size: 0.8rem; color: #a33a2d; }
    .tm-btn-fermer { background: #6c757d; border: none; color: #fff; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
    const SA_TICKETS = {!! Illuminate\Support\Js::from($ticketsModal) !!};
    const SA_TICKETS_URL = @json(url('/superadmin/tickets'));

    function tmTexte(id, valeur, videParDefaut = '—') {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = (valeur === null || valeur === undefined || valeur === '') ? videParDefaut : valeur;
    }

    function tmOuiNon(id, valeur) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = valeur ? 'Oui' : 'Non';
    }

    function tmStatut(statut) {
        const el = document.getElementById('tm-statut');
        if (!el) return;
        const connus = {
            'payé': ['Payé', 'is-ok'],
            'échoué': ['Échoué', 'is-ko'],
            'remboursé': ['Remboursé', 'is-warn'],
            'en_attente': ['En attente', 'is-muted'],
        };
        const [libelle, classe] = connus[statut] || [statut || 'Inconnu', 'is-muted'];
        el.textContent = libelle;
        el.className = 'tm-badge ' + classe;
    }

    function ouvrirTicketModal(id) {
        const t = SA_TICKETS.find(b => Number(b.id) === Number(id));
        if (!t) return;

        tmTexte('tm-code', t.code);
        tmStatut(t.statut);
        tmTexte('tm-montant', t.montant > 0 ? new Intl.NumberFormat('fr-FR').format(t.montant) + ' F' : 'Gratuit');
        tmTexte('tm-methode', t.methode);
        tmTexte('tm-reduction', t.reduction > 0 ? new Intl.NumberFormat('fr-FR').format(t.reduction) + ' F' : null);
        tmTexte('tm-transaction', t.transaction);
        tmTexte('tm-fedapay', t.fedapay);
        tmTexte('tm-tel-paiement', t.telephone_paiement);
        tmTexte('tm-date-achat', t.date_achat);
        tmTexte('tm-source', t.source);

        tmTexte('tm-evenement', t.evenement);
        tmTexte('tm-date-evenement', t.date_evenement);
        tmTexte('tm-lieu', t.lieu);
        tmTexte('tm-tarif', t.tarif);
        tmTexte('tm-quantite', t.quantite);
        tmOuiNon('tm-utilise', t.utilise);
        tmOuiNon('tm-annule', t.annule);
        tmTexte('tm-expire', t.expire_at);

        tmTexte('tm-nom', t.nom);
        tmTexte('tm-email', t.email);
        tmTexte('tm-telephone', t.telephone);
        tmTexte('tm-whatsapp', t.whatsapp);

        tmTexte('tm-email-date', t.email_envoye_at);
        tmTexte('tm-email-tentatives', t.email_tentatives);
        tmTexte('tm-email-erreur', t.email_erreur);

        const blocOk = document.getElementById('tm-renvoyer-ok');
        const blocRefus = document.getElementById('tm-renvoyer-refus');
        const form = document.getElementById('tm-renvoyer-form');

        if (t.renvoyable) {
            form.action = SA_TICKETS_URL + '/' + t.id + '/renvoyer';
            blocOk.style.display = 'flex';
            blocRefus.style.display = 'none';
        } else {
            let raison = 'Le renvoi est desactive : ';
            if (t.statut !== 'payé') {
                raison += "l'argent n'a pas ete encaisse (statut : " + (t.statut || 'inconnu') + ').';
            } else if (t.annule) {
                raison += 'ce billet a ete annule.';
            } else if (t.expire_at) {
                raison += 'la reservation a expire.';
            } else if (!t.email) {
                raison += "aucune adresse email n'est renseignee.";
            }
            document.getElementById('tm-renvoyer-refus-texte').textContent = raison;
            blocOk.style.display = 'none';
            blocRefus.style.display = 'block';
        }

        document.getElementById('ticketModal').style.display = 'flex';
    }

    function fermerTicketModal() {
        document.getElementById('ticketModal').style.display = 'none';
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') fermerTicketModal();
    });
</script>
@endpush
