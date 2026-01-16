<?php

namespace Tests\Unit\Classes\Services\Plugin;

use Container;
use Exception;
use Fuel\Core\Fuel;
use GuzzleHttp\Client;
use Models\Whitelabel;
use Models\WhitelabelPlugin;
use Psr\Http\Message\ResponseInterface;
use Repositories\WhitelabelPluginLogRepository;
use Repositories\WhitelabelPluginRepository;
use Repositories\WhitelabelPluginUserRepository;
use Services\Logs\FileLoggerService;
use Services\Plugin\RegisterService;
use Test_Unit;
use Tests\Fixtures\WhitelabelFixture;
use Validators\ClickIdValidator;

class RegisterServiceTest extends Test_Unit
{
    private RegisterService $registerService;
    private Client $client;
    private ResponseInterface $response;
    private ClickIdValidator $clickIdValidator;
    public FileLoggerService $fileLoggerService;
    public WhitelabelFixture $whitelabelFixture;
    public WhitelabelPluginLogRepository $whitelabelPluginLogRepository;
    public WhitelabelPluginRepository $whitelabelPluginRepository;
    private WhitelabelPluginUserRepository $whitelabelPluginUserRepository;
    private WhitelabelPlugin $whitelabelPluginStub;

    public const TRAFFIC_BAR_TOKEN = '537861c913';
    public const PRIMEADS_TOKEN = '919b3b1e2c';
    public const INCORRECT_TOKEN = '939b2b1e2c';
    public const CLICK_NAME = 'clickID';
    public const REF_NAME = 'ref';

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->client = $this->createMock(Client::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->clickIdValidator = $this->createMock(ClickIdValidator::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->whitelabelPluginLogRepository = $this->createMock(WhitelabelPluginLogRepository::class);
        $this->whitelabelPluginUserRepository = $this->createMock(WhitelabelPluginUserRepository::class);
        $this->whitelabelPluginRepository = $this->createMock(WhitelabelPluginRepository::class);
        $this->whitelabelPluginStub = new WhitelabelPlugin();
        $this->registerService = new RegisterService(
            $this->client,
            $this->clickIdValidator,
            $this->fileLoggerService,
            $this->whitelabelPluginLogRepository,
            $this->whitelabelPluginRepository,
            $this->whitelabelPluginUserRepository,
        );

        $this->whitelabelPluginStub->is_enabled = true;
        $this->whitelabelPluginStub->options = '{"secureUrlParameter":"asdqwe"}';
    }

    /** @test */
    public function sendToTrafficBarClickId_isTrafficBar(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = 'asd123';

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=reg&id=$userClickId&payout=0",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_isTrafficBar_devEnv(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        Fuel::$env = Fuel::DEVELOPMENT;

        $this->clickIdValidator
            ->expects($this->never())
            ->method('setCustomInput');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('isValid');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('getProperty');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_isNotTrafficBar(): void
    {
        $_COOKIE[self::REF_NAME] = self::INCORRECT_TOKEN;

        $this->clickIdValidator
            ->expects($this->never())
            ->method('setCustomInput');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('isValid');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('getProperty');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_withCorrectRefTokenAndCorrectStatus(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=reg&id=$userClickId&payout=0",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_withCorrectRefTokenAndinCorrectStatus(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=reg&id=$userClickId&payout=0",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_requestThrowException(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=reg&id=$userClickId&payout=0",
                ['timeout' => 5]
            )
            ->willThrowException(new Exception());

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_getStatusCodeThrowException(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=reg&id=$userClickId&payout=0",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willThrowException(new Exception());

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_cookieIsNull(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = null;

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->clickIdValidator
            ->expects($this->never())
            ->method('getProperty');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickId_clickIdCookieIsInCorrect(): void
    {
        $_COOKIE[self::REF_NAME] = self::TRAFFIC_BAR_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = 'asdqwe98^sas%';

        $this->clickIdValidator
            ->expects($this->once())
            ->method('setCustomInput')
            ->with(['clickID' => $userClickId]);

        $this->clickIdValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->clickIdValidator
            ->expects($this->never())
            ->method('getProperty');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToTrafficBarClickId();
        $this->assertFalse($result);
    }

    /** @test */
    public function generatePrimeadsRegisterUrl_urlContainsSecureParameter(): void
    {
        $result = $this->registerService->generatePrimeadsRegisterUrl('1sd0as', '1234');
        $this->assertTrue(str_contains($result, 'secure'));
    }

    /** @test */
    public function generatePrimeadsRegisterUrl_urlIsCorrect(): void
    {
        $result = $this->registerService->generatePrimeadsRegisterUrl('1sd0as', '1234');
        $this->assertTrue($result === "https://pb.primeads.io/postback?clickid=1sd0as&status=2&goal=reg&secure=1234");
    }

    /** @test */
    public function sendToPrimeadsClickId(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = 'asd123';
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        Fuel::$env = Fuel::PRODUCTION;
        $userId = 1;

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($this->whitelabelPluginStub);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($userId)
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->generatePrimeadsRegisterUrl($userClickId, $this->whitelabelPluginStub->findPrimeadsSecureUrlParameter()),
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_secureParameterNotExists(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = 'asd123';
        $this->whitelabelPluginStub->options = null;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        Fuel::$env = Fuel::PRODUCTION;
        $userId = 1;
        $url = $this->generatePrimeadsRegisterUrl($userClickId, '');

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($this->whitelabelPluginStub);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($userId)
            ->willReturn($userClickId);

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error');

        $message = json_encode([
            'message' => 'Unsuccessful request to primeads, response: Secure parameter in postback to primeads is required. Check database whitelabel_plugin.options does it contain json with "secureUrlParameter".',
            'url' => $url,
        ]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_DevEnv(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        Fuel::$env = Fuel::DEVELOPMENT;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = true;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_WithIncorrectToken(): void
    {
        $_COOKIE[self::REF_NAME] = self::INCORRECT_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = true;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_ReturnIncorrectStatus(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;
        $url = $this->generatePrimeadsRegisterUrl($userClickId, $this->whitelabelPluginStub->findPrimeadsSecureUrlParameter());

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($this->whitelabelPluginStub);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($userId)
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $url,
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $message = json_encode(['message' => "Unsuccessful request to primeads with status 400, response: ", 'url' => $url]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsBarClickId_RequestThrowException(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;
        $url = $this->generatePrimeadsRegisterUrl($userClickId, $this->whitelabelPluginStub->findPrimeadsSecureUrlParameter());
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($this->whitelabelPluginStub);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($userId)
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $url,
                ['timeout' => 5]
            )
            ->willThrowException(new Exception());

        $message = json_encode(['message' => "Unsuccessful request to primeads, response: ", 'url' => $url]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_getStatusCodeThrowException(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '10';
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;
        $url = $this->generatePrimeadsRegisterUrl($userClickId, $this->whitelabelPluginStub->findPrimeadsSecureUrlParameter());

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = true;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($this->whitelabelPluginStub);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($userId)
            ->willReturn($userClickId);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', $url, ['timeout' => 5])
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willThrowException(new Exception());

        $message = json_encode(['message' => "Unsuccessful request to primeads, response: ", 'url' => $url]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);
        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_clickIdNotExists(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        $userClickId = $_COOKIE[self::CLICK_NAME] = '';
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = true;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($userId)
            ->willReturn($userClickId);

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_pluginDisabled(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        Fuel::$env = Fuel::PRODUCTION;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $userId = 1;

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = false;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('setCustomInput');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('isValid');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('getProperty');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_isNotLottopark(): void
    {
        $_COOKIE[self::REF_NAME] = self::PRIMEADS_TOKEN;
        Fuel::$env = Fuel::PRODUCTION;
        Container::get('whitelabel')->theme = Whitelabel::REDFOXLOTTO_THEME;
        $userId = 1;

        $this->whitelabelPluginRepository
            ->expects($this->never())
            ->method('findPluginByNameAndWhitelabelId');

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('setCustomInput');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('isValid');

        $this->clickIdValidator
            ->expects($this->never())
            ->method('getProperty');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickId_PluginNotExists(): void
    {
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        Fuel::$env = Fuel::PRODUCTION;
        $userId = 1;

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn(null);

        $this->client
            ->expects($this->never())
            ->method('request');

        $result = $this->registerService->sendToPrimeadsClickId($userId);
        $this->assertFalse($result);
    }

    private function generatePrimeadsRegisterUrl(string $userClickId, string $secureParameter): string
    {
        return "https://pb.primeads.io/postback?clickid={$userClickId}&status=2&goal=reg&secure={$secureParameter}";
    }
}
