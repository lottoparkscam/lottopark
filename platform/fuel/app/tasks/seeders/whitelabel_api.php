<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel API ip seeder.
*/
final class Whitelabel_Api extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'whitelabel_api' => ['id', 'whitelabel_id', 'api_key', 'api_secret']
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