<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte cr&eacute;&eacute; — PaxEvent</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f5f7 0%, #e8e0ec 100%);
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 1.5rem;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
            text-align: center;
        }
        .card .logo { max-width: 100%; height: auto; }
        .success-icon { font-size: 3.5rem; color: #2E7D4F; margin-bottom: 1rem; }
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .75rem; }
        .card p { font-size: .9rem; color: #6c757d; line-height: 1.6; margin-bottom: .5rem; }
        .card .email-highlight { font-weight: 700; color: #542680; word-break: break-all; }
        .btn-primary {
            display: inline-block; background: #542680; border: none; border-radius: 10px; padding: .7rem 2rem;
            font-weight: 600; text-decoration: none; color: #fff; transition: .2s; margin-top: 1.25rem;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
        <h1>Compte cr&eacute;&eacute; avec succ&egrave;s</h1>
        <p>Votre compte est en cours de validation.</p>
        <p>Vous recevrez un email de confirmation &agrave;&nbsp;<span class="email-highlight">{{ $email }}</span> sous 24h.</p>
        <a href="{{ route('accueil') }}" class="btn-primary">Retour &agrave; l'accueil</a>
    </div>
</body>
</html>
