<?php

return [
    'emergency_emails'         => array_key_exists('EMERGENCY_EMAILS',$_ENV) ? explode(",", $_ENV['EMERGENCY_EMAILS']) : [],
    'ltech_low_balance_emails' => array_key_exists('LTECH_LOW_BALANCE_EMAILS',$_ENV) ? explode(",", $_ENV['LTECH_LOW_BALANCE_EMAILS']) : [],
    'support_errors_emails'    => array_key_exists('SUPPORT_ERRORS_EMAILS', $_ENV) ? explode(",", $_ENV['SUPPORT_ERRORS_EMAILS']) : [],
    'sale_report_emails'    => (array_key_exists('SALE_REPORT_EMAILS', $_ENV) && !empty($_ENV['SALE_REPORT_EMAILS'])) ? explode(",", $_ENV['SALE_REPORT_EMAILS']) : "",
    'domain' => $_ENV['DEFAULT_NETWORK_DOMAIN'],
];
