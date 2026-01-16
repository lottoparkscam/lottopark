<?php

namespace Helpers;

use Container;
use Fuel\Core\Config;
use Fuel\Core\File;
use Models\Whitelabel;

class LogoHelper
{
    public static function isCurrentWhitelabelLogoExists(): bool
    {
        return File::exists(self::generateCurrentWhitelabelLogoWordpressPath());
    }

    public static function generateCurrentWhitelabelLogoWordpressPath(): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $wordpressThemesPath = Config::get('wordpress.path') . '/wp-content/themes/';
        return $wordpressThemesPath . $whitelabel->theme . '/images/logo.png';
    }

    public static function generateCurrentWhitelabelLogoUrl(): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        return 'https://' . $whitelabel->domain . '/wp-content/themes/' . $whitelabel->theme . '/images/logo.png';
    }

    public static function getWhitelabelImgLogoSection(): ?string
    {
        return self::isCurrentWhitelabelLogoExists() ? self::generateWhitelabelImgLogoSection() : null;
    }

    public static function generateWhitelabelImgLogoSection(): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $src = self::generateCurrentWhitelabelLogoUrl();
        return <<<HTML
        <img src="$src" alt="$whitelabel->name" title="$whitelabel->name">
        HTML;
    }

    public static function getWhitelabelWidgetLogoUrl(): string|bool
    {
        $whitelabel = Container::get('whitelabel');
        $domain = UrlHelper::addWwwPrefixIfNeeded(Container::get('domain'));

        $theme = $whitelabel->theme;
        $logoPath = 'images/logo-widget.png';
        $wordpressThemesPath = Config::get('wordpress.path') . '/wp-content/themes/';
        $imagePath = "$wordpressThemesPath/$theme/$logoPath";
        $imageUrl = "https://$domain/wp-content/themes/$theme/$logoPath";
        $whitelabelImageNotExists = File::exists($imagePath) === false;
        return $whitelabelImageNotExists ? false : $imageUrl;
    }
}
