@extends('layouts.app')

@php
    $edition = $edition ?? null;
    $evenement = $evenement ?? null;
@endphp

@section('title', $edition ? 'Modifier l\'événement' : 'Créer un événement')

@section('page-title', $edition ? 'Modifier l\'événement' : 'Créer un événement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $edition ? 'Modifier' : 'Créer' }}</li>
@endsection

@section('topbar-actions')
<a href="{{ route('admin.evenements.index') }}" class="btn btn-secondary-custom">
    <i class="bi bi-arrow-left me-1"></i> Retour
</a>
@endsection

@php
    $ancien = collect(session()->getOldInput())->except(['_token'])->all();
    $minDate = now()->format('Y-m-d\TH:i');

    $etapes = [
        'type' => 'Type',
        'infos' => 'Informations',
        'acces' => 'Gratuit ou payant',
        'dates' => 'Dates',
        'lieu' => 'Lieu et capacité',
        'billets' => 'Billets',
        'recap' => 'Options et récap',
    ];

    // Champs de chaque étape : sert à rouvrir l'étape en erreur après validation serveur.
    $champsParEtape = [
        'type' => ['type_evenement', 'categorie', 'autre_categorie'],
        'infos' => ['titre', 'description', 'image'],
        'acces' => ['gratuit'],
        'dates' => ['duree', 'date_event', 'jours'],
        'lieu' => ['lieu', 'capacite', 'meme_capacite', 'capacites_jours'],
        'billets' => ['tarifs', 'generer_vip'],
        'recap' => ['co_parrainage_actif', 'co_parrainage_pourcentage', 'statut'],
    ];
    $etapeDuChamp = fn (string $cle) => collect($champsParEtape)
        ->search(fn ($champs) => in_array(explode('.', $cle)[0], $champs, true));
    $erreursEtape = fn (string $etape) => collect($errors->getMessages())
        ->filter(fn ($messages, $cle) => $etapeDuChamp($cle) === $etape)
        ->flatten()
        ->unique()
        ->values();
    $etapeErreur = collect(array_keys($errors->getMessages()))
        ->map($etapeDuChamp)
        ->filter()
        ->sortBy(fn ($etape) => array_search($etape, array_keys($etapes), true))
        ->first();

    $types = [
        'spectacle' => ['Spectacle / Soirée', 'Concert, gala, festival, match… L\'acheteur « achète un billet ».', 'bi-music-note-beamed'],
        'formation' => ['Formation', 'Atelier, séminaire, cours. Le participant « s\'inscrit ».', 'bi-mortarboard'],
        'conference' => ['Conférence', 'Panel, colloque, webinaire. Le participant « réserve sa place ».', 'bi-easel'],
    ];

    $categories = [
        'Festival' => 'Festival',
        'Gala' => 'Gala',
        'Match' => 'Match',
        'Webinaire' => 'Webinaire',
        'Autre' => 'Autre',
    ];
@endphp

@section('styles')
<style>
.wz{--wz-violet:#542680;--wz-violet-clair:#9972B0;--wz-vert:#428c79;--wz-bord:#e6e3eb;--wz-texte2:#6b6474}
.wz-layout{display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start}
.wz-side{flex:1 1 220px;max-width:260px;background:#fff;border-radius:14px;padding:12px;border:1px solid var(--wz-bord);position:sticky;top:90px}
.wz-main{flex:999 1 560px;min-width:0;background:#fff;border-radius:14px;border:1px solid var(--wz-bord);padding:28px}
@media (max-width:991.98px){.wz-side{position:static;max-width:none;display:flex;overflow-x:auto;gap:4px}.wz-stepbtn{width:auto!important;white-space:nowrap}}
@media (max-width:575.98px){.wz-main{padding:18px}}
.wz-stepbtn{display:flex;align-items:center;gap:12px;width:100%;padding:8px 10px;border:0;background:none;border-radius:10px;text-align:left;font-size:.9rem;color:#5d5766;min-height:44px}
.wz-stepbtn:hover{background:#f6f2f9}
.wz-stepbtn.is-on{background:#f1ebf6;color:var(--wz-violet);font-weight:600}
.wz-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:600;background:#edecf0;color:#5d5766;flex:none}
.wz-stepbtn.is-on .wz-dot{background:var(--wz-violet);color:#fff}
.wz-stepbtn.is-done .wz-dot{background:var(--wz-vert);color:#fff}
.wz-progress{height:6px;background:#edecf0;border-radius:99px;overflow:hidden;margin-bottom:10px}
.wz-bar{height:100%;width:0;background:var(--wz-violet);border-radius:99px;transition:width .25s}
.wz-kicker{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;font-size:.75rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:var(--wz-texte2);margin-bottom:18px}
.wz-kicker span:last-child{text-transform:none;letter-spacing:0;font-weight:400}
.wz-h{font-size:1.35rem;font-weight:700;margin:0 0 6px}
.wz-sub{color:var(--wz-texte2);margin:0 0 22px;font-size:.9rem;line-height:1.5}
.wz-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
.wz-card{display:block;margin:0;cursor:pointer}
.wz-card input,.wz-chip input{position:absolute;opacity:0;pointer-events:none}
.wz-card-body{display:flex;flex-direction:column;gap:8px;height:100%;border:2px solid var(--wz-bord);border-radius:14px;padding:20px;background:#fff;transition:border-color .15s,box-shadow .15s}
.wz-card:hover .wz-card-body{border-color:var(--wz-violet-clair)}
.wz-card input:checked + .wz-card-body{border-color:var(--wz-violet);background:#f8f4fb;box-shadow:0 0 0 3px rgba(84,38,128,.12)}
.wz-card input:focus-visible + .wz-card-body,.wz-chip input:focus-visible + span{outline:3px solid rgba(84,38,128,.45);outline-offset:2px}
.wz-card-t{font-weight:600;font-size:1rem;color:#211C31}
.wz-card-d{font-size:.82rem;color:var(--wz-texte2);line-height:1.45}
.wz-ico{width:42px;height:42px;border-radius:10px;background:#f1ebf6;color:var(--wz-violet);display:flex;align-items:center;justify-content:center;font-size:1.25rem}
.wz-chips{display:flex;flex-wrap:wrap;gap:8px;border:0;padding:0;margin:0}
.wz-chip{margin:0;cursor:pointer}
.wz-chip span{display:inline-flex;align-items:center;min-height:42px;border:1.5px solid #d9d4df;border-radius:99px;padding:8px 16px;background:#fff;font-size:.9rem;color:#211C31}
.wz-chip:hover span{border-color:var(--wz-violet-clair)}
.wz-chip input:checked + span{background:var(--wz-violet);border-color:var(--wz-violet);color:#fff}
.wz .form-control,.wz .form-select{min-height:44px;border-radius:10px}
.wz-drop{display:block;border:2px dashed #cfc7d8;border-radius:14px;padding:26px 16px;text-align:center;color:var(--wz-texte2);background:#faf9fb;cursor:pointer;margin:0}
.wz-drop.is-over{border-color:var(--wz-violet);background:#f6f0fa}
.wz-drop img{max-height:160px;max-width:100%;border-radius:10px;margin-bottom:10px}
.wz-preview{border:1px solid var(--wz-bord);border-radius:14px;overflow:hidden;max-width:320px}
.wz-preview-img{height:130px;background:#ece6f2 center/cover no-repeat;display:flex;align-items:center;justify-content:center;color:var(--wz-texte2);font-size:.8rem}
.wz-block{border:1px solid var(--wz-bord);border-radius:12px;padding:16px;margin-bottom:16px}
.wz-block-pack{border-color:var(--wz-violet-clair)}
.wz-block-h{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap}
.wz-trow{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr) minmax(0,.8fr) 44px;gap:8px;align-items:center;margin-bottom:8px}
.wz-trow .wz-perso{grid-column:1 / -1}
.wz-thead{font-size:.75rem;color:var(--wz-texte2);font-weight:600;margin-bottom:4px}
@media (max-width:575.98px){.wz-trow{grid-template-columns:minmax(0,1fr) minmax(0,1fr) 44px}.wz-trow select{grid-column:1 / -1}.wz-thead{display:none}}
.wz-prix-qte{display:grid;grid-template-columns:repeat(2,minmax(0,200px));gap:10px}
.wz-prix-qte label{font-size:.75rem;font-weight:600;color:var(--wz-texte2);display:flex;flex-direction:column;gap:4px}
.wz-pack{padding:10px 0;border-bottom:1px dashed #eeebf1}
.wz-pack:last-child{border-bottom:0}
.wz-x{width:44px;height:44px;border:0;background:#f6f5f8;border-radius:10px;color:var(--wz-texte2);display:flex;align-items:center;justify-content:center}
.wz-x:hover{background:#f6e9e8;color:#a5322a}
.wz-link{color:var(--wz-violet);font-weight:600;padding:8px 4px;background:none;border:0}
.wz-link:hover{text-decoration:underline}
.wz-info{background:#eef7f4;border-radius:10px;padding:12px 14px;font-size:.85rem;color:#245a4c;line-height:1.5}
.wz-alert{background:#fdf0ef;border-radius:10px;padding:12px 14px;font-size:.85rem;color:#9c2f27;margin-bottom:16px}
.wz-alert ul{margin:4px 0 0;padding-left:18px}
.wz-gauge{display:flex;justify-content:space-between;gap:8px;font-size:.9rem;padding:8px 0;border-bottom:1px dashed #eeebf1}
.wz-ok{color:#245a4c;font-weight:600}.wz-ko{color:#a5322a;font-weight:600}
.wz-badge{font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:99px}
.wz-badge-gratuit{background:#b2e0d6;color:#1d4a3f}
.wz-badge-pack{background:#edecf0;color:#4a4453}
.wz-dayrow{display:grid;grid-template-columns:80px minmax(0,1fr) 44px;gap:10px;align-items:center;margin-bottom:10px}
.wz-daycap{display:grid;grid-template-columns:minmax(0,1fr) 150px;gap:10px;align-items:center;margin-bottom:8px;font-size:.9rem}
.wz-recap{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:14px}
.wz-rc{border:1px solid var(--wz-bord);border-radius:12px;padding:14px 16px}
.wz-rc-h{display:flex;justify-content:space-between;align-items:center;font-weight:600;margin-bottom:6px}
.wz-rc p{margin:0;padding:7px 0;font-size:.85rem;color:#4a4453;line-height:1.45}
.wz-rc p + p{border-top:1px solid #eeebf1}
.wz-rc-edit{display:inline-flex;align-items:center;gap:5px;border:0;border-radius:99px;background:#FED514;color:#211C31;font-size:.78rem;font-weight:600;padding:5px 12px;min-height:32px}
.wz-rc-edit:hover{background:#f0c800}
.wz-rc-edit:focus-visible{outline:3px solid rgba(84,38,128,.45);outline-offset:2px}
.wz-rc-img{display:block;width:100%;height:130px;object-fit:cover;border-radius:10px;margin:4px 0 10px}
.wz-rc-img--vide{display:flex;align-items:center;justify-content:center;background:#ece6f2;color:var(--wz-texte2);font-size:.8rem}
.wz-nav{display:flex;justify-content:space-between;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid #eeebf1;flex-wrap:wrap}
.wz-banner{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;background:#fff8d6;border-radius:10px;padding:10px 14px;font-size:.85rem;margin-bottom:16px}
</style>
@endsection

@section('content')
<div class="page-content">
    @if($edition && ($edition['aDesBillets'] ?? false))
        <div class="alert alert-warning" role="status">
            Des billets ont déjà été vendus. Si vous modifiez les dates, les acheteurs reçoivent leurs billets mis à jour par email et leurs billets restent valables sur le jour déplacé. Un changement de prix ne s'applique qu'aux prochains achats.
        </div>
    @endif
    @if($edition && $joursAvecScansReussis->isNotEmpty())
        <div class="alert alert-info" role="status">
            Les dates ayant déjà enregistré un scan réussi sont verrouillées. Vous pouvez toujours modifier les autres dates de l'événement.
        </div>
    @endif
    <form id="wz-form" class="wz" action="{{ $edition ? route('admin.evenements.update', $evenement->id) : route('admin.evenements.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @if($edition)
            @method('PUT')
        @endif
        <div class="wz-layout">
            <nav class="wz-side" aria-label="Étapes">
                @foreach($etapes as $id => $libelle)
                    <button type="button" class="wz-stepbtn" data-aller="{{ $id }}">
                        <span class="wz-dot" data-dot>{{ $loop->iteration }}</span><span>{{ $libelle }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="wz-main">
                <div class="wz-progress"><div class="wz-bar" id="wz-bar"></div></div>
                <div class="wz-kicker"><span id="wz-kicker">Étape 1</span><span id="wz-sauvegarde" aria-live="polite"></span></div>

                <div id="wz-brouillon" class="wz-banner d-none">
                    <span><i class="bi bi-clock-history me-1"></i>Nous avons repris votre saisie précédente.</span>
                    <button type="button" class="wz-link" id="wz-effacer">Repartir de zéro</button>
                </div>

                {{-- 1. TYPE --}}
                <section class="wz-step" data-etape="type">
                    <h2 class="wz-h">Quel type d'événement organisez-vous ?</h2>
                    <p class="wz-sub">Les textes vus par vos acheteurs s'adaptent au type : « Acheter un billet » ou « S'inscrire ».</p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('type')])
                    <div class="wz-cards" role="radiogroup" aria-label="Type d'événement">
                        @foreach($types as $valeur => [$libelle, $description, $icone])
                            <label class="wz-card">
                                <input type="radio" name="type_evenement" value="{{ $valeur }}" @checked(old('type_evenement', 'spectacle') === $valeur)>
                                <span class="wz-card-body">
                                    <span class="wz-ico"><i class="bi {{ $icone }}" aria-hidden="true"></i></span>
                                    <span class="wz-card-t">{{ $libelle }}</span>
                                    <span class="wz-card-d">{{ $description }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <span class="form-label fw-semibold d-block" id="lbl-categorie">Catégorie</span>
                        <fieldset class="wz-chips" aria-labelledby="lbl-categorie">
                            @foreach($categories as $valeur => $libelle)
                                <label class="wz-chip">
                                    <input type="radio" name="categorie" value="{{ $valeur }}" @checked(old('categorie') === $valeur)>
                                    <span>{{ $libelle }}</span>
                                </label>
                            @endforeach
                        </fieldset>
                        <div id="wz-autre-cat" class="mt-3 d-none" style="max-width: 360px;">
                            <label class="form-label fw-semibold" for="autre_categorie">Précisez la catégorie</label>
                            <input type="text" class="form-control" id="autre_categorie" name="autre_categorie" maxlength="255" value="{{ old('autre_categorie') }}" placeholder="Ex. Salon professionnel">
                        </div>
                    </div>
                </section>

                {{-- 2. INFORMATIONS --}}
                <section class="wz-step d-none" data-etape="infos">
                    <h2 class="wz-h">Présentez votre événement</h2>
                    <p class="wz-sub">Ces informations apparaissent sur la page publique de l'événement.</p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('infos')])
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="titre">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre" maxlength="255" value="{{ old('titre') }}" placeholder="Ex. Festival des arts urbains">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="description">Description <span class="fw-normal text-muted">(facultatif)</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" maxlength="5000" placeholder="Programme, artistes, intervenants, ce qui est inclus…">{{ old('description') }}</textarea>
                                <div class="form-text"><span id="wz-desc-count">0</span> / 5000 caractères</div>
                            </div>
                            <div class="mb-1">
                                <span class="form-label fw-semibold d-block">Image de couverture <span class="fw-normal text-muted">(facultatif)</span></span>
                                <label class="wz-drop" for="image" id="wz-drop">
                                    <img id="wz-img" alt="Aperçu de l'image choisie" class="d-none">
                                    <span class="d-block fw-semibold text-dark mb-1" id="wz-drop-titre">Glissez une image ici ou cliquez pour choisir</span>
                                    <span class="d-block small">JPG ou PNG, 2 Mo maximum{{ $edition && $evenement->image ? ' · laissez vide pour garder l\'image actuelle' : '' }}</span>
                                </label>
                                <input type="file" class="visually-hidden" id="image" name="image" accept="image/*">
                                <div id="wz-img-trop-lourde" class="text-danger small mt-1 d-none">
                                    <i class="bi bi-exclamation-circle me-1"></i>Image trop lourde : le maximum est 2 Mo.
                                </div>
                                @if(! empty($ancien) && ! $edition)
                                    <div class="form-text">Pour des raisons de sécurité, l'image doit être choisie à nouveau après une erreur.</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <span class="form-label fw-semibold d-block">Aperçu</span>
                            <div class="wz-preview">
                                <div class="wz-preview-img" id="wz-apercu-img">Image de couverture</div>
                                <div class="p-3">
                                    <div class="small fw-semibold" style="color: var(--wz-violet);" id="wz-apercu-cat">Catégorie</div>
                                    <div class="fw-bold my-1" id="wz-apercu-titre">Titre de votre événement</div>
                                    <div class="small text-muted" id="wz-apercu-dates">Date à définir</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 3. GRATUIT OU PAYANT --}}
                <section class="wz-step d-none" data-etape="acces">
                    <h2 class="wz-h">L'événement est-il gratuit ou payant ?</h2>
                    <p class="wz-sub">Ce choix détermine la suite : un événement gratuit n'a pas d'étape « Billets ».</p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('acces')])
                    <div class="wz-cards" role="radiogroup" aria-label="Accès">
                        <label class="wz-card">
                            <input type="radio" name="gratuit" value="1" @checked(old('gratuit') === '1')>
                            <span class="wz-card-body">
                                <span class="wz-ico"><i class="bi bi-gift" aria-hidden="true"></i></span>
                                <span class="wz-card-t">Gratuit</span>
                                <span class="wz-card-d">Inscription libre. Un seul billet, valable pour tous les jours de l'événement.</span>
                            </span>
                        </label>
                        <label class="wz-card">
                            <input type="radio" name="gratuit" value="0" @checked(old('gratuit', '0') === '0')>
                            <span class="wz-card-body">
                                <span class="wz-ico"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                                <span class="wz-card-t">Payant</span>
                                <span class="wz-card-d">Paiement en ligne par mobile money ou carte bancaire.</span>
                            </span>
                        </label>
                    </div>
                </section>

                {{-- 4. DATES --}}
                <section class="wz-step d-none" data-etape="dates">
                    <h2 class="wz-h">Quand a lieu l'événement ?</h2>
                    <p class="wz-sub">Sur plusieurs jours, chaque jour peut avoir ses propres billets et sa propre jauge.</p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('dates')])
                    <div class="wz-cards mb-4" role="radiogroup" aria-label="Durée">
                        <label class="wz-card">
                            <input type="radio" name="duree" value="un" @checked(old('duree', 'un') === 'un')>
                            <span class="wz-card-body">
                                <span class="wz-ico"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
                                <span class="wz-card-t">Un seul jour</span>
                                <span class="wz-card-d">Une date et une heure de début.</span>
                            </span>
                        </label>
                        <label class="wz-card">
                            <input type="radio" name="duree" value="plusieurs" @checked(old('duree') === 'plusieurs')>
                            <span class="wz-card-body">
                                <span class="wz-ico"><i class="bi bi-calendar-range" aria-hidden="true"></i></span>
                                <span class="wz-card-t">Plusieurs jours</span>
                                <span class="wz-card-d">Festival, formation sur plusieurs sessions… (jusqu'à {{ \App\Http\Controllers\EvenementController::MAX_JOURS }} jours)</span>
                            </span>
                        </label>
                    </div>
                    <div id="wz-date-unique" style="max-width: 320px;">
                        <label class="form-label fw-semibold" for="date_event">Date et heure de début</label>
                        <input type="datetime-local" class="form-control" id="date_event" name="date_event" value="{{ old('date_event') }}" @unless($edition) min="{{ $minDate }}" @endunless>
                    </div>
                    <div id="wz-dates-multi" class="d-none" style="max-width: 520px;">
                        <div id="wz-jours"></div>
                        <button type="button" class="wz-link" id="wz-ajout-jour"><i class="bi bi-plus-lg me-1"></i>Ajouter un jour</button>
                        <div class="form-text">Les jours sont remis dans l'ordre chronologique automatiquement.</div>
                    </div>
                </section>

                {{-- 5. LIEU ET CAPACITE --}}
                <section class="wz-step d-none" data-etape="lieu">
                    <h2 class="wz-h">Où et combien de personnes ?</h2>
                    <p class="wz-sub">La capacité limite le nombre de places vendues.</p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('lieu')])
                    <div class="mb-3" style="max-width: 520px;">
                        <label class="form-label fw-semibold" for="lieu">Lieu</label>
                        <input type="text" class="form-control" id="lieu" name="lieu" maxlength="255" value="{{ old('lieu') }}" placeholder="Ex. Palais des Congrès, Cotonou">
                    </div>
                    {{-- Capacité et « même capacité tous les jours » sur la même ligne --}}
                    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                        <div style="width: 240px; max-width: 100%;">
                            <label class="form-label fw-semibold" for="capacite" id="wz-cap-label">Capacité</label>
                            <input type="number" class="form-control" id="capacite" name="capacite" min="1" value="{{ old('capacite') }}" placeholder="Ex. 500">
                        </div>
                        <div id="wz-cap-multi" class="d-none d-flex align-items-center" style="min-height: 44px;">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="meme_capacite" name="meme_capacite" value="1" @checked(empty($ancien) || old('meme_capacite'))>
                                <label class="form-check-label" for="meme_capacite">Même capacité tous les jours</label>
                            </div>
                        </div>
                    </div>
                    <div id="wz-cap-jours" class="wz-block d-none" style="max-width: 520px;"></div>
                </section>

                {{-- 6. BILLETS --}}
                <section class="wz-step d-none" data-etape="billets">
                    <h2 class="wz-h">Vos billets</h2>
                    <p class="wz-sub" id="wz-billets-sub"></p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('billets')])

                    <div id="wz-billets-mono">
                        <div class="wz-block">
                            <div class="wz-trow wz-thead"><span>Nom</span><span>Prix (F CFA)</span><span>Qté</span><span></span></div>
                            <div id="wz-tarifs-unique"></div>
                            <button type="button" class="wz-link" data-ajout-tarif="unique"><i class="bi bi-plus-lg me-1"></i>Ajouter un tarif</button>
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="generer_vip" name="generer_vip" value="1" @checked(old('generer_vip'))>
                                <label class="form-check-label" for="generer_vip">Créer automatiquement un tarif VIP au double du prix du premier tarif</label>
                            </div>
                        </div>
                        <div class="wz-gauge"><span>Total des places</span><span id="wz-jauge-mono"></span></div>
                    </div>

                    <div id="wz-billets-multi" class="d-none">
                        <div id="wz-billets-jours"></div>
                        <div class="wz-block wz-block-pack">
                            <div class="wz-block-h">
                                <div><span class="fw-semibold">Packs</span> <span class="text-muted small">· facultatifs, valables tous les jours</span></div>
                            </div>
                            <div id="wz-packs"></div>
                            <div id="wz-pack-info" class="wz-info mt-2 d-none"></div>
                        </div>
                        <div class="wz-block">
                            <div class="fw-semibold mb-1">Jauges par jour</div>
                            <div class="small text-muted mb-1">Billets du jour + packs, comparés à la capacité du jour.</div>
                            <div id="wz-jauges"></div>
                            <div id="wz-depassement" class="wz-alert mt-2 mb-0 d-none">Un jour dépasse sa capacité : réduisez les places ou augmentez la jauge à l'étape « Lieu et capacité ».</div>
                        </div>
                    </div>
                    <div class="form-text" id="wz-max-tarifs">Jusqu'à {{ \App\Http\Controllers\EvenementController::MAX_TARIFS }} tarifs par événement, VIP automatique compris.</div>
                </section>

                {{-- 7. OPTIONS ET RECAPITULATIF --}}
                <section class="wz-step d-none" data-etape="recap">
                    <h2 class="wz-h">Dernières options et récapitulatif</h2>
                    <p class="wz-sub">Vérifiez avant de publier. Chaque bloc peut être modifié.</p>
                    @include('evenements.partials.wizard-erreurs', ['messages' => $erreursEtape('recap')])
                    <div class="wz-block" id="wz-co">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="co_parrainage_actif" name="co_parrainage_actif" value="1" @checked(old('co_parrainage_actif'))>
                            <label class="form-check-label fw-semibold" for="co_parrainage_actif"><i class="bi bi-share me-1"></i>Ouvrir le co-parrainage</label>
                        </div>
                        <div class="form-text">Des affiliés partagent votre événement et touchent un pourcentage du prix de chaque billet vendu via leur lien.</div>
                        <div id="wz-co-champs" class="mt-3 d-none" style="max-width: 280px;">
                            <label class="form-label fw-semibold" for="co_parrainage_pourcentage">Commission par billet</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="1" max="{{ $maxCoParrainage }}" class="form-control" id="co_parrainage_pourcentage" name="co_parrainage_pourcentage" value="{{ old('co_parrainage_pourcentage') }}" placeholder="ex. 10">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Maximum {{ $maxCoParrainage }} % (100 % moins votre commission PaxEvent).</div>
                        </div>
                    </div>
                    <div class="wz-recap" id="wz-recap"></div>
                </section>

                <div class="wz-nav">
                    <div>
                        <button type="button" class="btn btn-secondary-custom" id="wz-prec">Précédent</button>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary-custom" id="wz-suiv">Continuer <i class="bi bi-arrow-right ms-1"></i></button>
                        <span id="wz-fin" class="d-none d-flex gap-2 flex-wrap">
                            <button type="submit" name="statut" value="brouillon" class="btn btn-secondary-custom">Enregistrer en brouillon</button>
                            <button type="submit" name="statut" value="publié" class="btn btn-primary-custom"><i class="bi bi-check-lg me-1"></i>Publier l'événement</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('wz-form');
    const ETAPES = @json(array_keys($etapes));
    const LIBELLES = @json($etapes);
    const CATEGORIES = @json($categories);
    const TYPES = @json(collect($types)->map(fn ($t) => $t[0]));
    const ANCIEN = @json((object) $ancien);
    const ETAPE_ERREUR = @json($etapeErreur);
    const MAX_JOURS = {{ \App\Http\Controllers\EvenementController::MAX_JOURS }};
    const MAX_TARIFS = {{ \App\Http\Controllers\EvenementController::MAX_TARIFS }};
    const MIN_DATE = @json($minDate);
    const CLE_BROUILLON = 'paxevent:creation-evenement:{{ Auth::id() }}';
    // Modification : verrous et données de l'événement existant (null à la création)
    const EDITION = @json($edition);
    const verrouJour = (date) => !!EDITION && !!date && EDITION.joursVerrouilles.includes(String(date).slice(0, 10));
    const infoTarif = (t) => (EDITION && t.tid && EDITION.tarifs[t.tid]) || null;
    const tarifVendu = (t) => (infoTarif(t) || {}).vendu > 0;
    const NOMS_BILLETS = ['Standard', 'VIP', 'VVIP', 'Étudiant', 'Prévente', 'Personnalisé'];
    const NOMS_PACKS = ['Pass complet', 'Pass VIP', 'Personnalisé'];
    const CHAMPS_SIMPLES = ['type_evenement', 'categorie', 'autre_categorie', 'titre', 'description', 'gratuit', 'duree',
        'date_event', 'lieu', 'capacite', 'meme_capacite', 'generer_vip', 'co_parrainage_actif', 'co_parrainage_pourcentage'];

    let sequence = 1;
    const nouvelId = () => sequence++;
    const etat = { jours: [], tarifsUnique: [], tarifsJours: [] };
    let courante = 'type';
    let urlImage = null;

    // ---------- Outils ----------
    const $ = (s, r) => (r || document).querySelector(s);
    const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const num = (v) => { const n = parseInt(v, 10); return isNaN(n) ? 0 : n; };
    const radio = (nom) => (form.querySelector('input[name="' + nom + '"]:checked') || {}).value;
    const estGratuit = () => radio('gratuit') === '1';
    const estMulti = () => radio('duree') === 'plusieurs';
    const fmtJour = (d) => { const x = new Date(d); return !d || isNaN(x) ? 'date à définir' : x.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' }); };
    const fmtDate = (d) => { const x = new Date(d); return !d || isNaN(x) ? 'date à définir' : fmtJour(d) + ' · ' + x.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }); };
    const fmtPrix = (p) => (num(p) === 0 ? 'Gratuit' : num(p).toLocaleString('fr-FR') + ' F');
    const nomTarif = (t) => (t.nom === 'Personnalisé' ? (t.nom_perso || 'Billet personnalisé') : t.nom);
    const capacite = () => num($('#capacite').value);
    const memeCapacite = () => $('#meme_capacite').checked;
    const capaciteJour = (j) => (memeCapacite() || j.cap === '' || j.cap == null ? capacite() : num(j.cap));
    const tarifsActifs = () => (estMulti() ? etat.tarifsJours : etat.tarifsUnique);
    const nbTarifs = () => tarifsActifs().length + (!estMulti() && $('#generer_vip').checked ? 1 : 0);
    const nouveauTarif = (extra) => Object.assign({ id: nouvelId(), nom: 'Standard', nom_perso: '', prix: '', qte: '' }, extra || {});
    // Multijours : les deux packs proposés (facultatifs).
    const PACKS = ['Pack Simple', 'Pack VIP'];

    // Multijours : exactement un tarif par jour (créé vide si besoin), et
    // aucun tarif rattaché à un jour supprimé.
    function assurerUnTarifParJour() {
        const vus = new Set();
        etat.tarifsJours = etat.tarifsJours.filter((t) => {
            if (t.jourId === 'pack') return PACKS.includes(t.nom) || !!t.tid;
            if (!etat.jours.some((j) => j.id === t.jourId)) return false;
            if (vus.has(t.jourId) && !t.tid) return false;
            vus.add(t.jourId);
            return true;
        });
        etat.jours.forEach((j) => {
            if (!vus.has(j.id)) etat.tarifsJours.push(nouveauTarif({ nom: 'Jour', jourId: j.id }));
        });
    }

    // ---------- État initial : erreur serveur > brouillon > valeurs par défaut ----------
    function chargerDepuisAncien(a) {
        const idParIndex = {};
        const caps = a.capacites_jours || {};
        etat.jours = Object.entries(a.jours || {}).map(([index, date]) => {
            const jour = { id: nouvelId(), date: date || '', cap: caps[index] ?? '', orig: date || '' };
            idParIndex[index] = jour.id;
            return jour;
        });
        const tarifs = Object.values(a.tarifs || {}).map((t) => ({ tid: t.id ? Number(t.id) : null, nom: t.nom || 'Standard', nom_perso: t.nom_perso || '', prix: t.prix ?? '', qte: t.qte ?? '', jour: t.jour }));
        if (a.duree === 'plusieurs') {
            etat.tarifsJours = tarifs.map((t) => nouveauTarif(Object.assign(t, { jourId: t.jour === 'pack' ? 'pack' : (idParIndex[t.jour] ?? 'pack') })));
        } else {
            etat.tarifsUnique = tarifs.map((t) => nouveauTarif(t));
        }
    }

    function lireBrouillon() {
        try { return JSON.parse(localStorage.getItem(CLE_BROUILLON) || 'null'); } catch (e) { return null; }
    }

    function appliquerBrouillon(b) {
        Object.entries(b.champs || {}).forEach(([nom, valeur]) => {
            const champs = $$('[name="' + nom + '"]', form);
            champs.forEach((el) => {
                if (el.type === 'radio') el.checked = el.value === valeur;
                else if (el.type === 'checkbox') el.checked = !!valeur;
                else el.value = valeur ?? '';
            });
        });
        etat.jours = b.etat.jours || [];
        etat.tarifsUnique = b.etat.tarifsUnique || [];
        etat.tarifsJours = b.etat.tarifsJours || [];
        sequence = Math.max(sequence, ...[...etat.jours, ...etat.tarifsUnique, ...etat.tarifsJours].map((x) => x.id + 1), 1);
    }

    let minuteur = null;
    function sauvegarder() {
        if (EDITION) return; // en modification, rien n'est gardé dans le navigateur
        clearTimeout(minuteur);
        minuteur = setTimeout(() => {
            const champs = {};
            CHAMPS_SIMPLES.forEach((nom) => {
                const el = form.elements[nom];
                if (!el) return;
                if (el instanceof RadioNodeList) champs[nom] = radio(nom) ?? null;
                else if (el.type === 'checkbox') champs[nom] = el.checked;
                else champs[nom] = el.value;
            });
            try {
                localStorage.setItem(CLE_BROUILLON, JSON.stringify({ champs, etat, etape: courante }));
                $('#wz-sauvegarde').textContent = 'Saisie gardée à ' + new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } catch (e) { /* stockage indisponible : on continue sans brouillon */ }
        }, 400);
    }

    // ---------- Rendu des parties dynamiques ----------
    // Modification : identifiant du tarif existant et attributs de verrouillage.
    const champId = (t, index) => (t.tid ? '<input type="hidden" name="tarifs[' + index + '][id]" value="' + t.tid + '">' : '');
    const attrPrix = (t) => '';
    const attrQte = (t) => { const i = infoTarif(t); return ' min="' + Math.max(1, i ? i.vendu : 1) + '"' + (i && i.illimite ? ' placeholder="Illimité"' : ' placeholder="Qté"'); };
    const noteVendu = (t) => {
        const i = infoTarif(t);
        if (!i || !i.vendu) return '';
        return '<div class="wz-perso small text-muted">' + i.vendu + ' vendu(s) · '
            + 'le nouveau prix s’applique aux prochains achats</div>';
    };
    const boutonSuppr = (t) => (tarifVendu(t) ? '<span></span>'
        : '<button type="button" class="wz-x" data-suppr-tarif="' + t.id + '" aria-label="Supprimer ce billet"><i class="bi bi-x-lg" aria-hidden="true"></i></button>');

    function ligneTarif(t, index, noms, jour) {
        const options = noms.map((n) => '<option' + (n === t.nom ? ' selected' : '') + '>' + esc(n) + '</option>').join('');
        return '<div class="wz-trow" data-tarif="' + t.id + '">'
            + '<select class="form-select" name="tarifs[' + index + '][nom]" data-champ="nom" aria-label="Nom du billet">' + options + '</select>'
            + '<input type="number" min="0" step="100" class="form-control" name="tarifs[' + index + '][prix]" data-champ="prix" value="' + esc(t.prix) + '" placeholder="Prix" aria-label="Prix en francs CFA"' + attrPrix(t) + '>'
            + '<input type="number" class="form-control" name="tarifs[' + index + '][qte]" data-champ="qte" value="' + esc(t.qte) + '"' + attrQte(t) + ' aria-label="Quantité">'
            + boutonSuppr(t) + champId(t, index) + noteVendu(t)
            + (jour !== undefined ? '<input type="hidden" name="tarifs[' + index + '][jour]" value="' + esc(jour) + '">' : '')
            + (t.nom === 'Personnalisé' ? '<input type="text" class="form-control wz-perso" name="tarifs[' + index + '][nom_perso]" data-champ="nom_perso" value="' + esc(t.nom_perso) + '" maxlength="100" placeholder="Nom du billet" aria-label="Nom du billet personnalisé">' : '')
            + '</div>';
    }

    function trierJours() {
        etat.jours.sort((a, b) => (a.date || '9999').localeCompare(b.date || '9999'));
    }

    function rendreJours() {
        const multi = estMulti();
        $('#wz-date-unique').classList.toggle('d-none', multi);
        $('#wz-dates-multi').classList.toggle('d-none', !multi);
        $('#date_event').disabled = multi;
        $('#wz-jours').innerHTML = !multi ? '' : etat.jours.map((j, i) => {
            const verrou = verrouJour(j.orig);
            return '<div class="wz-dayrow">'
                + '<label class="fw-semibold" for="jour-' + j.id + '">Jour ' + (i + 1) + '</label>'
                + '<input type="datetime-local" class="form-control" id="jour-' + j.id + '" name="jours[' + i + ']" value="' + esc(j.date) + '"' + (verrou ? ' readonly title="Jour verrouillé : un scan a déjà eu lieu"' : (j.orig ? '' : ' min="' + esc(MIN_DATE) + '"')) + ' data-jour="' + j.id + '">'
                + (verrou ? '<span class="wz-badge wz-badge-pack" title="Un scan a déjà eu lieu ce jour"><i class="bi bi-lock"></i></span>'
                    : (etat.jours.length > 2 && !(EDITION && j.orig && (EDITION.joursProteges || []).includes(String(j.orig).slice(0, 10))) ? '<button type="button" class="wz-x" data-suppr-jour="' + j.id + '" aria-label="Supprimer le jour ' + (i + 1) + '"><i class="bi bi-x-lg" aria-hidden="true"></i></button>' : '<span></span>'))
                + '</div>';
        }).join('');
        // Date unique verrouillée (un seul jour déjà scanné)
        $('#date_event').readOnly = !!EDITION && verrouJour(EDITION.dateInitiale);
        $('#wz-ajout-jour').classList.toggle('d-none', etat.jours.length >= MAX_JOURS);
    }

    function rendreCapacites() {
        const multi = estMulti();
        $('#wz-cap-multi').classList.toggle('d-none', !multi);
        $('#wz-cap-label').textContent = multi ? 'Capacité générale' : 'Capacité';
        const box = $('#wz-cap-jours');
        const parJour = multi && !memeCapacite();
        box.classList.toggle('d-none', !parJour);
        box.innerHTML = !parJour ? '' : etat.jours.map((j, i) => '<div class="wz-daycap">'
            + '<label for="cap-' + j.id + '"><strong>Jour ' + (i + 1) + '</strong> · ' + esc(fmtJour(j.date)) + '</label>'
            + '<input type="number" min="1" class="form-control" id="cap-' + j.id + '" name="capacites_jours[' + i + ']" value="' + esc(j.cap) + '" placeholder="' + (capacite() || '') + '" data-cap="' + j.id + '">'
            + '</div>').join('') + '<div class="form-text">Laissez vide pour reprendre la capacité générale.</div>';
    }

    function rendreBillets() {
        const multi = estMulti();
        const gratuit = estGratuit();
        $('#wz-billets-mono').classList.toggle('d-none', multi);
        $('#wz-billets-multi').classList.toggle('d-none', !multi);
        $('#wz-billets-sub').textContent = multi
            ? 'Un tarif par jour, valable uniquement ce jour-là (0 F rend le jour gratuit). Les packs facultatifs donnent accès à tous les jours.'
            : 'Ajoutez un ou plusieurs tarifs. Le nom se choisit dans la liste.';
        $('#generer_vip').disabled = multi || gratuit;

        let index = 0;
        if (gratuit) {
            $('#wz-tarifs-unique').innerHTML = '';
            $('#wz-billets-jours').innerHTML = '';
            $('#wz-packs').innerHTML = '';
            return;
        }
        if (!multi) {
            $('#wz-tarifs-unique').innerHTML = etat.tarifsUnique.map((t) => ligneTarif(t, index++, NOMS_BILLETS)).join('');
            $('#wz-billets-jours').innerHTML = '';
            $('#wz-packs').innerHTML = '';
        } else {
            $('#wz-tarifs-unique').innerHTML = '';
            assurerUnTarifParJour();
            // Un seul tarif par jour : une ligne prix + quantité, nommée « Jour N ».
            $('#wz-billets-jours').innerHTML = etat.jours.map((j, i) => {
                const duJour = etat.tarifsJours.filter((x) => x.jourId === j.id);
                return '<div class="wz-block">'
                    + '<div class="wz-block-h"><div><span class="fw-semibold">Jour ' + (i + 1) + '</span> <span class="text-muted small">· ' + esc(fmtDate(j.date)) + '</span></div><span data-badge-jour="' + j.id + '"></span></div>'
                    + duJour.map((t) => {
                        const k = index++;
                        return '<div class="wz-prix-qte mb-2" data-tarif="' + t.id + '">'
                            + '<input type="hidden" name="tarifs[' + k + '][nom]" value="' + esc(t.tid ? t.nom : 'Jour ' + (i + 1)) + '">'
                            + '<input type="hidden" name="tarifs[' + k + '][jour]" value="' + i + '">' + champId(t, k)
                            + '<label>' + (duJour.length > 1 ? esc(t.nom) + ' · ' : '') + 'Prix (F CFA)<input type="number" min="0" step="100" class="form-control" name="tarifs[' + k + '][prix]" data-champ="prix" value="' + esc(t.prix) + '" placeholder="' + 'Vide ou 0 = gratuit' + '"' + attrPrix(t) + '></label>'
                            + '<label>Qté<input type="number" class="form-control" name="tarifs[' + k + '][qte]" data-champ="qte" value="' + esc(t.qte) + '"' + attrQte(t) + '></label>'
                            + noteVendu(t) + '</div>';
                    }).join('')
                    + '<div class="wz-reste small mt-1" data-reste-jour="' + j.id + '"></div></div>';
            }).join('');
            // Packs facultatifs : Pack Simple et Pack VIP, chacun activé par une case.
            const champsPack = (t, k, libelle) => '<div class="wz-prix-qte" data-tarif="' + t.id + '">'
                + '<input type="hidden" name="tarifs[' + k + '][nom]" value="' + esc(t.nom) + '">'
                + '<input type="hidden" name="tarifs[' + k + '][jour]" value="pack">' + champId(t, k)
                + '<label>' + libelle + '<input type="number" min="0" step="100" class="form-control" name="tarifs[' + k + '][prix]" data-champ="prix" value="' + esc(t.prix) + '" placeholder="Prix"' + attrPrix(t) + '></label>'
                + '<label>Qté<input type="number" class="form-control" name="tarifs[' + k + '][qte]" data-champ="qte" value="' + esc(t.qte) + '"' + attrQte(t) + '></label>'
                + noteVendu(t) + '</div>';
            // Packs d'un ancien événement (autres noms) : conservés tels quels.
            const anciensPacks = etat.tarifsJours.filter((x) => x.jourId === 'pack' && !PACKS.includes(x.nom));
            $('#wz-packs').innerHTML = anciensPacks.map((t) => '<div class="wz-pack"><div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-semibold">' + esc(t.nom) + '</span>' + boutonSuppr(t) + '</div>'
                + champsPack(t, index++, 'Prix pour ' + etat.jours.length + ' jours (F CFA)') + '</div>').join('') + PACKS.map((nomPack) => {
                const t = etat.tarifsJours.find((x) => x.jourId === 'pack' && x.nom === nomPack);
                const id = 'pack-' + nomPack.replace(/\W+/g, '-').toLowerCase();
                let html = '<div class="wz-pack"><div class="form-check form-switch mb-2">'
                    + '<input class="form-check-input" type="checkbox" role="switch" id="' + id + '" data-pack="' + esc(nomPack) + '"' + (t ? ' checked' : '') + (t && tarifVendu(t) ? ' disabled title="Pack déjà vendu"' : '') + '>'
                    + '<label class="form-check-label fw-semibold" for="' + id + '">' + esc(nomPack) + '</label></div>';
                if (t) html += champsPack(t, index++, 'Prix pour ' + etat.jours.length + ' jours (F CFA)');
                return html + '</div>';
            }).join('');
        }
        majCalculs();
    }

    // Jauges, badges des jours, date de fin de vente des packs
    function majCalculs() {
        const plein = nbTarifs() >= MAX_TARIFS;
        $$('[data-ajout-tarif]').forEach((b) => { b.disabled = plein; });
        $('#wz-max-tarifs').classList.toggle('text-danger', plein);

        if (!estMulti()) {
            const total = etat.tarifsUnique.reduce((s, t) => s + num(t.qte), 0);
            const ok = total <= capacite();
            const el = $('#wz-jauge-mono');
            el.textContent = total + ' / ' + capacite() + ' places' + (ok ? '' : ' · dépassement');
            el.className = ok ? 'wz-ok' : 'wz-ko';
            return;
        }

        const packs = etat.tarifsJours.filter((t) => t.jourId === 'pack');
        const placesPacks = packs.reduce((s, t) => s + num(t.qte), 0);
        let premierPayant = null;
        let depassement = false;

        // Aide par jour : places que les packs réservent, et ce qu'il reste pour les billets du jour.
        etat.jours.forEach((j) => {
            const el = $('[data-reste-jour="' + j.id + '"]');
            if (!el) return;
            const duJour = etat.tarifsJours.filter((t) => t.jourId === j.id);
            const cap = capaciteJour(j);
            const reste = cap - placesPacks;
            const saisi = duJour.reduce((s, t) => s + num(t.qte), 0);
            let html = 'Capacité du jour : <strong>' + cap + '</strong>'
                + (placesPacks ? ' − packs : <strong>' + placesPacks + '</strong>' : '')
                + ' = <strong>' + Math.max(0, reste) + '</strong> places possibles pour les billets de ce jour.';
            if (duJour.length && saisi !== Math.max(0, reste) && reste > 0) {
                html += ' <button type="button" class="wz-link" data-remplir-reste="' + j.id + '">Utiliser ' + reste + '</button>';
            }
            if (saisi > reste) html += ' <span class="wz-ko">Réduisez de ' + (saisi - Math.max(0, reste)) + '.</span>';
            el.innerHTML = html;
        });

        $('#wz-jauges').innerHTML = etat.jours.map((j, i) => {
            const duJour = etat.tarifsJours.filter((t) => t.jourId === j.id);
            const gratuitJour = duJour.length > 0 && duJour.every((t) => num(t.prix) === 0);
            if (!premierPayant && !gratuitJour) premierPayant = { numero: i + 1, date: j.date };
            const badge = $('[data-badge-jour="' + j.id + '"]');
            if (badge) {
                badge.className = 'wz-badge ' + (gratuitJour ? 'wz-badge-gratuit' : (duJour.length ? '' : 'wz-badge-pack'));
                badge.textContent = gratuitJour ? 'Jour gratuit' : (duJour.length ? '' : 'Accessible avec un pack');
            }
            const utilise = duJour.reduce((s, t) => s + num(t.qte), 0) + placesPacks;
            const cap = capaciteJour(j);
            const ok = utilise <= cap;
            if (!ok) depassement = true;
            return '<div class="wz-gauge"><span><strong>Jour ' + (i + 1) + '</strong> · ' + esc(fmtJour(j.date)) + '</span>'
                + '<span class="' + (ok ? 'wz-ok' : 'wz-ko') + '">' + utilise + ' / ' + cap + ' places' + (ok ? '' : ' · dépassement') + '</span></div>';
        }).join('');
        $('#wz-depassement').classList.toggle('d-none', !depassement);

        const info = $('#wz-pack-info');
        info.classList.toggle('d-none', !packs.length);
        if (packs.length) {
            const fin = premierPayant || { numero: etat.jours.length, date: (etat.jours[etat.jours.length - 1] || {}).date };
            info.innerHTML = 'En vente jusqu’au <strong>' + esc(fmtJour(fin.date)) + ' à minuit</strong>, fin du premier jour payant (Jour ' + fin.numero + '). Ensuite, seuls les billets à la journée restent en vente.';
        }
    }

    function rendreApercu() {
        const titre = $('#titre').value.trim();
        $('#wz-apercu-titre').textContent = titre || 'Titre de votre événement';
        const cat = radio('categorie');
        $('#wz-apercu-cat').textContent = cat === 'Autre' ? ($('#autre_categorie').value.trim() || 'Autre') : (CATEGORIES[cat] || 'Catégorie');
        const jours = estMulti() ? etat.jours : [{ date: $('#date_event').value }];
        $('#wz-apercu-dates').textContent = estMulti()
            ? jours.length + ' jours · à partir du ' + fmtJour((jours[0] || {}).date)
            : fmtDate(jours[0].date);
        $('#wz-desc-count').textContent = $('#description').value.length;
        $('#wz-autre-cat').classList.toggle('d-none', cat !== 'Autre');
    }

    function rendreOptions() {
        const gratuit = estGratuit();
        $('#wz-co').classList.toggle('d-none', gratuit);
        $$('input', $('#wz-co')).forEach((el) => { el.disabled = gratuit; });
        $('#wz-co-champs').classList.toggle('d-none', gratuit || !$('#co_parrainage_actif').checked);
    }

    function rendreRecap() {
        const multi = estMulti();
        const gratuit = estGratuit();
        const cat = radio('categorie');
        const jours = multi ? etat.jours : [{ date: $('#date_event').value }];
        let billets;
        if (gratuit) {
            billets = ['Billet gratuit unique' + (multi ? ', valable tous les jours' : '')];
        } else if (multi) {
            billets = etat.jours.flatMap((j, i) => etat.tarifsJours.filter((t) => t.jourId === j.id)
                .map((t) => 'Jour ' + (i + 1) + ' : ' + fmtPrix(t.prix) + ' · ' + num(t.qte) + ' places'))
                .concat(etat.tarifsJours.filter((t) => t.jourId === 'pack').map((t) => 'Pack : ' + nomTarif(t) + ' · ' + fmtPrix(t.prix) + ' · ' + num(t.qte) + ' places'));
        } else {
            billets = etat.tarifsUnique.map((t) => nomTarif(t) + ' · ' + fmtPrix(t.prix) + ' · ' + num(t.qte) + ' places');
            if ($('#generer_vip').checked) billets.push('VIP automatique au double du premier prix');
        }
        const capacites = multi && !memeCapacite()
            ? etat.jours.map((j, i) => 'Jour ' + (i + 1) + ' : ' + capaciteJour(j) + ' places')
            : [capacite() + ' places'];
        const bloc = (titre, etape, lignes, avant) => '<div class="wz-rc"><div class="wz-rc-h"><span>' + esc(titre) + '</span>'
            + '<button type="button" class="wz-rc-edit" data-aller="' + etape + '"><i class="bi bi-pencil-square" aria-hidden="true"></i>Modifier</button></div>'
            + (avant || '')
            + lignes.map((l) => '<p>' + esc(l) + '</p>').join('') + '</div>';
        // Image de couverture choisie à l'étape Informations (URL locale, rien n'est envoyé)
        const imageActuelle = urlImage || (EDITION && EDITION.image);
        const image = imageActuelle
            ? '<img src="' + esc(imageActuelle) + '" alt="Image de couverture" class="wz-rc-img">'
            : '<div class="wz-rc-img wz-rc-img--vide">Aucune image</div>';

        $('#wz-recap').innerHTML = [
            bloc('Événement', 'infos', [$('#titre').value.trim() || 'Titre à renseigner', (TYPES[radio('type_evenement')] || '') + ' · ' + (cat === 'Autre' ? ($('#autre_categorie').value.trim() || 'Autre') : (CATEGORIES[cat] || 'Catégorie à choisir'))], image),
            bloc('Accès', 'acces', [gratuit ? 'Gratuit' : 'Payant']),
            bloc('Dates', 'dates', jours.map((j, i) => (multi ? 'Jour ' + (i + 1) + ' : ' : '') + fmtDate(j.date))),
            bloc('Lieu et capacité', 'lieu', [$('#lieu').value.trim() || 'Lieu à renseigner'].concat(capacites)),
            bloc('Billets', gratuit ? 'acces' : 'billets', billets.length ? billets : ['Aucun billet']),
        ].join('');
    }

    function rendreTout() {
        rendreJours();
        rendreCapacites();
        rendreBillets();
        rendreApercu();
        rendreOptions();
        if (courante === 'recap') rendreRecap();
    }

    // ---------- Navigation ----------
    const etapesVisibles = () => ETAPES.filter((e) => e !== 'billets' || !estGratuit());

    function allerA(etape) {
        const visibles = etapesVisibles();
        if (!visibles.includes(etape)) etape = visibles[Math.min(visibles.length - 1, ETAPES.indexOf(etape))] || visibles[0];
        courante = etape;
        const idx = visibles.indexOf(etape);

        $$('.wz-step').forEach((s) => s.classList.toggle('d-none', s.dataset.etape !== etape));
        $$('.wz-stepbtn').forEach((b) => {
            const pos = visibles.indexOf(b.dataset.aller);
            b.classList.toggle('d-none', pos === -1);
            b.classList.toggle('is-on', pos === idx);
            b.classList.toggle('is-done', pos > -1 && pos < idx);
            b.setAttribute('aria-current', pos === idx ? 'step' : 'false');
            const dot = $('[data-dot]', b);
            dot.innerHTML = pos > -1 && pos < idx ? '<i class="bi bi-check-lg" aria-hidden="true"></i>' : String(pos + 1);
        });
        $('#wz-bar').style.width = Math.round(((idx + 1) / visibles.length) * 100) + '%';
        $('#wz-kicker').textContent = 'Étape ' + (idx + 1) + ' sur ' + visibles.length + ' · ' + LIBELLES[etape];
        $('#wz-prec').classList.toggle('invisible', idx === 0);
        $('#wz-suiv').classList.toggle('d-none', idx === visibles.length - 1);
        $('#wz-fin').classList.toggle('d-none', idx !== visibles.length - 1);

        rendreTout();
        sauvegarder();
        // Sur téléphone, la liste des étapes défile : on garde l'étape en cours visible.
        const actif = $('.wz-stepbtn.is-on');
        const liste = $('.wz-side');
        if (actif && liste.scrollWidth > liste.clientWidth) {
            liste.scrollLeft = actif.offsetLeft - (liste.clientWidth - actif.offsetWidth) / 2;
        }
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ---------- Validation côté navigateur (le serveur reste la référence) ----------
    function valider(etape) {
        const e = [];
        const maintenant = new Date();
        if (etape === 'type') {
            if (!radio('type_evenement')) e.push('Choisissez le type d’événement.');
            if (!radio('categorie')) e.push('Choisissez une catégorie.');
            if (radio('categorie') === 'Autre' && !$('#autre_categorie').value.trim()) e.push('Précisez la catégorie.');
        }
        if (etape === 'infos') {
            if (!$('#titre').value.trim()) e.push('Le titre est obligatoire.');
            const f = $('#image').files[0];
            if (f && f.size > 2 * 1024 * 1024) e.push('L’image dépasse 2 Mo.');
        }
        if (etape === 'acces' && radio('gratuit') === undefined) e.push('Indiquez si l’événement est gratuit ou payant.');
        if (etape === 'dates') {
            if (!estMulti()) {
                const d = $('#date_event').value;
                if (!d) e.push('Renseignez la date et l’heure de début.');
                else if (new Date(d) < maintenant && !(EDITION && d === EDITION.dateInitiale)) e.push('La date doit être dans le futur.');
            } else {
                if (etat.jours.some((j) => !j.date)) e.push('Renseignez la date et l’heure de chaque jour.');
                if (etat.jours.some((j) => j.date && j.date !== j.orig && new Date(j.date) < maintenant)) e.push('Chaque jour doit être dans le futur.');
                const dates = etat.jours.map((j) => j.date).filter(Boolean);
                if (new Set(dates).size !== dates.length) e.push('Deux jours ont la même date et la même heure.');
            }
        }
        if (etape === 'lieu') {
            if (!$('#lieu').value.trim()) e.push('Le lieu est obligatoire.');
            if (capacite() < 1) e.push('Indiquez une capacité d’au moins 1 place.');
            if (estMulti() && !memeCapacite() && etat.jours.some((j) => j.cap !== '' && j.cap != null && num(j.cap) < 1)) e.push('La capacité d’un jour doit être d’au moins 1 place.');
        }
        if (etape === 'billets' && !estGratuit()) {
            const tarifs = tarifsActifs();
            // Multijours : un jour dont le prix est vide ou 0 est gratuit (quantité facultative).
            const jourGratuitVide = (t) => estMulti() && t.jourId !== 'pack' && (t.prix === '' || num(t.prix) === 0);
            if (!tarifs.length) e.push('Ajoutez au moins un billet.');
            if (tarifs.some((t) => (t.prix === '' && !jourGratuitVide(t)) || num(t.prix) < 0)) e.push('Indiquez le prix de chaque billet (0 pour gratuit).');
            if (tarifs.some((t) => num(t.qte) < 1 && !(infoTarif(t) || {}).illimite && !(jourGratuitVide(t) && (t.qte === '' || t.qte == null)))) e.push('Indiquez le nombre de places de chaque billet.');
            if (tarifs.some((t) => tarifVendu(t) && t.qte !== '' && num(t.qte) < infoTarif(t).vendu)) e.push('La quantité d’un tarif ne peut pas descendre sous ses ventes.');
            if (tarifs.some((t) => t.nom === 'Personnalisé' && !t.nom_perso.trim())) e.push('Donnez un nom à chaque billet personnalisé.');
            if (nbTarifs() > MAX_TARIFS) e.push('Pas plus de ' + MAX_TARIFS + ' tarifs par événement.');
            if (!estMulti() && tarifs.reduce((s, t) => s + num(t.qte), 0) > capacite()) e.push('Le total des places dépasse la capacité de l’événement.');
            if (estMulti() && !$('#wz-depassement').classList.contains('d-none')) e.push('Un jour dépasse sa capacité.');
        }
        if (etape === 'recap' && !estGratuit() && $('#co_parrainage_actif').checked && !$('#co_parrainage_pourcentage').value) {
            e.push('Indiquez la commission à reverser aux affiliés.');
        }
        return e;
    }

    function afficherErreurs(etape, erreurs) {
        const section = $('.wz-step[data-etape="' + etape + '"]');
        let box = $('[data-erreurs-client]', section);
        if (!box) {
            box = document.createElement('div');
            box.dataset.erreursClient = '';
            $('.wz-sub', section).after(box);
        }
        box.innerHTML = erreurs.length
            ? '<div class="wz-alert" role="alert"><strong>À compléter :</strong><ul>' + erreurs.map((m) => '<li>' + esc(m) + '</li>').join('') + '</ul></div>'
            : '';
    }

    // Une fois des erreurs affichées sur l'étape, elles se mettent à jour
    // pendant la saisie (et disparaissent quand tout est corrigé).
    function rafraichirErreurs() {
        const section = $('.wz-step[data-etape="' + courante + '"]');
        const box = section && $('[data-erreurs-client]', section);
        if (box && box.innerHTML) afficherErreurs(courante, valider(courante));
    }

    // ---------- Événements ----------
    form.addEventListener('click', (ev) => {
        const cible = ev.target.closest('button');
        if (!cible) return;

        if (cible.dataset.aller) { allerA(cible.dataset.aller); return; }
        if (cible.id === 'wz-prec') { const v = etapesVisibles(); allerA(v[Math.max(0, v.indexOf(courante) - 1)]); return; }
        if (cible.id === 'wz-suiv') {
            const erreurs = valider(courante);
            afficherErreurs(courante, erreurs);
            if (erreurs.length) return;
            const v = etapesVisibles();
            allerA(v[Math.min(v.length - 1, v.indexOf(courante) + 1)]);
            return;
        }
        if (cible.id === 'wz-ajout-jour' && etat.jours.length < MAX_JOURS) {
            etat.jours.push({ id: nouvelId(), date: '', cap: '' });
            rendreTout(); sauvegarder();
            const derniers = $$('[data-jour]');
            if (derniers.length) derniers[derniers.length - 1].focus();
            return;
        }
        if (cible.dataset.supprJour) {
            const id = num(cible.dataset.supprJour);
            etat.jours = etat.jours.filter((j) => j.id !== id);
            etat.tarifsJours = etat.tarifsJours.filter((t) => t.jourId !== id);
            rendreTout(); sauvegarder();
            return;
        }
        if (cible.dataset.remplirReste) {
            const j = etat.jours.find((x) => x.id === num(cible.dataset.remplirReste));
            const t = j && etat.tarifsJours.find((x) => x.jourId === j.id);
            if (t) {
                const packs = etat.tarifsJours.filter((x) => x.jourId === 'pack').reduce((s, x) => s + num(x.qte), 0);
                t.qte = String(Math.max(capaciteJour(j) - packs, (infoTarif(t) || {}).vendu || 1));
                rendreBillets(); sauvegarder();
            }
            return;
        }
        if (cible.dataset.ajoutTarif) {
            if (nbTarifs() >= MAX_TARIFS) return;
            const cle = cible.dataset.ajoutTarif;
            if (cle === 'unique') etat.tarifsUnique.push(nouveauTarif());
            else if (cle === 'pack') etat.tarifsJours.push(nouveauTarif({ nom: 'Pass complet', jourId: 'pack' }));
            else etat.tarifsJours.push(nouveauTarif({ jourId: num(cle) }));
            rendreBillets(); sauvegarder();
            return;
        }
        if (cible.dataset.supprTarif) {
            const id = num(cible.dataset.supprTarif);
            etat.tarifsUnique = etat.tarifsUnique.filter((t) => t.id !== id);
            etat.tarifsJours = etat.tarifsJours.filter((t) => t.id !== id);
            rendreBillets(); sauvegarder();
        }
    });

    form.addEventListener('input', (ev) => {
        const el = ev.target;
        if (el.dataset.jour) {
            const j = etat.jours.find((x) => x.id === num(el.dataset.jour));
            if (j) j.date = el.value;
        } else if (el.dataset.cap) {
            const j = etat.jours.find((x) => x.id === num(el.dataset.cap));
            if (j) j.cap = el.value;
            majCalculs();
        } else if (el.dataset.champ) {
            const ligne = el.closest('[data-tarif]');
            const t = [...etat.tarifsUnique, ...etat.tarifsJours].find((x) => x.id === num(ligne.dataset.tarif));
            if (t) t[el.dataset.champ] = el.value;
            majCalculs();
        } else if (el.id === 'capacite') {
            rendreCapacites(); majCalculs();
        }
        rendreApercu();
        rafraichirErreurs();
        sauvegarder();
    });

    form.addEventListener('change', (ev) => {
        const el = ev.target;
        if (el.dataset.jour) { trierJours(); rendreTout(); }
        else if (el.dataset.champ === 'nom') { rendreBillets(); }
        else if (el.dataset.pack) {
            // Activation / désactivation d'un pack (Pack Simple, Pack VIP)
            etat.tarifsJours = etat.tarifsJours.filter((t) => !(t.jourId === 'pack' && t.nom === el.dataset.pack));
            if (el.checked) etat.tarifsJours.push(nouveauTarif({ nom: el.dataset.pack, jourId: 'pack' }));
            rendreBillets();
        }
        else if (['gratuit', 'duree', 'meme_capacite', 'generer_vip', 'co_parrainage_actif', 'categorie'].includes(el.name)) {
            rendreTout();
            if (el.name === 'gratuit') allerA(courante);
        }
        rafraichirErreurs();
        sauvegarder();
    });

    // Entrée dans un champ : passe à l'étape suivante au lieu d'envoyer le
    // formulaire (le premier bouton d'envoi est « Enregistrer en brouillon »).
    form.addEventListener('keydown', (ev) => {
        if (ev.key !== 'Enter' || ev.target.tagName === 'TEXTAREA' || ev.target.tagName === 'BUTTON') return;
        ev.preventDefault();
        if (courante !== 'recap') $('#wz-suiv').click();
    });

    // Image : glisser-déposer, aperçu et contrôle de taille
    const zone = $('#wz-drop');
    const champImage = $('#image');
    ['dragenter', 'dragover'].forEach((t) => zone.addEventListener(t, (ev) => { ev.preventDefault(); zone.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((t) => zone.addEventListener(t, () => zone.classList.remove('is-over')));
    zone.addEventListener('drop', (ev) => {
        ev.preventDefault();
        if (ev.dataTransfer.files.length) {
            champImage.files = ev.dataTransfer.files;
            champImage.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    champImage.addEventListener('change', () => {
        const f = champImage.files[0];
        $('#wz-img-trop-lourde').classList.toggle('d-none', !(f && f.size > 2 * 1024 * 1024));
        if (!f) return;
        if (urlImage) URL.revokeObjectURL(urlImage);
        const url = URL.createObjectURL(f);
        urlImage = url;
        $('#wz-img').src = url;
        $('#wz-img').classList.remove('d-none');
        $('#wz-drop-titre').textContent = f.name;
        $('#wz-apercu-img').style.backgroundImage = 'url("' + url + '")';
        $('#wz-apercu-img').textContent = '';
    });

    // Envoi : contrôle de toutes les étapes, puis effacement du brouillon
    form.addEventListener('submit', (ev) => {
        rendreTout();
        for (const etape of etapesVisibles()) {
            const erreurs = valider(etape);
            if (erreurs.length) {
                ev.preventDefault();
                allerA(etape);
                afficherErreurs(etape, erreurs);
                return;
            }
        }
        clearTimeout(minuteur);
        try { localStorage.removeItem(CLE_BROUILLON); } catch (e) { /* rien */ }
    });

    $('#wz-effacer').addEventListener('click', () => {
        try { localStorage.removeItem(CLE_BROUILLON); } catch (e) { /* rien */ }
        window.location.reload();
    });

    // ---------- Démarrage ----------
    let etapeDepart = 'type';
    if (EDITION) {
        chargerDepuisAncien(ANCIEN);
        etapeDepart = ETAPE_ERREUR || 'type';
        if (EDITION.image) {
            $('#wz-img').src = EDITION.image;
            $('#wz-img').classList.remove('d-none');
            $('#wz-apercu-img').style.backgroundImage = 'url("' + EDITION.image + '")';
            $('#wz-apercu-img').textContent = '';
        }
    } else if (Object.keys(ANCIEN).length) {
        chargerDepuisAncien(ANCIEN);
        etapeDepart = ETAPE_ERREUR || 'recap';
    } else {
        const brouillon = lireBrouillon();
        if (brouillon && brouillon.etat) {
            appliquerBrouillon(brouillon);
            $('#wz-brouillon').classList.remove('d-none');
            etapeDepart = brouillon.etape || 'type';
        }
    }
    if (EDITION && !etat.jours.length) etat.jours.push({ id: nouvelId(), date: EDITION.dateInitiale, cap: '', orig: EDITION.dateInitiale });
    while (etat.jours.length < 2) etat.jours.push({ id: nouvelId(), date: '', cap: '' });
    if (!etat.tarifsUnique.length) etat.tarifsUnique.push(nouveauTarif());
    trierJours();
    allerA(etapeDepart);
})();
</script>
@endsection
