<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Services\SocialMediaConnect\SessionService;
use Test_Unit;

/** This session test should work like platform/fuel/vendor/hybridauth/hybridauth/tests/Storage/SessionTest.php */
class SessionServiceTest extends Test_Unit
{
    private SessionService $sessionServiceUnderTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->sessionServiceUnderTest = new SessionService();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->sessionServiceUnderTest->clear();
    }

    /**
     * @test
     * @dataProvider sessionData
     */
    public function setAndGet(string|int $key, mixed $value): void
    {
        $this->sessionServiceUnderTest->set($key, $value);

        $result = $this->sessionServiceUnderTest->get($key);

        $this->assertEquals($value, $result);
    }

    /**
     * @test
     * @dataProvider sessionData
     */
    public function delete(string|int $key, mixed $value): void
    {
        $this->sessionServiceUnderTest->set($key, $value);

        $this->sessionServiceUnderTest->delete($key);

        $data = $this->sessionServiceUnderTest->get($key);

        $this->assertNull($data);
    }

    /**
     * @test
     * @dataProvider sessionData
     */
    public function clear(string|int $key, mixed $value): void
    {
        $this->sessionServiceUnderTest->set($key, $value);

        $this->sessionServiceUnderTest->clear();

        $data = $this->sessionServiceUnderTest->get($key);

        $this->assertNull($data);
    }

    /**
     * @test
     */
    public function clear_dataBulk(): void
    {
        foreach ($this->sessionData() as $key => $value) {
            $this->sessionServiceUnderTest->set($key, $value);
        }

        $this->sessionServiceUnderTest->clear();

        foreach ($this->sessionData() as $key => $value) {
            $data = $this->sessionServiceUnderTest->get($key);

            $this->assertNull($data);
        }
    }

    /**
     * @test
     * @dataProvider sessionData
     */
    public function deleteMatch(string|int $key, mixed $value): void
    {
        $this->sessionServiceUnderTest->set($key, $value);

        $this->sessionServiceUnderTest->deleteMatch('provider.token.');

        $data = $this->sessionServiceUnderTest->get('provider.token.request_token');

        $this->assertNull($data);
    }

    public function sessionData(): array
    {
        return [
            ['foo', 'bar'],
            [1234, 'bar'],
            ['foo', 1234],

            ['Bonjour', '안녕하세요'],
            ['ஹலோ', 'Γεια σας'],

            ['array', [1, 2, 3]],
            ['string', json_encode($this)],
            ['object', $this],

            ['provider.token.request_token', '9DYPEJ&qhvhP3eJ!'],
            ['provider.token.oauth_token', '80359084-clg1DEtxQF3wstTcyUdHF3wsdHM'],
            ['provider.token.oauth_token_secret', 'qiHTi1znz6qiH3tTcyUdHnz6qiH3tTcyUdH3xW3wsDvV08e'],
        ];
    }
}
