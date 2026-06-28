<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — PaxEvent</title>
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
            padding: 0.75rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
            text-align: center;
        }
        .card .logo { max-width: 100%; height: auto; }
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .35rem; }
        .card .subtitle { font-size: .9rem; color: #6c757d; margin-bottom: 1.5rem; }
        .form-control { border-radius: 10px; padding: .7rem 1rem; border: 1.5px solid #e0dde3; }
        .form-control:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,.12); }
        .btn-primary {
            background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
            font-weight: 600; width: 100%; transition: .2s;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: .6; }
        .login-link { margin-top: 1.5rem; font-size: .85rem; color: #6c757d; }
        .login-link a { color: #542680; font-weight: 600; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        .invalid-feedback { text-align: left; font-size: .8rem; }
        .is-invalid { border-color: #dc3545 !important; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: .6rem 1rem; font-size: .85rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Cr&eacute;er un compte organisateur</h1>
        <p class="subtitle">Entrez votre adresse email pour commencer</p>

        @if($errors->any())
            <div class="alert-danger">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('inscriptions.send-otp') }}">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label small fw-semibold text-muted">Adresse email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus placeholder="exemple@email.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Recevoir mon code</button>
        </form>

        <p class="login-link">
            D&eacute;j&agrave; un compte ? <a href="{{ route('login') }}">Connectez-vous</a>
        </p>
    </div>
</body>
</html>
