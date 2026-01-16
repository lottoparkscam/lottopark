<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel API ip seeder.
*/
final class Whitelabel_Api_Ip extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'whitelabel_api_ip' => ['id', 'whitelabel_id', 'ip']
        ];
    }

    protected function rowsStaging(): array
    {
        return [];
    }

    protected function rowsProduction(): array
    {
        return [];
    }

}