<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Payment_Method;

/**
 * Payment Methods seeder.
 */
final class Payment_Methods extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'whitelabel_payment_method' => ['id', 'whitelabel_id', 'payment_method_id', 'language_id', 'name', 'show', 'data', 'order', 'cost_percent', 'cost_fixed', 'cost_currency_id', 'payment_currency_id'],
            'payment_method_currency' => ['whitelabel_payment_method_id', 'currency_id', 'min_purchase', 'additional_data'],
            'whitelabel_payment_method_currency' => ['whitelabel_payment_method_id', 'currency_id', 'is_zero_decimal', 'min_purchase', 'is_default', 'is_enabled'],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'whitelabel_payment_method' => [
                [$whitelabel_payment_method_id_astro = 2, 1, Helpers_Payment_Method::ASTRO_PAY, 1, Helpers_Payment_Method::ASTRO_PAY_NAME, true, serialize([
                    'login' => 'MsmdMGvRSe',
                    'password' => 'gKZXztiZCr',
                    'secret_key' => 'DIKWfpRYwCZPPiXtxGBHOnzbkeDhrIfTJ',
                    'is_test' => '1',
                ]), 2, '0.00', '10.00', 5, 2],
                [$whitelabel_payment_method_id_epg = 3, 1, Helpers_Payment_Method::EASY_PAYMENT_GATEWAY, 1, Helpers_Payment_Method::EASY_PAYMENT_GATEWAY_NAME, true, serialize([
                    'merchant_id' => '10363',
                    'product_id' => '103630001',
                    'merchant_password' => 'c27ff7f39g51g773gb230f00c5e4deg8',
                    'top_logo_url' => 'https://lottopark.com/wp-content/themes/lottopark/images/logo.png',
                    'subtitle' => 'Subtitle test123',
                    'is_test' => '1',
                ]), 3, '0.00', '10.00', 5, 2],
            ],
            'payment_method_currency' => [
                [$whitelabel_payment_method_id_astro, 2, '1.00', null],
                [$whitelabel_payment_method_id_epg, 2, '1.00', null],
            ],
            'whitelabel_payment_method_currency' => [
                [$whitelabel_payment_method_id_astro, 2, 0, '1.00', 1, 1],
                [$whitelabel_payment_method_id_epg, 2, 0, '1.00', 1, 1],
            ],
        ];
    }

    protected function rowsProduction(): array
    {
        return [];
    }
}
