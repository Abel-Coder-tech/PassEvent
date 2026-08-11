<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planches de tickets physiques</title>
    <style>
        @page { margin: 12mm 10mm; }
        html, body { font-family: 'DejaVu Sans', sans-serif; color: #1d1d1f; }
        * { margin: 0; padding: 0; }

        .entete { text-align: center; margin-bottom: 8px; }
        .entete .titre { font-size: 15px; font-weight: 800; color: #542680; }
        .entete .sous { font-size: 10px; color: #888; margin-top: 2px; }

        .grille { width: 100%; }
        .grille td { width: 25%; vertical-align: top; }

        .carte {
            border: 1.2px solid #d8c8e6;
            border-radius: 8px;
            padding: 6px 7px;
            margin: 4px;
            page-break-inside: avoid;
        }
        .carte .nom { font-size: 9px; font-weight: 800; color: #542680; text-transform: uppercase; letter-spacing: 0.5px; }
        .carte .meta { font-size: 8px; color: #666; margin-top: 1px; }
        .carte .qr { text-align: center; margin: 3px 0; }
        .carte .qr img { width: 64px; height: 64px; }
        .carte .code {
            text-align: center; font-size: 10px; font-weight: 800;
            color: #1d1d1f; letter-spacing: 1.5px;
        }
        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
        .pied { text-align: center; font-size: 8px; color: #aaa; margin-top: 6px; }
    </style>
</head>
<body>
@foreach($groupes as $groupeIndex => $groupe)
    @php $lot = $groupe->lot; $pages = $groupe->pages; @endphp
    @foreach($pages as $page)
        @if($groupeIndex > 0 && $loop->first)
        <div class="page-break"></div>
        @endif
        <div class="entete">
            <div class="titre">{{ $lot->evenement?->titre ?? 'Événement' }}</div>
            <div class="sous">
                {{ $lot->nom }}
                @if($lot->tarif) &ndash; {{ $lot->tarif->nom }} &ndash; {{ number_format($lot->tarif->prix, 0, ',', ' ') }} FCFA @endif
                @if($lot->evenement?->date_event) &ndash; {{ $lot->evenement->date_event->isoFormat('D MMM YYYY') }} @endif
            </div>
        </div>

        <table class="grille" cellpadding="0" cellspacing="0">
            <tr>
                @foreach($page as $index => $ticket)
                    @if($index > 0 && $index % 4 === 0)
                    </tr><tr>
                    @endif
                    <td>
                        <div class="carte">
                            <div class="nom">{{ $lot->evenement?->titre ?? 'Événement' }}</div>
                            <div class="meta">{{ $lot->tarif?->nom ?? '' }} @if($lot->tarif?->prix) &ndash; {{ number_format($lot->tarif->prix, 0, ',', ' ') }} FCFA @endif</div>
                            <div class="qr"><img src="{{ $qrs[$ticket->id] }}" alt="QR"></div>
                            <div class="code">{{ $ticket->code_unique }}</div>
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>

        <div class="pied">Billetterie PaxEvent &ndash; {{ $lot->nom }} &ndash; page {{ $loop->iteration }}</div>

        @if(!$loop->last)
        <div class="page-break"></div>
        @endif
    @endforeach
@endforeach
</body>
</html>
