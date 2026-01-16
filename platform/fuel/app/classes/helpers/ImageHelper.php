<?php

namespace Helpers;

final class ImageHelper
{
    public const ALLOWED_IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg'];
    public const BASE_64_IMAGE = 'data:image/png;base64,';

    public static function generateBase64Image(string $image): string
    {
        return self::BASE_64_IMAGE . ' ' . base64_encode($image);
    }

    public static function isImageBase64Encoded(string $image): bool
    {
        return strncmp($image, self::BASE_64_IMAGE, 22) === 0;
    }
}
