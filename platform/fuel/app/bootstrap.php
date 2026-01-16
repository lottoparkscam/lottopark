<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.1
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2018 Fuel Development Team
 * @link       http://fuelphp.com
 */

use Helpers\UserHelper;

// These are requirements for WordpressInFuel
// We use there UrlHelper and casino config to maintain only one place to config casino subdomain
global $casinoConfig;
try {
    require_once(APPPATH . 'classes/helpers/UrlHelper.php');
    $casinoConfig = require_once(APPPATH . 'config/casino.php');
} catch (Throwable $exception) {
    $casinoConfig = [
        'prefixesMap' => [],
        'slugMap' => [],
        'titleMap' => []
    ];
}

require_once(APPPATH . '../../../wordpress/wp-content/plugins/lotto-platform/includes/wordpress_in_fuel.php');
$wordpress_in_fuel = new WordpressInFuel();
$wordpress_in_fuel->runBootstrap();

// Turn off PageCache by default
header('cache-control: no-cache');

if (!defined("WORDPRESS_INSIDE_FUEL")) {
    // Bootstrap the framework DO NOT edit this
    require COREPATH.'bootstrap.php';

\Autoloader::add_classes([
    // Add classes you want to override here
    // Example: 'View' => APPPATH.'classes/view.php',
    'Log' => APPPATH.'core/log.php',
    'Validation' => APPPATH.'core/validation.php',
    'Fieldset' => APPPATH.'core/fieldset.php',
    'Migrate' => APPPATH.'core/migrate.php',
]);

    // Load .env handling library
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(APPPATH . "../..");
        $dotenv->load();
    } catch (\Throwable $e) {
        echo 'Env package failed to load!'; // For more information check manually exception trace. IMPORTANT: note confidentiality on production.
        throw $e;
    }

    // Register the autoloader
    \Autoloader::register();

    /**
     * Your environment.  Can be set to any of the following:
     *
     * Fuel::DEVELOPMENT
     * Fuel::TEST
     * Fuel::STAGING
     * Fuel::PRODUCTION
     */
    \Fuel::$env = \Arr::get($_SERVER, 'FUEL_ENV', \Arr::get($_ENV, 'FUEL_ENV', getenv('FUEL_ENV') ?: \Fuel::DEVELOPMENT));

    // Initialize the framework with the config file.
    \Fuel::init('config.php');
    UserHelper::setShouldLogoutAfterBrowserClose();
}
