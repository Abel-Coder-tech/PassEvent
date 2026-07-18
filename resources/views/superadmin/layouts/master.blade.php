<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin - PaxEvent')</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <script src="/assets/js/chart.umd.min.js">
    </script>
    <style>
        :root {
            --sa-bg: #f4f5f7;
            --sa-sidebar: #1a1d23;
            --sa-sidebar-hover: #262a33;
            --sa-sidebar-active: #2d313a;
            --sa-primary: #6B3FA0;
            --sa-success: #27ae60;
            --sa-danger: #e74c3c;
            --sa-warning: #f39c12;
            --sa-text: #2c3e50;
            --sa-text-muted: #8898aa;
            --sa-card-bg: #ffffff;
            --sa-border: #e9ecef;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--sa-bg);
            color: var(--sa-text);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .sa-wrapper { display: flex; min-height: 100vh; }

        .sa-sidebar {
            width: 250px;
            background: var(--sa-sidebar);
            color: rgba(255,255,255,0.7);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform 0.3s;
        }

        .sa-sidebar-brand {
            padding: 1.2rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sa-sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--sa-primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
        }
        .sa-sidebar-brand .brand-text {
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
            line-height: 1.2;
        }
        .sa-sidebar-brand .brand-sub {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            font-weight: 600;
        }

        .sa-sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem 0;
        }
        .sa-sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sa-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .sa-nav-section {
            padding: 0.5rem 1.25rem 0.3rem;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.4;
            font-weight: 700;
        }

        .sa-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.82rem;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .sa-nav-link:hover {
            background: var(--sa-sidebar-hover);
            color: #fff;
        }
        .sa-nav-link.active {
            background: var(--sa-sidebar-active);
            color: #fff;
            border-left-color: var(--sa-primary);
        }
        .sa-nav-link i { font-size: 1rem; width: 20px; text-align: center; }

        .sa-nav-badge {
            background: #e74c3c;
            color: #fff;
            font-size: 0.6rem;
            padding: 0.15rem 0.45rem;
            border-radius: 20px;
            margin-left: auto;
            font-weight: 700;
            line-height: 1.4;
        }
        .sa-nav-badge.amber {
            background: #f59e0b;
        }

        .sa-notif-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--sa-text-muted);
            cursor: pointer;
            padding: 0.3rem;
            line-height: 1;
        }
        .sa-notif-btn:hover { color: var(--sa-text); }
        .sa-notif-dot {
            position: absolute;
            top: -2px;
            right: -4px;
            width: 18px; height: 18px;
            background: #e74c3c;
            color: #fff;
            font-size: 0.55rem;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sa-sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sa-sidebar-footer .avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--sa-primary);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.75rem; font-weight: 700;
            flex-shrink: 0;
        }
        .sa-sidebar-footer .user-info { flex: 1; min-width: 0; }
        .sa-sidebar-footer .user-name { font-size: 0.8rem; color: #fff; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sa-sidebar-footer .user-role { font-size: 0.65rem; opacity: 0.5; }
        .sa-sidebar-footer a { color: rgba(255,255,255,0.5); font-size: 0.9rem; }
        .sa-sidebar-footer a:hover { color: #fff; }

        .sa-main {
            margin-left: 250px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sa-topbar {
            background: #fff;
            border-bottom: 1px solid var(--sa-border);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .sa-topbar-left { display: flex; align-items: center; gap: 1rem; }
        .sa-topbar-title { font-size: 1.1rem; font-weight: 700; color: var(--sa-text); }
        .sa-topbar-right { display: flex; align-items: center; gap: 1rem; }
        .sa-topbar-badge {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            background: rgba(107,63,160,0.08);
            color: var(--sa-primary);
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .sa-topbar-date { font-size: 0.8rem; color: var(--sa-text-muted); }

        .sa-content { padding: 1.5rem; }

        .sa-card {
            background: var(--sa-card-bg);
            border-radius: 12px;
            border: 1px solid var(--sa-border);
            overflow: hidden;
        }
        .sa-card-body { padding: 1.25rem; }
        .sa-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--sa-border);
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .kpi-card {
            background: var(--sa-card-bg);
            border-radius: 12px;
            border: 1px solid var(--sa-border);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: box-shadow 0.2s;
        }
        .kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .kpi-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .kpi-info { flex: 1; }
        .kpi-value { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
        .kpi-label { font-size: 0.75rem; color: var(--sa-text-muted); text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }

        .sa-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sa-table th {
            padding: 0.7rem 0.75rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--sa-text-muted);
            font-weight: 700;
            border-bottom: 2px solid var(--sa-border);
            text-align: left;
        }
        .sa-table td {
            padding: 0.7rem 0.75rem;
            border-bottom: 1px solid var(--sa-border);
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .sa-table tr:hover td { background: rgba(0,0,0,0.01); }

        .sa-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.68rem;
            font-weight: 600;
        }
        .sa-badge-success { background: rgba(39,174,96,0.1); color: var(--sa-success); }
        .sa-badge-danger { background: rgba(231,76,60,0.1); color: var(--sa-danger); }
        .sa-badge-warning { background: rgba(243,156,18,0.1); color: var(--sa-warning); }
        .sa-badge-info { background: rgba(107,63,160,0.1); color: var(--sa-primary); }
        .sa-badge-secondary { background: rgba(136,152,168,0.1); color: var(--sa-text-muted); }

        .sa-activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--sa-border);
        }
        .sa-activity-item:last-child { border-bottom: none; }
        .sa-activity-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
        }
        .sa-activity-content { flex: 1; min-width: 0; }
        .sa-activity-text { font-size: 0.82rem; }
        .sa-activity-time { font-size: 0.7rem; color: var(--sa-text-muted); }

        .sa-btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }
        .sa-btn-primary { background: var(--sa-primary); color: #fff; border-color: var(--sa-primary); }
        .sa-btn-primary:hover { background: #5a35a0; color: #fff; }
        .sa-btn-outline { background: transparent; color: var(--sa-text-muted); border-color: var(--sa-border); }
        .sa-btn-outline:hover { background: #f5f5f5; color: var(--sa-text); }
        .sa-btn-danger { background: var(--sa-danger); color: #fff; border-color: var(--sa-danger); }
        .sa-btn-danger:hover { background: #c0392b; color: #fff; }
        .sa-btn-sm { padding: 0.25rem 0.6rem; font-size: 0.72rem; }

        .sa-form-control {
            border: 1px solid var(--sa-border);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.82rem;
            width: 100%;
            transition: border-color 0.15s;
        }
        .sa-form-control:focus { border-color: var(--sa-primary); outline: none; box-shadow: 0 0 0 3px rgba(107,63,160,0.1); }

        .chart-container { position: relative; height: 280px; min-height: 0; width: 100%; }
        .chart-container canvas { display: block; max-width: 100%; }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--sa-text);
            cursor: pointer;
            padding: 0.25rem;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }

        @media (max-width: 991.98px) {
            .sa-sidebar { transform: translateX(-100%); }
            .sa-sidebar.open { transform: translateX(0); }
            .sa-main { margin-left: 0; }
            .toggle-sidebar { display: block; }
            .sidebar-overlay.active { display: block; }
            .sa-topbar {
                position: sticky;
                top: 0;
                z-index: 99;
            }
        }

        .activity-pulse {
            width: 8px; height: 8px;
            background: var(--sa-success);
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.3); }
            100% { opacity: 1; transform: scale(1); }
        }

        .pagination { gap: 0.25rem; }
        .pagination .page-link {
            border-radius: 8px !important;
            border: 1px solid var(--sa-border);
            color: var(--sa-text);
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
        }
        .pagination .page-item.active .page-link {
            background: var(--sa-primary);
            border-color: var(--sa-primary);
            color: #fff;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast-item {
            pointer-events: auto;
            min-width: 320px;
            max-width: 420px;
            padding: 16px 20px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: toastIn 0.3s ease;
            font-size: 14px;
            border-left: 4px solid #ddd;
        }
        .toast-item.toast-success { border-left-color: #27ae60; }
        .toast-item.toast-error { border-left-color: #e74c3c; }
        .toast-item .toast-msg { flex: 1; }
        .toast-item .toast-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
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
        .toast-item .toast-btn-retry { background: #e74c3c; color: #fff; }
        .toast-item .toast-btn-cancel { background: #f0f0f0; color: #666; }
        .toast-item .toast-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #999;
            padding: 0 4px;
            line-height: 1;
        }
        .toast-item .toast-close:hover { color: #666; }
        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sa-wrapper">
    <aside class="sa-sidebar" id="saSidebar">
        <div class="sa-sidebar-brand">
            <div class="brand-icon"><i class="bi bi-shield-fill-check"></i></div>
            <div>
                <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" height="64" class="mb-1">
                <div class="brand-sub">Super Admin</div>
            </div>
        </div>

        <nav class="sa-sidebar-nav">
            <div class="sa-nav-section">Supervision</div>
            <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
            </a>
            <a href="{{ route('superadmin.statistiques') }}" class="sa-nav-link {{ request()->routeIs('superadmin.statistiques') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Statistiques
            </a>
            <a href="{{ route('superadmin.securite') }}" class="sa-nav-link {{ request()->routeIs('superadmin.securite') ? 'active' : '' }}">
                <i class="bi bi-shield-fill"></i> Securite
            </a>

            <div class="sa-nav-section">Gestion</div>
            <a href="{{ route('superadmin.utilisateurs') }}" class="sa-nav-link {{ request()->routeIs('superadmin.utilisateurs') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Utilisateurs
            </a>
            <a href="{{ route('superadmin.organisateurs') }}" class="sa-nav-link {{ request()->routeIs('superadmin.organisateurs') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i> Organisateurs
                @php $pendingOrgs = \App\Models\User::where('role','admin')->where('statut','en_attente')->count(); @endphp
                @if($pendingOrgs > 0)<span class="sa-nav-badge amber">{{ $pendingOrgs }}</span>@endif
            </a>
            <a href="{{ route('superadmin.evenements') }}" class="sa-nav-link {{ request()->routeIs('superadmin.evenements') ? 'active' : '' }}">
                <i class="bi bi-calendar-event-fill"></i> Evenements
            </a>
            <a href="{{ route('superadmin.moderation') }}" class="sa-nav-link {{ request()->routeIs('superadmin.moderation') ? 'active' : '' }}">
                <i class="bi bi-shield-exclamation"></i> Moderation
            </a>

            <div class="sa-nav-section">Finances</div>
            <a href="{{ route('superadmin.retraits') }}" class="sa-nav-link {{ request()->routeIs('superadmin.retraits*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Retraits
                @php $pendingRetraits = \App\Models\Withdrawal::where('status','en_attente')->count(); @endphp
                @if($pendingRetraits > 0)<span class="sa-nav-badge">{{ $pendingRetraits }}</span>@endif
            </a>
            <a href="{{ route('superadmin.remboursements.demandes') }}" class="sa-nav-link {{ request()->routeIs('superadmin.remboursements*') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i> Remboursements
                @php $pendingRemb = \App\Models\DemandeRemboursement::where('statut','en_attente')->count(); @endphp
                @if($pendingRemb > 0)<span class="sa-nav-badge">{{ $pendingRemb }}</span>@endif
            </a>
            <a href="{{ route('superadmin.transactions') }}" class="sa-nav-link {{ request()->routeIs('superadmin.transactions') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Transactions
            </a>
            <a href="{{ route('superadmin.tickets') }}" class="sa-nav-link {{ request()->routeIs('superadmin.tickets') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated-fill"></i> Tickets
            </a>
            <a href="{{ route('superadmin.scans') }}" class="sa-nav-link {{ request()->routeIs('superadmin.scans') ? 'active' : '' }}">
                <i class="bi bi-qr-code"></i> Scans
            </a>

            <div class="sa-nav-section">Systeme</div>
            <a href="{{ route('superadmin.notifications') }}" class="sa-nav-link {{ request()->routeIs('superadmin.notifications') ? 'active' : '' }}">
                <i class="bi bi-bell-fill"></i> Notifications
                @php $unreadMsgs = \App\Models\Message::where('lu',false)->whereNull('user_id')->count(); @endphp
                @if($unreadMsgs > 0)<span class="sa-nav-badge">{{ $unreadMsgs }}</span>@endif
            </a>
            <a href="{{ route('superadmin.logs') }}" class="sa-nav-link {{ request()->routeIs('superadmin.logs') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Logs systeme
            </a>
            <a href="{{ route('superadmin.parametres') }}" class="sa-nav-link {{ request()->routeIs('superadmin.parametres') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i> Parametres
            </a>
        </nav>

        <div class="sa-sidebar-footer">
            <div class="avatar">{{ substr(auth('superadmin')->user()->nom, 0, 1) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth('superadmin')->user()->nom }}</div>
                <div class="user-role">Super Admin</div>
            </div>
            <a href="{{ route('superadmin.logout') }}" onclick="event.preventDefault(); document.getElementById('sa-logout-form').submit();" title="Deconnexion">
                <i class="bi bi-box-arrow-right"></i>
            </a>
            <form id="sa-logout-form" action="{{ route('superadmin.logout') }}" method="POST" style="display:none;">@csrf</form>
        </div>
    </aside>

    <main class="sa-main">
        <div class="sa-topbar">
            <div class="sa-topbar-left">
                <button class="toggle-sidebar" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <span class="sa-topbar-title">@yield('page-title', 'Tableau de bord')</span>
            </div>
            <div class="sa-topbar-right">
                <a href="{{ route('superadmin.notifications') }}" class="sa-notif-btn" title="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    @php $headerUnread = \App\Models\Message::where('lu',false)->whereNull('user_id')->count(); @endphp
                    @if($headerUnread > 0)<span class="sa-notif-dot">{{ $headerUnread > 99 ? '99+' : $headerUnread }}</span>@endif
                </a>
                <span class="sa-topbar-badge"><i class="bi bi-shield-fill-check"></i> Super Admin</span>
                <span class="sa-topbar-date">{{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="sa-content">
            @if(session('success'))
                <script>document.addEventListener('DOMContentLoaded',function(){showToast('success',{{ Js::from(session('success')) }});});</script>
            @endif
            @if(session('error'))
                <script>document.addEventListener('DOMContentLoaded',function(){showToast('error',{{ Js::from(session('error')) }});});</script>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<script src="/assets/js/bootstrap.bundle.min.js">
</script>
<script>
    function toggleSidebar() {
        document.getElementById('saSidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }

    document.addEventListener('click', function (e) {
        const sidebar = document.getElementById('saSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (window.innerWidth <= 991 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== overlay) {
            // allow clicks on toggle button
        }
    });
</script>
<div id="toast-container" class="toast-container"></div>
<script>
function showToast(type,message){var c=document.getElementById('toast-container');if(!c||!message)return;var i=type==='success'?'bi-check-circle-fill':'bi-x-circle-fill';var cl=type==='success'?'#27ae60':'#e74c3c';var t=document.createElement('div');t.className='toast-item toast-'+type;t.innerHTML='<i class="bi '+i+'" style="color:'+cl+';font-size:1.3rem"></i><span class="toast-msg">'+message+'</span>'+(type==='error'?'<div class="toast-actions"><button class="toast-btn toast-btn-retry" onclick="location.reload()">Réessayer</button><button class="toast-btn toast-btn-cancel" onclick="this.closest(\'.toast-item\').remove()">Annuler</button></div>':'<button class="toast-close" onclick="this.closest(\'.toast-item\').remove()">&times;</button>');c.appendChild(t);if(type!=='error'){setTimeout(function(){t.style.animation='toastOut 0.3s ease forwards';setTimeout(function(){t.remove()},300)},4000)}
}
</script>
@stack('scripts')
</body>
</html>
