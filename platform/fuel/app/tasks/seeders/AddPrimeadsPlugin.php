<?php

namespace Fuel\Tasks\Seeders;

use Container;
use Models\{
    WhitelabelPlugin,
    Whitelabel
    };
use Repositories\WhitelabelRepository;

class AddPrimeadsPlugin extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            WhitelabelPlugin::get_table_name() => ['whitelabel_id', 'plugin', 'is_enabled', 'options']
        ];
    }

    protected function rowsStaging(): array
    {
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->findOneByTheme(Whitelabel::LOTTOPARK_THEME);
        return [
            WhitelabelPlugin::get_table_name() => [
                [$whitelabel->id, WhitelabelPlugin::PRIMEADS_NAME, true, '{"secureUrlParameter":"as123adsf2352fswf23452fsdafa321"}'],
            ]
        ];
    }
}
