<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('images/logo-header.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php $paxSuivi = \App\Models\ParametreSite::suivi(); @endphp
    @if(!empty($paxSuivi['gtm_id']))
    @once
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@js($paxSuivi['gtm_id']));</script>
    @endonce
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tarteaucitronjs/1.34.0/tarteaucitron.min.js"></script>
    <script>
    tarteaucitron.init({
        "privacyUrl": @js(route('confidentialite')),
        "hashtag": "#tarteaucitron",
        "cookieName": @js($paxSuivi['consentement_cookie']),
        "orientation": "bottom",
        "groupServices": false,
        "showIcon": false,
        "iconPosition": "BottomRight",
        "showAlertSmall": false,
        "acceptAllCta": true,
        "DenyAllCta": true,
        "highPrivacy": true,
        "handleBrowserDNTRequest": false,
        "removeCredit": false,
        "readMoreLink": @js(route('confidentialite'))
    });
    @if(!empty($paxSuivi['gtm_id']))
    tarteaucitron.user.googletagmanagerId = @js($paxSuivi['gtm_id']);
    (tarteaucitron.job = tarteaucitron.job || []).push('googletagmanager');
    @endif
    </script>
    <script>
    (function () {
        var consigneAuChargement = false;

        function statutDecision() {
            var t = window.tarteaucitron || {};
            var jobs = t.job || [];
            var acceptes = [];
            var tousAcceptes = true, tousRefuses = true;
            for (var i = 0; i < jobs.length; i++) {
                var ok = t.state && t.state[jobs[i]] === true;
                if (ok) { acceptes.push(jobs[i]); } else { tousAcceptes = false; }
                if (ok) { tousRefuses = false; }
            }
            if (jobs.length === 0) { return null; }
            return {
                statut: tousRefuses ? 'refuse' : (tousAcceptes ? 'accepte' : 'personnalise'),
                services: acceptes
            };
        }

        function enregistrer() {
            consigneAuChargement = true;
            var decision = statutDecision();
            if (!decision) { return; }
            fetch(@js(route('consentement.inscrire')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    statut: decision.statut,
                    services: decision.services,
                    version: @js($paxSuivi['consentement_version'])
                })
            });
        }

        document.addEventListener('tac.consent_updated', enregistrer);
        window.addEventListener('load', function () {
            setTimeout(function () {
                if (consigneAuChargement) { return; }
                var t = window.tarteaucitron || {};
                if (!t.job || !t.state || t.job.length === 0) { return; }
                for (var i = 0; i < t.job.length; i++) {
                    if (t.state[t.job[i]] === undefined) { return; }
                }
                consigneAuChargement = true;
                enregistrer();
            }, 1500);
        });
    })();
    </script>
    <title>@yield('title', 'PaxEvent — Billetterie en ligne 100% Bénin')</title>
    <meta name="description" content="@yield('description', 'PaxEvent, Billeterie Intélligente 100% Bénin — La solution simple et rapide pour gérer vos événements, acheter et vendre vos tickets en ligne. Festival, Concert, Conférence, Soirée...')">
    
    @php
    $categories = ['Festival', 'Concert', 'Conférence', 'Soirée'];
    @endphp
    <meta property="og:title" content="@yield('og_title', 'PaxEvent — Billetterie en ligne 100% Bénin')">
    <meta property="og:description" content="@yield('og_description','Billeterie Intélligente 100% Bénin — La solution simple et rapide pour gérer vos événements, acheter et vendre vos tickets en ligne. Festival, Concert, Conférence, Soirée...')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.png'))">
    <meta property="og:image:width" content="5001">
    <meta property="og:image:height" content="2626">

    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:site_name" content="PaxEvent">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'PaxEvent — Billetterie en ligne 100% Bénin')">
    <meta name="twitter:description" content="@yield('og_description','Billeterie Intélligente 100% Bénin — La solution simple et rapide pour gérer vos événements, acheter et vendre vos tickets en ligne. Festival, Concert, Conférence, Soirée...')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-image.png'))">

    @stack('meta')
    <script>document.documentElement.classList.add('fouc');</script>
    <style>html.fouc body{visibility:hidden!important}</style>
    <link rel="preload" href="/assets/css/bootstrap.min.css" as="style">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        html { overflow-x: hidden; }

        :root {
            --violet:        #542680;
            --violet-clair:  #9972B0;
            --accent:        #FED514;
            --sombre:        #211C31;
            --blanc:         #ffffff;
            --blanc-casse:   #edecf0;
            --gris:          #98919b;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--blanc-casse);
            color: var(--sombre);
            margin: 0;
            padding-top: 64px;
            overflow-x: hidden;
        }

        /* ========== STICKY HEADER ========== */
        .public-header {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .public-header .brand {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--sombre);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-shrink: 0;
        }

        .public-header .brand span:first-child { color: var(--violet); }
        .public-header .brand span:last-child { color: var(--sombre); }

        .public-nav {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 0.15rem;
            align-items: center;
        }

        .public-nav a {
            color: var(--sombre);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .public-nav a:hover {
            background: rgba(84,38,128,0.06);
            color: var(--violet);
        }

        .public-nav a.active {
            background: transparent;
            color: var(--violet);
            font-weight: 700;
            border-top: 3px solid var(--violet);
            border-radius: 4px 4px 0 0;
            padding-top: calc(0.45rem - 3px);
        }

        .public-nav a.active:hover {
            background: transparent;
            color: var(--violet);
        }

        .public-nav .nav-recuperer {
            background: linear-gradient(135deg, var(--violet), var(--violet));
            color: #fff;
            font-weight: 700;
            margin-left: 0.25rem;
            box-shadow: 0 2px 8px rgba(84,38,128,0.25);
            transition: all 0.25s ease;
        }

        .public-nav .nav-recuperer:hover {
            background: linear-gradient(135deg, var(--violet), #542680);
            color: #fff;
            box-shadow: 0 4px 14px rgba(84,38,128,0.4);
            transform: translateY(-1px);
        }

        .public-nav .nav-recuperer.active {
            background: linear-gradient(135deg, var(--violet), #542680);
            color: #fff;
            box-shadow: 0 2px 8px rgba(84,38,128,0.3);
        }

        /* Breadcrumb bar */

        
        .breadcrumb-bar .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: 0.78rem;
        }

        .breadcrumb-bar .breadcrumb-item a {
            color: var(--gris);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-bar .breadcrumb-item a:hover {
            color: var(--violet);
        }

        .breadcrumb-bar .breadcrumb-item.active {
            color: var(--violet);
            font-weight: 700;
        }

        .breadcrumb-bar .breadcrumb-item + .breadcrumb-item::before {
            content: '\F285';
            font-family: 'bootstrap-icons';
            font-size: 0.65rem;
            color: var(--gris);
        }

        /* Mobile menu */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--sombre);
            padding: 0.25rem 0.5rem;
        }

        @media (max-width: 991.98px) {
            .mobile-toggle { display: block; }

            .public-nav {
                display: none;
                position: absolute;
                top: 64px;
                left: 0;
                right: 0;
                background: var(--blanc);
                border-bottom: 1px solid #e5e5e5;
                flex-direction: column;
                padding: 0.75rem 1rem;
                gap: 0.25rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            }

            .public-nav.show { display: flex; }

            .public-nav li { border-bottom: none; }

            .public-nav a {
                display: block;
                padding: 0.65rem 0.75rem;
                font-size: 0.92rem;
                border-radius: 8px;
            }

            .public-nav .nav-recuperer {
                margin-left: 0;
                border: 1.5px solid var(--violet);
                text-align: center;
                margin-top: 0.25rem;
            }
        }

        /* ========== EVENT CARDS ========== */
        .event-card-link {
            display: block;
            color: inherit;
        }

        .event-card-link:hover {
            text-decoration: none;
            color: inherit;
        }

        .event-card {
            background: var(--blanc);
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        .event-card img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }

        @media (max-width: 767.98px) {
            .event-card img { height: 140px; }
        }

        /* Gauge / progress bar */
        .gauge-bar {
            height: 4px;
            border-radius: 2px;
            background: #f0f0f0;
            overflow: hidden;
        }

        .gauge-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.4s ease;
        }

        .gauge-low { background: var(--violet); }
        .gauge-mid { background: #f59e0b; }
        .gauge-high { background: var(--danger); }
        .gauge-full { background: var(--danger); }

        /* ========== BUTTONS ========== */
        .btn-accent {
            background: var(--violet);
            border-color: var(--violet);
            color: #fff;
            font-weight: 600;
        }

        .btn-accent:hover { background: #3d1a5c; border-color: #3d1a5c; color: #fff; }

        .btn-violet {
            background: var(--violet);
            border-color: var(--violet);
            color: #fff;
            font-weight: 600;
        }

        .btn-violet:hover { background: var(--violet); border-color: var(--violet); color: #fff; }

        .btn-outline-violet {
            background: transparent;
            border: 1px solid var(--violet);
            color: var(--violet);
            font-weight: 600;
        }

        .btn-outline-violet:hover { background: var(--violet); color: #fff; }

        .form-control:focus, .form-select:focus {
            border-color: var(--violet);
            box-shadow: 0 0 0 0.15rem rgba(84, 38, 128, 0.15);
        }

        /* ========== STEPS ========== */
        .step-card { text-align: center; padding: 1.5rem 1rem; }

        .step-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(84,38,128,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.3rem;
            color: var(--violet);
        }

        /* ========== PAYMENT LOGOS ========== */
        .payment-logo {
            width: 80px;
            height: 50px;
            border-radius: 8px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.7rem;
            color: var(--sombre);
            margin: 0 auto;
        }

        /* ========== FOOTER ========== */
        .public-footer {
            background: linear-gradient(180deg, #1a1430 0%, #211C31 50%, #2a1f3a 100%);
            color: #fff;
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .public-footer::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--violet), var(--violet-clair), var(--accent));
        }

        .public-footer a { color: rgba(255,255,255,0.65); text-decoration: none; font-size: 0.85rem; transition: color 0.2s; }
        .public-footer a:hover { color: var(--accent); }

        .public-footer h6 {
            color: #fff;
            font-weight: 700;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 1.1rem;
        }

        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 0.6rem; }

        .footer-newsletter {
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 2rem 0;
        }
        .footer-newsletter .form-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fff;
            border-radius: 8px;
        }
        .footer-newsletter .form-control::placeholder { color: rgba(255,255,255,0.4); }
        .footer-newsletter .form-control:focus {
            background: rgba(255,255,255,0.15);
            border-color: var(--violet-clair);
            box-shadow: none;
        }
        .footer-newsletter .btn-subscribe {
            background: var(--violet-clair);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .footer-newsletter .btn-subscribe:hover { background: var(--violet); }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .footer-social a:hover {
            background: var(--violet-clair);
            color: #fff;
            transform: translateY(-2px);
        }

        .footer-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin: 0;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
        }

        /* ========== ALERTS ========== */
        .alert { border-radius: 8px; border: none; font-size: 0.88rem; }
        .alert-success { background: rgba(84,38,128,0.08); color: var(--violet); }
        .alert-danger { background: rgba(231,76,60,0.08); color: #e74c3c; }

        /* ========== BADGES ========== */
        .badge-publie { background: rgba(84,38,128,0.12); color: var(--violet); font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 20px; font-weight: 600; }
        .badge-complet { background: rgba(231,76,60,0.12); color: #e74c3c; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 20px; font-weight: 600; }

        /* ========== TARIFF OPTION ========== */
        .tarif-option {
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tarif-option:hover { border-color: var(--violet-clair); }
        .tarif-option.selected { border-color: var(--violet); background: rgba(84,38,128,0.04); }

        /* ========== MOBILE MONEY ========== */
        .mm-logo {
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--blanc);
        }

        .mm-logo:hover { border-color: var(--violet-clair); }
        .mm-logo.selected { border-color: var(--violet); background: rgba(84,38,128,0.04); }

        .mm-logo .mm-name { font-weight: 700; font-size: 0.82rem; color: var(--sombre); }

        /* ========== HERO ========== */
        .hero-section {
            background: linear-gradient(135deg, var(--violet) 0%, var(--violet) 100%);
            color: #fff;
            padding: 4rem 0;
        }

        @media (max-width: 767.98px) {
            .hero-section { padding: 2.5rem 0; }
            .hero-section h1 { font-size: 1.75rem !important; }
        }

        @media (max-width: 575.98px) {
            .public-footer .container { padding-left: 24px; padding-right: 24px; }
            .public-footer .row.g-4 { --bs-gutter-y: 1.5rem; }
            .public-footer .brand { justify-content: flex-start; }
            .public-footer .brand img { height: 70px; }
            .footer-social { justify-content: flex-start; }
        }

        /* ========== PANNEAU COOKIES (modal Tarteaucitron) ========== */
        html body #tarteaucitronRoot div#tarteaucitron {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            width: 560px !important;
            max-width: 94vw !important;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(33, 28, 49, 0.35);
            font-size: 13px;
        }

        /* Fond sombre derriere la modale */
        html body.tarteaucitron-modal-open div#tarteaucitronRoot::before {
            background: rgba(33, 28, 49, 0.55) !important;
        }

        /* En-tete violet */
        html body #tarteaucitronRoot #tarteaucitron div#tarteaucitronMainLineOffset {
            background: linear-gradient(135deg, #542680 0%, #3d1a5c 100%) !important;
            color: #fff;
            border-radius: 0;
        }
        #tarteaucitronMainLineOffset .tarteaucitronH1,
        #tarteaucitronMainLineOffset .tarteaucitronH2,
        #tarteaucitronMainLineOffset #tarteaucitronInfo,
        #tarteaucitronMainLineOffset #tarteaucitronInfo * {
            color: #fff !important;
        }
        #tarteaucitronInfo #tarteaucitronPrivacyUrlDialog {
            color: #FED514 !important;
            font-weight: 600;
        }
        #tarteaucitronClosePanel {
            color: #211C31 !important;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
        }

        /* Boutons Tout accepter / Tout refuser du panneau */
        #tarteaucitronMainLineOffset .tarteaucitronAsk #tarteaucitronAllAllowed {
            background: #fff;
            color: #542680;
            border-radius: 8px;
        }
        #tarteaucitronMainLineOffset .tarteaucitronAsk #tarteaucitronAllDenied {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            border-radius: 8px;
        }

        /* Categorias */
        html body #tarteaucitronRoot #tarteaucitron #tarteaucitronServices .tarteaucitronTitle {
            background: #f4effb !important;
            border-left: 4px solid #542680 !important;
            color: #542680 !important;
            font-weight: 700;
        }
        #tarteaucitronServices #tarteaucitronServicesTitle_mandatory .tarteaucitronTitle {
            background: #f4effb !important;
            border-left: 4px solid #FED514 !important;
            color: #542680 !important;
        }

        /* Lignes de services */
        #tarteaucitronServices .tarteaucitronLine .tarteaucitronName .tarteaucitronH3 {
            color: #211C31;
        }

        /* Texte "Cookies obligatoires" : cellule en pleine largeur, va jusqu'au bout */
        #tarteaucitronServices #tarteaucitronServices_mandatory .tarteaucitronLine .tarteaucitronName {
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
            display: block !important;
            margin-left: 0 !important;
            box-sizing: border-box;
        }
        #tarteaucitronServices #tarteaucitronServices_mandatory .tarteaucitronLine .tarteaucitronName .tarteaucitronH3 {
            color: #211C31;
        }

        /* Boutons Autoriser / Interdire + Enregistrer */
        html body #tarteaucitronRoot #tarteaucitron .tarteaucitronAsk button {
            border-radius: 8px;
            font-weight: 600;
        }
        #tarteaucitronRoot #tarteaucitron #tarteaucitronSaveButton {
            background: linear-gradient(135deg, #542680, #3d1a5c);
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(84, 38, 128, 0.35);
        }

        /* ========== BANDEAU TAC AUX COULEURS PAXEVENT ========== */
        html body #tarteaucitronRoot #tarteaucitronAlertBig {
            background: #ffffff !important;
            border: 1px solid #E8EDF2 !important;
            border-radius: 0 !important;
            color: #542680 !important;
        }
        html body #tarteaucitronRoot #tarteaucitronAlertBig #tarteaucitronDisclaimerAlert {
            color: #542680 !important;
        }
        html body #tarteaucitronRoot #tarteaucitronAlertBig button {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        html body #tarteaucitronRoot #tarteaucitronAlertBig #tarteaucitronPersonalize2 {
            background: #FED514 !important;
            color: #542680 !important;
            border: none !important;
            border-radius: 0 !important;
            font-weight: 800;
        }
        html body #tarteaucitronRoot #tarteaucitronAlertBig #tarteaucitronAllDenied2 {
            background: #ffffff !important;
            color: #542680 !important;
            border: 1px solid #542680 !important;
            border-radius: 0 !important;
            font-weight: 700;
        }
        html body #tarteaucitronRoot #tarteaucitronAlertBig #tarteaucitronCloseAlert {
            background: transparent !important;
            color: #542680 !important;
            border: none !important;
            text-decoration: underline;
        }

        /* ========== POPUP CONSENTEMENT COOKIES (carte basse) ========== */
        /* Le bandeau TAC du bas reste affiche ; la popup s'ouvre via Personnaliser */
        #paxConsentOverlay {
            position: fixed;
            inset: 0;
            z-index: 2147483000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 0 24px 32px;
            background: rgba(11, 16, 32, 0.55);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            box-sizing: border-box;
        }
        #paxConsentOverlay.pax-hidden {
            display: none;
        }
        .pax-consent-card {
            background: #ffffff;
            width: 400px;
            max-width: 100%;
            border-radius: 18px;
            padding: 26px 30px 24px;
            box-shadow: 0 24px 60px rgba(8, 12, 24, 0.35);
            box-sizing: border-box;
        }
        .pax-consent-card.pax-hidden {
            display: none;
        }
        .pax-continuer {
            display: block;
            text-align: right;
            font-size: 12.5px;
            font-weight: 600;
            color: #1D4ED8;
            text-decoration: underline;
            margin-bottom: 8px;
        }
        .pax-retour {
            display: inline-block;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #1D4ED8;
            text-decoration: none;
        }
        .pax-retour:hover {
            text-decoration: underline;
        }
        .pax-consent-card h2 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 800;
            color: #0F1B3D;
            line-height: 1.25;
        }
        .pax-lead {
            margin: 0 0 12px;
            font-size: 14px;
            line-height: 1.55;
            color: #334155;
        }
        .pax-interest {
            margin: 0 0 16px;
            font-size: 13px;
            line-height: 1.55;
            color: #64748B;
        }
        .pax-interest strong {
            color: #0F1B3D;
        }
        .pax-finalites {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 700;
            color: #0F1B3D;
        }
        .pax-liste {
            margin: 0;
            font-size: 12.5px;
            line-height: 1.55;
            color: #64748B;
        }
        .pax-voir-plus {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 10px 0 16px;
            padding: 0;
            border: none;
            background: none;
            font-size: 13px;
            font-weight: 700;
            color: #1D4ED8;
            cursor: pointer;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .pax-voir-plus .pax-chevron {
            display: inline-block;
            font-size: 12px;
            transition: transform 0.2s ease;
        }
        .pax-voir-plus.pax-ouvert .pax-chevron {
            transform: rotate(180deg);
        }
        .pax-voir-plus-contenu {
            margin: 0 0 16px;
            font-size: 12.5px;
            line-height: 1.55;
            color: #475569;
            background: #F8FAFC;
            border: 1px solid #EEF2F7;
            border-radius: 10px;
            padding: 12px 14px;
        }
        .pax-voir-plus-contenu.pax-hidden {
            display: none;
        }
        .pax-btn {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 15px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            cursor: pointer;
            transition: filter 0.15s ease, background 0.15s ease;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .pax-btn-primary {
            background: #0F2A5F;
            color: #fff;
            border: none;
        }
        .pax-btn-primary:hover {
            filter: brightness(1.15);
        }
        .pax-btn-secondary {
            background: #ffffff;
            color: #475569;
            border: 1px solid #CBD2DC;
            margin-top: 10px;
        }
        .pax-btn-secondary:hover {
            background: #F1F5F9;
        }
        .pax-consent-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #F0F3F7;
        }
        .pax-consent-section-text {
            min-width: 0;
        }
        .pax-consent-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1E293B;
        }
        .pax-consent-section-desc {
            font-size: 12.5px;
            line-height: 1.45;
            color: #64748B;
            margin-top: 2px;
        }
        .pax-consent-icon-lock {
            flex: 0 0 auto;
            color: #B0B8C4;
            font-size: 16px;
        }

        /* Interrupteur */
        .pax-toggle {
            position: relative;
            display: inline-block;
            flex: 0 0 auto;
            width: 44px;
            height: 24px;
        }
        .pax-toggle input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
            z-index: 1;
        }
        .pax-toggle-track {
            position: absolute;
            inset: 0;
            background: #D3DAE3;
            border-radius: 999px;
            transition: background 0.2s ease;
        }
        .pax-toggle-track::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        }
        .pax-toggle input:checked + .pax-toggle-track {
            background: #542680;
        }
        .pax-toggle input:checked + .pax-toggle-track::after {
            transform: translateX(20px);
        }

        @media (max-width: 480px) {
            #paxConsentOverlay {
                padding: 0 12px 16px;
            }
            .pax-consent-card {
                padding: 22px 20px 18px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(!empty($paxSuivi['gtm_id']))
    @once
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $paxSuivi['gtm_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endonce
    @endif
    <!-- Sticky Header -->
    <header class="public-header">
        <div class="container header-inner position-relative">
            <a href="{{ route('accueil') }}" class="brand">
                <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="52">
            </a>

            <button class="mobile-toggle" id="navToggle" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>

            <ul class="public-nav" id="publicNav">
                <li><a href="{{ route('accueil') }}" class="{{ request()->routeIs('accueil') ? 'active' : '' }}">Accueil</a></li>
                <li><a href="{{ route('evenements.public') }}" class="{{ request()->routeIs('evenements.public*') ? 'active' : '' }}">Evenements</a></li>
                <li><a href="{{ route('aide') }}" class="{{ request()->routeIs('aide') ? 'active' : '' }}">Comment ça marche</a></li>
                <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
                <li><a href="{{ route('tickets.recuperer') }}" class="nav-recuperer {{ request()->routeIs('tickets.recuperer', 'tickets.rechercher') ? 'active' : '' }}">Récupérer un ticket</a></li>
                <li><a href="{{ route('login') }}" style="display:inline-block;font-size:0.85rem;font-weight:600;margin-left:0.25rem;border:2px solid var(--violet);color:var(--violet);background-color:white;border-radius:6px;padding:6px 16px;text-decoration:none;">Se connecter</a></li>
            </ul>
        </div>
    </header>

    <script>
        document.getElementById('navToggle').addEventListener('click', function() {
            document.getElementById('publicNav').classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            var nav = document.getElementById('publicNav');
            var btn = document.getElementById('navToggle');
            if (!nav.contains(e.target) && !btn.contains(e.target)) {
                nav.classList.remove('show');
            }
        });
    </script>

    <!-- Alerts -->
    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="public-footer">
        {{-- Newsletter --}}
        <div class="footer-newsletter">
            <div class="container">
                <div class="row align-items-center g-3">
                    <div class="col-lg-4">
                        <h6 class="mb-0" style="text-transform: none; letter-spacing: 0; font-size: 0.95rem;">
                            <i class="bi bi-envelope-open me-2"></i>Restez informés !
                        </h6>
                        <p class="mb-0" style="color: rgba(255,255,255,0.5); font-size: 0.82rem;">
                            Recevez les actualités et offres exclusives
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <form id="newsletter-form" class="d-flex gap-2">
                            @csrf
                            <input type="email" name="email" class="form-control" placeholder="Votre adresse email" required>
                            <button type="submit" class="btn-subscribe">S'abonner</button>
                        </form>
                        <div id="newsletter-msg" style="font-size:0.78rem; margin-top:4px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Corps du footer --}}
        <div class="container py-5">
            <div class="row g-4">
                {{-- Colonne 1 : Logo + Réseaux sociaux --}}
                <div class="col-12 col-md-3">
                    <a href="{{ route('accueil') }}" class="brand" style="display:flex; align-items:center; gap:0.5rem;">
                        <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="64" style="filter:brightness(0) invert(1);">
                    </a>
                    <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem; margin: 0.25rem 0 0;">
                        Billetterie simple et rapide pour vos événements
                    </p>
                    <div class="footer-social d-flex gap-2 mt-3">
                        <a href="https://facebook.com/paxevent" target="_blank" title="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="https://instagram.com/paxevent" target="_blank" title="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://wa.me/22962836629" target="_blank" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        <a href="https://youtube.com/@paxevent" target="_blank" title="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="https://linkedin.com/company/paxevent" target="_blank" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                    
                </div>

                {{-- Colonne 2 : Liens utiles --}}
                <div class="col-6 col-md-3">
                    <h6>Liens utiles</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('evenements.public') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Événements</a></li>
                        <li><a href="{{ route('aide') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Comment ça marche</a></li>
                        <li><a href="{{ route('contact') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Contact</a></li>
                        <li><a href="{{ route('tickets.recuperer') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Mon ticket</a></li>
                        <li><a href="{{ route('inscriptions.organisateur') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Devenir organisateur</a></li>
                    </ul>
                </div>

                {{-- Colonne 3 : Support --}}
                <div class="col-6 col-md-3">
                    <h6>Support</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('cgv') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Conditions générales de vente</a></li>
                        <li><a href="{{ route('aide') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Aide / Comment ça marche</a></li>
                        <li><a href="{{ route('politique-remboursement') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Politique de remboursement</a></li>
                    </ul>
                </div>

                {{-- Colonne 4 : Société --}}
                <div class="col-6 col-md-3">
                    <h6>La société</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('cgu') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Conditions générales d'utilisation</a></li>
                        <li><a href="{{ route('confidentialite') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Politique de confidentialité</a></li>
                        <li><a href="{{ route('mentions-legales') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Mentions légales</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <hr class="footer-divider">

        {{-- Bas de page --}}
        <div class="container py-3">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0" style="color: rgba(255,255,255,0.4); font-size: 0.78rem;">
                        &copy; {{ date('Y') }} PaxEvent . Billetterie en ligne – Tous droits réservés.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <span style="color: rgba(255,255,255,0.3); font-size: 0.75rem;">
                        Propulsé par <a class="text-decoration-none" href="https://noctamcommunication.com" target="_blank" style="color: rgba(255,255,255,0.5); text-decoration:none; hover:text-decoration:underline; hover:color: rgba(255,255,255,0.7);">Noctam Communication</a>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const msg = document.getElementById('newsletter-msg');
        const formData = new FormData(this);
        btn.disabled = true;
        btn.textContent = '...';
        fetch('{{ route("newsletter.subscribe") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            msg.innerHTML = '<span style="color:var(--violet-clair);">' + escapeHtml(d.message) + '</span>';
            if (d.success) this.querySelector('input').value = '';
        })
        .catch(() => msg.innerHTML = '<span style="color:#e74c3c;">Erreur. Reessayez.</span>')
        .finally(() => { btn.disabled = false; btn.textContent = 'OK'; });
    });
    </script>
    <script>function escapeHtml(str){if(!str)return'';var d=document.createElement('div');d.textContent=str;return d.innerHTML;}</script>
    @yield('scripts')

    {{-- Popup consentement cookies (carte basse) --}}
    <div id="paxConsentOverlay" class="pax-hidden" role="dialog" aria-modal="true" aria-labelledby="paxConsentTitle">
        <div class="pax-consent-card" id="paxCardPopup">
            <a href="#" id="paxContinuerSansAccepter" class="pax-continuer">Continuer sans accepter</a>
            <h2 id="paxConsentTitle">Faites un choix pour vos données</h2>
            <p class="pax-lead">Nous et nos partenaires traitons vos données afin de diffuser des publicités et du contenu personnalisés, mesurer leurs performances, en apprendre davantage sur leur audience, développer et améliorer les produits de nos partenaires.</p>
            <p class="pax-interest">Nos partenaires et nous collectons et traitons certaines de vos données sur la base de notre <strong>intérêt légitime</strong>. Vous pouvez vous opposer à ce traitement à tout moment en utilisant le bouton «&nbsp;RÉGLAGES&nbsp;».</p>
            <h3 class="pax-finalites">Finalités de traitement de vos données</h3>
            <p class="pax-liste">publicités et contenu personnalisés, mesure de performance, études d&rsquo;audience, développement de services, stockage et accès à l&rsquo;information sur votre appareil, géolocalisation précise, identification des appareils.</p>
            <button type="button" id="paxVoirPlus" class="pax-voir-plus">Voir plus <span class="pax-chevron">&#9660;</span></button>
            <div id="paxVoirPlusContenu" class="pax-voir-plus-contenu pax-hidden">
                Vous pouvez à tout moment retirer votre consentement ou vous opposer aux traitements fondés sur notre intérêt légitime depuis les réglages. Les cookies strictement nécessaires restent activés pour garantir le fonctionnement du site.
            </div>
            <button type="button" id="paxConsentAccept" class="pax-btn pax-btn-primary">Tout accepter</button>
            <button type="button" id="paxConsentReglages" class="pax-btn pax-btn-secondary">Réglages</button>
        </div>

        <div class="pax-consent-card pax-hidden" id="paxCardReglages">
            <a href="#" id="paxReglagesRetour" class="pax-retour">&larr; Retour</a>
            <h2 id="paxReglagesTitle">Réglages des cookies</h2>
            <div class="pax-consent-section">
                <div class="pax-consent-section-text">
                    <div class="pax-consent-section-title">Strictement nécessaires</div>
                    <div class="pax-consent-section-desc">Ces cookies sont nécessaires au fonctionnement du site et ne peuvent pas être désactivés.</div>
                </div>
                <i class="bi bi-lock pax-consent-icon-lock"></i>
            </div>
            <div class="pax-consent-section">
                <div class="pax-consent-section-text">
                    <div class="pax-consent-section-title">Marketing &amp; analyse</div>
                    <div class="pax-consent-section-desc">Ces cookies peuvent être déposés par nos partenaires publicitaires via notre site.</div>
                </div>
                <label class="pax-toggle">
                    <input type="checkbox" id="paxToggleMarketing" checked>
                    <span class="pax-toggle-track"></span>
                </label>
            </div>
            <div class="pax-consent-section">
                <div class="pax-consent-section-text">
                    <div class="pax-consent-section-title">Préférences</div>
                    <div class="pax-consent-section-desc">Pour personnaliser votre contenu, nous utilisons des outils qui adaptent votre expérience.</div>
                </div>
                <label class="pax-toggle">
                    <input type="checkbox" id="paxTogglePrefs">
                    <span class="pax-toggle-track"></span>
                </label>
            </div>
            <button type="button" id="paxConsentSave" class="pax-btn pax-btn-primary" style="margin-top:18px;">Enregistrer mes choix</button>
        </div>
    </div>

    <script>
    (function () {
        var overlay = document.getElementById('paxConsentOverlay');
        if (!overlay) { return; }
        var cardPopup = document.getElementById('paxCardPopup');
        var cardReglages = document.getElementById('paxCardReglages');

        function aucunElementTarte() { return typeof window.tarteaucitron === 'undefined' || !tarteaucitron.job || tarteaucitron.job.length === 0; }

        function decisionFaite() {
            var t = window.tarteaucitron || {};
            var jobs = t.job || [];
            if (jobs.length === 0) { return true; }
            var cookie = (t.cookie && t.cookie.read) ? t.cookie.read() : '';
            for (var i = 0; i < jobs.length; i++) {
                var k = jobs[i];
                if (cookie.indexOf(k + '=true') === -1 && cookie.indexOf(k + '=false') === -1) {
                    return false;
                }
            }
            return true;
        }

        function cacher() {
            overlay.classList.add('pax-hidden');
            document.body.style.overflow = '';
        }

        function afficherPopup() {
            cardPopup.classList.remove('pax-hidden');
            cardReglages.classList.add('pax-hidden');
        }

        function afficherReglages() {
            cardReglages.classList.remove('pax-hidden');
            cardPopup.classList.add('pax-hidden');
        }

        function montrer() {
            if (!overlay.classList.contains('pax-hidden')) { return; }
            synchroniserToggles();
            afficherPopup();
            overlay.classList.remove('pax-hidden');
            document.body.style.overflow = 'hidden';
        }

        function appliquerStats(autorise) {
            if (typeof window.tarteaucitron === 'undefined' || !tarteaucitron.userInterface || !tarteaucitron.userInterface.respondAll) { return; }
            try {
                tarteaucitron.userInterface.respondAll(autorise);
            } catch (e) {}
        }

        function synchroniserToggles() {
            var marketing = document.getElementById('paxToggleMarketing');
            var prefs = document.getElementById('paxTogglePrefs');
            if (typeof window.tarteaucitron !== 'undefined' && tarteaucitron.state) {
                var cles = Object.keys(tarteaucitron.state).filter(function (k) { return k !== 'privacy' && k !== 'refuse'; });
                var vrai = 0, faux = 0;
                cles.forEach(function (k) {
                    if (tarteaucitron.state[k] === true) { vrai++; }
                    if (tarteaucitron.state[k] === false) { faux++; }
                });
                if (vrai === 0 && faux === 0) { marketing.checked = true; }
                else { marketing.checked = vrai >= faux; }
            }
            try {
                var p = localStorage.getItem('pax_prefs_cookies');
                prefs.checked = p === null ? true : p === '1';
            } catch (e) {}
        }

        document.getElementById('paxConsentAccept').addEventListener('click', function () {
            if (typeof window.tarteaucitron !== 'undefined' && tarteaucitron.userInterface) {
                try {
                    tarteaucitron.userInterface.respondAll(true);
                    tarteaucitron.userInterface.closeAlert();
                } catch (e) {}
            }
            cacher();
        });

        document.getElementById('paxContinuerSansAccepter').addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof window.tarteaucitron !== 'undefined' && tarteaucitron.userInterface) {
                try {
                    tarteaucitron.userInterface.respondAll(false);
                    tarteaucitron.userInterface.closeAlert();
                } catch (e) {}
            }
            cacher();
        });

        document.getElementById('paxConsentSave').addEventListener('click', function () {
            var marketing = document.getElementById('paxToggleMarketing').checked;
            var prefs = document.getElementById('paxTogglePrefs').checked;
            appliquerStats(marketing);
            try { localStorage.setItem('pax_prefs_cookies', prefs ? '1' : '0'); } catch (e) {}
            cacher();
        });

        document.getElementById('paxConsentReglages').addEventListener('click', afficherReglages);

        document.getElementById('paxReglagesRetour').addEventListener('click', function (e) {
            e.preventDefault();
            afficherPopup();
        });

        document.getElementById('paxVoirPlus').addEventListener('click', function () {
            var contenu = document.getElementById('paxVoirPlusContenu');
            var ouvert = !contenu.classList.contains('pax-hidden');
            if (ouvert) {
                contenu.classList.add('pax-hidden');
                this.classList.remove('pax-ouvert');
                this.innerHTML = 'Voir plus <span class="pax-chevron">&#9660;</span>';
            } else {
                contenu.classList.remove('pax-hidden');
                this.classList.add('pax-ouvert');
                this.innerHTML = 'Voir moins <span class="pax-chevron">&#9650;</span>';
            }
        });

        function ouvrirPersonnalisation() {
            if (aucunElementTarte()) { return; }
            if (typeof window.tarteaucitron !== 'undefined' && tarteaucitron.userInterface) {
                try { tarteaucitron.userInterface.closePanel(); } catch (e) {}
            }
            montrer();
        }

        document.addEventListener('click', function (e) {
            if (e.target && e.target.closest('#tarteaucitronCloseAlert')) {
                e.stopImmediatePropagation();
                e.preventDefault();
                ouvrirPersonnalisation();
            }
        }, true);

        document.addEventListener('tac.consent_updated', function () {
            if (aucunElementTarte() || decisionFaite()) { cacher(); }
        });
    })();
    </script>

    <script>document.documentElement.classList.remove('fouc');</script>
</body>
</html>
