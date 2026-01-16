<?php

namespace Tests\Unit\Classes\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Helpers_Ltech;
use Psr\Http\Message\StreamInterface;
use Services\CacheService;
use Services\Logs\FileLoggerService;
use Services\LtechService;
use Test_Unit;
use Wrappers\Db;

class LtechServiceTest extends Test_Unit
{
    private LtechService $ltechService;
    private Client $httpClient;
    private Response $httpResponse;
    private FileLoggerService $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(Client::class);
        $helpersLtech = $this->createMock(Helpers_Ltech::class);
        $this->httpResponse = $this->createMock(Response::class);
        $this->logger = $this->createMock(FileLoggerService::class);
        $db = $this->createMock(Db::class);
        $cacheService = $this->container->get(CacheService::class);
        $this->ltechService = new LtechService($this->httpClient, $helpersLtech, $this->logger, $cacheService, $db);
    }

    /** @test */
    public function getCurrentBalances_withWrongStatusCode(): void
    {
        // Given
        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->httpResponse);
        $this->httpResponse->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(201);

        // When
        $currentBalances = $this->ltechService->getCurrentBalances();

        // Then
        $this->assertEmpty($currentBalances);
    }

    /** @test */
    public function getCurrentBalances_withWrongResponse(): void
    {
        // Given
        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->httpResponse);
        $this->httpResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $this->httpResponse->expects($this->once())
            ->method('getBody')
            ->willThrowException(new Exception('Wrong request'));

        // When
        $currentBalances = $this->ltechService->getCurrentBalances();

        // Then
        $this->assertEmpty($currentBalances);
    }

    /** @test */
    public function getCurrentBalances_withCorrectResponse(): void
    {
        // Given
        $content = [
            'accounts' => [
                [
                    'name' => 'Tickets (ABC)',
                    'currency' => 'ABC',
                    'balance' => 10
                ],
                [
                    'name' => 'Fees (DEF)',
                    'currency' => 'DEF',
                    'balance' => 100
                ],
                [
                    'name' => 'Tickets (EUR)',
                    'currency' => 'EUR',
                    'balance' => 200
                ],
            ]
        ];
        $body = $this->createMock(StreamInterface::class);
        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->httpResponse);
        $this->httpResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $this->httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($content));

        // When
        $currentBalances = $this->ltechService->getCurrentBalances();

        // Then
        $this->assertArrayHasKey('ABC', $currentBalances);
        $this->assertArrayHasKey('EUR', $currentBalances);
        $this->assertArrayNotHasKey('DEF', $currentBalances);
        $this->assertSame(10.0, $currentBalances['ABC']);
        $this->assertSame(200.0, $currentBalances['EUR']);
    }

    /** @test */
    public function getCurrenciesWithEnoughBalance(): void
    {
        // Given
        $content = [
            'accounts' => [
                [
                    'name' => 'Fees (AAA)',
                    'currency' => 'AAA',
                    'balance' => 600
                ],
                [
                    'name' => 'Tickets (BBB)',
                    'currency' => 'BBB',
                    'balance' => 4.99
                ],
                [
                    'name' => 'Tickets (CCC)',
                    'currency' => 'CCC',
                    'balance' => 5
                ],
                [
                    'name' => 'Tickets (DDD)',
                    'currency' => 'DDD',
                    'balance' => 5.1
                ],
            ]
        ];
        $body = $this->createMock(StreamInterface::class);
        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->httpResponse);
        $this->httpResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $this->httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($body);
        $body->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($content));

        // When
        $currentBalances = $this->ltechService->getCurrenciesWithEnoughBalance();

        // Then
        $this->assertContains('CCC', $currentBalances);
        $this->assertContains('DDD', $currentBalances);
        $this->assertNotContains('AAA', $currentBalances);
        $this->assertNotContains('BBB', $currentBalances);
    }

    /** @test */
    public function getCurrenciesWithEnoughBalance_wrongResponse(): void
    {
        // Expect
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Ltech - Fetching balances error');

        // Given
        $this->httpClient->expects($this->once())
            ->method('get')
            ->willReturn($this->httpResponse);
        $this->httpResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $this->httpResponse->expects($this->once())
            ->method('getBody')
            ->willThrowException(new Exception('Wrong request'));

        // When
        $this->ltechService->getCurrenciesWithEnoughBalance();
    }
}
