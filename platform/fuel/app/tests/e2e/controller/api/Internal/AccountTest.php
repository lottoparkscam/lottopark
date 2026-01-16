<?php

namespace Tests\E2e\Controller\Api\Internal;

use Container;
use Repositories\WhitelabelReferStatisticsRepository;
use Test_E2e_Controller_Api;

class AccountTest extends Test_E2e_Controller_Api
{
    protected $in_transaction = false;
    private WhitelabelReferStatisticsRepository $whitelabelReferStatisticsRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelReferStatisticsRepository = Container::get(WhitelabelReferStatisticsRepository::class);
    }

    /** @test */
    public function getDetails_whenUserNotExists(): void
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/account/details'
        );

        $this->assertSame(404, $response['status']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getDetails_whenUserExists(): void
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/account/details',
            $this->prepareAndGetLoggedUserHeaders()
        );

        $this->assertSame(200, $response['status']);

        $body = $response['body'];
        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('balance', $body);
        $this->assertArrayHasKey('bonusBalance', $body);
        $this->assertArrayHasKey('casinoBalance', $body);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function isUserLogged_whenUserExists(): void
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/account/isUserLogged',
            $this->prepareAndGetLoggedUserHeaders()
        );

        $this->assertSame(200, $response['status']);
        $this->assertTrue($response['body']['isUserLogged']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function isUserLogged_whenUserNotExists(): void
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/account/isUserLogged'
        );

        $this->assertSame(200, $response['status']);
        $this->assertFalse($response['body']['isUserLogged']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function isUserLogged_whenUserExists_withWrongUref(): void
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/account/isUserLogged?uref=123',
            $this->prepareAndGetLoggedUserHeaders()
        );

        $cookies = implode(array_values($this->responseHeaders['Set-Cookie']));

        $this->assertStringNotContainsString('uref', $cookies);
        $this->assertSame(200, $response['status']);
        $this->assertTrue($response['body']['isUserLogged']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function isUserLogged_whenUserExists_withCorrectUref(): void
    {
        $this->whitelabelUser->token = '123456789';
        $this->whitelabelUser->save();
        $uref = 'LPU' . $this->whitelabelUser->token;
        $response = $this->getResponse(
            'GET',
            "/api/internal/account/isUserLogged?uref=$uref",
            $this->prepareAndGetLoggedUserHeaders()
        );

        $cookies = implode(array_values($this->responseHeaders['Set-Cookie']));

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString("uref=$uref", $cookies);

        $whitelabelReferStatistics = $this->whitelabelReferStatisticsRepository->findOneByWhitelabelAndUser(
            $this->whitelabel->id,
            $this->whitelabelUser->id,
            $this->whitelabelUser->token
        );

        $clicks = $whitelabelReferStatistics->clicks;
        $uniqueClicks = $whitelabelReferStatistics->uniqueClicks;


        $this->getResponse(
            'GET',
            "/api/internal/account/isUserLogged?uref=$uref",
            $this->prepareAndGetLoggedUserHeaders()
        );

        $whitelabelReferStatistics->reload();

        $this->assertSame($clicks + 1, $whitelabelReferStatistics->clicks);
        $this->assertSame($uniqueClicks + 1, $whitelabelReferStatistics->uniqueClicks);
    }
}