<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planche de tickets - {{ $lot->nom }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        html, body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .grille {
            width: 190mm;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .grille td {
            width: {{ $ticketLargeur }}mm;
            height: {{ $ticketHauteur }}mm;
            padding: 0;
            vertical-align: top;
            page-break-inside: avoid;
            overflow: hidden;
        }
        .ticket-cell {
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
            box-sizing: border-box;
        }
        .qr-zone img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .page-footer {
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #1d1d1f;
            margin-top: 3mm;
        }

        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
    </style>
</head>
<body>
@foreach($pages as $pageIdx => $page)
    <table class="grille" cellpadding="0" cellspacing="0">
        @foreach($page->chunk(2) as $row)
        <tr>
            @foreach($row as $ticket)
            <td>
                <div class="ticket-cell">
                    <img src="{{ $templateUrl }}" alt="" class="ticket-bg">
                    <div class="qr-zone">
                        <img src="{{ $qrs[$ticket->id] }}" alt="QR">
                    </div>
                </div>
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>
    <div class="page-footer">TICKETS {{ mb_strtoupper($lot->tarif?->nom ?? 'PASS') }} — {{ $lot->evenement?->titre }}</div>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
