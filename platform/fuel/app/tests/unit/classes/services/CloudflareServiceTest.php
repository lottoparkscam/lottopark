<?php

namespace Tests\Unit\Classes\Services;

use Core\App;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Models\CloudflareZone;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Services\CloudflareService;
use Services\Logs\FileLoggerService;
use Test_Unit;

class CloudflareServiceTest extends Test_Unit
{
    private CloudflareService $cloudflareService;
    private Whitelabel $whitelabel;
    private Client $httpClient;
    private WhitelabelRepository $whitelabelRepository;
    private FileLoggerService $logger;
    private App $app;
    private const CLOUDFLARE_PURGE_CACHE_EXPECTED_TOKEN = 'test123';

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(Client::class);
        $this->whitelabelRepository = $this->createMock(WhitelabelRepository::class);
        $this->logger = $this->createMock(FileLoggerService::class);
        $this->app = $this->createMock(App::class);
        $this->whitelabel = $this->container->get('whitelabel');

        $_ENV[CloudflareService::CLOUDFLARE_PURGE_CACHE_TOKEN_ENV_KEY] = self::CLOUDFLARE_PURGE_CACHE_EXPECTED_TOKEN;

        $cloudflareZone = new CloudflareZone([
            'id' => 123,
            'identifier' => 'xyz'
        ]);

        $this->whitelabel->cloudflareZone = $cloudflareZone;
        $this->whitelabel->cloudflareZoneId = $cloudflareZone->id;

