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
            text-align: center;
        }
        .qr-zone img {
            display: block;
            margin: 0 auto;
        }
        .pax-code {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
            color: #000;
            white-space: nowrap;
            line-height: 1;
            margin-top: 1mm;
            text-shadow:
                -0.5px -0.5px 0 #fff,
                0.5px -0.5px 0 #fff,
                -0.5px 0.5px 0 #fff,
                0.5px 0.5px 0 #fff;
        }
    </style>
</head>
<body>
<div class="ticket">
    @if ($templateUrl)
    <img src="{{ $templateUrl }}" alt="" class="ticket-bg" style="left: {{ $imgLeft }}mm; top: {{ $imgTop }}mm; width: {{ $imgW }}mm; height: {{ $imgH }}mm;">
@endif
    @php $qrImgSize = max(0, $qrSize - 2 * $qrPadding - 4); @endphp
    <div class="qr-zone">
        <img src="{{ $qrDataUri }}" alt="QR" style="width: {{ $qrImgSize }}mm; height: {{ $qrImgSize }}mm;">
        <div class="pax-code">{{ $codeUnique ?? 'PAX-XXXXX' }}</div>
    </div>
</div>
</body>
</html>