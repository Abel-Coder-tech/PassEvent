<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Agent — PaxEvent')</title>
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
            padding: 1rem 0;
        }
        .agent-header .brand {
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.1rem;
        }
        .agent-header .brand i { margin-right: 0.4rem; }
        .agent-body { padding: 2rem 0; }
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
            padding: 1.25rem;
            border-radius: 12px;
            background: #fff;
        }
        .stat-card .value { font-size: 1.5rem; font-weight: 800; color: var(--violet); }
        .stat-card .label { font-size: 0.78rem; color: #6c757d; }
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
    </style>
    @stack('styles')
</head>
<body>
    <header class="agent-header">
        <div class="container d-flex align-items-center justify-content-between">
            <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="50" style="filter: brightness(0) invert(1);">
            <div class="d-flex align-items-center gap-3">
                @if(Auth::guard('agent')->check())
                    <span style="opacity:0.8;font-size:0.85rem;">
                        <i class="bi bi-person-circle me-1"></i>{{ Auth::guard('agent')->user()->nom }}
                    </span>
                    <a href="{{ route('agent.logout') }}" class="btn btn-sm btn-outline-light" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Déconnecter
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
</body>
</html>
