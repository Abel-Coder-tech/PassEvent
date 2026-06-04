<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Accès interdit — PassEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f5f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
        }
        .card {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        .card-body { padding: 3rem 2rem; }
        .icon { font-size: 3.5rem; color: #dc3545; }
        h1 { font-size: 4rem; font-weight: 800; color: #dc3545; margin: 0; line-height: 1; }
        h2 { font-size: 1.2rem; color: #333; margin: 0.5rem 0 1rem; }
        p { color: #666; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .btn-primary {
            background: #7B3FA0; border: none; border-radius: 8px;
            padding: 0.6rem 1.5rem; font-weight: 600;
        }
        .btn-primary:hover { background: #6a3590; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-body">
            <div class="icon"><i class="bi bi-shield-lock-fill"></i></div>
            <h1>403</h1>
            <h2>Accès interdit</h2>
            <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
            <a href="<?php echo e(url('/')); ?>" class="btn btn-primary"><i class="bi bi-house me-1"></i> Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\errors\403.blade.php ENDPATH**/ ?>