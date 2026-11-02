<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset_v('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — PaxEvent</title>
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
            background: #f5f5f7;
            padding: 1rem;
        }
        .card {
            width: 100%;
            max-width: 440px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            background: #fff;
        }
        .header {
            background: linear-gradient(135deg, #5a3d5e, #3d2a40);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .header h1 { font-size: 1.3rem; font-weight: 800; margin: 0; }
        .header p { font-size: 0.85rem; opacity: 0.85; margin: 6px 0 0; }
        .body { padding: 2rem; }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #542680;
            box-shadow: 0 0 0 3px rgba(84,38,128,0.12);
        }
        .input-group-custom { position: relative; }
        .input-group-custom .icon {
            position: absolute; left: 0.8rem; top: 50%;
            transform: translateY(-50%);
            color: #9a9a9a; font-size: 1rem; z-index: 5;
        }
        .btn-submit {
            background: #542680; border: none; border-radius: 8px;
            color: #fff; font-weight: 700; font-size: 0.9rem;
            padding: 0.7rem; width: 100%;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #3d1a5c; }
        .back-link { color: #542680; font-size: 0.85rem; text-decoration: underline; }
        .back-link:hover { color: #542680; }
        .alert { font-size: 0.82rem; padding: 0.6rem 0.8rem; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="{{ asset_v('images/logo_paxevent.png') }}" alt="PaxEvent" height="88" class="mb-2" style="filter:brightness(0) invert(1);">
            <p>Mot de passe oublié</p>
        </div>
        <div class="body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <p style="font-size:0.85rem; color:#6c757d; margin-bottom:1.2rem;">
                Entrez votre email pour recevoir un code de réinitialisation.
            </p>

            @if(!$email || !$sentAt)
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.82rem; font-weight:600;">Email</label>
                        <div class="input-group-custom">
                            <i class="bi bi-envelope icon"></i>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="exemple@email.com" value="{{ old('email', $email) }}" required autofocus>
                        </div>
                        @error('email')<div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-submit">Envoyer un code</button>
                </form>
            @else
                <p style="font-size:0.85rem; color:#6c757d; margin-bottom:1.2rem;">
                    Un code a été envoyé à <strong>{{ $email }}</strong>.
                </p>
                @if(!$verified)
                    <button type="button" class="btn-submit mb-3" disabled>Code envoyé</button>
                    <p id="organizer-countdown" class="text-center small text-muted">
                        Vous pourrez renvoyer un code dans <strong id="organizer-timer">10:00</strong>
                    </p>
                    <form method="POST" action="{{ route('password.resend') }}" id="organizer-resend-form" class="text-center mb-3" hidden>
                        @csrf
                        <a href="#" class="back-link" onclick="this.closest('form').requestSubmit(); return false;">Renvoyer le code</a>
                    </form>
                @endif

                @if(!$verified)
                    <form method="POST" action="{{ route('password.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Code à 4 chiffres</label>
                            <div class="input-group-custom">
                                <i class="bi bi-shield-lock icon"></i>
                                <input id="organizer-reset-code" type="text" name="code" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="one-time-code" class="form-control @error('code') is-invalid @enderror" placeholder="••••" value="{{ old('code') }}" required autofocus>
                            </div>
                            @error('code')<div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                        </div>
                        <button id="organizer-verify-submit" type="submit" class="btn-submit" disabled>Vérifier le code</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Nouveau mot de passe</label>
                            <div class="input-group-custom">
                                <i class="bi bi-lock icon"></i>
                                <input type="password" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror" placeholder="Minimum 8 caractères" required>
                            </div>
                            @error('mot_de_passe')<div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Confirmer le mot de passe</label>
                            <div class="input-group-custom">
                                <i class="bi bi-lock icon"></i>
                                <input type="password" name="mot_de_passe_confirmation" class="form-control" placeholder="Répétez le mot de passe" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">Réinitialiser le mot de passe</button>
                    </form>
                @endif
            @endif

            <p class="text-center mt-3">
                <a href="{{ route('login') }}" class="back-link">Retour à la connexion</a>
            </p>
        </div>
    </div>
<script>
const sentAt = {{ is_numeric($sentAt) ? (int) $sentAt : 0 }};
const timerEl = document.getElementById('organizer-timer');
const resendForm = document.getElementById('organizer-resend-form');
if (sentAt && timerEl && resendForm) {
    const updateTimer = () => {
        const remaining = Math.max(0, sentAt + 600 - Math.floor(Date.now() / 1000));
        timerEl.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
        if (remaining === 0) {
            document.getElementById('organizer-countdown').hidden = true;
            resendForm.hidden = false;
        }
    };
    updateTimer();
    setInterval(updateTimer, 1000);
}

const codeInput = document.getElementById('organizer-reset-code');
const verifyButton = document.getElementById('organizer-verify-submit');
if (codeInput && verifyButton) {
    const updateVerifyButton = () => {
        verifyButton.disabled = !/^\d{4}$/.test(codeInput.value);
    };
    codeInput.addEventListener('input', updateVerifyButton);
    updateVerifyButton();
}
</script>
</body>
</html>