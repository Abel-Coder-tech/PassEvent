<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Espace affilié — PaxEvent'); ?></title>
    <script>document.documentElement.classList.add('fouc');</script>
    <style>html.fouc body{visibility:hidden!important}</style>
    <link rel="preload" href="/assets/css/bootstrap.min.css" as="style">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --violet: #542680;
            --violet-clair: #9972B0;
            --accent: #FED514;
            --sombre: #211C31;
            --blanc: #ffffff;
            --blanc-casse: #edecf0;
            --gris: #98919b;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--blanc-casse);
            color: var(--sombre);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .aff-header {
            background: var(--blanc);
            color: var(--sombre);
            padding: 0.9rem 0;
            flex: 0 0 auto;
            border-bottom: 1px solid #e2e0e4;
            box-shadow: 0 1px 8px rgba(33,28,49,0.05);
        }
        .aff-shell {
            flex: 1 1 auto;
            display: flex;
            align-items: stretch;
            min-height: 0;
        }
        .aff-column {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .aff-content {
            flex: 1 1 auto;
            min-width: 0;
            padding: 1.5rem;
        }
        .aff-header .brand { color: var(--violet); text-decoration: none; font-weight: 800; font-size: 1.1rem; }
        .aff-header .brand i { margin-right: 0.4rem; }
        .aff-sidebar {
            flex: 0 0 230px;
            width: 230px;
            order: -1;
            background: linear-gradient(180deg, var(--violet), #3d1a5c);
            border-radius: 0;
            padding: 1rem 0.75rem;
            box-shadow: none;
        }
        .aff-sidebar .nav-link {
            color: rgba(255,255,255,0.78);
            font-weight: 600;
            border-radius: 10px;
            padding: 0.6rem 0.85rem;
            font-size: 0.9rem;
            transition: background 0.15s, color 0.15s;
        }
        .aff-header .dropdown-menu { font-size: 0.9rem; }
        .aff-sidebar .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .aff-sidebar .nav-link.active {
            background: rgba(255,255,255,0.14);
            color: #fff;
            box-shadow: inset 3px 0 0 var(--accent);
            font-weight: 700;
        }
        .aff-sidebar .nav-link i { width: 1.25rem; }
        .aff-sidebar .nav-link { white-space: nowrap; }
        .empty-state { text-align: center; padding: 1.5rem 1rem; }
        .empty-state i { font-size: 1.9rem; color: var(--violet-clair); }
        .empty-state .empty-title { font-weight: 700; margin: 0.5rem 0 0.15rem; }
        @media (max-width: 991.98px) {
            .aff-shell { flex-direction: row; }
            .aff-content { padding: 1rem; }
            .aff-sidebar { display: none !important; }
        }
        .card-aff, .aff-card {
            background: #fff;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .stat-card {
            padding: 1.25rem;
            border-radius: 14px;
            background: #fff;
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .stat-card .value { font-size: 1.5rem; font-weight: 800; color: var(--violet); }
        .stat-card .label { font-size: 0.78rem; color: var(--gris); }
        .btn-violet { background: var(--violet); border-color: var(--violet); color: #fff; font-weight: 600; }
        .btn-violet:hover { background: #3d1a5c; border-color: #3d1a5c; color: #fff; }
        .code-chip {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            background: #f0ecf6; color: var(--violet);
            padding: 0.35rem 0.6rem; border-radius: 8px; font-weight: 700;
            letter-spacing: 0.04em;
        }
        /* Visuel des offres de co-parrainage */
        .aff-ev-thumb {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            overflow: hidden;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0ecf6;
            color: var(--violet-clair);
        }
        .aff-ev-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .aff-ev-thumb i { font-size: 1.6rem; }
        .aff-offer-cover {
            width: 100%;
            height: 150px;
            overflow: hidden;
            background: #f0ecf6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--violet-clair);
        }
        .aff-offer-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .aff-offer-cover i { font-size: 2rem; }
        @media (max-width: 575.98px) {
            .aff-ev-thumb { width: 72px; height: 72px; }
            .aff-offer-cover { height: 120px; }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div class="aff-shell">
        <div class="aff-column">
            <header class="aff-header">
                <div class="container-fluid px-3 px-lg-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm d-lg-none px-2 py-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#affMobileNav" aria-controls="affMobileNav" style="border:1px solid #d6d3da; background:#fff;">
                            <i class="bi bi-list" style="font-size:1.1rem;"></i>
                        </button>
                        <a href="<?php echo e(route('affiliate.dashboard')); ?>" class="brand">
                            <i class="bi bi-broadcast-pin"></i> <span>PaxEvent</span> <span class="d-none d-sm-inline" style="opacity:.7;font-weight:500;">· Affiliés</span>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-sm-3">
                        <span class="d-none d-md-inline text-truncate" style="opacity:.85;font-size:.9rem; max-width: 180px;">
                            <i class="bi bi-person-circle me-1"></i><?php echo e(auth('affiliate')->user()->nom_complet); ?>

                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2 px-sm-3" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Déconnexion</span>
                        </button>
                    </div>
                </div>
            </header>

            <?php if(session('success')): ?>
                <div class="px-2 px-lg-3 pt-2">
                    <div class="alert alert-success alert-dismissible fade show mb-0"> <?php echo e(session('success')); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="px-2 px-lg-3 pt-2">
                    <div class="alert alert-danger alert-dismissible fade show mb-0"> <?php echo e(session('error')); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="px-2 px-lg-3 pt-2">
                    <div class="alert alert-danger alert-dismissible fade show mb-0">
                        <strong>Veuillez corriger les erreurs suivantes :</strong>
                        <ul class="mb-0 mt-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <main class="aff-content">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>

        <nav class="aff-sidebar d-none d-lg-block">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('affiliate.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.dashboard')); ?>">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('affiliate.filleuls') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.filleuls')); ?>">
                        <i class="bi bi-people"></i> Mes filleuls
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('affiliate.liens*') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.liens')); ?>">
                        <i class="bi bi-link-45deg"></i> Mes liens
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('affiliate.commissions') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.commissions')); ?>">
                        <i class="bi bi-cash-coin"></i> Mes commissions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('affiliate.paiements*') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.paiements')); ?>">
                        <i class="bi bi-wallet2"></i> Paiements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('affiliate.profil*') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.profil')); ?>">
                        <i class="bi bi-gear"></i> Profil
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="affMobileNav" aria-labelledby="affMobileNavLabel" style="width:280px; background: linear-gradient(180deg, var(--violet) 0%, #3d1a5c 100%);">
        <div class="offcanvas-header border-0 pb-2">
            <h5 class="offcanvas-title text-white d-flex align-items-center gap-2" id="affMobileNavLabel">
                <i class="bi bi-broadcast-pin"></i> PaxEvent · Affiliés
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
        </div>
        <div class="offcanvas-body pt-2">
            <div class="mb-3 px-2">
                <div class="d-flex align-items-center gap-2 text-white-50 small">
                    <i class="bi bi-person-circle"></i>
                    <span class="text-truncate"><?php echo e(auth('affiliate')->user()->nom_complet); ?></span>
                </div>
            </div>
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link aff-mobile-link rounded-2 <?php echo e(request()->routeIs('affiliate.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.dashboard')); ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link aff-mobile-link rounded-2 <?php echo e(request()->routeIs('affiliate.filleuls') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.filleuls')); ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-people"></i> Mes filleuls
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link aff-mobile-link rounded-2 <?php echo e(request()->routeIs('affiliate.liens*') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.liens')); ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-link-45deg"></i> Mes liens
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link aff-mobile-link rounded-2 <?php echo e(request()->routeIs('affiliate.commissions') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.commissions')); ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-cash-coin"></i> Mes commissions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link aff-mobile-link rounded-2 <?php echo e(request()->routeIs('affiliate.paiements*') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.paiements')); ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-wallet2"></i> Paiements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link aff-mobile-link rounded-2 <?php echo e(request()->routeIs('affiliate.profil*') ? 'active' : ''); ?>" href="<?php echo e(route('affiliate.profil')); ?>" data-bs-dismiss="offcanvas">
                        <i class="bi bi-gear"></i> Profil
                    </a>
                </li>
            </ul>
            <div class="mt-4 pt-3 border-top border-white border-opacity-10">
                <button type="button" class="btn btn-sm aff-mobile-logout w-100 text-start border-0" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal" onclick="const off = bootstrap.Offcanvas.getInstance(document.getElementById('affMobileNav')); if(off){off.hide();}">
                    <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                </button>
            </div>
        </div>
    </div>

    <style>
        .aff-mobile-link {
            color: rgba(255,255,255,0.75);
            font-weight: 600;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .aff-mobile-link:hover,
        .aff-mobile-link:focus {
            color: #fff;
            background: rgba(254,213,20,0.16);
        }
        .aff-mobile-link.active {
            color: var(--sombre);
            background: var(--accent);
            font-weight: 700;
            box-shadow: none;
        }
        .aff-mobile-logout {
            color: rgba(255,255,255,0.75);
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .aff-mobile-logout:hover,
        .aff-mobile-logout:focus {
            color: #fff;
            background: rgba(254,213,20,0.18);
        }
    </style>

    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius:12px; overflow:hidden;">
                <div class="modal-header border-0 pb-1">
                    <h5 class="modal-title text-danger" id="logoutConfirmModalLabel">Déconnexion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body pt-1">
                    <p class="mb-0">Voulez-vous vous déconnecter ?</p>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-link btn-sm text-dark text-decoration-none p-0" data-bs-dismiss="modal">Non</button>
                    <form action="<?php echo e(route('affiliate.logout')); ?>" method="POST" class="m-0">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-link btn-sm text-danger text-decoration-none p-0">Oui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script>document.documentElement.classList.remove('fouc');</script>
</body>
</html>
<?php /**PATH /home/abel/Projects/PassEvent/resources/views/layouts/affiliate.blade.php ENDPATH**/ ?>