<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif — PaxEvent</title>
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
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 40px rgba(0,0,0,0.06);
        }
        .card .logo { max-width: 150px; height: auto; display: block; margin: 0 auto 1rem; }
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
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Récapitulatif</h1>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <div class="recap-section">
            <div class="recap-section-title">Identité</div>
            <div class="recap-row">
                <span class="recap-label">Nom</span>
                <span class="recap-value">{{ $reg['identity']['nom'] }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Email</span>
                <span class="recap-value">{{ $reg['email'] }}</span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Téléphone</span>
                <span class="recap-value">{{ $reg['identity']['telephone'] }}</span>
            </div>
            @if(!empty($reg['identity']['avatar']))
            <div class="recap-row">
                <span class="recap-label">Photo</span>
                <span class="recap-value"><img src="{{ asset('storage/' . $reg['identity']['avatar']) }}" class="avatar-preview" alt="Avatar"></span>
            </div>
            @endif
        </div>

        <div class="recap-section">
            <div class="recap-section-title">Organisation</div>
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
            @if($reg['type'] === 'universitaire' || $reg['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label">{{ $reg['type'] === 'universitaire' ? 'Université' : 'Organisation' }}</span>
                    <span class="recap-value">{{ $reg['org_data']['organisation'] }}</span>
                </div>
                @if($reg['type'] === 'organisation')
                <div class="recap-row">
                    <span class="recap-label">Type détail</span>
                    <span class="recap-value">{{ ucfirst($reg['org_data']['type_detail']) }}</span>
                </div>
                @endif
            @endif
            @if(!empty($reg['org_data']['description']))
            <div class="recap-row">
                <span class="recap-label">Description</span>
                <span class="recap-value" style="max-width:70%;">{{ Str::limit($reg['org_data']['description'], 60) }}</span>
            </div>
            @endif
            <div class="recap-row">
                <span class="recap-label">Justificatif</span>
                <span class="recap-value">Fourni</span>
            </div>
        </div>

        <form method="POST" action="{{ route('inscriptions.confirm') }}">
            @csrf
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cgu" id="cgu" value="1" required>
                    <label class="form-check-label" for="cgu">
                        J'accepte les <a href="{{ route('cgu') }}" target="_blank">conditions générales d'utilisation</a>
                        et la <a href="{{ route('confidentialite') }}" target="_blank">politique de confidentialité</a>
                    </label>
                    @error('cgu') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
            <button type="submit" class="btn-primary">Créer mon compte</button>
            <a href="{{ route('inscriptions.org') }}" class="btn-secondary">Précédent</a>
        </form>
    </div>
</body>
</html>
