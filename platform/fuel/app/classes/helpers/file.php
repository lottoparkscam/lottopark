<?php

final class Helpers_File
{
    /**
     * Get path and url for file 
     * @param string $domain
     * @param string $content_dir
     * @param string $file_name
     * @return array
     */
    public static function get_WP_file_url_path(
        string $domain,
        string $content_dir,
        string $file_name
    ): array {
        $fuel_root = DOCROOT;
        $document_root = substr($fuel_root, 0, strpos($fuel_root, 'platform'));

        // Generate file path
        $path = $document_root . 'wordpress/' . $content_dir;
        $path .= '/' . $file_name;

        // Generate file url
        $url = 'https://' . $domain;
        $url .= $content_dir;
        $url .= '/' . $file_name;

        return [
            'path' => $path,
            'url' => $url
        ];
    }

    /**
     * @param string $site_id
     * @return string
     */
    public static function get_welcome_popup_image(string $site_id)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $domain = $whitelabel['domain'];

        $file_params = self::get_WP_file_url_path(
            $domain, 
            "/wp-content/uploads/sites/{$site_id}",
            'welcome_popup.png'
        );

        // Check if there's a welcome popup image for the site
        if (file_exists($file_params['path'])) {
            return $file_params['url'];
        }

        // Use default welcome popup image
        $default_file_params = self::get_WP_file_url_path(
            $domain, 
            '/wp-content/themes/base/images/popup',
            'welcome_popup.png'
        );

        return $default_file_params['url'];
    }
}