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
            left: {{ $qrX }}mm;
            top: {{ $qrY }}mm;
            width: {{ $qrSize }}mm;
            height: {{ $qrSize }}mm;
            background: #fff;
            padding: {{ $qrPadding }}mm;
            box-sizing: border-box;
            overflow: hidden;
        }
        .qr-zone img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .pax-code {
            position: absolute;
            bottom: 0.3mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #1d1d1f;
            white-space: nowrap;
            text-shadow:
                -1px -1px 0 #fff,
                1px -1px 0 #fff,
                -1px 1px 0 #fff,
                1px 1px 0 #fff;
            line-height: 1.1;
        }
    </style>
</head>
<body>
<div class="ticket">
    @if ($templateUrl)
    <img src="{{ $templateUrl }}" alt="" class="ticket-bg" style="left: {{ $imgLeft }}mm; top: {{ $imgTop }}mm; width: {{ $imgW }}mm; height: {{ $imgH }}mm;">
@endif
    <div class="qr-zone">
        <img src="{{ $qrDataUri }}" alt="QR">
        <div class="pax-code">{{ $codeUnique ?? 'PAX-XXXXX' }}</div>
    </div>
</div>
</body>
</html>