@php
    $format = fn ($d) => \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($d)->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'));
    $plusieurs = count($nouvellesDates) > 1;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changement de date</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0eeec; margin: 0; padding: 24px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #542680, #3d1a5c); color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .body p { font-size: 14px; color: #1d1d1f; margin: 0 0 12px; line-height: 1.6; }
        .bloc { padding: 12px 16px; border-radius: 8px; margin: 12px 0; font-size: 13px; }
        .ancien { background: #f5f5f5; color: #777; }
        .ancien span { text-decoration: line-through; }
        .nouveau { background: #f6f2fa; border-left: 4px solid #542680; color: #3d1a5c; }
        .bloc strong { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .rassurer { background: #eaf7ef; color: #1e6b43; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin: 16px 0; }
        .footer { padding: 18px 32px; text-align: center; font-size: 11px; color: #fff; background: linear-gradient(135deg, #542680, #3d1a5c); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>Changement de date</h1></div>
        <div class="body">
            <p>Bonjour,</p>
            <p>L'organisateur de <strong>{{ $evenement->titre }}</strong> a modifié {{ $plusieurs ? 'les dates' : 'la date' }} de l'événement.</p>

            <div class="bloc ancien">
                <strong>{{ count($anciennesDates) > 1 ? 'Anciennes dates' : 'Ancienne date' }}</strong>
                @foreach($anciennesDates as $d)<div><span>{{ $format($d) }}</span></div>@endforeach
            </div>
            <div class="bloc nouveau">
                <strong>{{ $plusieurs ? 'Nouvelles dates' : 'Nouvelle date' }}</strong>
                @foreach($nouvellesDates as $d)<div>{{ $format($d) }}</div>@endforeach
                @if($evenement->lieu)<div style="margin-top:6px;">Lieu : {{ $evenement->lieu }}</div>@endif
            </div>

            <div class="rassurer">
                <strong>Votre billet reste valable.</strong> Vous n'avez rien à faire : présentez le même billet (même QR code) à l'entrée, à la nouvelle date.
            </div>
            <p style="font-size:12px;color:#777;">Ne partagez pas votre billet : il ne peut être scanné qu'une seule fois.</p>
        </div>
        <div class="footer">PaxEvent — Billetterie Intelligente 100% Bénin</div>
    </div>
</body>
</html>
