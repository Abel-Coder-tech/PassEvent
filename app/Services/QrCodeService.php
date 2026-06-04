<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public static function generateDataUri(string $data, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($data);

        $base64 = base64_encode($svg);

        return 'data:image/svg+xml;base64,' . $base64;
    }
}
