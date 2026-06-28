<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_paxevent.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identité — PaxEvent</title>
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
        .card h1 { font-size: 1.5rem; font-weight: 700; color: #1d1d1f; margin-bottom: .25rem; text-align: center; }
        .card .subtitle { font-size: .9rem; color: #6c757d; margin-bottom: 1.5rem; text-align: center; }
        .form-control { border-radius: 10px; padding: .65rem 1rem; border: 1.5px solid #e0dde3; }
        .form-control:focus { border-color: #542680; box-shadow: 0 0 0 3px rgba(84,38,128,.12); }
        .is-invalid { border-color: #dc3545 !important; }
        .invalid-feedback { font-size: .8rem; }
        .form-label { font-size: .82rem; font-weight: 600; color: #495057; margin-bottom: .25rem; }
        .btn-primary {
            background: #542680; border: none; border-radius: 10px; padding: .7rem 1rem;
            font-weight: 600; width: 100%; transition: .2s; margin-top: .5rem;
        }
        .btn-primary:hover { background: #451e68; transform: translateY(-1px); }
        .btn-secondary {
            background: transparent; border: 1.5px solid #e0dde3; border-radius: 10px; padding: .6rem 1rem;
            font-weight: 600; width: 100%; color: #6c757d; transition: .2s; margin-top: .5rem;
            text-decoration: none; display: block; text-align: center;
        }
        .btn-secondary:hover { background: #f8f6f9; }
        .avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #e0dde3; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo_paxevent.png') }}" alt="PaxEvent" class="logo">
        <h1>Votre identité</h1>
        <p class="subtitle">Qui êtes-vous ?</p>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;border-radius:10px;">
                @foreach($errors->all() as $e) {{ $e }} @break @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('inscriptions.post-identity') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nom complet</label>
                <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                       value="{{ old('nom', $data['nom'] ?? '') }}" required placeholder="Votre nom et prénoms">
                @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                       value="{{ old('telephone', $data['telephone'] ?? '') }}" required placeholder="+229 XX XX XX XX">
                @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if(!$from_google)
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror"
                       minlength="8" required placeholder="Min. 8 caractères">
                @error('mot_de_passe') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="mot_de_passe_confirmation" class="form-control"
                       required placeholder="Répétez le mot de passe">
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Photo de profil <span class="text-muted fw-normal">(optionnel)</span></label>
                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                <div class="form-text">Formats acceptés : JPG, PNG. Max 2 Mo.</div>
                @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-primary">Continuer</button>
            <a href="{{ route('inscriptions.create') }}" class="btn-secondary">Recommencer</a>
        </form>
    </div>
</body>
</html>
