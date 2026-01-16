<?php
/*
Plugin Name: Lotto Platform
Description: The best lotto platform ever made.
Version: 1.0
Author: Tomasz Kłapsia
Text Domain: lotto-platform
Network: True
*/

// If this file is called directly, abort.
use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

define('LOTTO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LOTTO_PLUGIN_DIR', plugin_dir_path(__FILE__));

require LOTTO_PLUGIN_DIR.'includes/fuel.php';

/*if(\Fuel::$env == \Fuel::STAGING)
{
	// remove access
	if(!preg_match('#^(/order/confirm)#', $_SERVER['REQUEST_URI']))
	{
		if(!in_array(Lotto_Security::getIP(), array('116.203.79.154', '51.77.244.72', 2001:41d0:401:3100::54cf', '::1')))
		{
			http_response_code(403);
			exit();
		}
	}
}*/
require LOTTO_PLUGIN_DIR.'includes/template-tags.php';

function lotto_platform_autoloader($class)
{
    if (substr($class, 0, 6) == 'Lotto_') {
        include 'includes/' . str_replace('_', '/', $class) . '.php';
    }
}
spl_autoload_register('lotto_platform_autoloader');

function lotto_platform_run()
{
    if (!defined('IS_CASINO')) {
        try {
            define('IS_CASINO', UrlHelper::isCasino());
        } catch (Throwable $exception) {
            define('IS_CASINO', false);
        }
    }

    $lotto = new Lotto_Platform();
    $lotto->run();
}

lotto_platform_run();
