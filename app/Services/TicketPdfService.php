<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class TicketPdfService
{
    // Génère le PDF du ticket à une taille fixe : 8 × 13 cm.
    public static function generer(Ticket $ticket, string $qrCodeDataUri, string $logoDataUri, ?array $shared = null): DomPdfWrapper
    {
        // dompdf attend des points : 1 cm = 28.3465 pt
        $largeur = 8 * 28.3465;   // 226.772 pt
        $hauteur = 13 * 28.3465;  // 368.5045 pt

        $eventImageInfo = $shared['eventImage'] ?? self::eventImageInfo($ticket);
        $eventImageDataUri = $eventImageInfo['uri'] ?? null;
        $eventImageW = $eventImageInfo['w'] ?? null;
        $eventImageH = $eventImageInfo['h'] ?? null;
        $faviconDataUri = $shared['favicon'] ?? self::faviconDataUri();

        // Sur le fond sombre du bas, on utilise le logo blanc pour rester lisible.
        if (isset($shared['logo'])) {
            $logoDataUri = $shared['logo'];
        } elseif ($eventImageDataUri) {
            $logoDataUri = Ticket::logoBlancDataUri();
        }

        $pdf = Pdf::loadView('tickets.pdf.ticket', compact(
            'ticket',
            'qrCodeDataUri',
            'logoDataUri',
            'eventImageDataUri',
            'eventImageW',
            'eventImageH',
            'faviconDataUri'
        ));
        $pdf->setPaper([0, 0, $largeur, $hauteur], 'portrait');
        $pdf->render();

        return $pdf;
    }

    // Données communes à tous les tickets d'un même événement : calculées une seule fois.
    public static function sharedForTicket(Ticket $ticket): array
    {
        $eventImageInfo = self::eventImageInfoFromPath($ticket->evenement?->image);

        return [
            'eventImage' => $eventImageInfo,
            'favicon' => self::faviconDataUri(),
            'logo' => ($eventImageInfo['uri'] ?? null)
                ? Ticket::logoBlancDataUri()
                : Ticket::logoVioletDataUri(),
        ];
    }

    // Rend le PDF du ticket et renvoie les octets, en s'appuyant sur un cache court-circuité.
    public static function renduTicket(Ticket $ticket, string $qrCodeDataUri, ?array $shared = null, bool $useCache = true): string
    {
        $shared ??= self::sharedForTicket($ticket);

        if (! $useCache) {
            return self::generer($ticket, $qrCodeDataUri, $shared['logo'], $shared)->output();
        }

        $signature = md5((string) json_encode([
            $ticket->id,
            $ticket->code_unique,
            $ticket->updated_at?->timestamp,
            $ticket->nom,
            $ticket->prenom,
            $ticket->email_acheteur,
            $ticket->evenement?->id,
            $ticket->evenement?->titre,
            $ticket->evenement?->image,
            $ticket->tarif?->id,
            $ticket->tarif?->designation,
            $ticket->tarif?->prix,
        ]));

        $cacheKey = 'pdf_ticket_'.$ticket->id.'_'.substr($signature, 0, 16);

        return Cache::remember($cacheKey, now()->addHours(2), fn () =>
            self::generer($ticket, $qrCodeDataUri, $shared['logo'], $shared)->output()
        );
    }

    // Image importée par l'organisateur pour l'événement : data-URI + dimensions.
    protected static function eventImageInfo(Ticket $ticket): array
    {
        return self::eventImageInfoFromPath($ticket->evenement?->image);
    }

    protected static function eventImageInfoFromPath(?string $path): array
    {
        $result = ['uri' => null, 'w' => null, 'h' => null];

        if (!$path) {
            return $result;
        }

        $abs = Storage::disk('public')->path($path);
        if (!is_file($abs)) {
            return $result;
        }

        $mime = @mime_content_type($abs) ?: 'image/jpeg';

        $result['uri'] = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($abs));

        $size = @getimagesize($abs);
        if ($size !== false) {
            $result['w'] = (int) $size[0];
            $result['h'] = (int) $size[1];
        }

        return $result;
    }

    // Favicon PaxEvent (incrusté au centre du QR code), en data-URI (ou null).
    protected static function faviconDataUri(): ?string
    {
        foreach (['images/paxevent_icone1.png', 'images/logo_paxevent.png', 'favicon.png'] as $rel) {
            $abs = public_path($rel);
            if (is_file($abs)) {
                return 'data:image/png;base64,' . base64_encode((string) file_get_contents($abs));
            }
        }

        return null;
    }
}
