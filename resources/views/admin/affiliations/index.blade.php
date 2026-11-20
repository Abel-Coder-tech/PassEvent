@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Co-parrainage')

@section('page-title', 'Co-parrainage')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">Co-parrainage</li>
@endsection

@php
    $fcfa = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' F';
    $nb = fn ($v) => number_format((int) $v, 0, ',', ' ');
    $taux = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ' '), '0'), ',') . ' %';
    $initiales = function ($nom) {
        $mots = preg_split('/\s+/', trim((string) $nom)) ?: [];
        return mb_strtoupper(mb_substr($mots[0] ?? '?', 0, 1) . mb_substr($mots[1] ?? '', 0, 1));
    };
    $statutsCommission = [
        'en_attente' => ['En attente', 'cp-chip-orange'],
        'disponible' => ['Disponible', 'cp-chip-teal'],
        'paye' => ['Payée', 'cp-chip-vert'],
        'annule' => ['Annulée', 'cp-chip-gris'],
    ];
    $maxVentes = max(1, (int) collect($ventesParLien)->max());
@endphp

@section('content')
<div class="page-content cp">
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- En-tête : résumé de la performance --}}
    <section class="cp-hero">
        <div class="cp-hero-texte">
            <span class="cp-hero-chip"><i class="bi bi-share me-1"></i> Co-parrainage</span>
            <h2>Vos affiliés font la promotion de vos événements</h2>
            <p>Chaque vente obtenue grâce à un lien d'affilié lui rapporte une commission, déduite de vos recettes.</p>
        </div>
        <div class="cp-entonnoir" aria-label="Parcours des visiteurs envoyés par vos affiliés">
            <div class="cp-etape">
                <span class="cp-etape-val">{{ $nb($stats['clics']) }}</span>
                <span class="cp-etape-lib">Clics</span>
            </div>
            <i class="bi bi-chevron-right cp-fleche"></i>
            <div class="cp-etape">
                <span class="cp-etape-val">{{ $nb($stats['ventes']) }}</span>
                <span class="cp-etape-lib">Ventes</span>
            </div>
            <i class="bi bi-chevron-right cp-fleche"></i>
            <div class="cp-etape cp-etape-accent">
                <span class="cp-etape-val">{{ str_replace('.', ',', $stats['conversion']) }}%</span>
                <span class="cp-etape-lib">Conversion</span>
            </div>
        </div>
    </section>

    {{-- Indicateurs --}}
    <div class="cp-kpis">
        <div class="cp-kpi">
            <div class="cp-kpi-icone" style="--c:var(--violet);"><i class="bi bi-people"></i></div>
            <div>
                <div class="cp-kpi-lib">Affiliés actifs</div>
                <div class="cp-kpi-val">{{ $nb($stats['affilies']) }}</div>
            </div>
        </div>
        <div class="cp-kpi">
            <div class="cp-kpi-icone" style="--c:var(--teal);"><i class="bi bi-link-45deg"></i></div>
            <div>
                <div class="cp-kpi-lib">Liens actifs</div>
                <div class="cp-kpi-val">{{ $nb($stats['liens_actifs']) }} <small>/ {{ $nb($stats['liens']) }}</small></div>
            </div>
        </div>
        <div class="cp-kpi">
            <div class="cp-kpi-icone" style="--c:#c27c00;"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="cp-kpi-lib">Commissions en attente</div>
                <div class="cp-kpi-val">{{ $fcfa($stats['commissions_en_attente']) }}</div>
            </div>
        </div>
        <div class="cp-kpi">
            <div class="cp-kpi-icone" style="--c:var(--vert);"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="cp-kpi-lib">Total des commissions</div>
                <div class="cp-kpi-val">{{ $fcfa($stats['commissions']) }}</div>
                <div class="cp-kpi-sous">{{ $fcfa($stats['commissions_payees']) }} déjà payées</div>
            </div>
        </div>
    </div>

    {{-- Événements ouverts au co-parrainage --}}
    <div class="cp-section-titre">
        <h3>Événements ouverts au co-parrainage <span class="cp-compte">{{ $parEvenement->count() }}</span></h3>
        <a href="{{ route('admin.evenements.index') }}" class="cp-lien">Mes événements <i class="bi bi-arrow-right"></i></a>
    </div>

    @if ($parEvenement->count() > 0)
        <div class="cp-evenements">
            @foreach ($parEvenement as $ligne)
                @php($ev = $ligne['evenement'])
                <article class="cp-ev">
                    <header class="cp-ev-tete">
                        <div class="cp-ev-date">
                            <span>{{ $ev->date_event?->isoFormat('DD') ?? '—' }}</span>
                            <small>{{ $ev->date_event ? Str::upper($ev->date_event->isoFormat('MMM')) : '' }}</small>
                        </div>
                        <div class="cp-ev-info">
                            <h4>{{ $ev->titre }}</h4>
                            <p><i class="bi bi-geo-alt"></i> {{ Str::limit($ev->lieu, 40) ?: 'Lieu à préciser' }}</p>
                        </div>
                        @if ($ev->statut === 'publié')
                            <span class="cp-chip cp-chip-vert">Publié</span>
                        @elseif ($ev->statut === 'annulé')
                            <span class="cp-chip cp-chip-rouge">Annulé</span>
                        @elseif ($ev->statut === 'terminé')
                            <span class="cp-chip cp-chip-gris">Terminé</span>
                        @else
                            <span class="cp-chip cp-chip-gris">Brouillon</span>
                        @endif
                    </header>
                    <dl class="cp-ev-stats">
                        <div><dt>Liens</dt><dd>{{ $nb($ligne['liens']) }}</dd></div>
                        <div><dt>Clics</dt><dd>{{ $nb($ligne['clics']) }}</dd></div>
                        <div><dt>Ventes</dt><dd>{{ $nb($ligne['ventes']) }}</dd></div>
                        <div><dt>Commissions</dt><dd>{{ $fcfa($ligne['commissions']) }}</dd></div>
                    </dl>
                    <footer class="cp-ev-pied">
                        <span>
                            @if ($ligne['en_attente'] > 0)
                                <i class="bi bi-hourglass-split"></i> {{ $fcfa($ligne['en_attente']) }} en attente
                            @else
                                <i class="bi bi-check2-circle"></i> Rien en attente
                            @endif
                        </span>
                        <a href="{{ route('admin.evenements.show', $ev) }}#coparrainage" class="cp-btn">
                            <i class="bi bi-sliders"></i> Gérer
                        </a>
                    </footer>
                </article>
            @endforeach
        </div>
    @else
        <div class="cp-vide cp-vide-grand">
            <div class="cp-vide-icone"><i class="bi bi-megaphone"></i></div>
            <h4>Aucun événement ouvert au co-parrainage</h4>
            <p>Activez le co-parrainage depuis la fiche d'un événement payant : des affiliés pourront le promouvoir contre une commission.</p>
            <a href="{{ route('admin.evenements.index') }}" class="cp-btn cp-btn-plein"><i class="bi bi-arrow-right"></i> Choisir un événement</a>
        </div>
    @endif

    <div class="cp-colonnes">
        {{-- Classement des affiliés --}}
        <section class="cp-carte">
            <div class="cp-carte-tete">
                <h3><i class="bi bi-trophy"></i> Classement des affiliés</h3>
                <span class="cp-compte">{{ $liens->count() }}</span>
            </div>
            @forelse ($liens as $i => $lien)
                @php($ventes = (int) ($ventesParLien[$lien->id] ?? 0))
                <div class="cp-affilie {{ $lien->actif ? '' : 'est-inactif' }}">
                    <span class="cp-rang {{ $i < 3 && $ventes > 0 ? 'cp-rang-' . ($i + 1) : '' }}">{{ $i + 1 }}</span>
                    <span class="cp-avatar">{{ $initiales($lien->affiliate?->nom_complet) }}</span>
                    <div class="cp-affilie-info">
                        <div class="cp-affilie-nom">
                            {{ $lien->affiliate?->nom_complet ?? '—' }}
                            @unless ($lien->actif)<span class="cp-chip cp-chip-gris">Inactif</span>@endunless
                        </div>
                        <div class="cp-affilie-sous">{{ Str::limit($lien->evenement?->titre ?? '—', 28) }} · {{ $taux($lien->pourcentage) }}</div>
                        <div class="cp-barre"><span style="width: {{ round($ventes / $maxVentes * 100) }}%"></span></div>
                    </div>
                    <div class="cp-affilie-chiffres">
                        <strong>{{ $nb($ventes) }} vente{{ $ventes > 1 ? 's' : '' }}</strong>
                        <span>{{ $nb($lien->clics) }} clic{{ $lien->clics > 1 ? 's' : '' }} · {{ $fcfa($commissionsParLien[$lien->id] ?? 0) }}</span>
                    </div>
                </div>
            @empty
                <div class="cp-vide">
                    <div class="cp-vide-icone"><i class="bi bi-link-45deg"></i></div>
                    <p>Aucun affilié n'a encore généré de lien.</p>
                </div>
            @endforelse
        </section>

        {{-- Commissions --}}
        <section class="cp-carte">
            <div class="cp-carte-tete">
                <h3><i class="bi bi-receipt"></i> Dernières commissions</h3>
                <span class="cp-compte">{{ $commissions->count() }}</span>
            </div>
            @if ($commissions->count() > 0)
                <div class="cp-filtres" role="tablist" aria-label="Filtrer les commissions">
                    <button type="button" class="actif" data-filtre="">Toutes</button>
                    @foreach (['en_attente', 'disponible', 'paye'] as $cle)
                        @if ($commissions->where('statut', $cle)->count())
                            <button type="button" data-filtre="{{ $cle }}">{{ $statutsCommission[$cle][0] }}</button>
                        @endif
                    @endforeach
                </div>
                <div class="cp-commissions">
                    @foreach ($commissions as $commission)
                        @php([$libelle, $classe] = $statutsCommission[$commission->statut] ?? $statutsCommission['annule'])
                        <div class="cp-commission" data-statut="{{ $commission->statut }}">
                            <span class="cp-avatar cp-avatar-petit">{{ $initiales($commission->affiliate?->nom_complet) }}</span>
                            <div class="cp-commission-info">
                                <div class="cp-affilie-nom">{{ $commission->affiliate?->nom_complet ?? '—' }}</div>
                                <div class="cp-affilie-sous">{{ $commission->created_at->isoFormat('D MMM YYYY') }} · {{ Str::limit($commission->evenement?->titre ?? '—', 24) }}</div>
                            </div>
                            <div class="cp-commission-droite">
                                <strong>{{ $fcfa($commission->montant) }}</strong>
                                <span class="cp-chip {{ $classe }}">{{ $libelle }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="cp-vide">
                    <div class="cp-vide-icone"><i class="bi bi-cash-coin"></i></div>
                    <p>Aucune commission de co-parrainage pour le moment.</p>
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    // Filtre des commissions par statut
    document.querySelectorAll('.cp-filtres button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const filtre = btn.dataset.filtre;
            btn.parentElement.querySelectorAll('button').forEach(b => b.classList.toggle('actif', b === btn));
            document.querySelectorAll('.cp-commission').forEach(function (ligne) {
                ligne.hidden = !!filtre && ligne.dataset.statut !== filtre;
            });
        });
    });
