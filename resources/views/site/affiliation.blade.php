@extends('layouts.public')

@section('title', "Programme d'Affiliation PaxEvent — Gagnez jusqu'à 2 % par ticket")
@section('description', "Recommandez PaxEvent et gagnez jusqu'à 2 % de commission sur chaque ticket vendu par les organisateurs que vous parrainez. Inscription gratuite, tableau de bord en temps réel.")

@section('styles')
<noscript><style>.aff-gain-card { opacity: 1 !important; transform: none !important; }</style></noscript>
<style>
    .aff-hero {
        background: linear-gradient(135deg, #542680 0%, #3d1a5c 100%);
        color: #fff;
        padding: 4rem 0 3.5rem;
        position: relative;
        overflow: hidden;
    }
    .aff-hero::after {
        content: '';
        position: absolute;
        right: -80px; top: -80px;
        width: 320px; height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(254,213,20,0.18) 0%, rgba(254,213,20,0) 70%);
    }
    .aff-hero h1 { font-weight: 800; line-height: 1.2; }
    .aff-hero .lead { color: rgba(255,255,255,0.88); }
    .aff-hero .micro { color: rgba(255,255,255,0.65); font-size: 0.85rem; }

    .aff-soft { background: #faf8fd; }
    .aff-section-title { font-weight: 800; color: var(--sombre); }
    .aff-sub { color: var(--gris); }

    .aff-card {
        background: #fff;
        border: 1px solid #ece7f3;
        border-radius: 14px;
        padding: 1.6rem 1.4rem;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .aff-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(84,38,128,0.1); }
    .aff-ico {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(84,38,128,0.08);
        color: var(--violet);
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }

    .aff-gain-card {
        position: relative;
        background: #fff;
        border: 1px solid #ece7f3;
        border-radius: 20px;
        padding: 2rem 1.6rem;
        height: 100%;
        text-align: center;
        transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.5s ease;
        opacity: 0;
        transform: translateY(18px);
    }
    .aff-gain-card.in-view { opacity: 1; transform: translateY(0); }
    .aff-gain-card.in-view:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(84,38,128,0.16); }
    .aff-gain-card.premium {
        background: #fff;
        border: 2px solid var(--violet);
        box-shadow: 0 14px 34px rgba(84,38,128,0.16);
    }
    .aff-gain-tag {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        background: rgba(84,38,128,0.08);
        color: var(--violet);
        margin-bottom: 1.1rem;
    }
    .aff-gain-pct { font-size: 3.4rem; font-weight: 800; line-height: 1; color: var(--violet); }
    .aff-gain-pct small { font-size: 1.6rem; margin-left: 0.1rem; }
    .aff-gain-sub { color: var(--gris); font-size: 0.85rem; margin: 0.5rem 0 1.25rem; }
    .aff-gain-amount {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--violet);
        border-top: 1px dashed #e5dcf0;
        padding-top: 1.25rem;
    }
    .aff-gain-amount-note { display: block; font-size: 0.75rem; font-weight: 500; color: var(--gris); margin-top: 0.3rem; }
    .aff-gain-badge {
        position: absolute;
        top: -14px; left: 50%;
        transform: translateX(-50%);
        background: var(--accent);
        color: var(--sombre);
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(254,213,20,0.4);
    }
    .aff-gain-total {
        margin-top: 2.25rem;
        text-align: center;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--sombre);
        background: #f4effb;
        border: 1px solid #e5dcf0;
        border-radius: 16px;
        padding: 1.5rem 1.25rem;
    }
    .aff-gain-total .aff-count { color: var(--violet); font-weight: 800; }
    @media (prefers-reduced-motion: reduce) {
        .aff-gain-card { opacity: 1; transform: none; transition: none; }
    }

    .aff-step-num {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: var(--violet);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800;
        margin: 0 auto 0.8rem;
    }
    .aff-step-ico { font-size: 1.5rem; color: var(--violet); }

    .aff-final {
        background: linear-gradient(135deg, #542680 0%, #3d1a5c 100%);
        color: #fff;
        border-radius: 18px;
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .aff-faq .accordion-button { font-weight: 600; color: var(--sombre); }
    .aff-faq .accordion-button:not(.collapsed) { background: rgba(84,38,128,0.06); color: var(--violet); }
    .aff-faq .accordion-button:focus { box-shadow: none; }
    .aff-faq .accordion-item { border-color: #ece7f3; }

    .aff-cg { border: 1px solid #ece7f3; border-radius: 14px; overflow: hidden; background: #fff; }
    .aff-cg .accordion-button { font-weight: 700; color: var(--violet); }
</style>
@endsection

@section('content')

{{-- ========== 1. HERO ========== --}}
<section class="aff-hero">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-12 col-lg-9">
                <span class="badge rounded-pill mb-3" style="background: rgba(254,213,20,0.15); color: var(--accent); font-weight: 700;">
                    <i class="bi bi-megaphone me-1"></i> Programme d'Affiliation
                </span>
                <h1 class="display-5 mb-3">Transformez votre réseau événementiel en une source de revenus récurrents.</h1>
                <p class="lead mb-4">
                    Gagnez jusqu'à <strong>2 % de commission</strong> sur chaque ticket vendu par les organisateurs que vous parrainez.
                </p>
                <button type="button" class="btn btn-violet btn-lg px-4 fw-bold" style="background: var(--accent); border-color: var(--accent); color: var(--sombre);">
                    Rejoindre le programme gratuitement <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <p class="micro mt-3 mb-0">Inscription en 2 minutes &bull; Aucun coût d'entrée &bull; Tableau de bord en temps réel</p>
            </div>
        </div>
    </div>
</section>

{{-- ========== 2. POURQUOI DEVENIR AFFILIÉ ========== --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Pourquoi devenir Affilié PaxEvent ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-cash-coin"></i></div>
                    <h6 class="fw-bold mb-2">Gagnez sur chaque billet vendu</h6>
                    <p class="text-muted small mb-0">Touchez entre 1 % et 2 % sur le prix de chaque ticket vendu par vos parrainés. Sans plafond de gains.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-arrow-repeat"></i></div>
                    <h6 class="fw-bold mb-2">Un partenariat sur la durée</h6>
                    <p class="text-muted small mb-0">Profitez d'une rémunération automatique sur le premier événement de vos filleuls, puis continuez à générer des commissions sur tous leurs événements futurs grâce à vos codes personnalisés.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-speedometer2"></i></div>
                    <h6 class="fw-bold mb-2">Dashboard en temps réel</h6>
                    <p class="text-muted small mb-0">Suivez vos inscriptions, visualisez le volume de billets vendus en direct et contrôlez vos commissions disponibles depuis un tableau de bord clair et intuitif.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-wallet2"></i></div>
                    <h6 class="fw-bold mb-2">Paiements garantis</h6>
                    <p class="text-muted small mb-0">Demandez le virement de vos commissions en quelques clics dès qu'elles sont validées.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 3. COMBIEN POUVEZ-VOUS GAGNER ========== --}}
<section class="py-5 aff-soft" id="affGains">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="aff-section-title h3">Combien pouvez-vous gagner ?</h2>
            <p class="aff-sub mb-0 mx-auto" style="max-width: 640px;">
                Votre commission est automatique : elle suit la formule de l'organisateur que vous parrainez.
            </p>
        </div>
        <div class="row justify-content-center g-4">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="aff-gain-card">
                    <span class="aff-gain-tag">Filleul en offre Standard</span>
                    <div class="aff-gain-pct"><span data-count="1">1</span><small>%</small></div>
                    <p class="aff-gain-sub">du prix de chaque ticket · PaxEvent prélève 5 %</p>
                    <div class="aff-gain-amount">
                        <span data-count="100000" data-suffix=" FCFA">100 000 FCFA</span>
                        <span class="aff-gain-amount-note">pour 1 000 tickets à 10 000 FCFA</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-5">
                <div class="aff-gain-card premium">
                    <span class="aff-gain-badge">2× plus de commission</span>
                    <span class="aff-gain-tag">Filleul en offre Premium</span>
                    <div class="aff-gain-pct"><span data-count="2">2</span><small>%</small></div>
                    <p class="aff-gain-sub">du prix de chaque ticket · PaxEvent prélève 8 % à 10 %</p>
                    <div class="aff-gain-amount">
                        <span data-count="200000" data-suffix=" FCFA">200 000 FCFA</span>
                        <span class="aff-gain-amount-note">pour 1 000 tickets à 10 000 FCFA</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="aff-gain-total">
            <i class="bi bi-lightning-charge-fill me-1"></i>
            Parrainez 5 organisateurs réalisant chacun 1 000 ventes et générez jusqu'à
            <span class="aff-count" data-count="1000000" data-suffix=" FCFA">1 000 000 FCFA</span> de revenus complémentaires !
        </div>
    </div>
</section>

{{-- ========== 4. COMMENT ÇA MARCHE ========== --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Comment fonctionne le programme ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-4 step-card">
                <div class="aff-step-num">1</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-person-plus"></i></div>
                <h6 class="fw-bold">Inscription &amp; Activation</h6>
                <p class="text-muted small mb-0">Créez votre compte affilié gratuitement. Votre lien et votre code de parrainage principal sont générés automatiquement sur votre tableau de bord.</p>
            </div>
            <div class="col-12 col-md-4 step-card">
                <div class="aff-step-num">2</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-share"></i></div>
                <h6 class="fw-bold">Partage auprès de votre réseau</h6>
                <p class="text-muted small mb-0">Partagez votre lien auprès des organisateurs d'événements, agences, artistes ou promoteurs. Lors de leur inscription, ils sont automatiquement associés à votre compte.</p>
            </div>
            <div class="col-12 col-md-4 step-card">
                <div class="aff-step-num">3</div>
                <div class="aff-step-ico mb-2"><i class="bi bi-cash-stack"></i></div>
                <h6 class="fw-bold">Accumulation des gains</h6>
                <p class="text-muted small mb-0">Dès que votre filleul publie un événement et vend des tickets, vos commissions s'accumulent en direct sur votre solde. Pour leurs événements futurs, générez un nouveau code depuis votre dashboard !</p>
            </div>
        </div>
    </div>
</section>

{{-- ========== 5. À QUI S'ADRESSE ========== --}}
<section class="py-5 aff-soft">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="aff-section-title h3">Un programme pensé pour les acteurs de l'événementiel</h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-briefcase"></i></div>
                    <h6 class="fw-bold mb-2">Agences &amp; Event Planners</h6>
                    <p class="text-muted small mb-0">Recommandez la meilleure billetterie à vos clients et créez une nouvelle marge financière sur chacun de vos projets.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-camera-video"></i></div>
                    <h6 class="fw-bold mb-2">Influenceurs &amp; Créateurs de Contenu</h6>
                    <p class="text-muted small mb-0">Monétisez vos communautés passionnées par la culture, la musique, le business ou les loisirs.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="aff-card">
                    <div class="aff-ico"><i class="bi bi-newspaper"></i></div>
                    <h6 class="fw-bold mb-2">Médias &amp; Plateformes Locales</h6>
                    <p class="text-muted small mb-0">Valorisez votre audience en promouvant la billetterie auprès des organisateurs partenaires.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 6. FAQ + CTA FINAL ========== --}}
<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-6">
                <h2 class="aff-section-title h4 mb-4">Questions fréquentes</h2>
                <div class="accordion aff-faq" id="affFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq1">
                                L'inscription au programme d'affiliation est-elle payante ?
                            </button>
                        </h2>
                        <div id="affFaq1" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Non, l'inscription est 100 % gratuite et sans aucun engagement.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq2">
                                Comment est attribué mon premier filleul ?
                            </button>
                        </h2>
                        <div id="affFaq2" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Dès la création de votre compte, un lien principal unique est généré. Le tout premier événement créé par l'organisateur inscrit via ce lien vous attribue automatiquement vos commissions.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq3">
                                Comment toucher des commissions sur les événements suivants de mon filleul ?
                            </button>
                        </h2>
                        <div id="affFaq3" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Après le premier événement, il vous suffit de générer un lien/code spécifique depuis votre tableau de bord pour chaque nouvel événement de votre filleul que vous souhaitez parrainer.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affFaq4">
                                Quand et comment suis-je payé ?
                            </button>
                        </h2>
                        <div id="affFaq4" class="accordion-collapse collapse" data-bs-parent="#affFaq">
                            <div class="accordion-body text-muted small">Vos commissions sont comptabilisées en temps réel. Vous pouvez demander le retrait de vos gains directement depuis votre tableau de bord dès que les ventes de l'événement sont validées.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="aff-final h-100">
                    <h2 class="h3 fw-bold mb-2">Prêt à monétiser votre réseau événementiel ?</h2>
                    <p class="mb-4" style="color: rgba(255,255,255,0.8);">Rejoignez les créateurs, agences et partenaires qui génèrent déjà des revenus récurrents avec PaxEvent.</p>
                    <button type="button" class="btn btn-lg fw-bold px-4" style="background: var(--accent); color: var(--sombre);">
                        Créer mon compte affilié maintenant <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== 8. CONDITIONS GÉNÉRALES (repliables) ========== --}}
<section class="pb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="accordion aff-cg" id="affCgAccordion">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#affCg" aria-expanded="false">
                                <i class="bi bi-file-earmark-text me-2"></i>Conditions Générales du programme d'affiliation
                            </button>
                        </h2>
                        <div id="affCg" class="accordion-collapse collapse" data-bs-parent="#affCgAccordion">
                            <div class="accordion-body">
                                <p class="text-muted" style="font-size: 0.85rem;">Dernière mise à jour : {{ $derniereMiseAJour ?? 'Octobre 2026' }}</p>

                                <div class="mb-4" style="font-size: 0.9rem; line-height: 1.7;">
                                    <p>Les présentes Conditions Générales régissent le programme d'affiliation (ci-après le « Programme ») mis en place par Noctam Communication (ci-après « PaxEvent ») sur sa plateforme https://paxevent.com.</p>
                                    <p>Le Programme permet à des personnes physiques ou morales (ci-après les « Affiliés » ou « Apporteurs d'Affaires ») de parrainer des organisateurs, de promouvoir les événements publiés par ces organisateurs, et de percevoir une commission sur les ventes de billets effectivement tracées grâce à leurs actions promotionnelles.</p>
                                    <p><strong>Le Programme repose sur deux étapes strictement distinctes :</strong></p>
                                    <ol>
                                        <li><strong>Le parrainage d'un organisateur</strong> — condition préalable d'accès, <strong>non rémunérée</strong> ;</li>
                                        <li><strong>La promotion des événements de cet organisateur</strong> — seule source possible de commission, <strong>exclusivement sur les ventes tracées</strong>.</li>
                                    </ol>
                                    <p>Aucun parrainage d'organisateur, en soi, ne crée un droit à rémunération, quelle que soit la nature de la relation entre l'Affilié et l'Organisateur.</p>

                                    <h6 class="fw-bold mt-4 mb-2">1. Inscription et éligibilité</h6>
                                    <p><strong>Adhésion :</strong> L'inscription au Programme s'effectue via un espace dédié sur le tableau de bord PaxEvent. PaxEvent se réserve le droit d'accepter ou de refuser toute candidature sans obligation de justification.</p>
                                    <p><strong>Capacité légale :</strong> L'Affilié certifie être majeur et disposer de la capacité juridique ou des statuts professionnels nécessaires au Bénin pour percevoir des revenus d'apport d'affaires.</p>

                                    <h6 class="fw-bold mt-4 mb-2">2. Parrainage d'un organisateur (condition non rémunérée)</h6>
                                    <p><strong>Modalité :</strong> L'Affilié peut parrainer un Organisateur par l'intermédiaire d'un lien ou d'un formulaire de parrainage mis à disposition par PaxEvent.</p>
                                    <p><strong>Validation :</strong> Le parrainage n'est efficace qu'à compter de sa validation par PaxEvent.</p>
                                    <p><strong>Aucune rémunération :</strong> Le parrainage d'un Organisateur ne donne droit à aucune rémunération, directe ou indirecte. Il ouvre uniquement la possibilité de demander l'ouverture de la promotion sur les événements de l'Organisateur parrainé.</p>
                                    <p><strong>Exclusivité :</strong> Un Organisateur ne peut être parrainé que par un seul Affilié. Le parrainage est attribué au premier Affilié dont le parrainage est validé par PaxEvent ; toute tentative de parrainage concurrente est sans effet.</p>

                                    <h6 class="fw-bold mt-4 mb-2">3. Ouverture de la promotion</h6>
                                    <p><strong>Condition d'événement publié :</strong> L'Affilié ne peut générer aucun lien ni code de traçage tant que l'Organisateur parrainé n'a publié au moins un événement sur PaxEvent.</p>
                                    <p><strong>Absence de droit automatique :</strong> La publication d'un événement ne crée pas un droit automatique de promotion. PaxEvent peut refuser, restreindre ou suspendre l'ouverture de la promotion sur un événement donné (événement gratuit, non conforme, conflit d'intérêts ou fraude suspectée), sans obligation de justification.</p>
                                    <p><strong>Périmètre strict :</strong> L'Affilié ne peut promouvoir que les événements des Organisateurs qu'il a effectivement parrainés et validés. La génération d'un lien ou d'un code hors de ce périmètre est impossible et tout lien ou code ainsi obtenu est juridiquement sans effet.</p>
                                    <p><strong>Durée :</strong> Le droit de promotion demeure permanent tant que le parrainage reste validé, que l'Organisateur parrainé conserve un compte actif et que l'Affilié n'a pas encouru de sanction. PaxEvent peut mettre fin au Programme par publication sur la plateforme, avec un préavis de trente (30) jours pour les ventes en cours.</p>

                                    <h6 class="fw-bold mt-4 mb-2">4. Liens, codes et traçabilité des ventes</h6>
                                    <p><strong>Génération :</strong> Après validation de son compte et ouverture de la promotion, l'Affilié reçoit un lien URL unique et/ou un code promotionnel rattachés à son compte et à l'événement concerné.</p>
                                    <p><strong>Vente tracée :</strong> Une commission n'est due que si le lien ou le code de l'Affilié a été utilisé lors de l'achat, et si le paiement a été intégralement confirmé. Sans utilisation du lien ou du code, aucune commission n'est attribuée, même si l'événement appartient à un Organisateur parrainé. PaxEvent ne pourra être tenu responsable des ventes non comptabilisées si l'Acheteur final n'a pas utilisé le protocole de suivi fourni.</p>
                                    <p><strong>Règle d'attribution :</strong> L'attribution suit la règle du « dernier clic valide » : la commission revient à l'Affilié dont le lien ou le code a été utilisé au moment de l'achat, quel que soit le parcours préalable de l'Acheteur. Une même vente ne peut générer qu'une seule commission d'affiliation, au profit d'un seul Affilié.</p>
                                    <p><strong>Fenêtre de suivi :</strong> Le lien ouvre une session traçable de trente (30) jours à compter de sa première activation. Au-delà, l'attribution expire. Le code de l'Affilié reste valable tant qu'il est actif sur la plateforme.</p>
                                    <p><strong>Exclusions :</strong> Sont hors du champ du Programme les événements gratuits (0 FCFA), les billets physiques (lots à codes QR) — auxquels aucune commission d'affiliation ne se rattache — et toute vente n'ayant pas donné lieu à un paiement intégral et définitif.</p>

                                    <h6 class="fw-bold mt-4 mb-2">5. Calcul et taux de commission</h6>
                                    <p><strong>Base de calcul :</strong> La commission est calculée exclusivement sur la valeur faciale nette du billet vendu (hors frais de service de la plateforme et taxes).</p>
                                    <p><strong>Mode de calcul :</strong> La commission d'affiliation est prélevée sur la commission perçue par PaxEvent sur la vente concernée (la « commission plateforme ») et s'élève à <strong>20 % de cette commission</strong>, soit <strong>entre 1 % et 2 % de la valeur faciale nette du billet</strong> selon le taux perçu par PaxEvent (5 % à 10 %). Le taux applicable à chaque Organisateur ou événement est affiché à l'Affilié sur son tableau de bord <strong>avant le début de toute promotion</strong>.</p>
                                    <p><em>Exemple : pour un billet à 10 000 FCFA vendu avec une commission plateforme de 10 % (1 000 FCFA), l'Affilié perçoit 20 % de 1 000 FCFA, soit 200 FCFA (2 % du billet), et PaxEvent conserve 800 FCFA.</em></p>
                                    <p><strong>Plafond :</strong> La commission totale due à l'Affilié sur une vente ne peut en aucun cas excéder la commission perçue par PaxEvent sur cette même vente. Le cas échéant, le taux appliqué est automatiquement réduit pour respecter ce plafond. Si PaxEvent ne perçoit aucune commission sur la vente, aucune commission d'affiliation n'est due.</p>
                                    <p><strong>Événement gratuit :</strong> Un événement gratuit (0 FCFA) ne génère aucune commission ni droit à rémunération.</p>
                                    <p><strong>Changement de taux :</strong> Tout nouveau taux ne s'applique qu'aux ventes tracées postérieures à sa publication. Les ventes déjà tracées conservent le taux affiché au moment de leur attribution.</p>
                                    <p><strong>Annulation et remboursement :</strong> Si un billet fait l'objet d'un remboursement (annulation de l'événement, transaction frauduleuse ou rejet Mobile Money), la commission correspondante est automatiquement annulée ou, si elle a déjà été créditée, déduite du solde de l'Affilié. PaxEvent peut imputer ces déductions sur toute commission future due à l'Affilié. Chaque annulation est visible dans l'historique du compte Affilié.</p>

                                    <h6 class="fw-bold mt-4 mb-2">6. Modalités de paiement et reversement</h6>
                                    <p><strong>Seuil de paiement :</strong> Les commissions cumulées sont payables dès que le solde de l'Affilié atteint un seuil minimum de 5 000 XOF.</p>
                                    <p><strong>Canaux de paiement :</strong> Les reversements s'effectuent directement sur le numéro Mobile Money (MTN, Moov, Wave) ou le compte bancaire renseigné par l'Affilié.</p>
                                    <p><strong>Frais techniques de reversement :</strong> Conformément à notre infrastructure FedaPay, les frais de transfert de masse (PAYOUT) s'appliquent selon le barème officiel en vigueur de la plateforme.</p>
                                    <p><strong>Vérification d'identité :</strong> PaxEvent peut demander un justificatif d'identité avant tout premier reversement.</p>

                                    <h6 class="fw-bold mt-4 mb-2">7. Règles de conduite et pratiques interdites</h6>
                                    <p>L'Affilié s'engage à préserver l'image de marque de PaxEvent et de l'Organisateur. Sont strictement interdits :</p>
                                    <ul>
                                        <li><strong>Le Spamming :</strong> l'envoi massif d'e-mails, de SMS ou de messages WhatsApp non sollicités contenant les liens d'affiliation.</li>
                                        <li><strong>Le "Self-Affiliation" :</strong> le fait d'utiliser son propre lien d'affiliation pour acheter ses propres billets pour un événement dans le seul but de toucher une commission.</li>
                                        <li><strong>Publicité mensongère :</strong> divulguer de fausses informations sur l'événement (tarifs erronés, faux invités, fausses dates) pour forcer la vente.</li>
                                        <li><strong>Achat de mots-clés :</strong> l'achat de mots-clés sur Google Ads ou d'autres régies publicitaires contenant la marque "PaxEvent" ou le nom exact de l'événement sans accord écrit.</li>
                                        <li><strong>Parrainage frauduleux :</strong> le parrainage d'un Organisateur dont l'Affilié est lui-même l'organisateur, un associé, un mandataire ou un proche, dans le but de détourner le Programme à son profit.</li>
                                    </ul>

                                    <h6 class="fw-bold mt-4 mb-2">8. Responsabilité et indépendance</h6>
                                    <p>L'Affilié exerce son activité en totale indépendance. Il n'existe aucun lien de subordination, de contrat de travail ou de mandant de représentation exclusif entre PaxEvent et l'Affilié. L'Affilié est seul responsable de la déclaration fiscale de ses gains auprès des autorités compétentes au Bénin.</p>

                                    <h6 class="fw-bold mt-4 mb-2">9. Modification et résiliation</h6>
                                    <p><strong>Résiliation par l'Affilié :</strong> L'Affilié peut cesser sa participation au programme à tout moment en cessant d'utiliser ses liens.</p>
                                    <p><strong>Bannissement pour fraude :</strong> En cas de non-respect constaté de l'Article 7, PaxEvent annulera immédiatement le compte de l'Affilié sans préavis. Toutes les commissions en attente de paiement seront définitivement confisquées au profit de PaxEvent ou de l'Organisateur pour réparer le préjudice causé.</p>

                                    <h6 class="fw-bold mt-4 mb-2">10. Loi applicable</h6>
                                    <p>Les présentes conditions d'affiliation sont régies par le droit béninois (Code du numérique). Tout litige relatif à leur exécution ou leur interprétation relève de la compétence des tribunaux de Cotonou.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('accueil') }}" class="btn btn-outline-violet" style="border-radius: 8px;">
                        <i class="bi bi-arrow-left me-1"></i> Retour à accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
(function () {
    var section = document.getElementById('affGains');
    if (!section) { return; }

    var cards = section.querySelectorAll('.aff-gain-card');
    var counters = section.querySelectorAll('[data-count]');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var done = false;

    function format(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function countUp(el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        if (reduce) { el.textContent = format(target) + suffix; return; }

        var duration = 1400, start = null;
        el.textContent = '0' + suffix;
        function step(ts) {
            if (start === null) { start = ts; }
            var p = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = format(target * eased) + suffix;
            if (p < 1) { requestAnimationFrame(step); }
            else { el.textContent = format(target) + suffix; }
        }
        requestAnimationFrame(step);
    }

    function run() {
        if (done) { return; }
        done = true;
        cards.forEach(function (card, i) {
            setTimeout(function () { card.classList.add('in-view'); }, i * 130);
        });
        counters.forEach(function (el) { countUp(el); });
    }

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { run(); io.disconnect(); }
            });
        }, { threshold: 0.3 });
        io.observe(section);
    } else {
        run();
    }
})();
</script>
@endsection
