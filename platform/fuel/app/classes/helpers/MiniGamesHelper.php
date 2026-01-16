<?php

namespace Helpers;

final class MiniGamesHelper
{
    public static function getBallImageSrc(string $slug): string
    {
        return get_template_directory_uri() . '/images/MiniGames/' . $slug . '/ball.png';
    }
}
