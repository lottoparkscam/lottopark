<?php

namespace Unit\Modules\Mediacle\ApiRepository;

use Container;
use Models\Whitelabel;
use Modules\Mediacle\ApiRepository\MediacleClientFactory;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class MediacleConfigTest extends Test_Unit
{
    private ConfigContract $config;

    private MediacleClientFactory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = Container::get(ConfigContract::class);
        $this->factory = new MediacleClientFactory($this->config);
        $this->container->set('whitelabel', Whitelabel::forge(['theme' => 'doublejack']));
    }

    /** @test */
    public function create__using_config_doublejack(): void
    {
        // Given
        $expectedVerify = true;
        $expectedHeaderKey = 'Content-Type';
        $expectedHeaderValue = 'application/json';

        // When
        $client = $this->factory->create();

        // Then
        $actualBaseUri = (string)$client->getConfig('base_uri');
        $actualVerify = $client->getConfig('verify');
        $actualHeaders = $client->getConfig('headers');

        $this->assertSame('https://affiliates.doublejack.club/', $actualBaseUri);
        $this->assertSame($expectedVerify, $actualVerify);
        $this->assertArrayHasKey($expectedHeaderKey, $actualHeaders);
        $this->assertSame($expectedHeaderValue, $actualHeaders[$expectedHeaderKey]);
    }

    /** @test */
    public function create__using_config_megajackpot(): void
    {
        $this->container->set('whitelabel', Whitelabel::forge(['theme' => 'megajackpot']));
        // Given
        $expectedVerify = true;
        $expectedHeaderKey = 'Content-Type';
        $expectedHeaderValue = 'application/json';

        // When
        $client = $this->factory->create();

        // Then
        $actualBaseUri = (string)$client->getConfig('base_uri');
        $actualVerify = $client->getConfig('verify');
        $actualHeaders = $client->getConfig('headers');

        $this->assertSame('https://partners.mega-jackpot.club/', $actualBaseUri);
        $this->assertSame($expectedVerify, $actualVerify);
        $this->assertArrayHasKey($expectedHeaderKey, $actualHeaders);
        $this->assertSame($expectedHeaderValue, $actualHeaders[$expectedHeaderKey]);
    }

    /** @test */
    public function create__using_config_lottopark(): void
    {
        $this->container->set('whitelabel', Whitelabel::forge(['theme' => 'lottopark'])); // check what happens when there is no config entry
        // Given
        $expectedVerify = true;
        $expectedHeaderKey = 'Content-Type';
        $expectedHeaderValue = 'application/json';

        // When
        $client = $this->factory->create();

        // Then
        $actualBaseUri = (string)$client->getConfig('base_uri');
        $actualVerify = $client->getConfig('verify');
        $actualHeaders = $client->getConfig('headers');

        $this->assertSame('', $actualBaseUri); // We will have broken base uri
        $this->assertSame($expectedVerify, $actualVerify);
        $this->assertArrayHasKey($expectedHeaderKey, $actualHeaders);
        $this->assertSame($expectedHeaderValue, $actualHeaders[$expectedHeaderKey]);
    }
}