</script>

<style>
.cp { --cp-violet: var(--violet); --cp-bord: #ebe7ef; --cp-texte2: var(--gris-fonce); }
.cp h2, .cp h3, .cp h4 { margin: 0; }

/* En-tête */
.cp-hero {
    display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;
    padding: 1.6rem 1.8rem; margin-bottom: 1.25rem; border-radius: 18px; color: #fff;
    background: radial-gradient(circle at 85% 20%, rgba(153,114,176,.55), transparent 55%), linear-gradient(135deg, #2c1745 0%, var(--violet) 100%);
}
.cp-hero-texte { max-width: 520px; }
.cp-hero-chip { display: inline-block; padding: .25rem .7rem; border-radius: 999px; background: rgba(255,255,255,.14); font-size: .75rem; font-weight: 600; margin-bottom: .6rem; }
.cp-hero h2 { font-size: 1.35rem; font-weight: 700; margin-bottom: .35rem; }
.cp-hero p { margin: 0; font-size: .88rem; color: rgba(255,255,255,.75); }
.cp-entonnoir { display: flex; align-items: center; gap: .6rem; }
.cp-etape { display: flex; flex-direction: column; align-items: center; min-width: 84px; padding: .7rem .9rem; border-radius: 14px; background: rgba(255,255,255,.1); }
.cp-etape-accent { background: var(--accent); color: var(--sombre); }
.cp-etape-val { font-size: 1.45rem; font-weight: 800; line-height: 1.1; }
.cp-etape-lib { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; opacity: .8; }
.cp-fleche { opacity: .5; }

/* Indicateurs */
.cp-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }
.cp-kpi { display: flex; gap: .85rem; align-items: center; padding: 1rem 1.1rem; background: #fff; border: 1px solid var(--cp-bord); border-radius: 14px; }
.cp-kpi-icone { width: 44px; height: 44px; flex: none; border-radius: 12px; display: grid; place-items: center; font-size: 1.15rem; color: var(--c); background: color-mix(in srgb, var(--c) 12%, transparent); }
.cp-kpi-lib { font-size: .74rem; color: var(--cp-texte2); font-weight: 600; }
.cp-kpi-val { font-size: 1.3rem; font-weight: 800; color: var(--sombre); line-height: 1.2; }
.cp-kpi-val small { font-size: .85rem; color: var(--cp-texte2); font-weight: 600; }
.cp-kpi-sous { font-size: .72rem; color: var(--cp-texte2); }

/* Titres de section */
.cp-section-titre { display: flex; justify-content: space-between; align-items: center; margin-bottom: .9rem; gap: 1rem; }
.cp-section-titre h3, .cp-carte-tete h3 { font-size: 1rem; font-weight: 700; color: var(--sombre); display: flex; align-items: center; gap: .5rem; }
.cp-carte-tete h3 .bi { color: var(--cp-violet); }
.cp-compte { display: inline-grid; place-items: center; min-width: 24px; height: 24px; padding: 0 .4rem; border-radius: 999px; background: var(--violet-pale); color: var(--cp-violet); font-size: .75rem; font-weight: 700; }
.cp-lien { color: var(--cp-violet); font-size: .85rem; font-weight: 600; text-decoration: none; white-space: nowrap; }
.cp-lien:hover { color: #3d1b5e; }

/* Cartes événements */
.cp-evenements { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.cp-ev { background: #fff; border: 1px solid var(--cp-bord); border-radius: 16px; padding: 1.1rem; display: flex; flex-direction: column; gap: 1rem; transition: box-shadow .2s, transform .2s; }
.cp-ev:hover { box-shadow: 0 12px 28px rgba(33,28,49,.08); transform: translateY(-2px); }
.cp-ev-tete { display: flex; align-items: flex-start; gap: .8rem; }
.cp-ev-date { flex: none; width: 50px; text-align: center; padding: .35rem 0; border-radius: 12px; background: var(--violet-pale); color: var(--cp-violet); line-height: 1.1; }
.cp-ev-date span { display: block; font-size: 1.2rem; font-weight: 800; }
.cp-ev-date small { font-size: .65rem; font-weight: 700; }
.cp-ev-info { flex: 1; min-width: 0; }
.cp-ev-info h4 { font-size: .98rem; font-weight: 700; color: var(--sombre); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-ev-info p { margin: .15rem 0 0; font-size: .78rem; color: var(--cp-texte2); }
.cp-ev-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin: 0; padding: .8rem; border-radius: 12px; background: #faf8fc; }
.cp-ev-stats div { text-align: center; }
.cp-ev-stats dt { font-size: .68rem; font-weight: 600; color: var(--cp-texte2); text-transform: uppercase; letter-spacing: .03em; }
.cp-ev-stats dd { margin: 0; font-size: 1rem; font-weight: 800; color: var(--sombre); white-space: nowrap; }
.cp-ev-pied { display: flex; justify-content: space-between; align-items: center; gap: .5rem; font-size: .8rem; color: var(--cp-texte2); }

/* Boutons et pastilles */
.cp-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .9rem; border-radius: 10px; border: 1px solid var(--ligne); color: var(--cp-violet); font-size: .82rem; font-weight: 600; text-decoration: none; background: #fff; transition: background .2s, border-color .2s; }
.cp-btn:hover { background: var(--violet-pale); border-color: var(--cp-violet); color: var(--cp-violet); }
.cp-btn-plein { background: var(--cp-violet); border-color: var(--cp-violet); color: #fff; }
.cp-btn-plein:hover { background: #3d1b5e; color: #fff; }
.cp-chip { display: inline-block; padding: .18rem .55rem; border-radius: 999px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
.cp-chip-vert { background: rgba(18,151,110,.12); color: var(--vert); }
.cp-chip-teal { background: rgba(66,140,121,.14); color: #2f6f5f; }
.cp-chip-orange { background: rgba(254,170,20,.18); color: #a35f00; }
.cp-chip-rouge { background: rgba(220,53,69,.12); color: #c82333; }
.cp-chip-gris { background: #f0eef2; color: #7a7482; }

/* Colonnes du bas */
.cp-colonnes { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
.cp-carte { background: #fff; border: 1px solid var(--cp-bord); border-radius: 16px; padding: 1.1rem; }
.cp-carte-tete { display: flex; justify-content: space-between; align-items: center; margin-bottom: .8rem; }

/* Classement */
.cp-affilie { display: flex; align-items: center; gap: .75rem; padding: .7rem .4rem; border-top: 1px solid var(--violet-pale); }
.cp-affilie:first-of-type { border-top: 0; }
.cp-affilie.est-inactif { opacity: .55; }
.cp-rang { width: 22px; flex: none; text-align: center; font-size: .8rem; font-weight: 800; color: #b3adb9; }
.cp-rang-1 { color: #d4a300; } .cp-rang-2 { color: #8f99a3; } .cp-rang-3 { color: #b0703a; }
.cp-avatar { flex: none; width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; background: var(--violet-pale); color: var(--cp-violet); font-size: .8rem; font-weight: 700; }
.cp-avatar-petit { width: 34px; height: 34px; font-size: .74rem; }
.cp-affilie-info, .cp-commission-info { flex: 1; min-width: 0; }
.cp-affilie-nom { font-size: .88rem; font-weight: 700; color: var(--sombre); display: flex; align-items: center; gap: .4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-affilie-sous { font-size: .75rem; color: var(--cp-texte2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-barre { height: 4px; margin-top: .4rem; border-radius: 99px; background: #f1edf5; overflow: hidden; }
.cp-barre span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--violet-clair), var(--violet)); }
.cp-affilie-chiffres { text-align: right; flex: none; }
.cp-affilie-chiffres strong { display: block; font-size: .88rem; color: var(--sombre); }
.cp-affilie-chiffres span { font-size: .72rem; color: var(--cp-texte2); }

/* Commissions */
.cp-filtres { display: flex; gap: .35rem; flex-wrap: wrap; margin-bottom: .6rem; }
.cp-filtres button { border: 1px solid var(--cp-bord); background: #fff; color: var(--cp-texte2); border-radius: 999px; padding: .3rem .8rem; font-size: .75rem; font-weight: 600; }
.cp-filtres button.actif { background: var(--cp-violet); border-color: var(--cp-violet); color: #fff; }
.cp-commissions { max-height: 420px; overflow-y: auto; }
.cp-commission { display: flex; align-items: center; gap: .75rem; padding: .7rem .4rem; border-top: 1px solid var(--violet-pale); }
.cp-commission:first-child { border-top: 0; }
.cp-commission-droite { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: .2rem; }
.cp-commission-droite strong { font-size: .92rem; color: var(--sombre); }

/* États vides */
.cp-vide { text-align: center; padding: 1.8rem 1rem; color: var(--cp-texte2); font-size: .85rem; }
.cp-vide p { margin: 0 auto; max-width: 380px; }
.cp-vide-grand { background: #fff; border: 1px dashed var(--ligne); border-radius: 16px; padding: 2.5rem 1rem; margin-bottom: 2rem; }
.cp-vide-grand h4 { font-size: 1rem; font-weight: 700; color: var(--sombre); margin-bottom: .35rem; }
.cp-vide-grand p { margin-bottom: 1rem; }
.cp-vide-icone { width: 56px; height: 56px; margin: 0 auto .8rem; border-radius: 16px; display: grid; place-items: center; background: var(--violet-pale); color: var(--cp-violet); font-size: 1.5rem; }

@media (max-width: 1199.98px) { .cp-kpis { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 991.98px) { .cp-colonnes { grid-template-columns: 1fr; } }
@media (max-width: 575.98px) {
    .cp-hero { padding: 1.2rem; }
    .cp-entonnoir { width: 100%; justify-content: space-between; }
    .cp-etape { min-width: 0; flex: 1; padding: .6rem .4rem; }
    .cp-etape-val { font-size: 1.2rem; }
    .cp-kpis { gap: .6rem; }
    .cp-kpi { flex-direction: column; align-items: flex-start; gap: .5rem; padding: .85rem; }
    .cp-kpi-val { font-size: 1.1rem; }
    .cp-evenements { grid-template-columns: 1fr; }
    .cp-ev-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endsection
