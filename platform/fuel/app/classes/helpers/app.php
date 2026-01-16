<?php

/**
 * Application helper
 */
class Helpers_App
{
    /**
     * Cache for file path.
     *
     * @var string
     */
    private static $base_path = null;

    /**
     * Get absolute file path with relative path attached.
     * @param string $relative_path omit to retrieve base path.
     * @return string absolute file path with relative_path attached.
     */
    public static function get_absolute_file_path(string $relative_path = ''): string
    {
        self::$base_path = self::$base_path ?: substr(
            DOCROOT, 
            0,
            strrpos(DOCROOT, DIRECTORY_SEPARATOR, -9) + 1 // offset sufficient to omit trailing separator and pre public one. + 1 for inclusion of separator.  
        ); 

        return self::$base_path . $relative_path;
    }

    /**
     * Check if current environment is production.
     * @return bool true if production.
     */
    public static function is_production_environment(): bool
    {
        return \Fuel\Core\Fuel::$env === \Fuel\Core\Fuel::PRODUCTION;
    }

    public static function is_not_production_environment(): bool
    {
        return !self::is_production_environment();
    }

    /**
     * Check if current environment is staging.
     * @return bool true if staging.
     */
    public static function is_staging_environment(): bool
    {
        return \Fuel\Core\Fuel::$env === \Fuel\Core\Fuel::STAGING;
    }

    /**
     * Check if current environment is development.
     * @return bool true if development.
     */
    public static function is_development_environment(): bool
    {
        return \Fuel\Core\Fuel::$env === \Fuel\Core\Fuel::DEVELOPMENT;
    }

    public static function isTestEnvironment(): bool
    {
        return \Fuel\Core\Fuel::$env === \Fuel\Core\Fuel::TEST;
    }
}
