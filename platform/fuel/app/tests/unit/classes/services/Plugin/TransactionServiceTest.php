<?php

namespace Tests\Unit\Classes\Services\Plugin;

use Container;
use Exception;
use Fuel\Core\Fuel;
use GuzzleHttp\Client;
use Models\Whitelabel;
use Models\WhitelabelAff;
use Models\WhitelabelPlugin;
use Models\WhitelabelPluginUser;
use Models\WhitelabelUserAff;
use Psr\Http\Message\ResponseInterface;
use Repositories\Aff\WhitelabelAffCommissionRepository;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\WhitelabelPluginLogRepository;
use Repositories\WhitelabelPluginRepository;
use Repositories\WhitelabelPluginUserRepository;
use Services\Logs\FileLoggerService;
use Services\Plugin\TransactionService;
use Test_Unit;
use Tests\Fixtures\WhitelabelUserAffFixture;
use Tests\Fixtures\WhitelabelUserFixture;

class TransactionServiceTest extends Test_Unit
{
    private TransactionService $transactionService;
    private WhitelabelAffCommissionRepository $whitelabelAffCommissionRepository;
    private Client $client;
    private ResponseInterface $response;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelAff $whitelabelAff;
    private WhitelabelUserAff $whitelabelUserAff;
    private FileLoggerService $fileLoggerService;
    private WhitelabelPluginUserRepository $whitelabelPluginUserRepository;
    private WhitelabelPluginRepository $whitelabelPluginRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;
    public const TRAFFIC_BAR_TOKEN = '537861c913';
    public const PRIMEADS_TOKEN = '919b3b1e2c';

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelAffCommissionRepository = $this->createMock(WhitelabelAffCommissionRepository::class);
        $this->client = $this->createMock(Client::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelUserAffFixture = $this->container->get(WhitelabelUserAffFixture::class);
        $this->whitelabelPluginUserRepository = $this->createMock(WhitelabelPluginUserRepository::class);
        $this->whitelabelPluginLogRepository = $this->createMock(WhitelabelPluginLogRepository::class);
        $this->whitelabelPluginRepository = $this->createMock(WhitelabelPluginRepository::class);
        $this->whitelabelAffRepository = $this->createMock(WhitelabelAffRepository::class);
        $this->whitelabelAff = new WhitelabelAff();
        $this->whitelabelUserAff = new WhitelabelUserAff();
        $this->transactionService = new TransactionService(
            $this->client,
            $this->whitelabelAffCommissionRepository,
            $this->fileLoggerService,
            $this->whitelabelPluginUserRepository,
            $this->whitelabelPluginLogRepository,
            $this->whitelabelPluginRepository,
            $this->whitelabelAffRepository,
        );
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_userIsFromTrafficBar()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        $whitelabelUserAffFixture = $this->whitelabelUserAffFixture->with('basic')->makeOne();

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $userClickId = $whitelabelUserAffFixture->externalId;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=sale&id=$userClickId&payout=$commission",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_userIsNotFromTrafficBar()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = 'asd';

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');


        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_devEnv()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        Fuel::$env = Fuel::DEVELOPMENT;

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');


        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_correctStatus()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        $whitelabelUserAffFixture = $this->whitelabelUserAffFixture->with('basic')->makeOne();

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $userClickId = $whitelabelUserAffFixture->external_id;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=sale&id=$userClickId&payout=$commission",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_inCorrectStatus()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        $whitelabelUserAffFixture = $this->whitelabelUserAffFixture->with('basic')->makeOne();

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $userClickId = $whitelabelUserAffFixture->external_id;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=sale&id=$userClickId&payout=$commission",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_getLastUserTransactionCommissionThrowError()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willThrowException(new Exception());

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_requestThrowException()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        $whitelabelUserAffFixture = $this->whitelabelUserAffFixture->with('basic')->makeOne();

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $userClickId = $whitelabelUserAffFixture->external_id;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=sale&id=$userClickId&payout=$commission",
                ['timeout' => 5]
            )
            ->willReturn($this->response)
            ->willThrowException(new Exception());


        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_getStatusCodeThrowException()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        $whitelabelUserAffFixture = $this->whitelabelUserAffFixture->with('basic')->makeOne();

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $userClickId = $whitelabelUserAffFixture->external_id;

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=sale&id=$userClickId&payout=$commission",
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willThrowException(new Exception());

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToTrafficBarClickIdAndCommission_whitelabelUserIsNotFromAff()
    {
        $user = $this->whitelabelUserFixture->with('basic')->makeOne();
        $user->whitelabel_user_aff =  null;

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendToTrafficBarClickIdAndCommission($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function generatePrimeadsTransactionUrl_urlContainsSecureParameter(): void
    {
        $result = $this->transactionService->generatePrimeadsTransactionUrl('1sd0as', '1234', 'asdqwe');
        $this->assertTrue(str_contains($result, 'secure'));
    }

    /** @test */
    public function generatePrimeadsTransactionUrl_urlIsCorrect(): void
    {
        $result = $this->transactionService->generatePrimeadsTransactionUrl('1sd0as', '1234', 'asdqwe');
        $this->assertTrue($result === "https://s.primeads.io/postback?clickid=1sd0as&status=2&goal=rs&sum=1234&secure=asdqwe");
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_userIsFromPrimeads()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->id = 1;
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPlugin->options = '{"secureUrlParameter":"asdqwe"}';
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->generatePrimeadsTransactionUrl($userClickId, $commission, $whitelabelPlugin->findPrimeadsSecureUrlParameter()),
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_secureParameterNoTExists()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->id = 1;
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error');

        $url = $this->generatePrimeadsTransactionUrl($userClickId, $commission, '');
        $message = json_encode([
            'message' => 'Unsuccessful request to primeads, response: Secure parameter in postback to primeads is required. Check database whitelabel_plugin.options does it contain json with "secureUrlParameter".',
            'url' => $url
        ]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_devEnv()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Fuel::$env = Fuel::DEVELOPMENT;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = true;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserAffTokenByWhitelabelUserId');

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_userIsNotFromPrimeads()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = 'asd';
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'traffic';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::TRAFFIC_BAR_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserAffTokenByWhitelabelUserId');


        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_correctStatus()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        $user->id = 1;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPlugin->options = '{"secureUrlParameter":"asdqwe"}';
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->generatePrimeadsTransactionUrl($userClickId, $commission, $whitelabelPlugin->findPrimeadsSecureUrlParameter()),
                ['timeout' => 5]
            )
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertTrue($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_inCorrectStatus()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        $user->id = 1;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPlugin->options = '{"secureUrlParameter":"asdqwe"}';
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $url = $this->generatePrimeadsTransactionUrl($userClickId, $commission, $whitelabelPlugin->findPrimeadsSecureUrlParameter());
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

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_getLastUserTransactionCommissionThrowError()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        $user->id = 1;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willThrowException(new Exception());

        $message = json_encode(['message' => "Unsuccessful request to primeads, response: ", 'url' => '']);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_requestThrowException()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        $user->id = 1;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPlugin->options = '{"secureUrlParameter":"asdqwe"}';
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $url = $this->generatePrimeadsTransactionUrl($userClickId, $commission, $whitelabelPlugin->findPrimeadsSecureUrlParameter());
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $url,
                ['timeout' => 5]
            )
            ->willReturn($this->response)
            ->willThrowException(new Exception());

        $message = json_encode(['message' => "Unsuccessful request to primeads, response: ", 'url' => $url]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_getStatusCodeThrowException()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        $user->id = 1;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPlugin->options = '{"secureUrlParameter":"asdqwe"}';
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);


        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn($userClickId);

        $this->whitelabelAffCommissionRepository
            ->expects($this->once())
            ->method('getLastUserTransactionCommission')
            ->willReturn($commission = 0.25);

        $url = $this->generatePrimeadsTransactionUrl($userClickId, $commission, $whitelabelPlugin->findPrimeadsSecureUrlParameter());
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $url,
                ['timeout' => 5]
            )
            ->willReturn($this->response)
            ->willThrowException(new Exception());

        $message = json_encode(['message' => "Unsuccessful request to primeads, response: ", 'url' => $url]);
        $this->whitelabelPluginLogRepository
            ->expects($this->once())
            ->method('addErrorLog')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, $message);

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_whitelabelUserIsNotFromAff()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::TRAFFIC_BAR_TOKEN;
        $user->id = 1;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'trafs';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::TRAFFIC_BAR_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::TRAFFIC_BAR_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_pluginDisabled()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Fuel::$env = Fuel::PRODUCTION;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;

        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->is_enabled = true;
        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserAffTokenByWhitelabelUserId');


        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_userAffTokenNotExists()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->id = 1;
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn('');

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_userClickIdNotExists()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->id = 1;
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;
        $whitelabelPlugin = new WhitelabelPlugin();
        $whitelabelPlugin->id = 1;
        $whitelabelPlugin->whitelabel_id = 1;
        $whitelabelPlugin->plugin = 'primeads';
        $whitelabelPlugin->is_enabled = true;
        $whitelabelPluginUser = new WhitelabelPluginUser();
        $whitelabelPluginUser->whitelabelUserId = $user->id;
        $whitelabelPluginUser->whitelabelPluginId = $whitelabelPlugin->id;
        $userClickId = 'u2s3e4r';
        $whitelabelPluginUser->data = '{"clickId":"' . $userClickId . '","affToken":"' . self::PRIMEADS_TOKEN . '","externalAffId":"22"}';

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn($whitelabelPlugin);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserAffTokenByWhitelabelUserId')
            ->with($user->id)
            ->willReturn(self::PRIMEADS_TOKEN);

        $this->whitelabelPluginUserRepository
            ->expects($this->once())
            ->method('getUserClickIdByWhitelabelUserId')
            ->with($user->id)
            ->willReturn('');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendToPrimeadsClickIdAndCommission_isNotLottopark()
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->id = 1;
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::REDFOXLOTTO_THEME;

        $this->whitelabelPluginRepository
            ->expects($this->never())
            ->method('findPluginByNameAndWhitelabelId');

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserAffTokenByWhitelabelUserId');

        $this->whitelabelPluginUserRepository
            ->expects($this->never())
            ->method('getUserClickIdByWhitelabelUserId');

        $this->whitelabelAffCommissionRepository
            ->expects($this->never())
            ->method('getLastUserTransactionCommission');

        $this->client
            ->expects($this->never())
            ->method('request');

        $this->response
            ->expects($this->never())
            ->method('getStatusCode');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    /** @test */
    public function sendCommissionToPrimeadsByUser_PluginNotExists(): void
    {
        $user = $this->whitelabelUserFixture->withUserAff($this->whitelabelUserAff)->withWhitelabelAff($this->whitelabelAff)->with('basic')->makeOne();
        $user->id = 1;
        $user->whitelabel_user_aff->whitelabel_aff->token = self::PRIMEADS_TOKEN;
        Container::get('whitelabel')->theme = Whitelabel::LOTTOPARK_THEME;

        $this->whitelabelPluginRepository
            ->expects($this->once())
            ->method('findPluginByNameAndWhitelabelId')
            ->with(WhitelabelPlugin::PRIMEADS_NAME, Container::get('whitelabel')->id)
            ->willReturn(null);

        $this->client
            ->expects($this->never())
            ->method('request');

        $result = $this->transactionService->sendCommissionToPrimeadsByUser($user);
        $this->assertFalse($result);
    }

    private function generatePrimeadsTransactionUrl(string $userClickId, string $commission, string $secure): string
    {
        return "https://s.primeads.io/postback?clickid={$userClickId}&status=2&goal=rs&sum={$commission}&secure={$secure}";
    }
}
