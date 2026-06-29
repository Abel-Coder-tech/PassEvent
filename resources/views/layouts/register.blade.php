<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inscription — PaxEvent')</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --violet: #542680; --violet-clair: #9972B0; --sombre: #211C31;
            --blanc: #ffffff; --blanc-casse: #f5f5f7; --gris: #98919b;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--blanc-casse);
            color: var(--sombre);
            margin: 0; padding-top: 64px;
        }
        .public-header {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 1050;
        }
        .header-inner {
            display: flex; align-items: center;
            justify-content: space-between; height: 64px;
        }
        .public-header .brand { display: flex; align-items: center; }
        .public-nav {
            list-style: none; margin: 0; padding: 0;
            display: flex; gap: 0.15rem; align-items: center;
        }
        .public-nav a {
            color: var(--sombre); text-decoration: none;
            font-size: 0.85rem; font-weight: 500;
            padding: 0.45rem 0.85rem; border-radius: 8px;
            transition: all 0.15s; white-space: nowrap;
        }
        .public-nav a:hover { background: rgba(84,38,128,0.06); color: var(--violet); }
        .public-nav .btn-login {
            background: var(--violet); color: #fff; font-weight: 700;
            padding: 0.45rem 1rem;
        }
        .public-nav .btn-login:hover { background: #451e68; color: #fff; }
        .mobile-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--sombre); }
        @media (max-width: 991.98px) {
            .mobile-toggle { display: block; }
            .public-nav {
                display: none; position: absolute; top: 64px; left: 0; right: 0;
                background: var(--blanc); border-bottom: 1px solid #e5e5e5;
                flex-direction: column; padding: 0.75rem 1rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            }
            .public-nav.show { display: flex; }
            .public-nav a { display: block; padding: 0.65rem 0.75rem; font-size: 0.92rem; border-radius: 8px; }
        }

        .progress-bar-container {
            background: var(--blanc);
            border-bottom: 1px solid #e5e5e5;
            padding: 1.25rem 0 0.75rem;
        }
        .progress-step {
            display: flex;
            align-items: center;
            flex: 1;
        }
        .progress-step:last-child { flex: 0; }
        .step-dot {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.82rem;
            background: #e0dde3; color: #fff;
            flex-shrink: 0; transition: all 0.3s;
        }
        .step-dot.active { background: var(--violet); }
        .step-dot.done { background: #2E7D4F; }
        .step-line {
            flex: 1; height: 2px; background: #e0dde3;
            margin: 0 8px; transition: background 0.3s;
        }
        .step-line.done { background: #2E7D4F; }
        .step-label {
            display: block;
            font-size: 0.7rem;
            color: var(--gris);
            text-align: center;
            margin-top: 0.35rem;
        }
        .step-label.active { color: var(--violet); font-weight: 600; }

        .register-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        @media (max-width: 575.98px) {
            .register-card { padding: 1.25rem; border-radius: 12px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="public-header">
        <div class="container header-inner position-relative">
            <a href="{{ route('accueil') }}" class="brand">
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="80">
            </a>
            <button class="mobile-toggle" id="navToggle" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            <ul class="public-nav" id="publicNav">
                <li><a href="{{ route('accueil') }}">Accueil</a></li>
                <li><a href="{{ route('evenements.public') }}">Événements</a></li>
                <li><a href="{{ route('aide') }}">Aide</a></li>
                <li><a href="{{ route('login') }}" class="btn-login"><i class="bi bi-person me-1"></i>Se connecter</a></li>
            </ul>
        </div>
    </header>

    <script>
        document.getElementById('navToggle')?.addEventListener('click', function() {
            document.getElementById('publicNav').classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            var nav = document.getElementById('publicNav');
            var btn = document.getElementById('navToggle');
            if (!nav.contains(e.target) && !btn.contains(e.target)) nav.classList.remove('show');
        });
    </script>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>
                @if(session('error')){{ session('error') }}
                @else<ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @yield('content')

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
