<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&eacute;capitulatif — PaxEvent</title>
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
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        }
        .card .logo { max-width: 100%; height: auto; margin-bottom: 1.5rem; }
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: 1.5rem; text-align: center; }
        .recap-row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid #f0eeec; font-size: .9rem; }
        .recap-row:last-child { border-bottom: none; }
        .recap-label { color: #6c757d; }
        .recap-value { font-weight: 600; color: #1d1d1f; text-align: right; max-width: 60%; word-break: break-word; }
        .recap-section { background: #f8f6f9; border-radius: 12px; padding: .75rem 1rem; margin-bottom: 1rem; }
        .recap-section-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9972B0; margin-bottom: .5rem; }
        .form-check-label { font-size: .85rem; color: #495057; }
        .form-check-input:checked { background-color: #542680; border-color: #542680; }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(84,38,128,.12); border-color: #542680; }
        .btn-primary {
            background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
            font-weight: 600; width: 100%; transition: .2s; margin-top: .5rem;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }
        .btn-secondary {
            background: transparent; border: 1.5px solid #e0dde3; border-radius: 10px; padding: .6rem 1rem;
            font-weight: 600; width: 100%; color: #6c757d; transition: .2s; margin-top: .5rem;
            text-decoration: none; display: block; text-align: center;
        }
        .btn-secondary:hover { background: #f8f6f9; }
        .avatar-preview { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #e0dde3; }
        .invalid-feedback { font-size: .8rem; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>R&eacute;capitulatif</h1>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <div class="recap-section">
            <div class="recap-section-title">Type de compte</div>
            <div class="recap-row">
                <span class="recap-label">Type</span>
                <span class="recap-value">
                    @if($reg['type'] === 'universitaire') <i class="bi bi-building-columns"></i>
                    @elseif($reg['type'] === 'particulier') <i class="bi bi-person"></i>
                    @else <i class="bi bi-building"></i>
                    @endif
                    {{ ucfirst($reg['type']) }}
                </span>
            </div>
        </div>

        <div class="recap-section">
            <div class="recap-section-title">Coordonn&eacute;es</div>
            <div class="recap-row">
                <span class="recap-label">Email</span>
                <span class="recap-value">{{ $reg['email'] }}</span>
            </div>
            @if($reg['type'] === 'universitaire' || $reg['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label">{{ $reg['type'] === 'universitaire' ? 'Universit&eacute;' : 'Organisation' }}</span>
                    <span class="recap-value">{{ $reg['data']['organisation'] }}</span>
                </div>
                @if($reg['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label">Type d&eacute;tail</span>
                    <span class="recap-value">{{ ucfirst($reg['data']['type_detail']) }}</span>
                </div>
                @endif
            @endif
            <div class="recap-row">
                <span class="recap-label">Nom</span>
                <span class="recap-value">{{ $reg['data']['nom'] }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">T&eacute;l&eacute;phone</span>
                <span class="recap-value">{{ $reg['data']['telephone'] }}</span>
            </div>
        </div>

        @if(!empty($reg['data']['avatar']))
        <div class="recap-section" style="text-align:center;">
            <div class="recap-section-title">
                @if($reg['type'] === 'particulier') Photo de profil
                @else Logo
                @endif
            </div>
            <img src="{{ asset('storage/' . $reg['data']['avatar']) }}" class="avatar-preview" alt="Avatar">
        </div>
        @endif

        <form method="POST" action="{{ route('inscriptions.confirm') }}">
            @csrf
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cgu" id="cgu" value="1" required>
                    <label class="form-check-label" for="cgu">
                        J'accepte les <a href="{{ route('cgu') }}" target="_blank">conditions g&eacute;n&eacute;rales d'utilisation</a>
                        et la <a href="{{ route('confidentialite') }}" target="_blank">politique de confidentialit&eacute;</a>
                    </label>
                    @error('cgu') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Cr&eacute;er mon compte</button>
            <a href="{{ route('inscriptions.infos') }}" class="btn-secondary">Pr&eacute;c&eacute;dent</a>
        </form>
    </div>
</body>
</html>
