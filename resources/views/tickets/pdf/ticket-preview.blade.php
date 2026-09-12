<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        html, body { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .ticket {
            width: {{ $slotW }}mm;
            height: {{ $slotH }}mm;
            position: relative;
            overflow: hidden;
            background-color: #fff;
        }
        .ticket-bg {
            position: absolute;
            display: block;
        }
        .qr-zone {
            position: absolute;
            left: {{ $zoneX }}mm;
            top: {{ $zoneY }}mm;
            width: {{ $zoneW }}mm;
            height: {{ $zoneH }}mm;
            background: #fff;
            border-radius: 1.5mm;
            overflow: hidden;
            text-align: center;
        }
        .qr-zone img {
            position: absolute;
            display: block;
        }
        .pax-band {
            position: absolute;
            top: {{ $bandTop }}mm;
            left: 0;
            width: 100%;
            height: {{ $paxBandH }}mm;
            padding: {{ $gap }}mm 0 {{ $paxBottom }}mm;
            background: #fff;
            text-align: center;
            overflow: hidden;
            box-sizing: border-box;
        }
        .pax-band .pax-code {
            font-size: {{ $paxFont }}px;
            font-weight: 700;
            letter-spacing: 0;
            color: #000;
            white-space: nowrap;
            line-height: {{ $paxLineH }}mm;
        }
    </style>
</head>
<body>
<div class="ticket">
    @if ($templateUrl)
    <img src="{{ $templateUrl }}" alt="" class="ticket-bg" style="left: {{ $imgLeft }}mm; top: {{ $imgTop }}mm; width: {{ $imgW }}mm; height: {{ $imgH }}mm;">
@endif
    <div class="qr-zone">
        <img src="{{ $qrDataUri }}" alt="QR" style="left: {{ $padX }}mm; top: {{ $padTop }}mm; width: {{ $qrSize }}mm; height: {{ $qrSize }}mm;">
        <div class="pax-band">
            <div class="pax-code">{{ $codeUnique ?? 'PAX-XXXXX' }}</div>
        </div>
    </div>
</div>
</body>
</html>