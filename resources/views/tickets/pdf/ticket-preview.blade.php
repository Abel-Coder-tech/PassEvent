<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        html, body { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .ticket {
            width: {{ $ticketLargeur }}mm;
            height: {{ $ticketHauteur }}mm;
            position: relative;
            overflow: hidden;
            background-color: #fff;
        }
        .ticket-bg {
            width: 100%;
            height: 100%;
            object-fit: fill;
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
    </style>
</head>
<body>
<div class="ticket">
    <img src="{{ $templateUrl }}" alt="" class="ticket-bg">
    <div class="qr-zone">
        <img src="{{ $qrDataUri }}" alt="QR">
    </div>
</div>
</body>
</html>
