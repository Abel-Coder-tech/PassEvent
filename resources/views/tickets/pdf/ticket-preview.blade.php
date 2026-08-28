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
            width: 100%;
            height: 100%;
            object-fit: contain;
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
        }
        .qr-zone img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .pax-code {
            position: absolute;
            padding-top: 0.8mm;
            text-align: center;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #1d1d1f;
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
    <img src="{{ $templateUrl }}" alt="" class="ticket-bg">
@endif
    <div class="qr-zone">
        <img src="{{ $qrDataUri }}" alt="QR">
    </div>
    <div class="pax-code" style="left: {{ $qrX }}mm; top: {{ $qrY + $qrSize + 0.8 }}mm; width: {{ $qrSize }}mm;">{{ $codeUnique ?? 'PAX-XXXXX' }}</div>
</div>
</body>
</html>