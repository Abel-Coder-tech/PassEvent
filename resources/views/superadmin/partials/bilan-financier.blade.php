{{--
    Bilan financier (super admin) : en ligne, espèces et physiques séparés,
    puis total, commissions et net. Avec $solde (fiche organisateur) : ce qu'il
    reste à verser en tenant compte des retraits déjà faits.
    Variables : $bilan (App\Services\BilanFinancier), $solde (optionnel, User::soldeRetrait()).
--}}
@php
    $f = fn ($v) => number_format((float) $v, 0, ',', ' ').' F';
    $pluriel = fn ($n, $mot) => $n.' '.$mot.($n > 1 ? 's' : '');
@endphp
<div class="sa-card mb-4">
    <div class="sa-card-header">
        <span><i class="bi bi-calculator me-2" style="color: var(--sa-primary);"></i>Bilan financier</span>
        <span class="text-muted" style="font-size:0.75rem;">Billets payés uniquement</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table bilan-table mb-0">
            <tbody>
                <tr class="bilan-section"><td colspan="2">Montants encaissés</td></tr>
                <tr>
                    <td>
                        <i class="bi bi-phone me-1" style="color:#3498db;"></i> Ventes en ligne — Mobile Money
                        <small class="d-block text-muted">{{ $pluriel($bilan['mobile_nb'], 'billet') }} · encaissé par PaxEvent (FedaPay)</small>
                    </td>
                    <td class="text-end fw-bold" style="color:#3498db;">{{ $f($bilan['mobile']) }}</td>
                </tr>
                <tr>
                    <td>
                        <i class="bi bi-cash me-1" style="color:#f39c12;"></i> Ventes en espèces
                        <small class="d-block text-muted">{{ $pluriel($bilan['especes_nb'], 'billet') }} · agents de vente / vente manuelle, encaissé par l'organisateur</small>
                    </td>
                    <td class="text-end fw-bold" style="color:#f39c12;">{{ $f($bilan['especes']) }}</td>
                </tr>
                <tr>
                    <td>
                        <i class="bi bi-ticket-perforated me-1" style="color:#7B3FA0;"></i> Billets physiques (lots imprimés)
                        <small class="d-block text-muted">{{ $pluriel($bilan['physique_nb'], 'billet') }} · valeur au guichet, encaissé par l'organisateur</small>
                    </td>
                    <td class="text-end fw-bold" style="color:#7B3FA0;">{{ $f($bilan['physique']) }}</td>
                </tr>
                <tr class="bilan-total">
                    <td>Montant total généré</td>
                    <td class="text-end">{{ $f($bilan['montant_genere']) }}</td>
                </tr>

                <tr class="bilan-section"><td colspan="2">Commissions</td></tr>
                <tr>
                    <td>Commission PaxEvent sur les ventes (en ligne + espèces)</td>
                    <td class="text-end" style="color:var(--sa-danger);">− {{ $f($bilan['commission_ventes']) }}</td>
                </tr>
                @if($bilan['physique_nb'] > 0 || $bilan['commission_physique'] > 0)
                <tr>
                    <td>
                        Commission sur les billets physiques
                        @if($bilan['commission_physique_payee'] > 0)
                            <small class="d-block text-muted">dont {{ $f($bilan['commission_physique_payee']) }} déjà réglés à la commande du lot</small>
                        @endif
                    </td>
                    <td class="text-end" style="color:var(--sa-danger);">− {{ $f($bilan['commission_physique']) }}</td>
                </tr>
                @endif
                @if($bilan['commission_affiliation'] > 0)
                <tr>
                    <td>Commissions des affiliés (co-parrainage)</td>
                    <td class="text-end" style="color:var(--sa-danger);">− {{ $f($bilan['commission_affiliation']) }}</td>
                </tr>
                @endif
                <tr class="bilan-total">
                    <td>Net organisateur (tous canaux)</td>
                    <td class="text-end" style="color:var(--sa-success);">{{ $f($bilan['net_organisateur']) }}</td>
                </tr>

                @isset($solde)
                <tr class="bilan-section"><td colspan="2">À verser à l'organisateur (Mobile Money uniquement)</td></tr>
                <tr>
                    <td>Mobile Money encaissé par PaxEvent</td>
                    <td class="text-end">{{ $f($solde['mobileRecettes']) }}</td>
                </tr>
                <tr>
                    <td>
                        Commissions prélevées
                        <small class="d-block text-muted">commission PaxEvent (y compris celle due sur les espèces) et affiliés</small>
                    </td>
                    <td class="text-end" style="color:var(--sa-danger);">− {{ $f($solde['commissionImputee'] + $solde['commissionAffiliation']) }}</td>
                </tr>
                <tr>
                    <td>Retraits déjà payés</td>
                    <td class="text-end" style="color:var(--sa-danger);">− {{ $f($solde['retraitsPayes']) }}</td>
                </tr>
                @if($solde['retraitsEnCours'] > 0)
                <tr>
                    <td>Retraits demandés, en attente ou en cours</td>
                    <td class="text-end" style="color:#e67e22;">− {{ $f($solde['retraitsEnCours']) }}</td>
                </tr>
                @endif
                <tr class="bilan-total bilan-solde">
                    <td>Reste à verser (retirable)</td>
                    <td class="text-end">{{ $f($solde['soldeDisponible']) }}</td>
                </tr>
                @endisset
            </tbody>
        </table>
    </div>
</div>

@once
<style>
    .bilan-table td { padding: 0.55rem 1rem; font-size: 0.84rem; vertical-align: middle; }
    .bilan-table .bilan-section td { background: #f7f5fa; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6c6478; padding-top: 0.45rem; padding-bottom: 0.45rem; }
    .bilan-table .bilan-total td { font-weight: 800; border-top: 2px solid #e6e1ec; }
    .bilan-table .bilan-solde td { background: rgba(39,174,96,0.08); color: #1e7a46; font-size: 0.95rem; }
    .bilan-table td.text-end { white-space: nowrap; }
</style>
@endonce
