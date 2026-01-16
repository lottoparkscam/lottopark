<?php

namespace unit\classes\services\LotteryProvider\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use Services\LotteryProvider\Api\TheLotterApiClient;
use Test_Unit;

class TheLotterApiClientTest extends Test_Unit
{
    private TheLotterApiClient $apiClient;

    public function setUp(): void
    {
        $this->apiClient = new TheLotterApiClient();
    }

    /** @test */
    public function buildApiUrlWithPlaceholders(): void
    {
        $path = 'lottery/{lotteryId}/draws/isavailable';
        $placeholders = ['{lotteryId}' => 25];
        $expectedUrl = 'https://api.global-services-pro.com/api/v1/lottery/25/draws/isavailable';

        $reflection = new ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('buildApiUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->apiClient, [$path, $placeholders]);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @test */
    public function buildApiUrlWithoutPlaceholders(): void
    {
        $path = 'tickets/purchase';
        $expectedUrl = 'https://api.global-services-pro.com/api/v1/tickets/purchase';

        $reflection = new ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('buildApiUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->apiClient, [$path]);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @test */
    public function drawAvailableForPurchaseSuccess(): void
    {
        $lotteryId = 25;
        $drawDate = '2023-09-30';

        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->method('post')
            ->willReturn(new Response(200, [], '{"is_available": "True"}'));

        $this->apiClient->setHttpClient($httpClientMock);

        $result = $this->apiClient->isDrawAvailable($lotteryId, $drawDate);

        $this->assertTrue($result);
    }

    /** @test */
    public function drawAvailableForPurchaseFailure(): void
    {
        $lotteryId = 25;
        $drawDate = '2099-09-30';

        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->method('post')
            ->willReturn(new Response(200, [], '{"is_available": "False"}'));

        $this->apiClient->setHttpClient($httpClientMock);

        $result = $this->apiClient->isDrawAvailable($lotteryId, $drawDate);

        $this->assertFalse($result);
    }

    /** @test */
    public function isDrawAvailableHandlesError(): void
    {
        $lotteryId = 25;
        $drawDate = '2099-09-30';

        $httpClientMock = $this->createMock(Client::class);
        $httpClientMock->method('post')
            ->willReturn(new Response(500, [], ''));

        $this->apiClient->setHttpClient($httpClientMock);

        $result = $this->apiClient->isDrawAvailable($lotteryId, $drawDate);

        $this->assertFalse($result);
    }
}
