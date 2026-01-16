<?php

declare(strict_types=1);

namespace Tests\Unit\Services\OAuth2Server;

use PHPUnit\Framework\MockObject\MockObject;
use Services\OAuth2Server\ServerFactory;
use Services\OAuth2Server\WhiteLottoStorage;
use Test_Unit;

class ServerFactoryTest extends Test_Unit
{
    private WhiteLottoStorage|MockObject $whiteLottoStorage;
    private ServerFactory $serverFactoryUnderTest;

    public function setUp(): void
    {
        parent::setUp();

        $this->whiteLottoStorage = $this->createMock(WhiteLottoStorage::class);
        $this->serverFactoryUnderTest = new ServerFactory($this->whiteLottoStorage);
    }

    /**
     * @test
     */
    public function create_getConfigValid(): void
    {
        $actual = $this->serverFactoryUnderTest->create();

        $this->assertTrue($actual->getConfig('enforce_pkce'));
        $this->assertSame(3600, $actual->getConfig('access_lifetime'));
        $this->assertSame(1209600, $actual->getConfig('refresh_token_lifetime'));
        $this->assertFalse($actual->getConfig('allow_public_clients'));
    }
}
