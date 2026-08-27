<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planche de tickets - {{ $lot->nom }}</title>
    <style>
        @page { size: A4 {{ $layout['orientation'] }}; margin: 0; }
        html, body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .page {
            position: relative;
            width: {{ $pageLargeur }}mm;
            height: {{ $pageHauteur }}mm;
            overflow: hidden;
            page-break-after: always;
        }
        .page:last-child { page-break-after: auto; }

        .slot {
            position: absolute;
            background-color: #fff;
            border-radius: 1.5mm;
            overflow: hidden;
            box-shadow: 0 0 0.5px rgba(0,0,0,.12);
        }
        .ticket-bg {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .qr-zone {
            position: absolute;
            background: #fff;
            padding: {{ $qrPadding }}mm;
            box-sizing: border-box;
        }
        .qr-zone img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .pax-code {
            position: absolute;
            bottom: 1.2mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #1d1d1f;
            text-shadow:
                -1px -1px 0 #fff,
                1px -1px 0 #fff,
                -1px 1px 0 #fff,
                1px 1px 0 #fff;
            line-height: 1.1;
        }

        .coupe-h, .coupe-v {
            position: absolute;
            border: 0;
            background: transparent;
            border-top: 0.15mm dashed #d9d9d9;
        }
        .coupe-h { border-top-style: dashed; }
        .coupe-v { border-left: 0.15mm dashed #d9d9d9; }
    </style>
</head>
<body>
@foreach($pages as $page)
    <div class="page">
        @foreach($page as $slotIdx => $ticket)
            @php $pos = $layout['positions'][$slotIdx % $layout['par_page']]; @endphp
            <div class="slot" style="left: {{ $pos['x'] }}mm; top: {{ $pos['y'] }}mm; width: {{ $layout['slot_largeur'] }}mm; height: {{ $layout['slot_hauteur'] }}mm;">
                <img src="{{ $templateUrl }}" alt="" class="ticket-bg">
                <div class="qr-zone" style="left: {{ $qrX }}mm; top: {{ $qrY }}mm; width: {{ $qrSize }}mm; height: {{ $qrSize }}mm;">
                    <img src="{{ $qrs[$ticket->id] }}" alt="QR">
                </div>
                <div class="pax-code">{{ $ticket->code_unique }}</div>
            </div>
        @endforeach

        @foreach($layout['coupes_h'] as $y)
            <div class="coupe-h" style="top: {{ $y }}mm; left: {{ $layout['bloc_gauche'] }}mm; width: {{ $layout['bloc_largeur'] }}mm;"></div>
        @endforeach
        @foreach($layout['coupes_v'] as $x)
            <div class="coupe-v" style="left: {{ $x }}mm; top: {{ $layout['bloc_haut'] }}mm; height: {{ $layout['bloc_hauteur'] }}mm;"></div>
        @endforeach
    </div>
@endforeach
</body>
</html>