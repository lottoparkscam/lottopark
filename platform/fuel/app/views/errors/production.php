<?php
function get_domain()
{
    $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
    $domain = explode('.', $domain);
    if (!empty($domain) && count($domain) > 2) {
        $domain = $domain[count($domain) - 2] . '.' . $domain[count($domain) - 1];
    } else {
        $domain = implode('.', $domain);
    }
    return $domain;
}

$config = APPPATH."../../../wordpress/maintenance-config.php";
if(file_exists($config))
{
	include($config);
}

$domain = get_domain();

$title = "Oh! Something's wrong!";

$logo_url = 'https://'.$domain.'/wp-content/maintenance-logo.png';
if(!empty($logo) && is_array($logo) && array_key_exists($domain, $logo)) {
    $logo_url = 'https://'.$domain.$logo[$domain];
}

$content = "Our development team has already been informed about the issue. We are sorry for any inconvenience. Please check back later!";

?>
<?php include(APPPATH."../../../wordpress/wp-content/maintenance-view.php"); ?>