<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Agent — PaxEvent')</title>
    <script>document.documentElement.classList.add('fouc');</script>
    <style>html.fouc body{visibility:hidden!important}</style>
    <link rel="preload" href="/assets/css/bootstrap.min.css" as="style">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --violet: #542680;
            --violet-clair: #9972B0;
            --sombre: #211C31;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f5f5f7;
            min-height: 100vh;
        }
        .agent-header {
            background: linear-gradient(135deg, var(--violet), #3d1a5c);
            color: #fff;
            padding: 0.875rem 0;
        }
        .agent-header .brand {
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.1rem;
        }
        .agent-header .brand i { margin-right: 0.4rem; }
        .agent-header .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: nowrap;
        }
        .agent-header .header-user {
            opacity: 0.9;
            font-size: 0.85rem;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .agent-body { padding: 1.5rem 0; }
        .card-agent {
            background: #fff;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .btn-violet {
            background: var(--violet);
            border-color: var(--violet);
            color: #fff;
            font-weight: 600;
        }
        .btn-violet:hover { background: #3d1a5c; border-color: #3d1a5c; color: #fff; }
        .btn-outline-violet {
            border: 1.5px solid var(--violet);
            color: var(--violet);
            font-weight: 600;
        }
        .btn-outline-violet:hover { background: var(--violet); color: #fff; }
        .stat-card {
            text-align: center;
            padding: 1rem 0.75rem;
            border-radius: 12px;
            background: #fff;
            height: 100%;
        }
        .stat-card .value { font-size: 1.4rem; font-weight: 800; color: var(--violet); }
        .stat-card .label { font-size: 0.75rem; color: #6c757d; }
        .scan-result-valid {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        .scan-result-invalid {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        .scan-card-mobile {
            display: none;
        }
        @media (max-width: 576px) {
            .agent-body { padding: 1rem 0; }
            .agent-header { padding: 0.75rem 0; }
            .agent-header .header-user { max-width: 90px; font-size: 0.8rem; }
            .agent-header .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
            .container { padding-left: 0.875rem; padding-right: 0.875rem; }
            .stat-card { padding: 0.875rem 0.5rem; }
            .stat-card .value { font-size: 1.25rem; }
            .stat-card .label { font-size: 0.72rem; }
            .card-agent { border-radius: 14px; }
            .table-responsive { border-radius: 0 0 14px 14px; }
            .w-100.w-md-auto { width: 100% !important; }
            .scan-card-desktop { display: none; }
            .scan-card-mobile {
                display: block;
            }
            .scan-card-mobile .scan-item {
                padding: 0.75rem 0.875rem;
                border-bottom: 1px solid #f1f1f1;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }
            .scan-card-mobile .scan-item:last-child { border-bottom: none; }
            .scan-card-mobile .scan-info { flex: 1; min-width: 0; }
            .scan-card-mobile .scan-code {
                font-family: monospace;
                font-size: 0.75rem;
                color: #6c757d;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 140px;
            }
            .scan-card-mobile .scan-time {
                font-size: 0.75rem;
                color: #6c757d;
            }
        }
        @media (max-width: 360px) {
            .agent-header .header-user { max-width: 70px; }
            .stat-card .value { font-size: 1.15rem; }
            .btn-violet, .btn-outline-violet { font-size: 0.9rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="agent-header">
        <div class="container d-flex align-items-center justify-content-between gap-2">
            <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="44" style="filter: brightness(0) invert(1);">
            <div class="header-actions">
                @if(Auth::guard('agent')->check())
                    <span class="header-user d-none d-sm-inline">
                        <i class="bi bi-person-circle me-1"></i>{{ Auth::guard('agent')->user()->nom }}
                    </span>
                    <a href="{{ route('agent.logout') }}" class="btn btn-sm btn-outline-light" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Déconnecter</span>
                    </a>
                    <form id="logout-form" action="{{ route('agent.logout') }}" method="POST" class="d-none">@csrf</form>
                @endif
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show"> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        </div>
    @endif
    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show"> {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        </div>
    @endif

    <div class="agent-body">
        @yield('content')
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>function escapeHtml(str){if(!str)return'';var d=document.createElement('div');d.textContent=str;return d.innerHTML;}</script>
    @stack('scripts')
    <script>document.documentElement.classList.remove('fouc');</script>
</body>
</html>
