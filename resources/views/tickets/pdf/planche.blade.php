<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planche de tickets</title>
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

        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
    </style>
</head>
<body>
@foreach($pages as $page)
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

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
