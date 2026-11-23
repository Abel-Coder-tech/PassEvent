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
        }
        .qr-zone img {
            position: absolute;
            display: block;
        }
        .qr-wrap {
            position: absolute;
            left: {{ $padX }}mm;
            top: {{ $padTop }}mm;
            width: {{ $qrSize }}mm;
            height: {{ $qrSize }}mm;
        }
        .qr-favicon {
            position: absolute;
            top: 50%;
            left: 50%;
            border-radius: 50%;
            background: #fff;
            padding: 0.3mm;
            box-sizing: border-box;
        }
        .pax-band {
            position: absolute;
            top: {{ $bandTop }}mm;
            left: 0;
            width: 100%;
            height: {{ $paxBandH }}mm;
            padding: 0 0 {{ $paxBottom }}mm;
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
        .code-vertical {
            position: absolute;
            overflow: hidden;
        }
        .code-vertical img {
            position: absolute;
            left: 0;
            top: 0;
            display: block;
        }
    </style>
</head>
<body>
<div class="ticket">
    @if ($templateUrl)
    {{-- Visuel recadré à sa largeur : la bande de pliage éventuelle (bracelet) reste blanche --}}
    <div style="position: absolute; left: 0; top: 0; width: {{ $visuelW ?? $slotW }}mm; height: {{ $slotH }}mm; overflow: hidden;">
        <img src="{{ $templateUrl }}" alt="" class="ticket-bg" style="left: {{ $imgLeft }}mm; top: {{ $imgTop }}mm; width: {{ $imgW }}mm; height: {{ $imgH }}mm;">
    </div>
@endif
    <div class="qr-zone">
        <div class="qr-wrap">
            <img src="{{ $qrDataUri }}" alt="QR" style="left: 0; top: 0; width: {{ $qrSize }}mm; height: {{ $qrSize }}mm;">
            @if(!empty($faviconDataUri))
                <img src="{{ $faviconDataUri }}" alt="" class="qr-favicon" style="width: {{ $faviconSize }}mm; height: {{ $faviconSize }}mm; margin-top: -{{ $faviconSize / 2 }}mm; margin-left: -{{ $faviconSize / 2 }}mm;">
            @endif
        </div>
        @unless(!empty($codeVertical))
        <div class="pax-band">
            <div class="pax-code">{{ $codeUnique ?? 'PAX-XXXXX' }}</div>
        </div>
        @endunless
    </div>
    @if(!empty($codeVertical) && !empty($codeVerticalUri))
        <div class="code-vertical" style="left: {{ $codeX }}mm; top: {{ $codeY }}mm; width: {{ $codeW }}mm; height: {{ $codeH }}mm;">
            <img src="{{ $codeVerticalUri }}" alt="" style="width: {{ $codeW }}mm; height: {{ $codeH }}mm;">
        </div>
    @endif
</div>
</body>
</html>