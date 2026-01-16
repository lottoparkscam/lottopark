<?php

$helperContent = file_get_contents(APPPATH . 'core/log.php');
$formattedContent = preg_replace("/(static function write\(\\\$level, \\\$msg, \\\$context = null\)\n\s\s\s\s{)[\s\S]*return false;\n\s\s\s\s(})/", '$1return false;$2', $helperContent);
$formattedContent = preg_replace("/(static function _init\(\)\n\s\s\s\s{)[\s\S]*}\n\s\s\s\s(})/", '$1$2', $formattedContent);
$formattedContent = preg_replace("/^<\?php/", '', $formattedContent);
eval($formattedContent);
