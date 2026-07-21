<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PaxEvent — Billetterie en ligne 100% Bénin')</title>
    <meta name="description" content="@yield('description', 'PaxEvent, Billeterie Intélligente 100% Bénin — La solution simple et rapide pour gérer vos événements, acheter et vendre vos tickets en ligne. Festival, Concert, Conférence, Soirée...')">
    
    @php
    $categories = ['Festival', 'Concert', 'Conférence', 'Soirée'];
    @endphp
    <meta property="og:title" content="@yield('og_title', 'PaxEvent — Billetterie en ligne 100% Bénin')">
    <meta property="og:description" content="@yield('og_description','Billeterie Intélligente 100% Bénin — La solution simple et rapide pour gérer vos événements, acheter et vendre vos tickets en ligne. Festival, Concert, Conférence, Soirée...')">
    <meta property="og:type" content="website">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:site_name" content="PaxEvent">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'PaxEvent — Billetterie en ligne 100% Bénin')">
    <meta name="twitter:description" content="@yield('og_description','Billeterie Intélligente 100% Bénin — La solution simple et rapide pour gérer vos événements, acheter et vendre vos tickets en ligne. Festival, Concert, Conférence, Soirée...')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-image.jpg'))">

    @stack('meta')
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
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
            .public-footer .brand { justify-content: center; }
            .public-footer .brand img { height: 70px; }
            .footer-social { justify-content: center; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sticky Header -->
    <header class="public-header">
        <div class="container header-inner position-relative">
            <a href="{{ route('accueil') }}" class="brand">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="80">
                <span style="font-size:0.6rem;font-weight:700;color:var(--violet);border:1.5px solid var(--violet);border-radius:4px;padding:1px 5px;margin-left:4px;vertical-align:super;text-transform:uppercase;">BETA</span>
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
                            <i class="bi bi-envelope-open me-2"></i>Restez informé
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
                        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="90" style="filter:brightness(0) invert(1);">
                    </a>
                    <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem; margin: 0.4rem 0 0.75rem;">
                        Billetterie simple et rapide pour vos événements
                    </p>
                    <div class="footer-social d-flex gap-2">
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
                        <li><a href="{{ route('affiliation') }}"><i class="bi bi-chevron-right me-1" style="font-size:0.65rem;"></i>Affiliation</a></li>
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
</body>
</html>
