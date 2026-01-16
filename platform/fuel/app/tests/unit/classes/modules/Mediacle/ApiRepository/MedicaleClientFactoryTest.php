<?php

namespace Unit\Modules\Mediacle\ApiRepository;

use Fuel\Tasks\Factory\Utils\Faker;
use Models\Whitelabel;
use Modules\Mediacle\ApiRepository\MediacleClientFactory;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class MedicaleClientFactoryTest extends Test_Unit
{
    private ConfigContract $config;

    private MediacleClientFactory $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->factory = new MediacleClientFactory($this->config);
        $this->container->set('whitelabel', Whitelabel::forge(['theme' => 'doublejack']));
    }

    /** @test */
    public function create__using_config(): void
    {
        // Given
        $baseUri = Faker::forge()->url();
        $expectedVerify = true;
        $expectedHeaderKey = 'Content-Type';
        $expectedHeaderValue = 'application/json';

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('mediacle.base_url.doublejack')
            ->willReturn($baseUri);

        // When
        $client = $this->factory->create();

        // Then
        $actualBaseUri = (string)$client->getConfig('base_uri');
        $actualVerify = $client->getConfig('verify');
        $actualHeaders = $client->getConfig('headers');

        $this->assertSame($baseUri, $actualBaseUri);
        $this->assertSame($expectedVerify, $actualVerify);
        $this->assertArrayHasKey($expectedHeaderKey, $actualHeaders);
        $this->assertSame($expectedHeaderValue, $actualHeaders[$expectedHeaderKey]);
    }
}
