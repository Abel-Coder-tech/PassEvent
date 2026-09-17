<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public static function generateDataUri(string $data, int $size = 200, string $ecc = 'L'): string
    {
        $level = match (strtoupper($ecc)) {
            'H' => ErrorCorrectionLevel::H(),
            'Q' => ErrorCorrectionLevel::Q(),
            'M' => ErrorCorrectionLevel::M(),
            default => ErrorCorrectionLevel::L(),
        };

        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($data, Encoder::DEFAULT_BYTE_MODE_ENCODING, $level);

        $base64 = base64_encode($svg);

        return 'data:image/svg+xml;base64,' . $base64;
    }
}
