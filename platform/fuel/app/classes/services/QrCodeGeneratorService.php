<?php

namespace Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Class implementing QR code generation based on a URL and size.
 * It generates base64 that can be used directly in HTML code
 */
final class QrCodeGeneratorService
{
    /** @return string (image in base64) */
    public function generate(string $url, int $size, int $margin = 0): string
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($url)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize($size)
            ->setMargin($margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $result = $writer->write($qrCode);

        return $result->getDataUri();
    }
}
