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
            position: absolute;
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
            height: auto;
            display: block;
        }

        .pax-code {
            position: absolute;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.4px;
            color: #000;
            white-space: nowrap;
            line-height: 1;
            background: #fff;
            text-shadow:
                -0.5px -0.5px 0 #fff,
                0.5px -0.5px 0 #fff,
                -0.5px 0.5px 0 #fff,
                0.5px 0.5px 0 #fff;
        }

        .coupe-h, .coupe-v {
            position: absolute;
            border: 0;
            background: transparent;
            border-top: 0.15mm dashed #d9d9d9;
        }
        .coupe-h { border-top-style: dashed; }
        .coupe-v { border-left: 0.15mm dashed #d9d9d9; }

        .marge-sign {
            position: absolute;
            color: #9a9a9a;
            letter-spacing: 0.5px;
            line-height: 1.1;
            white-space: nowrap;
        }
    </style>
</head>
<body>
@foreach($pages as $page)
    <div class="page">
        @foreach($page as $slotIdx => $ticket)
            @php $pos = $layout['positions'][$slotIdx % $layout['par_page']]; @endphp
            <div class="slot" style="left: {{ $pos['x'] }}mm; top: {{ $pos['y'] }}mm; width: {{ $layout['slot_largeur'] }}mm; height: {{ $layout['slot_hauteur'] }}mm;">
                @if ($templateUrl)
                    <img src="{{ $templateUrl }}" alt="" class="ticket-bg" style="left: {{ $imgLeft }}mm; top: {{ $imgTop }}mm; width: {{ $imgW }}mm; height: {{ $imgH }}mm;">
                @endif
                <div class="qr-zone" style="left: {{ $qrX }}mm; top: {{ $qrY }}mm; width: {{ $qrSize }}mm; height: {{ $qrSize }}mm;">
                    <img src="{{ $qrs[$ticket->id] }}" alt="QR">
                </div>
                @php $paxTop = $qrY + $qrSize + 1.5; @endphp
                <div class="pax-code" style="left: {{ $qrX }}mm; top: {{ $paxTop }}mm; width: {{ $qrSize }}mm;">{{ $ticket->code_unique }}</div>
            </div>
        @endforeach

        @foreach($layout['coupes_h'] as $y)
            <div class="coupe-h" style="top: {{ $y }}mm; left: {{ $layout['bloc_gauche'] }}mm; width: {{ $layout['bloc_largeur'] }}mm;"></div>
        @endforeach
        @foreach($layout['coupes_v'] as $x)
            <div class="coupe-v" style="left: {{ $x }}mm; top: {{ $layout['bloc_haut'] }}mm; height: {{ $layout['bloc_hauteur'] }}mm;"></div>
        @endforeach

        <span class="marge-sign" style="left: {{ $layout['marge_gauche'] }}mm; bottom: {{ $signBottom }}mm; font-size: {{ $signFont }}px;">{{ $lot->evenement?->titre ?? '' }}{{ $lot->tarif?->nom ? ' — '.$lot->tarif->nom : '' }}</span>
        <span class="marge-sign" style="right: {{ $layout['marge_gauche'] }}mm; bottom: {{ $signBottom }}mm; font-size: {{ $signFont }}px;">© {{ date('Y') }} PaxEvent . Billetterie en ligne 100% Bénin</span>
    </div>
@endforeach
</body>
</html>