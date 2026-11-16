<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrService
{
    public static function svg(string $contenido, int $tamano = 178): string
    {
        $renderer = new ImageRenderer (
            new RendererStyle($tamano, 0),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        $svg = $writer->writeString($contenido, 'UTF-8', ErrorCorrectionLevel::M());

        return trim(preg_replace('/^<\?xml[^>]+>\s*/i', '', $svg));
    }
}