<?php

namespace Helpers;

use Container;
use Exception;
use Lotto_Helper;
use Models\Whitelabel;

final class AssetHelper
{
    const TYPE_WORDPRESS = 1;
    const TYPE_CRM = 2;
    const TYPE_LOTTO_PLATFORM = 3;

    const MIX_MANIFEST_FILE_NAME = 'mix-manifest.json';
    const WORDPRESS_THEMES_PATH = '/wordpress/wp-content/themes/';
    const CRM_ASSET_PATH = '/platform/public/assets/crm/';
    const LOTTO_PLATFORM_ASSET_PATH = '/wordpress/wp-content/plugins/lotto-platform/public/';

    /**
     * Get the path to a versioned Mix file.
     *
     * @throws Exception
     */
    public static function mix(string $path, int $type, bool $fromBase = false): string
    {
        static $manifests = [];

        $rootPath = dirname(__DIR__);
        $manifestPath = $rootPath . '/../../../../' . self::MIX_MANIFEST_FILE_NAME;

        if (!isset($manifests[$manifestPath])) {
            if (!is_file($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];
        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();

        $filePath = '';
        switch ($type) {
            case self::TYPE_WORDPRESS:
                /** @var Whitelabel|null $whitelabel */
                $whitelabel = Container::get('whitelabel');

                $shouldGetThemeUrlFromWordpress = empty($whitelabel) && function_exists('get_template_directory_uri');
                if ($shouldGetThemeUrlFromWordpress) {
                    $theme = get_stylesheet();
                    $themeUrl = get_stylesheet_directory_uri() . '/';
                } else {
                    $theme = $whitelabel->theme;
                    $themeUrl = "/wp-content/themes/$theme/";
                }

                if ($fromBase) {
                    $themeUrl = str_replace($theme . '/', 'base/', $themeUrl);
                    $theme = 'base';
                }

                $filePath = self::WORDPRESS_THEMES_PATH . $theme . '/' . $path;
                $filePathWithVersion = str_replace("/wordpress/wp-content/themes/$theme/", '', $manifest[$filePath]);

                $assetPath = "$themeUrl{$filePathWithVersion}";
                break;
            case self::TYPE_CRM:
                $filePath = self::CRM_ASSET_PATH . $path;
                $filePathWithVersion = str_replace('/platform/public/', '', $manifest[$filePath]);

                $assetPath = "https://$domain/{$filePathWithVersion}";
                break;
            case self::TYPE_LOTTO_PLATFORM:
                $filePath = self::LOTTO_PLATFORM_ASSET_PATH . $path;
                $filePathWithVersion = str_replace('/wordpress', '', $manifest[$filePath]);

                $assetPath = "https://$domain{$filePathWithVersion}";
                break;
            default:
                break;
        }

        if (!isset($manifest[$filePath])) {
            throw new Exception("Unable to locate Mix file: {$path}.");
        }

        return $assetPath;
    }

    /**
     * NOTICE: Extension is required eg. image.png
     * This function takes images from:
     * wordpress/wp-content/themes/base/images/yourImage.ext
     * Return empty string if extension is disallowed
     */
    public static function getBaseImage(string $imageName): string
    {
        $allowedExtensions = ImageHelper::ALLOWED_IMAGE_EXTENSIONS;
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);

        $imageHasInvalidFormat = !in_array($imageExtension, $allowedExtensions);
        if ($imageHasInvalidFormat) {
            return '';
        }

        return get_template_directory_uri() . '/images/' . $imageName;
    }
}
