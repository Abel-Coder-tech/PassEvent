{{--
    Bilan financier (super admin) : un bloc par canal (en ligne, espèces,
    physiques), puis le calcul « montant généré − commissions = net ».
    Avec $solde (fiche organisateur) : ce qu'il reste à verser, retraits déduits.
    Variables : $bilan (App\Services\BilanFinancier), $solde (optionnel, User::soldeRetrait()).
--}}
@php
    $f = fn ($v) => number_format((float) $v, 0, ',', ' ').' F';
    $billets = fn ($n) => number_format($n, 0, ',', ' ').' billet'.($n > 1 ? 's' : '');
    $commissions = $bilan['commission_ventes'] + $bilan['commission_physique'] + $bilan['commission_affiliation'];
    $part = fn ($v) => $bilan['montant_genere'] > 0 ? round($v / $bilan['montant_genere'] * 100) : 0;
@endphp
<div class="sa-card mb-4 bf">
    <div class="sa-card-header">
        <span><i class="bi bi-calculator me-2" style="color: var(--sa-primary);"></i>Bilan financier</span>
        <span class="text-muted" style="font-size:0.75rem;">Billets payés uniquement</span>
    </div>
    <div class="sa-card-body">

        {{-- 1. Un bloc par canal --}}
        <div class="bf-canaux">
            <div class="bf-canal" style="--c:#3498db;">
                <div class="bf-canal-tete"><i class="bi bi-phone"></i> En ligne · Mobile Money</div>
                <div class="bf-canal-montant">{{ $f($bilan['mobile']) }}</div>
                <div class="bf-canal-pied">{{ $billets($bilan['mobile_nb']) }} · <strong>encaissé par PaxEvent</strong></div>
                <div class="bf-barre"><span style="width: {{ $part($bilan['mobile']) }}%"></span></div>
            </div>
            <div class="bf-canal" style="--c:#f39c12;">
                <div class="bf-canal-tete"><i class="bi bi-cash"></i> Espèces · agents / vente manuelle</div>
                <div class="bf-canal-montant">{{ $f($bilan['especes']) }}</div>
                <div class="bf-canal-pied">{{ $billets($bilan['especes_nb']) }} · encaissé par l'organisateur</div>
                <div class="bf-barre"><span style="width: {{ $part($bilan['especes']) }}%"></span></div>
            </div>
            <div class="bf-canal" style="--c:#7B3FA0;">
                <div class="bf-canal-tete"><i class="bi bi-ticket-perforated"></i> Billets physiques</div>
                <div class="bf-canal-montant">{{ $f($bilan['physique']) }}</div>
                <div class="bf-canal-pied">{{ $billets($bilan['physique_nb']) }} · valeur au guichet, encaissé par l'organisateur</div>
                <div class="bf-barre"><span style="width: {{ $part($bilan['physique']) }}%"></span></div>
            </div>
        </div>

        {{-- 2. Montant généré − commissions = net --}}
        <div class="bf-calcul">
            <div class="bf-terme">
                <small>Montant total généré</small>
                <strong>{{ $f($bilan['montant_genere']) }}</strong>
            </div>
            <div class="bf-op">−</div>
            <div class="bf-terme bf-moins">
                <small>Commissions</small>
                <strong>{{ $f($commissions) }}</strong>
                <ul>
                    <li>PaxEvent sur les ventes : {{ $f($bilan['commission_ventes']) }}</li>
                    @if($bilan['physique_nb'] > 0 || $bilan['commission_physique'] > 0)
                        <li>Billets physiques : {{ $f($bilan['commission_physique']) }}@if($bilan['commission_physique_payee'] > 0) <span class="text-muted">(dont {{ $f($bilan['commission_physique_payee']) }} déjà réglés)</span>@endif</li>
                    @endif
                    @if($bilan['commission_affiliation'] > 0)
                        <li>Affiliés : {{ $f($bilan['commission_affiliation']) }}</li>
                    @endif
                </ul>
            </div>
            <div class="bf-op">=</div>
            <div class="bf-terme bf-net">
                <small>Net organisateur (tous canaux)</small>
                <strong>{{ $f($bilan['net_organisateur']) }}</strong>
            </div>
        </div>

        {{-- 3. Ce que PaxEvent doit verser (fiche organisateur) --}}
        @isset($solde)
        <div class="bf-solde">
            <div class="bf-solde-titre"><i class="bi bi-wallet2 me-1"></i> À verser à l'organisateur <span>(seul le Mobile Money transite par PaxEvent)</span></div>
            <div class="bf-calcul bf-calcul-solde">
                <div class="bf-terme">
                    <small>Mobile Money encaissé</small>
                    <strong>{{ $f($solde['mobileRecettes']) }}</strong>
                </div>
                <div class="bf-op">−</div>
                <div class="bf-terme bf-moins">
                    <small>Commissions prélevées</small>
                    <strong>{{ $f($solde['commissionImputee'] + $solde['commissionAffiliation']) }}</strong>
                </div>
                <div class="bf-op">−</div>
                <div class="bf-terme bf-moins">
                    <small>Retraits payés</small>
                    <strong>{{ $f($solde['retraitsPayes']) }}</strong>
                    @if($solde['retraitsEnCours'] > 0)
                        <ul><li>+ {{ $f($solde['retraitsEnCours']) }} en attente / en cours</li></ul>
                    @endif
                </div>
                <div class="bf-op">=</div>
                <div class="bf-terme bf-reste">
                    <small>Reste à verser</small>
                    <strong>{{ $f($solde['soldeDisponible']) }}</strong>
                </div>
            </div>
        </div>
        @endisset
    </div>
