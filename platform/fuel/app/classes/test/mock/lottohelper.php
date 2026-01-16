<?php

$helperContent = file_get_contents(APPPATH . 'classes/lotto/helper.php');
$formattedHelperContent = preg_replace("/(function save_formatted_log_message[\s\S]*void {)[\s\S]*(} \/\/end save_formatted_log_message)/", '$1$2', $helperContent);
$formattedHelperContent = preg_replace("/^<\?php/", '', $formattedHelperContent);
eval($formattedHelperContent);
