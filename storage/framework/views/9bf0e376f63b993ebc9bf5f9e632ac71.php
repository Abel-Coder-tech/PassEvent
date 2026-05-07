<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'PassEvent'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --vert: #12976e;
            --vert-fonce: #428c79;
            --teal: #428c79;
            --violet: #87428b;
            --violet-fonce: #6d4e72;
            --violet-dark: #6d3570;
            --menthe: #b2e0d6;
            --aubergine: #6d4e72;
            --sombre: #3d4345;
            --gris: #98919b;
            --blanc-casse: #edecf0;
            --blanc: #ffffff;
            --danger: #e74c3c;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--blanc-casse);
            color: var(--sombre);
            margin: 0;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            height: 100vh;
            background: var(--sombre);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-shrink: 0;
        }

        .sidebar-logo-icon {
            width: 32px;
            height: 32px;
            background: var(--vert);
            border-radius: 6px;
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
            color: var(--menthe);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-logo-text span:last-child {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.75rem 0;
            overflow-y: auto;
        }

        .sidebar-section-label {
            padding: 0.75rem 1rem 0.4rem;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gris);
        }

        .sidebar .nav-link {
            color: var(--gris);
            padding: 0.5rem 1rem;
            font-size: 0.82rem;
            border-radius: 0;
            transition: all 0.2s;
            border-left: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar .nav-link i {
            width: 14px;
            font-size: 0.82rem;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }

        .sidebar .nav-link.active {
            color: var(--menthe);
            background: rgba(18, 151, 110, 0.15);
            border-left-color: var(--menthe);
            font-weight: 700;
        }

        .sidebar-user {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-shrink: 0;
        }

        .sidebar-user-avatar {
            width: 34px;
            height: 34px;
            background: var(--violet-fonce);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            overflow: hidden;
        }

        .sidebar-user-name {
            color: #fff;
            font-weight: 700;
            font-size: 0.82rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            color: var(--gris);
            font-size: 0.7rem;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Main Content */
        .main-content {
            margin-left: 220px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Bar */
        .top-bar {
            background: var(--blanc);
            border-bottom: 1px solid #e5e5e5;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .top-bar-left h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--sombre);
            margin: 0;
        }

        .top-bar-left p {
            font-size: 0.75rem;
            color: var(--gris);
            margin: 0.2rem 0 0;
        }

        .top-bar-left p span {
            color: var(--vert);
            font-weight: 700;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Hamburger button */
        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: var(--sombre);
            font-size: 1.5rem;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
        }

        /* Cards */
        .metric-card {
            background: var(--blanc);
            border-radius: 10px;
            padding: 1.25rem;
            border-top: 3px solid transparent;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .metric-label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gris);
            margin-bottom: 0.25rem;
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--sombre);
            line-height: 1.2;
        }

        .metric-subtitle {
            font-size: 0.75rem;
            color: var(--gris);
            margin-top: 0.15rem;
        }

        /* Panel Cards */
        .panel-card {
            background: var(--blanc);
            border-radius: 10px;
            overflow: hidden;
        }

        .panel-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .panel-card-header h5 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--sombre);
            margin: 0;
        }

        .panel-card-header a {
            font-size: 0.78rem;
            color: var(--vert);
            text-decoration: none;
            font-weight: 600;
        }

        .panel-card-body {
            padding: 1.25rem;
        }

        /* Event list */
        .event-row {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f5f5f5;
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
        .event-meta {
            font-size: 0.7rem;
            color: var(--gris);
        }

        /* Status badges */
        .status-badge {
            font-size: 0.68rem;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-en-cours { background: rgba(18, 151, 110, 0.12); color: var(--vert); }
        .status-a-venir { background: rgba(66,140,121,0.12); color: var(--teal); }
        .status-termine { background: rgba(152, 145, 155, 0.15); color: var(--gris); }
        .status-brouillon { background: rgba(152, 145, 155, 0.15); color: var(--gris); }

        /* Progress */
        .progress-bar-custom {
            height: 6px;
            border-radius: 3px;
            background: #f0f0f0;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 3px;
            background: var(--vert);
            transition: width 0.5s ease;
        }

        /* Activity log */
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f5f5f5;
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

        /* Promo code */
        .promo-code {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--sombre);
        }

        .promo-status {
            font-size: 0.7rem;
            font-weight: 600;
        }

        .promo-utilise { color: var(--vert-fonce); }
        .promo-disponible { color: var(--gris); }

        /* Mini stat cards */
        .mini-stat {
            background: #f5f5f5;
            border-radius: 8px;
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

        .mini-stat-label {
            font-size: 0.68rem;
            color: var(--gris);
            font-weight: 500;
        }

        /* Buttons */
        .btn-vert {
            background: var(--vert);
            border-color: var(--vert);
            color: #fff;
            font-weight: 600;
        }

        .btn-vert:hover {
            background: var(--vert-fonce);
            border-color: var(--vert-fonce);
            color: #fff;
        }

        .btn-outline-vert {
            background: transparent;
            border: 1px solid var(--vert);
            color: var(--vert);
            font-weight: 600;
        }

        .btn-outline-vert:hover {
            background: var(--vert);
            color: #fff;
        }

        .btn-outline-rouge {
            border: 1px solid #e74c3c;
            color: #e74c3c;
            font-weight: 600;
            background: transparent;
        }

        .btn-outline-rouge:hover {
            background: #e74c3c;
            color: #fff;
        }

        .btn-secondary-custom {
            background: transparent;
            border: 1px solid #ddd;
            color: var(--sombre);
            font-weight: 600;
            font-size: 0.82rem;
        }

        .btn-secondary-custom:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        /* Form controls */
        .form-control:focus, .form-select:focus {
            border-color: var(--vert);
            box-shadow: 0 0 0 0.15rem rgba(18, 151, 110, 0.15);
        }

        .btn-primary-custom {
            background: var(--vert);
            border-color: var(--vert);
            color: #fff;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            background: var(--vert-fonce);
            border-color: var(--vert-fonce);
            color: #fff;
        }

        /* Page content area */
        .page-content {
            padding: 1.5rem 2rem 2rem;
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            font-size: 0.85rem;
            border: none;
        }

        /* Table responsive */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .custom-table th {
            white-space: nowrap;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gris);
            border-bottom: 2px solid #e5e5e5;
        }

        .custom-table td {
            vertical-align: middle;
            font-size: 0.85rem;
        }

        /* Scrollbar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .hamburger-btn {
                display: inline-flex;
            }

            .top-bar {
                padding: 0.75rem 1rem;
            }

            .top-bar-left h2 {
                font-size: 1.1rem;
            }

            .top-bar-left p {
                font-size: 0.7rem;
            }

            .page-content {
                padding: 1rem 1rem 1.5rem;
            }

            .panel-card-body {
                padding: 1rem;
            }

            .metric-card {
                padding: 1rem;
            }

            .metric-value {
                font-size: 1.5rem;
            }

            .panel-card-header {
                padding: 0.75rem 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .top-bar-right .btn span,
            .top-bar-right .btn-text {
                display: none;
            }

            .top-bar-right .btn-sm {
                padding: 0.35rem 0.5rem;
                font-size: 0.75rem;
            }

            .event-card .panel-card-body {
                padding: 0.75rem !important;
            }

            .event-card .d-flex.justify-content-end.gap-2 {
                flex-wrap: wrap;
            }

            .event-card .d-flex.justify-content-end.gap-2 .btn {
                flex: 1;
                min-width: 0;
                text-align: center;
            }

            .metric-card {
                padding: 0.75rem;
            }

            .metric-value {
                font-size: 1.25rem;
            }

            .metric-label {
                font-size: 0.6rem;
            }

            .panel-card-header h5 {
                font-size: 0.82rem;
            }

            .status-badge {
                font-size: 0.6rem;
                padding: 0.2rem 0.5rem;
            }
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>

<?php if(auth()->guard()->check()): ?>
    <!-- Sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">P</div>
            <div class="sidebar-logo-text">
                <span>Pass</span><span>Event</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Principal</div>
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
            </a>
            <a href="<?php echo e(route('admin.evenements.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.evenements.*') ? 'active' : ''); ?>">
                <i class="bi bi-calendar-event"></i> Evenements
            </a>
            <a href="<?php echo e(route('admin.messages.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.messages.*') ? 'active' : ''); ?>">
                <i class="bi bi-envelope"></i> Messages
                <?php
                    $unreadMessages = \App\Models\Message::where('lu', false)->count();
                ?>
                <?php if($unreadMessages > 0): ?>
                    <span class="badge bg-danger ms-auto" style="font-size: 0.65rem;"><?php echo e($unreadMessages); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('ventes-manuelles.create')); ?>" class="nav-link <?php echo e(request()->routeIs('ventes-manuelles.*') ? 'active' : ''); ?>">
                <i class="bi bi-bag"></i> Vente manuelle
            </a>

            <div class="sidebar-section-label">Billetterie</div>
            <a href="<?php echo e(route('scan.index')); ?>" class="nav-link <?php echo e(request()->routeIs('scan.*') ? 'active' : ''); ?>">
                <i class="bi bi-qr-code-scan"></i> Scan QR
            </a>
            <a href="<?php echo e(route('admin.codes-promos.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.codes-promos.*') ? 'active' : ''); ?>">
                <i class="bi bi-tag"></i> Codes promos
            </a>
            <a href="<?php echo e(route('tickets.index')); ?>" class="nav-link <?php echo e(request()->routeIs('tickets.*') ? 'active' : ''); ?>">
                <i class="bi bi-ticket-perforated"></i> Tickets
            </a>
            <a href="<?php echo e(route('admin.remboursements.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.remboursements.*') ? 'active' : ''); ?>">
                <i class="bi bi-arrow-return-left"></i> Remboursements
            </a>

            <div class="sidebar-section-label">Analyse</div>
            <a href="<?php echo e(route('statistiques.index')); ?>" class="nav-link <?php echo e(request()->routeIs('statistiques.*') ? 'active' : ''); ?>">
                <i class="bi bi-bar-chart-line"></i> Statistiques
            </a>
            <a href="<?php echo e(route('logs.index')); ?>" class="nav-link <?php echo e(request()->routeIs('logs.*') ? 'active' : ''); ?>">
                <i class="bi bi-file-earmark-text"></i> Logs systeme
            </a>

            <div class="sidebar-section-label">Compte</div>
            <a href="<?php echo e(route('parametres.index')); ?>" class="nav-link <?php echo e(request()->routeIs('parametres.*') ? 'active' : ''); ?>">
                <i class="bi bi-gear"></i> Parametres
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?php echo e(strtoupper(substr(Auth::user()->nom, 0, 2))); ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo e(Auth::user()->nom); ?></div>
                <div class="sidebar-user-role">Organisateur</div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content -->
<div class="main-content">
    <?php if(auth()->guard()->check()): ?>
    <div class="top-bar">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="top-bar-left">
                <h2><?php echo $__env->yieldContent('page-title', 'Tableau de bord'); ?></h2>
                <p><?php echo e(now()->format('d F Y')); ?> &mdash; Prestataire de paiement <span>KKiaPay</span></p>
            </div>
        </div>
        <div class="top-bar-right">
            <?php echo $__env->yieldContent('topbar-actions'); ?>
            <a href="<?php echo e(route('admin.evenements.create')); ?>" class="btn btn-vert btn-sm">
                <i class="bi bi-plus-lg me-1"></i> <span class="btn-text">Creer un evenement</span>
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="page-content pb-0">
            <div class="alert alert-success alert-dismissible fade show" style="background: rgba(18,151,110,0.08); color: var(--vert); border-radius: 8px;">
                <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="page-content pb-0">
            <div class="alert alert-danger alert-dismissible fade show" style="background: rgba(231,76,60,0.08); color: #e74c3c; border-radius: 8px;">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</div>

<?php if(auth()->guard()->guest()): ?>
    <?php echo $__env->yieldContent('content'); ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/layouts/app.blade.php ENDPATH**/ ?>