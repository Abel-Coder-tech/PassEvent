<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planches de tickets</title>
    <style>
        @page { size: A4 portrait; margin: 1.35cm 0; }
        html, body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; }
        * { margin: 0; padding: 0; }

        .grille { width: 21cm; table-layout: fixed; border-collapse: collapse; }
        .grille td {
            width: 3.5cm;
            height: 4.5cm;
            text-align: center;
            vertical-align: middle;
            page-break-inside: avoid;
        }
        .grille td img { width: 2.5cm; height: 2.5cm; }
        .grille td .code {
            font-size: 8.5px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }
        .type-footer {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .type-footer td { font-size: 8px; }
        .type-footer .tf-left {
            text-align: left;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #1d1d1f;
        }
        .type-footer .tf-right {
            text-align: right;
            color: #888;
        }

        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
    </style>
</head>
<body>
@foreach($groupes as $groupeIndex => $groupe)
    @foreach($groupe->pages as $page)
        @if($groupeIndex > 0 && $loop->first)
        <div class="page-break"></div>
        @endif
        <table class="grille" cellpadding="0" cellspacing="0">
            @foreach($page->chunk(6) as $row)
            <tr>
                @foreach($row as $ticket)
                <td>
                    <img src="{{ $qrs[$ticket->id] }}" alt="QR">
                    <div class="code">{{ $ticket->code_unique }}</div>
                </td>
                @endforeach
            </tr>
            @endforeach
        </table>
        <table class="type-footer" cellpadding="0" cellspacing="0">
            <tr>
                <td class="tf-left">TICKETS {{ mb_strtoupper($groupe->lot->tarif?->nom ?? 'PASS') }} — {{ $groupe->lot->evenement?->titre }}</td>
                <td class="tf-right">© {{ date('Y') }} PaxEvent . Billetterie en ligne 100% Bénin</td>
            </tr>
        </table>

        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
    @endforeach
@endforeach
</body>
</html>
