<?php

namespace Fuel\Tasks;

use Container;
use OpenApi\Generator;
use Task_Cli;
use Wrappers\Decorators\ConfigContract;

/**
 * Scan whole code, generate api doc in .json, and save to cache/api/doc.json
 */
final class Api_Doc extends Task_Cli
{
    private ConfigContract $config;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->config = Container::get(ConfigContract::class);
    }

    public function generate(): void
    {
        ini_set('memory_limit', '256M');
        set_time_limit(90);

        $cacheFilePath = $this->config->get('cache.api_doc.path') . 'doc.json';
        $apiDocInJson = Generator::scan([APPPATH . '/classes/controller/api/'])->toJson();
        file_put_contents($cacheFilePath, $apiDocInJson);
    }
}
