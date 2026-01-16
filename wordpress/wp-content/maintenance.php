<?php

use Services\MaintenanceService;

if(file_exists('maintenance-config.php'))
{
	include('maintenance-config.php');
}

if (!class_exists('Services\MaintenanceService')) {
    require_once realpath(__DIR__ . '/../../platform/fuel/app/classes/services/MaintenanceService.php');
}

function get_domain()
{
    return (new MaintenanceService())->getDomainFromString($_SERVER['HTTP_HOST'] ?? '');
}

$protocol = $_SERVER['SERVER_PROTOCOL'];
if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
    $protocol = 'HTTP/1.0';
}
header("$protocol 503 Service Unavailable", true, 503);
header('Content-Type: text/html; charset=utf-8');

// Turn off PageCache by default
header('cache-control: no-cache');

if(!isset($title) || empty($title)) {
    $title = _('Site under maintenance');
}

if(!isset($content) || empty($content)) {
    $content = _('Site is under unscheduled maintenance');
}

$logo_url = '/wp-content/maintenance-logo.png';
if(!empty($logo) && is_array($logo) && array_key_exists(get_domain(), $logo)) {
    $logo_url = $logo[get_domain()];
}
?>
<?php include("maintenance-view.php"); ?>
<?php die(); ?>