</div>

@once
<style>
    .bf-canaux { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
    .bf-canal { border: 1px solid #ece8f1; border-left: 4px solid var(--c); border-radius: 10px; padding: 0.8rem 0.9rem; }
    .bf-canal-tete { font-size: 0.72rem; font-weight: 700; color: var(--c); text-transform: uppercase; letter-spacing: .03em; }
    .bf-canal-tete i { margin-right: 0.25rem; }
    .bf-canal-montant { font-size: 1.35rem; font-weight: 800; color: #211c31; margin: 0.2rem 0 0.1rem; }
    .bf-canal-pied { font-size: 0.74rem; color: #7a7386; }
    .bf-barre { height: 4px; background: #f1eef5; border-radius: 4px; margin-top: 0.55rem; overflow: hidden; }
    .bf-barre span { display: block; height: 100%; background: var(--c); border-radius: 4px; }

    .bf-calcul { display: flex; align-items: stretch; gap: 0.5rem; background: #faf9fc; border-radius: 10px; padding: 0.75rem; }
    .bf-terme { flex: 1 1 0; min-width: 0; background: #fff; border: 1px solid #ece8f1; border-radius: 8px; padding: 0.6rem 0.75rem; }
    .bf-terme small { display: block; font-size: 0.7rem; color: #7a7386; font-weight: 600; }
    .bf-terme strong { display: block; font-size: 1.1rem; font-weight: 800; color: #211c31; white-space: nowrap; }
    .bf-terme ul { list-style: none; padding: 0; margin: 0.35rem 0 0; font-size: 0.7rem; color: #7a7386; }
    .bf-moins strong { color: var(--sa-danger); }
    .bf-net { border-color: rgba(39,174,96,0.35); background: rgba(39,174,96,0.06); }
    .bf-net strong { color: #1e7a46; }
    .bf-op { align-self: center; font-size: 1.3rem; font-weight: 700; color: #b3adbb; }

    .bf-solde { margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #e3ddea; }
    .bf-solde-titre { font-size: 0.8rem; font-weight: 700; color: #211c31; margin-bottom: 0.5rem; }
    .bf-solde-titre span { font-weight: 400; color: #7a7386; font-size: 0.74rem; }
    .bf-reste { border-color: var(--sa-primary); background: rgba(107,63,160,0.07); }
    .bf-reste strong { color: var(--sa-primary); font-size: 1.25rem; }

    @media (max-width: 767.98px) {
        .bf-canaux { grid-template-columns: 1fr; }
        .bf-calcul { flex-direction: column; }
        .bf-op { align-self: flex-start; padding-left: 0.6rem; line-height: 1; }
    }
</style>
@endonce
