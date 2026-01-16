<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel Plugin seeder.
*/
final class Whitelabel_Plugin extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'whitelabel_plugin' => ['id', 'whitelabel_id', 'plugin', 'is_enabled', 'options']
        ];
    }

    protected function rowsStaging(): array
    {
        return [];

        // Comment below is left for reference

        /*
        return [
            'whitelabel_plugin' => [
                [1, 1, 'customer-api', 0, "{\"url\":\"https://track.customer.io/api/v1/\",\"site_id\":\"ac0d3d9cb8b187021fdd\",\"api_key\":\"60322181d7fefd1f2c54\"}"],
                [2, 1, 'mautic-api', 0, "{\"url\":\"https://m.lottopark.com\",\"user\":\"\",\"password\":\"\"}"],
            ]
        ];
        */
    }

    protected function rowsProduction(): array
    {
        return [];
    }

}