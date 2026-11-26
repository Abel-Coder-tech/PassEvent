<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PaxEvent')</title>
    <script>document.documentElement.classList.add('fouc');</script>
    <style>html.fouc body{visibility:hidden!important}</style>
    <link rel="preload" href="/assets/css/bootstrap.min.css" as="style">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            /* ---- Charte PaxEvent : une seule source de vérité ---- */
            --violet: #542680;
            --violet-clair: #7b3fa0;
            --violet-fonce: #3f1c60;
            --violet-pale: #f2ecf8;
            --accent: #fed514;
            --sombre: #211c31;

            /* Neutres */
            --blanc: #ffffff;
            --fond: #f4f3f7;
            --ligne: #e9e5ef;
            --gris: #98919b;
            --gris-fonce: #625c6b;
            --gris-clair: #f7f6f9;

            /* Sémantique : vert = argent / succès, bleu = mobile money, orange = espèces, rouge = erreur */
            --vert: #12976e;
            --vert-fonce: #0e7a59;
            --vert-pale: #e7f5f0;
            --teal: #428c79;
            --teal-pale: #eaf1ef;
            --info: #2f80b8;
            --info-pale: #e9f3fa;
            --warning: #d68910;
            --warning-pale: #fdf3e3;
            --danger: #e74c3c;
            --danger-pale: #fdeceb;

            /* Alias de compatibilité avec les anciennes pages */
            --menthe: var(--accent);
            --aubergine: var(--violet-fonce);
            --violet-dark: var(--violet-fonce);
            --blanc-casse: var(--fond);
        }

        /* Bootstrap : le bleu par défaut passe au violet PaxEvent */
        :root {
            --bs-primary: #542680;
            --bs-primary-rgb: 84, 38, 128;
            --bs-link-color: #542680;
            --bs-link-color-rgb: 84, 38, 128;
            --bs-link-hover-color: #3f1c60;
            --bs-link-hover-color-rgb: 63, 28, 96;
            --bs-body-color: #211c31;
            --bs-border-color: #e9e5ef;
        }
        .form-check-input:checked { background-color: var(--violet); border-color: var(--violet); }
        .form-check-input:focus { border-color: var(--violet-clair); box-shadow: 0 0 0 0.15rem rgba(84, 38, 128, 0.16); }
        .btn-primary { --bs-btn-bg: #542680; --bs-btn-border-color: #542680; --bs-btn-hover-bg: #3f1c60; --bs-btn-hover-border-color: #3f1c60; --bs-btn-active-bg: #3f1c60; --bs-btn-active-border-color: #3f1c60; --bs-btn-disabled-bg: #542680; --bs-btn-disabled-border-color: #542680; }
        .btn-outline-primary { --bs-btn-color: #542680; --bs-btn-border-color: #542680; --bs-btn-hover-bg: #542680; --bs-btn-hover-border-color: #542680; --bs-btn-active-bg: #542680; --bs-btn-active-border-color: #542680; }
        .alert-info { --bs-alert-color: #3f1c60; --bs-alert-bg: #f2ecf8; --bs-alert-border-color: #ddd0ea; --bs-alert-link-color: #542680; }
        .alert-success { --bs-alert-color: #0e7a59; --bs-alert-bg: #e7f5f0; --bs-alert-border-color: #c7e8dc; }
        .alert-warning { --bs-alert-color: #8b6914; --bs-alert-bg: #fdf3e3; --bs-alert-border-color: #f3e0b8; }
        .alert-danger { --bs-alert-color: #a93226; --bs-alert-bg: #fdeceb; --bs-alert-border-color: #f5c6c2; }
        .text-primary { color: var(--violet) !important; }
        .bg-primary { background-color: var(--violet) !important; }
        .border-primary { border-color: var(--violet) !important; }

        * { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--fond);
            color: var(--sombre);
            margin: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a { color: var(--violet); }
        a:hover { color: var(--violet-fonce); }

        :focus-visible { outline: 2px solid var(--violet-clair); outline-offset: 2px; }

        /* ---------- Sidebar ---------- */
        .sidebar {
            width: 236px;
            height: 100vh;
            background: var(--sombre);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            transition: transform 0.28s cubic-bezier(.4, 0, .2, 1);
        }

        .sidebar-logo {
            padding: 1.1rem 1.15rem;
            border-bottom: 1px solid rgba(255,255,255,0.09);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-shrink: 0;
            min-height: 74px;
        }

        .sidebar-logo-icon {
            width: 34px;
            height: 34px;
            background: var(--violet);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            position: relative;
        }

        .sidebar-logo-icon::before,
        .sidebar-logo-icon::after {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--sombre);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
        }

        .sidebar-logo-icon::before { left: -3px; }
        .sidebar-logo-icon::after { right: -3px; }

        .sidebar-logo-text span:first-child {
            color: var(--accent);
            font-weight: 800;
            font-size: 1.12rem;
            letter-spacing: .2px;
        }

        .sidebar-logo-text span:last-child {
            color: #fff;
            font-weight: 700;
            font-size: 1.12rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.65rem 0.6rem;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.18) transparent;
        }

        .sidebar-section-label {
            padding: 0.9rem 0.7rem 0.35rem;
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            color: #7d7689;
        }

        .sidebar .nav-link {
            color: #c6c0d0;
            padding: 0.55rem 0.7rem;
            margin-bottom: 2px;
            font-size: 0.82rem;
            border-radius: 9px;
            transition: background 0.18s, color 0.18s, transform 0.18s;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sidebar .nav-link i {
            width: 16px;
            font-size: 0.88rem;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.07);
        }

        .sidebar button.nav-link {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(123, 63, 160, 0.32);
            font-weight: 700;
            box-shadow: inset 3px 0 0 var(--accent);
        }
        .sidebar .nav-link.active i { color: var(--accent); }

        .profil-btn {
            width: 38px; height: 38px; border-radius: 50%; border: 0; padding: 0;
            background: var(--violet); color: #fff; font-weight: 700; font-size: 0.78rem;
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer;
        }
        .profil-btn:hover, .profil-btn[aria-expanded="true"] { box-shadow: 0 0 0 3px rgba(123,63,160,0.25); }
        .profil-menu { min-width: 220px; border-radius: 12px; border: 1px solid #ececec; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 0.4rem; margin-top: 0.4rem !important; }
        .profil-menu-entete { padding: 0.45rem 0.75rem 0.2rem; }
        .profil-menu-nom { font-weight: 700; font-size: 0.85rem; color: var(--sombre); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .profil-menu-role { font-size: 0.72rem; color: var(--gris); }
        .profil-menu .dropdown-item { border-radius: 8px; font-size: 0.85rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.6rem; }
        .profil-menu .dropdown-divider { margin: 0.3rem 0; }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(33, 28, 49, 0.55);
            backdrop-filter: blur(2px);
            z-index: 1040;
        }
        .sidebar-overlay.active { display: block; }

        /* ---------- Main ---------- */
        .main-content {
            margin-left: 236px;
            min-height: 100vh;
            transition: margin-left 0.28s cubic-bezier(.4, 0, .2, 1);
        }

        /* Bouton flottant « Contacter PaxEvent » */
        .contact-fab {
            position: fixed;
            bottom: 22px;
            right: 22px;
            z-index: 1045;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--violet);
            color: #fff;
            font-size: 1.3rem;
            box-shadow: 0 8px 22px rgba(84, 38, 128, 0.35);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .contact-fab:hover {
            transform: translateY(-3px);
            background: var(--violet-fonce);
            box-shadow: 0 12px 26px rgba(84, 38, 128, 0.45);
        }
        .contact-fab:focus { outline: none; }
        @media (max-width: 575.98px) {
            .contact-fab { width: 46px; height: 46px; font-size: 1.15rem; bottom: 14px; right: 14px; border-radius: 14px; }
        }

        /* ---------- Top bar ---------- */
        .top-bar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--ligne);
            padding: 0.85rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .top-bar-left h2 {
            font-size: 1.18rem;
            font-weight: 800;
            color: var(--sombre);
            margin: 0;
            letter-spacing: -0.01em;
        }

        .top-bar-left p {
            font-size: 0.75rem;
            color: var(--gris);
            margin: 0.18rem 0 0;
        }

        .top-bar-left p span {
            color: var(--violet);
            font-weight: 700;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: var(--sombre);
            font-size: 1.5rem;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
        }

        /* ---------- Cartes de métriques ---------- */
        .metric-card {
            background: var(--blanc);
            border: 1px solid var(--ligne);
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            border-top: 3px solid transparent;
            transition: transform 0.18s, box-shadow 0.18s, border-color 0.18s;
            height: 100%;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(33, 28, 49, 0.07);
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            margin-bottom: 0.8rem;
        }

        .metric-label {
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--gris);
            margin-bottom: 0.3rem;
        }

        .metric-value {
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--sombre);
            line-height: 1.2;
            word-break: break-word;
        }

        .metric-subtitle {
            font-size: 0.75rem;
            color: var(--gris);
            margin-top: 0.2rem;
        }

        /* ---------- Panneaux ---------- */
        .panel-card {
            background: var(--blanc);
            border: 1px solid var(--ligne);
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
        }

        .panel-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--ligne);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            background: linear-gradient(180deg, #fff, #fcfbfd);
        }

        .panel-card-header h5 {
            font-size: 0.92rem;
            font-weight: 800;
            color: var(--sombre);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .panel-card-header a {
            font-size: 0.78rem;
            color: var(--violet);
            text-decoration: none;
            font-weight: 700;
        }
        .panel-card-header a:hover { color: var(--violet-fonce); }

        .panel-card-body { padding: 1.25rem; }

        /* ---------- Listes d'événements ---------- */
        .event-row {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--ligne);
        }
        .event-row:last-child { border-bottom: none; }

        .event-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .event-info { flex: 1; min-width: 0; }
        .event-name {
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--sombre);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event-meta { font-size: 0.7rem; color: var(--gris); }

        /* ---------- Badges de statut ---------- */
        .status-badge {
            font-size: 0.68rem;
            padding: 0.28rem 0.65rem;
            border-radius: 20px;
            font-weight: 700;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .status-en-cours { background: var(--vert-pale); color: var(--vert); }
        .status-a-venir { background: var(--violet-pale); color: var(--violet); }
        .status-termine,
        .status-brouillon { background: var(--gris-clair); color: var(--gris-fonce); }

        /* ---------- Progression ---------- */
        .progress-bar-custom {
            height: 6px;
            border-radius: 99px;
            background: var(--gris-clair);
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: var(--violet);
            transition: width 0.5s ease;
        }

        /* ---------- Journal d'activité ---------- */
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--ligne);
            font-size: 0.78rem;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-top: 0.35rem;
            margin-right: 0.65rem;
            flex-shrink: 0;
        }
        .activity-text { flex: 1; color: var(--sombre); }
        .activity-text strong { font-weight: 700; }
        .activity-time { color: var(--gris); font-size: 0.68rem; white-space: nowrap; margin-left: 0.5rem; }

        /* ---------- Codes promo ---------- */
        .promo-code {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--sombre);
        }
        .promo-status { font-size: 0.7rem; font-weight: 600; }
        .promo-utilise { color: var(--vert-fonce); }
        .promo-disponible { color: var(--gris); }

        /* ---------- Mini stats ---------- */
        .mini-stat {
            background: var(--gris-clair);
            border-radius: 11px;
            padding: 0.75rem 1rem;
            text-align: center;
        }
        .mini-stat-value {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--sombre);
        }
        .mini-stat-value.text-rouge { color: var(--danger); }
        .mini-stat-value.text-vert { color: var(--vert-fonce); }
        .mini-stat-label { font-size: 0.68rem; color: var(--gris); font-weight: 500; }

        /* ---------- Boutons ---------- */
        .btn-vert,
        .btn-primary-custom {
            background: var(--violet);
            border: 1px solid var(--violet);
            color: #fff;
            font-weight: 600;
        }
        .btn-vert:hover,
        .btn-primary-custom:hover {
            background: var(--violet-fonce);
            border-color: var(--violet-fonce);
            color: #fff;
        }
        .btn-outline-vert {
            background: transparent;
            border: 1px solid var(--violet);
            color: var(--violet);
            font-weight: 600;
        }
        .btn-outline-vert:hover { background: var(--violet); color: #fff; }
        .btn-outline-rouge {
            border: 1px solid var(--danger);
            color: var(--danger);
            font-weight: 600;
            background: transparent;
        }
        .btn-outline-rouge:hover { background: var(--danger); color: #fff; }
        .btn-secondary-custom {
            background: transparent;
            border: 1px solid var(--ligne);
            color: var(--sombre);
            font-weight: 600;
            font-size: 0.82rem;
        }
        .btn-secondary-custom:hover { background: var(--gris-clair); border-color: var(--gris); }

        .btn { transition: transform .15s, box-shadow .15s, background .15s, border-color .15s; }
        .btn:active { transform: translateY(1px); }

        /* Boutons d'action icône */
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: 1px solid var(--ligne);
            border-radius: 8px;
            background: transparent;
            color: var(--sombre);
            text-decoration: none;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.15s;
            padding: 0;
            line-height: 1;
        }
        .btn-icon:hover { background: var(--violet-pale); border-color: var(--violet); color: var(--violet); }
        .btn-icon-vert { color: var(--violet); border-color: var(--violet); }
        .btn-icon-vert:hover { background: var(--violet-pale); color: var(--violet); border-color: var(--violet); }
        .btn-icon-danger { color: var(--danger); border-color: var(--danger); }
        .btn-icon-danger:hover { background: var(--danger-pale); color: var(--danger); border-color: var(--danger); }
        .btn-icon-gris { color: var(--gris); border-color: var(--ligne); }
        .btn-icon-gris:hover { background: var(--gris-clair); color: var(--gris-fonce); border-color: var(--gris); }

        /* ---------- Pagination ---------- */
        .pagination { margin-bottom: 0; gap: 3px; flex-wrap: wrap; }
        .page-link {
            border: 1px solid var(--ligne);
            color: var(--sombre);
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.3rem 0.5rem;
            border-radius: 7px !important;
            margin: 0;
            min-width: 30px;
            text-align: center;
        }
        .page-link i {
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .page-item:not(:first-child) .page-link { margin-left: 0; }
        .page-item.active .page-link { background: var(--violet); border-color: var(--violet); color: #fff; }
        .page-item.disabled .page-link { background: var(--gris-clair); border-color: var(--ligne); color: var(--gris); }
        .page-link:hover { background: var(--violet-pale); border-color: var(--violet); color: var(--violet); }
        @media (max-width: 576px) {
            .pagination { justify-content: center !important; gap: 2px; }
            .page-link { font-size: 0.75rem; padding: 0.25rem 0.4rem; min-width: 28px; }
            .page-link i { font-size: 0.6rem; }
        }

        /* ---------- Formulaires ---------- */
        .form-control:focus, .form-select:focus,
        .form-control:focus-visible, .form-select:focus-visible {
            border-color: var(--violet);
            box-shadow: 0 0 0 0.15rem rgba(84, 38, 128, 0.14);
        }
        .form-control, .form-select { border-color: var(--ligne); border-radius: 9px; }

        /* ---------- Contenu ---------- */
        .page-content { padding: 1.5rem 1.75rem 2.5rem; min-width: 0; }
        .breadcrumb-bar { overflow-x: auto; white-space: nowrap; }
        .d-flex > *, .row > * { min-width: 0; }

        /* ---------- Alertes ---------- */
        .alert { border-radius: 11px; font-size: 0.85rem; }

        /* ---------- Tableaux ---------- */
        .table-responsive { -webkit-overflow-scrolling: touch; }
        .custom-table th {
            white-space: nowrap;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gris);
            border-bottom: 2px solid var(--ligne);
        }
        .custom-table td { vertical-align: middle; font-size: 0.85rem; }

        /* Composants partagés */
        .section-title {
            display: flex;
            align-items: center;
            gap: .55rem;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--gris-fonce);
            margin: 0 0 .9rem;
        }
        .app-alert {
            display: flex;
            align-items: center;
            gap: .85rem;
            flex-wrap: wrap;
            border-radius: 13px;
            padding: .8rem 1rem;
            font-size: .85rem;
            border: 1px solid transparent;
            border-left: 4px solid var(--violet);
            background: var(--violet-pale);
            color: var(--violet-fonce);
            margin-bottom: 1rem;
        }
        .app-alert > div:first-child { flex: 1; min-width: 0; }
        .app-alert.alert-warn { background: var(--warning-pale); border-color: #f3e0b8; border-left-color: var(--warning); color: #8b6914; }
        .app-alert.alert-ok { background: var(--vert-pale); border-color: #c7e8dc; border-left-color: var(--vert); color: var(--vert-fonce); }
        .app-alert.alert-info { background: var(--info-pale); border-color: #cfe4f2; border-left-color: var(--info); color: #245f8a; }
        .app-alert .btn { flex-shrink: 0; }
        .app-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            border-radius: 9px;
            border: 1px solid var(--ligne);
            background: var(--blanc);
            color: var(--sombre);
            font-size: .78rem;
            font-weight: 700;
            padding: .45rem .8rem;
            text-decoration: none;
            white-space: nowrap;
            min-height: 38px;
        }
        .app-btn:hover { background: var(--gris-clair); border-color: var(--gris); color: var(--sombre); }
        .app-btn.primary { background: var(--violet); border-color: var(--violet); color: #fff; }
        .app-btn.primary:hover { background: var(--violet-fonce); border-color: var(--violet-fonce); color: #fff; }
        .app-btn.vert { background: var(--vert); border-color: var(--vert); color: #fff; }
        .app-btn.vert:hover { background: var(--vert-fonce); border-color: var(--vert-fonce); color: #fff; }
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--gris);
        }
        .empty-state i { font-size: 2rem; display: block; margin-bottom: .5rem; color: var(--gris); }
        .empty-state small { font-size: .82rem; }

        /* Scrollbar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.18); border-radius: 3px; }
        ::-webkit-scrollbar { width: 9px; height: 9px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d7d2de; border-radius: 99px; border: 2px solid var(--fond); }
        ::-webkit-scrollbar-thumb:hover { background: #bdb5c8; }

        /* ---------- Responsive ---------- */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); box-shadow: 8px 0 30px rgba(33,28,49,.2); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .hamburger-btn { display: inline-flex; }
            .top-bar { padding: 0.75rem 1rem; flex-wrap: wrap; }
            .top-bar-right { flex: 1; justify-content: flex-end; }
            .top-bar-left h2 { font-size: 1.05rem; }
            .top-bar-left p { font-size: 0.7rem; }
            .page-content { padding: 1rem 1rem 2rem; }
            .panel-card-body { padding: 1rem; }
            .metric-card { padding: 1rem; }
            .metric-value { font-size: 1.5rem; }
            .panel-card-header { padding: 0.8rem 1rem; }
            .breadcrumb-bar { padding-left: 1rem; padding-right: 1rem; }
        }

        @media (max-width: 575.98px) {
            .top-bar-right .btn span,
            .top-bar-right .btn-text { display: none; }
            .top-bar-right .btn-sm { padding: 0.35rem 0.5rem; font-size: 0.75rem; }
            .top-bar-left p { display: none; }
            .top-bar-right .btn { white-space: nowrap; }
            .top-bar-right .btn:last-child { margin-right: 0; }
            .top-bar-right .btn-group {
                flex-wrap: nowrap;
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .event-card .panel-card-body { padding: 0.75rem !important; }
            .event-card .d-flex.justify-content-end.gap-2 { flex-wrap: wrap; }
            .event-card .d-flex.justify-content-end.gap-2 .btn { flex: 1; min-width: 0; text-align: center; }
            .metric-card { padding: 0.9rem; border-radius: 12px; }
            .metric-value { font-size: 1.3rem; }
            .metric-label { font-size: 0.6rem; }
            .metric-icon { width: 34px; height: 34px; margin-bottom: .6rem; }
            .panel-card-header h5 { font-size: 0.85rem; }
            .status-badge { font-size: 0.62rem; padding: 0.22rem 0.5rem; }
            .page-content { padding: 0.9rem 0.75rem 1.75rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; scroll-behavior: auto !important; }
        }

        /* ---------- Toasts ---------- */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            left: auto;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
            max-width: calc(100vw - 40px);
        }
        .toast-item {
            pointer-events: auto;
            min-width: 320px;
            max-width: 420px;
            padding: 14px 18px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(33,28,49,.14);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: toastIn 0.3s ease;
            font-size: 14px;
            border-left: 4px solid var(--gris);
        }
        @media (max-width: 575.98px) {
            .toast-container { top: 10px; right: 10px; left: 10px; max-width: none; }
            .toast-item { min-width: 0; width: 100%; }
        }
        .toast-item.toast-success { border-left-color: var(--vert); }
        .toast-item.toast-error { border-left-color: var(--danger); }
        .toast-item .toast-msg { flex: 1; }
        .toast-item .toast-msg a { color: var(--violet); font-weight: 700; text-decoration: underline; }
        .toast-item .toast-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .toast-item .toast-btn {
            padding: 6px 14px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            cursor: pointer;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .toast-item .toast-btn:hover { opacity: 0.85; }
        .toast-item .toast-btn-retry { background: var(--danger); color: #fff; }
        .toast-item .toast-btn-cancel { background: var(--gris-clair); color: var(--gris-fonce); }
        .toast-item .toast-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gris);
            padding: 0 4px;
            line-height: 1;
        }
        .toast-item .toast-close:hover { color: var(--sombre); }
        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
    @yield('styles')
</head>
<body>
@if(session('gestion'))
<div style="position:sticky;top:0;z-index:3000;background:#FED514;color:#211C31;padding:.55rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;font-weight:600;font-size:.88rem;border-bottom:2px solid #211C31;">
    <span><i class="bi bi-person-gear me-1"></i> Vous gérez <strong>{{ Auth::user()?->nom }}</strong> au nom de PaxEvent. Vos actions sont enregistrées dans son journal.</span>
    <form action="{{ route('admin.gestion.quitter') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn btn-sm" style="background:#211C31;color:#fff;font-weight:600;border-radius:8px;"><i class="bi bi-box-arrow-left me-1"></i> Quitter</button>
    </form>
</div>
@endif

@auth
    <!-- Sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="72" style="filter:brightness(0) invert(1);">
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Principal</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
            </a>
            @if(auth()->user()->role === 'super_admin')
            <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->routeIs('superadmin.*') ? 'active' : '' }}">
                <i class="bi bi-shield-fill-check" style="color: #6B3FA0;"></i> Super Admin
            </a>
            @endif
            <a href="{{ route('admin.evenements.index') }}" class="nav-link {{ request()->routeIs('admin.evenements.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Événements
            </a>
            <a href="{{ route('admin.messages.index') }}" class="nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                <i class="bi bi-envelope"></i> Messages
                @php $unreadMessages = \App\Models\Message::where('lu', false)->where('user_id', auth()->id())->count(); @endphp
                @if($unreadMessages > 0)
                    <span class="badge bg-danger ms-auto" style="font-size: 0.65rem;">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="{{ route('ventes-manuelles.create') }}" class="nav-link {{ request()->routeIs('ventes-manuelles.*') ? 'active' : '' }}">
                <i class="bi bi-bag"></i> Vente manuelle
            </a>

            <div class="sidebar-section-label">Billetterie</div>
            <a href="{{ route('admin.scan-codes.index') }}" class="nav-link {{ request()->routeIs('admin.scan-codes.*') ? 'active' : '' }}">
                <i class="bi bi-key"></i> Générer code d'accès
            </a>
            <a href="{{ route('scan.index') }}" class="nav-link {{ request()->routeIs('scan.*') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i> Scan QR
            </a>
            <a href="{{ route('admin.agents.index') }}" class="nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Agents de scan
            </a>
            <a href="{{ route('admin.agents-vente.index') }}" class="nav-link {{ request()->routeIs('admin.agents-vente.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check"></i> Agents de vente
            </a>
            <a href="{{ route('admin.lots-physiques.index') }}" class="nav-link {{ request()->routeIs('admin.lots-physiques.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated-fill"></i> Vente physique
            </a>
            <a href="{{ route('admin.codes-promos.index') }}" class="nav-link {{ request()->routeIs('admin.codes-promos.*') ? 'active' : '' }}">
                <i class="bi bi-tag"></i> Codes promos
            </a>
            <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i> Tickets
            </a>
            <a href="{{ route('admin.remboursements.index') }}" class="nav-link {{ request()->routeIs('admin.remboursements.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i> Remboursements
            </a>

            <div class="sidebar-section-label">Finances</div>
            <a href="{{ route('admin.retraits.index') }}" class="nav-link {{ request()->routeIs('admin.retraits.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Retraits
            </a>
            <a href="{{ route('admin.affiliations.index') }}" class="nav-link {{ request()->routeIs('admin.affiliations.*') ? 'active' : '' }}">
                <i class="bi bi-share"></i> Affiliation
            </a>

            <div class="sidebar-section-label">Analyse</div>
            <a href="{{ route('statistiques.index') }}" class="nav-link {{ request()->routeIs('statistiques.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Statistiques
            </a>

        </nav>

    </div>
@endauth

<!-- Main Content -->
<div class="main-content">
    @auth
    <div class="top-bar">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="top-bar-left">
                <h2>@yield('page-title', 'Tableau de bord')</h2>
                <p>{{ now()->isoFormat('D MMMM YYYY') }}</p>
            </div>
        </div>
        <div class="top-bar-right">
            @php $topbarUnread = \App\Models\Message::where('lu', false)->where('user_id', auth()->id())->count(); @endphp
            <a href="{{ route('admin.messages.index') }}" class="btn btn-sm position-relative" style="border-radius:10px;border:1px solid #e5e5e5;background:var(--blanc);" title="Notifications">
                <i class="bi bi-bell" style="font-size:1.05rem;"></i>
                @if($topbarUnread > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:#dc3545;font-size:0.6rem;">{{ $topbarUnread }}</span>
                @endif
            </a>
            @yield('topbar-actions')
            @if(Auth::user()->statut !== 'bloque')
            <a href="{{ route('admin.evenements.create') }}" class="btn btn-vert btn-sm {{ request()->routeIs('admin.evenements.create', 'admin.evenements.edit', 'admin.evenements.show', 'admin.codes-promos.*') ? 'd-none d-sm-inline-flex' : '' }}">
                <i class="bi bi-plus-lg me-1"></i> <span class="btn-text">@if(request()->routeIs('statistiques.index')) Créer @else Créer un événement @endif</span>
            </a>
            @endif
            <div class="dropdown">
                <button type="button" class="profil-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mon compte" title="{{ Auth::user()->nom }}">
                    {{ strtoupper(mb_substr(Auth::user()->nom, 0, 2)) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end profil-menu">
                    <li class="profil-menu-entete">
                        <div class="profil-menu-nom">{{ Auth::user()->nom }}</div>
                        <div class="profil-menu-role">{{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Organisateur' }}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('parametres.index') }}"><i class="bi bi-gear"></i> Paramètres</a></li>
                    <li><a class="dropdown-item" href="{{ route('evenements.public') }}" target="_blank"><i class="bi bi-globe2"></i> Voir le site</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal"><i class="bi bi-box-arrow-left"></i> Déconnexion</button></li>
                </ul>
            </div>
        </div>
    </div>

    @if(Auth::user()->statut === 'bloque')
    <div style="background:#fdecea;border-bottom:1px solid #f5c6c2;color:#a93226;padding:0.7rem 2rem;font-size:0.88rem;display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
        <i class="bi bi-exclamation-octagon-fill" style="font-size:1.05rem;flex-shrink:0;"></i>
        <strong>Compte temporairement désactivé</strong>
        <span>— Vous ne pouvez effectuer aucune action pour le moment. Contactez PaxEvent (<a href="mailto:contact@paxevent.com" style="color:#a93226;font-weight:600;">contact@paxevent.com</a>) pour plus d'informations.</span>
    </div>
    @endif

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded',function(){showToast('success',{!! Js::from(session('success')) !!});});</script>
    @endif

    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded',function(){showToast('error',{!! Js::from(session('error')) !!});});</script>
    @endif
    @endauth

    @auth
        @hasSection('breadcrumb')
            <div class="breadcrumb-bar" style="padding: 0.6rem 2rem; background: var(--blanc); border-bottom: 1px solid #e5e5e5; font-size: 0.82rem;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
        @endif
    @endauth

    @yield('content')
</div>

@guest
    @yield('content')
@endguest

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (hamburger && sidebar && overlay) {
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Close sidebar when clicking a nav link on mobile
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    }
});
</script>
<div id="toast-container" class="toast-container"></div>
<script>
function escapeHtml(str){if(!str)return'';var d=document.createElement('div');d.textContent=str;return d.innerHTML;}
function showToast(type,message){var c=document.getElementById('toast-container');if(!c||!message)return;var i=type==='success'?'bi-check-circle-fill':'bi-x-circle-fill';var cl=type==='success'?'#27ae60':'#e74c3c';var t=document.createElement('div');t.className='toast-item toast-'+type;var msg=document.createElement('span');msg.className='toast-msg';msg.textContent=message;t.innerHTML='<i class="bi '+i+'" style="color:'+cl+';font-size:1.3rem"></i>';t.appendChild(msg);if(type==='error'){var a=document.createElement('div');a.className='toast-actions';a.innerHTML='<button class="toast-btn toast-btn-retry" onclick="location.reload()">Réessayer</button><button class="toast-btn toast-btn-cancel" onclick="this.closest(\'.toast-item\').remove()">Annuler</button>';t.appendChild(a)}else{var b=document.createElement('button');b.className='toast-close';b.innerHTML='&times;';b.onclick=function(){t.remove()};t.appendChild(b)}c.appendChild(t);if(type!=='error'){setTimeout(function(){t.style.animation='toastOut 0.3s ease forwards';setTimeout(function(){t.remove()},300)},4000)}}
</script>
<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-copier-code');
    if (!btn || !btn.dataset.code) return;
    const code = btn.dataset.code;
    const confirmer = function () {
        if (typeof showToast === 'function') showToast('success', 'Code copié : ' + code);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(confirmer).catch(function () { copierTexte(code, confirmer); });
    } else {
        copierTexte(code, confirmer);
    }
});

function copierTexte(texte, apres) {
    const zone = document.createElement('textarea');
    zone.value = texte;
    zone.style.position = 'fixed';
    zone.style.opacity = '0';
    document.body.appendChild(zone);
    zone.select();
    try { document.execCommand('copy'); apres(); } catch (err) {}
    document.body.removeChild(zone);
}
</script>
@yield('scripts')
@yield('chart-scripts')

@auth
    @if(auth()->user()->role !== 'super_admin')
    <button type="button" class="contact-fab" data-bs-toggle="modal" data-bs-target="#demandeSuperadminModal" title="Contacter PaxEvent" aria-label="Contacter PaxEvent">
        <i class="bi bi-headset"></i>
    </button>
    @endif
    @include('partials.demande-superadmin-modal')

    {{-- Confirmation de déconnexion --}}
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
                <div class="modal-header border-0 pb-0 pt-3 px-3">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="logoutConfirmModalLabel" style="font-weight:800;color:var(--sombre);font-size:1rem;">
                        <i class="bi bi-box-arrow-right" style="color:var(--danger);"></i> Se déconnecter
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-3 py-2">
                    <p class="mb-0" style="color:var(--gris-fonce);font-size:.85rem;">Souhaitez-vous vraiment vous déconnecter ?</p>
                </div>
                <div class="modal-footer border-0 pt-0 px-3 pb-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="background:var(--gris-clair);color:var(--sombre);border-radius:8px;font-weight:600;">Non</button>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="background:var(--danger);color:#fff;border-radius:8px;font-weight:700;">Oui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endauth
<script>document.documentElement.classList.remove('fouc');</script>
</body>
</html>
