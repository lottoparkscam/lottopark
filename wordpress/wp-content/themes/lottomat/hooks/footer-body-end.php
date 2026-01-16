<?php

use Helpers\Wordpress\LanguageHelper;

$language = LanguageHelper::getCurrentWhitelabelLanguage();

$wheel_codes = array(
    'en' => 'nbXtiauSB4gyLEK3m',
    'pt' => '72TvcnPXFApqiBwLi',
    'es' => 'k2DBawLhAo3TtjJ8K',
    'hr' => 'a6Er5ciqLeqp9QYfD'
);

$language_code = explode('_', $language['code']);
$language_code = $language_code[0];
if (!isset($wheel_codes[$language_code]))
{
    $language_code = 'en';
}
echo '<script id="wheelscript" src="https://app.wheelysales.com/wheel/" type="text/javascript" wheelHex="'.$wheel_codes[$language_code].'" defer></script>';