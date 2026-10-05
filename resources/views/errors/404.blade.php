<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page introuvable — PaxEvent</title>
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
        .icon { font-size: 3.5rem; color: #7B3FA0; }
        h1 { font-size: 4rem; font-weight: 800; color: #7B3FA0; margin: 0; line-height: 1; }
        h2 { font-size: 1.2rem; font-weight: 600; color: #333; margin: 0.5rem 0 1rem; }
        p { color: #666; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .details {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .details dt { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #86868b; }
        .details dd { margin: 0 0 0.5rem; font-size: 0.82rem; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; color: #333; word-break: break-all; }
        .details dd:last-child { margin-bottom: 0; }
        .hint { font-size: 0.8rem; color: #86868b; }
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
            <div class="icon"><i class="bi bi-search"></i></div>
            <h1>404</h1>
            <h2>Page introuvable</h2>
            <p>La page demandée n'existe pas sur ce serveur.</p>
            @php
                $chemin = request()->path();
                $methode = request()->method();
            @endphp
            <dl class="details">
                <dt>Adresse demandée</dt>
                <dd>{{ $methode }} /{{ $chemin }}</dd>
                @if (request()->query())
                    <dt>Paramètres</dt>
                    <dd>{{ http_build_query(request()->query()) }}</dd>
                @endif
                <dt>Serveur</dt>
                <dd>{{ request()->getHost() }}</dd>
            </dl>
            <p class="hint">Si cette page devrait exister, signalez-nous l'adresse ci-dessus.</p>
            <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house me-1"></i> Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
