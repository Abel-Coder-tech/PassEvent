<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inscription — PaxEvent')</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: #fff;
            border-bottom: 1px solid #e0dde3;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-logo { height: 40px; width: auto; }
        .header-links { display: flex; align-items: center; gap: 1.25rem; font-size: 0.85rem; }
        .header-links a { color: #542680; font-weight: 600; text-decoration: none; }
        .header-links a:hover { text-decoration: underline; }
        .header-links .btn-connexion {
            background: #542680; color: #fff !important; padding: 0.4rem 1.25rem;
            border-radius: 8px; font-weight: 600; font-size: 0.82rem;
        }
        .header-links .btn-connexion:hover { background: #3d1a5c; text-decoration: none; }

        .steps-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 1rem 0.5rem;
            background: #fff;
            border-bottom: 1px solid #f0eeec;
            flex-wrap: wrap;
            gap: 0;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            color: #ccc;
            font-weight: 500;
            white-space: nowrap;
        }
        .step-item.active {
            color: #542680;
            font-weight: 700;
        }
        .step-item.done {
            color: #2e7d4f;
            font-weight: 600;
        }
        .step-number {
            width: 26px; height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            background: #e0dde3;
            color: #fff;
            flex-shrink: 0;
        }
        .step-item.active .step-number {
            background: #542680;
        }
        .step-item.done .step-number {
            background: #2e7d4f;
        }
        .step-connector {
            width: 24px; height: 2px;
            background: #e0dde3;
            margin: 0 0.5rem;
            flex-shrink: 0;
        }
        .step-connector.done {
            background: #2e7d4f;
        }

        .page-desc {
            text-align: center;
            padding: 0.5rem 1rem 0.25rem;
            background: #fff;
        }
        .page-desc h1 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1d1d1f;
            margin-bottom: 0.1rem;
        }
        .page-desc p {
            font-size: 0.82rem;
            color: #6c757d;
            margin: 0;
        }

        .page-content {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1.5rem 1rem 2rem;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 1.5rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        }
        .card-wide {
            max-width: 560px;
        }
        .card .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }

        .form-control, .form-select { border-radius: 10px; padding: 0.65rem 1rem; border: 1.5px solid #e0dde3; }
        .form-control:focus, .form-select:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,0.12); }
        .is-invalid { border-color: #dc3545 !important; }
        .invalid-feedback { font-size: 0.8rem; }
        .form-label { font-size: 0.82rem; font-weight: 600; color: #495057; margin-bottom: 0.25rem; }
        .btn-primary {
            background: #542680; border: none; border-radius: 10px; padding: 0.7rem 1rem;
            font-weight: 600; width: 100%; transition: 0.2s; margin-top: 0.5rem; color: #fff;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); color: #fff; }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-secondary {
            background: transparent; border: 1.5px solid #e0dde3; border-radius: 10px; padding: 0.6rem 1rem;
            font-weight: 600; width: 100%; color: #6c757d; transition: 0.2s; margin-top: 0.5rem;
            text-decoration: none; display: block; text-align: center;
        }
        .btn-secondary:hover { background: #f8f6f9; }
        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            background: #fff; border: 1.5px solid #e0dde3; border-radius: 10px; padding: 0.7rem 1rem;
            font-weight: 600; font-size: 0.9rem; color: #1d1d1f; text-decoration: none; transition: 0.2s;
        }
        .btn-google:hover { background: #f8f6f9; border-color: #9972B0; color: #1d1d1f; }
        .btn-google i { color: #4285F4; font-size: 1.1rem; }
        .divider { display: flex; align-items: center; color: #ccc; font-size: 0.85rem; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e0dde3; }

        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 0.6rem 1rem; font-size: 0.85rem; margin-bottom: 1rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 10px; padding: 0.6rem 1rem; font-size: 0.85rem; margin-bottom: 1rem; }

        .type-card {
            border: 2px solid #e0dde3; border-radius: 14px; padding: 1.25rem;
            cursor: pointer; transition: all 0.2s; text-align: center; height: 100%;
        }
        .type-card:hover { border-color: #9972B0; background: #faf8fb; }
        .type-card.selected { border-color: #542680; background: #f5f0f9; }
        .type-card .icon { font-size: 2rem; color: #542680; margin-bottom: 0.5rem; }
        .type-card .name { font-weight: 700; font-size: 1rem; color: #1d1d1f; margin-bottom: 0.25rem; }
        .type-card .desc { font-size: 0.8rem; color: #6c757d; line-height: 1.4; }
        input[type="radio"] { display: none; }

        .toggle-group { display: flex; gap: 0.5rem; }
        .toggle-btn {
            flex: 1; text-align: center; padding: 0.6rem 0.5rem; border-radius: 10px;
            border: 1.5px solid #e0dde3; cursor: pointer; font-weight: 600; font-size: 0.85rem;
            background: #fff; transition: 0.2s; color: #495057;
        }
        .toggle-btn:hover { border-color: #9972B0; }
        .toggle-btn.active { background: #f5f0f9; border-color: #542680; color: #542680; }
        .toggle-btn input { display: none; }

        .doc-info { background: #f8f6f9; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.82rem; color: #495057; }
        .doc-info i { color: #542680; margin-right: 0.35rem; }

        .recap-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f0eeec; font-size: 0.9rem; }
        .recap-row:last-child { border-bottom: none; }
        .recap-label { color: #6c757d; }
        .recap-value { font-weight: 600; color: #1d1d1f; text-align: right; max-width: 60%; word-break: break-word; }
        .recap-section { background: #f8f6f9; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; }
        .recap-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9972B0; margin-bottom: 0.5rem; }
        .avatar-preview { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #e0dde3; }

        .form-check-label { font-size: 0.85rem; color: #495057; }
        .form-check-input:checked { background-color: #542680; border-color: #542680; }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(84,38,128,0.12); border-color: #542680; }

        .success-icon { font-size: 3.5rem; color: #2E7D4F; margin-bottom: 1rem; }

        @media (max-width: 576px) {
            .header { padding: 0.6rem 1rem; }
            .header-logo { height: 32px; }
            .steps-bar { padding: 1rem 0.5rem 0.25rem; gap: 0; }
            .step-item { font-size: 0.65rem; }
            .step-connector { width: 12px; margin: 0 0.25rem; }
            .step-number { width: 22px; height: 22px; font-size: 0.65rem; }
            .page-desc h1 { font-size: 1rem; }
            .card { padding: 1.25rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="header">
        <a href="{{ route('accueil') }}">
            <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="header-logo">
        </a>
        <div class="header-links">
            <a href="{{ route('login') }}" class="btn-connexion">
                <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
            </a>
        </div>
    </div>

    @hasSection('steps')
    <div class="steps-bar">
        @yield('steps')
    </div>
    @endif

    @hasSection('page-title')
    <div class="page-desc">
        <h1>@yield('page-title')</h1>
        @hasSection('page-subtitle')
        <p>@yield('page-subtitle')</p>
        @endif
    </div>
    @endif

    <div class="page-content">
        <div class="card @yield('card-class')">
            @yield('card-content')
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
