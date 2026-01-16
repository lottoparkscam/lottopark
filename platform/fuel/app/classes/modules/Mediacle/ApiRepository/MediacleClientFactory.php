<?php

namespace Modules\Mediacle\ApiRepository;

use Container;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Models\Whitelabel;
use Wrappers\Decorators\ConfigContract;

/**
 * Class MediacleClientFactory
 */
class MediacleClientFactory
{
    private ConfigContract $config;

    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    public function create(): ClientInterface
    {
        /** @var Whitelabel */
        $whitelabel = Container::get('whitelabel');
        $client = new Client([
            'base_uri' => $this->config->get("mediacle.base_url.{$whitelabel->theme}"), // NOTE: assumption that theme is constant. up to this point in app lifetime it is true.
            'verify' => true,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => \Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS
        ]);

        return $client;
    }
}
