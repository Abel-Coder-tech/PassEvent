<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'PassEvent'); ?></title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --violet: #87428b;
            --violet-dark: #6d3570;
            --vert: #12976e;
            --vert-fonce: #428c79;
            --teal: #428c79;
            --menthe: #b2e0d6;
            --aubergine: #6d4e72;
            --sombre: #3d4345;
            --gris: #98919b;
            --blanc-casse: #edecf0;
            --blanc: #ffffff;
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
            background: var(--blanc);
            border-bottom: 1px solid #e5e5e5;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
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

        .public-header .brand span:first-child { color: var(--vert); }
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
            background: rgba(135,66,139,0.06);
            color: var(--violet);
        }

        .public-nav a.active {
            background: var(--violet);
            color: #fff;
            font-weight: 700;
        }

        .public-nav a.active:hover {
            background: var(--violet-dark);
            color: #fff;
        }

        .public-nav .nav-recuperer {
            border: 1.5px solid var(--violet);
            color: var(--violet);
            font-weight: 700;
            margin-left: 0.25rem;
        }

        .public-nav .nav-recuperer:hover {
            background: var(--violet);
            color: #fff;
        }

        .public-nav .nav-recuperer.active {
            background: var(--violet);
            color: #fff;
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

        .gauge-low { background: var(--vert); }
        .gauge-mid { background: #f59e0b; }
        .gauge-high { background: var(--danger); }
        .gauge-full { background: var(--danger); }

        /* ========== BUTTONS ========== */
        .btn-vert {
            background: var(--vert);
            border-color: var(--vert);
            color: #fff;
            font-weight: 600;
        }

        .btn-vert:hover { background: var(--vert-fonce); border-color: var(--vert-fonce); color: #fff; }

        .btn-violet {
            background: var(--violet);
            border-color: var(--violet);
            color: #fff;
            font-weight: 600;
        }

        .btn-violet:hover { background: var(--violet-dark); border-color: var(--violet-dark); color: #fff; }

        .btn-outline-violet {
            background: transparent;
            border: 1px solid var(--violet);
            color: var(--violet);
            font-weight: 600;
        }

        .btn-outline-violet:hover { background: var(--violet); color: #fff; }

        .form-control:focus, .form-select:focus {
            border-color: var(--vert);
            box-shadow: 0 0 0 0.15rem rgba(18, 151, 110, 0.15);
        }

        /* ========== STEPS ========== */
        .step-card { text-align: center; padding: 1.5rem 1rem; }

        .step-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(135,66,139,0.08);
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
            background: var(--sombre);
            color: #fff;
            padding: 3rem 0 1.5rem;
        }

        .public-footer a {
            color: var(--menthe);
            text-decoration: none;
            font-size: 0.88rem;
        }

        .public-footer a:hover { color: #fff; }

        .public-footer h6 {
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 0.5rem; }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
        }

        /* ========== ALERTS ========== */
        .alert { border-radius: 8px; border: none; font-size: 0.88rem; }
        .alert-success { background: rgba(18,151,110,0.08); color: var(--vert); }
        .alert-danger { background: rgba(231,76,60,0.08); color: #e74c3c; }

        /* ========== BADGES ========== */
        .badge-publie { background: rgba(18,151,110,0.12); color: var(--vert); font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 20px; font-weight: 600; }
        .badge-complet { background: rgba(231,76,60,0.12); color: #e74c3c; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 20px; font-weight: 600; }

        /* ========== TARIFF OPTION ========== */
        .tarif-option {
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tarif-option:hover { border-color: var(--menthe); }
        .tarif-option.selected { border-color: var(--vert); background: rgba(18,151,110,0.04); }

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

        .mm-logo:hover { border-color: var(--menthe); }
        .mm-logo.selected { border-color: var(--vert); background: rgba(18,151,110,0.04); }

        .mm-logo .mm-name { font-weight: 700; font-size: 0.82rem; color: var(--sombre); }

        /* ========== HERO ========== */
        .hero-section {
            background: linear-gradient(135deg, var(--violet) 0%, var(--violet-dark) 100%);
            color: #fff;
            padding: 4rem 0;
        }

        @media (max-width: 767.98px) {
            .hero-section { padding: 2.5rem 0; }
            .hero-section h1 { font-size: 1.75rem !important; }
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <!-- Sticky Header -->
    <header class="public-header">
        <div class="container header-inner position-relative">
            <a href="<?php echo e(route('accueil')); ?>" class="brand">
                <span>Pass</span><span>Event</span>
            </a>

            <button class="mobile-toggle" id="navToggle" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>

            <ul class="public-nav" id="publicNav">
                <li><a href="<?php echo e(route('accueil')); ?>" class="<?php echo e(request()->routeIs('accueil') ? 'active' : ''); ?>">Accueil</a></li>
                <li><a href="<?php echo e(route('evenements.public')); ?>" class="<?php echo e(request()->routeIs('evenements.public*') ? 'active' : ''); ?>">Evenements</a></li>
                <li><a href="<?php echo e(route('aide')); ?>" class="<?php echo e(request()->routeIs('aide') ? 'active' : ''); ?>">Comment ça marche</a></li>
                <li><a href="<?php echo e(route('contact')); ?>" class="<?php echo e(request()->routeIs('contact') ? 'active' : ''); ?>">Contact</a></li>
                <li><a href="<?php echo e(route('tickets.recuperer')); ?>" class="nav-recuperer <?php echo e(request()->routeIs('tickets.recuperer', 'tickets.rechercher') ? 'active' : ''); ?>">Récupérer un ticket</a></li>
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
    <?php if(session('success')): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>
                <ul class="mb-0 ps-3">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <?php echo $__env->yieldContent('content'); ?>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <!-- Premiere ligne : 4 colonnes -->
            <div class="row g-4">
                <!-- Colonne 1 : Logo + Slogan -->
                <div class="col-12 col-md-3">
                    <a href="<?php echo e(route('accueil')); ?>" class="brand" style="font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none; display:flex; align-items:center; gap:0.5rem;">
                        <span style="color: var(--menthe);">Pass</span><span>Event</span>
                    </a>
                    <p class="mt-2" style="color: rgba(255, 255, 255, 0.7); font-size: 0.92rem;">
                        Billetterie simple et rapide pour vos evenements
                    </p>
                </div>
                <!-- Colonne 2 : Liens utiles -->
                <div class="col-6 col-md-3">
                    <h6>Liens utiles</h6>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(route('evenements.public')); ?>">Evenements</a></li>
                        <li><a href="<?php echo e(route('aide')); ?>">Comment ca marche</a></li>
                        <li><a href="<?php echo e(route('tickets.recuperer')); ?>">Mon ticket</a></li>
                        <li><a href="<?php echo e(route('contact')); ?>">Contact</a></li>
                        <li><a href="<?php echo e(route('login')); ?>">Devenir organisateur</a></li>
                    </ul>
                </div>
                <!-- Colonne 3 : Contact + Paiement -->
                <div class="col-6 col-md-3">
                    <h6>Contact</h6>
                    <ul class="footer-links">
                        <li><a href="mailto:passevent2026@gmail.com"><i class="bi bi-envelope me-1"></i>passevent2026@gmail.com</a></li>
                        <li><a href="https://wa.me/22943704513" target="_blank"><i class="bi bi-whatsapp me-1"></i>WhatsApp: 0143704513</a></li>
                    </ul>
                    <h6 class="mt-3">Paiement securise</h6>
                    <p style="color:rgba(255,255,255,0.5); font-size:0.82rem;">Par KKiaPay</p>
                </div>
                <!-- Colonne 4 : Newsletter -->
                <div class="col-6 col-md-3">
                    <h6>Newsletter</h6>
                    <p style="color:rgba(255,255,255,0.6); font-size:0.82rem;">Recevez nos actualites</p>
                    <form id="newsletter-form" class="d-flex gap-1">
                        <?php echo csrf_field(); ?>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="Votre email" required style="border-radius:6px; font-size:0.82rem;">
                        <button type="submit" class="btn btn-sm" style="background:var(--menthe); color:#fff; border-radius:6px; font-weight:600; white-space:nowrap;">OK</button>
                    </form>
                    <div id="newsletter-msg" style="font-size:0.78rem; margin-top:6px;"></div>
                    <div class="d-flex gap-3 mt-2">
                        <a href="https://facebook.com" target="_blank" style="color:rgba(255,255,255,0.6); font-size:1.3rem;"><i class="bi bi-facebook"></i></a>
                        <a href="https://instagram.com" target="_blank" style="color:rgba(255,255,255,0.6); font-size:1.3rem;"><i class="bi bi-instagram"></i></a>
                        <a href="https://wa.me/22943704513" target="_blank" style="color:rgba(255,255,255,0.6); font-size:1.3rem;"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <!-- Deuxieme ligne : petits caracteres -->
            <div class="footer-bottom text-center">
                <p class="mb-0" style="color: rgba(255,255,255,0.45); font-size: 0.78rem;">
                    &copy; 2026 PassEvent – Tous droits reserves
                </p>
                <div class="mt-1">
                    <a href="<?php echo e(route('cgu')); ?>" class="text-decoration-none" style="color: rgba(255,255,255,0.45); font-size: 0.72rem;">CGU</a>
                    <span class="mx-1" style="color: rgba(255,255,255,0.25); font-size: 0.72rem;">·</span>
                    <a href="<?php echo e(route('confidentialite')); ?>" class="text-decoration-none" style="color: rgba(255,255,255,0.45); font-size: 0.72rem;">Politiques</a>
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
        fetch('<?php echo e(route("newsletter.subscribe")); ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            msg.innerHTML = '<span style="color:var(--menthe);">' + d.message + '</span>';
            if (d.success) this.querySelector('input').value = '';
        })
        .catch(() => msg.innerHTML = '<span style="color:#e74c3c;">Erreur. Reessayez.</span>')
        .finally(() => { btn.disabled = false; btn.textContent = 'OK'; });
    });
    </script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views/layouts/public.blade.php ENDPATH**/ ?>