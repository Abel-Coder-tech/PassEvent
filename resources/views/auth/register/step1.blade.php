<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Type de compte — PaxEvent</title>
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
            padding: 2.5rem 2rem;
            max-width: 560px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
            text-align: center;
        }
        .card .logo { max-width: 100%; height: auto; margin-bottom: 1.5rem; }
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: 1.75rem; }
        .type-card {
            border: 2px solid #e0dde3;
            border-radius: 14px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
            height: 100%;
        }
        .type-card:hover { border-color: #9972B0; background: #faf8fb; }
        .type-card.selected { border-color: #542680; background: #f5f0f9; }
        .type-card .icon { font-size: 2rem; color: #542680; margin-bottom: .5rem; }
        .type-card .name { font-weight: 700; font-size: 1rem; color: #1d1d1f; margin-bottom: .25rem; }
        .type-card .desc { font-size: .8rem; color: #6c757d; line-height: 1.4; }
        .btn-primary {
            background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
            font-weight: 600; width: 100%; transition: .2s; margin-top: 1.5rem;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
        .invalid-feedback { text-align: center; }
        .back-link { font-size: .85rem; color: #6c757d; margin-top: 1rem; }
        .back-link a { color: #6c757d; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        input[type="radio"] { display: none; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Quel type de compte souhaitez-vous cr&eacute;er ?</h1>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('inscriptions.post-type') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="type-card" onclick="select(this, 'universitaire')">
                        <div class="icon"><i class="bi bi-building-columns"></i></div>
                        <div class="name">Universitaire</div>
                        <div class="desc">Vous repr&eacute;sentez une universit&eacute; ou un &eacute;tablissement scolaire</div>
                        <input type="radio" name="type" value="universitaire" required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card" onclick="select(this, 'particulier')">
                        <div class="icon"><i class="bi bi-person"></i></div>
                        <div class="name">Particulier</div>
                        <div class="desc">Vous organisez des &eacute;v&eacute;nements en votre nom propre</div>
                        <input type="radio" name="type" value="particulier" required>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="type-card" onclick="select(this, 'organisation')">
                        <div class="icon"><i class="bi bi-building"></i></div>
                        <div class="name">Organisation</div>
                        <div class="desc">Vous repr&eacute;sentez une entreprise, une association ou un club</div>
                        <input type="radio" name="type" value="organisation" required>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Continuer</button>
        </form>

        <p class="back-link">
            <a href="{{ route('inscriptions.create') }}"><i class="bi bi-arrow-left"></i> Recommencer</a>
        </p>
    </div>

    <script>
        function select(el, val) {
            document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input[type="radio"]').checked = true;
            document.getElementById('submit-btn').disabled = false;
        }
    </script>
</body>
</html>
