<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planche de tickets</title>
    <style>
        @page { margin: 10mm; }
        html, body { font-family: 'DejaVu Sans', sans-serif; }
        * { margin: 0; padding: 0; }

        .grille { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grille td {
            width: 25%;
            text-align: center;
            vertical-align: middle;
            padding: 6px;
            page-break-inside: avoid;
        }
        .grille td img { width: 108px; height: 108px; }

        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
    </style>
</head>
<body>
@foreach($pages as $page)
    <table class="grille" cellpadding="0" cellspacing="0">
        <tr>
            @foreach($page as $index => $ticket)
                @if($index > 0 && $index % 4 === 0)
                </tr><tr>
                @endif
                <td>
                    <img src="{{ $qrs[$ticket->id] }}" alt="QR">
                </td>
            @endforeach
        </tr>
    </table>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