        $this->cloudflareService = new CloudflareService(
            $this->httpClient,
            $this->whitelabelRepository,
            $this->logger,
            $this->app
        );
    }

    /** @test */
    public function clearCacheByWhitelabel_sendCorrectRequest(): void
    {
        // Given
        $url = $this->getRequestUrl($this->whitelabel->cloudflareZone->identifier);
        $options = $this->getRequestOptions();

        // Then
        $this->whitelabelRepository->expects($this->once())
            ->method('findOneByDomain')
            ->with('lottopark.loc')
            ->willReturn($this->whitelabel);

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with($url, $options)
            ->willReturn($response);

        // When
        $response = $this->cloudflareService->clearCacheByWhitelabel('lottopark.loc');
        $this->assertTrue($response);
    }

    /** @test */
    public function clearCacheByWhitelabel_stopPuringIfEnvIsNotProduction(): void
    {
        // Then
        $this->app->expects($this->once())
            ->method('isNotProduction')
            ->willReturn(true);

        $response = $this->createMock(Response::class);
        $response->expects($this->never())
            ->method('getStatusCode');

        $this->httpClient->expects($this->never())
            ->method('post');

        // When
        $response = $this->cloudflareService->clearCacheByWhitelabel('lottopark.loc');
        $this->assertTrue($response);
    }

    /** @test */
    public function clearCacheByWhitelabel_falseResponse(): void
    {
        // Given
        $url = $this->getRequestUrl($this->whitelabel->cloudflareZone->identifier);
        $options = $this->getRequestOptions();

        // Then
        $this->whitelabelRepository->expects($this->once())
            ->method('findOneByDomain')
            ->with('lottopark.loc')
            ->willReturn($this->whitelabel);

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with($url, $options)
            ->willReturn($response);

        // When
        $response = $this->cloudflareService->clearCacheByWhitelabel('lottopark.loc');
        $this->assertFalse($response);
    }

    /** @test */
    public function clearCacheByWhitelabel_exceptionInGuzzle(): void
    {
        // Given
        $url = $this->getRequestUrl($this->whitelabel->cloudflareZone->identifier);
        $options = $this->getRequestOptions();

        // Then
        $this->whitelabelRepository->expects($this->once())
            ->method('findOneByDomain')
            ->with('lottopark.loc')
            ->willReturn($this->whitelabel);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with($url, $options)
            ->willThrowException(new Exception('Error'));

        // When
        $response = $this->cloudflareService->clearCacheByWhitelabel('lottopark.loc');
        $this->assertFalse($response);
    }

    /** @test */
    public function testGetPurgeCacheToken(): void
    {
        $actualToken = $this->cloudflareService->getPurgeCacheToken();

        $this->assertIsString($actualToken);
        $this->assertSame(self::CLOUDFLARE_PURGE_CACHE_EXPECTED_TOKEN, $actualToken);
    }

    /** @test */
    public function testGetPurgeCacheToken_envSettingNotExist(): void
    {
        unset($_ENV[CloudflareService::CLOUDFLARE_PURGE_CACHE_TOKEN_ENV_KEY]);

        $this->expectExceptionMessage(
            'CLOUDFLARE_PURGE_CACHE_TOKEN not exist in platform .env setting. 
                Ask the devops team which token you should set.'
        );

        $this->cloudflareService->getPurgeCacheToken();
    }

    /** @test */
    public function clearCacheByWhitelabel_emptyCloudflareToken(): void
    {
        unset($_ENV[CloudflareService::CLOUDFLARE_PURGE_CACHE_TOKEN_ENV_KEY]);
        $message = 'Error while sending \'purge cache\' request to Cloudflare.' .
            ' Error message: CLOUDFLARE_PURGE_CACHE_TOKEN not exist in platform .env setting. 
                Ask the devops team which token you should set.';

        // Then
        $this->whitelabelRepository->expects($this->once())
            ->method('findOneByDomain')
            ->with('lottopark.loc')
            ->willReturn($this->whitelabel);

        $this->httpClient->expects($this->never())
            ->method('post');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($message);

        // When
        $response = $this->cloudflareService->clearCacheByWhitelabel('lottopark.loc');
        $this->assertFalse($response);
    }

    /** @test */
    public function clearCacheByZoneId_emptyCloudflareToken(): void
    {
        unset($_ENV[CloudflareService::CLOUDFLARE_PURGE_CACHE_TOKEN_ENV_KEY]);
        $message = 'Error while sending \'purge cache\' request to Cloudflare.' .
            ' Error message: CLOUDFLARE_PURGE_CACHE_TOKEN not exist in platform .env setting. 
                Ask the devops team which token you should set.';

        // Then
        $this->httpClient->expects($this->never())
            ->method('post');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($message);

        // When
        $response = $this->cloudflareService->clearCacheByZoneId('asdqwe123');
        $this->assertFalse($response);
    }

    /** @test */
    public function clearCacheByZoneId_sendCorrectRequest(): void
    {
        // Given
        $url = $this->getRequestUrl('asdqw3123');
        $options = $this->getRequestOptions();

        // Then
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with($url, $options)
            ->willReturn($response);

        // When
        $response = $this->cloudflareService->clearCacheByZoneId('asdqw3123');
        $this->assertTrue($response);
    }

    /** @test */
    public function clearCacheByZoneId_stopPuringIfEnvIsNotProduction(): void
    {
        // Then
        $this->app->expects($this->once())
            ->method('isNotProduction')
            ->willReturn(true);

        $response = $this->createMock(Response::class);
        $response->expects($this->never())
            ->method('getStatusCode');

        $this->httpClient->expects($this->never())
            ->method('post');

        // When
        $response = $this->cloudflareService->clearCacheByZoneId('asd123');
        $this->assertTrue($response);
    }

    /** @test */
    public function clearCacheByZoneId_falseResponse(): void
    {
        // Given
        $url = $this->getRequestUrl('123asd2');
        $options = $this->getRequestOptions();

        // Then
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with($url, $options)
            ->willReturn($response);

        // When
        $response = $this->cloudflareService->clearCacheByZoneId('123asd2');
        $this->assertFalse($response);
    }

    /** @test */
    public function clearCacheByZoneId_exceptionInGuzzle(): void
    {
        // Given
        $url = $this->getRequestUrl('asd123as2');
        $options = $this->getRequestOptions();

        // Then
        $this->httpClient->expects($this->once())
            ->method('post')
            ->with($url, $options)
            ->willThrowException(new Exception('Error'));

        // When
        $response = $this->cloudflareService->clearCacheByZoneId('asd123as2');
        $this->assertFalse($response);
    }

    /** @test */
    public function getZoneIdsToClear(): void
    {
        $_ENV[CloudflareService::CLOUDFLARE_ZONE_IDS_ENV_KEY] = 'asd123ase231as23,asd123asd123,asd12312312as';
        $expectedArray = ['asd123ase231as23', 'asd123asd123', 'asd12312312as',];
        $actualZoneIds = $this->cloudflareService->getZoneIdsToClear();
        $this->assertIsArray($actualZoneIds);
        $this->assertSame($expectedArray, $actualZoneIds);
    }

    /** @test */
    public function getZoneIdsToClear_checkSingleId(): void
    {
        $_ENV[CloudflareService::CLOUDFLARE_ZONE_IDS_ENV_KEY] = 'asd123ase231as23';
        $expectedArray = ['asd123ase231as23',];
        $actualZoneIds = $this->cloudflareService->getZoneIdsToClear();
        $this->assertIsArray($actualZoneIds);
        $this->assertSame($expectedArray, $actualZoneIds);
    }

    /** @test */
    public function getZoneIdsToClear_zoneIdsNotExists(): void
    {
        unset($_ENV[CloudflareService::CLOUDFLARE_ZONE_IDS_ENV_KEY]);
        $actualZoneIds = $this->cloudflareService->getZoneIdsToClear();
        $this->assertIsArray($actualZoneIds);
        $this->assertSame([], $actualZoneIds);
    }

    private function getRequestUrl(string $zoneId): string
    {
        return "https://api.cloudflare.com/client/v4/zones/$zoneId/purge_cache";
    }

    private function getRequestOptions(): array
    {
        $token = self::CLOUDFLARE_PURGE_CACHE_EXPECTED_TOKEN;
        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ],
            'body' => json_encode([
                'purge_everything' => true
            ]),
        ];
    }
}
