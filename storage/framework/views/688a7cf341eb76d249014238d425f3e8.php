<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation email</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f0eeec;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 480px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .header {
            background: linear-gradient(135deg, #5a3d5e, #3d2a40);
            color: #fff;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .btn {
            display: inline-block;
            background: #5a3d5e;
            color: #fff !important;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin: 8px 0;
        }
        .footer {
            padding: 18px 32px;
            text-align: center;
            border-top: 1px solid #eeedeb;
            font-size: 11px;
            color: #8a7a8e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PassEvent</h1>
        </div>
        <div class="body">
            <p>Bonjour <strong><?php echo e($user->nom); ?></strong>,</p>
            <p>Merci de vous etre inscrit sur PassEvent. Veuillez confirmer votre adresse email en cliquant sur le lien ci-dessous :</p>
            <p style="text-align: center;">
                <a href="<?php echo e($url); ?>" class="btn">Confirmer mon email</a>
            </p>
            <p style="font-size:13px; color:#6c757d;">Si vous n'avez pas cree de compte, ignorez cet email.</p>
        </div>
        <div class="footer">
            <p>PassEvent — Billetterie intelligente</p>
        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\DELL\Documents\Laravel\passEvent\resources\views\emails\verification.blade.php ENDPATH**/ ?>