<style>
    :root {
        --av-violet: #542680;
        --av-violet-clair: #7a45a8;
        --av-encre: #211c31;
        --av-muet: #6b6575;
        --av-ligne: #e9e4ef;
        --av-fond: #f6f4f8;
        --av-vert: #167c46;
        --av-orange: #b45309;
    }

    *, *::before, *::after { box-sizing: border-box; }
    .av-app { min-height: 100vh; background: var(--av-fond); color: var(--av-encre); }
    button, input { font: inherit; }
    button, a, input, summary { -webkit-tap-highlight-color: transparent; }
    :focus-visible { outline: 3px solid #fed514; outline-offset: 2px; }

    /* En-tête compact, épinglé, avec zones tactiles larges */
    .av-topbar {
        position: sticky; top: 0; z-index: 50;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: calc(9px + env(safe-area-inset-top)) 14px 9px;
        background: var(--av-violet); color: #fff;
        box-shadow: 0 2px 12px rgba(33, 28, 49, .16);
    }
    .av-brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .av-brand a { display: inline-flex; align-items: center; flex: none; }
    .av-brand img { height: 30px; width: auto; flex: none; }
    .av-brand-divider { width: 1px; height: 26px; background: rgba(255,255,255,.35); flex: none; }
    .av-brand-event { min-width: 0; line-height: 1.15; }
    .av-brand-event span { display: block; font-size: .62rem; letter-spacing: .09em; text-transform: uppercase; color: rgba(255,255,255,.72); }
    .av-brand-event strong { display: block; font-size: .86rem; font-weight: 700; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .av-top-actions { display: flex; align-items: center; gap: 8px; flex: none; }
    .av-agent { display: none; align-items: center; gap: 6px; font-size: .8rem; color: rgba(255,255,255,.88); }
    .av-icon-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; border: 1px solid rgba(255,255,255,.35); border-radius: 12px;
        background: rgba(255,255,255,.08); color: #fff; cursor: pointer;
        transition: background .15s ease;
    }
    .av-icon-btn:hover { background: rgba(255,255,255,.18); }

    .av-main {
        max-width: 1440px; margin: 0 auto;
        padding: 14px 12px calc(28px + env(safe-area-inset-bottom));
    }

    /* Notifications */
    .av-alert {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 12px 14px; margin-bottom: 14px; border-radius: 14px;
        font-size: .88rem; line-height: 1.4;
    }
    .av-alert > i { font-size: 1.1rem; margin-top: 1px; }
    .av-alert-body { flex: 1; min-width: 0; }
    .av-alert-body strong { display: block; font-weight: 700; }
    .av-alert-body span, .av-alert-body li { color: var(--av-muet); }
    .av-alert-body ul { margin: 6px 0 0; padding-left: 18px; }
    .av-alert--success { background: #e7f7ed; border: 1px solid #bce6cb; color: var(--av-vert); }
    .av-alert--success a { display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; color: var(--av-violet); font-weight: 700; text-decoration: none; }
    .av-alert--error { background: #fff1f1; border: 1px solid #f4cccc; color: #b3261e; }
    .av-alert-close { border: 0; background: transparent; color: inherit; width: 32px; height: 32px; cursor: pointer; }

    /* Statistiques : 2 colonnes sur téléphone */
    .av-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .av-stat {
        display: flex; align-items: center; gap: 10px; min-width: 0;
        background: #fff; border: 1px solid var(--av-ligne); border-radius: 14px; padding: 11px 12px;
    }
    .av-stat > div { min-width: 0; }
    .av-stat strong { display: block; font-size: 1rem; font-weight: 800; line-height: 1.15; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .av-stat span:not(.av-stat-icon) { display: block; margin-top: 2px; font-size: .72rem; color: var(--av-muet); }
    .av-stat-icon { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 11px; flex: none; font-size: 1rem; }
    .av-stat-icon--violet { background: #f1e9f8; color: var(--av-violet); }
    .av-stat-icon--green { background: #e7f7ed; color: var(--av-vert); }
    .av-stat-icon--blue { background: #e9f1fd; color: #2563eb; }
    .av-stat-icon--amber { background: #fff6e5; color: var(--av-orange); }

    /* Cartes */
    .av-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; align-items: start; }
    .av-card { background: #fff; border: 1px solid var(--av-ligne); border-radius: 18px; overflow: hidden; }
    .av-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 16px 16px 12px; }
    .av-kicker { display: block; font-size: .68rem; text-transform: uppercase; letter-spacing: .09em; font-weight: 800; color: var(--av-violet); }
    .av-card-head h1, .av-card-head h2 { margin: 3px 0 0; font-size: 1.12rem; font-weight: 800; line-height: 1.25; }
    .av-live { display: inline-flex; align-items: center; gap: 6px; flex: none; font-size: .72rem; font-weight: 700; color: var(--av-vert); background: #e7f7ed; border-radius: 999px; padding: 5px 9px; }
    .av-live > span { width: 7px; height: 7px; border-radius: 50%; background: var(--av-vert); }
    .av-link { flex: none; display: inline-flex; align-items: center; gap: 5px; min-height: 36px; padding: 0 10px; border-radius: 10px; color: var(--av-violet); background: #f5eff9; font-size: .78rem; font-weight: 700; text-decoration: none; }
    .av-refresh { display: flex; align-items: center; gap: 6px; margin: 0; padding: 0 16px 10px; font-size: .72rem; color: var(--av-muet); }

    /* Formulaire */
    .av-block { margin: 0; padding: 14px 16px; border: 0; border-top: 1px solid var(--av-ligne); }
    .av-block legend { display: flex; align-items: center; gap: 9px; width: 100%; padding: 0 0 12px; font-size: .9rem; font-weight: 800; }
    .av-num { display: inline-flex; align-items: center; justify-content: center; width: 25px; height: 25px; border-radius: 50%; background: var(--av-violet); color: #fff; font-size: .75rem; flex: none; }
    .av-fields { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; }
    .av-field { display: block; min-width: 0; }
    .av-field > span { display: block; margin-bottom: 5px; font-size: .78rem; font-weight: 700; color: #4a4453; }
    .av-field > span em { color: #c53030; font-style: normal; }
    .av-field > span small { font-weight: 500; color: var(--av-muet); }
    .av-field input {
        width: 100%; min-height: 46px; padding: 10px 12px;
        border: 1.5px solid #ddd6e4; border-radius: 11px; background: #fff; font-size: .92rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .av-field input:focus { border-color: var(--av-violet); box-shadow: 0 0 0 3px rgba(84,38,128,.12); outline: none; }
    .av-field input::placeholder { color: #aaa3b0; }
    .av-error { display: block; margin-top: 5px; color: #b3261e; font-size: .76rem; }

    .av-tarifs { display: grid; gap: 8px; }
    .av-group-label { margin: 4px 0 0; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--av-muet); }
    .av-tarif {
        position: relative; display: flex; align-items: center; gap: 10px; min-height: 62px;
        padding: 10px 12px; border: 1.5px solid #e0d9e8; border-radius: 13px; background: #fff; cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }
    .av-tarif:active { background: #faf7fc; }
    .av-tarif input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .av-tarif:has(input:checked), .av-tarif.is-selected { border-color: var(--av-violet); background: #f8f4fb; }
    .av-tarif:has(input:focus-visible) { outline: 3px solid #fed514; outline-offset: 2px; }
    .av-tarif.is-disabled { opacity: .55; cursor: not-allowed; }
    .av-tarif-info { flex: 1; min-width: 0; }
    .av-tarif-info strong { display: block; font-size: .88rem; line-height: 1.25; }
    .av-tarif-info small { display: block; margin-top: 2px; font-size: .72rem; color: var(--av-muet); }
    .av-tarif-info small.is-full { color: #c53030; font-weight: 700; }
    .av-tarif-price { flex: none; font-size: .88rem; font-weight: 800; color: var(--av-violet); }
    .av-tarif-check { display: none; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: var(--av-violet); color: #fff; font-size: .75rem; flex: none; }
    .av-tarif:has(input:checked) .av-tarif-check, .av-tarif.is-selected .av-tarif-check { display: inline-flex; }
    .av-empty-inline, .av-capacity, .av-warning { margin: 8px 0 0; font-size: .76rem; color: var(--av-muet); }
    .av-capacity { line-height: 1.5; }
    .av-warning { display: flex; gap: 7px; align-items: flex-start; padding: 9px 11px; border-radius: 10px; background: #fff7e6; color: var(--av-orange); }

    /* Espèces et mobile money côte à côte, même sur petit écran */
    .av-pay { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .av-pay-option {
        position: relative; display: flex; align-items: center; gap: 8px; min-height: 58px;
        padding: 9px 10px; border: 1.5px solid #e0d9e8; border-radius: 13px; cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }
    .av-pay-option:active { background: #faf7fc; }
    .av-pay-option input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .av-pay-option:has(input:checked), .av-pay-option.is-selected { border-color: var(--av-violet); background: #f8f4fb; }
    .av-pay-option:has(input:focus-visible) { outline: 3px solid #fed514; outline-offset: 2px; }
    .av-pay-option.is-disabled { opacity: .55; cursor: not-allowed; }
    .av-pay-icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 9px; background: #f1e9f8; color: var(--av-violet); font-size: .95rem; flex: none; }
    .av-pay-text { min-width: 0; }
    .av-pay-text strong { display: block; font-size: .8rem; line-height: 1.2; }
    .av-pay-text small { display: block; font-size: .68rem; line-height: 1.25; color: var(--av-muet); }

    .av-promo { margin-top: 12px; border: 1px solid var(--av-ligne); border-radius: 12px; background: #fcfbfd; }
    .av-promo summary { display: flex; align-items: center; gap: 8px; min-height: 46px; padding: 0 12px; cursor: pointer; font-size: .8rem; font-weight: 700; list-style: none; }
    .av-promo summary::-webkit-details-marker { display: none; }
    .av-promo summary > span { font-weight: 500; color: var(--av-muet); }
    .av-promo-chevron { margin-left: auto; transition: transform .15s ease; }
    .av-promo[open] .av-promo-chevron { transform: rotate(180deg); }
    .av-promo > .av-field { padding: 0 12px 12px; }

    /* Barre d'action fixe sur téléphone */
    .av-checkout {
        position: sticky; bottom: 0; z-index: 10;
        padding: 12px 16px calc(14px + env(safe-area-inset-bottom));
        border-top: 1px solid var(--av-ligne); background: rgba(255,255,255,.97);
        backdrop-filter: blur(8px);
    }
    .av-total { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 9px; }
    .av-total span { font-size: .78rem; color: var(--av-muet); }
    .av-total strong { font-size: 1.25rem; font-weight: 800; color: var(--av-vert); }
    .av-submit {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 50px; border: 0; border-radius: 12px;
        background: var(--av-violet); color: #fff; font-size: .92rem; font-weight: 800; cursor: pointer;
        transition: background .15s ease, transform .15s ease;
    }
    .av-submit:hover { background: var(--av-violet-clair); }
    .av-submit:active { transform: translateY(1px); }
    .av-submit:disabled { opacity: .65; cursor: wait; }
    .av-hint { display: flex; align-items: center; justify-content: center; gap: 6px; margin: 8px 0 0; font-size: .72rem; color: var(--av-muet); text-align: center; }
    .av-hint .bi-whatsapp { color: #25d366; }

    /* Historique : cartes lisibles au pouce */
    .av-sales { display: grid; gap: 8px; padding: 0 12px 14px; max-height: none; }
    .av-sale { padding: 12px; border: 1px solid var(--av-ligne); border-radius: 13px; background: #fff; }
    .av-sale-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .av-sale-time { font-size: .72rem; font-weight: 700; color: var(--av-muet); font-variant-numeric: tabular-nums; }
    .av-sale-amount { font-size: .88rem; font-weight: 800; color: var(--av-vert); }
    .av-sale-name { display: block; margin-top: 4px; font-size: .88rem; }
    .av-sale-meta { margin-top: 2px; font-size: .74rem; color: var(--av-muet); }
    .av-sale-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 9px; padding-top: 8px; border-top: 1px dashed var(--av-ligne); }
    .av-sale-phone { font-size: .72rem; color: var(--av-muet); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .av-pdf { display: inline-flex; align-items: center; justify-content: center; gap: 5px; min-height: 34px; min-width: 56px; padding: 0 9px; border-radius: 9px; background: #f5eff9; color: var(--av-violet); font-size: .74rem; font-weight: 700; text-decoration: none; flex: none; }
    .av-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 32px 12px; color: var(--av-muet); font-size: .82rem; text-align: center; }
    .av-empty i { font-size: 1.6rem; }

    @media (max-width: 359px) {
        .av-pay-icon { width: 28px; height: 28px; font-size: .85rem; }
        .av-pay-text small { display: none; }
    }

    @media (min-width: 576px) {
        .av-main { padding: 18px 20px 32px; }
        .av-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .av-field--wide { grid-column: 1 / -1; }
        .av-sales { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .av-pay-option { min-height: 62px; padding: 10px 12px; gap: 10px; }
        .av-pay-icon { width: 36px; height: 36px; font-size: 1.05rem; }
        .av-pay-text strong { font-size: .86rem; }
        .av-pay-text small { font-size: .72rem; }
    }

    @media (min-width: 768px) {
        .av-agent { display: inline-flex; }
        .av-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .av-card-head { padding: 18px 20px 14px; }
        .av-block { padding: 16px 20px; }
        .av-sales { padding: 0 16px 16px; }
    }

    @media (min-width: 1024px) {
        .av-grid { grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr); gap: 18px; }
        .av-sales { grid-template-columns: minmax(0, 1fr); max-height: 620px; overflow-y: auto; }
        .av-checkout { position: static; padding: 16px 20px; background: #fff; }
        .av-submit { min-height: 48px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
    }

    /* Historique et paiement */
    .av-filters { display: flex; flex-wrap: wrap; gap: 8px; padding: 14px 16px; }
    .av-filter {
        display: inline-flex; align-items: center; justify-content: center;
        min-height: 40px; padding: 0 14px; border: 1.5px solid var(--av-ligne); border-radius: 999px;
        background: #fff; color: #4a4453; font-size: .8rem; font-weight: 700; text-decoration: none;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .av-filter:hover { background: #faf7fc; }
    .av-filter.is-active { background: var(--av-violet); border-color: var(--av-violet); color: #fff; }
    .av-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .av-summary .av-stat { flex-direction: column; align-items: flex-start; }
    .av-pagination { padding: 0 16px 16px; }
    .av-pagination .pagination { margin: 0; flex-wrap: wrap; gap: 4px; }
    .av-pagination .page-link { border-radius: 10px; border-color: var(--av-ligne); color: var(--av-violet); font-size: .8rem; }
    .av-pagination .page-item.active .page-link { background: var(--av-violet); border-color: var(--av-violet); color: #fff; }
    .av-recap { display: grid; grid-template-columns: minmax(0, 1fr); gap: 12px; padding: 4px 0 16px; }
    .av-recap-ligne { min-width: 0; padding: 12px 14px; border-radius: 12px; background: var(--av-fond); }
    .av-recap-ligne span { display: block; font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; color: var(--av-muet); }
    .av-recap-ligne strong { display: block; margin-top: 3px; font-size: .92rem; overflow-wrap: anywhere; }
    .av-recap-ligne code { display: inline-block; margin-top: 3px; padding: 3px 7px; border-radius: 7px; background: #fff; border: 1px solid var(--av-ligne); font-size: .82rem; }
    .av-recap-ligne--montant strong { color: var(--av-violet); font-size: 1.1rem; }
    .av-back { display: inline-flex; align-items: center; gap: 6px; }
    .av-action {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 52px; border: 0; border-radius: 13px;
        background: var(--av-violet); color: #fff; font-size: .94rem; font-weight: 800; cursor: pointer;
        transition: background .15s ease, transform .15s ease;
    }
    .av-action:hover { background: var(--av-violet-clair); }
    .av-action:active { transform: translateY(1px); }
    .av-action:disabled { opacity: .65; cursor: wait; }
    .av-secure { display: flex; align-items: center; justify-content: center; gap: 6px; margin: 10px 0 0; font-size: .74rem; color: var(--av-muet); }
    .av-secure strong { color: var(--av-violet); }
    .av-error-box { margin-top: 12px; padding: 10px 12px; border-radius: 10px; background: #fff1f1; color: #b3261e; font-size: .8rem; }
    .av-footer-link { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; margin-top: 12px; color: var(--av-violet); font-size: .8rem; font-weight: 700; text-decoration: none; }
</style>
