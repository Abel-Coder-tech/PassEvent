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
        .pax-band {
            position: absolute;
            left: {{ $qrX }}mm;
            top: {{ $qrY + $qrSize }}mm;
            width: {{ $qrSize }}mm;
            height: {{ $qrPaddingBottom }}mm;
            background: #fff;
            line-height: {{ $qrPaddingBottom }}mm;
            text-align: center;
            overflow: hidden;
        }
        .pax-band .pax-code {
            display: inline-block;
            margin-top: 0.3mm;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0;
            color: #000;
            white-space: nowrap;
            line-height: normal;
        }
    </style>
</head>
<body>
<div class="ticket">
    @if ($templateUrl)
    <img src="{{ $templateUrl }}" alt="" class="ticket-bg" style="left: {{ $imgLeft }}mm; top: {{ $imgTop }}mm; width: {{ $imgW }}mm; height: {{ $imgH }}mm;">
@endif
    @php $qrImgSize = max(0, $qrSize - 2 * $qrPadding); @endphp
    <div class="qr-zone">
        <img src="{{ $qrDataUri }}" alt="QR" style="width: {{ $qrImgSize }}mm; height: {{ $qrImgSize }}mm;">
    </div>
    <div class="pax-band">
        <div class="pax-code">{{ $codeUnique ?? 'PAX-XXXXX' }}</div>
    </div>
</div>
</body>
</html>